@extends('layouts.app')

@section('content')
<div class="p-4 md:p-6">
    <div class="max-w mx-auto">
        <!-- Page Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-base-content">Calendar</h1>
                <p class="text-base-content/60">View and manage task schedules</p>
            </div>

            <div class="flex items-center gap-2">
                <!-- View Toggle -->
                <div class="join">
                    <a href="{{ route('calendar.index', array_merge(request()->query(), ['view' => 'list'])) }}"
                       class="join-item btn btn-sm {{ $view === 'list' ? 'btn-primary' : 'btn-ghost' }}">
                        <span class="icon-[tabler--list] size-4"></span>
                        <span class="hidden sm:inline">List</span>
                    </a>
                    <a href="{{ route('calendar.index', array_merge(request()->query(), ['view' => 'calendar'])) }}"
                       class="join-item btn btn-sm {{ $view === 'calendar' ? 'btn-primary' : 'btn-ghost' }}">
                        <span class="icon-[tabler--calendar] size-4"></span>
                        <span class="hidden sm:inline">Calendar</span>
                    </a>
                </div>

                @if(auth()->user()->canSyncGoogleCalendar())
                <!-- Sync with Google Button -->
                <button type="button" onclick="syncGoogleCalendar()" class="btn btn-ghost btn-sm" id="googleSyncBtn">
                    <span class="icon-[tabler--brand-google] size-4"></span>
                    <span class="hidden sm:inline">Sync Google</span>
                </button>
                @elseif(auth()->user()->companyHasGoogleSyncEnabled() && !auth()->user()->hasGoogleConnected())
                <!-- Connect Google Button -->
                <a href="{{ route('google.connect') }}" class="btn btn-ghost btn-sm">
                    <span class="icon-[tabler--brand-google] size-4"></span>
                    <span class="hidden sm:inline">Connect Google</span>
                </a>
                @endif

                <!-- Add Task Button -->
                <a href="{{ route('tasks.create') }}" class="btn btn-primary btn-sm">
                    <span class="icon-[tabler--plus] size-4"></span>
                    <span class="hidden sm:inline">Add Task</span>
                </a>
            </div>
        </div>

        <!-- Tabs: All / Overdue -->
        <div class="inline-flex p-1 bg-base-200 rounded-xl mb-6">
            <a href="{{ route('calendar.index', array_merge(request()->except('tab'), ['tab' => 'all'])) }}"
               class="flex items-center gap-2 px-5 py-2.5 rounded-lg font-medium transition-all duration-200 {{ ($tab ?? 'all') === 'all' ? 'bg-primary text-primary-content shadow-sm' : 'text-base-content/60 hover:text-primary hover:bg-primary/10' }}">
                <span class="icon-[tabler--calendar-event] size-5"></span>
                <span>All</span>
                <span class="badge badge-sm {{ ($tab ?? 'all') === 'all' ? 'bg-primary-content/20 text-primary-content border-0' : '' }}">{{ $stats['all'] }}</span>
            </a>
            <a href="{{ route('calendar.index', array_merge(request()->except('tab'), ['tab' => 'overdue'])) }}"
               class="flex items-center gap-2 px-5 py-2.5 rounded-lg font-medium transition-all duration-200 {{ ($tab ?? 'all') === 'overdue' ? 'bg-error text-error-content shadow-sm' : 'text-base-content/60 hover:text-error hover:bg-error/10' }}">
                <span class="icon-[tabler--alert-triangle] size-5"></span>
                <span>Overdue</span>
                <span class="badge badge-sm {{ ($tab ?? 'all') === 'overdue' ? 'bg-error-content/20 text-error-content border-0' : 'badge-error' }}">{{ $stats['overdue'] }}</span>
            </a>
        </div>

        <!-- Filters -->
        <div class="card bg-base-100 shadow mb-6">
            <div class="card-body p-4">
                <form action="{{ route('calendar.index') }}" method="GET" id="calendar-filter-form" class="flex flex-wrap items-center gap-3">
                    <input type="hidden" name="tab" value="{{ $tab ?? 'all' }}">
                    <input type="hidden" name="view" value="{{ $view }}">

                    <!-- Workspace Filter -->
                    <input type="hidden" name="workspace_id" id="calendar-workspace-filter-value" value="{{ $filters['workspace_id'] ?? '' }}" />
                    <div class="relative">
                        <button type="button" id="calendar-workspace-filter-btn" onclick="toggleCalendarWorkspaceDropdown()" class="btn btn-ghost btn-sm gap-2 border border-base-300 min-w-48">
                            <span class="icon-[tabler--layout-grid] size-4"></span>
                            <span id="calendar-workspace-filter-label">
                                @if(!empty($filters['workspace_id']))
                                    {{ $workspaces->firstWhere('id', $filters['workspace_id'])?->name ?? 'All Workspaces' }}
                                @else
                                    All Workspaces
                                @endif
                            </span>
                            <span class="icon-[tabler--chevron-down] size-4"></span>
                        </button>
                        <div id="calendar-workspace-dropdown" class="hidden absolute left-0 top-full mt-2 w-64 bg-base-100 rounded-xl shadow-xl border border-base-300 z-[100]">
                            <div class="p-2 border-b border-base-200">
                                <div class="relative">
                                    <span class="icon-[tabler--search] size-4 absolute left-3 top-1/2 -translate-y-1/2 text-base-content/40"></span>
                                    <input type="text" id="calendar-workspace-search" placeholder="Search workspaces..." class="input input-sm input-bordered w-full pl-9" oninput="searchCalendarWorkspaces(this.value)" autocomplete="off" />
                                </div>
                            </div>
                            <ul class="p-2 max-h-64 overflow-y-auto" id="calendar-workspace-list">
                                <li class="calendar-workspace-option" data-name="all">
                                    <button type="button" onclick="selectCalendarWorkspace('', 'All Workspaces')" class="w-full text-left px-3 py-2 rounded-lg hover:bg-base-200 flex items-center gap-2">
                                        <span class="icon-[tabler--layout-grid] size-4 text-base-content/60"></span>
                                        <span>All Workspaces</span>
                                    </button>
                                </li>
                                <li class="border-t border-base-200 my-1"></li>
                                @foreach($workspaces as $ws)
                                    <li class="calendar-workspace-option" data-name="{{ strtolower($ws->name) }}">
                                        <button type="button" onclick="selectCalendarWorkspace('{{ $ws->id }}', '{{ $ws->name }}')" class="w-full text-left px-3 py-2 rounded-lg hover:bg-base-200 flex items-center gap-2">
                                            <span class="icon-[tabler--folder] size-4 text-primary"></span>
                                            <span>{{ $ws->name }}</span>
                                        </button>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>

                    <!-- Assignee Filter (only visible to owner/admin) -->
                    @if($canViewAllAssignees)
                        <input type="hidden" name="assignee_id" id="calendar-assignee-filter-value" value="{{ $filters['assignee_id'] ?? '' }}" />
                        <div class="relative">
                            <button type="button" id="calendar-assignee-filter-btn" onclick="toggleCalendarAssigneeDropdown()" class="btn btn-ghost btn-sm gap-2 border border-base-300 min-w-44">
                                <span class="icon-[tabler--user] size-4"></span>
                                <span id="calendar-assignee-filter-label">
                                    @if(($filters['assignee_id'] ?? '') == auth()->id())
                                        Assigned to Me
                                    @elseif(!empty($filters['assignee_id']))
                                        {{ $teamMembers->firstWhere('id', $filters['assignee_id'])?->name ?? 'All Assignees' }}
                                    @else
                                        All Assignees
                                    @endif
                                </span>
                                <span class="icon-[tabler--chevron-down] size-4"></span>
                            </button>
                            <div id="calendar-assignee-dropdown" class="hidden absolute left-0 top-full mt-2 w-64 bg-base-100 rounded-xl shadow-xl border border-base-300 z-[100]">
                                <div class="p-2 border-b border-base-200">
                                    <div class="relative">
                                        <span class="icon-[tabler--search] size-4 absolute left-3 top-1/2 -translate-y-1/2 text-base-content/40"></span>
                                        <input type="text" id="calendar-assignee-search" placeholder="Search members..." class="input input-sm input-bordered w-full pl-9" oninput="searchCalendarAssignees(this.value)" autocomplete="off" />
                                    </div>
                                </div>
                                <ul class="p-2 max-h-64 overflow-y-auto" id="calendar-assignee-list">
                                    <li class="calendar-assignee-option" data-name="all">
                                        <button type="button" onclick="selectCalendarAssignee('', 'All Assignees')" class="w-full text-left px-3 py-2 rounded-lg hover:bg-base-200 flex items-center gap-2">
                                            <span class="icon-[tabler--users] size-4 text-base-content/60"></span>
                                            <span>All Assignees</span>
                                        </button>
                                    </li>
                                    <li class="calendar-assignee-option" data-name="assigned to me">
                                        <button type="button" onclick="selectCalendarAssignee('{{ auth()->id() }}', 'Assigned to Me')" class="w-full text-left px-3 py-2 rounded-lg hover:bg-base-200 flex items-center gap-2">
                                            <span class="icon-[tabler--user-check] size-4 text-primary"></span>
                                            <span class="font-medium">Assigned to Me</span>
                                        </button>
                                    </li>
                                    <li class="border-t border-base-200 my-1"></li>
                                    <li class="px-3 py-1 text-xs text-base-content/50 uppercase tracking-wide">Team Members</li>
                                    @foreach($teamMembers as $member)
                                        <li class="calendar-assignee-option" data-name="{{ strtolower($member->name) }}">
                                            <button type="button" onclick="selectCalendarAssignee('{{ $member->id }}', '{{ $member->name }}')" class="w-full text-left px-3 py-2 rounded-lg hover:bg-base-200 flex items-center gap-2">
                                                <div class="avatar">
                                                    <div class="w-5 h-5 rounded-full">
                                                        <img src="{{ $member->avatar_url }}" alt="{{ $member->name }}" />
                                                    </div>
                                                </div>
                                                <span>{{ $member->name }}</span>
                                            </button>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    @php
                        // For regular members, don't count assignee_id in clear button since it's forced
                        // For admins, show clear if any filter is set (including assignee)
                        $hasFilters = ($filters['workspace_id'] ?? null) ||
                            ($canViewAllAssignees && ($filters['assignee_id'] ?? null));
                    @endphp
                    @if($hasFilters)
                        <a href="{{ route('calendar.index', ['tab' => $tab ?? 'all', 'view' => $view]) }}"
                           class="btn btn-ghost btn-sm text-error">
                            <span class="icon-[tabler--x] size-4"></span>
                            Clear
                        </a>
                    @endif
                </form>
            </div>
        </div>

        <!-- List View -->
        @if($view === 'list')
            @include('calendar::partials.list-view')
        @else
            @include('calendar::partials.calendar-view')
        @endif
    </div>
