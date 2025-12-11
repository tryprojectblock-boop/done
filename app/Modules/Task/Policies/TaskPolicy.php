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
        // Assignee can always view their assigned tasks
        if ($task->assignee_id == $user->id) {
            return true;
        }

        // Task owner can always view
        if ($task->created_by == $user->id) {
            return true;
        }

        // Watchers can view
        if ($task->isWatcher($user)) {
            return true;
        }

        // Same company can view
        if ($user->company_id == $task->company_id) {
            return true;
        }

        // Workspace members can view tasks in their workspace
        if ($task->workspace && $task->workspace->hasMember($user)) {
            return true;
        }

        return false;
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
