<?php

declare(strict_types=1);

namespace App\Modules\Discussion\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Discussion\Contracts\DiscussionServiceInterface;
use App\Modules\Discussion\Enums\DiscussionType;
use App\Modules\Discussion\Http\Requests\StoreDiscussionRequest;
use App\Modules\Discussion\Http\Requests\UpdateDiscussionRequest;
use App\Modules\Discussion\Models\Discussion;
use App\Modules\Discussion\Models\DiscussionComment;
use App\Modules\Task\Models\Task;
use App\Modules\Workspace\Models\Workspace;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DiscussionController extends Controller
{
    public function __construct(
        private readonly DiscussionServiceInterface $discussionService
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();

        $filters = [
            'type' => $request->get('type'),
            'is_public' => $request->get('is_public'),
            'workspace_id' => $request->get('workspace_id'),
            'my_discussions' => $request->get('my_discussions'),
            'search' => $request->get('search'),
            'sort' => $request->get('sort', 'last_activity_at'),
            'direction' => $request->get('direction', 'desc'),
        ];

        $viewMode = $request->get('view', 'card');
        if (!in_array($viewMode, ['card', 'table'])) {
            $viewMode = 'table';
        }

        $discussions = $this->discussionService->getDiscussionsForUser($user, $filters, 20);
        $workspaces = Workspace::forUser($user)->get();
        $types = DiscussionType::options();

        return view('discussion::index', compact('discussions', 'workspaces', 'types', 'filters', 'viewMode'));
    }

    public function create(Request $request): View
    {
        $user = $request->user();
        $workspaces = Workspace::forUser($user)->get();
        $members = User::where('company_id', $user->company_id)
            ->where('id', '!=', $user->id)
            ->where('role', '!=', User::ROLE_GUEST)
            ->with('workspaces')
            ->get();

        // Get guests from user's workspaces
        $workspaceIds = $workspaces->pluck('id');
        $guestUserIds = \DB::table('workspace_guests')
            ->whereIn('workspace_id', $workspaceIds)
            ->pluck('user_id')
            ->unique();
        $guests = User::whereIn('id', $guestUserIds)
            ->where('id', '!=', $user->id)
            ->get();

        $types = DiscussionType::options();

        return view('discussion::create', compact('workspaces', 'members', 'guests', 'types'));
    }

    public function store(StoreDiscussionRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Handle file uploads
        if ($request->hasFile('attachments')) {
            $data['attachments'] = $request->file('attachments');
        }

        $discussion = $this->discussionService->createDiscussion($data, $request->user());

        return redirect()
            ->route('discussions.show', $discussion->uuid)
            ->with('success', 'Discussion created successfully!');
    }

    public function show(string $uuid): View
    {
        $discussion = $this->discussionService->getDiscussionByUuid($uuid);

        if (!$discussion) {
            abort(404);
        }

        $user = auth()->user();

        if (!$discussion->canView($user)) {
            abort(403, 'You don\'t have permission to view this discussion.');
        }

        // Get workspaces for task creation drawer
        $workspaces = Workspace::forUser($user)->get();

        return view('discussion::show', compact('discussion', 'user', 'workspaces'));
    }

    public function edit(string $uuid): View
    {
        $discussion = $this->discussionService->getDiscussionByUuid($uuid);

        if (!$discussion) {
            abort(404);
        }

        $user = auth()->user();

        if (!$discussion->canEdit($user)) {
            abort(403);
        }

        $workspaces = Workspace::forUser($user)->get();
        $members = User::where('company_id', $user->company_id)
            ->where('id', '!=', $discussion->created_by)
            ->where('role', '!=', User::ROLE_GUEST)
            ->with('workspaces')
            ->get();

        // Get guests from user's workspaces
        $workspaceIds = $workspaces->pluck('id');
        $guestUserIds = \DB::table('workspace_guests')
            ->whereIn('workspace_id', $workspaceIds)
            ->pluck('user_id')
            ->unique();
        $guests = User::whereIn('id', $guestUserIds)
            ->where('id', '!=', $discussion->created_by)
            ->get();

        $types = DiscussionType::options();

        return view('discussion::edit', compact('discussion', 'workspaces', 'members', 'guests', 'types'));
    }

    public function update(UpdateDiscussionRequest $request, string $uuid): RedirectResponse
    {
        $discussion = $this->discussionService->getDiscussionByUuid($uuid);

        if (!$discussion) {
            abort(404);
        }

        $user = $request->user();

        if (!$discussion->canEdit($user)) {
            abort(403);
        }

        $data = $request->validated();

        // Handle file uploads
        if ($request->hasFile('attachments')) {
            $data['attachments'] = $request->file('attachments');
        }

        $this->discussionService->updateDiscussion($discussion, $data, $user);

        return redirect()
            ->route('discussions.show', $discussion->uuid)
            ->with('success', 'Discussion updated successfully!');
    }

    public function destroy(string $uuid): RedirectResponse
    {
        $discussion = $this->discussionService->getDiscussionByUuid($uuid);

        if (!$discussion) {
            abort(404);
        }

        $user = auth()->user();

        if (!$discussion->canDelete($user)) {
            abort(403);
        }

        $this->discussionService->deleteDiscussion($discussion, $user);

        return redirect()
            ->route('discussions.index')
            ->with('success', 'Discussion deleted successfully!');
    }

    /**
     * Create a task from a discussion comment.
     */
    public function createTask(Request $request, Discussion $discussion): RedirectResponse
    {
        $user = $request->user();

        if (!$discussion->canView($user)) {
            return back()->with('error', 'You do not have permission to create tasks from this discussion.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'workspace_id' => ['required', 'exists:workspaces,id'],
            'description' => ['nullable', 'string'],
            'assignee_id' => ['nullable', 'exists:users,id'],
            'workspace_priority_id' => ['nullable', 'exists:workspace_priorities,id'],
            'due_date' => ['nullable', 'date'],
            'comment_id' => ['nullable', 'exists:discussion_comments,id'],
        ]);

        // Verify user has access to the workspace
        $workspace = Workspace::findOrFail($validated['workspace_id']);
        if (!$workspace->hasMember($user) && !$user->isAdminOrHigher()) {
            return back()->with('error', 'You do not have access to this workspace.');
        }

        // Get the default status for the workspace
        $defaultStatus = $workspace->workflow?->statuses()->where('is_default', true)->first()
            ?? $workspace->workflow?->statuses()->first();

        // Build description with link to discussion
        $description = $validated['description'] ?? '';
        $discussionLink = route('discussions.show', $discussion->uuid);
        $description .= "\n\n---\n📝 Created from discussion: [{$discussion->title}]({$discussionLink})";

        // Create the task
        $task = Task::create([
            'uuid' => Str::uuid()->toString(),
            'workspace_id' => $workspace->id,
            'title' => $validated['title'],
            'description' => $description,
            'status_id' => $defaultStatus?->id,
            'assignee_id' => $validated['assignee_id'] ?? null,
            'workspace_priority_id' => $validated['workspace_priority_id'] ?? null,
            'due_date' => $validated['due_date'] ?? null,
            'created_by' => $user->id,
        ]);

        return redirect()
            ->route('discussions.show', $discussion->uuid)
            ->with('success', "Task '{$task->title}' created successfully! <a href='" . route('tasks.show', $task->uuid) . "' class='link link-primary'>View Task</a>");
    }

    /**
     * Get tasks for a workspace (for AJAX).
     */
    public function getWorkspaceTasks(Request $request, Discussion $discussion, int $workspaceId): JsonResponse
    {
        $user = $request->user();

        if (!$discussion->canView($user)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Manually fetch workspace to avoid route model binding issues
        $workspace = Workspace::find($workspaceId);

        if (!$workspace) {
            return response()->json(['error' => 'Workspace not found'], 404);
        }

        // Verify user has access to the workspace
        if (!$workspace->hasMember($user) && !$user->isAdminOrHigher()) {
            return response()->json(['error' => 'You do not have access to this workspace'], 403);
        }

        // Get tasks for workspace, excluding already linked ones
        $linkedTaskIds = $discussion->tasks()->pluck('tasks.id')->toArray();

        $query = Task::where('workspace_id', $workspace->id)
            ->whereNotIn('id', $linkedTaskIds)
            ->with(['status', 'assignee'])
            ->orderBy('created_at', 'desc')
            ->limit(100);

        // Only apply visibleTo scope for non-admin users
        if (!$user->isAdminOrHigher()) {
            $query->visibleTo($user);
        }

        $tasks = $query->get()
            ->map(function ($task) use ($workspace) {
                $statusColor = $task->status?->color ?? '#6b7280';
                return [
                    'id' => $task->id,
                    'uuid' => $task->uuid,
                    'task_number' => $task->task_number,
                    'title' => $task->title,
                    'full_number' => $workspace->prefix . '-' . $task->task_number,
                    'status' => $task->status ? [
                        'name' => $task->status->name,
                        'color' => $statusColor,
                    ] : null,
                    'assignee' => $task->assignee ? [
                        'name' => $task->assignee->name,
                        'avatar_url' => $task->assignee->avatar_url,
                    ] : null,
                ];
            });

        return response()->json([
            'success' => true,
            'tasks' => $tasks,
            'workspace' => [
                'id' => $workspace->id,
                'name' => $workspace->name,
                'prefix' => $workspace->prefix,
            ],
        ]);
    }

    /**
     * Link tasks to a discussion.
     */
    public function linkTasks(Request $request, Discussion $discussion): JsonResponse
    {
        $user = $request->user();

        if (!$discussion->canView($user)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'task_ids' => 'required|array|min:1',
            'task_ids.*' => 'exists:tasks,id',
        ]);

        // Attach tasks with the linked_by user
        $attachData = [];
        foreach ($validated['task_ids'] as $taskId) {
            $attachData[$taskId] = ['linked_by' => $user->id];
        }

        $discussion->tasks()->syncWithoutDetaching($attachData);

        // Return updated linked tasks
        $linkedTasks = $discussion->tasks()
            ->with(['workspace', 'status'])
            ->get()
            ->map(function ($task) {
                $statusColor = $task->status?->color ?? '#6b7280';
                return [
                    'id' => $task->id,
                    'uuid' => $task->uuid,
                    'task_number' => $task->task_number,
                    'title' => $task->title,
                    'full_number' => ($task->workspace?->prefix ?? 'T') . '-' . $task->task_number,
                    'workspace_name' => $task->workspace?->name,
                    'status' => $task->status ? [
                        'name' => $task->status->name,
                        'color' => $statusColor,
                    ] : null,
                ];
            });

        return response()->json([
            'success' => true,
            'message' => count($validated['task_ids']) . ' task(s) linked successfully',
            'linked_tasks' => $linkedTasks,
        ]);
    }

    /**
     * Unlink a task from a discussion.
     */
    public function unlinkTask(Request $request, Discussion $discussion, Task $task): JsonResponse
    {
        $user = $request->user();

        if (!$discussion->canView($user)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $discussion->tasks()->detach($task->id);

        return response()->json([
            'success' => true,
            'message' => 'Task unlinked successfully',
        ]);
    }
}