</div>

<!-- Task Detail Drawer -->
@include('calendar::partials.task-drawer')

@push('scripts')
<script>
// Calendar Filter Dropdowns
let calendarWorkspaceDropdownOpen = false;
let calendarAssigneeDropdownOpen = false;

function toggleCalendarWorkspaceDropdown() {
    const dropdown = document.getElementById('calendar-workspace-dropdown');
    if (!dropdown) return;
    const searchInput = document.getElementById('calendar-workspace-search');
    calendarWorkspaceDropdownOpen = !calendarWorkspaceDropdownOpen;
    closeAllCalendarDropdowns('workspace');
    if (calendarWorkspaceDropdownOpen) {
        dropdown.classList.remove('hidden');
        if (searchInput) {
            searchInput.value = '';
            searchCalendarWorkspaces('');
            setTimeout(() => searchInput.focus(), 50);
        }
    } else {
        dropdown.classList.add('hidden');
    }
}

function searchCalendarWorkspaces(query) {
    const options = document.querySelectorAll('#calendar-workspace-list .calendar-workspace-option');
    const lowerQuery = query.toLowerCase().trim();
    options.forEach(option => {
        const name = option.dataset.name || '';
        option.style.display = (lowerQuery === '' || name.includes(lowerQuery) || name === 'all') ? '' : 'none';
    });
}

