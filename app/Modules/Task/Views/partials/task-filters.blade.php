@php
    $showWorkspaceFilter = $showWorkspaceFilter ?? true;
    $showTaskFilter = $showTaskFilter ?? true;
    $currentFilters = $filters ?? [];
    $filterWorkspaces = $workspaces ?? collect();
    $filterStatuses = $statuses ?? collect();
    $filterUsers = $users ?? collect();
    $formId = $formId ?? 'task-filter-form';
    $formAction = $formAction ?? route('tasks.index');
@endphp

<div class="flex flex-wrap items-center gap-2">
    <!-- Search Input -->
    <div class="relative">
        <span class="icon-[tabler--search] size-4 absolute left-3 top-1/2 -translate-y-1/2 text-base-content/40"></span>
        <input type="text" name="search" id="task-search-input" value="{{ $currentFilters['search'] ?? '' }}" placeholder="Search tasks..." class="input input-sm input-bordered w-48 pl-9 pr-8" autocomplete="off" oninput="handleSearchInput()" />
        <button type="button" id="clear-search-btn" onclick="clearSearch()" class="absolute right-2 top-1/2 -translate-y-1/2 text-base-content/40 hover:text-base-content/60 {{ empty($currentFilters['search']) ? 'hidden' : '' }}">
            <span class="icon-[tabler--x] size-4"></span>
        </button>
    </div>

    @if($showTaskFilter)
        <!-- Task Type Filter (All/Overdue/Closed) -->
        <input type="hidden" name="task_filter" id="task-filter-value" value="{{ $currentFilters['task_filter'] ?? '' }}" />
        <div class="relative">
            <button type="button" id="task-filter-btn" onclick="toggleTaskFilterDropdown()" class="btn btn-ghost btn-sm gap-2 border border-base-300 min-w-32">
                <span class="icon-[tabler--filter] size-4"></span>
                <span id="task-filter-label">
                    @if(($currentFilters['task_filter'] ?? '') == 'overdue')
                        Overdue
                    @elseif(($currentFilters['task_filter'] ?? '') == 'closed')
                        Closed
                    @else
                        All Tasks
                    @endif
                </span>
                <span class="icon-[tabler--chevron-down] size-4"></span>
            </button>
            <div id="task-filter-dropdown" class="hidden absolute left-0 top-full mt-2 w-44 bg-base-100 rounded-xl shadow-xl border border-base-300 z-[100]">
                <ul class="p-2">
                    <li>
                        <button type="button" onclick="selectTaskFilter('', 'All Tasks')" class="w-full text-left px-3 py-2 rounded-lg hover:bg-base-200 flex items-center gap-2 {{ empty($currentFilters['task_filter']) ? 'bg-primary/10' : '' }}">
                            <span class="icon-[tabler--list-check] size-4 text-base-content/60"></span>
                            <span>All Tasks</span>
                            @if(empty($currentFilters['task_filter']))
                                <span class="icon-[tabler--check] size-4 text-primary ml-auto"></span>
                            @endif
                        </button>
                    </li>
                    <li>
                        <button type="button" onclick="selectTaskFilter('overdue', 'Overdue')" class="w-full text-left px-3 py-2 rounded-lg hover:bg-base-200 flex items-center gap-2 {{ ($currentFilters['task_filter'] ?? '') == 'overdue' ? 'bg-primary/10' : '' }}">
                            <span class="icon-[tabler--alert-triangle] size-4 text-error"></span>
                            <span>Overdue</span>
                            @if(($currentFilters['task_filter'] ?? '') == 'overdue')
                                <span class="icon-[tabler--check] size-4 text-primary ml-auto"></span>
                            @endif
                        </button>
                    </li>
                    <li>
                        <button type="button" onclick="selectTaskFilter('closed', 'Closed')" class="w-full text-left px-3 py-2 rounded-lg hover:bg-base-200 flex items-center gap-2 {{ ($currentFilters['task_filter'] ?? '') == 'closed' ? 'bg-primary/10' : '' }}">
                            <span class="icon-[tabler--circle-check] size-4 text-success"></span>
                            <span>Closed</span>
                            @if(($currentFilters['task_filter'] ?? '') == 'closed')
                                <span class="icon-[tabler--check] size-4 text-primary ml-auto"></span>
                            @endif
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    @endif

    @if($showWorkspaceFilter && $filterWorkspaces->count() > 0)
        <!-- Workspace Filter -->
        <input type="hidden" name="workspace_id" id="workspace-filter-value" value="{{ $currentFilters['workspace_id'] ?? '' }}" />
        <div class="relative">
            <button type="button" id="workspace-filter-btn" onclick="toggleWorkspaceDropdown()" class="btn btn-ghost btn-sm gap-2 border border-base-300 min-w-48">
                <span class="icon-[tabler--layout-grid] size-4"></span>
                <span id="workspace-filter-label">
                    @if(!empty($currentFilters['workspace_id']))
                        {{ $filterWorkspaces->firstWhere('id', $currentFilters['workspace_id'])?->name ?? 'All Workspaces' }}
                    @else
                        All Workspaces
                    @endif
                </span>
                <span class="icon-[tabler--chevron-down] size-4"></span>
            </button>
            <div id="workspace-dropdown" class="hidden absolute left-0 top-full mt-2 w-64 bg-base-100 rounded-xl shadow-xl border border-base-300 z-[100]">
                <div class="p-2 border-b border-base-200">
                    <div class="relative">
                        <span class="icon-[tabler--search] size-4 absolute left-3 top-1/2 -translate-y-1/2 text-base-content/40"></span>
                        <input type="text" id="workspace-search" placeholder="Search workspaces..." class="input input-sm input-bordered w-full pl-9" oninput="searchWorkspaces(this.value)" autocomplete="off" />
                    </div>
                </div>
                <ul class="p-2 max-h-64 overflow-y-auto" id="workspace-list">
                    <li class="workspace-option" data-name="all">
                        <button type="button" onclick="selectWorkspace('', 'All Workspaces')" class="w-full text-left px-3 py-2 rounded-lg hover:bg-base-200 flex items-center gap-2">
                            <span class="icon-[tabler--layout-grid] size-4 text-base-content/60"></span>
                            <span>All Workspaces</span>
                        </button>
                    </li>
                    <li class="border-t border-base-200 my-1"></li>
                    @foreach($filterWorkspaces as $ws)
                        <li class="workspace-option" data-name="{{ strtolower($ws->name) }}">
                            <button type="button" onclick="selectWorkspace('{{ $ws->id }}', '{{ $ws->name }}')" class="w-full text-left px-3 py-2 rounded-lg hover:bg-base-200 flex items-center gap-2">
                                <span class="icon-[tabler--folder] size-4 text-primary"></span>
                                <span>{{ $ws->name }}</span>
                            </button>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    @if($filterStatuses->count() > 0)
        <!-- Status Filter -->
        <input type="hidden" name="status_id" id="status-filter-value" value="{{ $currentFilters['status_id'] ?? '' }}" />
        <div class="relative">
            <button type="button" id="status-filter-btn" onclick="toggleStatusDropdown()" class="btn btn-ghost btn-sm gap-2 border border-base-300 min-w-36">
                <span class="icon-[tabler--circle-dot] size-4"></span>
                <span id="status-filter-label">
                    @if(!empty($currentFilters['status_id']))
                        {{ $filterStatuses->firstWhere('id', $currentFilters['status_id'])?->name ?? 'All Statuses' }}
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
                    @foreach($filterStatuses as $status)
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

    @if($filterUsers->count() > 0)
        <!-- Assignee Filter -->
        <input type="hidden" name="assignee_id" id="assignee-filter-value" value="{{ $currentFilters['assignee_id'] ?? '' }}" />
        <div class="relative">
            <button type="button" id="assignee-filter-btn" onclick="toggleAssigneeDropdown()" class="btn btn-ghost btn-sm gap-2 border border-base-300 min-w-44">
                <span class="icon-[tabler--user] size-4"></span>
                <span id="assignee-filter-label">
                    @if(($currentFilters['assignee_id'] ?? '') == auth()->id())
                        Assigned to Me
                    @elseif(!empty($currentFilters['assignee_id']))
                        {{ $filterUsers->firstWhere('id', $currentFilters['assignee_id'])?->name ?? 'All Assignees' }}
                    @else
                        All Assignees
                    @endif
                </span>
                <span class="icon-[tabler--chevron-down] size-4"></span>
            </button>
            <div id="assignee-dropdown" class="hidden absolute left-0 top-full mt-2 w-64 bg-base-100 rounded-xl shadow-xl border border-base-300 z-[100]">
                <div class="p-2 border-b border-base-200">
                    <div class="relative">
                        <span class="icon-[tabler--search] size-4 absolute left-3 top-1/2 -translate-y-1/2 text-base-content/40"></span>
                        <input type="text" id="assignee-search" placeholder="Search members..." class="input input-sm input-bordered w-full pl-9" oninput="searchAssignees(this.value)" autocomplete="off" />
                    </div>
                </div>
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
                    <li class="border-t border-base-200 my-1"></li>
                    <li class="px-3 py-1 text-xs text-base-content/50 uppercase tracking-wide">Team Members</li>
                    @foreach($filterUsers as $member)
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
                </ul>
            </div>
        </div>
    @endif

    <!-- Priority Filter -->
    <input type="hidden" name="priority" id="priority-filter-value" value="{{ $currentFilters['priority'] ?? '' }}" />
    <div class="relative">
        <button type="button" id="priority-filter-btn" onclick="togglePriorityDropdown()" class="btn btn-ghost btn-sm gap-2 border border-base-300 min-w-36">
            <span class="icon-[tabler--flag] size-4"></span>
            <span id="priority-filter-label">
                @if(!empty($currentFilters['priority']))
                    {{ \App\Modules\Task\Enums\TaskPriority::tryFrom($currentFilters['priority'])?->label() ?? 'All Priorities' }}
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

    @if(!empty(array_filter($currentFilters ?? [])))
        <a href="{{ $formAction }}" class="btn btn-ghost btn-sm text-error">
            <span class="icon-[tabler--x] size-4"></span>
            Clear
        </a>
    @endif
