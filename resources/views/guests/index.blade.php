@extends('layouts.app')

@section('content')
<div class="p-4 md:p-6">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-base-content">Guests</h1>
                <p class="text-base-content/60">Manage external guests with limited workspace access</p>
            </div>
            <a href="{{ route('guests.create') }}" class="btn btn-primary">
                <span class="icon-[tabler--plus] size-5"></span>
                Add Guest
            </a>
        </div>

        <!-- Filters -->
        <div class="card bg-base-100 shadow mb-6">
            <div class="card-body p-4">
                <form action="{{ route('guests.index') }}" method="GET" class="flex flex-col lg:flex-row gap-4">
                    <!-- Search -->
                    <div class="flex-1">
                        <div class="relative">
                            <span class="icon-[tabler--search] size-5 absolute left-3 top-1/2 -translate-y-1/2 text-base-content/50"></span>
                            <input type="text" name="search" value="{{ $search }}" placeholder="Search by name, email, or company..." class="input input-bordered w-full pl-10">
                        </div>
                    </div>

                    <!-- Status Filter -->
                    <select name="status" class="select select-bordered w-full lg:w-40">
                        <option value="">All Status</option>
                        @foreach($statuses as $key => $status)
                            <option value="{{ $key }}" {{ $currentStatus === $key ? 'selected' : '' }}>{{ $status['label'] }}</option>
                        @endforeach
                    </select>

                    <!-- Workspace Filter -->
                    <select name="workspace" class="select select-bordered w-full lg:w-48">
                        <option value="">All Workspaces</option>
                        @foreach($workspaces as $workspace)
                            <option value="{{ $workspace->id }}" {{ $currentWorkspace == $workspace->id ? 'selected' : '' }}>{{ $workspace->name }}</option>
                        @endforeach
                    </select>

                    <button type="submit" class="btn btn-neutral">
                        <span class="icon-[tabler--filter] size-5"></span>
                        Filter
                    </button>

                    @if($search || $currentStatus || $currentWorkspace)
                        <a href="{{ route('guests.index') }}" class="btn btn-ghost">
                            <span class="icon-[tabler--x] size-5"></span>
                            Clear
                        </a>
                    @endif
                </form>
            </div>
        </div>

        <!-- Status Tabs -->
        <div class="tabs tabs-bordered mb-6">
            <a href="{{ route('guests.index') }}" class="tab {{ !$currentStatus ? 'tab-active' : '' }}">
                All ({{ array_sum($statusCounts) }})
            </a>
            @foreach($statuses as $key => $status)
                <a href="{{ route('guests.index', ['status' => $key]) }}" class="tab {{ $currentStatus === $key ? 'tab-active' : '' }}">
                    {{ $status['label'] }} ({{ $statusCounts[$key] ?? 0 }})
                </a>
            @endforeach
        </div>

        <!-- Guests Table -->
        <div class="card bg-base-100 shadow">
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Guest</th>
                            <th>Company</th>
                            <th>Workspaces</th>
                            <th>Status</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($guests as $guest)
                            <tr class="hover">
                                <td>
                                    <div class="flex items-center gap-3">
                                        @include('partials.user-avatar', ['user' => $guest, 'size' => 'md'])
                                        <div>
                                            <div class="font-medium">{{ $guest->full_name }}</div>
                                            <div class="text-sm text-base-content/60">{{ $guest->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($guest->guest_company_name)
                                        <div>{{ $guest->guest_company_name }}</div>
                                        @if($guest->guest_position)
                                            <div class="text-xs text-base-content/50">{{ $guest->guest_position }}</div>
                                        @endif
                                    @else
                                        <span class="text-base-content/40">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($guest->guestWorkspaces->count() > 0)
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($guest->guestWorkspaces->take(2) as $ws)
                                                <span class="badge badge-ghost badge-sm">{{ $ws->name }}</span>
                                            @endforeach
                                            @if($guest->guestWorkspaces->count() > 2)
                                                <span class="badge badge-ghost badge-sm">+{{ $guest->guestWorkspaces->count() - 2 }}</span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-base-content/40">None</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'active' => 'success',
                                            'invited' => 'warning',
                                            'suspended' => 'error',
                                        ];
                                    @endphp
                                    <span class="badge badge-{{ $statusColors[$guest->status] ?? 'ghost' }}">{{ ucfirst($guest->status) }}</span>
                                </td>
                                <td>
                                    <div class="flex items-center justify-end gap-1">
                                        @if($guest->status === 'invited')
                                            <button type="button" class="btn btn-ghost btn-sm btn-square text-info" onclick="resendInvitation({{ $guest->id }}, '{{ $guest->full_name }}')" title="Resend Invitation">
                                                <span class="icon-[tabler--send] size-4"></span>
                                            </button>
                                        @endif
                                        <button type="button" class="btn btn-ghost btn-sm btn-square" onclick="viewGuest({{ $guest->id }})" title="View Details">
                                            <span class="icon-[tabler--eye] size-4"></span>
                                        </button>
                                        <a href="{{ route('guests.edit', $guest) }}" class="btn btn-ghost btn-sm btn-square" title="Edit">
                                            <span class="icon-[tabler--edit] size-4"></span>
                                        </a>
                                        <button type="button" class="btn btn-ghost btn-sm btn-square text-error" onclick="confirmDelete({{ $guest->id }}, '{{ $guest->full_name }}')" title="Delete">
                                            <span class="icon-[tabler--trash] size-4"></span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-12">
                                    <div class="flex flex-col items-center">
                                        <div class="size-16 rounded-full bg-base-200 flex items-center justify-center mb-4">
                                            <span class="icon-[tabler--users] size-8 text-base-content/30"></span>
                                        </div>
                                        <p class="text-base-content/60 mb-4">No guests found</p>
                                        <a href="{{ route('guests.create') }}" class="btn btn-primary btn-sm">
                                            <span class="icon-[tabler--plus] size-4"></span>
                                            Add Your First Guest
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($guests->hasPages())
                <div class="card-body border-t border-base-200 pt-4">
                    {{ $guests->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Guest Detail Drawer -->
<div id="guest-drawer-backdrop" class="fixed inset-0 bg-black/50 z-[200] hidden" onclick="closeGuestDrawer()"></div>
<div id="guest-drawer" class="fixed top-0 right-0 h-full w-full max-w-md bg-base-100 shadow-xl z-[201] transform translate-x-full transition-transform duration-300">
    <div class="flex flex-col h-full">
        <!-- Drawer Header -->
        <div class="flex items-center justify-between p-4 border-b border-base-200">
            <h3 class="text-lg font-semibold">Guest Details</h3>
            <button type="button" class="btn btn-ghost btn-sm btn-square" onclick="closeGuestDrawer()">
                <span class="icon-[tabler--x] size-5"></span>
            </button>
        </div>

        <!-- Drawer Content -->
        <div id="guest-drawer-content" class="flex-1 overflow-y-auto p-4">
            <div class="flex justify-center py-8">
                <span class="loading loading-spinner loading-lg"></span>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<dialog id="delete-modal" class="modal">
    <div class="modal-box">
        <h3 class="font-bold text-lg">Delete Guest</h3>
        <p class="py-4">Are you sure you want to delete <strong id="delete-guest-name"></strong>? This action cannot be undone.</p>
        <div class="modal-action">
            <form method="dialog">
                <button class="btn btn-ghost">Cancel</button>
            </form>
            <button type="button" id="confirm-delete-btn" class="btn btn-error">Delete</button>
        </div>
    </div>
    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</dialog>
@endsection

@push('scripts')
<script>
let currentDeleteId = null;

function viewGuest(id) {
    document.getElementById('guest-drawer-backdrop').classList.remove('hidden');
    document.getElementById('guest-drawer').classList.remove('translate-x-full');
    document.getElementById('guest-drawer-content').innerHTML = '<div class="flex justify-center py-8"><span class="loading loading-spinner loading-lg"></span></div>';

    fetch(`/guests/${id}`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.guest) {
            renderGuestDetails(data.guest);
        }
    })
    .catch(error => {
        document.getElementById('guest-drawer-content').innerHTML = '<div class="text-center py-8 text-error">Failed to load guest details</div>';
    });
}

function renderGuestDetails(guest) {
    const workspacesHtml = guest.workspaces && guest.workspaces.length > 0
        ? guest.workspaces.map(ws => `<span class="badge badge-ghost badge-sm">${ws.name}</span>`).join('')
        : '<span class="text-base-content/50">None assigned</span>';

    const statusColors = {
        'active': 'success',
        'invited': 'warning',
        'suspended': 'error'
    };

    const html = `
        <div class="text-center mb-6">
            ${guest.avatar_url
                ? `<img src="${guest.avatar_url}" alt="${guest.full_name}" class="w-20 h-20 rounded-full mx-auto mb-3 object-cover">`
                : `<div class="w-20 h-20 rounded-full bg-neutral text-neutral-content flex items-center justify-center mx-auto mb-3">
                    <span class="text-2xl font-bold">${guest.initials}</span>
                   </div>`
            }
            <h4 class="text-xl font-semibold">${guest.full_name}</h4>
            <p class="text-base-content/60">${guest.email}</p>
            ${guest.position ? `<p class="text-sm text-base-content/50">${guest.position}</p>` : ''}
        </div>

        <div class="space-y-4">
            <div class="flex items-center justify-between py-2 border-b border-base-200">
                <span class="text-base-content/60">Status</span>
                <span class="badge badge-${statusColors[guest.status] || 'ghost'}">${guest.status_label}</span>
            </div>
            ${guest.company_name ? `
            <div class="flex items-center justify-between py-2 border-b border-base-200">
                <span class="text-base-content/60">Company</span>
                <span class="font-medium">${guest.company_name}</span>
            </div>
            ` : ''}
            ${guest.phone ? `
            <div class="flex items-center justify-between py-2 border-b border-base-200">
                <span class="text-base-content/60">Phone</span>
                <span class="font-medium">${guest.phone}</span>
            </div>
            ` : ''}
            <div class="py-2 border-b border-base-200">
                <span class="text-base-content/60 block mb-2">Workspaces</span>
                <div class="flex flex-wrap gap-1">${workspacesHtml}</div>
            </div>
            ${guest.notes ? `
            <div class="py-2 border-b border-base-200">
                <span class="text-base-content/60 block mb-2">Notes</span>
                <p class="text-sm">${guest.notes}</p>
            </div>
            ` : ''}
            <div class="flex items-center justify-between py-2 border-b border-base-200">
                <span class="text-base-content/60">Added</span>
                <span class="text-sm">${guest.created_at}</span>
            </div>
        </div>

        <div class="mt-6 flex gap-2">
            <a href="/guests/${guest.id}/edit" class="btn btn-primary flex-1">
                <span class="icon-[tabler--edit] size-4"></span>
                Edit
            </a>
        </div>
    `;

    document.getElementById('guest-drawer-content').innerHTML = html;
}

function closeGuestDrawer() {
    document.getElementById('guest-drawer-backdrop').classList.add('hidden');
    document.getElementById('guest-drawer').classList.add('translate-x-full');
}

function confirmDelete(id, name) {
    currentDeleteId = id;
    document.getElementById('delete-guest-name').textContent = name;
    document.getElementById('delete-modal').showModal();
}

document.getElementById('confirm-delete-btn').addEventListener('click', function() {
    if (!currentDeleteId) return;

    fetch(`/guests/${currentDeleteId}`, {
        method: 'DELETE',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message || 'Guest deleted successfully!', 'success');
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showToast(data.error || 'Failed to delete guest', 'error');
        }
    })
    .catch(error => {
        showToast('An error occurred. Please try again.', 'error');
    });

    document.getElementById('delete-modal').close();
});

// Close drawer on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeGuestDrawer();
    }
});

// Resend invitation
function resendInvitation(id, name) {
    if (!confirm(`Resend invitation email to ${name}?`)) {
        return;
    }

    const btn = event.target.closest('button');
    const originalHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="loading loading-spinner loading-xs"></span>';

    fetch(`/guests/${id}/resend-invitation`, {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message || 'Invitation sent successfully!', 'success');
        } else {
            showToast(data.error || 'Failed to send invitation', 'error');
        }
    })
    .catch(error => {
        showToast('An error occurred. Please try again.', 'error');
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalHtml;
    });
}
</script>
@endpush
