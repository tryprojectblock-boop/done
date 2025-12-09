@extends('admin::layouts.app')

@section('title', $company->name)
@section('page-title', 'Client Details')

@section('content')
<div class="space-y-6">
    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-sm">
        <a href="{{ route('backoffice.clients.index') }}" class="text-base-content/60 hover:text-primary">Clients</a>
        <span class="icon-[tabler--chevron-right] size-4 text-base-content/40"></span>
        <span class="text-base-content font-medium">{{ $company->name }}</span>
    </nav>

    @include('admin::partials.alerts')

    <!-- Row 1: Three Cards -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Card 1: Company Information -->
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <h3 class="card-title text-lg mb-4">
                    <span class="icon-[tabler--building] size-5"></span>
                    Company Information
                </h3>
                <div class="flex items-center gap-4 mb-4">
                    <div class="avatar placeholder">
                        <div class="bg-primary text-primary-content rounded-lg w-14 h-14">
                            <span class="text-xl">{{ strtoupper(substr($company->name, 0, 2)) }}</span>
                        </div>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold">{{ $company->name }}</h2>
                        <div class="flex items-center gap-2 mt-1">
                            @if($company->paused_at ?? false)
                                <span class="badge badge-warning badge-sm">Paused</span>
                            @else
                                <span class="badge badge-success badge-sm">Active</span>
                            @endif
                            @if($company->isOnTrial())
                                <span class="badge badge-outline badge-sm">Trial</span>
                            @endif
                        </div>
                    </div>
                </div>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-base-content/60">Industry</dt>
                        <dd>{{ $company->industry_type?->label() ?? '-' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-base-content/60">Company Size</dt>
                        <dd>{{ $company->size?->label() ?? '-' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-base-content/60">Website</dt>
                        <dd>
                            @if($company->website_url)
                                <a href="{{ $company->website_url }}" target="_blank" class="link link-primary">{{ parse_url($company->website_url, PHP_URL_HOST) }}</a>
                            @else
                                -
                            @endif
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-base-content/60">Created</dt>
                        <dd>{{ $company->created_at->format('M d, Y') }}</dd>
                    </div>
                    @if($company->isOnTrial())
                    <div class="flex justify-between">
                        <dt class="text-base-content/60">Trial Ends</dt>
                        <dd class="text-warning">{{ $company->trialDaysRemaining() }} days left</dd>
                    </div>
                    @endif
                </dl>
            </div>
        </div>

        <!-- Card 2: Owner Information -->
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <h3 class="card-title text-lg mb-4">
                    <span class="icon-[tabler--user] size-5"></span>
                    Owner Information
                </h3>
                @if($company->owner)
                    <div class="flex items-center gap-4 mb-4">
                        <div class="avatar">
                            <div class="w-14 h-14 rounded-full">
                                <img src="{{ $company->owner->avatar_url }}" alt="{{ $company->owner->name }}" />
                            </div>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold">{{ $company->owner->name }}</h2>
                            <div class="text-sm text-base-content/60">{{ $company->owner->email }}</div>
                        </div>
                    </div>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-base-content/60">Phone</dt>
                            <dd>{{ $company->owner->phone ?? '-' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-base-content/60">Status</dt>
                            <dd>
                                <span class="badge {{ $company->owner->status === 'active' ? 'badge-success' : 'badge-warning' }} badge-sm">
                                    {{ ucfirst($company->owner->status ?? 'active') }}
                                </span>
                            </dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-base-content/60">Role</dt>
                            <dd>
                                <span class="badge badge-{{ $company->owner->role_color ?? 'primary' }} badge-sm">
                                    {{ $company->owner->role_label ?? 'Owner' }}
                                </span>
                            </dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-base-content/60">Joined</dt>
                            <dd>{{ $company->owner->created_at->format('M d, Y') }}</dd>
                        </div>
                    </dl>
                @else
                    <div class="flex items-center justify-center h-32">
                        <p class="text-base-content/50">No owner assigned</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Card 3: Actions -->
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <h3 class="card-title text-lg mb-4">
                    <span class="icon-[tabler--settings] size-5"></span>
                    Actions
                </h3>
                <div class="flex flex-col gap-3">
                    @if($company->isPaused())
                        <form action="{{ route('backoffice.clients.activate', $company) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success w-full">
                                <span class="icon-[tabler--player-play] size-5"></span>
                                Activate Account
                            </button>
                        </form>
                    @else
                        <button type="button" class="btn btn-warning w-full" id="open-pause-modal">
                            <span class="icon-[tabler--player-pause] size-5"></span>
                            Pause Account
                        </button>
                    @endif
                    <a href="{{ route('backoffice.clients.edit', $company) }}" class="btn btn-outline w-full">
                        <span class="icon-[tabler--edit] size-5"></span>
                        Edit Information
                    </a>
                    <button type="button" class="btn btn-outline w-full" id="open-send-email-modal">
                        <span class="icon-[tabler--mail] size-5"></span>
                        Send Email
                    </button>

                    <!-- Danger Zone -->
                    <div class="divider text-error text-xs">Danger Zone</div>
                    <button type="button" class="btn btn-outline btn-error w-full" id="open-delete-data-modal">
                        <span class="icon-[tabler--database-off] size-5"></span>
                        Delete Data
                    </button>
                    <button type="button" class="btn btn-error w-full" id="open-delete-account-modal">
                        <span class="icon-[tabler--trash] size-5"></span>
                        Delete Account
                    </button>
                </div>

                @if($company->isPaused())
                <!-- Pause Info -->
                <div class="divider"></div>
                <div class="alert alert-warning">
                    <span class="icon-[tabler--alert-triangle] size-5"></span>
                    <div>
                        <h4 class="font-bold">Account Paused</h4>
                        <p class="text-sm">{{ $company->pause_reason }}</p>
                        @if($company->pause_description)
                            <p class="text-xs mt-1 opacity-70">{{ $company->pause_description }}</p>
                        @endif
                        <p class="text-xs mt-2 opacity-60">Paused on {{ $company->paused_at->format('M d, Y H:i') }}</p>
                    </div>
                </div>
                @endif

                <!-- Quick Stats -->
                <div class="divider"></div>
                <h4 class="font-medium text-sm text-base-content/70 mb-3">Quick Stats</h4>
                <div class="grid grid-cols-2 gap-2">
                    <div class="bg-base-200 rounded-lg p-3 text-center">
                        <div class="text-2xl font-bold">{{ $stats['workspaces'] }}</div>
                        <div class="text-xs text-base-content/60">Workspaces</div>
                    </div>
                    <div class="bg-base-200 rounded-lg p-3 text-center">
                        <div class="text-2xl font-bold">{{ $stats['team_members'] }}</div>
                        <div class="text-xs text-base-content/60">Members</div>
                    </div>
                    <div class="bg-base-200 rounded-lg p-3 text-center">
                        <div class="text-2xl font-bold">{{ $stats['tasks'] }}</div>
                        <div class="text-xs text-base-content/60">Tasks</div>
                    </div>
                    <div class="bg-base-200 rounded-lg p-3 text-center">
                        <div class="text-2xl font-bold">{{ $stats['files'] }}</div>
                        <div class="text-xs text-base-content/60">Files</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 2: Tabs Navigation Card -->
    <div class="card bg-base-100 shadow">
        <div class="card-body pb-0">
            <div class="flex gap-2 border-b border-base-200 -mx-6 px-6">
                <button type="button" class="tab-btn px-4 py-3 text-sm font-medium border-b-2 transition-colors border-primary text-primary" data-tab="workspaces">
                    <span class="icon-[tabler--briefcase] size-4 inline-block mr-1.5 align-middle"></span>
                    Workspaces
                </button>
                <button type="button" class="tab-btn px-4 py-3 text-sm font-medium border-b-2 transition-colors border-transparent text-base-content/70 hover:text-base-content" data-tab="members">
                    <span class="icon-[tabler--users] size-4 inline-block mr-1.5 align-middle"></span>
                    Team Members
                </button>
                <button type="button" class="tab-btn px-4 py-3 text-sm font-medium border-b-2 transition-colors border-transparent text-base-content/70 hover:text-base-content" data-tab="tasks">
                    <span class="icon-[tabler--checkbox] size-4 inline-block mr-1.5 align-middle"></span>
                    Tasks
                </button>
                <button type="button" class="tab-btn px-4 py-3 text-sm font-medium border-b-2 transition-colors border-transparent text-base-content/70 hover:text-base-content" data-tab="files">
                    <span class="icon-[tabler--file] size-4 inline-block mr-1.5 align-middle"></span>
                    Files
                </button>
            </div>
        </div>

        <!-- Tab Contents Inside Same Card -->
        <!-- Workspaces Content -->
        <div id="tab-workspaces" class="tab-content">
            <div class="card-body pt-4">
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Workspace</th>
                                <th>Type</th>
                                <th>Members</th>
                                <th>Status</th>
                                <th>Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($workspaces as $workspace)
                                <tr>
                                    <td>
                                        <div class="flex items-center gap-2">
                                            <div class="avatar placeholder">
                                                <div class="bg-info text-info-content rounded w-8 h-8">
                                                    <span class="text-xs">{{ strtoupper(substr($workspace->name, 0, 2)) }}</span>
                                                </div>
                                            </div>
                                            <span class="font-medium">{{ $workspace->name }}</span>
                                        </div>
                                    </td>
                                    <td><span class="badge badge-outline badge-sm">{{ $workspace->type?->value ?? '-' }}</span></td>
                                    <td><span class="badge badge-ghost">{{ $workspace->members_count }}</span></td>
                                    <td>
                                        <span class="badge {{ $workspace->isActive() ? 'badge-success' : 'badge-warning' }} badge-sm">
                                            {{ $workspace->status?->value ?? 'active' }}
                                        </span>
                                    </td>
                                    <td class="text-sm text-base-content/60">{{ $workspace->created_at->format('M d, Y') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-base-content/50 py-8">No workspaces found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Team Members Content -->
        <div id="tab-members" class="tab-content hidden">
            <div class="card-body pt-4">
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Joined</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($teamMembers as $member)
                                <tr>
                                    <td>
                                        <div class="flex items-center gap-2">
                                            <div class="avatar">
                                                <div class="w-8 h-8 rounded-full">
                                                    <img src="{{ $member->avatar_url }}" alt="{{ $member->name }}" />
                                                </div>
                                            </div>
                                            <span class="font-medium">{{ $member->name }}</span>
                                        </div>
                                    </td>
                                    <td>{{ $member->email }}</td>
                                    <td><span class="badge badge-{{ $member->role_color ?? 'ghost' }} badge-sm">{{ $member->role_label ?? 'Member' }}</span></td>
                                    <td>
                                        <span class="badge {{ $member->status === 'active' ? 'badge-success' : 'badge-warning' }} badge-sm">
                                            {{ ucfirst($member->status ?? 'active') }}
                                        </span>
                                    </td>
                                    <td class="text-sm text-base-content/60">{{ $member->created_at->format('M d, Y') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-base-content/50 py-8">No team members found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($teamMembers->hasPages())
                    <div class="mt-4">{{ $teamMembers->links() }}</div>
                @endif
            </div>
        </div>

        <!-- Tasks Content -->
        <div id="tab-tasks" class="tab-content hidden">
            <div class="card-body pt-4">
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Task</th>
                                <th>Creator</th>
                                <th>Assignee</th>
                                <th>Status</th>
                                <th>Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tasks as $task)
                                <tr>
                                    <td>
                                        <div class="font-medium">{{ Str::limit($task->title, 40) }}</div>
                                        <div class="text-xs text-base-content/60">{{ $task->task_number ?? '' }}</div>
                                    </td>
                                    <td>{{ $task->creator?->name ?? '-' }}</td>
                                    <td>{{ $task->assignee?->name ?? '-' }}</td>
                                    <td>
                                        @if($task->status)
                                            <span class="badge badge-sm" style="background-color: {{ $task->status->color ?? '#6b7280' }}; color: white;">
                                                {{ $task->status->name }}
                                            </span>
                                        @else
                                            <span class="badge badge-outline badge-sm">-</span>
                                        @endif
                                    </td>
                                    <td class="text-sm text-base-content/60">{{ $task->created_at->format('M d, Y') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-base-content/50 py-8">No tasks found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($tasks->hasPages())
                    <div class="mt-4">{{ $tasks->links() }}</div>
                @endif
            </div>
        </div>

        <!-- Files Content -->
        <div id="tab-files" class="tab-content hidden">
            <div class="card-body pt-4">
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>File Name</th>
                                <th>Uploaded By</th>
                                <th>Size</th>
                                <th>Uploaded</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($files as $file)
                                <tr>
                                    <td>
                                        <div class="flex items-center gap-2">
                                            <span class="icon-[tabler--file] size-5 text-base-content/60"></span>
                                            <span class="font-medium">{{ $file->name ?? $file->original_name ?? '-' }}</span>
                                        </div>
                                    </td>
                                    <td>{{ $file->uploadedBy?->name ?? '-' }}</td>
                                    <td>{{ isset($file->size) ? number_format($file->size / 1024, 2) . ' KB' : '-' }}</td>
                                    <td class="text-sm text-base-content/60">{{ $file->created_at->format('M d, Y') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-base-content/50 py-8">No files found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($files->hasPages())
                    <div class="mt-4">{{ $files->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab functionality
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');

    tabBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const tabId = this.dataset.tab;

            // Update button states
            tabBtns.forEach(b => {
                b.classList.remove('border-primary', 'text-primary');
                b.classList.add('border-transparent', 'text-base-content/70');
            });
            this.classList.remove('border-transparent', 'text-base-content/70');
            this.classList.add('border-primary', 'text-primary');

            // Show/hide content
            tabContents.forEach(content => {
                if (content.id === 'tab-' + tabId) {
                    content.classList.remove('hidden');
                } else {
                    content.classList.add('hidden');
                }
            });
        });
    });

    // Generic Modal functionality
    function openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) modal.classList.add('open');
    }

    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) modal.classList.remove('open');
    }

    // Setup modal open buttons
    const modalButtons = {
        'open-pause-modal': 'pause-modal',
        'open-send-email-modal': 'send-email-modal',
        'open-delete-data-modal': 'delete-data-modal',
        'open-delete-account-modal': 'delete-account-modal'
    };

    Object.entries(modalButtons).forEach(([btnId, modalId]) => {
        const btn = document.getElementById(btnId);
        if (btn) {
            btn.addEventListener('click', () => openModal(modalId));
        }
    });

    // Pause modal cancel button (uses different pattern)
    const pauseCancelBtn = document.getElementById('pause-modal-cancel');
    if (pauseCancelBtn) {
        pauseCancelBtn.addEventListener('click', () => closeModal('pause-modal'));
    }

    // Pause modal backdrop
    const pauseBackdrop = document.getElementById('pause-modal-backdrop');
    if (pauseBackdrop) {
        pauseBackdrop.addEventListener('click', () => closeModal('pause-modal'));
    }

    // Generic close buttons and backdrops using data attributes
    document.querySelectorAll('[data-modal-close]').forEach(el => {
        el.addEventListener('click', function() {
            closeModal(this.dataset.modalClose);
        });
    });
});
</script>
@endpush

<!-- Pause Account Modal -->
<div id="pause-modal" class="pause-modal">
    <div class="pause-modal-backdrop" id="pause-modal-backdrop"></div>
    <div class="pause-modal-box bg-base-100 rounded-lg shadow-xl p-6 max-w-md w-full mx-4">
        <h3 class="font-bold text-lg text-warning flex items-center gap-2 mb-2">
            <span class="icon-[tabler--player-pause] size-6"></span>
            <span>Pause Account</span>
        </h3>
        <p class="text-sm text-base-content/70 mb-4">
            Are you sure you want to pause "<strong>{{ $company->name }}</strong>"?
        </p>
        <p class="text-sm text-base-content/60 mb-4">
            When paused, the owner and all team members will see a notification when they try to log in.
        </p>
        <form action="{{ route('backoffice.clients.pause', $company) }}" method="POST">
            @csrf
            <div class="form-control mb-4">
                <label class="label" for="pause-reason">
                    <span class="label-text font-medium">Pause Reason <span class="text-error">*</span></span>
                </label>
                <select name="pause_reason" id="pause-reason" class="select select-bordered w-full" required>
                    <option value="">Select a reason...</option>
                    <option value="Payment overdue">Payment overdue</option>
                    <option value="Subscription expired">Subscription expired</option>
                    <option value="Terms of service violation">Terms of service violation</option>
                    <option value="Suspicious activity">Suspicious activity</option>
                    <option value="User request">User request</option>
                    <option value="Account review">Account review</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <div class="form-control mb-4">
                <label class="label" for="pause-description">
                    <span class="label-text font-medium">Additional Details</span>
                    <span class="label-text-alt">Optional</span>
                </label>
                <textarea name="pause_description" id="pause-description" class="textarea textarea-bordered w-full h-24" placeholder="Enter any additional details about this pause..."></textarea>
            </div>
            <div class="flex justify-end gap-2 mt-6">
                <button type="button" class="btn btn-ghost" id="pause-modal-cancel">Cancel</button>
                <button type="submit" class="btn btn-warning">
                    <span class="icon-[tabler--player-pause] size-5"></span>
                    Pause Account
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.pause-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 9999;
    justify-content: center;
    align-items: center;
}
.pause-modal.open {
    display: flex !important;
}
.pause-modal-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1;
}
.pause-modal-box {
    position: relative;
    z-index: 2;
}
</style>

<!-- Send Email Modal -->
<div id="send-email-modal" class="action-modal">
    <div class="action-modal-backdrop" data-modal-close="send-email-modal"></div>
    <div class="action-modal-box bg-base-100 rounded-lg shadow-xl p-6 max-w-md w-full mx-4">
        <h3 class="font-bold text-lg flex items-center gap-2 mb-4">
            <span class="icon-[tabler--mail] size-6 text-primary"></span>
            <span>Send Email to Client</span>
        </h3>
        <form action="{{ route('backoffice.clients.send-email', $company) }}" method="POST">
            @csrf
            <div class="form-control mb-4">
                <label class="label" for="email-to">
                    <span class="label-text">To</span>
                </label>
                <input type="text" id="email-to" value="{{ $company->owner?->email ?? 'N/A' }}" class="input input-bordered" disabled />
            </div>
            <div class="form-control mb-4">
                <label class="label" for="email-subject">
                    <span class="label-text">Subject</span>
                </label>
                <input type="text" name="subject" id="email-subject" class="input input-bordered" required />
            </div>
            <div class="form-control mb-4">
                <label class="label" for="email-message">
                    <span class="label-text">Message</span>
                </label>
                <textarea name="message" id="email-message" class="textarea textarea-bordered h-32" required></textarea>
            </div>
            <div class="flex justify-end gap-2 mt-6">
                <button type="button" class="btn btn-ghost" data-modal-close="send-email-modal">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <span class="icon-[tabler--send] size-4"></span>
                    Send Email
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Data Modal -->
<div id="delete-data-modal" class="action-modal">
    <div class="action-modal-backdrop" data-modal-close="delete-data-modal"></div>
    <div class="action-modal-box bg-base-100 rounded-lg shadow-xl p-6 max-w-md w-full mx-4">
        <h3 class="font-bold text-lg text-error flex items-center gap-2 mb-2">
            <span class="icon-[tabler--database-off] size-6"></span>
            <span>Delete Company Data</span>
        </h3>
        <p class="text-sm text-base-content/70 mb-4">
            Are you sure you want to delete all data for "<strong>{{ $company->name }}</strong>"?
        </p>
        <div class="alert alert-warning mb-4">
            <span class="icon-[tabler--alert-triangle] size-5"></span>
            <div>
                <p class="text-sm font-medium">This will permanently delete:</p>
                <ul class="text-sm mt-1 list-disc list-inside">
                    <li>All workspaces ({{ $stats['workspaces'] }})</li>
                    <li>All tasks ({{ $stats['tasks'] }})</li>
                    <li>All files ({{ $stats['files'] }})</li>
                    <li>All discussions and comments</li>
                </ul>
            </div>
        </div>
        <p class="text-sm text-base-content/60 mb-4">
            The company account and team members will remain intact.
        </p>
        <form action="{{ route('backoffice.clients.delete-data', $company) }}" method="POST">
            @csrf
            @method('DELETE')
            <div class="flex justify-end gap-2">
                <button type="button" class="btn btn-ghost" data-modal-close="delete-data-modal">Cancel</button>
                <button type="submit" class="btn btn-error">
                    <span class="icon-[tabler--database-off] size-5"></span>
                    Yes, Delete All Data
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Account Modal -->
<div id="delete-account-modal" class="action-modal">
    <div class="action-modal-backdrop" data-modal-close="delete-account-modal"></div>
    <div class="action-modal-box bg-base-100 rounded-lg shadow-xl p-6 max-w-md w-full mx-4">
        <h3 class="font-bold text-lg text-error flex items-center gap-2 mb-2">
            <span class="icon-[tabler--trash] size-6"></span>
            <span>Delete Account</span>
        </h3>
        <p class="text-sm text-base-content/70 mb-4">
            Are you sure you want to permanently delete "<strong>{{ $company->name }}</strong>"?
        </p>
        <div class="alert alert-error mb-4">
            <span class="icon-[tabler--alert-octagon] size-5"></span>
            <div>
                <p class="text-sm font-medium">This action is irreversible!</p>
                <p class="text-sm mt-1">This will permanently delete:</p>
                <ul class="text-sm mt-1 list-disc list-inside">
                    <li>The company account</li>
                    <li>All team members ({{ $stats['team_members'] }})</li>
                    <li>All workspaces ({{ $stats['workspaces'] }})</li>
                    <li>All tasks, files, and data</li>
                </ul>
            </div>
        </div>
        <form action="{{ route('backoffice.clients.destroy', $company) }}" method="POST">
            @csrf
            @method('DELETE')
            <div class="form-control mb-4">
                <label class="label" for="delete-confirmation">
                    <span class="label-text font-medium">Type <span class="text-error font-bold">{{ $company->name }}</span> to confirm</span>
                </label>
                <input type="text" name="confirmation" id="delete-confirmation" class="input input-bordered" placeholder="Enter company name" required pattern="{{ preg_quote($company->name, '/') }}" />
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" class="btn btn-ghost" data-modal-close="delete-account-modal">Cancel</button>
                <button type="submit" class="btn btn-error">
                    <span class="icon-[tabler--trash] size-5"></span>
                    Delete Permanently
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.action-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 9999;
    justify-content: center;
    align-items: center;
}
.action-modal.open {
    display: flex !important;
}
.action-modal-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1;
}
.action-modal-box {
    position: relative;
    z-index: 2;
}
</style>
@endsection