</div>

@push('scripts')
<script>
// Filter form ID
const filterFormId = '{{ $formId ?? "task-filter-form" }}';

// Debounce timer for search
let searchDebounceTimer = null;

// Search functions
function handleSearchInput() {
    const searchInput = document.getElementById('task-search-input');
    const clearBtn = document.getElementById('clear-search-btn');

    // Show/hide clear button
    if (clearBtn) {
        if (searchInput.value.length > 0) {
            clearBtn.classList.remove('hidden');
        } else {
            clearBtn.classList.add('hidden');
        }
    }

    // Debounce the search - wait 400ms after user stops typing
    clearTimeout(searchDebounceTimer);
    searchDebounceTimer = setTimeout(() => {
        document.getElementById(filterFormId)?.submit();
    }, 400);
}

function clearSearch() {
    document.getElementById('task-search-input').value = '';
    document.getElementById('clear-search-btn')?.classList.add('hidden');
    clearTimeout(searchDebounceTimer);
    document.getElementById(filterFormId)?.submit();
}

// Filter dropdown states
let workspaceDropdownOpen = false;
let statusDropdownOpen = false;
let assigneeDropdownOpen = false;
let priorityDropdownOpen = false;
let taskFilterDropdownOpen = false;

// Task Filter dropdown (All/Overdue/Closed)
function toggleTaskFilterDropdown() {
    const dropdown = document.getElementById('task-filter-dropdown');
    if (!dropdown) return;
    taskFilterDropdownOpen = !taskFilterDropdownOpen;
    closeAllFilterDropdowns('taskFilter');
    if (taskFilterDropdownOpen) {
        dropdown.classList.remove('hidden');
    } else {
        dropdown.classList.add('hidden');
    }
}

