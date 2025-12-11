<?php

namespace App\Http\Controllers;

use App\Mail\UserInvitationMail;
use App\Mail\TeamInvitationMail;
use App\Models\User;
use App\Models\TeamInvitation;
use App\Services\PlanLimitService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UsersController extends Controller
{
    private const USER_NOT_FOUND = 'User not found.';

    public function __construct(
        private readonly PlanLimitService $planLimitService
    ) {}

    /**
     * Display a listing of users.
     */
    public function index(Request $request): View
    {
        $companyId = $request->user()->company_id;

        // Query users from the company_user pivot table
        $query = User::query()
            ->join('company_user', 'users.id', '=', 'company_user.user_id')
            ->where('company_user.company_id', $companyId)
            ->select('users.*', 'company_user.role as company_role', 'company_user.joined_at as company_joined_at')
            ->orderByRaw("FIELD(company_user.role, 'owner', 'admin', 'member', 'guest')")
            ->orderBy('users.first_name');

        // Filter by role (from pivot table)
        if ($request->filled('role')) {
            $query->where('company_user.role', $request->role);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('users.status', $request->status);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('users.first_name', 'like', "%{$search}%")
                    ->orWhere('users.last_name', 'like', "%{$search}%")
                    ->orWhere('users.email', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(20)->withQueryString();

        // Get counts by role from pivot table
        $roleCounts = \DB::table('company_user')
            ->where('company_id', $companyId)
            ->selectRaw('role, count(*) as count')
            ->groupBy('role')
            ->pluck('count', 'role')
            ->toArray();

        // Get pending team invitations (existing users from other companies)
        $pendingInvitationsQuery = TeamInvitation::with('user')
            ->where('company_id', $companyId)
            ->pending();

        // Apply search filter to pending invitations too
        if ($request->filled('search')) {
            $search = $request->search;
            $pendingInvitationsQuery->whereHas('user', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Apply role filter to pending invitations
        if ($request->filled('role')) {
            $pendingInvitationsQuery->where('role', $request->role);
        }

        // Only show pending invitations when status filter is empty or 'invited'
        $pendingInvitations = collect();
        if (!$request->filled('status') || $request->status === 'invited') {
            $pendingInvitations = $pendingInvitationsQuery->get();
        }

        // Count pending invitations
        $pendingInvitationCount = TeamInvitation::where('company_id', $companyId)->pending()->count();

        return view('users.index', [
            'users' => $users,
            'roles' => User::ROLES,
            'roleCounts' => $roleCounts,
            'currentRole' => $request->role,
            'currentStatus' => $request->status,
            'search' => $request->search,
            'pendingInvitations' => $pendingInvitations,
            'pendingInvitationCount' => $pendingInvitationCount,
        ]);
    }

    /**
     * Show the invite members page.
     */
    public function invitePage(Request $request): View
    {
        $currentUser = $request->user();

        // Filter roles that the current user can invite
        $availableRoles = array_filter(User::ROLES, function ($role, $key) use ($currentUser) {
            return $currentUser->canInviteRole($key);
        }, ARRAY_FILTER_USE_BOTH);

        return view('users.invite', [
            'roles' => $availableRoles,
        ]);
    }

    /**
     * Show user details (for drawer/API).
     */
    public function show(Request $request, User $user): JsonResponse
    {
        $companyId = $request->user()->company_id;

        // Check if user belongs to this company via pivot table
        $membership = \DB::table('company_user')
            ->where('company_id', $companyId)
            ->where('user_id', $user->id)
            ->first();

        if (!$membership) {
            return response()->json(['error' => self::USER_NOT_FOUND], 404);
        }

        // Get the role for this company from the pivot table
        $companyRole = $membership->role;
        $roleData = User::ROLES[$companyRole] ?? null;
        $roleLabel = $roleData['label'] ?? ucfirst($companyRole);
        $roleColor = $roleData['color'] ?? 'neutral';

        // Check if user is the owner of THIS company (not their own company)
        $currentUserCompany = $request->user()->company;
        $isOwnerOfThisCompany = $currentUserCompany && $currentUserCompany->owner_id === $user->id;

        return response()->json([
            'user' => [
                'id' => $user->id,
                'uuid' => $user->uuid,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'full_name' => $user->full_name,
                'email' => $user->email,
                'role' => $companyRole,
                'role_label' => $roleLabel,
                'role_color' => $roleColor,
                'status' => $user->status,
                'description' => $user->description,
                'timezone' => $user->timezone,
                'avatar_url' => $user->avatar_url,
                'initials' => $user->initials,
                'created_at' => $user->created_at->format('M d, Y'),
                'last_login_at' => $user->last_login_at?->format('M d, Y h:i A'),
                'can_edit' => $request->user()->canManage($user),
                'can_delete' => $request->user()->canRemoveRole($companyRole) && $user->id !== $request->user()->id && !$isOwnerOfThisCompany,
                'is_company_owner' => $isOwnerOfThisCompany,
            ],
        ]);
    }

    /**
     * Show the edit user page.
     */
    public function edit(Request $request, User $user): View
    {
        // Ensure user belongs to same company
        if ($user->company_id !== $request->user()->company_id) {
            abort(404);
        }

        // Check permissions
        if (!$request->user()->canManage($user)) {
            abort(403, 'You do not have permission to edit this user');
        }

        $currentUser = $request->user();

        // Filter roles that the current user can assign
        $availableRoles = array_filter(User::ROLES, function ($role, $key) use ($currentUser, $user) {
            // Can always keep current role
            if ($key === $user->role) {
                return true;
            }
            // Only owners can assign owner role
            if ($key === User::ROLE_OWNER) {
                return $currentUser->isOwner();
            }
            // Admins can only assign member and guest roles
            if ($currentUser->isAdmin()) {
                return in_array($key, [User::ROLE_MEMBER, User::ROLE_GUEST]);
            }
            // Owners can assign all roles
            return $currentUser->isOwner();
        }, ARRAY_FILTER_USE_BOTH);

        return view('users.edit', [
            'user' => $user,
            'roles' => $availableRoles,
            'statuses' => [
                User::STATUS_ACTIVE => 'Active',
                User::STATUS_SUSPENDED => 'Suspended',
            ],
            'canChangeRole' => $currentUser->canManage($user),
            'isCompanyOwner' => $user->isCompanyOwner(),
        ]);
    }

    /**
     * Send invitations to multiple members.
     */
    public function sendInvitations(Request $request): JsonResponse
    {
        $currentUser = $request->user();

        if (!$currentUser->canManageUsers()) {
            return response()->json(['error' => 'You do not have permission to invite users'], 403);
        }

        $validated = $request->validate([
            'members' => ['required', 'array', 'min:1', 'max:8'],
            'members.*.first_name' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z\s\-\']+$/'],
            'members.*.email' => ['required', 'email', 'distinct'],
            'members.*.role' => ['required', Rule::in(array_keys(User::ROLES))],
        ], [
            'members.*.first_name.regex' => 'First name can only contain letters, spaces, hyphens and apostrophes.',
            'members.*.email.distinct' => 'Duplicate email addresses are not allowed.',
        ]);

        // Check team member limit before inviting
        $company = $currentUser->company;
        if ($company) {
            $membersToInvite = count($validated['members']);
            $currentCount = $this->planLimitService->getTeamMemberCount($company);
            $limits = $this->planLimitService->getLimits($company);
            $limit = $limits['team_members'];

            // If not unlimited (0 = unlimited)
            if ($limit > 0 && ($currentCount + $membersToInvite) > $limit) {
                $remaining = max(0, $limit - $currentCount);
                $planName = $company->plan?->name ?? 'Free';

                return response()->json([
                    'error' => "Your {$planName} plan allows up to {$limit} team members. You currently have {$currentCount} members and are trying to invite {$membersToInvite}. You can only invite {$remaining} more member(s). Please upgrade your plan to invite more team members.",
                    'limit_reached' => true,
                    'upgrade_url' => route('settings.billing.plans'),
                ], 403);
            }
        }

        $invitedCount = 0;
        $errors = [];

        foreach ($validated['members'] as $index => $memberData) {
            // Check if user can invite this role
            if (!$currentUser->canInviteRole($memberData['role'])) {
                $roleLabel = User::ROLES[$memberData['role']]['label'] ?? $memberData['role'];
                $errors["members.{$index}.role"] = ["You don't have permission to invite {$roleLabel}s."];
                continue;
            }

            // Check if email already exists
            $existingUser = User::where('email', $memberData['email'])->first();

            if ($existingUser) {
                // User exists - check if already in this company
                if ($existingUser->company_id === $currentUser->company_id) {
                    $errors["members.{$index}.email"] = ["This user is already a member of your team."];
                    continue;
                }

                // Check if there's already a pending invitation for this user to this company
                if (TeamInvitation::hasPendingInvitation($currentUser->company_id, $existingUser->id)) {
                    $errors["members.{$index}.email"] = ["An invitation has already been sent to this user."];
                    continue;
                }

                // Create team invitation for existing user
                $invitation = TeamInvitation::create([
                    'company_id' => $currentUser->company_id,
                    'user_id' => $existingUser->id,
                    'invited_by' => $currentUser->id,
                    'role' => $memberData['role'],
                ]);

                // Send team invitation email (different from new user invitation)
                Mail::to($existingUser->email)->send(new TeamInvitationMail($invitation, $currentUser));

                $invitedCount++;
            } else {
                // New user - create user record with invited status
                $invitationToken = Str::random(64);

                $user = User::create([
                    'first_name' => $memberData['first_name'],
                    'last_name' => '',
                    'name' => $memberData['first_name'],
                    'email' => $memberData['email'],
                    'password' => '',
                    'company_id' => $currentUser->company_id,
                    'role' => $memberData['role'],
                    'status' => User::STATUS_INVITED,
                    'invited_by' => $currentUser->id,
                    'invited_at' => now(),
                    'invitation_token' => $invitationToken,
                    'invitation_expires_at' => now()->addDays(7),
                    'timezone' => 'UTC',
                ]);

                // Add user to company_user pivot table
                \DB::table('company_user')->insert([
                    'company_id' => $currentUser->company_id,
                    'user_id' => $user->id,
                    'role' => $memberData['role'],
                    'is_primary' => true,
                    'joined_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Send invitation email for new user
                Mail::to($user->email)->send(new UserInvitationMail($user, $currentUser, $invitationToken));

                $invitedCount++;
            }
        }

        if (!empty($errors)) {
            return response()->json([
                'success' => false,
                'errors' => $errors,
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => $invitedCount === 1
                ? 'Invitation sent successfully!'
                : "All {$invitedCount} invitations sent successfully!",
            'invited_count' => $invitedCount,
        ]);
    }

    /**
     * Update user details.
     */
    public function update(Request $request, User $user): JsonResponse
    {
        // Ensure user belongs to same company
        if ($user->company_id !== $request->user()->company_id) {
            return response()->json(['error' => self::USER_NOT_FOUND], 404);
        }

        // Check permissions
        if (!$request->user()->canManage($user)) {
            return response()->json(['error' => 'You do not have permission to edit this user'], 403);
        }

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'role' => ['required', Rule::in(array_keys(User::ROLES))],
            'status' => ['sometimes', Rule::in([User::STATUS_ACTIVE, User::STATUS_SUSPENDED])],
        ]);

        // Prevent role escalation
        $currentUser = $request->user();
        if ($validated['role'] === User::ROLE_OWNER && !$currentUser->isOwner()) {
            return response()->json(['error' => 'Only owners can promote users to owner'], 403);
        }

        $user->update([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'name' => trim($validated['first_name'] . ' ' . $validated['last_name']),
            'role' => $validated['role'],
            'status' => $validated['status'] ?? $user->status,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully.',
            'user' => [
                'id' => $user->id,
                'full_name' => $user->full_name,
                'role_label' => $user->role_label,
            ],
        ]);
    }

    /**
     * Remove a user.
     */
    public function destroy(Request $request, User $user): JsonResponse
    {
        $currentUser = $request->user();

        // Ensure user belongs to same company
        if ($user->company_id !== $currentUser->company_id) {
            return response()->json(['error' => self::USER_NOT_FOUND], 404);
        }

        // Cannot delete yourself
        if ($user->id === $currentUser->id) {
            return response()->json(['error' => 'You cannot delete your own account'], 403);
        }

        // Cannot delete the company owner
        if ($user->isCompanyOwner()) {
            return response()->json(['error' => 'The company owner cannot be deleted'], 403);
        }

        // Check if current user can remove this role
        if (!$currentUser->canRemoveRole($user->role)) {
            return response()->json(['error' => 'You do not have permission to remove this user'], 403);
        }

        // Soft delete
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User removed successfully.',
        ]);
    }

    /**
     * Resend invitation email.
     */
    public function resendInvitation(Request $request, User $user): JsonResponse
    {
        // Ensure user belongs to same company
        if ($user->company_id !== $request->user()->company_id) {
            return response()->json(['error' => self::USER_NOT_FOUND], 404);
        }

        if ($user->status !== User::STATUS_INVITED) {
            return response()->json(['error' => 'User has already accepted the invitation'], 400);
        }

        // Generate new invitation token
        $invitationToken = Str::random(64);
        $user->update([
            'invitation_token' => $invitationToken,
            'invitation_expires_at' => now()->addDays(7),
        ]);

        // Resend invitation email
        Mail::to($user->email)->send(new UserInvitationMail($user, $request->user(), $invitationToken));

        return response()->json([
            'success' => true,
            'message' => 'Invitation email resent successfully.',
        ]);
    }
}
