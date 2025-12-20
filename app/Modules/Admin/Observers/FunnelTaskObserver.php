<?php

declare(strict_types=1);

namespace App\Modules\Admin\Observers;

use App\Modules\Admin\Services\FunnelTagService;
use App\Modules\Task\Models\Task;

class FunnelTaskObserver
{
    public function __construct(
        protected FunnelTagService $tagService
    ) {}

    /**
     * Handle the Task "created" event.
     */
    public function created(Task $task): void
    {
        // Check if this is the user's first task
        $creator = $task->creator;
        if (!$creator) {
            return;
        }

        // Count tasks created by this user (excluding the one just created)
        $taskCount = Task::where('created_by', $creator->id)->count();

        // If this is the first task (count is 1 after creation)
        if ($taskCount === 1) {
            $this->tagService->addTag($creator, 'pb_first_task_created');
        }
    }
}
