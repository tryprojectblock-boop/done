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
            'user_ids' => ['nullable', 'array'],
            'user_ids.*' => ['exists:users,id'],
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

        // Either user_id, user_ids, or email must be provided
        if (empty($validated['user_id']) && empty($validated['user_ids']) && empty($validated['email'])) {
            return back()->with('error', 'Please select a team member or enter an email address.');
        }

        $role = WorkspaceRole::from($validated['role']);
        $companyId = $request->user()->company_id;

        // Handle multiple user_ids (multi-select)
        if (!empty($validated['user_ids'])) {
            $addedMembers = [];
            $skippedMembers = [];

            foreach ($validated['user_ids'] as $userId) {
                $user = User::find($userId);

                if (!$user) {
                    continue;
                }

                // Check if user is already a member
                if ($workspace->hasMember($user)) {
                    $skippedMembers[] = $user->name;
                    continue;
                }

                // Check if user belongs to the same company (via company_user pivot table)
                $isCompanyMember = \DB::table('company_user')
                    ->where('company_id', $companyId)
                    ->where('user_id', $user->id)
                    ->exists();

                if (!$isCompanyMember) {
                    $skippedMembers[] = $user->name;
                    continue;
                }

                $workspace->addMember($user, $role, $request->user());
                $addedMembers[] = $user->name;
            }

            if (count($addedMembers) === 0 && count($skippedMembers) > 0) {
                return back()->with('error', 'All selected users are already members of this workspace.');
            }

            $message = count($addedMembers) === 1
                ? "{$addedMembers[0]} has been added to the workspace."
                : count($addedMembers) . " members have been added to the workspace.";

            if (count($skippedMembers) > 0) {
                $message .= " (" . count($skippedMembers) . " skipped - already members)";
            }

            return back()->with('success', $message);
        }

        // If single user_id is provided, add them directly (backward compatibility)
        if (!empty($validated['user_id'])) {
            $user = User::find($validated['user_id']);

            if (!$user) {
                return back()->with('error', 'User not found.');
            }

            // Check if user is already a member
            if ($workspace->hasMember($user)) {
                return back()->with('error', 'This user is already a member of this workspace.');
            }

            // Check if user belongs to the same company (via company_user pivot table)
            $isCompanyMember = \DB::table('company_user')
                ->where('company_id', $companyId)
                ->where('user_id', $user->id)
                ->exists();

            if (!$isCompanyMember) {
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
    public function removeGuest(Request $request, Workspace $workspace, User $guest): RedirectResponse
    {
        $this->authorizeWorkspaceAccess($request, $workspace);
        $this->authorizeManageMembers($request, $workspace);

        // Check if guest is associated with this workspace
        if (!$workspace->guests()->where('users.id', $guest->id)->exists()) {
            return back()->with('error', 'Guest is not associated with this workspace.');
        }

        $workspace->guests()->detach($guest->id);

        $label = $workspace->type->value === 'inbox' ? 'Client' : 'Guest';
        return back()->with('success', "{$guest->full_name} has been removed from the workspace.");
    }

    /**
     * Add an existing user as a guest to the workspace.
     */
    public function storeGuest(Request $request, Workspace $workspace): RedirectResponse
    {
        $this->authorizeWorkspaceAccess($request, $workspace);
        $this->authorizeManageMembers($request, $workspace);

        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::findOrFail($request->input('user_id'));

        // Check if already a guest
        if ($workspace->guests()->where('users.id', $user->id)->exists()) {
            return back()->with('error', "{$user->full_name} is already a guest of this workspace.");
        }

        // Check if already a member
        if ($workspace->members()->where('users.id', $user->id)->exists()) {
            return back()->with('error', "{$user->full_name} is already a member of this workspace.");
        }

        $workspace->guests()->attach($user->id, ['invited_by' => $request->user()->id]);

        $label = $workspace->type->value === 'inbox' ? 'client' : 'guest';
        return back()->with('success', "{$user->full_name} has been added as a {$label}.");
    }

    /**
     * Invite a new guest to the workspace by email.
     */
    public function inviteGuest(Request $request, Workspace $workspace): RedirectResponse
    {
        $this->authorizeWorkspaceAccess($request, $workspace);
        $this->authorizeManageMembers($request, $workspace);

        $request->validate([
            'email' => 'required|email',
            'name' => 'nullable|string|max:255',
        ]);

        $email = $request->input('email');
        $name = $request->input('name');

        // Check if user already exists
        $user = User::where('email', $email)->first();

        if ($user) {
            // User exists - check if already a guest or member
            if ($workspace->guests()->where('users.id', $user->id)->exists()) {
                return back()->with('error', "{$user->full_name} is already a guest of this workspace.");
            }
            if ($workspace->members()->where('users.id', $user->id)->exists()) {
                return back()->with('error', "{$user->full_name} is already a member of this workspace.");
            }

            // Add as guest
            $workspace->guests()->attach($user->id, ['invited_by' => $request->user()->id]);
            $label = $workspace->type->value === 'inbox' ? 'client' : 'guest';
            return back()->with('success', "{$user->full_name} has been added as a {$label}.");
        }

        // Create new guest user
        $invitationToken = \Illuminate\Support\Str::random(64);
        $user = User::create([
            'email' => $email,
            'name' => $name ?: explode('@', $email)[0],
            'password' => bcrypt(\Illuminate\Support\Str::random(32)),
            'role' => User::ROLE_GUEST,
            'is_guest' => true,
            'status' => User::STATUS_INVITED,
            'invitation_token' => $invitationToken,
            'invitation_expires_at' => now()->addDays(30),
        ]);

        // Add as guest to workspace
        $workspace->guests()->attach($user->id, ['invited_by' => $request->user()->id]);

        // TODO: Send invitation email to the new guest

        $label = $workspace->type->value === 'inbox' ? 'Client' : 'Guest';
        return back()->with('success', "{$label} invitation sent to {$email}.");
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
