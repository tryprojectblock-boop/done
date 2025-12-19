@php
    $isInbox = $workspace->type->value === 'inbox';
    $taskLabel = $isInbox ? 'Ticket' : 'Task';
    $tasksLabel = $isInbox ? 'Tickets' : 'Tasks';

    // Get active statuses
    $activeStatuses = $workspace->workflow?->statuses()
        ->where('is_active', true)
        ->orderBy('sort_order')
        ->get() ?? collect();

    // Get inactive/terminal statuses (statuses with no outgoing transitions)
    $inactiveStatuses = $workspace->workflow?->statuses()
        ->where('is_active', false)
        ->orderBy('sort_order')
        ->get() ?? collect();

    // Combine: active statuses first, then inactive statuses at the end
    $statuses = $activeStatuses->concat($inactiveStatuses);

    // Get all on-hold tasks
    $onHoldTasks = $tasks->where('is_on_hold', true);

    // Get unique team members (assignees) from tasks
    $teamMembers = $tasks->pluck('assignee')->filter()->unique('id')->sortBy('name');
    $currentUserId = auth()->id();
@endphp

<div class="space-y-4">
    <!-- Header -->
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h2 class="text-lg font-semibold">{{ $tasksLabel }} Board</h2>
            <p class="text-sm text-base-content/60">Drag and drop {{ strtolower($tasksLabel) }} to change their status</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <!-- Assignee Filter -->
            <div class="relative">
                <button type="button" id="assignee-filter-btn" onclick="toggleAssigneeDropdown()" class="btn btn-sm btn-ghost gap-2">
                    <span class="icon-[tabler--users] size-4"></span>
                    <span id="assignee-filter-label">All {{ $tasksLabel }}</span>
                    <span class="icon-[tabler--chevron-down] size-4"></span>
                </button>
                <div id="assignee-dropdown" class="hidden absolute right-0 top-full mt-2 w-64 bg-base-100 rounded-xl shadow-xl border border-base-300 z-[100]">
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
                            <button type="button" onclick="filterByAssignee('all', 'All {{ $tasksLabel }}')" class="w-full text-left px-3 py-2 rounded-lg hover:bg-base-200 flex items-center gap-2">
                                <span class="icon-[tabler--users] size-4 text-base-content/60"></span>
                                <span>All {{ $tasksLabel }}</span>
                            </button>
                        </li>
                        <li class="assignee-option" data-name="my">
                            <button type="button" onclick="filterByAssignee('{{ $currentUserId }}', 'My {{ $tasksLabel }}')" class="w-full text-left px-3 py-2 rounded-lg hover:bg-base-200 flex items-center gap-2">
                                <span class="icon-[tabler--user] size-4 text-primary"></span>
                                <span class="font-medium">My {{ $tasksLabel }}</span>
                            </button>
                        </li>
                        @if($teamMembers->count() > 0)
                            <li class="border-t border-base-200 my-1"></li>
                            <li class="px-3 py-1 text-xs text-base-content/50 uppercase tracking-wide">Team Members</li>
                            @foreach($teamMembers as $member)
                                <li class="assignee-option" data-name="{{ strtolower($member->name) }}">
                                    <button type="button" onclick="filterByAssignee('{{ $member->id }}', '{{ $member->name }}')" class="w-full text-left px-3 py-2 rounded-lg hover:bg-base-200 flex items-center gap-2">
                                        <div class="avatar">
                                            <div class="w-5 h-5 rounded-full">
                                                <img src="{{ $member->avatar_url }}" alt="{{ $member->name }}" />
                                            </div>
                                        </div>
                                        <span class="{{ $member->id == $currentUserId ? 'font-medium' : '' }}">{{ $member->name }}</span>
                                    </button>
                                </li>
                            @endforeach
                        @endif
                    </ul>
                </div>
            </div>

            @if($onHoldTasks->count() > 0)
                <button type="button"
                        id="on-hold-filter-btn"
                        onclick="toggleOnHoldFilter()"
                        class="btn btn-sm btn-outline btn-warning gap-1">
                    <span class="icon-[tabler--player-pause] size-4"></span>
                    On Hold
                    <span class="badge badge-sm badge-warning">{{ $onHoldTasks->count() }}</span>
                </button>
            @endif
        </div>
    </div>

    @if($statuses->isEmpty())
        <!-- No Workflow Setup -->
        <div class="card bg-base-100 shadow">
            <div class="card-body items-center text-center py-12">
                <span class="icon-[tabler--layout-kanban] size-16 text-base-content/20 mb-4"></span>
                <h3 class="text-lg font-semibold">No Workflow Configured</h3>
                <p class="text-base-content/60 mb-4">Set up a workflow with statuses to use the board view.</p>
                <a href="{{ route('workspace.settings', $workspace) }}" class="btn btn-primary">
                    <span class="icon-[tabler--settings] size-4"></span>
                    Configure Workflow
                </a>
            </div>
        </div>
    @else
        <!-- Kanban Board -->
        <div class="kanban-board overflow-x-auto pb-4">
            <div class="flex gap-4 min-w-max items-start">
                @foreach($statuses as $status)
                    @php
                        $statusTasks = $tasks->where('status_id', $status->id);
                    @endphp
                    <div class="kanban-column w-80 flex-shrink-0 {{ !$status->is_active ? 'opacity-75' : '' }}" data-column-status-id="{{ $status->id }}">
                        <!-- Column Header -->
                        <div class="flex items-center justify-between p-3 rounded-t-xl" style="background-color: {{ $status->background_color }}20;">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full" style="background-color: {{ $status->background_color }};"></span>
                                <h3 class="font-semibold" style="color: {{ $status->background_color }};">{{ $status->name }}</h3>
                                @if(!$status->is_active)
                                    <span class="tooltip tooltip-top" data-tip="Inactive status - no transitions from here">
                                        <span class="icon-[tabler--lock] size-4" style="color: {{ $status->background_color }};"></span>
                                    </span>
                                @endif
                                <span class="badge badge-sm column-count-badge" style="background-color: {{ $status->background_color }}30; color: {{ $status->background_color }};">
                                    {{ $statusTasks->count() }}
                                </span>
                            </div>
                        </div>

                        <!-- Column Body (Droppable) -->
                        <div class="kanban-column-body bg-base-200/50 rounded-b-xl p-2 min-h-[200px] space-y-2"
                             data-status-id="{{ $status->id }}"
                             data-status-name="{{ $status->name }}"
                             data-status-active="{{ $status->is_active ? 'true' : 'false' }}"
                             ondragover="handleDragOver(event)"
                             ondragleave="handleDragLeave(event)"
                             ondrop="handleDrop(event)">

                            @forelse($statusTasks as $task)
                                @php
                                    $canChangeStatus = $task->canChangeStatus(auth()->user());
                                    $isDraggable = !$task->is_on_hold && $canChangeStatus;
                                @endphp
                                <!-- Task Card (Draggable) -->
                                <div class="kanban-card bg-base-100 rounded-lg p-3 shadow-sm hover:shadow-md transition-shadow border border-base-300 {{ $task->is_on_hold ? 'ring-2 ring-warning/50 cursor-not-allowed opacity-80' : ($canChangeStatus ? 'cursor-grab' : 'cursor-default') }}"
                                     draggable="{{ $isDraggable ? 'true' : 'false' }}"
                                     data-task-id="{{ $task->id }}"
                                     data-task-uuid="{{ $task->uuid }}"
                                     data-task-title="{{ $task->title }}"
                                     data-current-status-id="{{ $task->status_id }}"
                                     data-assignee-id="{{ $task->assignee_id ?? '' }}"
                                     data-on-hold="{{ $task->is_on_hold ? 'true' : 'false' }}"
                                     data-can-change="{{ $canChangeStatus ? 'true' : 'false' }}"
                                     ondragstart="handleDragStart(event)"
                                     ondragend="handleDragEnd(event)">

                                    <!-- Task Header -->
                                    <div class="flex items-start justify-between gap-2 mb-2">
                                        <a href="{{ route('tasks.show', $task) }}" class="font-medium text-sm hover:text-primary transition-colors line-clamp-2">
                                            {{ $task->title }}
                                        </a>
                                        @if($task->priority)
                                            <span class="flex-shrink-0" title="{{ $task->priority->label() }}">
                                                <span class="icon-[{{ $task->priority->icon() }}] size-4" style="color: {{ $task->priority->color() }};"></span>
                                            </span>
                                        @endif
                                    </div>

                                    <!-- Task Number & On Hold Badge -->
                                    <div class="flex items-center gap-2 text-xs text-base-content/50 mb-2">
                                        <span>{{ $task->task_number }}</span>
                                        @if($task->is_on_hold)
                                            <span class="badge badge-xs badge-warning gap-1">
                                                <span class="icon-[tabler--player-pause] size-3"></span>
                                                On Hold
                                            </span>
                                        @endif
                                    </div>

                                    <!-- Progress Bar -->
                                    @if(($task->progress ?? 0) > 0)
                                    <div class="mb-2">
                                        <div class="flex items-center gap-2">
                                            <div class="flex-1 bg-base-200 rounded-full h-1.5">
                                                <div class="bg-primary h-1.5 rounded-full" style="width: {{ $task->progress }}%;"></div>
                                            </div>
                                            <span class="text-xs text-base-content/50">{{ $task->progress }}%</span>
                                        </div>
                                    </div>
                                    @endif

                                    <!-- Task Footer -->
                                    <div class="flex items-center justify-between">
                                        <!-- Assignee -->
                                        @if($task->assignee)
                                            <div class="flex items-center gap-1.5" title="{{ $task->assignee->name }}">
                                                <div class="avatar">
                                                    <div class="w-6 h-6 rounded-full">
                                                        <img src="{{ $task->assignee->avatar_url }}" alt="{{ $task->assignee->name }}" />
                                                    </div>
                                                </div>
                                                <span class="text-xs text-base-content/60 truncate max-w-[80px]">{{ $task->assignee->name }}</span>
                                            </div>
                                        @else
                                            <span class="text-xs text-base-content/40">Unassigned</span>
                                        @endif

                                        <!-- Due Date -->
                                        @if($task->due_date)
                                            <span class="text-xs {{ $task->isOverdue() ? 'text-error font-medium' : 'text-base-content/50' }}" title="Due: {{ $task->due_date->format('M d, Y') }}">
                                                <span class="icon-[tabler--calendar] size-3 inline"></span>
                                                {{ $task->due_date->format('M d') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="flex items-center justify-center h-20 text-base-content/40 text-sm">
                                    No {{ strtolower($tasksLabel) }}
                                </div>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

<!-- Status Change Confirmation Modal -->
<div id="status-change-modal" class="fixed inset-0 z-[100] hidden">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black/50 transition-opacity" onclick="closeStatusChangeModal()"></div>

    <!-- Modal -->
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="bg-base-100 rounded-xl shadow-2xl max-w-md w-full p-6 relative">
            <!-- Header -->
            <div class="flex items-center gap-3 mb-4">
                <div class="w-12 h-12 rounded-full bg-primary/20 flex items-center justify-center">
                    <span class="icon-[tabler--arrows-exchange] size-6 text-primary"></span>
                </div>
                <div>
                    <h3 class="text-lg font-bold">Change Status</h3>
                    <p class="text-sm text-base-content/60" id="status-change-subtitle">Moving task to new status</p>
                </div>
            </div>

            <!-- Task Info -->
            <div class="bg-base-200 rounded-lg p-3 mb-4">
                <p class="text-sm font-medium" id="status-change-task-title">Task Title</p>
                <div class="flex items-center gap-2 mt-2">
                    <span class="badge badge-sm" id="status-change-from-badge">From</span>
                    <span class="icon-[tabler--arrow-right] size-4 text-base-content/40"></span>
                    <span class="badge badge-sm badge-primary" id="status-change-to-badge">To</span>
                </div>
            </div>

            <!-- Note Input -->
            <div class="form-control mb-4">
                <label class="label">
                    <span class="label-text font-medium">Add a note (optional)</span>
                </label>
                <textarea id="status-change-note"
                          class="textarea textarea-bordered w-full"
                          rows="3"
                          placeholder="Why are you changing the status? (e.g., 'Completed review', 'Blocked by dependency')"></textarea>
            </div>

            <!-- Error Message -->
            <div id="status-change-error" class="alert alert-error mb-4 hidden">
                <span class="icon-[tabler--alert-circle] size-5"></span>
                <span id="status-change-error-text"></span>
            </div>

            <!-- Actions -->
            <div class="flex justify-end gap-2">
                <button type="button" class="btn btn-ghost" onclick="closeStatusChangeModal()">Cancel</button>
                <button type="button" class="btn btn-primary" id="status-change-confirm-btn" onclick="confirmStatusChange()">
                    <span class="icon-[tabler--check] size-5"></span>
                    Confirm Change
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* Board horizontal scroll */
.kanban-board {
    scrollbar-width: thin;
    scrollbar-color: hsl(var(--bc) / 0.2) transparent;
}

.kanban-board::-webkit-scrollbar {
    height: 8px;
}

.kanban-board::-webkit-scrollbar-track {
    background: transparent;
}

.kanban-board::-webkit-scrollbar-thumb {
    background-color: hsl(var(--bc) / 0.2);
    border-radius: 4px;
}

/* Each column has its own vertical scroll */
.kanban-column-body {
    height: calc(100vh - 300px);
    overflow-y: auto;
    scrollbar-width: thin;
    scrollbar-color: hsl(var(--bc) / 0.2) transparent;
}

.kanban-column-body::-webkit-scrollbar {
    width: 6px;
}

.kanban-column-body::-webkit-scrollbar-track {
    background: transparent;
}

.kanban-column-body::-webkit-scrollbar-thumb {
    background-color: hsl(var(--bc) / 0.2);
    border-radius: 3px;
}

.kanban-column-body::-webkit-scrollbar-thumb:hover {
    background-color: hsl(var(--bc) / 0.4);
}

.kanban-card {
    transition: transform 0.15s ease, box-shadow 0.15s ease, opacity 0.15s ease;
}

.kanban-card.dragging {
    opacity: 0.5;
    transform: rotate(3deg);
    cursor: grabbing;
}

.kanban-column-body.drag-over {
    background-color: hsl(var(--p) / 0.1);
    border: 2px dashed hsl(var(--p));
    border-radius: 0.75rem;
}

.kanban-card.drag-ghost {
    opacity: 0.3;
}
</style>

@push('scripts')
<script>
// Filter states
let onHoldFilterActive = false;
let currentAssigneeFilter = 'all';
let assigneeDropdownOpen = false;

// Assignee dropdown toggle
function toggleAssigneeDropdown() {
    const dropdown = document.getElementById('assignee-dropdown');
    const searchInput = document.getElementById('assignee-search');
    assigneeDropdownOpen = !assigneeDropdownOpen;

    if (assigneeDropdownOpen) {
        dropdown.classList.remove('hidden');
        searchInput.value = '';
        searchAssignees('');
        setTimeout(() => searchInput.focus(), 50);
    } else {
        dropdown.classList.add('hidden');
    }
}

function closeAssigneeDropdown() {
    const dropdown = document.getElementById('assignee-dropdown');
    dropdown.classList.add('hidden');
    assigneeDropdownOpen = false;
}

// Search assignees
function searchAssignees(query) {
    const options = document.querySelectorAll('#assignee-list .assignee-option');
    const lowerQuery = query.toLowerCase().trim();

    options.forEach(option => {
        const name = option.dataset.name || '';
        if (lowerQuery === '' || name.includes(lowerQuery) || name === 'all' || name === 'my') {
            option.style.display = '';
        } else {
            option.style.display = 'none';
        }
    });
}

// Assignee filter
function filterByAssignee(assigneeId, label) {
    currentAssigneeFilter = assigneeId;
    document.getElementById('assignee-filter-label').textContent = label;

    // Close dropdown
    closeAssigneeDropdown();

    applyFilters();
}

// Close dropdown when clicking outside
document.addEventListener('click', function(e) {
    const dropdown = document.getElementById('assignee-dropdown');
    const btn = document.getElementById('assignee-filter-btn');
    if (dropdown && btn && !dropdown.contains(e.target) && !btn.contains(e.target)) {
        closeAssigneeDropdown();
    }
});

function toggleOnHoldFilter() {
    const btn = document.getElementById('on-hold-filter-btn');
    onHoldFilterActive = !onHoldFilterActive;

    if (onHoldFilterActive) {
        btn.classList.remove('btn-outline');
        btn.classList.add('btn-active');
    } else {
        btn.classList.add('btn-outline');
        btn.classList.remove('btn-active');
    }

    applyFilters();
}

function applyFilters() {
    const cards = document.querySelectorAll('.kanban-card');

    cards.forEach(card => {
        let show = true;

        // Apply assignee filter
        if (currentAssigneeFilter !== 'all') {
            const cardAssignee = card.dataset.assigneeId || '';
            if (cardAssignee !== currentAssigneeFilter) {
                show = false;
            }
        }

        // Apply on-hold filter
        if (onHoldFilterActive && card.dataset.onHold !== 'true') {
            show = false;
        }

        card.style.display = show ? '' : 'none';
    });

    updateAllColumnCounts();
}

function updateAllColumnCounts() {
    document.querySelectorAll('.kanban-column').forEach(column => {
        const columnBody = column.querySelector('.kanban-column-body');
        const cards = columnBody.querySelectorAll('.kanban-card');
        const visibleCount = Array.from(cards).filter(card => card.style.display !== 'none').length;
        const countBadge = column.querySelector('.column-count-badge');
        if (countBadge) {
            countBadge.textContent = visibleCount;
        }
    });
}

// Drag and drop state
let draggedTask = null;
let draggedTaskData = {};

function handleDragStart(event) {
    const card = event.target.closest('.kanban-card');
    if (!card) return;

    // Prevent dragging on-hold tasks
    if (card.dataset.onHold === 'true') {
        event.preventDefault();
        showToast('On Hold tasks cannot be moved. Resume the task first.', 'warning');
        return;
    }

    // Prevent dragging tasks user can't change
    if (card.dataset.canChange !== 'true') {
        event.preventDefault();
        showToast('You can only change status of tasks assigned to you.', 'warning');
        return;
    }

    draggedTask = card;
    draggedTaskData = {
        taskId: card.dataset.taskId,
        taskUuid: card.dataset.taskUuid,
        taskTitle: card.dataset.taskTitle,
        currentStatusId: card.dataset.currentStatusId,
        onHold: card.dataset.onHold === 'true',
        canChange: card.dataset.canChange === 'true'
    };

    card.classList.add('dragging');
    event.dataTransfer.effectAllowed = 'move';
    event.dataTransfer.setData('text/plain', card.dataset.taskId);
}

function handleDragEnd(event) {
    const card = event.target.closest('.kanban-card');
    if (card) {
        card.classList.remove('dragging');
    }

    // Remove drag-over from all columns
    document.querySelectorAll('.kanban-column-body').forEach(col => {
        col.classList.remove('drag-over');
    });
}

function handleDragOver(event) {
    event.preventDefault();
    event.dataTransfer.dropEffect = 'move';

    const column = event.target.closest('.kanban-column-body');
    if (column) {
        column.classList.add('drag-over');
    }
}

function handleDragLeave(event) {
    const column = event.target.closest('.kanban-column-body');
    if (column && !column.contains(event.relatedTarget)) {
        column.classList.remove('drag-over');
    }
}

function handleDrop(event) {
    event.preventDefault();

    const column = event.target.closest('.kanban-column-body');
    if (!column || !draggedTask) return;

    column.classList.remove('drag-over');

    // Safety check: prevent on-hold tasks from being moved
    if (draggedTaskData.onHold) {
        showToast('On Hold tasks cannot be moved. Resume the task first.', 'warning');
        return;
    }

    const newStatusId = column.dataset.statusId;
    const newStatusName = column.dataset.statusName;

    // Check if status actually changed
    if (newStatusId === draggedTaskData.currentStatusId) {
        // Just reorder within same column (optional: could implement later)
        return;
    }

    // Get current status name
    const currentColumn = document.querySelector(`.kanban-column-body[data-status-id="${draggedTaskData.currentStatusId}"]`);
    const currentStatusName = currentColumn ? currentColumn.dataset.statusName : 'Unknown';

    // Open confirmation modal
    openStatusChangeModal({
        taskId: draggedTaskData.taskId,
        taskUuid: draggedTaskData.taskUuid,
        taskTitle: draggedTaskData.taskTitle,
        fromStatusId: draggedTaskData.currentStatusId,
        fromStatusName: currentStatusName,
        toStatusId: newStatusId,
        toStatusName: newStatusName,
        targetColumn: column
    });
}

// Modal state
let pendingStatusChange = null;

function openStatusChangeModal(data) {
    pendingStatusChange = data;

    // Update modal content
    document.getElementById('status-change-task-title').textContent = data.taskTitle;
    document.getElementById('status-change-subtitle').textContent = `Moving "${data.taskTitle}" to ${data.toStatusName}`;
    document.getElementById('status-change-from-badge').textContent = data.fromStatusName;
    document.getElementById('status-change-to-badge').textContent = data.toStatusName;
    document.getElementById('status-change-note').value = '';
    document.getElementById('status-change-error').classList.add('hidden');

    // Show modal
    document.getElementById('status-change-modal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';

    // Focus on note input
    setTimeout(() => {
        document.getElementById('status-change-note').focus();
    }, 100);
}

function closeStatusChangeModal() {
    document.getElementById('status-change-modal').classList.add('hidden');
    document.body.style.overflow = '';
    pendingStatusChange = null;
    draggedTask = null;
    draggedTaskData = {};
}

async function confirmStatusChange() {
    if (!pendingStatusChange) return;

    const btn = document.getElementById('status-change-confirm-btn');
    const originalText = btn.innerHTML;
    const note = document.getElementById('status-change-note').value.trim();
    const errorEl = document.getElementById('status-change-error');
    const errorText = document.getElementById('status-change-error-text');

    // Hide previous errors
    errorEl.classList.add('hidden');

    // Show loading
    btn.disabled = true;
    btn.innerHTML = '<span class="loading loading-spinner loading-sm"></span> Updating...';

    try {
        const response = await fetch(`/tasks/${pendingStatusChange.taskUuid}/status`, {
            method: 'PATCH',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                status_id: pendingStatusChange.toStatusId,
                note: note
            })
        });

        const data = await response.json();

        if (response.ok && data.success) {
            // Move the card to the new column
            if (draggedTask && pendingStatusChange.targetColumn) {
                // Update card's data attribute
                draggedTask.dataset.currentStatusId = pendingStatusChange.toStatusId;

                // Remove "No tasks" placeholder if exists
                const placeholder = pendingStatusChange.targetColumn.querySelector('.flex.items-center.justify-center');
                if (placeholder) {
                    placeholder.remove();
                }

                // Move card to new column
                pendingStatusChange.targetColumn.appendChild(draggedTask);

                // Update counts
                updateColumnCounts();

                // Check if old column is empty and add placeholder
                const oldColumn = document.querySelector(`.kanban-column-body[data-status-id="${pendingStatusChange.fromStatusId}"]`);
                if (oldColumn && oldColumn.querySelectorAll('.kanban-card').length === 0) {
                    oldColumn.innerHTML = `<div class="flex items-center justify-center h-20 text-base-content/40 text-sm">No {{ strtolower($tasksLabel) }}</div>`;
                }
            }

            closeStatusChangeModal();
        } else {
            errorText.textContent = data.message || 'Failed to update status';
            errorEl.classList.remove('hidden');
        }
    } catch (error) {
        console.error('Error updating status:', error);
        errorText.textContent = 'An error occurred. Please try again.';
        errorEl.classList.remove('hidden');
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}

function updateColumnCounts() {
    document.querySelectorAll('.kanban-column-body').forEach(column => {
        const statusId = column.dataset.statusId;
        const count = column.querySelectorAll('.kanban-card').length;
        const countBadge = column.parentElement.querySelector('.badge');
        if (countBadge) {
            countBadge.textContent = count;
        }
    });
}

// Close modal on Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modal = document.getElementById('status-change-modal');
        if (modal && !modal.classList.contains('hidden')) {
            closeStatusChangeModal();
        }
    }
});

// Toast notification
function showToast(message, type = 'info') {
    // Remove existing toast
    const existingToast = document.getElementById('kanban-toast');
    if (existingToast) existingToast.remove();

    const alertClass = {
        'info': 'alert-info',
        'success': 'alert-success',
        'warning': 'alert-warning',
        'error': 'alert-error'
    }[type] || 'alert-info';

    const iconClass = {
        'info': 'icon-[tabler--info-circle]',
        'success': 'icon-[tabler--check]',
        'warning': 'icon-[tabler--alert-triangle]',
        'error': 'icon-[tabler--x]'
    }[type] || 'icon-[tabler--info-circle]';

    const toast = document.createElement('div');
    toast.id = 'kanban-toast';
    toast.className = `fixed bottom-4 right-4 z-[200] alert ${alertClass} shadow-lg max-w-sm animate-fade-in`;
    toast.innerHTML = `
        <span class="${iconClass} size-5"></span>
        <span>${message}</span>
    `;

    document.body.appendChild(toast);

    // Auto remove after 3 seconds
    setTimeout(() => {
        toast.classList.add('opacity-0', 'transition-opacity');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}
</script>
@endpush