function selectTaskFilter(value, label) {
    document.getElementById('task-filter-value').value = value;
    document.getElementById('task-filter-label').textContent = label;
    document.getElementById('task-filter-dropdown')?.classList.add('hidden');
    taskFilterDropdownOpen = false;
    document.getElementById(filterFormId)?.submit();
}

// Workspace dropdown
function toggleWorkspaceDropdown() {
    const dropdown = document.getElementById('workspace-dropdown');
    if (!dropdown) return;
    const searchInput = document.getElementById('workspace-search');
    workspaceDropdownOpen = !workspaceDropdownOpen;
    closeAllFilterDropdowns('workspace');
    if (workspaceDropdownOpen) {
        dropdown.classList.remove('hidden');
        if (searchInput) {
            searchInput.value = '';
            searchWorkspaces('');
            setTimeout(() => searchInput.focus(), 50);
        }
    } else {
        dropdown.classList.add('hidden');
    }
}

function searchWorkspaces(query) {
    const options = document.querySelectorAll('#workspace-list .workspace-option');
    const lowerQuery = query.toLowerCase().trim();
    options.forEach(option => {
        const name = option.dataset.name || '';
        option.style.display = (lowerQuery === '' || name.includes(lowerQuery) || name === 'all') ? '' : 'none';
    });
}