function selectCalendarWorkspace(value, label) {
    document.getElementById('calendar-workspace-filter-value').value = value;
    document.getElementById('calendar-workspace-filter-label').textContent = label;
    document.getElementById('calendar-workspace-dropdown')?.classList.add('hidden');
    calendarWorkspaceDropdownOpen = false;
    document.getElementById('calendar-filter-form')?.submit();
}

function toggleCalendarAssigneeDropdown() {
    const dropdown = document.getElementById('calendar-assignee-dropdown');
    if (!dropdown) return;
    const searchInput = document.getElementById('calendar-assignee-search');
    calendarAssigneeDropdownOpen = !calendarAssigneeDropdownOpen;
    closeAllCalendarDropdowns('assignee');
    if (calendarAssigneeDropdownOpen) {
        dropdown.classList.remove('hidden');
        if (searchInput) {
            searchInput.value = '';
            searchCalendarAssignees('');
            setTimeout(() => searchInput.focus(), 50);
        }
    } else {
        dropdown.classList.add('hidden');
    }
}

function searchCalendarAssignees(query) {
    const options = document.querySelectorAll('#calendar-assignee-list .calendar-assignee-option');
    const lowerQuery = query.toLowerCase().trim();
    options.forEach(option => {
        const name = option.dataset.name || '';
        option.style.display = (lowerQuery === '' || name.includes(lowerQuery) || name === 'all' || name === 'assigned to me') ? '' : 'none';
    });
}

