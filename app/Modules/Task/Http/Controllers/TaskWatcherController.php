<?php

declare(strict_types=1);

namespace App\Modules\Task\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Task\Contracts\TaskServiceInterface;
use App\Modules\Task\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TaskWatcherController extends Controller
{
    public function __construct(
        private readonly TaskServiceInterface $taskService
    ) {}

    public function store(Request $request, Task $task): RedirectResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $this->taskService->addWatcher($task, $request->input('user_id'), auth()->user());

        return back()->with('success', 'Watcher added successfully.');
    }

    public function destroy(Task $task, int $userId): RedirectResponse
    {
        $this->taskService->removeWatcher($task, $userId);

        return back()->with('success', 'Watcher removed successfully.');
    }

    public function toggle(Task $task): RedirectResponse
    {
        $user = auth()->user();

        if ($task->isWatcher($user)) {
            $this->taskService->removeWatcher($task, $user->id);
            return back()->with('success', 'You are no longer watching this task.');
        }

        $this->taskService->addWatcher($task, $user->id, $user);
        return back()->with('success', 'You are now watching this task.');
    }
}
