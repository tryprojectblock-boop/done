<?php

declare(strict_types=1);

namespace App\Modules\Workspace\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Mail\GuestInvitationMail;
use App\Models\User;
use App\Models\Workflow;
use App\Services\PlanLimitService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Modules\Workspace\Contracts\WorkspaceServiceInterface;
use App\Modules\Workspace\DTOs\CreateWorkspaceDTO;
use App\Modules\Workspace\Enums\WorkspaceRole;
use App\Modules\Workspace\Enums\WorkspaceType;
use App\Modules\Workspace\Exceptions\WorkspaceException;
use App\Modules\Workspace\Models\Workspace;
use App\Modules\Workspace\Models\WorkspaceDepartment;
use App\Modules\Workspace\Models\WorkspaceHoliday;
use App\Modules\Workspace\Models\WorkspaceInboxSetting;
use App\Modules\Workspace\Models\WorkspacePriority;
use App\Modules\Workspace\Models\WorkspaceSlaRule;
use App\Modules\Workspace\Models\WorkspaceSlaSetting;
use App\Modules\Workspace\Models\WorkspaceTicketForm;
use App\Modules\Workspace\Models\WorkspaceTicketFormField;
use App\Modules\Workspace\Models\WorkspaceTicketRule;
use App\Modules\Workspace\Models\WorkspaceWorkingHour;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WorkspaceController extends Controller
{
    public function __construct(
        private WorkspaceServiceInterface $workspaceService,
        private PlanLimitService $planLimitService,
    ) {}

    /**
     * Display a listing of workspaces.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        // Get all workspaces from user's own company (active + archived)
        $workspaces = $this->workspaceService->getForUser($user);

        // Get workspaces from other companies where user is a member
        $otherCompanyWorkspaces = $this->workspaceService->getOtherCompanyWorkspaces($user);

        // Get workspaces where user is added as a guest
        $guestWorkspaces = $user->guestWorkspaces()
            ->with(['owner.company', 'members'])
            ->withCount(['tasks', 'discussions'])
            ->get();

        return view('workspace::index', [
            'workspaces' => $workspaces,
            'otherCompanyWorkspaces' => $otherCompanyWorkspaces,
            'guestWorkspaces' => $guestWorkspaces,
        ]);
    }

    /**
     * Show the form for creating a new workspace.
     */
    public function create(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        // Guest-only users cannot create workspaces
        if ($user->role === User::ROLE_GUEST && !$user->company_id) {
            return redirect()->route('workspace.index')
                ->with('upgrade_required', 'Please upgrade your account to create workspaces.');
        }

        // Check workspace limit
        $company = $user->company;
        if ($company && !$this->planLimitService->canCreateWorkspace($company)) {
            $limits = $this->planLimitService->getLimits($company);
            $planName = $company->plan?->name ?? 'Free';

            return redirect()->route('workspace.index')
                ->with('error', "You have reached the workspace limit ({$limits['workspaces']}) for your {$planName} plan. Please upgrade to create more workspaces.");
        }

        // Get team members for invitation (excluding current user) from company_user pivot table
        $teamMembers = User::query()
            ->join('company_user', 'users.id', '=', 'company_user.user_id')
            ->where('company_user.company_id', $request->user()->company_id)
            ->where('users.id', '!=', $request->user()->id)
            ->where('users.status', User::STATUS_ACTIVE)
            ->select('users.*', 'company_user.role as company_role')
            ->orderBy('users.name')
            ->get();

        // Get all available workflows for this company (built-in and user-created)
        $workflows = Workflow::forCompany($request->user()->company_id)
            ->active()
            ->with('statuses')
            ->orderByRaw('is_default DESC, name ASC')
            ->get();

        // Get existing guests (users with is_guest = true)
        $existingGuests = User::where('is_guest', true)
            ->where('status', User::STATUS_ACTIVE)
            ->orderBy('first_name')
            ->get();

        return view('workspace::create', [
            'workspaceRoles' => WorkspaceRole::cases(),
            'teamMembers' => $teamMembers,
            'workflows' => $workflows,
            'existingGuests' => $existingGuests,
        ]);
    }

    /**
     * Store a newly created workspace.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        // Guest-only users cannot create workspaces
        if ($user->role === User::ROLE_GUEST && !$user->company_id) {
            return redirect()->route('workspace.index')
                ->with('upgrade_required', 'Please upgrade your account to create workspaces.');
        }

        // Base validation rules
        $rules = [
            'type' => ['required', 'string', 'in:classic,product,inbox'],
        ];

        // Conditional validation based on workspace type
        if ($request->input('type') === 'inbox') {
            // Inbox workspace validation
            $rules = array_merge($rules, [
                'inbox_name' => ['required', 'string', 'max:100'],
                'inbox_owner_id' => ['required', 'exists:users,id'],
                'inbox_workflow_id' => ['required', 'exists:workflows,id'],
                'inbound_email_prefix' => ['required', 'string', 'max:50', 'regex:/^[a-z0-9\-]+$/'],
            ]);
        } else {
            // Classic/Product workspace validation
            $rules = array_merge($rules, [
                'name' => ['required', 'string', 'max:100'],
                'workflow_id' => ['required', 'exists:workflows,id'],
                'description' => ['nullable', 'string', 'max:500'],
                'start_date' => ['nullable', 'date'],
                'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
                'members' => ['nullable', 'array'],
                'members.*.user_id' => ['required_with:members', 'exists:users,id'],
                'members.*.role' => ['required_with:members', 'string', 'in:admin,member,reviewer'],
                'guest_ids' => ['nullable', 'array'],
                'guest_ids.*' => ['exists:users,id'],
                'guest_emails' => ['nullable', 'array'],
                'guest_emails.*' => ['email'],
            ]);
        }

        $validated = $request->validate($rules);

        // Build DTO based on workspace type
        if ($validated['type'] === 'inbox') {
            $inboundEmail = $validated['inbound_email_prefix'] . '@inbound.findmypool.net';

            $dto = new CreateWorkspaceDTO(
                name: $validated['inbox_name'],
                type: WorkspaceType::from($validated['type']),
                ownerId: (int) $validated['inbox_owner_id'],
                description: null,
                workflowId: (int) $validated['inbox_workflow_id'],
                settings: [], // Settings now stored in separate tables
            );
        } else {
            $dto = new CreateWorkspaceDTO(
                name: $validated['name'],
                type: WorkspaceType::from($validated['type']),
                ownerId: $request->user()->id,
                description: $validated['description'] ?? null,
                workflowId: (int) $validated['workflow_id'],
                settings: [
                    'start_date' => $validated['start_date'] ?? null,
                    'end_date' => $validated['end_date'] ?? null,
                ],
            );
        }

        try {
            $workspace = $this->workspaceService->create($dto);

            // Initialize inbox defaults (priorities, working hours, settings)
            if ($validated['type'] === 'inbox') {
                $workspace->initializeInboxDefaults($inboundEmail, $validated['inbound_email_prefix']);
            }
        } catch (WorkspaceException $e) {
            return redirect()->route('workspace.index')
                ->with('error', $e->getMessage());
        }

        // For non-inbox workspaces, add members and guests
        if ($validated['type'] !== 'inbox') {
            // Add invited members
            if (!empty($validated['members'])) {
                foreach ($validated['members'] as $member) {
                    $memberUser = User::find($member['user_id']);
                    if ($memberUser) {
                        $role = WorkspaceRole::from($member['role']);
                        $workspace->addMember($memberUser, $role, $request->user());
                    }
                }
            }

            // Add existing guests by ID
            if (!empty($validated['guest_ids'])) {
                foreach ($validated['guest_ids'] as $guestId) {
                    $guestUser = User::find($guestId);
                    if ($guestUser && $guestUser->is_guest) {
                        $workspace->addGuest($guestUser, $request->user());
                    }
                }
            }

            // Add new guests by email - creates User if not exists
            if (!empty($validated['guest_emails'])) {
                foreach ($validated['guest_emails'] as $guestEmail) {
                    $guestUser = User::where('email', strtolower($guestEmail))->first();

                    if (!$guestUser) {
                        // Create new user as guest
                        $invitationToken = Str::random(64);

                        $guestUser = User::create([
                            'email' => strtolower($guestEmail),
                            'name' => explode('@', $guestEmail)[0],
                            'first_name' => explode('@', $guestEmail)[0],
                            'password' => Hash::make(Str::random(32)), // Temporary password
                            'role' => User::ROLE_GUEST,
                            'status' => User::STATUS_INVITED,
                            'is_guest' => true,
                            'invitation_token' => $invitationToken,
                            'invitation_expires_at' => now()->addDays(7),
                            'invited_by' => $request->user()->id,
                            'invited_at' => now(),
                        ]);

                        // Send invitation email to guest
                        Mail::to($guestUser->email)->send(new GuestInvitationMail(
                            $guestUser,
                            $request->user(),
                            $invitationToken
                        ));
                    } else {
                        // Existing user - mark as guest if not already
                        if (!$guestUser->is_guest) {
                            $guestUser->update(['is_guest' => true]);
                        }
                    }

                    // Add to workspace as guest
                    $workspace->addGuest($guestUser, $request->user());
                }
            }
        }

        return redirect()->route('workspace.show', $workspace)
            ->with('success', 'Workspace created successfully.');
    }

    /**
     * Display the specified workspace.
     */
    public function show(Request $request, Workspace $workspace): View
    {
        $this->authorizeWorkspaceAccess($request, $workspace);

        // Get task filters
        $taskFilters = $request->only(['status_id', 'assignee_id', 'priority', 'search', 'task_filter']);

        // Load tasks for this workspace (filtered by visibility for private tasks)
        $tasksQuery = \App\Modules\Task\Models\Task::where('workspace_id', $workspace->id)
            ->visibleTo($request->user())
            ->with(['assignee', 'creator', 'status']);

        // Apply task type filter (All/Overdue/Closed)
        // A task is considered "closed" if closed_at is set OR status type is 'closed'
        $taskFilter = $taskFilters['task_filter'] ?? '';
        if ($taskFilter === 'overdue') {
            // Only overdue tasks (not closed, due date passed)
            $tasksQuery->whereNull('closed_at')
                ->whereDoesntHave('status', fn($q) => $q->where('type', 'closed'))
                ->whereNotNull('due_date')
                ->where('due_date', '<', now());
        } elseif ($taskFilter === 'closed') {
            // Only closed tasks (closed_at set OR status type is 'closed')
            $tasksQuery->where(function ($q) {
                $q->whereNotNull('closed_at')
                  ->orWhereHas('status', fn($sq) => $sq->where('type', 'closed'));
            });
        } else {
            // "All Tasks" excludes closed tasks by default
            $tasksQuery->whereNull('closed_at')
                ->whereDoesntHave('status', fn($q) => $q->where('type', 'closed'));
        }

        // Apply filters
        if (!empty($taskFilters['status_id'])) {
            $tasksQuery->where('status_id', $taskFilters['status_id']);
        }
        if (!empty($taskFilters['assignee_id'])) {
            $tasksQuery->where('assignee_id', $taskFilters['assignee_id']);
        }
        if (!empty($taskFilters['priority'])) {
            $tasksQuery->where('priority', $taskFilters['priority']);
        }

        // Apply search filter (search in title, status name, priority, assignee name)
        if (!empty($taskFilters['search'])) {
            $search = $taskFilters['search'];
            $tasksQuery->where(function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhereHas('status', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('assignee', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                          ->orWhere('first_name', 'like', "%{$search}%")
                          ->orWhere('last_name', 'like', "%{$search}%");
                    })
                    ->orWhere('priority', 'like', "%{$search}%");
            });
        }

        $tasks = $tasksQuery->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        // Get task stats (for the stats bar - these are totals, not filtered)
        // A task is "closed" if closed_at is set OR status type is 'closed'
        $allWorkspaceTasks = \App\Modules\Task\Models\Task::where('workspace_id', $workspace->id)
            ->visibleTo($request->user());
        $taskStats = [
            'total' => (clone $allWorkspaceTasks)->count(),
            'open' => (clone $allWorkspaceTasks)
                ->whereNull('closed_at')
                ->whereDoesntHave('status', fn($q) => $q->where('type', 'closed'))
                ->count(),
            'closed' => (clone $allWorkspaceTasks)
                ->where(function ($q) {
                    $q->whereNotNull('closed_at')
                      ->orWhereHas('status', fn($sq) => $sq->where('type', 'closed'));
                })
                ->count(),
            'overdue' => (clone $allWorkspaceTasks)
                ->whereNull('closed_at')
                ->whereDoesntHave('status', fn($q) => $q->where('type', 'closed'))
                ->whereNotNull('due_date')
                ->where('due_date', '<', now())
                ->count(),
        ];

        // Load discussions for this workspace
        $discussions = \App\Modules\Discussion\Models\Discussion::where('workspace_id', $workspace->id)
            ->with(['creator', 'participants'])
            ->orderBy('last_activity_at', 'desc')
            ->limit(20)
            ->get();

        // Load files (drive attachments) for this workspace
        $files = \App\Modules\Drive\Models\DriveAttachment::where('workspace_id', $workspace->id)
            ->with(['uploader', 'tags'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('workspace::show', [
            'workspace' => $workspace->load(['members', 'owner', 'invitations', 'workflow.statuses', 'guests']),
            'tasks' => $tasks,
            'taskFilters' => $taskFilters,
            'taskStats' => $taskStats,
            'discussions' => $discussions,
            'files' => $files,
        ]);
    }

    /**
     * Display workspace as guest (limited view).
     */
    public function guestView(Request $request, Workspace $workspace): View
    {
        $user = $request->user();

        // Check if user has guest access to this workspace
        if (!$workspace->hasGuest($user)) {
            abort(403, 'You do not have guest access to this workspace.');
        }

        return view('workspace::guest-view', [
            'workspace' => $workspace->load(['members', 'owner', 'workflow']),
        ]);
    }

    /**
     * Show workspace settings.
     * Only accessible by workspace owner or company admin.
     */
    public function settings(Request $request, Workspace $workspace): View
    {
        $this->authorizeWorkspaceAccess($request, $workspace);

        $user = $request->user();
        if (!$workspace->isOwner($user) && !$user->isAdminOrHigher()) {
            abort(403, 'You do not have permission to access workspace settings.');
        }

        return view('workspace::settings', [
            'workspace' => $workspace->load(['members', 'owner']),
        ]);
    }

    /**
     * Update the specified workspace.
     */
    public function update(Request $request, Workspace $workspace): RedirectResponse
    {
        $this->authorizeWorkspaceAccess($request, $workspace);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $this->workspaceService->update($workspace, $validated);

        return back()->with('success', 'Workspace updated successfully.');
    }

    /**
     * Remove the specified workspace.
     */
    public function destroy(Request $request, Workspace $workspace): RedirectResponse
    {
        $this->authorizeWorkspaceAccess($request, $workspace);

        if (!$workspace->isOwner($request->user())) {
            return back()->with('error', 'Only workspace owner can delete the workspace.');
        }

        $this->workspaceService->delete($workspace);

        return redirect()->route('workspace.index')
            ->with('success', 'Workspace deleted successfully.');
    }

    /**
     * Archive the specified workspace.
     */
    public function archive(Request $request, Workspace $workspace): RedirectResponse
    {
        $this->authorizeWorkspaceAccess($request, $workspace);

        $this->workspaceService->archive($workspace);

        return back()->with('success', 'Workspace archived successfully.');
    }

    /**
     * Restore the specified workspace.
     */
    public function restore(Request $request, Workspace $workspace): RedirectResponse
    {
        $this->authorizeWorkspaceAccess($request, $workspace);

        $this->workspaceService->restore($workspace);

        return back()->with('success', 'Workspace restored successfully.');
    }

    /**
     * Update workspace logo.
     */
    public function updateLogo(Request $request, Workspace $workspace): RedirectResponse
    {
        $this->authorizeWorkspaceAccess($request, $workspace);

        $validated = $request->validate([
            'logo' => ['required', 'image', 'max:2048'],
        ]);

        $this->workspaceService->updateLogo($workspace, $validated['logo']);

        return back()->with('success', 'Logo updated successfully.');
    }

    /**
     * Update workspace modules.
     */
    public function updateModules(Request $request, Workspace $workspace): RedirectResponse
    {
        $this->authorizeWorkspaceAccess($request, $workspace);

        $validated = $request->validate([
            'modules' => ['required', 'array'],
            'modules.*' => ['string'],
        ]);

        // Disable all modules first, then enable selected ones
        foreach ($workspace->enabled_modules as $module) {
            $this->workspaceService->disableModules($workspace, [$module]);
        }

        $this->workspaceService->enableModules($workspace, $validated['modules']);

        return back()->with('success', 'Modules updated successfully.');
    }

    /**
     * Update working hours for inbox workspace.
     */
    public function updateWorkingHours(Request $request, Workspace $workspace): RedirectResponse
    {
        $this->authorizeWorkspaceAccess($request, $workspace);

        // Check if this is an inbox workspace
        if (!$workspace->isInbox()) {
            return back()->with('error', 'This feature is only available for inbox workspaces.');
        }

        $validated = $request->validate([
            'hour_format' => ['required', 'in:12,24'],
            'date_format' => ['required', 'string', 'max:50'],
            'timezone' => ['nullable', 'string', 'max:100'],
            'days' => ['nullable', 'array'],
            'days.*.enabled' => ['nullable'],
            'days.*.start' => ['nullable', 'date_format:H:i'],
            'days.*.end' => ['nullable', 'date_format:H:i'],
            'days.*.hours' => ['nullable', 'numeric', 'min:0', 'max:24'],
        ]);

        // Update inbox settings
        $workspace->inboxSettings()->updateOrCreate(
            ['workspace_id' => $workspace->id],
            [
                'hour_format' => $validated['hour_format'],
                'date_format' => $validated['date_format'],
                'timezone' => $validated['timezone'] ?? config('app.timezone'),
                'working_hours_configured_at' => now(),
            ]
        );

        // Update working hours for each day
        foreach (WorkspaceWorkingHour::DAYS as $day) {
            $dayData = $validated['days'][$day] ?? [];

            $workspace->workingHours()->updateOrCreate(
                ['workspace_id' => $workspace->id, 'day' => $day],
                [
                    'is_enabled' => !empty($dayData['enabled']),
                    'start_time' => ($dayData['start'] ?? '09:00') . ':00',
                    'end_time' => ($dayData['end'] ?? '17:00') . ':00',
                    'total_hours' => (float) ($dayData['hours'] ?? 8),
                ]
            );
        }

        return redirect()->route('workspace.show', $workspace)->with('success', 'Working hours updated successfully.');
    }

    /**
     * Verify inbound email setup for inbox workspace.
     */
    public function verifyInboundEmail(Request $request, Workspace $workspace): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $this->authorizeWorkspaceAccess($request, $workspace);

        $isAjax = $request->ajax() || $request->wantsJson();

        // Check if this is an inbox workspace
        if (!$workspace->isInbox()) {
            $error = 'This feature is only available for inbox workspaces.';
            return $isAjax
                ? response()->json(['success' => false, 'error' => $error], 400)
                : back()->with('error', $error);
        }

        // Validate from_email
        $validated = $request->validate([
            'from_email' => ['required', 'email', 'max:255'],
        ]);

        // Check if this from_email is already used by another workspace
        $existingMapping = \App\Modules\Workspace\Models\WorkspaceInboxSetting::where('from_email', $validated['from_email'])
            ->where('workspace_id', '!=', $workspace->id)
            ->first();

        if ($existingMapping) {
            $error = 'This email address is already mapped to another workspace.';
            return $isAjax
                ? response()->json(['success' => false, 'errors' => ['from_email' => [$error]]], 422)
                : back()->withErrors(['from_email' => $error])->withInput();
        }

        // Get or create inbox settings
        $inboxSettings = $workspace->inboxSettings;
        if (!$inboxSettings) {
            $error = 'Inbox settings not found for this workspace.';
            return $isAjax
                ? response()->json(['success' => false, 'error' => $error], 400)
                : back()->with('error', $error);
        }

        // Check if inbound email is configured
        if (empty($inboxSettings->inbound_email)) {
            $error = 'Inbound email is not configured for this workspace.';
            return $isAjax
                ? response()->json(['success' => false, 'error' => $error], 400)
                : back()->with('error', $error);
        }

        // Save from_email (don't mark as verified yet)
        $inboxSettings->update([
            'from_email' => $validated['from_email'],
            'email_verified' => false,
            'email_verified_at' => null,
        ]);

        // Send verification email to the from_email address
        try {
            \Illuminate\Support\Facades\Mail::raw(
                "This is a verification email for your inbox workspace.\n\n" .
                "Workspace: {$workspace->name}\n" .
                "Inbound Email: {$inboxSettings->inbound_email}\n\n" .
                "To complete verification, please forward this email (or any email) to:\n" .
                "{$inboxSettings->inbound_email}\n\n" .
                "Once we receive an email at the inbound address, your inbox will be verified automatically.\n\n" .
                "Thank you!",
                function ($message) use ($validated, $workspace) {
                    $message->to($validated['from_email'])
                        ->subject("Verify your inbox: {$workspace->name}");
                }
            );

            $message = 'Verification email sent to ' . $validated['from_email'] . '! Please forward it to ' . $inboxSettings->inbound_email . ' to complete verification.';
            return $isAjax
                ? response()->json(['success' => true, 'message' => $message])
                : back()->with('success', $message);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send verification email', [
                'workspace_id' => $workspace->id,
                'from_email' => $validated['from_email'],
                'error' => $e->getMessage(),
            ]);

            $message = 'Email configuration saved, but we could not send the verification email. Please send any email to ' . $inboxSettings->inbound_email . ' to verify.';
            return $isAjax
                ? response()->json(['success' => true, 'message' => $message])
                : back()->with('warning', $message);
        }
    }

    /**
     * Add a department to inbox workspace.
     */
    public function addDepartment(Request $request, Workspace $workspace): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $this->authorizeWorkspaceAccess($request, $workspace);

        $isAjax = $request->ajax() || $request->wantsJson();

        if (!$workspace->isInbox()) {
            $error = 'This feature is only available for inbox workspaces.';
            return $isAjax
                ? response()->json(['success' => false, 'message' => $error])
                : back()->with('error', $error);
        }

        $action = $request->input('action', 'add');

        if ($action === 'add') {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:100'],
                'public_view' => ['required', 'in:0,1'],
                'incharge_id' => ['nullable', 'exists:users,id'],
            ]);

            // Verify incharge is a workspace member
            if (!empty($validated['incharge_id'])) {
                $incharge = User::find($validated['incharge_id']);
                if (!$workspace->hasMember($incharge)) {
                    $error = 'Selected incharge must be a workspace member.';
                    return $isAjax
                        ? response()->json(['success' => false, 'message' => $error])
                        : back()->with('error', $error);
                }
            }

            // Check for duplicate department name
            if ($workspace->departments()->whereRaw('LOWER(name) = ?', [strtolower($validated['name'])])->exists()) {
                $error = 'A department with this name already exists.';
                return $isAjax
                    ? response()->json(['success' => false, 'message' => $error])
                    : back()->with('error', $error);
            }

            // Get max sort order
            $maxOrder = $workspace->departments()->max('sort_order') ?? 0;

            // Create department
            $department = $workspace->departments()->create([
                'name' => $validated['name'],
                'is_public' => (bool) $validated['public_view'],
                'incharge_id' => $validated['incharge_id'] ? (int) $validated['incharge_id'] : null,
                'sort_order' => $maxOrder + 1,
            ]);

            // Load incharge for response
            $department->load('incharge');

            // Mark departments as configured
            $workspace->inboxSettings()->update(['departments_configured_at' => now()]);

            if ($isAjax) {
                return response()->json([
                    'success' => true,
                    'message' => 'Department added successfully.',
                    'department' => [
                        'id' => $department->id,
                        'name' => $department->name,
                        'is_public' => $department->is_public,
                        'incharge_id' => $department->incharge_id,
                        'incharge_name' => $department->incharge?->name,
                        'incharge_avatar' => $department->incharge?->avatar_url,
                    ],
                ]);
            }

            return back()->with('success', 'Department added successfully.');

        } elseif ($action === 'edit') {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:100'],
                'public_view' => ['required', 'in:0,1'],
                'incharge_id' => ['nullable', 'exists:users,id'],
                'edit_id' => ['required', 'integer', 'min:1'],
            ]);

            $departmentId = (int) $validated['edit_id'];
            $department = $workspace->departments()->find($departmentId);

            if (!$department) {
                $error = 'Department not found.';
                return $isAjax
                    ? response()->json(['success' => false, 'message' => $error])
                    : back()->with('error', $error);
            }

            // Verify incharge is a workspace member
            if (!empty($validated['incharge_id'])) {
                $incharge = User::find($validated['incharge_id']);
                if (!$workspace->hasMember($incharge)) {
                    $error = 'Selected incharge must be a workspace member.';
                    return $isAjax
                        ? response()->json(['success' => false, 'message' => $error])
                        : back()->with('error', $error);
                }
            }

            // Check for duplicate department name (excluding current)
            if ($workspace->departments()
                ->whereRaw('LOWER(name) = ?', [strtolower($validated['name'])])
                ->where('id', '!=', $departmentId)
                ->exists()) {
                $error = 'A department with this name already exists.';
                return $isAjax
                    ? response()->json(['success' => false, 'message' => $error])
                    : back()->with('error', $error);
            }

            $department->update([
                'name' => $validated['name'],
                'is_public' => (bool) $validated['public_view'],
                'incharge_id' => $validated['incharge_id'] ? (int) $validated['incharge_id'] : null,
            ]);

            // Reload incharge for response
            $department->load('incharge');

            if ($isAjax) {
                return response()->json([
                    'success' => true,
                    'message' => 'Department updated successfully.',
                    'department' => [
                        'id' => $department->id,
                        'name' => $department->name,
                        'is_public' => $department->is_public,
                        'incharge_id' => $department->incharge_id,
                        'incharge_name' => $department->incharge?->name,
                        'incharge_avatar' => $department->incharge?->avatar_url,
                    ],
                ]);
            }

            return back()->with('success', 'Department updated successfully.');

        } elseif ($action === 'delete') {
            $validated = $request->validate([
                'delete_id' => ['required', 'integer', 'min:1'],
            ]);

            $departmentId = (int) $validated['delete_id'];
            $department = $workspace->departments()->find($departmentId);

            if (!$department) {
                $error = 'Department not found.';
                return $isAjax
                    ? response()->json(['success' => false, 'message' => $error])
                    : back()->with('error', $error);
            }

            $department->delete();

            if ($isAjax) {
                return response()->json([
                    'success' => true,
                    'message' => 'Department deleted successfully.',
                ]);
            }

            return back()->with('success', 'Department deleted successfully.');
        }

        $error = 'Invalid action.';
        return $isAjax
            ? response()->json(['success' => false, 'message' => $error])
            : back()->with('error', $error);
    }

    /**
     * Update a department in inbox workspace.
     */
    public function updateDepartment(Request $request, Workspace $workspace, int $departmentId): RedirectResponse
    {
        $this->authorizeWorkspaceAccess($request, $workspace);

        if (!$workspace->isInbox()) {
            return back()->with('error', 'This feature is only available for inbox workspaces.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'public_view' => ['required', 'in:0,1'],
            'incharge_id' => ['nullable', 'exists:users,id'],
        ]);

        // Verify incharge is a workspace member
        if (!empty($validated['incharge_id'])) {
            $incharge = User::find($validated['incharge_id']);
            if (!$workspace->hasMember($incharge)) {
                return back()->with('error', 'Selected incharge must be a workspace member.');
            }
        }

        // Find department
        $department = $workspace->departments()->find($departmentId);
        if (!$department) {
            return back()->with('error', 'Department not found.');
        }

        // Check for duplicate department name (excluding current)
        if ($workspace->departments()
            ->where('name', $validated['name'])
            ->where('id', '!=', $departmentId)
            ->exists()) {
            return back()->with('error', 'A department with this name already exists.');
        }

        $department->update([
            'name' => $validated['name'],
            'is_public' => (bool) $validated['public_view'],
            'incharge_id' => $validated['incharge_id'] ? (int) $validated['incharge_id'] : null,
        ]);

        return back()->with('success', 'Department updated successfully.');
    }

    /**
     * Delete a department from inbox workspace.
     */
    public function deleteDepartment(Request $request, Workspace $workspace, int $departmentId): RedirectResponse
    {
        $this->authorizeWorkspaceAccess($request, $workspace);

        if (!$workspace->isInbox()) {
            return back()->with('error', 'This feature is only available for inbox workspaces.');
        }

        $department = $workspace->departments()->find($departmentId);
        if (!$department) {
            return back()->with('error', 'Department not found.');
        }

        $department->delete();

        return back()->with('success', 'Department deleted successfully.');
    }

    /**
     * Save priorities for inbox workspace.
     */
    public function savePriorities(Request $request, Workspace $workspace): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $this->authorizeWorkspaceAccess($request, $workspace);

        $isAjax = $request->ajax() || $request->wantsJson();

        if (!$workspace->isInbox()) {
            $error = 'This feature is only available for inbox workspaces.';
            return $isAjax
                ? response()->json(['success' => false, 'message' => $error])
                : back()->with('error', $error);
        }

        $action = $request->input('action', 'add');

        // Initialize defaults action - creates default priorities if none exist
        if ($action === 'init_defaults') {
            if ($workspace->priorities()->count() === 0) {
                WorkspacePriority::createDefaults($workspace);
            }

            $priorities = $workspace->priorities()->orderBy('sort_order')->get();

            return response()->json([
                'success' => true,
                'message' => 'Defaults initialized.',
                'priorities' => $priorities->map(fn($p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'color' => $p->color,
                ])->toArray(),
            ]);
        }

        if ($action === 'add') {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:50'],
                'color' => ['required', 'string', 'max:20'],
            ]);

            // Check for duplicate name
            if ($workspace->priorities()->whereRaw('LOWER(name) = ?', [strtolower($validated['name'])])->exists()) {
                $error = 'A priority with this name already exists.';
                return $isAjax
                    ? response()->json(['success' => false, 'message' => $error])
                    : back()->with('error', $error);
            }

            $maxOrder = $workspace->priorities()->max('sort_order') ?? 0;

            $priority = $workspace->priorities()->create([
                'name' => $validated['name'],
                'color' => $validated['color'],
                'sort_order' => $maxOrder + 1,
            ]);

            if ($isAjax) {
                return response()->json([
                    'success' => true,
                    'message' => 'Priority added successfully.',
                    'priority' => [
                        'id' => $priority->id,
                        'name' => $priority->name,
                        'color' => $priority->color,
                    ],
                ]);
            }

            $message = 'Priority added successfully.';

        } elseif ($action === 'edit') {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:50'],
                'color' => ['required', 'string', 'max:20'],
                'edit_id' => ['required', 'integer', 'min:1'],
            ]);

            $priorityId = (int) $validated['edit_id'];
            $priority = $workspace->priorities()->find($priorityId);

            if (!$priority) {
                $error = 'Priority not found.';
                return $isAjax
                    ? response()->json(['success' => false, 'message' => $error])
                    : back()->with('error', $error);
            }

            // Check for duplicate name (excluding current)
            if ($workspace->priorities()
                ->whereRaw('LOWER(name) = ?', [strtolower($validated['name'])])
                ->where('id', '!=', $priorityId)
                ->exists()) {
                $error = 'A priority with this name already exists.';
                return $isAjax
                    ? response()->json(['success' => false, 'message' => $error])
                    : back()->with('error', $error);
            }

            $priority->update([
                'name' => $validated['name'],
                'color' => $validated['color'],
            ]);

            $message = 'Priority updated successfully.';

        } elseif ($action === 'delete') {
            $validated = $request->validate([
                'delete_id' => ['required', 'integer', 'min:1'],
            ]);

            $priorityId = (int) $validated['delete_id'];
            $priority = $workspace->priorities()->find($priorityId);

            if (!$priority) {
                $error = 'Priority not found.';
                return $isAjax
                    ? response()->json(['success' => false, 'message' => $error])
                    : back()->with('error', $error);
            }

            if ($workspace->priorities()->count() <= 1) {
                $error = 'Cannot delete the last priority. At least one priority is required.';
                return $isAjax
                    ? response()->json(['success' => false, 'message' => $error])
                    : back()->with('error', $error);
            }

            $priority->delete();

            $message = 'Priority deleted successfully.';

        } elseif ($action === 'reorder') {
            $validated = $request->validate([
                'priorities' => ['required', 'string'],
            ]);

            $newPriorities = json_decode($validated['priorities'], true);

            if (!is_array($newPriorities)) {
                $error = 'Invalid priorities data.';
                return $isAjax
                    ? response()->json(['success' => false, 'message' => $error])
                    : back()->with('error', $error);
            }

            // Update sort order for each priority
            foreach ($newPriorities as $index => $priorityData) {
                if (isset($priorityData['id'])) {
                    $workspace->priorities()
                        ->where('id', $priorityData['id'])
                        ->update(['sort_order' => $index + 1]);
                }
            }

            $message = 'Priorities reordered successfully.';

        } elseif ($action === 'bulk_save') {
            $validated = $request->validate([
                'priorities' => ['required', 'string'],
            ]);

            $prioritiesData = json_decode($validated['priorities'], true);

            if (!is_array($prioritiesData)) {
                $error = 'Invalid priorities data.';
                return $isAjax
                    ? response()->json(['success' => false, 'message' => $error])
                    : back()->with('error', $error);
            }

            // Get existing priority IDs
            $existingIds = $workspace->priorities()->pluck('id')->toArray();
            $submittedIds = [];

            foreach ($prioritiesData as $index => $priorityData) {
                $name = trim($priorityData['name'] ?? '');
                $color = $priorityData['color'] ?? 'blue';
                $sortOrder = $priorityData['sort_order'] ?? $index;

                if (empty($name)) {
                    continue;
                }

                if (!empty($priorityData['id'])) {
                    // Update existing priority
                    $priority = $workspace->priorities()->find($priorityData['id']);
                    if ($priority) {
                        $priority->update([
                            'name' => $name,
                            'color' => $color,
                            'sort_order' => $sortOrder,
                        ]);
                        $submittedIds[] = $priority->id;
                    }
                } else {
                    // Create new priority
                    $priority = $workspace->priorities()->create([
                        'name' => $name,
                        'color' => $color,
                        'sort_order' => $sortOrder,
                    ]);
                    $submittedIds[] = $priority->id;
                }
            }

            // Delete priorities that were not submitted (removed by user)
            $toDelete = array_diff($existingIds, $submittedIds);
            if (!empty($toDelete) && count($submittedIds) > 0) {
                $workspace->priorities()->whereIn('id', $toDelete)->delete();
            }

            $message = 'Priorities saved successfully.';

        } else {
            $error = 'Invalid action.';
            return $isAjax
                ? response()->json(['success' => false, 'message' => $error])
                : back()->with('error', $error);
        }

        // Mark priorities as configured
        $workspace->inboxSettings()->update(['priorities_configured_at' => now()]);

        // Get updated priorities for response
        $priorities = $workspace->priorities()
            ->orderBy('sort_order')
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'color' => $p->color,
                'order' => $p->sort_order,
            ])
            ->toArray();

        return $isAjax
            ? response()->json(['success' => true, 'message' => $message, 'priorities' => $priorities])
            : back()->with('success', $message);
    }

    /**
     * Save holidays for inbox workspace.
     */
    public function saveHolidays(Request $request, Workspace $workspace): \Illuminate\Http\JsonResponse
    {
        $this->authorizeWorkspaceAccess($request, $workspace);

        if (!$workspace->isInbox()) {
            return response()->json(['success' => false, 'message' => 'This feature is only available for inbox workspaces.']);
        }

        $action = $request->input('action', 'add');

        if ($action === 'add') {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:100'],
                'date' => ['required', 'date'],
                'working_hours' => ['required', 'numeric', 'min:0', 'max:24'],
            ]);

            // Check for duplicate date
            if ($workspace->holidays()->where('date', $validated['date'])->exists()) {
                return response()->json(['success' => false, 'message' => 'A holiday already exists for this date.']);
            }

            $holiday = $workspace->holidays()->create([
                'name' => $validated['name'],
                'date' => $validated['date'],
                'working_hours' => $validated['working_hours'],
            ]);

            // Mark holidays as configured
            $workspace->inboxSettings()->update(['holidays_configured_at' => now()]);

            return response()->json([
                'success' => true,
                'message' => 'Holiday added successfully.',
                'holiday' => [
                    'id' => $holiday->id,
                    'name' => $holiday->name,
                    'date' => $holiday->date->format('Y-m-d'),
                    'working_hours' => $holiday->working_hours,
                ],
            ]);

        } elseif ($action === 'edit') {
            $validated = $request->validate([
                'edit_id' => ['required', 'integer'],
                'name' => ['required', 'string', 'max:100'],
                'date' => ['required', 'date'],
                'working_hours' => ['required', 'numeric', 'min:0', 'max:24'],
            ]);

            $holiday = $workspace->holidays()->find($validated['edit_id']);
            if (!$holiday) {
                return response()->json(['success' => false, 'message' => 'Holiday not found.']);
            }

            // Check for duplicate date (excluding current)
            if ($workspace->holidays()->where('date', $validated['date'])->where('id', '!=', $validated['edit_id'])->exists()) {
                return response()->json(['success' => false, 'message' => 'A holiday already exists for this date.']);
            }

            $holiday->update([
                'name' => $validated['name'],
                'date' => $validated['date'],
                'working_hours' => $validated['working_hours'],
            ]);

            return response()->json(['success' => true, 'message' => 'Holiday updated successfully.']);

        } elseif ($action === 'delete') {
            $validated = $request->validate([
                'delete_id' => ['required', 'integer'],
            ]);

            $holiday = $workspace->holidays()->find($validated['delete_id']);
            if (!$holiday) {
                return response()->json(['success' => false, 'message' => 'Holiday not found.']);
            }

            $holiday->delete();

            return response()->json(['success' => true, 'message' => 'Holiday deleted successfully.']);
        }

        return response()->json(['success' => false, 'message' => 'Invalid action.']);
    }

    /**
     * Save SLA settings for inbox workspace.
     */
    public function saveSlaSettings(Request $request, Workspace $workspace): \Illuminate\Http\JsonResponse
    {
        $this->authorizeWorkspaceAccess($request, $workspace);

        if (!$workspace->isInbox()) {
            return response()->json(['success' => false, 'message' => 'This feature is only available for inbox workspaces.']);
        }

        $slaData = $request->input('sla', []);

        foreach ($slaData as $priorityId => $settings) {
            // Verify priority belongs to this workspace
            if (!$workspace->priorities()->where('id', $priorityId)->exists()) {
                continue;
            }

            $workspace->slaSettings()->updateOrCreate(
                ['workspace_id' => $workspace->id, 'priority_id' => $priorityId],
                [
                    'first_reply_days' => (int) ($settings['first_reply_days'] ?? 0),
                    'first_reply_hours' => (int) ($settings['first_reply_hours'] ?? 1),
                    'first_reply_minutes' => (int) ($settings['first_reply_minutes'] ?? 0),
                    'next_reply_days' => (int) ($settings['next_reply_days'] ?? 0),
                    'next_reply_hours' => (int) ($settings['next_reply_hours'] ?? 4),
                    'next_reply_minutes' => (int) ($settings['next_reply_minutes'] ?? 0),
                    'resolution_days' => (int) ($settings['resolution_days'] ?? 1),
                    'resolution_hours' => (int) ($settings['resolution_hours'] ?? 0),
                    'resolution_minutes' => (int) ($settings['resolution_minutes'] ?? 0),
                ]
            );
        }

        // Mark SLA settings as configured
        $workspace->inboxSettings()->update(['sla_configured_at' => now()]);

        return response()->json(['success' => true, 'message' => 'SLA settings saved successfully.']);
    }

    /**
     * Save ticket assignment rules for inbox workspace.
     */
    public function saveTicketRules(Request $request, Workspace $workspace): \Illuminate\Http\JsonResponse
    {
        $this->authorizeWorkspaceAccess($request, $workspace);

        if (!$workspace->isInbox()) {
            return response()->json(['success' => false, 'message' => 'This feature is only available for inbox workspaces.']);
        }

        $action = $request->input('action', 'add');

        if ($action === 'add') {
            $validated = $request->validate([
                'department_id' => ['required', 'integer'],
                'assigned_user_id' => ['nullable', 'integer'],
                'backup_user_id' => ['nullable', 'integer'],
            ]);

            // Verify department belongs to this workspace
            if (!$workspace->departments()->where('id', $validated['department_id'])->exists()) {
                return response()->json(['success' => false, 'message' => 'Invalid department.']);
            }

            // Check for duplicate rule for this department
            if ($workspace->ticketRules()->where('department_id', $validated['department_id'])->exists()) {
                return response()->json(['success' => false, 'message' => 'A rule already exists for this department.']);
            }

            $maxOrder = $workspace->ticketRules()->max('sort_order') ?? 0;

            $workspace->ticketRules()->create([
                'department_id' => $validated['department_id'],
                'assigned_user_id' => $validated['assigned_user_id'] ?: null,
                'backup_user_id' => $validated['backup_user_id'] ?: null,
                'sort_order' => $maxOrder + 1,
            ]);

            // Mark ticket rules as configured
            $workspace->inboxSettings()->update(['ticket_rules_configured_at' => now()]);

            return response()->json(['success' => true, 'message' => 'Rule added successfully.']);

        } elseif ($action === 'edit') {
            $validated = $request->validate([
                'edit_id' => ['required', 'integer'],
                'department_id' => ['required', 'integer'],
                'assigned_user_id' => ['nullable', 'integer'],
                'backup_user_id' => ['nullable', 'integer'],
            ]);

            $rule = $workspace->ticketRules()->find($validated['edit_id']);
            if (!$rule) {
                return response()->json(['success' => false, 'message' => 'Rule not found.']);
            }

            // Check for duplicate (excluding current)
            if ($workspace->ticketRules()
                ->where('department_id', $validated['department_id'])
                ->where('id', '!=', $validated['edit_id'])
                ->exists()) {
                return response()->json(['success' => false, 'message' => 'A rule already exists for this department.']);
            }

            $rule->update([
                'department_id' => $validated['department_id'],
                'assigned_user_id' => $validated['assigned_user_id'] ?: null,
                'backup_user_id' => $validated['backup_user_id'] ?: null,
            ]);

            return response()->json(['success' => true, 'message' => 'Rule updated successfully.']);

        } elseif ($action === 'delete') {
            $validated = $request->validate([
                'delete_id' => ['required', 'integer'],
            ]);

            $rule = $workspace->ticketRules()->find($validated['delete_id']);
            if (!$rule) {
                return response()->json(['success' => false, 'message' => 'Rule not found.']);
            }

            $rule->delete();

            return response()->json(['success' => true, 'message' => 'Rule deleted successfully.']);
        }

        return response()->json(['success' => false, 'message' => 'Invalid action.']);
    }

    /**
     * Save SLA rules for inbox workspace.
     */
    public function saveSlaRules(Request $request, Workspace $workspace): \Illuminate\Http\JsonResponse
    {
        $this->authorizeWorkspaceAccess($request, $workspace);

        if (!$workspace->isInbox()) {
            return response()->json(['success' => false, 'message' => 'This feature is only available for inbox workspaces.']);
        }

        $action = $request->input('action', 'add');

        if ($action === 'add') {
            $validated = $request->validate([
                'department_id' => ['required', 'integer'],
                'priority_id' => ['required', 'integer'],
                'assigned_user_id' => ['nullable', 'integer'],
                'resolution_hours' => ['required', 'integer', 'min:1', 'max:720'],
                'escalation_notes' => ['nullable', 'string', 'max:1000'],
            ]);

            // Verify department belongs to this workspace
            if (!$workspace->departments()->where('id', $validated['department_id'])->exists()) {
                return response()->json(['success' => false, 'message' => 'Invalid department.']);
            }

            // Verify priority belongs to this workspace
            if (!$workspace->priorities()->where('id', $validated['priority_id'])->exists()) {
                return response()->json(['success' => false, 'message' => 'Invalid priority.']);
            }

            // Check if department+priority combination already has an SLA rule
            if ($workspace->slaRules()
                ->where('department_id', $validated['department_id'])
                ->where('priority_id', $validated['priority_id'])
                ->exists()) {
                return response()->json(['success' => false, 'message' => 'This department already has an SLA rule for this priority. Please edit the existing rule.']);
            }

            $maxOrder = $workspace->slaRules()->max('sort_order') ?? 0;

            $workspace->slaRules()->create([
                'department_id' => $validated['department_id'],
                'priority_id' => $validated['priority_id'],
                'assigned_user_id' => $validated['assigned_user_id'] ?: null,
                'resolution_hours' => $validated['resolution_hours'],
                'escalation_notes' => $validated['escalation_notes'] ?? null,
                'sort_order' => $maxOrder + 1,
            ]);

            // Mark SLA rules as configured
            $workspace->inboxSettings()->update(['sla_rules_configured_at' => now()]);

            return response()->json(['success' => true, 'message' => 'SLA rule added successfully.']);

        } elseif ($action === 'edit') {
            $validated = $request->validate([
                'edit_id' => ['required', 'integer'],
                'department_id' => ['required', 'integer'],
                'priority_id' => ['required', 'integer'],
                'assigned_user_id' => ['nullable', 'integer'],
                'resolution_hours' => ['required', 'integer', 'min:1', 'max:720'],
                'escalation_notes' => ['nullable', 'string', 'max:1000'],
            ]);

            $rule = $workspace->slaRules()->find($validated['edit_id']);
            if (!$rule) {
                return response()->json(['success' => false, 'message' => 'Rule not found.']);
            }

            // Check if new department+priority combination already has a rule (excluding current rule)
            $duplicateExists = $workspace->slaRules()
                ->where('department_id', $validated['department_id'])
                ->where('priority_id', $validated['priority_id'])
                ->where('id', '!=', $rule->id)
                ->exists();

            if ($duplicateExists) {
                return response()->json(['success' => false, 'message' => 'This department already has an SLA rule for this priority.']);
            }

            $rule->update([
                'department_id' => $validated['department_id'],
                'priority_id' => $validated['priority_id'],
                'assigned_user_id' => $validated['assigned_user_id'] ?: null,
                'resolution_hours' => $validated['resolution_hours'],
                'escalation_notes' => $validated['escalation_notes'] ?? null,
            ]);

            return response()->json(['success' => true, 'message' => 'SLA rule updated successfully.']);

        } elseif ($action === 'delete') {
            $validated = $request->validate([
                'delete_id' => ['required', 'integer'],
            ]);

            $rule = $workspace->slaRules()->find($validated['delete_id']);
            if (!$rule) {
                return response()->json(['success' => false, 'message' => 'Rule not found.']);
            }

            $rule->delete();

            return response()->json(['success' => true, 'message' => 'SLA rule deleted successfully.']);
        }

        return response()->json(['success' => false, 'message' => 'Invalid action.']);
    }

    /**
     * Display Idle Ticket Settings page.
     */
    public function idleSettingsPage(Request $request, Workspace $workspace): View
    {
        $this->authorizeWorkspaceAccess($request, $workspace);

        if (!$workspace->isInbox()) {
            abort(404, 'This feature is only available for inbox workspaces.');
        }

        // Get statuses from the workspace's workflow
        $statuses = collect();
        if ($workspace->workflow_id) {
            $statuses = \App\Models\WorkflowStatus::where('workflow_id', $workspace->workflow_id)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get();
        }

        return view('workspace::inbox.idle-settings', [
            'workspace' => $workspace,
            'inboxSettings' => $workspace->inboxSettings,
            'statuses' => $statuses,
        ]);
    }

    /**
     * Save Idle Ticket Settings.
     */
    public function saveIdleSettings(Request $request, Workspace $workspace): \Illuminate\Http\RedirectResponse
    {
        $this->authorizeWorkspaceAccess($request, $workspace);

        if (!$workspace->isInbox()) {
            return redirect()->back()->with('error', 'This feature is only available for inbox workspaces.');
        }

        $validated = $request->validate([
            'idle_ticket_hours' => ['nullable', 'integer', 'min:1', 'max:8760'],
            'idle_ticket_reply_status_id' => ['nullable', 'integer', 'exists:workflow_statuses,id'],
        ]);

        $inboxSettings = $workspace->inboxSettings;
        if (!$inboxSettings) {
            $inboxSettings = $workspace->inboxSettings()->create([]);
        }

        $inboxSettings->update([
            'idle_ticket_hours' => $validated['idle_ticket_hours'] ?: null,
            'idle_ticket_reply_status_id' => $validated['idle_ticket_reply_status_id'] ?: null,
            'idle_rules_configured_at' => now(),
        ]);

        return redirect()->route('workspace.show', $workspace)->with('success', 'Idle ticket settings saved successfully.');
    }

    /**
     * Display Email Templates page.
     */
    public function emailTemplatesPage(Request $request, Workspace $workspace): View
    {
        $this->authorizeWorkspaceAccess($request, $workspace);

        if (!$workspace->isInbox()) {
            abort(404, 'This feature is only available for inbox workspaces.');
        }

        // Initialize default templates (creates any missing template types)
        \App\Modules\Workspace\Models\WorkspaceEmailTemplate::createDefaults($workspace);

        $templates = $workspace->emailTemplates()->orderBy('type')->get()->groupBy('type');

        return view('workspace::inbox.email-templates', [
            'workspace' => $workspace,
            'templates' => $templates,
            'templateTypes' => \App\Modules\Workspace\Models\WorkspaceEmailTemplate::TYPES,
            'templateCategories' => \App\Modules\Workspace\Models\WorkspaceEmailTemplate::CATEGORIES,
            'typesByCategory' => \App\Modules\Workspace\Models\WorkspaceEmailTemplate::getTypesByCategory(),
            'placeholders' => \App\Modules\Workspace\Models\WorkspaceEmailTemplate::PLACEHOLDERS,
        ]);
    }

    /**
     * Save Email Template.
     */
    public function saveEmailTemplate(Request $request, Workspace $workspace): \Illuminate\Http\JsonResponse
    {
        $this->authorizeWorkspaceAccess($request, $workspace);

        if (!$workspace->isInbox()) {
            return response()->json(['success' => false, 'message' => 'This feature is only available for inbox workspaces.']);
        }

        $action = $request->input('action', 'edit');

        if ($action === 'edit') {
            $validated = $request->validate([
                'template_id' => ['required', 'integer'],
                'name' => ['required', 'string', 'max:255'],
                'subject' => ['required', 'string', 'max:255'],
                'body' => ['required', 'string', 'max:10000'],
                'is_active' => ['nullable', 'boolean'],
            ]);

            $template = $workspace->emailTemplates()->find($validated['template_id']);
            if (!$template) {
                return response()->json(['success' => false, 'message' => 'Template not found.']);
            }

            $template->update([
                'name' => $validated['name'],
                'subject' => $validated['subject'],
                'body' => $validated['body'],
                'is_active' => $request->boolean('is_active', true),
            ]);

            // Mark email templates as configured
            $workspace->inboxSettings()->update(['email_templates_configured_at' => now()]);

            return response()->json(['success' => true, 'message' => 'Template updated successfully.']);

        } elseif ($action === 'toggle') {
            $validated = $request->validate([
                'template_id' => ['required', 'integer'],
            ]);

            $template = $workspace->emailTemplates()->find($validated['template_id']);
            if (!$template) {
                return response()->json(['success' => false, 'message' => 'Template not found.']);
            }

            $template->update(['is_active' => !$template->is_active]);

            return response()->json([
                'success' => true,
                'message' => $template->is_active ? 'Template enabled.' : 'Template disabled.',
                'is_active' => $template->is_active,
            ]);

        } elseif ($action === 'reset') {
            $validated = $request->validate([
                'template_id' => ['required', 'integer'],
            ]);

            $template = $workspace->emailTemplates()->find($validated['template_id']);
            if (!$template) {
                return response()->json(['success' => false, 'message' => 'Template not found.']);
            }

            $defaults = \App\Modules\Workspace\Models\WorkspaceEmailTemplate::getDefaultTemplates();
            if (isset($defaults[$template->type])) {
                $template->update([
                    'name' => $defaults[$template->type]['name'],
                    'subject' => $defaults[$template->type]['subject'],
                    'body' => $defaults[$template->type]['body'],
                ]);
            }

            return response()->json(['success' => true, 'message' => 'Template reset to default.']);
        }

        return response()->json(['success' => false, 'message' => 'Invalid action.']);
    }

    /**
     * Toggle client portal access for inbox workspace.
     */
    public function toggleClientPortal(Request $request, Workspace $workspace): RedirectResponse
    {
        $this->authorizeWorkspaceAccess($request, $workspace);

        if (!$workspace->isInbox()) {
            return back()->with('error', 'This feature is only available for inbox workspaces.');
        }

        $validated = $request->validate([
            'enabled' => ['required', 'in:0,1'],
        ]);

        $inboxSettings = $workspace->inboxSettings;
        if (!$inboxSettings) {
            $inboxSettings = $workspace->inboxSettings()->create([]);
        }

        $enabled = (bool) $validated['enabled'];
        $inboxSettings->update(['client_portal_enabled' => $enabled]);

        $message = $enabled
            ? 'Client portal enabled. Guests can now log in to view their tickets.'
            : 'Client portal disabled. Guests cannot log in.';

        return back()->with('success', $message);
    }

    /**
     * Display Working Hours settings page.
     */
    public function workingHoursPage(Request $request, Workspace $workspace): View
    {
        $this->authorizeWorkspaceAccess($request, $workspace);

        if (!$workspace->isInbox()) {
            abort(404, 'This feature is only available for inbox workspaces.');
        }

        return view('workspace::inbox.working-hours', [
            'workspace' => $workspace,
            'inboxSettings' => $workspace->inboxSettings,
            'workingHours' => $workspace->workingHours()->get()->keyBy('day'),
        ]);
    }

    /**
     * Display Departments settings page.
     */
    public function departmentsPage(Request $request, Workspace $workspace): View
    {
        $this->authorizeWorkspaceAccess($request, $workspace);

        if (!$workspace->isInbox()) {
            abort(404, 'This feature is only available for inbox workspaces.');
        }

        return view('workspace::inbox.departments', [
            'workspace' => $workspace->load('members'),
            'departments' => $workspace->departments()->with('incharge')->orderBy('sort_order')->get(),
        ]);
    }

    /**
     * Display Priorities settings page.
     */
    public function prioritiesPage(Request $request, Workspace $workspace): View
    {
        $this->authorizeWorkspaceAccess($request, $workspace);

        if (!$workspace->isInbox()) {
            abort(404, 'This feature is only available for inbox workspaces.');
        }

        // Initialize defaults if none exist
        if ($workspace->priorities()->count() === 0) {
            WorkspacePriority::createDefaults($workspace);
        }

        return view('workspace::inbox.priorities', [
            'workspace' => $workspace,
            'priorities' => $workspace->priorities()->orderBy('sort_order')->get(),
        ]);
    }

    /**
     * Display Holidays settings page.
     */
    public function holidaysPage(Request $request, Workspace $workspace): View
    {
        $this->authorizeWorkspaceAccess($request, $workspace);

        if (!$workspace->isInbox()) {
            abort(404, 'This feature is only available for inbox workspaces.');
        }

        return view('workspace::inbox.holidays', [
            'workspace' => $workspace,
            'holidays' => $workspace->holidays()->orderBy('date')->get(),
        ]);
    }

    /**
     * Display SLA Settings page.
     */
    public function slaSettingsPage(Request $request, Workspace $workspace): View
    {
        $this->authorizeWorkspaceAccess($request, $workspace);

        if (!$workspace->isInbox()) {
            abort(404, 'This feature is only available for inbox workspaces.');
        }

        // Initialize default priorities if none exist
        if ($workspace->priorities()->count() === 0) {
            WorkspacePriority::createDefaults($workspace);
        }

        return view('workspace::inbox.sla-settings', [
            'workspace' => $workspace,
            'priorities' => $workspace->priorities()->orderBy('sort_order')->get(),
            'slaSettings' => $workspace->slaSettings()->get()->keyBy('priority_id'),
        ]);
    }

    /**
     * Display Ticket Rules settings page.
     */
    public function ticketRulesPage(Request $request, Workspace $workspace): View
    {
        $this->authorizeWorkspaceAccess($request, $workspace);

        if (!$workspace->isInbox()) {
            abort(404, 'This feature is only available for inbox workspaces.');
        }

        return view('workspace::inbox.ticket-rules', [
            'workspace' => $workspace->load('members'),
            'departments' => $workspace->departments()->orderBy('sort_order')->get(),
            'ticketRules' => $workspace->ticketRules()->with(['department', 'assignedUser', 'backupUser'])->orderBy('sort_order')->get(),
        ]);
    }

    /**
     * Display SLA Rules settings page.
     */
    public function slaRulesPage(Request $request, Workspace $workspace): View
    {
        $this->authorizeWorkspaceAccess($request, $workspace);

        if (!$workspace->isInbox()) {
            abort(404, 'This feature is only available for inbox workspaces.');
        }

        // Get priorities with their SLA settings
        $priorities = $workspace->priorities()->orderBy('sort_order')->get();
        $slaSettings = $workspace->slaSettings()->get()->keyBy('priority_id');

        // Calculate resolution hours for each priority from SLA settings
        $prioritySlaHours = [];
        foreach ($priorities as $priority) {
            $sla = $slaSettings->get($priority->id);
            if ($sla) {
                $hours = ($sla->resolution_days * 24) + $sla->resolution_hours + ($sla->resolution_minutes / 60);
                $prioritySlaHours[$priority->id] = round($hours, 1);
            } else {
                $prioritySlaHours[$priority->id] = 24; // Default 24 hours
            }
        }

        return view('workspace::inbox.sla-rules', [
            'workspace' => $workspace->load('members'),
            'departments' => $workspace->departments()->orderBy('sort_order')->get(),
            'priorities' => $priorities,
            'prioritySlaHours' => $prioritySlaHours,
            'slaRules' => $workspace->slaRules()->with(['department', 'priority', 'assignedUser'])->orderBy('sort_order')->get(),
        ]);
    }

    /**
     * Display Ticket Form Builder page.
     */
    public function ticketFormPage(Request $request, Workspace $workspace): View
    {
        $this->authorizeWorkspaceAccess($request, $workspace);

        if (!$workspace->isInbox()) {
            abort(404, 'This feature is only available for inbox workspaces.');
        }

        // Get or create ticket form with defaults
        $ticketForm = $workspace->ticketForm;
        if (!$ticketForm) {
            $ticketForm = $workspace->ticketForm()->create([
                'name' => 'Support Request',
                'description' => 'Submit a support ticket and we\'ll get back to you as soon as possible.',
            ]);
        }

        // Load custom fields
        $ticketForm->load('fields');

        return view('workspace::inbox.ticket-form', [
            'workspace' => $workspace->load(['departments', 'priorities']),
            'ticketForm' => $ticketForm,
        ]);
    }

    /**
     * Save Ticket Form settings.
     */
    public function saveTicketForm(Request $request, Workspace $workspace): RedirectResponse
    {
        $this->authorizeWorkspaceAccess($request, $workspace);

        if (!$workspace->isInbox()) {
            return back()->with('error', 'This feature is only available for inbox workspaces.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'success_message' => ['nullable', 'string', 'max:500'],
            'submit_button_text' => ['nullable', 'string', 'max:50'],
            'is_active' => ['nullable'],
            'show_name' => ['nullable'],
            'name_required' => ['nullable'],
            'show_email' => ['nullable'],
            'email_required' => ['nullable'],
            'show_phone' => ['nullable'],
            'phone_required' => ['nullable'],
            'show_subject' => ['nullable'],
            'subject_required' => ['nullable'],
            'show_description' => ['nullable'],
            'description_required' => ['nullable'],
            'show_department' => ['nullable'],
            'department_required' => ['nullable'],
            'show_priority' => ['nullable'],
            'priority_required' => ['nullable'],
            'show_attachments' => ['nullable'],
            'default_department_id' => ['nullable', 'exists:workspace_departments,id'],
            'default_priority_id' => ['nullable', 'exists:workspace_priorities,id'],
            'logo_url' => ['nullable', 'string', 'max:500'],
            'primary_color' => ['nullable', 'string', 'max:20'],
            'background_color' => ['nullable', 'string', 'max:20'],
            'enable_captcha' => ['nullable'],
            'enable_honeypot' => ['nullable'],
            // Confirmation settings
            'confirmation_type' => ['nullable', 'in:inline,modal,redirect'],
            'confirmation_headline' => ['nullable', 'string', 'max:200'],
            'confirmation_message' => ['nullable', 'string', 'max:1000'],
            'redirect_url' => ['nullable', 'url', 'max:500'],
            // Spam protection
            'enable_rate_limiting' => ['nullable'],
            'rate_limit_per_hour' => ['nullable', 'integer', 'min:1', 'max:100'],
            'block_disposable_emails' => ['nullable'],
            'blocked_emails' => ['nullable', 'string', 'max:5000'],
            'blocked_domains' => ['nullable', 'string', 'max:5000'],
            'blocked_ips' => ['nullable', 'string', 'max:5000'],
        ]);

        // Get or create ticket form
        $ticketForm = $workspace->ticketForm;
        if (!$ticketForm) {
            $ticketForm = new WorkspaceTicketForm();
            $ticketForm->workspace_id = $workspace->id;
        }

        // Update form settings
        $ticketForm->fill([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'success_message' => $validated['success_message'] ?? 'Thank you! Your ticket has been submitted successfully.',
            'submit_button_text' => $validated['submit_button_text'] ?? 'Submit Ticket',
            'is_active' => $request->boolean('is_active', true),
            'show_name' => $request->boolean('show_name', true),
            'name_required' => $request->boolean('name_required', true),
            'show_email' => $request->boolean('show_email', true),
            'email_required' => $request->boolean('email_required', true),
            'show_phone' => $request->boolean('show_phone', false),
            'phone_required' => $request->boolean('phone_required', false),
            'show_subject' => $request->boolean('show_subject', true),
            'subject_required' => $request->boolean('subject_required', true),
            'show_description' => $request->boolean('show_description', true),
            'description_required' => $request->boolean('description_required', true),
            'show_department' => $request->boolean('show_department', false),
            'department_required' => $request->boolean('department_required', false),
            'show_priority' => $request->boolean('show_priority', false),
            'priority_required' => $request->boolean('priority_required', false),
            'show_attachments' => $request->boolean('show_attachments', false),
            'default_department_id' => $validated['default_department_id'] ?? null,
            'default_priority_id' => $validated['default_priority_id'] ?? null,
            'logo_url' => $validated['logo_url'] ?? null,
            'primary_color' => $validated['primary_color'] ?? '#3b82f6',
            'background_color' => $validated['background_color'] ?? '#f8fafc',
            'enable_captcha' => $request->boolean('enable_captcha', false),
            'enable_honeypot' => $request->boolean('enable_honeypot', true),
            // Confirmation settings
            'confirmation_type' => $validated['confirmation_type'] ?? 'inline',
            'confirmation_headline' => $validated['confirmation_headline'] ?? 'Thank You!',
            'confirmation_message' => $validated['confirmation_message'] ?? 'Your ticket has been submitted successfully. We\'ll get back to you soon.',
            'redirect_url' => $validated['redirect_url'] ?? null,
            // Spam protection
            'enable_rate_limiting' => $request->boolean('enable_rate_limiting', true),
            'rate_limit_per_hour' => $validated['rate_limit_per_hour'] ?? 10,
            'block_disposable_emails' => $request->boolean('block_disposable_emails', false),
            'blocked_emails' => $validated['blocked_emails'] ?? null,
            'blocked_domains' => $validated['blocked_domains'] ?? null,
            'blocked_ips' => $validated['blocked_ips'] ?? null,
        ]);

        $ticketForm->save();

        // Mark form as configured
        $workspace->inboxSettings()->update(['form_configured_at' => now()]);

        return redirect()->route('workspace.inbox.ticket-form', $workspace)->with('success', 'Ticket form settings saved successfully.');
    }

    /**
     * Publish (lock) the ticket form.
     */
    public function publishTicketForm(Request $request, Workspace $workspace): RedirectResponse
    {
        $this->authorizeWorkspaceAccess($request, $workspace);

        if (!$workspace->isInbox()) {
            return back()->with('error', 'This feature is only available for inbox workspaces.');
        }

        $ticketForm = $workspace->ticketForm;
        if (!$ticketForm) {
            return back()->with('error', 'Ticket form not found.');
        }

        $ticketForm->update(['published_at' => now()]);

        return redirect()->route('workspace.inbox.ticket-form', $workspace)->with('success', 'Form published successfully. The form is now locked.');
    }

    /**
     * Unpublish (unlock) the ticket form.
     */
    public function unpublishTicketForm(Request $request, Workspace $workspace): RedirectResponse
    {
        $this->authorizeWorkspaceAccess($request, $workspace);

        if (!$workspace->isInbox()) {
            return back()->with('error', 'This feature is only available for inbox workspaces.');
        }

        $ticketForm = $workspace->ticketForm;
        if (!$ticketForm) {
            return back()->with('error', 'Ticket form not found.');
        }

        $ticketForm->update(['published_at' => null]);

        return redirect()->route('workspace.inbox.ticket-form', $workspace)->with('success', 'Form unpublished. You can now edit the form.');
    }

    /**
     * Show create ticket form field page.
     */
    public function createTicketFormField(Request $request, Workspace $workspace): View
    {
        $this->authorizeWorkspaceAccess($request, $workspace);

        if (!$workspace->isInbox()) {
            abort(404, 'This feature is only available for inbox workspaces.');
        }

        return view('workspace::inbox.ticket-form-field', [
            'workspace' => $workspace,
            'fieldTypes' => WorkspaceTicketFormField::getFieldTypes(),
        ]);
    }

    /**
     * Store a new ticket form field.
     */
    public function storeTicketFormField(Request $request, Workspace $workspace): RedirectResponse
    {
        $this->authorizeWorkspaceAccess($request, $workspace);

        if (!$workspace->isInbox()) {
            return back()->with('error', 'This feature is only available for inbox workspaces.');
        }

        $validated = $request->validate([
            'type' => ['required', 'string', 'in:text,textarea,email,phone,date,select,file'],
            'label' => ['required', 'string', 'max:100'],
            'placeholder' => ['nullable', 'string', 'max:200'],
            'help_text' => ['nullable', 'string', 'max:500'],
            'is_required' => ['nullable'],
            'options' => ['nullable', 'array'],
            'options.*' => ['nullable', 'string', 'max:100'],
        ]);

        $ticketForm = $workspace->ticketForm;
        if (!$ticketForm) {
            return back()->with('error', 'Ticket form not found.');
        }

        // Get max sort order
        $maxOrder = $ticketForm->fields()->max('sort_order') ?? 0;

        // Process options for select fields
        $options = null;
        if ($validated['type'] === 'select' && !empty($validated['options'])) {
            $options = array_values(array_filter($validated['options']));
        }

        $ticketForm->fields()->create([
            'type' => $validated['type'],
            'label' => $validated['label'],
            'name' => WorkspaceTicketFormField::generateFieldName($validated['label']),
            'placeholder' => $validated['placeholder'] ?? null,
            'help_text' => $validated['help_text'] ?? null,
            'is_required' => $request->boolean('is_required'),
            'options' => $options,
            'sort_order' => $maxOrder + 1,
        ]);

        return redirect()->route('workspace.inbox.ticket-form', $workspace)->with('success', 'Field added successfully.');
    }

    /**
     * Show edit ticket form field page.
     */
    public function editTicketFormField(Request $request, Workspace $workspace, WorkspaceTicketFormField $field): View
    {
        $this->authorizeWorkspaceAccess($request, $workspace);

        // Verify field belongs to this workspace
        if ($field->form->workspace_id !== $workspace->id) {
            abort(404);
        }

        return view('workspace::inbox.ticket-form-field', [
            'workspace' => $workspace,
            'field' => $field,
            'fieldTypes' => WorkspaceTicketFormField::getFieldTypes(),
        ]);
    }

    /**
     * Update a ticket form field.
     */
    public function updateTicketFormField(Request $request, Workspace $workspace, WorkspaceTicketFormField $field): RedirectResponse
    {
        $this->authorizeWorkspaceAccess($request, $workspace);

        // Verify field belongs to this workspace
        if ($field->form->workspace_id !== $workspace->id) {
            abort(404);
        }

        $validated = $request->validate([
            'type' => ['required', 'string', 'in:text,textarea,email,phone,date,select,file'],
            'label' => ['required', 'string', 'max:100'],
            'placeholder' => ['nullable', 'string', 'max:200'],
            'help_text' => ['nullable', 'string', 'max:500'],
            'is_required' => ['nullable'],
            'options' => ['nullable', 'array'],
            'options.*' => ['nullable', 'string', 'max:100'],
        ]);

        // Process options for select fields
        $options = null;
        if ($validated['type'] === 'select' && !empty($validated['options'])) {
            $options = array_values(array_filter($validated['options']));
        }

        $field->update([
            'type' => $validated['type'],
            'label' => $validated['label'],
            'name' => WorkspaceTicketFormField::generateFieldName($validated['label']),
            'placeholder' => $validated['placeholder'] ?? null,
            'help_text' => $validated['help_text'] ?? null,
            'is_required' => $request->boolean('is_required'),
            'options' => $options,
        ]);

        return redirect()->route('workspace.inbox.ticket-form', $workspace)->with('success', 'Field updated successfully.');
    }

    /**
     * Delete a ticket form field.
     */
    public function deleteTicketFormField(Request $request, Workspace $workspace, WorkspaceTicketFormField $field): \Illuminate\Http\JsonResponse
    {
        $this->authorizeWorkspaceAccess($request, $workspace);

        // Verify field belongs to this workspace
        if ($field->form->workspace_id !== $workspace->id) {
            return response()->json(['error' => 'Not found'], 404);
        }

        $field->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Reorder ticket form fields.
     */
    public function reorderTicketFormFields(Request $request, Workspace $workspace): \Illuminate\Http\JsonResponse
    {
        $this->authorizeWorkspaceAccess($request, $workspace);

        $validated = $request->validate([
            'field_order' => ['required', 'array'],
            'field_order.*' => ['required', 'string'],
        ]);

        $ticketForm = $workspace->ticketForm;
        if (!$ticketForm) {
            return response()->json(['error' => 'Form not found'], 404);
        }

        // Save the field order (includes both standard field keys and custom_X field keys)
        $ticketForm->update(['field_order' => $validated['field_order']]);

        return response()->json(['success' => true]);
    }

    /**
     * Check if user has workspace access.
     */
    protected function authorizeWorkspaceAccess(Request $request, Workspace $workspace): void
    {
        if (!$workspace->hasMember($request->user())) {
            abort(403, 'Unauthorized access to workspace.');
        }
    }

    /**
     * Get task form data for a workspace (members, priorities, etc.)
     * Used by AJAX calls for dynamic task creation forms.
     */
    public function getTaskFormData(Request $request, int $workspaceId): \Illuminate\Http\JsonResponse
    {
        $workspace = Workspace::find($workspaceId);

        if (!$workspace) {
            return response()->json(['error' => 'Workspace not found'], 404);
        }

        $user = $request->user();

        // Check user access
        if (!$workspace->hasMember($user) && !$user->isAdminOrHigher()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Get workspace members
        $members = $workspace->members()
            ->select('users.id', 'users.name', 'users.email')
            ->get()
            ->map(fn ($member) => [
                'id' => $member->id,
                'name' => $member->name,
                'email' => $member->email,
            ]);

        // Get workspace priorities
        $priorities = $workspace->priorities()
            ->orderBy('sort_order')
            ->get()
            ->map(fn ($priority) => [
                'id' => $priority->id,
                'name' => $priority->name,
                'color' => $priority->color,
            ]);

        return response()->json([
            'members' => $members,
            'priorities' => $priorities,
        ]);
    }
}
