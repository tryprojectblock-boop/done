<div class="space-y-6">
    <!-- Header with Add Member Button -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-base-content">People</h2>
            <p class="text-base-content/60">Manage workspace members and their roles</p>
        </div>
        @if($workspace->isOwner(auth()->user()) || $workspace->getMemberRole(auth()->user())?->isAdmin())
        <button type="button" class="btn btn-primary" onclick="openInviteModal()">
            <span class="icon-[tabler--user-plus] size-5"></span>
            Add Team Member
        </button>
        @endif
    </div>

    <!-- Members List -->
    @php
        // Filter out guests from members list (guests should only be in workspace_guests table)
        $teamMembers = $workspace->members->filter(function($m) {
            $roleValue = $m->pivot->role instanceof \App\Modules\Workspace\Enums\WorkspaceRole
                ? $m->pivot->role->value
                : (string) $m->pivot->role;
            return $roleValue !== 'guest';
        });
    @endphp
    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <div class="flex items-center justify-between mb-4">
                <h3 class="card-title text-lg">
                    <span class="icon-[tabler--users] size-5"></span>
                    Team Members
                </h3>
                <span class="badge badge-ghost">{{ $teamMembers->count() }} members</span>
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
                        @foreach($teamMembers->sortBy(function($m) {
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
                                    <div class="avatar">
                                        <div class="w-10 rounded-full">
                                            <img src="{{ $member->avatar_url }}" alt="{{ $member->name }}" />
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

    @php
        $isInboxWorkspace = $workspace->type->value === 'inbox';
        $guestStatusColors = [
            'active' => 'success',
            'invited' => 'warning',
            'suspended' => 'error',
        ];

        // Separate clients (users who have submitted tickets) from regular guests
        $workspaceGuestIds = $workspace->guests->pluck('id')->toArray();

        if ($isInboxWorkspace) {
            // Clients: guests who have created tickets in this workspace
            $clientUserIds = \App\Modules\Task\Models\Task::where('workspace_id', $workspace->id)
                ->whereNotNull('created_by')
                ->pluck('created_by')
                ->unique()
                ->toArray();
            $clients = $workspace->guests->filter(fn($g) => in_array($g->id, $clientUserIds));
            $regularGuests = $workspace->guests->filter(fn($g) => !in_array($g->id, $clientUserIds));
        } else {
            $clients = collect();
            $regularGuests = $workspace->guests;
        }
    @endphp

    @if($workspace->isOwner(auth()->user()) || auth()->user()->isAdminOrHigher())
    @if($isInboxWorkspace)
    <!-- Clients List (Inbox workspace only) -->
    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <h3 class="card-title text-lg">
                        <span class="icon-[tabler--user-check] size-5"></span>
                        Clients
                    </h3>
                    <span class="badge badge-primary">{{ $clients->count() }} clients</span>
                </div>
                @if($workspace->isOwner(auth()->user()) || $workspace->getMemberRole(auth()->user())?->isAdmin())
                <button type="button" class="btn btn-primary btn-sm" onclick="openAddClientModal()">
                    <span class="icon-[tabler--user-plus] size-4"></span>
                    Add Client
                </button>
                @endif
            </div>

            @if($clients->count() > 0)
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Client</th>
                            <th>Tickets</th>
                            <th>Status</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($clients as $client)
                        @php
                            $ticketCount = \App\Modules\Task\Models\Task::where('workspace_id', $workspace->id)
                                ->where('created_by', $client->id)
                                ->count();
                        @endphp
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="avatar">
                                        <div class="w-10 rounded-full">
                                            <img src="{{ $client->avatar_url }}" alt="{{ $client->full_name }}" />
                                        </div>
                                    </div>
                                    <div>
                                        <div class="font-medium">{{ $client->full_name }}</div>
                                        <div class="text-sm text-base-content/60">{{ $client->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-ghost">{{ $ticketCount }} {{ Str::plural('ticket', $ticketCount) }}</span>
                            </td>
                            <td>
                                <span class="badge badge-{{ $guestStatusColors[$client->status] ?? 'ghost' }}">{{ ucfirst($client->status) }}</span>
                            </td>
                            <td class="text-right">
                                @if($workspace->isOwner(auth()->user()) || $workspace->getMemberRole(auth()->user())?->isAdmin())
                                <div class="flex items-center justify-end gap-1">
                                    @if($client->status !== 'active')
                                    <form action="{{ route('workspace.guests.resend-portal-email', [$workspace, $client]) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="btn btn-ghost btn-sm gap-1 text-primary" title="Resend Portal Email">
                                            <span class="icon-[tabler--mail-forward] size-4"></span>
                                            <span class="hidden sm:inline">Resend Email</span>
                                        </button>
                                    </form>
                                    @endif
                                    <button type="button" class="btn btn-ghost btn-sm btn-square text-error"
                                        onclick="confirmRemoveGuest({{ $client->id }}, '{{ $client->full_name }}', 'client')">
                                        <span class="icon-[tabler--user-minus] size-4"></span>
                                    </button>
                                </div>
                                @else
                                    <span class="text-base-content/30">-</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-8">
                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-base-200 flex items-center justify-center">
                    <span class="icon-[tabler--user-check] size-8 text-base-content/30"></span>
                </div>
                <h4 class="font-medium text-base-content mb-1">No Clients Yet</h4>
                <p class="text-sm text-base-content/60 mb-4">
                    Clients are automatically added when they submit tickets via email.
                </p>
                @if($workspace->isOwner(auth()->user()) || $workspace->getMemberRole(auth()->user())?->isAdmin())
                <button type="button" class="btn btn-primary btn-sm" onclick="openAddClientModal()">
                    <span class="icon-[tabler--user-plus] size-4"></span>
                    Add Client
                </button>
                @endif
            </div>
            @endif
        </div>
    </div>
    @endif

    <!-- Guests List (only for workspace owner or admin) -->
    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <h3 class="card-title text-lg">
                        <span class="icon-[tabler--user-star] size-5"></span>
                        Guests
                    </h3>
                    <span class="badge badge-warning">{{ $regularGuests->count() }} guests</span>
                </div>
                @if($workspace->isOwner(auth()->user()) || $workspace->getMemberRole(auth()->user())?->isAdmin())
                <button type="button" class="btn btn-warning btn-sm" onclick="openAddGuestModal()">
                    <span class="icon-[tabler--user-plus] size-4"></span>
                    Add Guest
                </button>
                @endif
            </div>

            @if($regularGuests->count() > 0)
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
                        @foreach($regularGuests as $guest)
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="avatar">
                                        <div class="w-10 rounded-full">
                                            <img src="{{ $guest->avatar_url }}" alt="{{ $guest->full_name }}" />
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
                                    onclick="confirmRemoveGuest({{ $guest->id }}, '{{ $guest->full_name }}', 'guest')">
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
            @else
            <div class="text-center py-8">
                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-base-200 flex items-center justify-center">
                    <span class="icon-[tabler--user-star] size-8 text-base-content/30"></span>
                </div>
                <h4 class="font-medium text-base-content mb-1">No Guests Yet</h4>
                <p class="text-sm text-base-content/60 mb-4">
                    Add guests to give them limited access to this workspace.
                </p>
                @if($workspace->isOwner(auth()->user()) || $workspace->getMemberRole(auth()->user())?->isAdmin())
                <button type="button" class="btn btn-warning btn-sm" onclick="openAddGuestModal()">
                    <span class="icon-[tabler--user-plus] size-4"></span>
                    Add Guest
                </button>
                @endif
            </div>
            @endif
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

<!-- Add Team Member Modal (using shared partial) -->
@include('workspace::partials.invite-member-modal')

<!-- Remove Member Confirmation Modal -->
<div id="remove-member-modal" class="custom-modal">
    <div class="custom-modal-box max-w-md bg-base-100">
        <div class="text-center mb-6">
            <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-error/20 flex items-center justify-center">
                <span class="icon-[tabler--user-minus] size-8 text-error"></span>
            </div>
            <h3 class="text-xl font-bold text-base-content mb-2">Remove Member</h3>
            <p class="text-base-content/70">Are you sure you want to remove <strong id="remove-member-name" class="text-error"></strong> from this workspace?</p>
        </div>

        <div class="p-4 bg-error/10 border border-error/20 rounded-lg mb-6">
            <div class="flex items-start gap-3">
                <span class="icon-[tabler--alert-triangle] size-5 text-error mt-0.5"></span>
                <p class="text-sm text-base-content/70">This action cannot be undone. They will lose access to all workspace content immediately.</p>
            </div>
        </div>

        <form id="remove-member-form" method="POST">
            @csrf
            @method('DELETE')
            <div class="flex justify-end gap-3">
                <button type="button" class="btn btn-ghost" onclick="closeRemoveMemberModal()">Cancel</button>
                <button type="submit" class="btn btn-error">
                    <span class="icon-[tabler--user-minus] size-5"></span>
                    Remove Member
                </button>
            </div>
        </form>
    </div>
    <div class="custom-modal-backdrop" onclick="closeRemoveMemberModal()"></div>
</div>

<!-- Transfer Ownership Confirmation Modal -->
<div id="transfer-ownership-modal" class="custom-modal">
    <div class="custom-modal-box max-w-md bg-base-100">
        <div class="text-center mb-6">
            <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-warning/20 flex items-center justify-center">
                <span class="icon-[tabler--crown] size-8 text-warning"></span>
            </div>
            <h3 class="text-xl font-bold text-base-content mb-2">Transfer Ownership</h3>
            <p class="text-base-content/70">Are you sure you want to transfer ownership to <strong id="transfer-member-name" class="text-warning"></strong>?</p>
        </div>

        <div class="p-4 bg-warning/10 border border-warning/20 rounded-lg mb-6">
            <div class="flex items-start gap-3">
                <span class="icon-[tabler--info-circle] size-5 text-warning mt-0.5"></span>
                <div class="text-sm text-base-content/70">
                    <p class="mb-2"><strong>What will happen:</strong></p>
                    <ul class="list-disc list-inside space-y-1">
                        <li>They will become the new workspace owner</li>
                        <li>You will be changed to an Admin role</li>
                        <li>This action cannot be undone by you</li>
                    </ul>
                </div>
            </div>
        </div>

        <form id="transfer-ownership-form" method="POST">
            @csrf
            <div class="flex justify-end gap-3">
                <button type="button" class="btn btn-ghost" onclick="closeTransferOwnershipModal()">Cancel</button>
                <button type="submit" class="btn btn-warning">
                    <span class="icon-[tabler--crown] size-5"></span>
                    Transfer Ownership
                </button>
            </div>
        </form>
    </div>
    <div class="custom-modal-backdrop" onclick="closeTransferOwnershipModal()"></div>
</div>

<!-- Remove Guest/Client Confirmation Modal -->
<div id="remove-guest-modal" class="custom-modal">
    <div class="custom-modal-box max-w-md bg-base-100">
        <div class="text-center mb-6">
            <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-error/20 flex items-center justify-center">
                <span class="icon-[tabler--user-star] size-8 text-error"></span>
            </div>
            <h3 class="text-xl font-bold text-base-content mb-2">Remove {{ $guestLabel ?? 'Guest' }}</h3>
            <p class="text-base-content/70">Are you sure you want to remove <strong id="remove-guest-name" class="text-error"></strong> from this workspace?</p>
        </div>

        <div class="p-4 bg-error/10 border border-error/20 rounded-lg mb-6">
            <div class="flex items-start gap-3">
                <span class="icon-[tabler--alert-triangle] size-5 text-error mt-0.5"></span>
                <p class="text-sm text-base-content/70">This action cannot be undone. They will lose access to all workspace content immediately.</p>
            </div>
        </div>

        <form id="remove-guest-form" method="POST">
            @csrf
            @method('DELETE')
            <div class="flex justify-end gap-3">
                <button type="button" class="btn btn-ghost" onclick="closeRemoveGuestModal()">Cancel</button>
                <button type="submit" class="btn btn-error">
                    <span class="icon-[tabler--user-minus] size-5"></span>
                    Remove {{ $guestLabel ?? 'Guest' }}
                </button>
            </div>
        </form>
    </div>
    <div class="custom-modal-backdrop" onclick="closeRemoveGuestModal()"></div>
</div>

@php
    // Get users who could be added (not already guests/members of this workspace)
    $existingGuestIds = $workspace->guests->pluck('id')->toArray();
    $existingMemberIds = $workspace->members->pluck('id')->toArray();
    $excludeIds = array_merge($existingGuestIds, $existingMemberIds);

    // Potential clients: users who have submitted tickets to ANY inbox workspace (but not already in this workspace)
    $potentialClientIds = \App\Modules\Task\Models\Task::whereHas('workspace', fn($q) => $q->where('type', 'inbox'))
        ->whereNotNull('created_by')
        ->pluck('created_by')
        ->unique()
        ->toArray();
    $potentialClients = \App\Models\User::whereIn('id', $potentialClientIds)
        ->whereNotIn('id', $excludeIds)
        ->orderBy('name')
        ->limit(20)
        ->get();

    // Potential guests: users marked as guests (is_guest = true) but not already in this workspace and not clients
    // Also filter by invited_by or guestWorkspaces to match /guests page logic
    $currentUser = auth()->user();
    $userWorkspaceIds = \App\Modules\Workspace\Models\Workspace::where('owner_id', $currentUser->id)
        ->orWhereHas('members', function ($q) use ($currentUser) {
            $q->where('user_id', $currentUser->id);
        })
        ->pluck('id');

    $potentialGuestUsers = \App\Models\User::where('is_guest', true)
        ->whereNotIn('id', $excludeIds)
        ->whereNotIn('id', $potentialClientIds)
        ->where(function ($q) use ($currentUser, $userWorkspaceIds) {
            $q->where('invited_by', $currentUser->id)
                ->orWhereHas('guestWorkspaces', function ($wsQuery) use ($userWorkspaceIds) {
                    $wsQuery->whereIn('workspace_id', $userWorkspaceIds);
                });
        })
        ->orderBy('name')
        ->limit(20)
        ->get();
@endphp

@if($isInboxWorkspace)
<!-- Add Client Modal -->
<div id="add-client-modal" class="custom-modal">
    <div class="custom-modal-box max-w-lg bg-base-100">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-bold text-base-content">Add Client</h3>
            <button type="button" class="btn btn-ghost btn-sm btn-circle" onclick="closeAddClientModal()">
                <span class="icon-[tabler--x] size-5"></span>
            </button>
        </div>

        <!-- Search/Add New -->
        <div class="mb-4">
            <label class="label"><span class="label-text font-medium">Email Address</span></label>
            <div class="join w-full">
                <input type="email" id="client-email-input" class="input input-bordered join-item flex-1"
                       placeholder="Enter email address..." onkeyup="searchClients(this.value)">
                <button type="button" class="btn btn-primary join-item" onclick="addClientByEmail()">
                    <span class="icon-[tabler--plus] size-4"></span>
                    Add
                </button>
            </div>
            <p class="text-xs text-base-content/60 mt-1">Enter an email to add an existing client or invite a new one</p>
        </div>

        <!-- Existing Clients List -->
        @if($potentialClients->count() > 0)
        <div class="divider text-xs text-base-content/50">Or select existing clients</div>

        <div id="potential-clients-list" class="max-h-48 overflow-y-auto space-y-2">
            @foreach($potentialClients as $potentialClient)
            <div class="flex items-center justify-between p-2 rounded-lg hover:bg-base-200 client-search-item" data-email="{{ strtolower($potentialClient->email) }}" data-name="{{ strtolower($potentialClient->name) }}">
                <div class="flex items-center gap-3">
                    <div class="avatar">
                        <div class="w-8 rounded-full">
                            <img src="{{ $potentialClient->avatar_url }}" alt="{{ $potentialClient->name }}" />
                        </div>
                    </div>
                    <div>
                        <div class="font-medium text-sm">{{ $potentialClient->name }}</div>
                        <div class="text-xs text-base-content/60">{{ $potentialClient->email }}</div>
                    </div>
                </div>
                <form action="{{ route('workspace.guests.store', $workspace) }}" method="POST" class="inline">
                    @csrf
                    <input type="hidden" name="user_id" value="{{ $potentialClient->id }}">
                    <button type="submit" class="btn btn-primary btn-xs">
                        <span class="icon-[tabler--plus] size-3"></span>
                        Add
                    </button>
                </form>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-4 text-base-content/60 text-sm">
            <span class="icon-[tabler--users] size-8 block mx-auto mb-2 opacity-30"></span>
            No existing clients available to add
        </div>
        @endif

        <!-- Add New Client Form (hidden by default) -->
        <div id="new-client-form" class="hidden mt-4 p-4 bg-base-200 rounded-lg">
            <h4 class="font-medium text-sm mb-3">Invite New Client</h4>
            <form action="{{ route('workspace.guests.invite', $workspace) }}" method="POST">
                @csrf
                <input type="hidden" name="email" id="new-client-email">
                <input type="hidden" name="type" value="client">
                <div class="form-control mb-3">
                    <label class="label"><span class="label-text text-sm">Name (optional)</span></label>
                    <input type="text" name="name" class="input input-bordered input-sm" placeholder="Client name">
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" class="btn btn-ghost btn-sm" onclick="hideNewClientForm()">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <span class="icon-[tabler--send] size-4"></span>
                        Send Invite
                    </button>
                </div>
            </form>
        </div>

        <div class="flex justify-end mt-6">
            <button type="button" class="btn btn-ghost" onclick="closeAddClientModal()">Close</button>
        </div>
    </div>
    <div class="custom-modal-backdrop" onclick="closeAddClientModal()"></div>
</div>
@endif

<!-- Add Guest Modal -->
<div id="add-guest-modal" class="custom-modal">
    <div class="custom-modal-box max-w-lg bg-base-100">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-bold text-base-content">Add Guest</h3>
            <button type="button" class="btn btn-ghost btn-sm btn-circle" onclick="closeAddGuestModal()">
                <span class="icon-[tabler--x] size-5"></span>
            </button>
        </div>

        <!-- Search/Add New -->
        <div class="mb-4">
            <label class="label"><span class="label-text font-medium">Email Address</span></label>
            <div class="join w-full">
                <input type="email" id="guest-email-input" class="input input-bordered join-item flex-1"
                       placeholder="Enter email address..." onkeyup="searchGuests(this.value)">
                <button type="button" class="btn btn-warning join-item" onclick="addGuestByEmail()">
                    <span class="icon-[tabler--plus] size-4"></span>
                    Add
                </button>
            </div>
            <p class="text-xs text-base-content/60 mt-1">Enter an email to add an existing guest or invite a new one</p>
        </div>

        <!-- Existing Guests List -->
        @if($potentialGuestUsers->count() > 0)
        <div class="divider text-xs text-base-content/50">Or select existing guests</div>

        <div id="potential-guests-list" class="max-h-48 overflow-y-auto space-y-2">
            @foreach($potentialGuestUsers as $potentialGuest)
            <div class="flex items-center justify-between p-2 rounded-lg hover:bg-base-200 guest-search-item" data-email="{{ strtolower($potentialGuest->email) }}" data-name="{{ strtolower($potentialGuest->name) }}">
                <div class="flex items-center gap-3">
                    <div class="avatar">
                        <div class="w-8 rounded-full">
                            <img src="{{ $potentialGuest->avatar_url }}" alt="{{ $potentialGuest->name }}" />
                        </div>
                    </div>
                    <div>
                        <div class="font-medium text-sm">{{ $potentialGuest->name }}</div>
                        <div class="text-xs text-base-content/60">{{ $potentialGuest->email }}</div>
                    </div>
                </div>
                <form action="{{ route('workspace.guests.store', $workspace) }}" method="POST" class="inline">
                    @csrf
                    <input type="hidden" name="user_id" value="{{ $potentialGuest->id }}">
                    <button type="submit" class="btn btn-warning btn-xs">
                        <span class="icon-[tabler--plus] size-3"></span>
                        Add
                    </button>
                </form>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-4 text-base-content/60 text-sm">
            <span class="icon-[tabler--users] size-8 block mx-auto mb-2 opacity-30"></span>
            No existing guests available to add
        </div>
        @endif

        <!-- Add New Guest Form (hidden by default) -->
        <div id="new-guest-form" class="hidden mt-4 p-4 bg-base-200 rounded-lg">
            <h4 class="font-medium text-sm mb-3">Invite New Guest</h4>
            <form action="{{ route('workspace.guests.invite', $workspace) }}" method="POST">
                @csrf
                <input type="hidden" name="email" id="new-guest-email">
                <input type="hidden" name="type" value="guest">
                <div class="form-control mb-3">
                    <label class="label"><span class="label-text text-sm">Name (optional)</span></label>
                    <input type="text" name="name" class="input input-bordered input-sm" placeholder="Guest name">
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" class="btn btn-ghost btn-sm" onclick="hideNewGuestForm()">Cancel</button>
                    <button type="submit" class="btn btn-warning btn-sm">
                        <span class="icon-[tabler--send] size-4"></span>
                        Send Invite
                    </button>
                </div>
            </form>
        </div>

        <div class="flex justify-end mt-6">
            <button type="button" class="btn btn-ghost" onclick="closeAddGuestModal()">Close</button>
        </div>
    </div>
    <div class="custom-modal-backdrop" onclick="closeAddGuestModal()"></div>
</div>

<script>
// Close modals on Escape key for tab-people specific modals
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeRemoveMemberModal();
        closeTransferOwnershipModal();
        closeRemoveGuestModal();
        closeAddClientModal();
        closeAddGuestModal();
    }
});

// Remove Member Modal Functions
function confirmRemoveMember(userId, userName) {
    document.getElementById('remove-member-name').textContent = userName;
    document.getElementById('remove-member-form').action = '/workspaces/{{ $workspace->uuid }}/members/' + userId;
    document.getElementById('remove-member-modal').classList.add('modal-open');
    document.body.style.overflow = 'hidden';
}

function closeRemoveMemberModal() {
    document.getElementById('remove-member-modal').classList.remove('modal-open');
    document.body.style.overflow = '';
}

// Transfer Ownership Modal Functions
function confirmTransferOwnership(userId, userName) {
    document.getElementById('transfer-member-name').textContent = userName;
    document.getElementById('transfer-ownership-form').action = '/workspaces/{{ $workspace->uuid }}/members/' + userId + '/transfer-ownership';
    document.getElementById('transfer-ownership-modal').classList.add('modal-open');
    document.body.style.overflow = 'hidden';
}

function closeTransferOwnershipModal() {
    document.getElementById('transfer-ownership-modal').classList.remove('modal-open');
    document.body.style.overflow = '';
}

// Remove Guest/Client Modal Functions
function confirmRemoveGuest(guestId, guestName, type = 'guest') {
    document.getElementById('remove-guest-name').textContent = guestName;
    document.getElementById('remove-guest-form').action = '/workspaces/{{ $workspace->uuid }}/guests/' + guestId;

    // Update modal title based on type
    const label = type === 'client' ? 'Client' : 'Guest';
    const modalTitle = document.querySelector('#remove-guest-modal h3');
    const modalBtn = document.querySelector('#remove-guest-modal button[type="submit"]');
    if (modalTitle) modalTitle.textContent = 'Remove ' + label;
    if (modalBtn) modalBtn.innerHTML = '<span class="icon-[tabler--user-minus] size-5"></span> Remove ' + label;

    document.getElementById('remove-guest-modal').classList.add('modal-open');
    document.body.style.overflow = 'hidden';
}

function closeRemoveGuestModal() {
    document.getElementById('remove-guest-modal').classList.remove('modal-open');
    document.body.style.overflow = '';
}

// Add Client Modal Functions
function openAddClientModal() {
    const modal = document.getElementById('add-client-modal');
    if (!modal) return;
    modal.classList.add('modal-open');
    document.body.style.overflow = 'hidden';
    const emailInput = document.getElementById('client-email-input');
    if (emailInput) {
        emailInput.value = '';
        emailInput.focus();
    }
    hideNewClientForm();
    searchClients('');
}

function closeAddClientModal() {
    const modal = document.getElementById('add-client-modal');
    if (!modal) return;
    modal.classList.remove('modal-open');
    document.body.style.overflow = '';
}

function searchClients(query) {
    const items = document.querySelectorAll('.client-search-item');
    const lowerQuery = query.toLowerCase().trim();

    items.forEach(item => {
        const email = item.dataset.email || '';
        const name = item.dataset.name || '';
        if (lowerQuery === '' || email.includes(lowerQuery) || name.includes(lowerQuery)) {
            item.style.display = '';
        } else {
            item.style.display = 'none';
        }
    });
}

function addClientByEmail() {
    const email = document.getElementById('client-email-input').value.trim();
    if (!email) {
        document.getElementById('client-email-input').focus();
        return;
    }

    // Check if email is valid
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        alert('Please enter a valid email address');
        return;
    }

    // Check if user already exists in the potential clients list
    const existingItem = document.querySelector(`.client-search-item[data-email="${email.toLowerCase()}"]`);
    if (existingItem) {
        // Click the add button for this user
        const addBtn = existingItem.querySelector('button[type="submit"]');
        if (addBtn) {
            addBtn.click();
            return;
        }
    }

    // Show the new client form for inviting
    showNewClientForm(email);
}

function showNewClientForm(email) {
    const form = document.getElementById('new-client-form');
    const emailInput = document.getElementById('new-client-email');
    if (form && emailInput) {
        emailInput.value = email;
        form.classList.remove('hidden');
    }
}

function hideNewClientForm() {
    const form = document.getElementById('new-client-form');
    const emailInput = document.getElementById('new-client-email');
    if (form) form.classList.add('hidden');
    if (emailInput) emailInput.value = '';
}

// Add Guest Modal Functions
function openAddGuestModal() {
    const modal = document.getElementById('add-guest-modal');
    if (!modal) return;
    modal.classList.add('modal-open');
    document.body.style.overflow = 'hidden';
    const emailInput = document.getElementById('guest-email-input');
    if (emailInput) {
        emailInput.value = '';
        emailInput.focus();
    }
    hideNewGuestForm();
    searchGuests('');
}

function closeAddGuestModal() {
    const modal = document.getElementById('add-guest-modal');
    if (!modal) return;
    modal.classList.remove('modal-open');
    document.body.style.overflow = '';
}

function searchGuests(query) {
    const items = document.querySelectorAll('.guest-search-item');
    const lowerQuery = query.toLowerCase().trim();

    items.forEach(item => {
        const email = item.dataset.email || '';
        const name = item.dataset.name || '';
        if (lowerQuery === '' || email.includes(lowerQuery) || name.includes(lowerQuery)) {
            item.style.display = '';
        } else {
            item.style.display = 'none';
        }
    });
}

function addGuestByEmail() {
    const email = document.getElementById('guest-email-input').value.trim();
    if (!email) {
        document.getElementById('guest-email-input').focus();
        return;
    }

    // Check if email is valid
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        alert('Please enter a valid email address');
        return;
    }

    // Check if user already exists in the potential guests list
    const existingItem = document.querySelector(`.guest-search-item[data-email="${email.toLowerCase()}"]`);
    if (existingItem) {
        // Click the add button for this user
        const addBtn = existingItem.querySelector('button[type="submit"]');
        if (addBtn) {
            addBtn.click();
            return;
        }
    }

    // Show the new guest form for inviting
    showNewGuestForm(email);
}

function showNewGuestForm(email) {
    const form = document.getElementById('new-guest-form');
    const emailInput = document.getElementById('new-guest-email');
    if (form && emailInput) {
        emailInput.value = email;
        form.classList.remove('hidden');
    }
}

function hideNewGuestForm() {
    const form = document.getElementById('new-guest-form');
    const emailInput = document.getElementById('new-guest-email');
    if (form) form.classList.add('hidden');
    if (emailInput) emailInput.value = '';
}
</script>
