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
use App\Services\InboxEmailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
        // Get users from company_user pivot table (includes invited members from other companies)
        $users = User::query()
            ->join('company_user', 'users.id', '=', 'company_user.user_id')
            ->where('company_user.company_id', $user->company_id)
            ->where('users.status', User::STATUS_ACTIVE)
            ->select('users.*', 'company_user.role as company_role')
            ->orderBy('users.name')
            ->get();
        $tags = Tag::where('company_id', $user->company_id)->get();

        $viewMode = $request->get('view', session('task_view_mode', 'table'));
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

        // Check for workspace by ID or UUID
        $selectedWorkspace = $request->get('workspace_id');
        if (!$selectedWorkspace && $request->get('workspace')) {
            // If workspace UUID is provided, find the ID
            $workspace = Workspace::where('uuid', $request->get('workspace'))->first();
            $selectedWorkspace = $workspace?->id;
        }

        // Get users only from selected workspace (if any)
        $users = collect();
        if ($selectedWorkspace) {
            $workspace = Workspace::find($selectedWorkspace);
            if ($workspace) {
                $users = $workspace->members()->where('users.status', User::STATUS_ACTIVE)->orderBy('users.name')->get();
            }
        }

        $tags = Tag::where('company_id', $user->company_id)->get();
        $taskTypes = TaskType::cases();
        $priorities = TaskPriority::cases();

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

    /**
     * Get workspace members for task assignment.
     */
    public function getWorkspaceMembers(Request $request): \Illuminate\Http\JsonResponse
    {
        $workspaceId = $request->get('workspace_id');

        if (!$workspaceId) {
            return response()->json(['users' => []]);
        }

        $workspace = Workspace::find($workspaceId);

        if (!$workspace) {
            return response()->json(['users' => []]);
        }

        // Check if user has access to this workspace
        $user = auth()->user();
        if (!$workspace->hasMember($user) && $workspace->company_id !== $user->company_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $members = $workspace->members()
            ->where('users.status', User::STATUS_ACTIVE)
            ->orderBy('users.name')
            ->get()
            ->map(function ($member) {
                return [
                    'id' => $member->id,
                    'name' => $member->name,
                    'full_name' => $member->full_name,
                    'email' => $member->email,
                    'avatar_url' => $member->avatar_url,
                    'initials' => $member->initials,
                ];
            });

        return response()->json(['users' => $members]);
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
            'workspace.workflow.statuses',
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
            'department',
            'workspacePriority',
        ]);
        $user = auth()->user();
        // Get statuses only from the task's workspace workflow
        $allStatuses = $task->workspace->workflow?->statuses ?? collect();

        // Filter statuses based on allowed transitions from current status
        // Get the current status from the workflow statuses to ensure we have fresh allowed_transitions
        $currentStatus = $task->status_id ? $allStatuses->firstWhere('id', $task->status_id) : null;
        if ($currentStatus && $currentStatus->allowed_transitions !== null) {
            // If rules are defined, only show allowed transitions + current status
            $allowedIds = $currentStatus->allowed_transitions;
            $statuses = $allStatuses->filter(function ($status) use ($allowedIds, $currentStatus) {
                return $status->id === $currentStatus->id || in_array($status->id, $allowedIds);
            })->values();
        } else {
            // No rules defined, show all statuses
            $statuses = $allStatuses;
        }

        // Get only workspace members for task assignment
        $users = $task->workspace ? $task->workspace->members()->where('users.status', User::STATUS_ACTIVE)->orderBy('users.name')->get() : collect();
        $tags = Tag::where('company_id', $user->company_id)->get();
        $priorities = TaskPriority::cases();

        // Get departments and workspace priorities for inbox workspaces
        $departments = collect();
        $workspacePriorities = collect();
        if ($task->workspace->type->value === 'inbox') {
            $departments = $task->workspace->departments()->ordered()->get();
            $workspacePriorities = $task->workspace->priorities()->ordered()->get();
        }

        return view('task::show', compact('task', 'statuses', 'users', 'tags', 'priorities', 'departments', 'workspacePriorities'));
    }

    public function edit(Task $task): View
    {
        $this->authorize('update', $task);

        $user = auth()->user();

        // Eager load workspace workflow and statuses
        $task->load(['workspace.workflow.statuses', 'status']);

        $workspaces = Workspace::forUser($user)->get();
        // Get statuses only from the task's workspace workflow
        $allStatuses = $task->workspace->workflow?->statuses ?? collect();

        // Filter statuses based on allowed transitions from current status
        // Get the current status from the workflow statuses to ensure we have fresh allowed_transitions
        $currentStatus = $task->status_id ? $allStatuses->firstWhere('id', $task->status_id) : null;
        if ($currentStatus && $currentStatus->allowed_transitions !== null) {
            // If rules are defined, only show allowed transitions + current status
            $allowedIds = $currentStatus->allowed_transitions;
            $statuses = $allStatuses->filter(function ($status) use ($allowedIds, $currentStatus) {
                return $status->id === $currentStatus->id || in_array($status->id, $allowedIds);
            })->values();
        } else {
            // No rules defined, show all statuses
            $statuses = $allStatuses;
        }

        // Get only workspace members for task assignment
        $users = $task->workspace ? $task->workspace->members()->where('users.status', User::STATUS_ACTIVE)->orderBy('users.name')->get() : collect();
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

    public function hold(Request $request, Task $task): RedirectResponse
    {
        $user = auth()->user();

        if (!$task->canManageHold($user)) {
            return back()->with('error', 'You do not have permission to put this task on hold.');
        }

        if ($task->isOnHold()) {
            return back()->with('error', 'This task is already on hold.');
        }

        $request->validate([
            'hold_reason' => 'required|string|max:1000',
            'notify_users' => 'nullable|array',
            'notify_users.*' => 'exists:users,id',
        ]);

        $this->taskService->putOnHold(
            $task,
            $user,
            $request->input('hold_reason'),
            $request->input('notify_users', [])
        );

        return back()->with('success', "Task {$task->task_number} has been put on hold.");
    }

    public function resume(Request $request, Task $task): RedirectResponse
    {
        $user = auth()->user();

        if (!$task->canManageHold($user)) {
            return back()->with('error', 'You do not have permission to resume this task.');
        }

        if (!$task->isOnHold()) {
            return back()->with('error', 'This task is not on hold.');
        }

        $request->validate([
            'notify_users' => 'nullable|array',
            'notify_users.*' => 'exists:users,id',
        ]);

        $this->taskService->resumeTask(
            $task,
            $user,
            $request->input('notify_users', [])
        );

        return back()->with('success', "Task {$task->task_number} has been resumed.");
    }

    public function updateStatus(Request $request, Task $task): RedirectResponse
    {
        $user = auth()->user();

        if (!$task->canChangeStatus($user)) {
            return back()->with('error', 'You do not have permission to change the status.');
        }

        $request->validate(['status_id' => 'required|exists:workflow_statuses,id']);

        $newStatusId = (int) $request->input('status_id');

        // Validate status transition is allowed
        $currentStatus = $task->status;
        if ($currentStatus && $currentStatus->allowed_transitions !== null) {
            // If staying on the same status, allow it
            if ($currentStatus->id !== $newStatusId && !$currentStatus->canTransitionTo($newStatusId)) {
                return back()->with('error', 'This status transition is not allowed.');
            }
        }

        $this->taskService->changeStatus($task, $newStatusId, $user);

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

        $oldPriority = $task->priority;
        $newPriority = $request->input('priority');

        $task->update(['priority' => $newPriority]);

        // Log activity
        TaskActivity::log(
            $task,
            $user,
            ActivityType::PRIORITY_CHANGED,
            ['label' => $oldPriority?->label() ?? 'None'],
            ['label' => $newPriority ? \App\Modules\Task\Enums\TaskPriority::from($newPriority)->label() : 'None']
        );

        return back()->with('success', 'Priority updated successfully.');
    }

    public function updateDueDate(Request $request, Task $task): RedirectResponse
    {
        $user = auth()->user();

        if (!$task->canEdit($user)) {
            return back()->with('error', 'You do not have permission to change the due date.');
        }

        $request->validate(['due_date' => 'nullable|date']);

        $oldDueDate = $task->due_date;
        $newDueDate = $request->input('due_date');

        $task->update(['due_date' => $newDueDate]);

        // Log activity
        TaskActivity::log(
            $task,
            $user,
            ActivityType::DUE_DATE_CHANGED,
            ['date' => $oldDueDate?->format('M d, Y')],
            ['date' => $newDueDate ? \Carbon\Carbon::parse($newDueDate)->format('M d, Y') : null]
        );

        return back()->with('success', 'Due date updated successfully.');
    }

    public function updateType(Request $request, Task $task): RedirectResponse
    {
        $user = auth()->user();

        if (!$task->canEdit($user)) {
            return back()->with('error', 'You do not have permission to change the type.');
        }

        $request->validate(['type' => 'nullable|array']);

        $oldTypes = $task->type ?? ['task'];
        $newTypes = $request->input('type', []);
        if (empty($newTypes)) {
            $newTypes = ['task'];
        }

        $task->update(['type' => $newTypes]);

        // Log activity
        TaskActivity::log(
            $task,
            $user,
            ActivityType::TYPE_CHANGED,
            ['types' => $oldTypes],
            ['types' => $newTypes]
        );

        return back()->with('success', 'Type updated successfully.');
    }

    /**
     * Update task department (inline edit for inbox workspaces).
     * Applies ticket rules for the department (assignee, etc.)
     */
    public function updateDepartment(Request $request, Task $task): RedirectResponse
    {
        $user = auth()->user();

        if (!$task->canEdit($user)) {
            return back()->with('error', 'You do not have permission to change the department.');
        }

        // Only allow department updates for inbox workspaces
        if ($task->workspace->type->value !== 'inbox') {
            return back()->with('error', 'Department can only be set for inbox workspaces.');
        }

        $request->validate([
            'department_id' => 'nullable|exists:workspace_departments,id',
        ]);

        // Store old department name for email notification
        $oldDepartment = $task->department;
        $oldDepartmentName = $oldDepartment?->name;

        // Verify the department belongs to this workspace
        $departmentId = $request->input('department_id');
        $updateData = ['department_id' => $departmentId ?: null];
        $appliedRules = [];

        if ($departmentId) {
            $department = $task->workspace->departments()->find($departmentId);
            if (!$department) {
                return back()->with('error', 'Invalid department selected.');
            }

            // Auto-advance status from "Unassigned" (open type) to next status when department is assigned
            $currentStatus = $task->status;
            if ($currentStatus && $currentStatus->type === \App\Models\WorkflowStatus::TYPE_OPEN) {
                // Find the next status in the workflow (next active status by sort_order)
                $nextStatus = \App\Models\WorkflowStatus::where('workflow_id', $currentStatus->workflow_id)
                    ->where('is_active', true)
                    ->where('sort_order', '>', $currentStatus->sort_order)
                    ->orderBy('sort_order')
                    ->first();

                // Only auto-advance if transition is allowed
                if ($nextStatus && $currentStatus->canTransitionTo($nextStatus->id)) {
                    $updateData['status_id'] = $nextStatus->id;
                    $appliedRules[] = 'status changed to ' . $nextStatus->name;
                }
            }

            // Find and apply ticket rule for this department
            $ticketRule = $task->workspace->ticketRules()
                ->where('department_id', $departmentId)
                ->where('is_active', true)
                ->first();

            if ($ticketRule) {
                // Apply primary assignee from ticket rule
                if ($ticketRule->assigned_user_id) {
                    // Check if assignee is out of office, use backup if available
                    $assignee = $ticketRule->assignedUser;
                    if ($assignee && method_exists($assignee, 'isOutOfOffice') && $assignee->isOutOfOffice() && $ticketRule->backup_user_id) {
                        $updateData['assignee_id'] = $ticketRule->backup_user_id;
                        $appliedRules[] = 'backup assignee (primary is out of office)';
                    } else {
                        $updateData['assignee_id'] = $ticketRule->assigned_user_id;
                        $appliedRules[] = 'primary assignee';
                    }
                }
            }

            // Find and apply SLA rule for this department
            $slaRule = $task->workspace->slaRules()
                ->where('department_id', $departmentId)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->first();

            if ($slaRule) {
                // Apply priority from SLA rule if set
                if ($slaRule->priority_id) {
                    $updateData['workspace_priority_id'] = $slaRule->priority_id;
                    $priority = $slaRule->priority;
                    $appliedRules[] = 'priority (' . ($priority?->name ?? 'set') . ')';
                }

                // Apply assignee from SLA rule if set (overrides ticket rule assignee)
                if ($slaRule->assigned_user_id) {
                    $updateData['assignee_id'] = $slaRule->assigned_user_id;
                    // Remove duplicate if ticket rule also set assignee
                    $appliedRules = array_filter($appliedRules, fn($r) => !str_contains($r, 'assignee'));
                    $appliedRules[] = 'SLA assignee';
                }

                // Calculate due date based on resolution hours
                if ($slaRule->resolution_hours) {
                    $updateData['due_date'] = now()->addHours($slaRule->resolution_hours);
                    $appliedRules[] = 'due date (' . $slaRule->getFormattedResolutionTime() . ')';
                }
            }
        }

        $task->update($updateData);

        // Send department changed email to client (only if department actually changed)
        $task->load(['department', 'workspace', 'creator']);
        $newDepartmentName = $task->department?->name;

        if ($oldDepartmentName !== $newDepartmentName && $newDepartmentName) {
            Log::info('Sending department changed email', [
                'task_id' => $task->id,
                'old_department' => $oldDepartmentName,
                'new_department' => $newDepartmentName,
            ]);
            $emailService = app(InboxEmailService::class);
            $emailService->sendDepartmentChangedEmail($task, $oldDepartmentName, $newDepartmentName);
        }

        // Log activity for department change
        if ($oldDepartmentName !== $newDepartmentName) {
            TaskActivity::log(
                $task,
                $user,
                ActivityType::DEPARTMENT_CHANGED,
                ['name' => $oldDepartmentName],
                ['name' => $newDepartmentName]
            );
        }

        $message = 'Department updated successfully.';
        if (!empty($appliedRules)) {
            $message .= ' Applied: ' . implode(', ', $appliedRules) . '.';
        }

        return back()->with('success', $message);
    }

    /**
     * Update task workspace priority (inline edit for inbox workspaces).
     */
    public function updateWorkspacePriority(Request $request, Task $task): RedirectResponse
    {
        $user = auth()->user();

        if (!$task->canEdit($user)) {
            return back()->with('error', 'You do not have permission to change the priority.');
        }

        // Only allow workspace priority updates for inbox workspaces
        if ($task->workspace->type->value !== 'inbox') {
            return back()->with('error', 'Workspace priority can only be set for inbox workspaces.');
        }

        $request->validate([
            'workspace_priority_id' => 'nullable|exists:workspace_priorities,id',
        ]);

        // Store old priority for activity log
        $oldPriority = $task->workspacePriority;
        $oldPriorityName = $oldPriority?->name;

        // Verify the priority belongs to this workspace
        $priorityId = $request->input('workspace_priority_id');
        $newPriority = null;
        if ($priorityId) {
            $newPriority = $task->workspace->priorities()->find($priorityId);
            if (!$newPriority) {
                return back()->with('error', 'Invalid priority selected.');
            }
        }

        $task->update(['workspace_priority_id' => $priorityId ?: null]);

        // Log activity
        $newPriorityName = $newPriority?->name;
        if ($oldPriorityName !== $newPriorityName) {
            TaskActivity::log(
                $task,
                $user,
                ActivityType::WORKSPACE_PRIORITY_CHANGED,
                ['name' => $oldPriorityName],
                ['name' => $newPriorityName]
            );
        }

        return back()->with('success', 'Priority updated successfully.');
    }

    /**
     * Update task progress (0-100%).
     */
    public function updateProgress(Request $request, Task $task): \Illuminate\Http\JsonResponse
    {
        $user = auth()->user();

        if (!$task->canEdit($user)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to update progress.',
            ], 403);
        }

        $request->validate([
            'progress' => 'required|integer|min:0|max:100',
        ]);

        $oldProgress = $task->progress;
        $newProgress = (int) $request->input('progress');

        $task->update(['progress' => $newProgress]);

        // Log activity
        TaskActivity::log(
            $task,
            $user,
            ActivityType::PROGRESS_UPDATED,
            ['progress' => $oldProgress],
            ['progress' => $newProgress]
        );

        return response()->json([
            'success' => true,
            'message' => 'Progress updated successfully.',
            'progress' => $newProgress,
        ]);
    }

    /**
     * Store a new subtask via AJAX.
     */
    public function storeSubtask(Request $request, Task $task): \Illuminate\Http\JsonResponse
    {
        $user = auth()->user();

        // Check if user can create subtasks for this task
        if (!$task->canEdit($user)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to create subtasks for this task.',
            ], 403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assignee_id' => 'nullable|exists:users,id',
            'priority' => 'nullable|string|in:lowest,low,medium,high,highest',
        ]);

        try {
            // Get the default status from the parent task's workspace workflow
            $defaultStatus = $task->workspace->workflow?->statuses()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->first();

            // Create the subtask using the same workspace and workflow as parent
            $subtask = $this->taskService->createTask([
                'title' => $request->input('title'),
                'description' => $request->input('description'),
                'workspace_id' => $task->workspace_id,
                'parent_task_id' => $task->id,
                'type' => ['subtask'],
                'status_id' => $defaultStatus?->id,
                'assignee_id' => $request->input('assignee_id'),
                'priority' => $request->input('priority', 'medium'),
            ], $user);

            // Load the status for the response
            $subtask->load('status');

            return response()->json([
                'success' => true,
                'message' => 'Subtask created successfully.',
                'subtask' => [
                    'id' => $subtask->id,
                    'uuid' => $subtask->uuid,
                    'task_number' => $subtask->task_number,
                    'title' => $subtask->title,
                    'status' => $subtask->status ? [
                        'id' => $subtask->status->id,
                        'name' => $subtask->status->name,
                        'background_color' => $subtask->status->background_color,
                    ] : null,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create subtask', [
                'parent_task_id' => $task->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create subtask. Please try again.',
            ], 500);
        }
    }
}
