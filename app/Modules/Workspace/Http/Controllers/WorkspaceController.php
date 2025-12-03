<?php

declare(strict_types=1);

namespace App\Modules\Workspace\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Workspace\Contracts\WorkspaceServiceInterface;
use App\Modules\Workspace\DTOs\CreateWorkspaceDTO;
use App\Modules\Workspace\Enums\WorkspaceRole;
use App\Modules\Workspace\Enums\WorkspaceType;
use App\Modules\Workspace\Models\Workspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WorkspaceController extends Controller
{
    public function __construct(
        private WorkspaceServiceInterface $workspaceService
    ) {}

    /**
     * Display a listing of workspaces.
     */
    public function index(Request $request): View
    {
        $workspaces = $this->workspaceService->getForUser($request->user());

        return view('workspace::index', [
            'workspaces' => $workspaces,
        ]);
    }

    /**
     * Show the form for creating a new workspace.
     */
    public function create(Request $request): View
    {
        // Get team members for invitation (excluding current user)
        $teamMembers = User::where('company_id', $request->user()->company_id)
            ->where('id', '!=', $request->user()->id)
            ->where('status', User::STATUS_ACTIVE)
            ->orderBy('name')
            ->get();

        return view('workspace::create', [
            'workspaceTypes' => WorkspaceType::cases(),
            'workspaceRoles' => WorkspaceRole::cases(),
            'teamMembers' => $teamMembers,
        ]);
    }

    /**
     * Store a newly created workspace.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'type' => ['required', 'string', 'in:classic,product'],
            'description' => ['nullable', 'string', 'max:500'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'members' => ['nullable', 'array'],
            'members.*.user_id' => ['required_with:members', 'exists:users,id'],
            'members.*.role' => ['required_with:members', 'string', 'in:admin,member,guest,reviewer'],
            'guests' => ['nullable', 'array'],
            'guests.*' => ['email'],
        ]);

        $dto = new CreateWorkspaceDTO(
            name: $validated['name'],
            type: WorkspaceType::from($validated['type']),
            ownerId: $request->user()->id,
            description: $validated['description'] ?? null,
            settings: [
                'start_date' => $validated['start_date'] ?? null,
                'end_date' => $validated['end_date'] ?? null,
            ],
        );

        $workspace = $this->workspaceService->create($dto);

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

        // Send guest invitations
        if (!empty($validated['guests'])) {
            foreach ($validated['guests'] as $guestEmail) {
                // TODO: Send invitation email to guest
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

        return view('workspace::show', [
            'workspace' => $workspace->load(['members', 'owner']),
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
