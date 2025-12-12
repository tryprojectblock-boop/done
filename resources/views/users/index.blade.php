@extends('layouts.app')

@section('content')
<div class="p-4 md:p-6">
    <div class="max-w-7xl mx-auto">
        <!-- Page Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-base-content">Team Members</h1>
                <p class="text-base-content/60">Manage your organization's team members and their roles</p>
            </div>
                <a href="{{ route('users.invite') }}" class="btn btn-primary">
                <span class="icon-[tabler--user-plus] size-5"></span>
                Invite User
            </a>
        </div>

        <!-- Filters & Search -->
        <div class="card bg-base-100 shadow mb-6">
            <div class="card-body p-4">
                <form action="{{ route('users.index') }}" method="GET" class="flex flex-col md:flex-row gap-4">
                    <!-- Search -->
                    <div class="flex-1">
                        <div class="relative">
                            <span class="icon-[tabler--search] size-5 absolute left-3 top-1/2 -translate-y-1/2 text-base-content/50"></span>
                            <input type="text" name="search" value="{{ $search }}" placeholder="Search by name or email..." class="input input-bordered w-full pl-10" />
                        </div>
                    </div>
                    <!-- Role Filter -->
                    <select name="role" class="select select-bordered w-full md:w-48" onchange="this.form.submit()">
                        <option value="">All Roles</option>
                        @foreach($roles as $key => $role)
                            <option value="{{ $key }}" {{ $currentRole === $key ? 'selected' : '' }}>
                                {{ $role['label'] }} ({{ $roleCounts[$key] ?? 0 }})
                            </option>
                        @endforeach
                    </select>
                    <!-- Status Filter -->
                    <select name="status" class="select select-bordered w-full md:w-40" onchange="this.form.submit()">
                        <option value="">All Status</option>
                        <option value="active" {{ $currentStatus === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="invited" {{ $currentStatus === 'invited' ? 'selected' : '' }}>Invited</option>
                        <option value="suspended" {{ $currentStatus === 'suspended' ? 'selected' : '' }}>Suspended</option>
                    </select>
                    <button type="submit" class="btn btn-ghost">
                        <span class="icon-[tabler--filter] size-5"></span>
                        Filter
                    </button>
                    @if($search || $currentRole || $currentStatus)
                        <a href="{{ route('users.index') }}" class="btn btn-ghost text-error">
                            <span class="icon-[tabler--x] size-5"></span>
                            Clear
                        </a>
                    @endif
                </form>
            </div>
        </div>

        <!-- Role Legend -->
        <div class="flex flex-wrap gap-4 mb-6">
            @foreach($roles as $key => $role)
                <div class="flex items-center gap-2 text-sm">
                    <span class="badge badge-{{ $role['color'] }} badge-sm">{{ $role['label'] }}</span>
                    <span class="text-base-content/60">{{ $roleCounts[$key] ?? 0 }}</span>
                </div>
            @endforeach
            @if($pendingInvitationCount > 0)
                <div class="flex items-center gap-2 text-sm">
                    <span class="badge badge-warning badge-sm">Pending Invitations</span>
                    <span class="text-base-content/60">{{ $pendingInvitationCount }}</span>
                </div>
            @endif
        </div>

        <!-- Users Table -->
        <div class="card bg-base-100 shadow">
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Last Login</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Pending Team Invitations (existing users from other companies) --}}
                        @foreach($pendingInvitations as $invitation)
                            <tr class="hover bg-warning/5">
                                <td>
                                    <div class="flex items-center gap-3">
                                        @include('partials.user-avatar', ['user' => $invitation->user, 'size' => 'md'])
                                        <div>
                                            <div class="font-medium">{{ $invitation->user->full_name }}</div>
                                            <div class="text-sm text-base-content/60">{{ $invitation->user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $roleData = \App\Models\User::ROLES[$invitation->role] ?? null;
                                        $roleLabel = $roleData['label'] ?? ucfirst($invitation->role);
                                        $roleColor = $roleData['color'] ?? 'neutral';
                                    @endphp
                                    <span class="badge badge-{{ $roleColor }}">{{ $roleLabel }}</span>
                                </td>
                                <td>
                                    <div class="flex flex-col">
                                        <span class="badge badge-warning">Pending Invitation</span>
                                        <span class="text-xs text-base-content/50 mt-1">Expires {{ $invitation->expires_at->diffForHumans() }}</span>
                                    </div>
                                </td>
                                <td class="text-base-content/60 text-sm">
                                    Invited {{ $invitation->created_at->diffForHumans() }}
                                </td>
                                <td class="text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <button class="btn btn-ghost btn-sm btn-circle" onclick="resendTeamInvitation({{ $invitation->id }})" title="Resend Invitation">
                                            <span class="icon-[tabler--mail-forward] size-4"></span>
                                        </button>
                                        <button class="btn btn-ghost btn-sm btn-circle text-error" onclick="cancelTeamInvitation({{ $invitation->id }})" title="Cancel Invitation">
                                            <span class="icon-[tabler--x] size-4"></span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach

                        {{-- Regular Users --}}
                        @forelse($users as $user)
                            <tr class="hover cursor-pointer" onclick="openUserDrawer({{ $user->id }})">
                                <td>
                                    <div class="flex items-center gap-3">
                                        @include('partials.user-avatar', ['user' => $user, 'size' => 'md'])
                                        <div>
                                            <div class="font-medium">{{ $user->full_name }}</div>
                                            <div class="text-sm text-base-content/60">{{ $user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @php
                                        // Use company_role from pivot table if available
                                        $displayRole = $user->company_role ?? $user->role;
                                        $roleData = \App\Models\User::ROLES[$displayRole] ?? null;
                                        $roleLabel = $roleData['label'] ?? ucfirst($displayRole);
                                        $roleColor = $roleData['color'] ?? 'neutral';
                                        // Check if user is the owner of THIS company (not their own company)
                                        $isOwnerOfThisCompany = auth()->user()->company && auth()->user()->company->owner_id === $user->id;
                                    @endphp
                                    <div class="flex items-center gap-1">
                                        <span class="badge badge-{{ $roleColor }}">{{ $roleLabel }}</span>
                                        @if($isOwnerOfThisCompany)
                                            <span class="badge badge-ghost badge-sm" title="Company Owner">
                                                <span class="icon-[tabler--crown] size-3 text-warning"></span>
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if($user->status === 'active')
                                        <span class="badge badge-success badge-outline">Active</span>
                                    @elseif($user->status === 'invited')
                                        <span class="badge badge-warning badge-outline">Invited</span>
                                    @else
                                        <span class="badge badge-error badge-outline">Suspended</span>
                                    @endif
                                </td>
                                <td class="text-base-content/60 text-sm">
                                    {{ $user->last_login_at?->diffForHumans() ?? 'Never' }}
                                </td>
                                <td class="text-right">
                                    <div class="flex items-center justify-end gap-1" onclick="event.stopPropagation()">
                                        <button class="btn btn-ghost btn-sm btn-circle" onclick="openUserDrawer({{ $user->id }})" title="View Details">
                                            <span class="icon-[tabler--eye] size-4"></span>
                                        </button>
                                        @if(auth()->user()->canManage($user))
                                            <a href="{{ route('users.edit', $user) }}" class="btn btn-ghost btn-sm btn-circle" title="Edit User">
                                                <span class="icon-[tabler--edit] size-4"></span>
                                            </a>
                                            @if($user->status === 'invited')
                                                <button class="btn btn-ghost btn-sm btn-circle" onclick="resendInvitation({{ $user->id }})" title="Resend Invitation">
                                                    <span class="icon-[tabler--mail-forward] size-4"></span>
                                                </button>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            @if($pendingInvitations->isEmpty())
                            <tr>
                                <td colspan="5" class="text-center py-12">
                                    <div class="text-base-content/50">
                                        <span class="icon-[tabler--users-group] size-12 mb-4"></span>
                                        <p class="text-lg font-medium">No users found</p>
                                        <p class="text-sm">Try adjusting your search or filters</p>
                                    </div>
                                </td>
                            </tr>
                            @endif
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($users->hasPages())
                <div class="card-body border-t border-base-200 py-4">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- User Preview Drawer -->
<div id="user-drawer-overlay" class="fixed inset-0 bg-black/50 z-[150] hidden" onclick="closeUserDrawer()"></div>
<div id="user-drawer" class="fixed top-0 right-0 h-full w-96 bg-base-100 shadow-xl z-[200] transform translate-x-full transition-transform duration-300 ease-in-out overflow-y-auto">
    <div class="p-6">
        <div id="drawer-loading" class="flex items-center justify-center h-64">
            <span class="loading loading-spinner loading-lg"></span>
        </div>
        <div id="drawer-content" class="hidden">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-bold">User Details</h3>
                <button onclick="closeUserDrawer()" class="btn btn-ghost btn-sm btn-circle">
                    <span class="icon-[tabler--x] size-5"></span>
                </button>
            </div>

            <!-- User Info -->
            <div class="text-center mb-6">
                <div id="drawer-avatar-container" class="avatar placeholder mb-4">
                    <div id="drawer-avatar" class="bg-primary text-primary-content rounded-full w-20 h-20 flex items-center justify-center overflow-hidden">
                        <span id="drawer-initials" class="text-2xl font-semibold"></span>
                        <img id="drawer-avatar-img" src="" alt="" class="w-full h-full object-cover hidden" />
                    </div>
                </div>
                <h4 id="drawer-name" class="text-xl font-bold"></h4>
                <p id="drawer-email" class="text-base-content/60"></p>
                <div class="mt-2 flex items-center justify-center gap-1 flex-wrap">
                    <span id="drawer-role-badge" class="badge"></span>
                    <span id="drawer-owner-badge" class="badge badge-warning badge-sm gap-1 hidden">
                        <span class="icon-[tabler--crown] size-3"></span>
                        Company Owner
                    </span>
                    <span id="drawer-status-badge" class="badge badge-outline"></span>
                </div>
            </div>

            <!-- Details -->
            <div class="space-y-4 mb-6">
                <div class="flex items-center justify-between py-2 border-b border-base-200">
                    <span class="text-base-content/60">Description</span>
                    <span id="drawer-description" class="text-right max-w-[200px] truncate"></span>
                </div>
                <div class="flex items-center justify-between py-2 border-b border-base-200">
                    <span class="text-base-content/60">Timezone</span>
                    <span id="drawer-timezone"></span>
                </div>
                <div class="flex items-center justify-between py-2 border-b border-base-200">
                    <span class="text-base-content/60">Joined</span>
                    <span id="drawer-created"></span>
                </div>
                <div class="flex items-center justify-between py-2 border-b border-base-200">
                    <span class="text-base-content/60">Last Login</span>
                    <span id="drawer-last-login"></span>
                </div>
            </div>

            <!-- Actions -->
            <div id="drawer-actions" class="space-y-2">
                <a id="drawer-edit-btn" href="#" class="btn btn-primary w-full hidden">
                    <span class="icon-[tabler--edit] size-5"></span>
                    Edit User
                </a>
                <button id="drawer-delete-btn" class="btn btn-error btn-outline w-full hidden" onclick="confirmDeleteUser()">
                    <span class="icon-[tabler--trash] size-5"></span>
                    Remove User
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let currentUserId = null;
let currentUserData = null;

function openUserDrawer(userId) {
    currentUserId = userId;

    // Show drawer
    document.getElementById('user-drawer-overlay').classList.remove('hidden');
    document.getElementById('user-drawer').classList.remove('translate-x-full');
    document.body.style.overflow = 'hidden';

    // Reset state
    document.getElementById('drawer-loading').classList.remove('hidden');
    document.getElementById('drawer-content').classList.add('hidden');

    fetch(`/users/${userId}`, {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
        },
    })
    .then(response => response.json())
    .then(data => {
        currentUserData = data.user;

        // Populate drawer - Handle avatar
        const avatarContainer = document.getElementById('drawer-avatar-container');
        const avatarImg = document.getElementById('drawer-avatar-img');
        const initialsSpan = document.getElementById('drawer-initials');

        if (data.user.avatar_url) {
            avatarContainer.classList.remove('placeholder');
            avatarImg.src = data.user.avatar_url;
            avatarImg.alt = data.user.full_name;
            avatarImg.classList.remove('hidden');
            initialsSpan.classList.add('hidden');
        } else {
            avatarContainer.classList.add('placeholder');
            avatarImg.classList.add('hidden');
            initialsSpan.classList.remove('hidden');
            initialsSpan.textContent = data.user.initials;
        }

        document.getElementById('drawer-name').textContent = data.user.full_name;
        document.getElementById('drawer-email').textContent = data.user.email;

        const roleBadge = document.getElementById('drawer-role-badge');
        roleBadge.textContent = data.user.role_label;
        roleBadge.className = `badge badge-${data.user.role_color}`;

        const statusBadge = document.getElementById('drawer-status-badge');
        statusBadge.textContent = data.user.status.charAt(0).toUpperCase() + data.user.status.slice(1);
        statusBadge.className = `badge badge-outline badge-${data.user.status === 'active' ? 'success' : data.user.status === 'invited' ? 'warning' : 'error'}`;

        // Show/hide company owner badge
        document.getElementById('drawer-owner-badge').classList.toggle('hidden', !data.user.is_company_owner);

        document.getElementById('drawer-description').textContent = data.user.description || 'No description';
        document.getElementById('drawer-timezone').textContent = data.user.timezone || 'UTC';
        document.getElementById('drawer-created').textContent = data.user.created_at;
        document.getElementById('drawer-last-login').textContent = data.user.last_login_at || 'Never';

        // Show/hide action buttons based on permissions
        const editBtn = document.getElementById('drawer-edit-btn');
        editBtn.classList.toggle('hidden', !data.user.can_edit);
        if (data.user.can_edit) {
            editBtn.href = `/users/${data.user.id}/edit`;
        }
        document.getElementById('drawer-delete-btn').classList.toggle('hidden', !data.user.can_delete);

        document.getElementById('drawer-loading').classList.add('hidden');
        document.getElementById('drawer-content').classList.remove('hidden');
    })
    .catch(error => {
        console.error('Error loading user:', error);
        closeUserDrawer();
    });
}

function closeUserDrawer() {
    document.getElementById('user-drawer').classList.add('translate-x-full');
    document.getElementById('user-drawer-overlay').classList.add('hidden');
    document.body.style.overflow = '';
}

async function confirmDeleteUser() {
    if (!currentUserId || !confirm('Are you sure you want to remove this user? This action cannot be undone.')) {
        return;
    }

    try {
        const response = await fetch(`/users/${currentUserId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
        });

        const data = await response.json();

        if (data.success) {
            closeUserDrawer();
            window.location.reload();
        } else {
            alert(data.error || 'Failed to remove user');
        }
    } catch (error) {
        alert('An error occurred. Please try again.');
    }
}

async function resendInvitation(userId) {
    if (!confirm('Resend invitation email to this user?')) {
        return;
    }

    try {
        const response = await fetch(`/users/${userId}/resend-invitation`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
        });

        const data = await response.json();

        if (data.success) {
            alert('Invitation email resent successfully!');
        } else {
            alert(data.error || 'Failed to resend invitation');
        }
    } catch (error) {
        alert('An error occurred. Please try again.');
    }
}

async function resendTeamInvitation(invitationId) {
    if (!confirm('Resend invitation email to this user?')) {
        return;
    }

    try {
        const response = await fetch(`/team-invitations/${invitationId}/resend`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
        });

        const data = await response.json();

        if (data.success) {
            alert('Invitation email resent successfully!');
        } else {
            alert(data.error || 'Failed to resend invitation');
        }
    } catch (error) {
        alert('An error occurred. Please try again.');
    }
}

async function cancelTeamInvitation(invitationId) {
    if (!confirm('Are you sure you want to cancel this invitation?')) {
        return;
    }

    try {
        const response = await fetch(`/team-invitations/${invitationId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
        });

        const data = await response.json();

        if (data.success) {
            window.location.reload();
        } else {
            alert(data.error || 'Failed to cancel invitation');
        }
    } catch (error) {
        alert('An error occurred. Please try again.');
    }
}
</script>
@endpush
@endsection
