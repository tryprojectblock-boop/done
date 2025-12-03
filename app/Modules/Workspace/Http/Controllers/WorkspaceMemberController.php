<?php

declare(strict_types=1);

namespace App\Modules\Workspace\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ClientCrm;
use App\Models\User;
use App\Modules\Workspace\Enums\WorkspaceRole;
use App\Modules\Workspace\Events\MemberInvited;
use App\Modules\Workspace\Models\Workspace;
use App\Modules\Workspace\Models\WorkspaceInvitation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class WorkspaceMemberController extends Controller
{
    /**
     * Display list of workspace members.
     */
    public function index(Request $request, Workspace $workspace): View
    {
        $this->authorizeWorkspaceAccess($request, $workspace);

        return view('workspace::members', [
            'workspace' => $workspace->load(['members', 'invitations']),
        ]);
    }

    /**
     * Invite a member to the workspace.
     */
    public function invite(Request $request, Workspace $workspace): RedirectResponse
    {
        $this->authorizeWorkspaceAccess($request, $workspace);
        $this->authorizeManageMembers($request, $workspace);

        $validated = $request->validate([
            'user_id' => ['nullable', 'exists:users,id'],
            'email' => ['nullable', 'email'],
            'role' => ['required', 'in:admin,member,reviewer'],
            'resend' => ['nullable', 'exists:workspace_invitations,id'],
        ]);

        // Handle resend invitation
        if (!empty($validated['resend'])) {
            $invitation = WorkspaceInvitation::find($validated['resend']);
            if ($invitation && $invitation->workspace_id === $workspace->id) {
                event(new MemberInvited($workspace, $invitation->email, $invitation->role, $request->user()));
                return back()->with('success', 'Invitation resent successfully.');
            }
        }

        // Either user_id or email must be provided
        if (empty($validated['user_id']) && empty($validated['email'])) {
            return back()->with('error', 'Please select a team member or enter an email address.');
        }

        $role = WorkspaceRole::from($validated['role']);

        // If user_id is provided, add them directly
        if (!empty($validated['user_id'])) {
            $user = User::find($validated['user_id']);

            if (!$user) {
                return back()->with('error', 'User not found.');
            }

            // Check if user is already a member
            if ($workspace->hasMember($user)) {
                return back()->with('error', 'This user is already a member of this workspace.');
            }

            // Check if user belongs to the same company
            if ($user->company_id !== $request->user()->company_id) {
                return back()->with('error', 'You can only invite team members from your company.');
            }

            $workspace->addMember($user, $role, $request->user());

            return back()->with('success', "{$user->name} has been added to the workspace.");
        }

        // If email is provided, create an invitation
        if (!empty($validated['email'])) {
            $email = strtolower($validated['email']);

            // Check if there's already a pending invitation
            $existingInvitation = WorkspaceInvitation::where('workspace_id', $workspace->id)
                ->where('email', $email)
                ->where('status', 'pending')
                ->first();

            if ($existingInvitation) {
                return back()->with('error', 'An invitation has already been sent to this email.');
            }

            // Check if email belongs to an existing user who's already a member
            $existingUser = User::where('email', $email)->first();
            if ($existingUser && $workspace->hasMember($existingUser)) {
                return back()->with('error', 'This user is already a member of this workspace.');
            }

            // Create invitation
            $invitation = WorkspaceInvitation::create([
                'workspace_id' => $workspace->id,
                'email' => $email,
                'role' => $role->value,
                'token' => Str::random(64),
                'invited_by' => $request->user()->id,
                'expires_at' => now()->addDays(7),
            ]);

            event(new MemberInvited($workspace, $email, $role->value, $request->user()));

            return back()->with('success', "Invitation sent to {$email}.");
        }

        return back()->with('error', 'Something went wrong.');
    }

    /**
     * Update a member's role.
     */
    public function updateRole(Request $request, Workspace $workspace, User $user): RedirectResponse
    {
        $this->authorizeWorkspaceAccess($request, $workspace);
        $this->authorizeManageMembers($request, $workspace);

        $validated = $request->validate([
            'role' => ['required', 'in:admin,member,reviewer'],
        ]);

        // Cannot change owner's role
        if ($workspace->isOwner($user)) {
            return back()->with('error', 'Cannot change the owner\'s role.');
        }

        // Check if user is a member
        if (!$workspace->hasMember($user)) {
            return back()->with('error', 'User is not a member of this workspace.');
        }

        // Admins cannot change other admins' roles (only owner can)
        $currentUserRole = $workspace->getMemberRole($request->user());
        $targetUserRole = $workspace->getMemberRole($user);

        if (!$workspace->isOwner($request->user()) && $targetUserRole?->isAdmin()) {
            return back()->with('error', 'Only the owner can change an admin\'s role.');
        }

        $role = WorkspaceRole::from($validated['role']);
        $workspace->updateMemberRole($user, $role);

        return back()->with('success', "{$user->name}'s role has been updated to {$role->label()}.");
    }

    /**
     * Remove a member from the workspace.
     */
    public function remove(Request $request, Workspace $workspace, User $user): RedirectResponse
    {
        $this->authorizeWorkspaceAccess($request, $workspace);
        $this->authorizeManageMembers($request, $workspace);

        // Cannot remove the owner
        if ($workspace->isOwner($user)) {
            return back()->with('error', 'Cannot remove the workspace owner.');
        }

        // Check if user is a member
        if (!$workspace->hasMember($user)) {
            return back()->with('error', 'User is not a member of this workspace.');
        }

        // Admins cannot remove other admins (only owner can)
        $currentUserRole = $workspace->getMemberRole($request->user());
        $targetUserRole = $workspace->getMemberRole($user);

        if (!$workspace->isOwner($request->user()) && $targetUserRole?->isAdmin()) {
            return back()->with('error', 'Only the owner can remove an admin.');
        }

        $workspace->removeMember($user);

        return back()->with('success', "{$user->name} has been removed from the workspace.");
    }

    /**
     * Transfer workspace ownership to another member.
     */
    public function transferOwnership(Request $request, Workspace $workspace, User $user): RedirectResponse
    {
        $this->authorizeWorkspaceAccess($request, $workspace);

        // Only owner can transfer ownership
        if (!$workspace->isOwner($request->user())) {
            return back()->with('error', 'Only the owner can transfer ownership.');
        }

        // Check if target user is a member
        if (!$workspace->hasMember($user)) {
            return back()->with('error', 'User must be a member of the workspace to become owner.');
        }

        // Cannot transfer to yourself
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'You are already the owner.');
        }

        // Update ownership
        $workspace->update(['owner_id' => $user->id]);

        // Update roles: new owner gets 'owner', old owner becomes 'admin'
        $workspace->updateMemberRole($user, WorkspaceRole::OWNER);
        $workspace->updateMemberRole($request->user(), WorkspaceRole::ADMIN);

        return back()->with('success', "Ownership has been transferred to {$user->name}. You are now an Admin.");
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
     * Remove a guest from the workspace.
     */
    public function removeGuest(Request $request, Workspace $workspace, ClientCrm $guest): RedirectResponse
    {
        $this->authorizeWorkspaceAccess($request, $workspace);
        $this->authorizeManageMembers($request, $workspace);

        // Check if guest is associated with this workspace
        if (!$workspace->guests()->where('client_crm.id', $guest->id)->exists()) {
            return back()->with('error', 'Guest is not associated with this workspace.');
        }

        $workspace->guests()->detach($guest->id);

        return back()->with('success', "{$guest->full_name} has been removed from the workspace.");
    }

    /**
     * Check if user can manage members (owner or admin).
     */
    protected function authorizeManageMembers(Request $request, Workspace $workspace): void
    {
        $role = $workspace->getMemberRole($request->user());

        if (!$workspace->isOwner($request->user()) && !$role?->isAdmin()) {
            abort(403, 'You do not have permission to manage members.');
        }
    }
}