function selectWorkspace(value, label) {
    document.getElementById('workspace-filter-value').value = value;
    document.getElementById('workspace-filter-label').textContent = label;
    document.getElementById('workspace-dropdown')?.classList.add('hidden');
    workspaceDropdownOpen = false;
    const statusInput = document.getElementById('status-filter-value');
    if (statusInput) statusInput.value = '';
    document.getElementById(filterFormId)?.submit();
}

// Status dropdown
function toggleStatusDropdown() {
    const dropdown = document.getElementById('status-dropdown');
    if (!dropdown) return;
    statusDropdownOpen = !statusDropdownOpen;
    closeAllFilterDropdowns('status');
    if (statusDropdownOpen) {
        dropdown.classList.remove('hidden');
    } else {
        dropdown.classList.add('hidden');
    }
}

function selectStatus(value, label) {
    document.getElementById('status-filter-value').value = value;
    document.getElementById('status-filter-label').textContent = label;
    document.getElementById('status-dropdown')?.classList.add('hidden');
    statusDropdownOpen = false;
    document.getElementById(filterFormId)?.submit();
}

// Assignee dropdown
function toggleAssigneeDropdown() {
    const dropdown = document.getElementById('assignee-dropdown');
    if (!dropdown) return;
    const searchInput = document.getElementById('assignee-search');
    assigneeDropdownOpen = !assigneeDropdownOpen;
    closeAllFilterDropdowns('assignee');
    if (assigneeDropdownOpen) {
        dropdown.classList.remove('hidden');
        if (searchInput) {
            searchInput.value = '';
            searchAssignees('');
            setTimeout(() => searchInput.focus(), 50);
        }
    } else {
        dropdown.classList.add('hidden');
    }
}

function searchAssignees(query) {
    const options = document.querySelectorAll('#assignee-list .assignee-option');
    const lowerQuery = query.toLowerCase().trim();
    options.forEach(option => {
        const name = option.dataset.name || '';
        option.style.display = (lowerQuery === '' || name.includes(lowerQuery) || name === 'all' || name === 'assigned to me') ? '' : 'none';
    });
}

function selectAssignee(value, label) {
    document.getElementById('assignee-filter-value').value = value;
    document.getElementById('assignee-filter-label').textContent = label;
    document.getElementById('assignee-dropdown')?.classList.add('hidden');
    assigneeDropdownOpen = false;
    document.getElementById(filterFormId)?.submit();
}

// Priority dropdown
function togglePriorityDropdown() {
    const dropdown = document.getElementById('priority-dropdown');
    if (!dropdown) return;
    priorityDropdownOpen = !priorityDropdownOpen;
    closeAllFilterDropdowns('priority');
    if (priorityDropdownOpen) {
        dropdown.classList.remove('hidden');
    } else {
        dropdown.classList.add('hidden');
    }
}

function selectPriority(value, label) {
    document.getElementById('priority-filter-value').value = value;
    document.getElementById('priority-filter-label').textContent = label;
    document.getElementById('priority-dropdown')?.classList.add('hidden');
    priorityDropdownOpen = false;
    document.getElementById(filterFormId)?.submit();
}

// Close all dropdowns except the specified one
function closeAllFilterDropdowns(except) {
    if (except !== 'taskFilter') {
        document.getElementById('task-filter-dropdown')?.classList.add('hidden');
        taskFilterDropdownOpen = false;
    }
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
    const dropdowns = [
        { dropdown: 'task-filter-dropdown', btn: 'task-filter-btn' },
        { dropdown: 'workspace-dropdown', btn: 'workspace-filter-btn' },
        { dropdown: 'status-dropdown', btn: 'status-filter-btn' },
        { dropdown: 'assignee-dropdown', btn: 'assignee-filter-btn' },
        { dropdown: 'priority-dropdown', btn: 'priority-filter-btn' },
    ];

    dropdowns.forEach(({ dropdown, btn }) => {
        const dropdownEl = document.getElementById(dropdown);
        const btnEl = document.getElementById(btn);
        if (dropdownEl && btnEl && !dropdownEl.contains(e.target) && !btnEl.contains(e.target)) {
            dropdownEl.classList.add('hidden');
        }
    });
});
</script>
@endpush
