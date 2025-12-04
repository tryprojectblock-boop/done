<?php

declare(strict_types=1);

namespace App\Modules\Task\Policies;

use App\Models\User;
use App\Modules\Task\Models\Task;

class TaskPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Task $task): bool
    {
        // User can view if they're in the same company, or they're a watcher
        return $user->company_id === $task->company_id
            || $task->isWatcher($user)
            || $task->isOwner($user)
            || $task->isAssignee($user);
    }

    public function create(User $user): bool
    {
        // Only team members can create tasks
        return $user->company_id !== null;
    }

    public function update(User $user, Task $task): bool
    {
        return $task->canEdit($user);
    }

    public function delete(User $user, Task $task): bool
    {
        // Only task owner or admin can delete
        return $task->isOwner($user) || $user->isAdminOrHigher();
    }

    public function close(User $user, Task $task): bool
    {
        return $task->canClose($user);
    }

    public function changeStatus(User $user, Task $task): bool
    {
        return $task->canChangeStatus($user);
    }
}
