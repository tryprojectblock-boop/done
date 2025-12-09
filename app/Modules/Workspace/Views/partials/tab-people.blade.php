<div class="space-y-6">
    <!-- Header with Invite Button -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-base-content">People</h2>
            <p class="text-base-content/60">Manage workspace members and their roles</p>
        </div>
        @if($workspace->isOwner(auth()->user()) || $workspace->getMemberRole(auth()->user())?->isAdmin())
        <button type="button" class="btn btn-primary" onclick="document.getElementById('invite-modal').showModal()">
            <span class="icon-[tabler--user-plus] size-5"></span>
            Invite Members
        </button>
        @endif
    </div>

    <!-- Members List -->
    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <div class="flex items-center justify-between mb-4">
                <h3 class="card-title text-lg">
                    <span class="icon-[tabler--users] size-5"></span>
                    Team Members
                </h3>
                <span class="badge badge-ghost">{{ $workspace->members->count() }} members</span>
            </div>

            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Member</th>
                            <th>Role</th>
                            <th>Joined</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($workspace->members->sortBy(function($m) {
                            $order = ['owner' => 0, 'admin' => 1, 'member' => 2, 'reviewer' => 3];
                            $roleValue = $m->pivot->role instanceof \App\Modules\Workspace\Enums\WorkspaceRole
                                ? $m->pivot->role->value
                                : (string) $m->pivot->role;
                            return $order[$roleValue] ?? 99;
                        }) as $member)
                        @php
                            $memberRole = $member->pivot->role;
                            $memberRoleValue = $memberRole instanceof \App\Modules\Workspace\Enums\WorkspaceRole
                                ? $memberRole->value
                                : (string) $memberRole;
                            $memberRoleLabel = $memberRole instanceof \App\Modules\Workspace\Enums\WorkspaceRole
                                ? $memberRole->label()
                                : ucfirst((string) $memberRole);
                            $roleColors = [
                                'owner' => 'badge-primary',
                                'admin' => 'badge-secondary',
                                'member' => 'badge-info',
                                'reviewer' => 'badge-warning',
                            ];
                        @endphp
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="avatar placeholder">
                                        <div class="bg-primary text-primary-content rounded-full w-10">
                                            <span>{{ substr($member->name, 0, 1) }}</span>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="font-medium">
                                            {{ $member->name }}
                                            @if($member->id === auth()->id())
                                                <span class="badge badge-ghost badge-xs ml-1">You</span>
                                            @endif
                                        </div>
                                        <div class="text-sm text-base-content/60">{{ $member->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge {{ $roleColors[$memberRoleValue] ?? 'badge-ghost' }}">
                                    {{ $memberRoleLabel }}
                                </span>
                            </td>
                            <td class="text-base-content/60">
                                {{ $member->pivot->joined_at ? \Carbon\Carbon::parse($member->pivot->joined_at)->format('M d, Y') : 'N/A' }}
                            </td>
                            <td class="text-right">
                                @if($memberRoleValue !== 'owner')
                                    @if($workspace->isOwner(auth()->user()) || ($workspace->getMemberRole(auth()->user())?->isAdmin() && $memberRoleValue !== 'admin'))
                                    <div class="dropdown dropdown-end">
                                        <label tabindex="0" class="btn btn-ghost btn-sm btn-square">
                                            <span class="icon-[tabler--dots-vertical] size-4"></span>
                                        </label>
                                        <ul tabindex="0" class="dropdown-menu dropdown-open:opacity-100 hidden min-w-40">
                                            <li class="dropdown-header">Change Role</li>
                                            @foreach(['admin', 'member', 'reviewer'] as $role)
                                                @if($memberRoleValue !== $role)
                                                <li>
                                                    <form action="{{ route('workspace.members.update-role', [$workspace, $member]) }}" method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                        <input type="hidden" name="role" value="{{ $role }}">
                                                        <button type="submit" class="dropdown-item w-full text-left">
                                                            <span class="icon-[tabler--shield] size-4"></span>
                                                            Make {{ ucfirst($role) }}
                                                        </button>
                                                    </form>
                                                </li>
                                                @endif
                                            @endforeach
                                            @if($workspace->isOwner(auth()->user()))
                                            <li class="dropdown-header mt-2">Ownership</li>
                                            <li>
                                                <button type="button" class="dropdown-item w-full text-left text-warning"
                                                    onclick="confirmTransferOwnership({{ $member->id }}, '{{ $member->name }}')">
                                                    <span class="icon-[tabler--crown] size-4"></span>
                                                    Transfer Ownership
                                                </button>
                                            </li>
                                            @endif
                                            <li class="border-t border-base-200 mt-2 pt-2">
                                                <button type="button" class="dropdown-item w-full text-left text-error"
                                                    onclick="confirmRemoveMember({{ $member->id }}, '{{ $member->name }}')">
                                                    <span class="icon-[tabler--user-minus] size-4"></span>
                                                    Remove from Workspace
                                                </button>
                                            </li>
                                        </ul>
                                    </div>
                                    @else
                                        <span class="text-base-content/30">-</span>
                                    @endif
                                @else
                                    <span class="text-base-content/30">-</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pending Invitations -->
    @php
        $pendingInvitations = $workspace->invitations->filter(fn($inv) => $inv->isPending());
    @endphp
    @if($pendingInvitations->count() > 0)
    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <div class="flex items-center justify-between mb-4">
                <h3 class="card-title text-lg">
                    <span class="icon-[tabler--mail] size-5"></span>
                    Pending Invitations
                </h3>
                <span class="badge badge-warning">{{ $pendingInvitations->count() }} pending</span>
            </div>

            <div class="space-y-3">
                @foreach($pendingInvitations as $invitation)
                <div class="flex items-center justify-between p-3 bg-base-200 rounded-lg">
                    <div class="flex items-center gap-3">
                        <div class="avatar placeholder">
                            <div class="bg-warning text-warning-content rounded-full w-10">
                                <span class="icon-[tabler--mail] size-5"></span>
                            </div>
                        </div>
                        <div>
                            <p class="font-medium">{{ $invitation->email }}</p>
                            <p class="text-sm text-base-content/60">Invited {{ $invitation->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        @php
                            $invRoleValue = $invitation->role instanceof \App\Modules\Workspace\Enums\WorkspaceRole
                                ? $invitation->role->value
                                : $invitation->role;
                        @endphp
                        <span class="badge badge-{{ $roleColors[$invRoleValue] ?? 'badge-ghost' }}">{{ ucfirst($invRoleValue) }}</span>
                        <form action="{{ route('workspace.members.invite', $workspace) }}" method="POST" class="inline">
                            @csrf
                            <input type="hidden" name="resend" value="{{ $invitation->id }}">
                            <button type="submit" class="btn btn-ghost btn-sm" title="Resend invitation">
                                <span class="icon-[tabler--refresh] size-4"></span>
                            </button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Guests List -->
    @if($workspace->guests->count() > 0)
    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <div class="flex items-center justify-between mb-4">
                <h3 class="card-title text-lg">
                    <span class="icon-[tabler--user-star] size-5"></span>
                    Guests
                </h3>
                <span class="badge badge-warning">{{ $workspace->guests->count() }} guests</span>
            </div>

            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Guest</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($workspace->guests as $guest)
                        @php
                            $guestStatusColors = [
                                'active' => 'success',
                                'invited' => 'warning',
                                'suspended' => 'error',
                            ];
                        @endphp
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="avatar placeholder">
                                        <div class="bg-warning text-warning-content rounded-full w-10">
                                            <span>{{ $guest->initials }}</span>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="font-medium">{{ $guest->full_name }}</div>
                                        <div class="text-sm text-base-content/60">{{ $guest->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-warning">Guest</span>
                            </td>
                            <td>
                                <span class="badge badge-{{ $guestStatusColors[$guest->status] ?? 'ghost' }}">{{ ucfirst($guest->status) }}</span>
                            </td>
                            <td class="text-right">
                                @if($workspace->isOwner(auth()->user()) || $workspace->getMemberRole(auth()->user())?->isAdmin())
                                <button type="button" class="btn btn-ghost btn-sm btn-square text-error"
                                    onclick="confirmRemoveGuest({{ $guest->id }}, '{{ $guest->full_name }}')">
                                    <span class="icon-[tabler--user-minus] size-4"></span>
                                </button>
                                @else
                                    <span class="text-base-content/30">-</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Role Info -->
    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <h3 class="card-title text-lg mb-4">
                <span class="icon-[tabler--info-circle] size-5"></span>
                Role Permissions
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="p-4 bg-primary/5 border border-primary/20 rounded-lg">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="badge badge-primary">Owner</span>
                    </div>
                    <p class="text-sm text-base-content/70">Full control. Can delete workspace and transfer ownership.</p>
                </div>
                <div class="p-4 bg-secondary/5 border border-secondary/20 rounded-lg">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="badge badge-secondary">Admin</span>
                    </div>
                    <p class="text-sm text-base-content/70">Can manage members, settings, and all content.</p>
                </div>
                <div class="p-4 bg-info/5 border border-info/20 rounded-lg">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="badge badge-info">Member</span>
                    </div>
                    <p class="text-sm text-base-content/70">Can create and manage their own content.</p>
                </div>
                <div class="p-4 bg-warning/5 border border-warning/20 rounded-lg">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="badge badge-warning">Reviewer</span>
                    </div>
                    <p class="text-sm text-base-content/70">Can view and comment on items only.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Invite Modal -->
<dialog id="invite-modal" class="modal">
    <div class="modal-box max-w-lg">
        <form method="dialog">
            <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">
                <span class="icon-[tabler--x] size-5"></span>
            </button>
        </form>
        <h3 class="font-bold text-lg mb-4">
            <span class="icon-[tabler--user-plus] size-5 mr-2"></span>
            Invite Team Members
        </h3>

        <form action="{{ route('workspace.members.invite', $workspace) }}" method="POST" id="invite-form">
            @csrf

            <!-- Invite from Team -->
            <div class="form-control mb-4">
                <label class="label" for="invite-user-select">
                    <span class="label-text font-medium">Select Team Member</span>
                </label>
                <select name="user_id" id="invite-user-select" class="select select-bordered">
                    <option value="">Choose a team member...</option>
                    @php
                        $existingMemberIds = $workspace->members->pluck('id')->toArray();
                        $availableMembers = \App\Models\User::where('company_id', auth()->user()->company_id)
                            ->whereNotIn('id', $existingMemberIds)
                            ->where('status', \App\Models\User::STATUS_ACTIVE)
                            ->orderBy('name')
                            ->get();
                    @endphp
                    @foreach($availableMembers as $user)
                        <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                    @endforeach
                </select>
            </div>

            <div class="divider">OR</div>

            <!-- Invite by Email -->
            <div class="form-control mb-4">
                <label class="label" for="invite-email">
                    <span class="label-text font-medium">Invite by Email</span>
                </label>
                <input type="email" name="email" id="invite-email" class="input input-bordered" placeholder="Enter email address...">
                <span class="label">
                    <span class="label-text-alt text-base-content/60">They'll receive an invitation email</span>
                </span>
            </div>

            <!-- Role Selection -->
            <div class="form-control mb-6">
                <label class="label" for="invite-role">
                    <span class="label-text font-medium">Role <span class="text-error">*</span></span>
                </label>
                <select name="role" id="invite-role" class="select select-bordered" required>
                    <option value="">Select a role...</option>
                    <option value="admin">Admin</option>
                    <option value="member">Member</option>
                    <option value="reviewer">Reviewer</option>
                </select>
            </div>

            <div class="modal-action">
                <button type="button" class="btn btn-ghost" onclick="document.getElementById('invite-modal').close()">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <span class="icon-[tabler--send] size-5"></span>
                    Send Invitation
                </button>
            </div>
        </form>
    </div>
    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</dialog>

<!-- Remove Member Confirmation Modal -->
<dialog id="remove-member-modal" class="modal">
    <div class="modal-box">
        <h3 class="font-bold text-lg text-error mb-4">
            <span class="icon-[tabler--alert-triangle] size-5 mr-2"></span>
            Remove Member
        </h3>
        <p class="mb-4">Are you sure you want to remove <strong id="remove-member-name"></strong> from this workspace?</p>
        <p class="text-sm text-base-content/60 mb-6">They will lose access to all workspace content immediately.</p>

        <form id="remove-member-form" method="POST">
            @csrf
            @method('DELETE')
            <div class="modal-action">
                <button type="button" class="btn btn-ghost" onclick="document.getElementById('remove-member-modal').close()">Cancel</button>
                <button type="submit" class="btn btn-error">
                    <span class="icon-[tabler--user-minus] size-5"></span>
                    Remove Member
                </button>
            </div>
        </form>
    </div>
    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</dialog>

<!-- Transfer Ownership Confirmation Modal -->
<dialog id="transfer-ownership-modal" class="modal">
    <div class="modal-box">
        <h3 class="font-bold text-lg text-warning mb-4">
            <span class="icon-[tabler--crown] size-5 mr-2"></span>
            Transfer Ownership
        </h3>
        <p class="mb-4">Are you sure you want to transfer ownership to <strong id="transfer-member-name"></strong>?</p>
        <p class="text-sm text-base-content/60 mb-6">You will become an Admin and they will have full control over this workspace.</p>

        <form id="transfer-ownership-form" method="POST">
            @csrf
            <div class="modal-action">
                <button type="button" class="btn btn-ghost" onclick="document.getElementById('transfer-ownership-modal').close()">Cancel</button>
                <button type="submit" class="btn btn-warning">
                    <span class="icon-[tabler--crown] size-5"></span>
                    Transfer Ownership
                </button>
            </div>
        </form>
    </div>
    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</dialog>

<!-- Remove Guest Confirmation Modal -->
<dialog id="remove-guest-modal" class="modal">
    <div class="modal-box">
        <h3 class="font-bold text-lg text-error mb-4">
            <span class="icon-[tabler--alert-triangle] size-5 mr-2"></span>
            Remove Guest
        </h3>
        <p class="mb-4">Are you sure you want to remove <strong id="remove-guest-name"></strong> from this workspace?</p>
        <p class="text-sm text-base-content/60 mb-6">They will lose access to all workspace content immediately.</p>

        <form id="remove-guest-form" method="POST">
            @csrf
            @method('DELETE')
            <div class="modal-action">
                <button type="button" class="btn btn-ghost" onclick="document.getElementById('remove-guest-modal').close()">Cancel</button>
                <button type="submit" class="btn btn-error">
                    <span class="icon-[tabler--user-minus] size-5"></span>
                    Remove Guest
                </button>
            </div>
        </form>
    </div>
    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</dialog>

<script>
function confirmRemoveMember(userId, userName) {
    document.getElementById('remove-member-name').textContent = userName;
    document.getElementById('remove-member-form').action = '/workspaces/{{ $workspace->uuid }}/members/' + userId;
    document.getElementById('remove-member-modal').showModal();
}

function confirmTransferOwnership(userId, userName) {
    document.getElementById('transfer-member-name').textContent = userName;
    document.getElementById('transfer-ownership-form').action = '/workspaces/{{ $workspace->uuid }}/members/' + userId + '/transfer-ownership';
    document.getElementById('transfer-ownership-modal').showModal();
}

function confirmRemoveGuest(guestId, guestName) {
    document.getElementById('remove-guest-name').textContent = guestName;
    document.getElementById('remove-guest-form').action = '/workspaces/{{ $workspace->uuid }}/guests/' + guestId;
    document.getElementById('remove-guest-modal').showModal();
}
</script>
