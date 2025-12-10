<?php

declare(strict_types=1);

namespace App\Modules\Document\Services;

use App\Models\User;
use App\Modules\Document\Contracts\DocumentServiceInterface;
use App\Modules\Document\Enums\CollaboratorRole;
use App\Modules\Document\Models\Document;
use App\Modules\Document\Models\DocumentComment;
use App\Modules\Document\Models\DocumentCommentReply;
use App\Modules\Document\Models\DocumentVersion;
use App\Services\NotificationService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DocumentService implements DocumentServiceInterface
{
    private const AUTO_VERSION_INTERVAL_SECONDS = 300; // 5 minutes

    public function __construct(
        private readonly NotificationService $notificationService
    ) {}

    // ==================== DOCUMENT CRUD ====================

    public function getDocumentsForUser(User $user, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = Document::query()
            ->with(['workspace', 'creator', 'collaborators', 'lastEditor'])
            ->withCount('pages')
            ->accessibleBy($user);

        return $this->applyFilters($query, $filters, $user)->paginate($perPage);
    }

    protected function applyFilters($query, array $filters, User $user)
    {
        if (!empty($filters['workspace_id'])) {
            $query->where('workspace_id', $filters['workspace_id']);
        }

        if (!empty($filters['my_documents'])) {
            $query->where('created_by', $user->id);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Default sort by last edited
        $sortField = $filters['sort'] ?? 'last_edited_at';
        $sortDirection = $filters['direction'] ?? 'desc';
        $allowedSortFields = ['created_at', 'last_edited_at', 'title'];

        if (in_array($sortField, $allowedSortFields)) {
            $query->orderByRaw("COALESCE({$sortField}, created_at) {$sortDirection}");
        }

        return $query;
    }

    public function getDocumentByUuid(string $uuid): ?Document
    {
        return Document::where('uuid', $uuid)
            ->with([
                'workspace',
                'creator',
                'collaborators',
                'lastEditor',
                'comments' => function ($query) {
                    $query->with(['user', 'replies.user', 'resolvedByUser']);
                },
            ])
            ->first();
    }

    public function createDocument(array $data, User $user): Document
    {
        return DB::transaction(function () use ($data, $user) {
            $document = Document::create([
                'company_id' => $user->company_id,
                'workspace_id' => $data['workspace_id'] ?? null,
                'created_by' => $user->id,
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'content' => $data['content'] ?? '',
                'last_edited_by' => $user->id,
                'last_edited_at' => now(),
                'version_count' => 1,
            ]);

            // Create initial version
            DocumentVersion::create([
                'document_id' => $document->id,
                'user_id' => $user->id,
                'content' => $data['content'] ?? '',
                'version_number' => 1,
                'change_summary' => 'Document created',
            ]);

            // Add collaborators with individual roles
            if (!empty($data['collaborators'])) {
                foreach ($data['collaborators'] as $collaborator) {
                    $userId = $collaborator['user_id'] ?? $collaborator;
                    $role = $collaborator['role'] ?? CollaboratorRole::EDITOR->value;

                    if ($userId != $user->id) {
                        $collaboratorUser = User::find($userId);
                        if ($collaboratorUser) {
                            $document->addCollaborator(
                                $collaboratorUser,
                                CollaboratorRole::from($role),
                                $user
                            );
                        }
                    }
                }
            }

            return $document->fresh(['workspace', 'creator', 'collaborators']);
        });
    }

    public function updateDocument(Document $document, array $data, User $user): Document
    {
        return DB::transaction(function () use ($document, $data, $user) {
            $document->update([
                'title' => $data['title'] ?? $document->title,
                'description' => $data['description'] ?? $document->description,
                'workspace_id' => array_key_exists('workspace_id', $data) ? $data['workspace_id'] : $document->workspace_id,
            ]);

            // Update collaborators if provided
            if (isset($data['collaborators'])) {
                $currentCollaboratorIds = $document->collaborators()->pluck('users.id')->toArray();
                $newCollaboratorData = collect($data['collaborators'])->keyBy(fn($c) => $c['user_id'] ?? $c);

                // Remove collaborators not in the new list
                foreach ($currentCollaboratorIds as $currentId) {
                    if (!$newCollaboratorData->has($currentId)) {
                        $document->removeCollaborator(User::find($currentId));
                    }
                }

                // Add or update collaborators
                foreach ($data['collaborators'] as $collaborator) {
                    $userId = $collaborator['user_id'] ?? $collaborator;
                    $role = CollaboratorRole::from($collaborator['role'] ?? CollaboratorRole::READER->value);

                    if ($userId == $user->id || $userId == $document->created_by) {
                        continue;
                    }

                    if (in_array($userId, $currentCollaboratorIds)) {
                        $document->updateCollaboratorRole(User::find($userId), $role);
                    } else {
                        $document->addCollaborator(User::find($userId), $role, $user);
                    }
                }
            }

            return $document->fresh(['workspace', 'creator', 'collaborators']);
        });
    }

    public function deleteDocument(Document $document, User $user): bool
    {
        return $document->delete();
    }

    // ==================== CONTENT MANAGEMENT ====================

    public function updateContent(Document $document, string $content, User $user, ?string $summary = null): Document
    {
        return DB::transaction(function () use ($document, $content, $user, $summary) {
            $document->update([
                'content' => $content,
                'last_edited_by' => $user->id,
                'last_edited_at' => now(),
            ]);

            // Always create a version on manual save
            DocumentVersion::createForDocument($document, $user, $summary);

            return $document->fresh();
        });
    }

    public function autoSave(Document $document, string $content, User $user): Document
    {
        return DB::transaction(function () use ($document, $content, $user) {
            $document->update([
                'content' => $content,
                'last_edited_by' => $user->id,
                'last_edited_at' => now(),
            ]);

            // Create version if enough time has passed since last version
            $lastVersion = $document->getLatestVersion();

            if (!$lastVersion || $lastVersion->created_at->diffInSeconds(now()) >= self::AUTO_VERSION_INTERVAL_SECONDS) {
                DocumentVersion::createForDocument($document, $user, 'Auto-saved');
            }

            return $document;
        });
    }

    // ==================== COLLABORATOR MANAGEMENT ====================

    public function addCollaborator(Document $document, int $userId, string $role, User $invitedBy): void
    {
        $user = User::find($userId);

        if ($user && !$document->isCollaborator($user) && !$document->isCreator($user)) {
            $document->addCollaborator($user, CollaboratorRole::from($role), $invitedBy);

            // Send notification
            // $this->notificationService->createDocumentCollaboratorNotification($user, $invitedBy, $document, $role);
        }
    }

    public function removeCollaborator(Document $document, int $userId): void
    {
        $user = User::find($userId);

        if ($user && $userId !== $document->created_by) {
            $document->removeCollaborator($user);
        }
    }

    public function updateCollaboratorRole(Document $document, int $userId, string $role): void
    {
        $user = User::find($userId);

        if ($user && $document->isCollaborator($user)) {
            $document->updateCollaboratorRole($user, CollaboratorRole::from($role));
        }
    }

    // ==================== COMMENTS ====================

    public function addComment(Document $document, array $data, User $user): DocumentComment
    {
        return DB::transaction(function () use ($document, $data, $user) {
            $comment = DocumentComment::create([
                'document_id' => $document->id,
                'user_id' => $user->id,
                'selection_start' => $data['selection_start'] ?? null,
                'selection_end' => $data['selection_end'] ?? null,
                'selection_text' => $data['selection_text'] ?? null,
                'selection_id' => $data['selection_id'] ?? null,
                'content' => $data['content'],
            ]);

            // Notify mentioned users in comment
            $this->notificationService->notifyMentionedUsersInDocument(
                $data['content'],
                $user,
                $document,
                $comment
            );

            return $comment->fresh(['user', 'replies.user']);
        });
    }

    public function updateComment(DocumentComment $comment, string $content, User $user): DocumentComment
    {
        $comment->update(['content' => $content]);
        $comment->markAsEdited();

        return $comment->fresh(['user', 'replies.user']);
    }

    public function deleteComment(DocumentComment $comment, User $user): bool
    {
        return $comment->delete();
    }

    public function resolveComment(DocumentComment $comment, User $user): DocumentComment
    {
        $comment->markAsResolved($user);

        return $comment->fresh(['user', 'replies.user', 'resolvedByUser']);
    }

    public function unresolveComment(DocumentComment $comment, User $user): DocumentComment
    {
        $comment->markAsUnresolved();

        return $comment->fresh(['user', 'replies.user']);
    }

    // ==================== COMMENT REPLIES ====================

    public function addReply(DocumentComment $comment, string $content, User $user): DocumentCommentReply
    {
        return DB::transaction(function () use ($comment, $content, $user) {
            $reply = DocumentCommentReply::create([
                'comment_id' => $comment->id,
                'user_id' => $user->id,
                'content' => $content,
            ]);

            // Notify mentioned users
            $this->notificationService->notifyMentionedUsers(
                $content,
                $user,
                $comment->document,
                $comment
            );

            return $reply->fresh(['user']);
        });
    }

    public function updateReply(DocumentCommentReply $reply, string $content, User $user): DocumentCommentReply
    {
        $reply->update(['content' => $content]);
        $reply->markAsEdited();

        return $reply->fresh(['user']);
    }

    public function deleteReply(DocumentCommentReply $reply, User $user): bool
    {
        return $reply->delete();
    }

    // ==================== VERSION HISTORY ====================

    public function getVersions(Document $document, int $limit = 50): Collection
    {
        return $document->versions()
            ->with('user')
            ->limit($limit)
            ->get();
    }

    public function getVersion(Document $document, int $versionNumber): ?DocumentVersion
    {
        return $document->versions()
            ->where('version_number', $versionNumber)
            ->with('user')
            ->first();
    }

    public function getVersionById(Document $document, int $versionId): ?DocumentVersion
    {
        return $document->versions()
            ->where('id', $versionId)
            ->with('user')
            ->first();
    }

    public function restoreVersion(Document $document, int $versionNumber, User $user): Document
    {
        return DB::transaction(function () use ($document, $versionNumber, $user) {
            $version = $this->getVersion($document, $versionNumber);

            if (!$version) {
                throw new \InvalidArgumentException("Version {$versionNumber} not found");
            }

            // Update document with version content
            $document->update([
                'content' => $version->content,
                'last_edited_by' => $user->id,
                'last_edited_at' => now(),
            ]);

            // Create a new version to record the restore
            DocumentVersion::createForDocument(
                $document,
                $user,
                "Restored from version {$versionNumber}"
            );

            return $document->fresh();
        });
    }
}
