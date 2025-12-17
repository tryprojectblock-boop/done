<?php

declare(strict_types=1);

namespace App\Modules\Task\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Task\Contracts\TaskServiceInterface;
use App\Modules\Task\Enums\ActivityType;
use App\Modules\Task\Models\Task;
use App\Modules\Task\Models\TaskActivity;
use App\Modules\Task\Models\TaskComment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TaskCommentController extends Controller
{
    public function __construct(
        private readonly TaskServiceInterface $taskService
    ) {}

    public function store(Request $request, Task $task): RedirectResponse
    {
        $request->validate([
            'content' => 'required|string|max:10000',
            'parent_id' => 'nullable|exists:task_comments,id',
            'is_private' => 'nullable|boolean',
        ]);

        $parentId = $request->input('parent_id');
        $isPrivate = (bool) $request->input('is_private', false);

        // Only team members can add private notes
        $user = auth()->user();
        if ($isPrivate && ($user->is_guest || $user->role === \App\Models\User::ROLE_GUEST)) {
            $isPrivate = false;
        }

        $this->taskService->addComment(
            $task,
            $request->input('content'),
            $user,
            $parentId ? (int) $parentId : null,
            $isPrivate
        );

        return back()->with('success', $isPrivate ? 'Private note added successfully.' : 'Comment added successfully.');
    }

    public function update(Request $request, TaskComment $comment): RedirectResponse
    {
        $user = auth()->user();

        if (!$comment->canEdit($user)) {
            return back()->with('error', 'You do not have permission to edit this comment.');
        }

        $request->validate([
            'content' => 'required|string|max:10000',
        ]);

        $comment->update(['content' => $request->input('content')]);
        $comment->markAsEdited();

        TaskActivity::log($comment->task, $user, ActivityType::COMMENT_EDITED);

        return back()->with('success', 'Comment updated successfully.');
    }

    public function destroy(TaskComment $comment): RedirectResponse
    {
        $user = auth()->user();

        if (!$comment->canDelete($user)) {
            return back()->with('error', 'You do not have permission to delete this comment.');
        }

        $task = $comment->task;
        $comment->delete();

        TaskActivity::log($task, $user, ActivityType::COMMENT_DELETED);

        return back()->with('success', 'Comment deleted successfully.');
    }
}
