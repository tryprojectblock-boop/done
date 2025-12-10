<?php

declare(strict_types=1);

namespace App\Modules\Task\Services;

use App\Models\User;
use App\Modules\Task\Contracts\TaskServiceInterface;
use App\Modules\Task\Enums\ActivityType;
use App\Modules\Task\Models\Tag;
use App\Modules\Task\Models\Task;
use App\Modules\Task\Models\TaskActivity;
use App\Modules\Task\Models\TaskComment;
use App\Services\NotificationService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class TaskService implements TaskServiceInterface
{
    public function __construct(
        private readonly NotificationService $notificationService
    ) {}

    public function getTasksForUser(User $user, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = Task::query()
            ->with(['workspace', 'status', 'assignee', 'creator', 'tags'])
            ->where('company_id', $user->company_id)
            ->where(function ($q) use ($user) {
                $q->where('assignee_id', $user->id)
                    ->orWhere('created_by', $user->id)
                    ->orWhereHas('watchers', fn ($wq) => $wq->where('user_id', $user->id));
            });

        return $this->applyFilters($query, $filters)->paginate($perPage);
    }

    public function getTasksForWorkspace(int $workspaceId, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = Task::query()
            ->with(['workspace', 'status', 'assignee', 'creator', 'tags'])
            ->where('workspace_id', $workspaceId);

        return $this->applyFilters($query, $filters)->paginate($perPage);
    }

    protected function applyFilters($query, array $filters)
    {
        if (!empty($filters['workspace_id'])) {
            $query->where('workspace_id', $filters['workspace_id']);
        }

        if (!empty($filters['status_id'])) {
            $query->where('status_id', $filters['status_id']);
        }

        if (!empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['assignee_id'])) {
            $query->where('assignee_id', $filters['assignee_id']);
        }

        if (!empty($filters['created_by'])) {
            $query->where('created_by', $filters['created_by']);
        }

        if (!empty($filters['tag_id'])) {
            $query->whereHas('tags', fn ($q) => $q->where('tag_id', $filters['tag_id']));
        }

        if (isset($filters['is_closed'])) {
            if ($filters['is_closed']) {
                $query->whereNotNull('closed_at');
            } else {
                $query->whereNull('closed_at');
            }
        }

        if (!empty($filters['due_date_from'])) {
            $query->where('due_date', '>=', $filters['due_date_from']);
        }

        if (!empty($filters['due_date_to'])) {
            $query->where('due_date', '<=', $filters['due_date_to']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('task_number', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortField = $filters['sort'] ?? 'created_at';
        $sortDirection = $filters['direction'] ?? 'desc';
        $allowedSortFields = ['created_at', 'updated_at', 'due_date', 'priority', 'title', 'task_number'];

        if (in_array($sortField, $allowedSortFields)) {
            $query->orderBy($sortField, $sortDirection);
        }

        // Include subtasks or parent tasks only
        if (isset($filters['parent_tasks_only']) && $filters['parent_tasks_only']) {
            $query->whereNull('parent_task_id');
        }

        return $query;
    }

    public function createTask(array $data, User $user): Task
    {
        return DB::transaction(function () use ($data, $user) {
            // Handle type - ensure it's an array
            $types = $data['type'] ?? ['task'];
            if (!is_array($types)) {
                $types = [$types];
            }
            // Filter out empty values
            $types = array_filter($types);
            if (empty($types)) {
                $types = ['task'];
            }

            // Handle assignee - support both assignee_id (single) and assignee_ids (array from multi-select)
            $assigneeId = $data['assignee_id'] ?? null;
            if (empty($assigneeId) && !empty($data['assignee_ids'])) {
                // Take the first assignee from the array
                $assigneeId = $data['assignee_ids'][0] ?? null;
            }

            $task = Task::create([
                'workspace_id' => $data['workspace_id'],
                'company_id' => $user->company_id,
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'type' => $types,
                'priority' => $data['priority'] ?? 'medium',
                'status_id' => $data['status_id'] ?? null,
                'assignee_id' => $assigneeId,
                'created_by' => $user->id,
                'due_date' => $data['due_date'] ?? null,
                'start_date' => $data['start_date'] ?? null,
                'parent_task_id' => $data['parent_task_id'] ?? null,
                'parent_link_notes' => $data['parent_link_notes'] ?? null,
                'estimated_time' => $data['estimated_time'] ?? null,
                'milestone_id' => $data['milestone_id'] ?? null,
            ]);

            // Handle tags from tagify (JSON format) or tag_ids array
            if (!empty($data['tags'])) {
                $tagIds = $this->processTagifyTags($data['tags'], $user, $data['workspace_id']);
                if (!empty($tagIds)) {
                    $task->tags()->attach($tagIds);
                }
            } elseif (!empty($data['tag_ids'])) {
                $task->tags()->attach($data['tag_ids']);
            }

            // Add watchers if provided
            if (!empty($data['watcher_ids'])) {
                foreach ($data['watcher_ids'] as $watcherId) {
                    $task->watchers()->attach($watcherId, ['added_by' => $user->id]);
                }
            }

            // Auto-add creator as watcher
            if (!$task->watchers()->where('user_id', $user->id)->exists()) {
                $task->watchers()->attach($user->id, ['added_by' => $user->id]);
            }

            // Log activity
            TaskActivity::log($task, $user, ActivityType::CREATED);

            // Create notifications for mentioned users in description
            if (!empty($data['description'])) {
                $this->notificationService->notifyMentionedUsers($data['description'], $user, $task);
            }

            // Create notification for assignee if assigned to someone else
            if ($assigneeId && $assigneeId !== $user->id) {
                $assignee = User::find($assigneeId);
                if ($assignee) {
                    $this->notificationService->createTaskAssignedNotification($assignee, $user, $task);
                }
            }

            return $task->fresh(['workspace', 'status', 'assignee', 'creator', 'tags', 'watchers']);
        });
    }

    public function updateTask(Task $task, array $data, User $user): Task
    {
        return DB::transaction(function () use ($task, $data, $user) {
            $changes = [];

            // Track title change
            if (isset($data['title']) && $data['title'] !== $task->title) {
                $changes['title'] = [
                    'old' => ['title' => $task->title],
                    'new' => ['title' => $data['title']],
                    'type' => ActivityType::TITLE_CHANGED,
                ];
            }

            // Track description change
            if (isset($data['description']) && $data['description'] !== $task->description) {
                $changes['description'] = [
                    'type' => ActivityType::DESCRIPTION_CHANGED,
                ];
            }

            // Track due date change
            if (array_key_exists('due_date', $data)) {
                $oldDate = $task->due_date?->format('Y-m-d');
                $newDate = $data['due_date'];
                if ($oldDate !== $newDate) {
                    $changes['due_date'] = [
                        'old' => ['date' => $oldDate],
                        'new' => ['date' => $newDate],
                        'type' => ActivityType::DUE_DATE_CHANGED,
                    ];
                }
            }

            // Update the task
            $task->update($data);

            // Log changes
            foreach ($changes as $change) {
                TaskActivity::log(
                    $task,
                    $user,
                    $change['type'],
                    $change['old'] ?? null,
                    $change['new'] ?? null
                );
            }

            return $task->fresh(['workspace', 'status', 'assignee', 'creator', 'tags', 'watchers']);
        });
    }

    public function deleteTask(Task $task, User $user): bool
    {
        return $task->delete();
    }

    public function closeTask(Task $task, User $user): Task
    {
        $task->update([
            'closed_at' => now(),
            'closed_by' => $user->id,
        ]);

        TaskActivity::log($task, $user, ActivityType::CLOSED);

        // Recalculate milestone progress if task is linked to a milestone
        if ($task->milestone_id) {
            $task->milestone?->recalculateProgress();
        }

        return $task->fresh();
    }

    public function reopenTask(Task $task, User $user): Task
    {
        $task->update([
            'closed_at' => null,
            'closed_by' => null,
        ]);

        TaskActivity::log($task, $user, ActivityType::REOPENED);

        // Recalculate milestone progress if task is linked to a milestone
        if ($task->milestone_id) {
            $task->milestone?->recalculateProgress();
        }

        return $task->fresh();
    }

    public function changeStatus(Task $task, int $statusId, User $user): Task
    {
        $oldStatus = $task->status;

        $task->update(['status_id' => $statusId]);
        $task->refresh();

        $newStatus = $task->status;

        TaskActivity::log(
            $task,
            $user,
            ActivityType::STATUS_CHANGED,
            ['id' => $oldStatus?->id, 'name' => $oldStatus?->name],
            ['id' => $newStatus?->id, 'name' => $newStatus?->name]
        );

        return $task;
    }

    public function changeAssignee(Task $task, ?int $assigneeId, User $user): Task
    {
        $oldAssignee = $task->assignee;

        $task->update(['assignee_id' => $assigneeId]);
        $task->refresh();

        $newAssignee = $task->assignee;

        TaskActivity::log(
            $task,
            $user,
            ActivityType::ASSIGNEE_CHANGED,
            $oldAssignee ? ['id' => $oldAssignee->id, 'name' => $oldAssignee->name] : null,
            $newAssignee ? ['id' => $newAssignee->id, 'name' => $newAssignee->name] : null
        );

        // Auto-add new assignee as watcher
        if ($assigneeId && !$task->watchers()->where('user_id', $assigneeId)->exists()) {
            $task->watchers()->attach($assigneeId, ['added_by' => $user->id]);
        }

        // Notify new assignee if assigned to someone else
        if ($newAssignee && $newAssignee->id !== $user->id) {
            $this->notificationService->createTaskAssignedNotification($newAssignee, $user, $task);
        }

        return $task;
    }

    public function addWatcher(Task $task, int $userId, User $addedBy): void
    {
        if ($task->watchers()->where('user_id', $userId)->exists()) {
            return;
        }

        $task->watchers()->attach($userId, ['added_by' => $addedBy->id]);

        $watcher = User::find($userId);
        TaskActivity::log(
            $task,
            $addedBy,
            ActivityType::WATCHER_ADDED,
            null,
            $watcher ? ['id' => $watcher->id, 'name' => $watcher->name] : null
        );
    }

    public function removeWatcher(Task $task, int $userId): void
    {
        $task->watchers()->detach($userId);
    }

    public function addTag(Task $task, int $tagId, User $user): void
    {
        if ($task->tags()->where('tag_id', $tagId)->exists()) {
            return;
        }

        $task->tags()->attach($tagId);

        $tag = $task->tags()->find($tagId);
        TaskActivity::log(
            $task,
            $user,
            ActivityType::TAG_ADDED,
            null,
            $tag ? ['id' => $tag->id, 'name' => $tag->name] : null
        );
    }

    public function removeTag(Task $task, int $tagId, User $user): void
    {
        $tag = $task->tags()->find($tagId);

        $task->tags()->detach($tagId);

        if ($tag) {
            TaskActivity::log(
                $task,
                $user,
                ActivityType::TAG_REMOVED,
                ['id' => $tag->id, 'name' => $tag->name],
                null
            );
        }
    }

    public function addComment(Task $task, string $content, User $user, ?int $parentId = null): TaskComment
    {
        $comment = TaskComment::create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'content' => $content,
            'parent_id' => $parentId,
        ]);

        TaskActivity::log($task, $user, ActivityType::COMMENT_ADDED);

        // Auto-add commenter as watcher
        if (!$task->watchers()->where('user_id', $user->id)->exists()) {
            $task->watchers()->attach($user->id, ['added_by' => $user->id]);
        }

        // Create notifications for mentioned users
        $this->notificationService->notifyMentionedUsers($content, $user, $task, $comment);

        return $comment->fresh(['user']);
    }

    public function getTaskByUuid(string $uuid): ?Task
    {
        return Task::where('uuid', $uuid)
            ->with(['workspace', 'status', 'assignee', 'creator', 'tags', 'watchers', 'comments.user', 'activities.user', 'attachments'])
            ->first();
    }

    /**
     * Process tags from Tagify input (JSON format).
     * Creates new tags if they don't exist.
     *
     * @param string $tagsJson JSON string from Tagify
     * @param User $user The user creating the tags
     * @param int|string $workspaceId The workspace ID
     * @return array Array of tag IDs
     */
    protected function processTagifyTags(string $tagsJson, User $user, int|string $workspaceId): array
    {
        if (empty($tagsJson)) {
            return [];
        }

        $tagsData = json_decode($tagsJson, true);

        if (!is_array($tagsData)) {
            return [];
        }

        $tagIds = [];
        $colors = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899', '#06b6d4', '#84cc16'];

        foreach ($tagsData as $tagData) {
            $tagName = trim($tagData['value'] ?? '');

            if (empty($tagName)) {
                continue;
            }

            // Check if tag already exists for this company
            $existingTag = Tag::where('company_id', $user->company_id)
                ->where('name', $tagName)
                ->first();

            if ($existingTag) {
                $tagIds[] = $existingTag->id;
            } else {
                // Create new tag with random color
                $newTag = Tag::create([
                    'company_id' => $user->company_id,
                    'name' => $tagName,
                    'color' => $colors[array_rand($colors)],
                ]);
                $tagIds[] = $newTag->id;
            }
        }

        return $tagIds;
    }
}
