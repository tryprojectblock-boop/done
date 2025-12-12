<?php

declare(strict_types=1);

namespace App\Modules\Discussion\Services;

use App\Models\User;
use App\Modules\Discussion\Contracts\DiscussionServiceInterface;
use App\Modules\Discussion\Models\Discussion;
use App\Modules\Discussion\Models\DiscussionAttachment;
use App\Modules\Discussion\Models\DiscussionComment;
use App\Modules\Discussion\Models\DiscussionCommentAttachment;
use App\Services\NotificationService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DiscussionService implements DiscussionServiceInterface
{
    public function __construct(
        private readonly NotificationService $notificationService
    ) {}

    public function getDiscussionsForUser(User $user, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = Discussion::query()
            ->with(['workspace', 'creator', 'participants'])
            ->accessibleBy($user);

        return $this->applyFilters($query, $filters, $user)->paginate($perPage);
    }

    protected function applyFilters($query, array $filters, User $user)
    {
        if (!empty($filters['workspace_id'])) {
            $query->where('workspace_id', $filters['workspace_id']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['is_public']) && $filters['is_public'] !== '') {
            $query->where('is_public', $filters['is_public'] === '1');
        }

        if (!empty($filters['my_discussions'])) {
            $query->where(function ($q) use ($user) {
                $q->where('created_by', $user->id)
                    ->orWhereHas('participants', function ($sub) use ($user) {
                        $sub->where('user_id', $user->id);
                    });
            });
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('details', 'like', "%{$search}%");
            });
        }

        // Default sort by last activity
        $sortField = $filters['sort'] ?? 'last_activity_at';
        $sortDirection = $filters['direction'] ?? 'desc';
        $allowedSortFields = ['created_at', 'last_activity_at', 'comments_count', 'title'];

        if (in_array($sortField, $allowedSortFields)) {
            $query->orderBy($sortField, $sortDirection);
        }

        return $query;
    }

    public function getDiscussionByUuid(string $uuid): ?Discussion
    {
        return Discussion::where('uuid', $uuid)
            ->with(['workspace', 'creator', 'participants', 'attachments', 'comments.user', 'comments.replies.user', 'comments.attachments'])
            ->first();
    }

    public function createDiscussion(array $data, User $user): Discussion
    {
        return DB::transaction(function () use ($data, $user) {
            $discussion = Discussion::create([
                'company_id' => $user->company_id,
                'workspace_id' => $data['workspace_id'] ?? null,
                'created_by' => $user->id,
                'title' => $data['title'],
                'details' => $data['details'] ?? null,
                'type' => $data['type'] ?? null,
                'is_public' => $data['is_public'] ?? false,
                'last_activity_at' => now(),
            ]);

            // Add creator as participant
            $discussion->addParticipant($user);

            // Add invited members
            if (!empty($data['member_ids'])) {
                foreach ($data['member_ids'] as $memberId) {
                    if ($memberId != $user->id) {
                        $discussion->addParticipant(User::find($memberId), $user);
                    }
                }
            }

            // Add invited guests
            if (!empty($data['guest_ids'])) {
                foreach ($data['guest_ids'] as $guestId) {
                    if ($guestId != $user->id) {
                        $discussion->addParticipant(User::find($guestId), $user);
                    }
                }
            }

            // Handle attachments
            if (!empty($data['attachments'])) {
                foreach ($data['attachments'] as $file) {
                    $this->storeAttachment($discussion, $file, $user);
                }
            }

            // Notify mentioned users in details
            if (!empty($data['details'])) {
                $this->notificationService->notifyMentionedUsers(
                    $data['details'],
                    $user,
                    $discussion
                );
            }

            // Notify all added participants about the new discussion
            $allParticipantIds = array_merge(
                $data['member_ids'] ?? [],
                $data['guest_ids'] ?? []
            );
            if (!empty($allParticipantIds)) {
                $this->notificationService->createDiscussionAddedNotifications(
                    $discussion,
                    $user,
                    $allParticipantIds
                );
            }

            return $discussion->fresh(['workspace', 'creator', 'participants', 'attachments']);
        });
    }

    public function updateDiscussion(Discussion $discussion, array $data, User $user): Discussion
    {
        return DB::transaction(function () use ($discussion, $data, $user) {
            $discussion->update([
                'title' => $data['title'] ?? $discussion->title,
                'details' => $data['details'] ?? $discussion->details,
                'workspace_id' => $data['workspace_id'] ?? $discussion->workspace_id,
                'type' => $data['type'] ?? $discussion->type,
                'is_public' => $data['is_public'] ?? $discussion->is_public,
            ]);

            // Update participants
            $allParticipantIds = [];
            if (isset($data['member_ids'])) {
                $allParticipantIds = array_merge($allParticipantIds, $data['member_ids']);
            }
            if (isset($data['guest_ids'])) {
                $allParticipantIds = array_merge($allParticipantIds, $data['guest_ids']);
            }

            if (isset($data['member_ids']) || isset($data['guest_ids'])) {
                $currentParticipantIds = $discussion->participants()->pluck('users.id')->toArray();
                $newParticipantIds = $allParticipantIds;

                // Always keep the creator
                if (!in_array($discussion->created_by, $newParticipantIds)) {
                    $newParticipantIds[] = $discussion->created_by;
                }

                // Remove participants not in the new list
                $toRemove = array_diff($currentParticipantIds, $newParticipantIds);
                foreach ($toRemove as $participantId) {
                    if ($participantId != $discussion->created_by) {
                        $discussion->removeParticipant(User::find($participantId));
                    }
                }

                // Add new participants
                $toAdd = array_diff($newParticipantIds, $currentParticipantIds);
                foreach ($toAdd as $participantId) {
                    $discussion->addParticipant(User::find($participantId), $user);
                }
            }

            // Handle new attachments
            if (!empty($data['attachments'])) {
                foreach ($data['attachments'] as $file) {
                    $this->storeAttachment($discussion, $file, $user);
                }
            }

            return $discussion->fresh(['workspace', 'creator', 'participants', 'attachments']);
        });
    }

    public function deleteDiscussion(Discussion $discussion, User $user): bool
    {
        // Delete attachments from storage
        foreach ($discussion->attachments as $attachment) {
            Storage::delete($attachment->path);
        }

        return $discussion->delete();
    }

    public function addComment(Discussion $discussion, string $content, User $user, ?int $parentId = null, array $attachments = []): DiscussionComment
    {
        return DB::transaction(function () use ($discussion, $content, $user, $parentId, $attachments) {
            $comment = DiscussionComment::create([
                'discussion_id' => $discussion->id,
                'user_id' => $user->id,
                'parent_id' => $parentId,
                'content' => $content,
            ]);

            // Handle attachments
            foreach ($attachments as $file) {
                $this->storeCommentAttachment($comment, $file, $user);
            }

            // Auto-add commenter as participant if not already
            if (!$discussion->isParticipant($user) && !$discussion->isCreator($user)) {
                $discussion->addParticipant($user);
            }

            // Update discussion stats
            $discussion->updateCommentsCount();
            $discussion->updateLastActivity();

            // Parse mentioned user IDs from comment content
            $mentionedUserIds = $this->notificationService->parseMentionsFromContent($content);

            // Notify mentioned users
            $this->notificationService->notifyMentionedUsers($content, $user, $discussion, $comment);

            // Create in-app notifications for creator and participants (excluding mentioned users who already got notified)
            $this->notificationService->createDiscussionCommentNotifications($discussion, $comment, $user, $mentionedUserIds);

            return $comment->fresh(['user', 'attachments']);
        });
    }

    public function updateComment(DiscussionComment $comment, string $content, User $user): DiscussionComment
    {
        $comment->update(['content' => $content]);
        $comment->markAsEdited();

        return $comment->fresh(['user', 'attachments']);
    }

    public function deleteComment(DiscussionComment $comment, User $user): bool
    {
        $discussion = $comment->discussion;

        // Delete attachments from storage
        foreach ($comment->attachments as $attachment) {
            Storage::delete($attachment->path);
        }

        $result = $comment->delete();

        // Update discussion stats
        $discussion->updateCommentsCount();

        return $result;
    }

    public function addParticipant(Discussion $discussion, int $userId, User $invitedBy): void
    {
        $user = User::find($userId);
        if ($user) {
            $discussion->addParticipant($user, $invitedBy);
        }
    }

    public function removeParticipant(Discussion $discussion, int $userId): void
    {
        $user = User::find($userId);
        if ($user && $userId !== $discussion->created_by) {
            $discussion->removeParticipant($user);
        }
    }

    public function addAttachment(Discussion $discussion, array $fileData, User $user): void
    {
        $this->storeAttachment($discussion, $fileData['file'], $user);
    }

    public function removeAttachment(int $attachmentId): void
    {
        $attachment = DiscussionAttachment::find($attachmentId);
        if ($attachment) {
            Storage::delete($attachment->path);
            $attachment->delete();
        }
    }

    protected function storeAttachment(Discussion $discussion, $file, User $user): DiscussionAttachment
    {
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('discussions/' . $discussion->id, $filename, 'public');

        return DiscussionAttachment::create([
            'discussion_id' => $discussion->id,
            'filename' => $filename,
            'original_filename' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'path' => $path,
            'uploaded_by' => $user->id,
        ]);
    }

    protected function storeCommentAttachment(DiscussionComment $comment, $file, User $user): DiscussionCommentAttachment
    {
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('discussion-comments/' . $comment->id, $filename, 'public');

        return DiscussionCommentAttachment::create([
            'comment_id' => $comment->id,
            'filename' => $filename,
            'original_filename' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'path' => $path,
            'uploaded_by' => $user->id,
        ]);
    }
}