function selectCalendarAssignee(value, label) {
    document.getElementById('calendar-assignee-filter-value').value = value;
    document.getElementById('calendar-assignee-filter-label').textContent = label;
    document.getElementById('calendar-assignee-dropdown')?.classList.add('hidden');
    calendarAssigneeDropdownOpen = false;
    document.getElementById('calendar-filter-form')?.submit();
}

function closeAllCalendarDropdowns(except) {
    if (except !== 'workspace') {
        document.getElementById('calendar-workspace-dropdown')?.classList.add('hidden');
        calendarWorkspaceDropdownOpen = false;
    }
    if (except !== 'assignee') {
        document.getElementById('calendar-assignee-dropdown')?.classList.add('hidden');
        calendarAssigneeDropdownOpen = false;
    }
}

// Close dropdowns when clicking outside
document.addEventListener('click', function(e) {
    const calendarDropdowns = [
        { dropdown: 'calendar-workspace-dropdown', btn: 'calendar-workspace-filter-btn' },
        { dropdown: 'calendar-assignee-dropdown', btn: 'calendar-assignee-filter-btn' },
    ];

    calendarDropdowns.forEach(({ dropdown, btn }) => {
        const dropdownEl = document.getElementById(dropdown);
        const btnEl = document.getElementById(btn);
        if (dropdownEl && btnEl && !dropdownEl.contains(e.target) && !btnEl.contains(e.target)) {
            dropdownEl.classList.add('hidden');
        }
    });
});

async function syncGoogleCalendar() {
    const btn = document.getElementById('googleSyncBtn');
    const originalContent = btn.innerHTML;

    // Show loading state
    btn.disabled = true;
    btn.innerHTML = '<span class="loading loading-spinner loading-xs"></span><span class="hidden sm:inline">Syncing...</span>';

    try {
        const response = await fetch('{{ route("google.sync") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        });

        const data = await response.json();

        if (data.success) {
            // Show success toast
            showToast(data.message, 'success');
            // Reload page to show new events
            if (data.data.synced_from_google > 0 || data.data.synced_to_google > 0) {
                setTimeout(() => window.location.reload(), 1500);
            }
        } else {
            showToast(data.message || 'Sync failed', 'error');
        }
    } catch (error) {
        console.error('Sync error:', error);
        showToast('Failed to sync with Google Calendar', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalContent;
    }
}

function showToast(message, type = 'info') {
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `alert alert-${type === 'success' ? 'success' : type === 'error' ? 'error' : 'info'} fixed bottom-4 right-4 w-auto max-w-md z-50 shadow-lg`;
    toast.innerHTML = `
        <span class="icon-[tabler--${type === 'success' ? 'check' : type === 'error' ? 'alert-circle' : 'info-circle'}] size-5"></span>
        <span>${message}</span>
    `;
    document.body.appendChild(toast);

    // Remove after 4 seconds
    setTimeout(() => {
        toast.remove();
    }, 4000);
}
</script>
@endpush

@endsection
