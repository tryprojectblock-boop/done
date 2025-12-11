<?php

declare(strict_types=1);

namespace App\Modules\Task\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WorkflowStatus;
use App\Modules\Core\Contracts\FileUploadInterface;
use App\Modules\Task\Contracts\TaskServiceInterface;
use App\Modules\Task\Enums\ActivityType;
use App\Modules\Task\Enums\TaskPriority;
use App\Modules\Task\Enums\TaskType;
use App\Modules\Task\Http\Requests\StoreTaskRequest;
use App\Modules\Task\Http\Requests\UpdateTaskRequest;
use App\Modules\Task\Models\Tag;
use App\Modules\Task\Models\Task;
use App\Modules\Task\Models\TaskActivity;
use App\Modules\Task\Models\TaskAttachment;
use App\Modules\Workspace\Models\Workspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function __construct(
        private readonly TaskServiceInterface $taskService,
        private readonly FileUploadInterface $fileUploadService
    ) {}

    public function index(Request $request): View|\Illuminate\Http\JsonResponse
    {
        $user = auth()->user();

        $filters = $request->only([
            'workspace_id', 'status_id', 'priority', 'type', 'assignee_id',
            'created_by', 'tag_id', 'is_closed', 'due_date_from', 'due_date_to',
            'search', 'sort', 'direction', 'parent_tasks_only'
        ]);

        // Default to showing open tasks
        if (!$request->has('is_closed')) {
            $filters['is_closed'] = false;
        }

        $tasks = $this->taskService->getTasksForUser($user, $filters, 10);

        // Get filter options - workspaces where user is a member
        $workspaces = Workspace::forUser($user)->get();
        // Get unique statuses by name to avoid duplicates in filter dropdown
        $statuses = WorkflowStatus::whereHas('workflow', fn ($q) => $q->where('company_id', $user->company_id))
            ->get()
            ->unique('name')
            ->values();
        $users = User::where('company_id', $user->company_id)->get();
        $tags = Tag::where('company_id', $user->company_id)->get();

        $viewMode = $request->get('view', session('task_view_mode', 'card'));
        session(['task_view_mode' => $viewMode]);

        // Handle AJAX request for real-time search
        if ($request->ajax() || $request->has('ajax')) {
            $html = view('task::partials.' . ($viewMode === 'card' ? 'task-cards' : 'task-table'), compact('tasks'))->render();

            return response()->json([
                'html' => $html,
                'total' => $tasks->total(),
                'pagination' => $tasks->hasPages() ? $tasks->withQueryString()->links()->render() : '',
            ]);
        }

        return view('task::index', compact(
            'tasks',
            'workspaces',
            'statuses',
            'users',
            'tags',
            'filters',
            'viewMode'
        ));
    }

    public function create(Request $request): View
    {
        $user = auth()->user();

        $workspaces = Workspace::forUser($user)->with('workflow.statuses')->latest()->get();
        $users = User::where('company_id', $user->company_id)->get();
        $tags = Tag::where('company_id', $user->company_id)->get();
        $taskTypes = TaskType::cases();
        $priorities = TaskPriority::cases();

        // Check for workspace by ID or UUID
        $selectedWorkspace = $request->get('workspace_id');
        if (!$selectedWorkspace && $request->get('workspace')) {
            // If workspace UUID is provided, find the ID
            $workspace = Workspace::where('uuid', $request->get('workspace'))->first();
            $selectedWorkspace = $workspace?->id;
        }

        // Check for milestone by ID
        $selectedMilestone = $request->get('milestone');

        $parentTask = $request->get('parent_task_id') ? Task::find($request->get('parent_task_id')) : null;

        return view('task::create', compact(
            'workspaces',
            'users',
            'tags',
            'taskTypes',
            'priorities',
            'selectedWorkspace',
            'selectedMilestone',
            'parentTask'
        ));
    }

    public function store(StoreTaskRequest $request): RedirectResponse
    {
        $user = auth()->user();

        $task = $this->taskService->createTask(
            $request->validated(),
            $user
        );

        // Handle file uploads
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $result = $this->fileUploadService->upload(
                    $file,
                    "tasks/{$task->id}/attachments",
                    [
                        'user_id' => $user->id,
                        'context' => 'task_attachment',
                        'tenant_id' => $user->company_id,
                    ]
                );

                if ($result->isSuccess()) {
                    $attachment = TaskAttachment::create([
                        'task_id' => $task->id,
                        'uploaded_by' => $user->id,
                        'original_name' => $result->originalName,
                        'file_path' => $result->path,
                        'file_type' => pathinfo($result->originalName, PATHINFO_EXTENSION),
                        'mime_type' => $result->mimeType,
                        'file_size' => $result->size,
                        'disk' => $result->disk,
                    ]);

                    TaskActivity::log(
                        $task,
                        $user,
                        ActivityType::ATTACHMENT_ADDED,
                        null,
                        ['name' => $attachment->original_name]
                    );
                }
            }
        }

        $action = $request->input('action', 'create');

        if ($action === 'create_and_add_more') {
            return redirect()
                ->route('tasks.create', ['workspace_id' => $task->workspace_id])
                ->with('success', "Task {$task->task_number} created successfully.");
        }

        if ($action === 'create_and_copy') {
            return redirect()
                ->route('tasks.create', [
                    'workspace_id' => $task->workspace_id,
                    'copy_from' => $task->uuid,
                ])
                ->with('success', "Task {$task->task_number} created. Create another from template.");
        }

        return redirect()
            ->route('tasks.show', $task)
            ->with('success', "Task {$task->task_number} created successfully.");
    }

    public function show(Task $task): View
    {
        $this->authorize('view', $task);

        $task->load([
            'workspace',
            'status',
            'assignee',
            'creator',
            'closedBy',
            'parentTask',
            'subtasks.status',
            'subtasks.assignee',
            'tags',
            'watchers',
            'attachments.uploader',
            'comments' => fn ($q) => $q->with(['user', 'replies.user'])->whereNull('parent_id'),
            'activities.user',
        ]);

        $user = auth()->user();
        // Get statuses only from the task's workspace workflow
        $statuses = $task->workspace->workflow?->statuses ?? collect();
        $users = User::where('company_id', $user->company_id)->get();
        $tags = Tag::where('company_id', $user->company_id)->get();
        $priorities = TaskPriority::cases();

        return view('task::show', compact('task', 'statuses', 'users', 'tags', 'priorities'));
    }

    public function edit(Task $task): View
    {
        $this->authorize('update', $task);

        $user = auth()->user();

        $workspaces = Workspace::forUser($user)->get();
        // Get statuses only from the task's workspace workflow
        $statuses = $task->workspace->workflow?->statuses ?? collect();
        $users = User::where('company_id', $user->company_id)->get();
        $tags = Tag::where('company_id', $user->company_id)->get();
        $taskTypes = TaskType::cases();
        $priorities = TaskPriority::cases();

        return view('task::edit', compact(
            'task',
            'workspaces',
            'statuses',
            'users',
            'tags',
            'taskTypes',
            'priorities'
        ));
    }

    public function update(UpdateTaskRequest $request, Task $task): RedirectResponse
    {
        $this->authorize('update', $task);

        $this->taskService->updateTask(
            $task,
            $request->validated(),
            auth()->user()
        );

        return redirect()
            ->route('tasks.show', $task)
            ->with('success', 'Task updated successfully.');
    }

    public function destroy(Task $task): RedirectResponse
    {
        $this->authorize('delete', $task);

        $taskNumber = $task->task_number;
        $this->taskService->deleteTask($task, auth()->user());

        return redirect()
            ->route('tasks.index')
            ->with('success', "Task {$taskNumber} deleted successfully.");
    }

    public function close(Task $task): RedirectResponse
    {
        $user = auth()->user();

        if (!$task->canClose($user)) {
            return back()->with('error', 'You do not have permission to close this task.');
        }

        $this->taskService->closeTask($task, $user);

        return back()->with('success', "Task {$task->task_number} closed successfully.");
    }

    public function reopen(Task $task): RedirectResponse
    {
        $user = auth()->user();

        if (!$task->canClose($user)) {
            return back()->with('error', 'You do not have permission to reopen this task.');
        }

        $this->taskService->reopenTask($task, $user);

        return back()->with('success', "Task {$task->task_number} reopened successfully.");
    }

    public function updateStatus(Request $request, Task $task): RedirectResponse
    {
        $user = auth()->user();

        if (!$task->canChangeStatus($user)) {
            return back()->with('error', 'You do not have permission to change the status.');
        }

        $request->validate(['status_id' => 'required|exists:workflow_statuses,id']);

        $this->taskService->changeStatus($task, (int) $request->input('status_id'), $user);

        return back()->with('success', 'Status updated successfully.');
    }

    public function updateAssignee(Request $request, Task $task): RedirectResponse
    {
        $user = auth()->user();

        if (!$task->canEdit($user)) {
            return back()->with('error', 'You do not have permission to change the assignee.');
        }

        $request->validate(['assignee_id' => 'nullable|exists:users,id']);

        $assigneeId = $request->input('assignee_id');
        $this->taskService->changeAssignee($task, $assigneeId ? (int) $assigneeId : null, $user);

        return back()->with('success', 'Assignee updated successfully.');
    }

    public function updatePriority(Request $request, Task $task): RedirectResponse
    {
        $user = auth()->user();

        if (!$task->canEdit($user)) {
            return back()->with('error', 'You do not have permission to change the priority.');
        }

        $request->validate(['priority' => 'nullable|string|in:lowest,low,medium,high,highest']);

        $task->update(['priority' => $request->input('priority')]);

        return back()->with('success', 'Priority updated successfully.');
    }

    public function updateDueDate(Request $request, Task $task): RedirectResponse
    {
        $user = auth()->user();

        if (!$task->canEdit($user)) {
            return back()->with('error', 'You do not have permission to change the due date.');
        }

        $request->validate(['due_date' => 'nullable|date']);

        $task->update(['due_date' => $request->input('due_date')]);

        return back()->with('success', 'Due date updated successfully.');
    }

    public function updateType(Request $request, Task $task): RedirectResponse
    {
        $user = auth()->user();

        if (!$task->canEdit($user)) {
            return back()->with('error', 'You do not have permission to change the type.');
        }

        $request->validate(['type' => 'nullable|array']);

        $types = $request->input('type', []);
        if (empty($types)) {
            $types = ['task'];
        }

        $task->update(['type' => $types]);

        return back()->with('success', 'Type updated successfully.');
    }
}
