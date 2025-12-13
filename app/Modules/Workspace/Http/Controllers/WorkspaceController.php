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

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'type' => ['required', 'string', 'in:classic,product'],
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

        try {
            $workspace = $this->workspaceService->create($dto);
        } catch (WorkspaceException $e) {
            return redirect()->route('workspace.index')
                ->with('error', $e->getMessage());
        }

        // Add invited members
        if (!empty($validated['members'])) {
            foreach ($validated['members'] as $member) {
                $user = User::find($member['user_id']);
                if ($user) {
                    $role = WorkspaceRole::from($member['role']);
                    $workspace->addMember($user, $role, $request->user());
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

        return redirect()->route('workspace.show', $workspace)
            ->with('success', 'Workspace created successfully.');
    }

    /**
     * Display the specified workspace.
     */
    public function show(Request $request, Workspace $workspace): View
    {
        $this->authorizeWorkspaceAccess($request, $workspace);

        // Load tasks for this workspace (filtered by visibility for private tasks)
        $tasks = \App\Modules\Task\Models\Task::where('workspace_id', $workspace->id)
            ->visibleTo($request->user())
            ->with(['assignee', 'creator', 'status'])
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

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
            'workspace' => $workspace->load(['members', 'owner', 'invitations', 'workflow', 'guests']),
            'tasks' => $tasks,
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
     */
    public function settings(Request $request, Workspace $workspace): View
    {
        $this->authorizeWorkspaceAccess($request, $workspace);

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
     * Check if user has workspace access.
     */
    protected function authorizeWorkspaceAccess(Request $request, Workspace $workspace): void
    {
        if (!$workspace->hasMember($request->user())) {
            abort(403, 'Unauthorized access to workspace.');
        }
    }
}
