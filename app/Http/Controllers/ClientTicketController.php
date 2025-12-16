<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Modules\Task\Models\Task;
use App\Modules\Task\Models\TaskComment;
use App\Modules\Task\Enums\ActivityType;
use App\Modules\Task\Models\TaskActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientTicketController extends Controller
{
    /**
     * Display the client ticket view page.
     */
    public function show(Request $request, string $task): View
    {
        $token = $request->query('token');

        if (!$token) {
            abort(403, 'Access token is required.');
        }

        $task = Task::where('uuid', $task)
            ->where('client_token', $token)
            ->with([
                'workspace',
                'status',
                'assignee',
                'department',
                'workspacePriority',
                'comments' => fn ($q) => $q->with('user')->whereNull('parent_id')->orderBy('created_at', 'asc'),
                'comments.replies' => fn ($q) => $q->with('user')->orderBy('created_at', 'asc'),
            ])
            ->first();

        if (!$task) {
            abort(404, 'Ticket not found or invalid access token.');
        }

        // Only allow access for inbox workspace tickets
        if ($task->workspace->type->value !== 'inbox') {
            abort(403, 'This ticket is not accessible.');
        }

        return view('client.ticket-show', compact('task', 'token'));
    }

    /**
     * Add a reply to the ticket.
     */
    public function reply(Request $request, string $task): RedirectResponse
    {
        $token = $request->input('token');

        if (!$token) {
            abort(403, 'Access token is required.');
        }

        $task = Task::where('uuid', $task)
            ->where('client_token', $token)
            ->with(['workspace', 'creator'])
            ->first();

        if (!$task) {
            abort(404, 'Ticket not found or invalid access token.');
        }

        // Only allow replies for inbox workspace tickets
        if ($task->workspace->type->value !== 'inbox') {
            abort(403, 'This ticket is not accessible.');
        }

        $request->validate([
            'content' => 'required|string|max:10000',
        ]);

        // Create the comment using the task creator (customer who submitted the ticket)
        $comment = TaskComment::create([
            'task_id' => $task->id,
            'user_id' => $task->created_by,
            'content' => '<p>' . nl2br(e($request->input('content'))) . '</p>',
            'parent_id' => null,
            'source' => 'client_portal',
        ]);

        // Log activity
        if ($task->creator) {
            TaskActivity::log($task, $task->creator, ActivityType::COMMENT_ADDED);
        }

        return redirect()
            ->route('client.ticket.show', ['task' => $task->uuid, 'token' => $token])
            ->with('success', 'Your reply has been submitted successfully.');
    }
}
