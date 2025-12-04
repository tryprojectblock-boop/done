<?php

declare(strict_types=1);

namespace App\Modules\Task\Contracts;

use App\Models\User;
use App\Modules\Task\Models\Task;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface TaskServiceInterface
{
    public function getTasksForUser(User $user, array $filters = [], int $perPage = 20): LengthAwarePaginator;

    public function getTasksForWorkspace(int $workspaceId, array $filters = [], int $perPage = 20): LengthAwarePaginator;

    public function createTask(array $data, User $user): Task;

    public function updateTask(Task $task, array $data, User $user): Task;

    public function deleteTask(Task $task, User $user): bool;

    public function closeTask(Task $task, User $user): Task;

    public function reopenTask(Task $task, User $user): Task;

    public function changeStatus(Task $task, int $statusId, User $user): Task;

    public function changeAssignee(Task $task, ?int $assigneeId, User $user): Task;

    public function addWatcher(Task $task, int $userId, User $addedBy): void;

    public function removeWatcher(Task $task, int $userId): void;

    public function addTag(Task $task, int $tagId, User $user): void;

    public function removeTag(Task $task, int $tagId, User $user): void;

    public function addComment(Task $task, string $content, User $user, ?int $parentId = null): \App\Modules\Task\Models\TaskComment;

    public function getTaskByUuid(string $uuid): ?Task;
}
