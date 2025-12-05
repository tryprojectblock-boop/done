<?php

declare(strict_types=1);

namespace App\Modules\Idea\Services;

use App\Models\User;
use App\Modules\Idea\Contracts\IdeaServiceInterface;
use App\Modules\Idea\Enums\IdeaStatus;
use App\Modules\Idea\Models\Idea;
use App\Modules\Idea\Models\IdeaComment;
use App\Modules\Idea\Models\IdeaVote;
use App\Services\NotificationService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class IdeaService implements IdeaServiceInterface
{
    public function __construct(
        private readonly NotificationService $notificationService
    ) {}

    public function getIdeasForUser(User $user, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = Idea::query()
            ->with(['workspace', 'creator', 'members'])
            ->where('company_id', $user->company_id);

        return $this->applyFilters($query, $filters)->paginate($perPage);
    }

    public function getIdeasForWorkspace(int $workspaceId, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = Idea::query()
            ->with(['workspace', 'creator', 'members'])
            ->where('workspace_id', $workspaceId);

        return $this->applyFilters($query, $filters)->paginate($perPage);
    }

    protected function applyFilters($query, array $filters)
    {
        if (!empty($filters['workspace_id'])) {
            $query->where('workspace_id', $filters['workspace_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        if (!empty($filters['created_by'])) {
            $query->where('created_by', $filters['created_by']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('short_description', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortField = $filters['sort'] ?? 'created_at';
        $sortDirection = $filters['direction'] ?? 'desc';
        $allowedSortFields = ['created_at', 'updated_at', 'votes_count', 'comments_count', 'title'];

        if (in_array($sortField, $allowedSortFields)) {
            $query->orderBy($sortField, $sortDirection);
        }

        return $query;
    }

    public function createIdea(array $data, User $user): Idea
    {
        return DB::transaction(function () use ($data, $user) {
            $idea = Idea::create([
                'company_id' => $user->company_id,
                'workspace_id' => $data['workspace_id'] ?? null,
                'created_by' => $user->id,
                'title' => $data['title'],
                'short_description' => $data['short_description'] ?? null,
                'description' => $data['description'] ?? null,
                'priority' => $data['priority'] ?? 'medium',
                'status' => IdeaStatus::NEW,
            ]);

            // Add creator as a member
            $idea->addMember($user);

            // Add invited members if provided
            if (!empty($data['member_ids'])) {
                foreach ($data['member_ids'] as $memberId) {
                    if ($memberId != $user->id) {
                        $idea->addMember(User::find($memberId), $user);
                    }
                }
            }

            // Add invited guests if provided
            if (!empty($data['guest_ids'])) {
                foreach ($data['guest_ids'] as $guestId) {
                    if ($guestId != $user->id) {
                        $idea->addMember(User::find($guestId), $user);
                    }
                }
            }

            // Notify mentioned users in description
            if (!empty($data['description'])) {
                $this->notificationService->notifyMentionedUsers(
                    $data['description'],
                    $user,
                    $idea
                );
            }

            return $idea->fresh(['workspace', 'creator', 'members']);
        });
    }

    public function updateIdea(Idea $idea, array $data, User $user): Idea
    {
        return DB::transaction(function () use ($idea, $data, $user) {
            $idea->update([
                'title' => $data['title'] ?? $idea->title,
                'short_description' => $data['short_description'] ?? $idea->short_description,
                'description' => $data['description'] ?? $idea->description,
                'workspace_id' => $data['workspace_id'] ?? $idea->workspace_id,
                'priority' => $data['priority'] ?? $idea->priority,
            ]);

            // Combine member_ids and guest_ids for updating
            $allMemberIds = [];
            if (isset($data['member_ids'])) {
                $allMemberIds = array_merge($allMemberIds, $data['member_ids']);
            }
            if (isset($data['guest_ids'])) {
                $allMemberIds = array_merge($allMemberIds, $data['guest_ids']);
            }

            // Update members if either member_ids or guest_ids is provided
            if (isset($data['member_ids']) || isset($data['guest_ids'])) {
                $currentMemberIds = $idea->members()->pluck('users.id')->toArray();
                $newMemberIds = $allMemberIds;

                // Always keep the creator
                if (!in_array($idea->created_by, $newMemberIds)) {
                    $newMemberIds[] = $idea->created_by;
                }

                // Remove members not in the new list
                $toRemove = array_diff($currentMemberIds, $newMemberIds);
                foreach ($toRemove as $memberId) {
                    if ($memberId != $idea->created_by) {
                        $idea->removeMember(User::find($memberId));
                    }
                }

                // Add new members
                $toAdd = array_diff($newMemberIds, $currentMemberIds);
                foreach ($toAdd as $memberId) {
                    $idea->addMember(User::find($memberId), $user);
                }
            }

            return $idea->fresh(['workspace', 'creator', 'members']);
        });
    }

    public function deleteIdea(Idea $idea, User $user): bool
    {
        return $idea->delete();
    }

    public function changeStatus(Idea $idea, IdeaStatus $status, User $user): Idea
    {
        $idea->markAsReviewed($user, $status);
        return $idea->fresh();
    }

    public function vote(Idea $idea, User $user, int $vote): void
    {
        $vote = max(-1, min(1, $vote)); // Ensure vote is -1, 0, or 1

        IdeaVote::updateOrCreate(
            ['idea_id' => $idea->id, 'user_id' => $user->id],
            ['vote' => $vote]
        );
    }

    public function removeVote(Idea $idea, User $user): void
    {
        IdeaVote::where('idea_id', $idea->id)
            ->where('user_id', $user->id)
            ->delete();
    }

    public function addComment(Idea $idea, string $content, User $user, ?int $parentId = null): IdeaComment
    {
        $comment = IdeaComment::create([
            'idea_id' => $idea->id,
            'user_id' => $user->id,
            'content' => $content,
            'parent_id' => $parentId,
        ]);

        // Auto-add commenter as member if not already
        if (!$idea->isMember($user)) {
            $idea->addMember($user);
        }

        // Notify mentioned users
        $this->notificationService->notifyMentionedUsers($content, $user, $idea, null);

        return $comment->fresh(['user']);
    }

    public function updateComment(IdeaComment $comment, string $content, User $user): IdeaComment
    {
        $comment->update(['content' => $content]);
        $comment->markAsEdited();

        return $comment->fresh(['user']);
    }

    public function deleteComment(IdeaComment $comment, User $user): bool
    {
        return $comment->delete();
    }

    public function addMember(Idea $idea, int $userId, User $invitedBy): void
    {
        $user = User::find($userId);
        if ($user) {
            $idea->addMember($user, $invitedBy);
        }
    }

    public function removeMember(Idea $idea, int $userId): void
    {
        $user = User::find($userId);
        if ($user && $userId !== $idea->created_by) {
            $idea->removeMember($user);
        }
    }

    public function getIdeaByUuid(string $uuid): ?Idea
    {
        return Idea::where('uuid', $uuid)
            ->with(['workspace', 'creator', 'members', 'comments.user', 'comments.replies.user'])
            ->first();
    }
}
