@extends('layouts.app')

@section('content')
<div class="p-4 md:p-6">
    <div class="max-w mx-auto">
        <!-- Page Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-base-content">My Tasks</h1>
                <p class="text-base-content/60">Tasks assigned to you or you're watching</p>
            </div>
            <a href="{{ route('tasks.create') }}" class="btn btn-primary">
                <span class="icon-[tabler--plus] size-5"></span>
                Add Task
            </a>
        </div>

        <!-- Success/Error Messages -->
        @if(session('success'))
            <div class="alert alert-success mb-4">
                <span class="icon-[tabler--check] size-5"></span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-error mb-4">
                <span class="icon-[tabler--x] size-5"></span>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Task Type Tabs -->
        @php
            $activeTaskTab = $filters['task_filter'] ?? 'all';
            $stats = $taskStats ?? ['total' => 0, 'open' => 0, 'closed' => 0, 'overdue' => 0];
        @endphp
        <div class="inline-flex p-1 bg-base-200 rounded-xl mb-6">
            <a href="{{ route('tasks.index', array_merge(request()->except('task_filter', 'page'), ['task_filter' => 'all'])) }}"
               class="flex items-center gap-2 px-5 py-2.5 rounded-lg font-medium transition-all duration-200 {{ $activeTaskTab === 'all' || $activeTaskTab === '' ? 'bg-primary text-primary-content shadow-sm' : 'text-base-content/60 hover:text-primary hover:bg-primary/10' }}">
                <span class="icon-[tabler--list-check] size-5"></span>
                <span>All Tasks</span>
                <span class="badge badge-sm {{ $activeTaskTab === 'all' || $activeTaskTab === '' ? 'bg-primary-content/20 text-primary-content border-0' : '' }}">{{ $stats['open'] }}</span>
            </a>
            <a href="{{ route('tasks.index', array_merge(request()->except('task_filter', 'page'), ['task_filter' => 'overdue'])) }}"
               class="flex items-center gap-2 px-5 py-2.5 rounded-lg font-medium transition-all duration-200 {{ $activeTaskTab === 'overdue' ? 'bg-error text-error-content shadow-sm' : 'text-base-content/60 hover:text-error hover:bg-error/10' }}">
                <span class="icon-[tabler--alert-triangle] size-5"></span>
                <span>Overdue</span>
                <span class="badge badge-sm {{ $activeTaskTab === 'overdue' ? 'bg-error-content/20 text-error-content border-0' : 'badge-error' }}">{{ $stats['overdue'] }}</span>
            </a>
            <a href="{{ route('tasks.index', array_merge(request()->except('task_filter', 'page'), ['task_filter' => 'closed'])) }}"
               class="flex items-center gap-2 px-5 py-2.5 rounded-lg font-medium transition-all duration-200 {{ $activeTaskTab === 'closed' ? 'bg-success text-success-content shadow-sm' : 'text-base-content/60 hover:text-success hover:bg-success/10' }}">
                <span class="icon-[tabler--circle-check] size-5"></span>
                <span>Closed</span>
                <span class="badge badge-sm {{ $activeTaskTab === 'closed' ? 'bg-success-content/20 text-success-content border-0' : 'badge-success' }}">{{ $stats['closed'] }}</span>
            </a>
        </div>

        <!-- Filters & Search -->
        <div class="card bg-base-100 shadow mb-6">
            <div class="card-body p-4">
                <form id="task-filter-form" action="{{ route('tasks.index') }}" method="GET" class="flex flex-col md:flex-row gap-4">
                    <input type="hidden" name="task_filter" value="{{ $activeTaskTab }}" />
                    <!-- Search -->
                    <div class="flex-1">
                        <div class="relative">
                            <span class="icon-[tabler--search] size-5 absolute left-3 top-1/2 -translate-y-1/2 text-base-content/50"></span>
                            <input type="text" name="search" id="search-input" value="{{ $filters['search'] ?? '' }}" placeholder="Search tasks..." class="input input-bordered w-full pl-10" autocomplete="off" />
                            <span id="search-loading" class="loading loading-spinner loading-sm absolute right-3 top-1/2 -translate-y-1/2 hidden"></span>
                        </div>
                    </div>
                    <!-- Workspace Filter (Searchable) -->
                    <input type="hidden" name="workspace_id" id="workspace-filter-value" value="{{ $filters['workspace_id'] ?? '' }}" />
                    <div class="relative">
                        <button type="button" id="workspace-filter-btn" onclick="toggleWorkspaceDropdown()" class="btn btn-ghost btn-sm gap-2 border border-base-300 min-w-48">
                            <span class="icon-[tabler--layout-grid] size-4"></span>
                            <span id="workspace-filter-label">
                                @if(!empty($filters['workspace_id']))
                                    {{ $workspaces->firstWhere('id', $filters['workspace_id'])?->name ?? 'All Workspaces' }}
                                @else
                                    All Workspaces
                                @endif
                            </span>
                            <span class="icon-[tabler--chevron-down] size-4"></span>
                        </button>
                        <div id="workspace-dropdown" class="hidden absolute left-0 top-full mt-2 w-64 bg-base-100 rounded-xl shadow-xl border border-base-300 z-[100]">
                            <!-- Search Input -->
                            <div class="p-2 border-b border-base-200">
                                <div class="relative">
                                    <span class="icon-[tabler--search] size-4 absolute left-3 top-1/2 -translate-y-1/2 text-base-content/40"></span>
                                    <input type="text"
                                           id="workspace-search"
                                           placeholder="Search workspaces..."
                                           class="input input-sm input-bordered w-full pl-9"
                                           oninput="searchWorkspaces(this.value)"
                                           autocomplete="off" />
                                </div>
                            </div>
                            <!-- Options List -->
                            <ul class="p-2 max-h-64 overflow-y-auto" id="workspace-list">
                                <li class="workspace-option" data-name="all">
                                    <button type="button" onclick="selectWorkspace('', 'All Workspaces')" class="w-full text-left px-3 py-2 rounded-lg hover:bg-base-200 flex items-center gap-2">
                                        <span class="icon-[tabler--layout-grid] size-4 text-base-content/60"></span>
                                        <span>All Workspaces</span>
                                    </button>
                                </li>
                                @if($workspaces->count() > 0)
                                    <li class="border-t border-base-200 my-1"></li>
                                    @foreach($workspaces as $ws)
                                        <li class="workspace-option" data-name="{{ strtolower($ws->name) }}">
                                            <button type="button" onclick="selectWorkspace('{{ $ws->id }}', '{{ $ws->name }}')" class="w-full text-left px-3 py-2 rounded-lg hover:bg-base-200 flex items-center gap-2">
                                                <span class="icon-[tabler--folder] size-4 text-primary"></span>
                                                <span>{{ $ws->name }}</span>
                                            </button>
                                        </li>
                                    @endforeach
                                @endif
                            </ul>
                        </div>
                    </div>
                    <!-- Status Filter (Shows when workspace selected) -->
                    @if(!empty($filters['workspace_id']) && $statuses->count() > 0)
                        <input type="hidden" name="status_id" id="status-filter-value" value="{{ $filters['status_id'] ?? '' }}" />
                        <div class="relative">
                            <button type="button" id="status-filter-btn" onclick="toggleStatusDropdown()" class="btn btn-ghost btn-sm gap-2 border border-base-300 min-w-36">
                                <span class="icon-[tabler--circle-dot] size-4"></span>
                                <span id="status-filter-label">
                                    @if(!empty($filters['status_id']))
                                        {{ $statuses->firstWhere('id', $filters['status_id'])?->name ?? 'All Statuses' }}
                                    @else
                                        All Statuses
                                    @endif
                                </span>
                                <span class="icon-[tabler--chevron-down] size-4"></span>
                            </button>
                            <div id="status-dropdown" class="hidden absolute left-0 top-full mt-2 w-56 bg-base-100 rounded-xl shadow-xl border border-base-300 z-[100]">
                                <ul class="p-2 max-h-64 overflow-y-auto" id="status-list">
                                    <li>
                                        <button type="button" onclick="selectStatus('', 'All Statuses')" class="w-full text-left px-3 py-2 rounded-lg hover:bg-base-200 flex items-center gap-2">
                                            <span class="icon-[tabler--circle-dot] size-4 text-base-content/60"></span>
                                            <span>All Statuses</span>
                                        </button>
                                    </li>
                                    <li class="border-t border-base-200 my-1"></li>
                                    @foreach($statuses as $status)
                                        <li>
                                            <button type="button" onclick="selectStatus('{{ $status->id }}', '{{ $status->name }}')" class="w-full text-left px-3 py-2 rounded-lg hover:bg-base-200 flex items-center gap-2">
                                                <span class="w-3 h-3 rounded-full" style="background-color: {{ $status->background_color }};"></span>
                                                <span>{{ $status->name }}</span>
                                            </button>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif
                    <!-- Assignee Filter (Searchable) - Only visible to owner/admin -->
                    @if($canViewAllAssignees ?? false)
                        <input type="hidden" name="assignee_id" id="assignee-filter-value" value="{{ $filters['assignee_id'] ?? '' }}" />
                        <div class="relative">
                            <button type="button" id="assignee-filter-btn" onclick="toggleAssigneeDropdown()" class="btn btn-ghost btn-sm gap-2 border border-base-300 min-w-44">
                                <span class="icon-[tabler--user] size-4"></span>
                                <span id="assignee-filter-label">
                                    @if(($filters['assignee_id'] ?? '') == auth()->id())
                                        Assigned to Me
                                    @elseif(!empty($filters['assignee_id']))
                                        {{ $users->firstWhere('id', $filters['assignee_id'])?->name ?? 'All Assignees' }}
                                    @else
                                        All Assignees
                                    @endif
                                </span>
                                <span class="icon-[tabler--chevron-down] size-4"></span>
                            </button>
                            <div id="assignee-dropdown" class="hidden absolute left-0 top-full mt-2 w-64 bg-base-100 rounded-xl shadow-xl border border-base-300 z-[100]">
                                <!-- Search Input -->
                                <div class="p-2 border-b border-base-200">
                                    <div class="relative">
                                        <span class="icon-[tabler--search] size-4 absolute left-3 top-1/2 -translate-y-1/2 text-base-content/40"></span>
                                        <input type="text"
                                               id="assignee-search"
                                               placeholder="Search members..."
                                               class="input input-sm input-bordered w-full pl-9"
                                               oninput="searchAssignees(this.value)"
                                               autocomplete="off" />
                                    </div>
                                </div>
                                <!-- Options List -->
                                <ul class="p-2 max-h-64 overflow-y-auto" id="assignee-list">
                                    <li class="assignee-option" data-name="all">
                                        <button type="button" onclick="selectAssignee('', 'All Assignees')" class="w-full text-left px-3 py-2 rounded-lg hover:bg-base-200 flex items-center gap-2">
                                            <span class="icon-[tabler--users] size-4 text-base-content/60"></span>
                                            <span>All Assignees</span>
                                        </button>
                                    </li>
                                    <li class="assignee-option" data-name="assigned to me">
                                        <button type="button" onclick="selectAssignee('{{ auth()->id() }}', 'Assigned to Me')" class="w-full text-left px-3 py-2 rounded-lg hover:bg-base-200 flex items-center gap-2">
                                            <span class="icon-[tabler--user-check] size-4 text-primary"></span>
                                            <span class="font-medium">Assigned to Me</span>
                                        </button>
                                    </li>
                                    @if($users->count() > 0)
                                        <li class="border-t border-base-200 my-1"></li>
                                        <li class="px-3 py-1 text-xs text-base-content/50 uppercase tracking-wide">Team Members</li>
                                        @foreach($users as $member)
                                            <li class="assignee-option" data-name="{{ strtolower($member->name) }}">
                                                <button type="button" onclick="selectAssignee('{{ $member->id }}', '{{ $member->name }}')" class="w-full text-left px-3 py-2 rounded-lg hover:bg-base-200 flex items-center gap-2">
                                                    <div class="avatar">
                                                        <div class="w-5 h-5 rounded-full">
                                                            <img src="{{ $member->avatar_url }}" alt="{{ $member->name }}" />
                                                        </div>
                                                    </div>
                                                    <span>{{ $member->name }}</span>
                                                </button>
                                            </li>
                                        @endforeach
                                    @endif
                                </ul>
                            </div>
                        </div>
                    @endif
                    <!-- Priority Filter (Searchable) -->
                    <input type="hidden" name="priority" id="priority-filter-value" value="{{ $filters['priority'] ?? '' }}" />
                    <div class="relative">
                        <button type="button" id="priority-filter-btn" onclick="togglePriorityDropdown()" class="btn btn-ghost btn-sm gap-2 border border-base-300 min-w-36">
                            <span class="icon-[tabler--flag] size-4"></span>
                            <span id="priority-filter-label">
                                @if(!empty($filters['priority']))
                                    {{ \App\Modules\Task\Enums\TaskPriority::tryFrom($filters['priority'])?->label() ?? 'All Priorities' }}
                                @else
                                    All Priorities
                                @endif
                            </span>
                            <span class="icon-[tabler--chevron-down] size-4"></span>
                        </button>
                        <div id="priority-dropdown" class="hidden absolute left-0 top-full mt-2 w-48 bg-base-100 rounded-xl shadow-xl border border-base-300 z-[100]">
                            <ul class="p-2" id="priority-list">
                                <li>
                                    <button type="button" onclick="selectPriority('', 'All Priorities')" class="w-full text-left px-3 py-2 rounded-lg hover:bg-base-200 flex items-center gap-2">
                                        <span class="icon-[tabler--flag] size-4 text-base-content/60"></span>
                                        <span>All Priorities</span>
                                    </button>
                                </li>
                                <li class="border-t border-base-200 my-1"></li>
                                @foreach(\App\Modules\Task\Enums\TaskPriority::cases() as $priority)
                                    <li>
                                        <button type="button" onclick="selectPriority('{{ $priority->value }}', '{{ $priority->label() }}')" class="w-full text-left px-3 py-2 rounded-lg hover:bg-base-200 flex items-center gap-2">
                                            <span class="icon-[{{ $priority->icon() }}] size-4" style="color: {{ $priority->color() }}"></span>
                                            <span>{{ $priority->label() }}</span>
                                        </button>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    @php
                        // For regular members, don't count assignee_id in clear button since it's forced
                        $clearableFilters = collect($filters ?? [])->filter(function ($value, $key) use ($canViewAllAssignees) {
                            if (empty($value)) return false;
                            if ($key === 'task_filter' && $value === 'all') return false;
                            if ($key === 'is_closed') return false;
                            if ($key === 'overdue_only') return false;
                            // For regular members, assignee_id is forced so don't count it
                            if (!($canViewAllAssignees ?? false) && $key === 'assignee_id') return false;
                            return true;
                        });
                    @endphp
                    @if($clearableFilters->isNotEmpty())
                        <a href="{{ route('tasks.index') }}" class="btn btn-ghost btn-sm text-error">
                            <span class="icon-[tabler--x] size-4"></span>
                            Clear
                        </a>
                    @endif
                </form>
            </div>
        </div>

        <!-- View Toggle -->
        <div class="flex items-center justify-between mb-4">
            <div id="tasks-count" class="text-sm text-base-content/60">
                {{ $tasks->total() }} {{ Str::plural('task', $tasks->total()) }} found
            </div>
            <div class="flex items-center gap-1 border border-base-300 rounded-lg p-1 bg-base-100">
                <a href="{{ route('tasks.index', array_merge(request()->query(), ['view' => 'card'])) }}"
                   class="btn btn-sm {{ $viewMode === 'card' ? 'btn-primary' : 'btn-ghost' }}">
                    <span class="icon-[tabler--layout-grid] size-4"></span>
                </a>
                <a href="{{ route('tasks.index', array_merge(request()->query(), ['view' => 'table'])) }}"
                   class="btn btn-sm {{ $viewMode === 'table' ? 'btn-primary' : 'btn-ghost' }}">
                    <span class="icon-[tabler--list] size-4"></span>
                </a>
            </div>
        </div>

        <!-- Tasks Content -->
        <div id="tasks-content">
            @if($tasks->isEmpty())
                <div class="card bg-base-100 shadow">
                    <div class="card-body text-center py-12">
                        <div class="text-base-content/50">
                            <span class="icon-[tabler--checkbox] size-12 block mx-auto mb-4"></span>
                            <p class="text-lg font-medium">No tasks found</p>
                            <p class="text-sm">
                                @if(!empty(array_filter($filters ?? [])))
                                    Try adjusting your search or filters
                                @else
                                    Create your first task to get started
                                @endif
                            </p>
                        </div>
                        <div class="mt-4 flex justify-center gap-2">
                            @if(!empty(array_filter($filters ?? [])))
                                <a href="{{ route('tasks.index') }}" class="btn btn-ghost">Clear Filters</a>
                            @endif
                            <a href="{{ route('tasks.create') }}" class="btn btn-primary">
                                <span class="icon-[tabler--plus] size-5"></span>
                                Create Task
                            </a>
                        </div>
                    </div>
                </div>
            @else
                @if($viewMode === 'card')
                    @include('task::partials.task-cards', ['tasks' => $tasks])
                @else
                    @include('task::partials.task-table', ['tasks' => $tasks])
                @endif
            @endif
        </div>

        <!-- Pagination -->
        <div id="tasks-pagination" class="mt-6">
            @if($tasks->hasPages())
                {{ $tasks->withQueryString()->links() }}
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search-input');
    const searchLoading = document.getElementById('search-loading');
    const tasksContent = document.getElementById('tasks-content');
    const tasksCount = document.getElementById('tasks-count');
    const tasksPagination = document.getElementById('tasks-pagination');
    const filterForm = document.getElementById('task-filter-form');

    let searchTimeout = null;
    let currentSearch = searchInput.value;

    // Real-time search
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();

        searchTimeout = setTimeout(() => {
            if (query !== currentSearch) {
                currentSearch = query;
                performSearch();
            }
        }, 300);
    });

    // Prevent form submission on Enter, use AJAX instead
    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            clearTimeout(searchTimeout);
            currentSearch = this.value.trim();
            performSearch();
        }
    });

    async function performSearch() {
        const formData = new FormData(filterForm);
        const params = new URLSearchParams(formData);
        params.set('ajax', '1');

        searchLoading.classList.remove('hidden');

        try {
            const response = await fetch(`{{ route('tasks.index') }}?${params.toString()}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            // Update tasks content
            tasksContent.innerHTML = data.html || `
                <div class="card bg-base-100 shadow">
                    <div class="card-body text-center py-12">
                        <div class="text-base-content/50">
                            <span class="icon-[tabler--checkbox] size-12 block mx-auto mb-4"></span>
                            <p class="text-lg font-medium">No tasks found</p>
                            <p class="text-sm">Try adjusting your search or filters</p>
                        </div>
                    </div>
                </div>
            `;

            // Update count
            tasksCount.textContent = `${data.total} ${data.total === 1 ? 'task' : 'tasks'} found`;

            // Update pagination
            tasksPagination.innerHTML = data.pagination || '';

            // Update URL without reload
            const url = new URL(window.location);
            if (currentSearch) {
                url.searchParams.set('search', currentSearch);
            } else {
                url.searchParams.delete('search');
            }
            window.history.replaceState({}, '', url);

        } catch (error) {
            console.error('Search error:', error);
        } finally {
            searchLoading.classList.add('hidden');
        }
    }
});

// Dropdown states
let workspaceDropdownOpen = false;
let statusDropdownOpen = false;
let assigneeDropdownOpen = false;
let priorityDropdownOpen = false;

// Workspace dropdown
function toggleWorkspaceDropdown() {
    const dropdown = document.getElementById('workspace-dropdown');
    const searchInput = document.getElementById('workspace-search');
    workspaceDropdownOpen = !workspaceDropdownOpen;

    closeAllDropdowns('workspace');

    if (workspaceDropdownOpen) {
        dropdown.classList.remove('hidden');
        searchInput.value = '';
        searchWorkspaces('');
        setTimeout(() => searchInput.focus(), 50);
    } else {
        dropdown.classList.add('hidden');
    }
}

function searchWorkspaces(query) {
    const options = document.querySelectorAll('#workspace-list .workspace-option');
    const lowerQuery = query.toLowerCase().trim();

    options.forEach(option => {
        const name = option.dataset.name || '';
        if (lowerQuery === '' || name.includes(lowerQuery) || name === 'all') {
            option.style.display = '';
        } else {
            option.style.display = 'none';
        }
    });
}

function selectWorkspace(value, label) {
    document.getElementById('workspace-filter-value').value = value;
    document.getElementById('workspace-filter-label').textContent = label;
    document.getElementById('workspace-dropdown').classList.add('hidden');
    workspaceDropdownOpen = false;
    // Clear status filter when workspace changes
    const statusInput = document.getElementById('status-filter-value');
    if (statusInput) statusInput.value = '';
    document.getElementById('task-filter-form').submit();
}

// Status dropdown
function toggleStatusDropdown() {
    const dropdown = document.getElementById('status-dropdown');
    if (!dropdown) return;
    statusDropdownOpen = !statusDropdownOpen;

    closeAllDropdowns('status');

    if (statusDropdownOpen) {
        dropdown.classList.remove('hidden');
    } else {
        dropdown.classList.add('hidden');
    }
}

function selectStatus(value, label) {
    document.getElementById('status-filter-value').value = value;
    document.getElementById('status-filter-label').textContent = label;
    document.getElementById('status-dropdown').classList.add('hidden');
    statusDropdownOpen = false;
    document.getElementById('task-filter-form').submit();
}

// Assignee dropdown
function toggleAssigneeDropdown() {
    const dropdown = document.getElementById('assignee-dropdown');
    const searchInput = document.getElementById('assignee-search');
    assigneeDropdownOpen = !assigneeDropdownOpen;

    closeAllDropdowns('assignee');

    if (assigneeDropdownOpen) {
        dropdown.classList.remove('hidden');
        searchInput.value = '';
        searchAssignees('');
        setTimeout(() => searchInput.focus(), 50);
    } else {
        dropdown.classList.add('hidden');
    }
}

function searchAssignees(query) {
    const options = document.querySelectorAll('#assignee-list .assignee-option');
    const lowerQuery = query.toLowerCase().trim();

    options.forEach(option => {
        const name = option.dataset.name || '';
        if (lowerQuery === '' || name.includes(lowerQuery) || name === 'all' || name === 'assigned to me') {
            option.style.display = '';
        } else {
            option.style.display = 'none';
        }
    });
}

function selectAssignee(value, label) {
    document.getElementById('assignee-filter-value').value = value;
    document.getElementById('assignee-filter-label').textContent = label;
    document.getElementById('assignee-dropdown').classList.add('hidden');
    assigneeDropdownOpen = false;
    document.getElementById('task-filter-form').submit();
}

// Priority dropdown
function togglePriorityDropdown() {
    const dropdown = document.getElementById('priority-dropdown');
    priorityDropdownOpen = !priorityDropdownOpen;

    closeAllDropdowns('priority');

    if (priorityDropdownOpen) {
        dropdown.classList.remove('hidden');
    } else {
        dropdown.classList.add('hidden');
    }
}

function selectPriority(value, label) {
    document.getElementById('priority-filter-value').value = value;
    document.getElementById('priority-filter-label').textContent = label;
    document.getElementById('priority-dropdown').classList.add('hidden');
    priorityDropdownOpen = false;
    document.getElementById('task-filter-form').submit();
}

// Close all dropdowns except the specified one
function closeAllDropdowns(except) {
    if (except !== 'workspace') {
        document.getElementById('workspace-dropdown')?.classList.add('hidden');
        workspaceDropdownOpen = false;
    }
    if (except !== 'status') {
        document.getElementById('status-dropdown')?.classList.add('hidden');
        statusDropdownOpen = false;
    }
    if (except !== 'assignee') {
        document.getElementById('assignee-dropdown')?.classList.add('hidden');
        assigneeDropdownOpen = false;
    }
    if (except !== 'priority') {
        document.getElementById('priority-dropdown')?.classList.add('hidden');
        priorityDropdownOpen = false;
    }
}

// Close dropdowns when clicking outside
document.addEventListener('click', function(e) {
    const workspaceDropdown = document.getElementById('workspace-dropdown');
    const workspaceBtn = document.getElementById('workspace-filter-btn');
    const statusDropdown = document.getElementById('status-dropdown');
    const statusBtn = document.getElementById('status-filter-btn');
    const assigneeDropdown = document.getElementById('assignee-dropdown');
    const assigneeBtn = document.getElementById('assignee-filter-btn');
    const priorityDropdown = document.getElementById('priority-dropdown');
    const priorityBtn = document.getElementById('priority-filter-btn');

    if (workspaceDropdown && workspaceBtn && !workspaceDropdown.contains(e.target) && !workspaceBtn.contains(e.target)) {
        workspaceDropdown.classList.add('hidden');
        workspaceDropdownOpen = false;
    }
    if (statusDropdown && statusBtn && !statusDropdown.contains(e.target) && !statusBtn.contains(e.target)) {
        statusDropdown.classList.add('hidden');
        statusDropdownOpen = false;
    }
    if (assigneeDropdown && assigneeBtn && !assigneeDropdown.contains(e.target) && !assigneeBtn.contains(e.target)) {
        assigneeDropdown.classList.add('hidden');
        assigneeDropdownOpen = false;
    }
    if (priorityDropdown && priorityBtn && !priorityDropdown.contains(e.target) && !priorityBtn.contains(e.target)) {
        priorityDropdown.classList.add('hidden');
        priorityDropdownOpen = false;
    }
});
</script>
@endpush
@endsection
