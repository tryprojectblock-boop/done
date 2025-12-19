{{-- Link Tasks Modal (Custom Popup) --}}
<div id="link-tasks-modal" class="fixed inset-0 z-50 hidden">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black/50 transition-opacity" onclick="closeLinkTasksModal()"></div>

    <!-- Modal Container -->
    <div class="fixed inset-0 flex items-center justify-center p-4 pointer-events-none">
        <div class="bg-base-100 rounded-xl shadow-2xl w-full max-w-2xl max-h-[85vh] overflow-hidden pointer-events-auto transform transition-all">
            <!-- Step 1: Workspace Selection -->
            <div id="link-tasks-step-1">
                <div class="flex items-center justify-between p-4 border-b border-base-300">
                    <h3 class="font-bold text-lg flex items-center gap-2">
                        <span class="icon-[tabler--link] size-5"></span>
                        Link Tasks
                    </h3>
                    <button type="button" class="btn btn-ghost btn-sm btn-circle" onclick="closeLinkTasksModal()">
                        <span class="icon-[tabler--x] size-5"></span>
                    </button>
                </div>

                <div class="p-4">
                    <p class="text-base-content/60 mb-4">Select a workspace to view available tasks.</p>

                    <!-- Workspace Search -->
                    <div class="form-control mb-4">
                        <input type="text"
                               id="workspace-search"
                               class="input input-bordered input-sm w-full"
                               placeholder="Search workspaces..."
                               oninput="filterWorkspaces()">
                    </div>

                    <!-- Workspace List -->
                    <div id="workspace-list" class="space-y-2 max-h-80 overflow-y-auto">
                        @forelse($workspaces as $workspace)
                            <button type="button"
                                    class="workspace-item w-full flex items-center gap-3 p-3 rounded-lg border border-base-300 hover:bg-base-200 hover:border-primary transition-colors text-left"
                                    data-workspace-id="{{ $workspace->id }}"
                                    data-workspace-name="{{ $workspace->name }}"
                                    onclick="selectWorkspace({{ $workspace->id }}, '{{ addslashes($workspace->name) }}', '{{ $workspace->prefix }}')">
                                <div class="w-10 h-10 rounded-lg flex items-center justify-center text-white font-bold"
                                     style="background-color: {{ $workspace->color ?? '#6b7280' }}">
                                    {{ strtoupper(substr($workspace->name, 0, 2)) }}
                                </div>
                                <div class="flex-1">
                                    <p class="font-medium">{{ $workspace->name }}</p>
                                    <p class="text-xs text-base-content/50">{{ $workspace->prefix }}</p>
                                </div>
                                <span class="icon-[tabler--chevron-right] size-5 text-base-content/30"></span>
                            </button>
                        @empty
                            <div class="text-center py-8 text-base-content/60">
                                <span class="icon-[tabler--folder-off] size-12 mx-auto mb-3 opacity-30"></span>
                                <p>No workspaces available.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Step 2: Task Selection -->
            <div id="link-tasks-step-2" class="hidden">
                <div class="flex items-center gap-2 p-4 border-b border-base-300">
                    <button type="button" class="btn btn-ghost btn-sm btn-circle" onclick="goBackToWorkspaces()">
                        <span class="icon-[tabler--arrow-left] size-5"></span>
                    </button>
                    <h3 class="font-bold text-lg flex-1 flex items-center gap-2">
                        <span class="icon-[tabler--subtask] size-5"></span>
                        Select Tasks from <span id="selected-workspace-name" class="text-primary"></span>
                    </h3>
                    <button type="button" class="btn btn-ghost btn-sm btn-circle" onclick="closeLinkTasksModal()">
                        <span class="icon-[tabler--x] size-5"></span>
                    </button>
                </div>

                <div class="p-4">
                    <!-- Task Search and Actions -->
                    <div class="flex items-center gap-2 mb-4">
                        <input type="text"
                               id="task-search"
                               class="input input-bordered input-sm flex-1"
                               placeholder="Search tasks..."
                               oninput="filterTasks()">
                        <button type="button" class="btn btn-ghost btn-xs" onclick="selectAllTasks()">Select All</button>
                        <button type="button" class="btn btn-ghost btn-xs" onclick="clearAllTasks()">Clear All</button>
                    </div>

                    <!-- Loading State -->
                    <div id="tasks-loading" class="hidden text-center py-8">
                        <span class="loading loading-spinner loading-lg"></span>
                        <p class="mt-2 text-base-content/60">Loading tasks...</p>
                    </div>

                    <!-- Task List -->
                    <div id="task-list" class="space-y-2 max-h-64 overflow-y-auto"></div>

                    <!-- Empty State -->
                    <div id="no-tasks-message" class="hidden text-center py-8 text-base-content/60">
                        <span class="icon-[tabler--checkbox] size-12 mx-auto mb-3 opacity-30"></span>
                        <p>No tasks available in this workspace.</p>
                        <p class="text-sm mt-1">All tasks may already be linked or there are no open tasks.</p>
                    </div>
                </div>

                <!-- Footer -->
                <div class="flex items-center justify-end gap-2 p-4 border-t border-base-300 bg-base-200/50">
                    <button type="button" class="btn btn-ghost btn-sm" onclick="goBackToWorkspaces()">
                        Back
                    </button>
                    <button type="button" id="link-tasks-btn" class="btn btn-primary btn-sm" onclick="linkSelectedTasks()" disabled>
                        <span class="icon-[tabler--link] size-4"></span>
                        Link <span id="selected-count">0</span> Task(s)
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Link Tasks Modal State
const linkTasksState = {
    discussionUuid: '{{ $discussion->uuid }}',
    selectedWorkspaceId: null,
    selectedWorkspaceName: '',
    selectedWorkspacePrefix: '',
    tasks: [],
    selectedTaskIds: new Set()
};

function openLinkTasksModal() {
    const modal = document.getElementById('link-tasks-modal');
    if (modal) {
        // Reset to step 1
        document.getElementById('link-tasks-step-1').classList.remove('hidden');
        document.getElementById('link-tasks-step-2').classList.add('hidden');
        document.getElementById('workspace-search').value = '';
        filterWorkspaces();

        // Show modal
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
}

function closeLinkTasksModal() {
    const modal = document.getElementById('link-tasks-modal');
    if (modal) {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }
}

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeLinkTasksModal();
    }
});

function filterWorkspaces() {
    const search = document.getElementById('workspace-search').value.toLowerCase();
    const items = document.querySelectorAll('.workspace-item');

    items.forEach(item => {
        const name = item.dataset.workspaceName.toLowerCase();
        item.style.display = name.includes(search) ? '' : 'none';
    });
}

async function selectWorkspace(workspaceId, workspaceName, workspacePrefix) {
    linkTasksState.selectedWorkspaceId = workspaceId;
    linkTasksState.selectedWorkspaceName = workspaceName;
    linkTasksState.selectedWorkspacePrefix = workspacePrefix;
    linkTasksState.selectedTaskIds.clear();

    // Update UI
    document.getElementById('selected-workspace-name').textContent = workspaceName;
    document.getElementById('link-tasks-step-1').classList.add('hidden');
    document.getElementById('link-tasks-step-2').classList.remove('hidden');

    // Show loading
    document.getElementById('tasks-loading').classList.remove('hidden');
    document.getElementById('task-list').classList.add('hidden');
    document.getElementById('no-tasks-message').classList.add('hidden');

    // Fetch tasks for workspace
    try {
        const url = `/discussions/${linkTasksState.discussionUuid}/workspace/${workspaceId}/tasks`;
        console.log('Fetching tasks from:', url);

        const response = await fetch(url, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        console.log('Response status:', response.status);

        if (!response.ok) {
            const errorText = await response.text();
            console.error('Response error:', errorText);
            throw new Error(`HTTP ${response.status}: ${errorText}`);
        }

        const data = await response.json();
        console.log('Response data:', data);

        document.getElementById('tasks-loading').classList.add('hidden');

        if (data.success && data.tasks && data.tasks.length > 0) {
            linkTasksState.tasks = data.tasks;
            renderTasks(data.tasks);
            document.getElementById('task-list').classList.remove('hidden');
        } else {
            linkTasksState.tasks = [];
            document.getElementById('no-tasks-message').classList.remove('hidden');
            console.log('No tasks found or empty response:', data);
        }
    } catch (error) {
        console.error('Error loading tasks:', error);
        document.getElementById('tasks-loading').classList.add('hidden');
        document.getElementById('task-list').innerHTML = `<p class="text-error text-center py-4">Failed to load tasks: ${error.message}</p>`;
        document.getElementById('task-list').classList.remove('hidden');
    }

    updateSelectedCount();
}

function renderTasks(tasks) {
    const container = document.getElementById('task-list');
    container.innerHTML = tasks.map(task => {
        const statusColor = task.status?.color || '#6b7280';
        const r = parseInt(statusColor.slice(1, 3), 16);
        const g = parseInt(statusColor.slice(3, 5), 16);
        const b = parseInt(statusColor.slice(5, 7), 16);

        return `
            <label class="task-item flex items-center gap-3 p-3 rounded-lg border border-base-300 hover:bg-base-200 cursor-pointer transition-colors"
                   data-task-id="${task.id}"
                   data-task-title="${escapeHtml(task.title)}">
                <input type="checkbox"
                       class="checkbox checkbox-sm checkbox-primary task-checkbox"
                       value="${task.id}"
                       onchange="toggleTaskSelection(${task.id})">
                <span class="badge badge-sm font-mono" style="background-color: rgba(${r}, ${g}, ${b}, 0.15); color: ${statusColor}; border: 1px solid rgba(${r}, ${g}, ${b}, 0.3);">
                    ${task.full_number}
                </span>
                <span class="flex-1 truncate">${escapeHtml(task.title)}</span>
                ${task.status ? `<span class="badge badge-sm" style="background-color: rgba(${r}, ${g}, ${b}, 0.15); color: ${statusColor};">${escapeHtml(task.status.name)}</span>` : ''}
            </label>
        `;
    }).join('');
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function filterTasks() {
    const search = document.getElementById('task-search').value.toLowerCase();
    const items = document.querySelectorAll('.task-item');

    items.forEach(item => {
        const title = item.dataset.taskTitle.toLowerCase();
        item.style.display = title.includes(search) ? '' : 'none';
    });
}

function toggleTaskSelection(taskId) {
    if (linkTasksState.selectedTaskIds.has(taskId)) {
        linkTasksState.selectedTaskIds.delete(taskId);
    } else {
        linkTasksState.selectedTaskIds.add(taskId);
    }
    updateSelectedCount();
}

function selectAllTasks() {
    document.querySelectorAll('.task-checkbox').forEach(checkbox => {
        if (checkbox.closest('.task-item').style.display !== 'none') {
            checkbox.checked = true;
            linkTasksState.selectedTaskIds.add(parseInt(checkbox.value));
        }
    });
    updateSelectedCount();
}

function clearAllTasks() {
    document.querySelectorAll('.task-checkbox').forEach(checkbox => {
        checkbox.checked = false;
    });
    linkTasksState.selectedTaskIds.clear();
    updateSelectedCount();
}

function updateSelectedCount() {
    const count = linkTasksState.selectedTaskIds.size;
    document.getElementById('selected-count').textContent = count;
    document.getElementById('link-tasks-btn').disabled = count === 0;
}

function goBackToWorkspaces() {
    document.getElementById('link-tasks-step-1').classList.remove('hidden');
    document.getElementById('link-tasks-step-2').classList.add('hidden');
    document.getElementById('task-search').value = '';
    linkTasksState.selectedTaskIds.clear();
    updateSelectedCount();
}

async function linkSelectedTasks() {
    if (linkTasksState.selectedTaskIds.size === 0) return;

    const btn = document.getElementById('link-tasks-btn');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="loading loading-spinner loading-xs"></span> Linking...';

    try {
        const response = await fetch(`/discussions/${linkTasksState.discussionUuid}/link-tasks`, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                task_ids: Array.from(linkTasksState.selectedTaskIds)
            })
        });

        const data = await response.json();

        if (data.success) {
            // Update the linked tasks list
            updateLinkedTasksList(data.linked_tasks);

            // Update count
            const countBadge = document.getElementById('linked-tasks-count');
            if (countBadge) {
                countBadge.textContent = data.linked_tasks.length;
            }

            // Close modal
            closeLinkTasksModal();

            // Switch to tasks tab
            switchTab('tasks');
        } else {
            alert(data.error || 'Failed to link tasks');
        }
    } catch (error) {
        console.error('Error linking tasks:', error);
        alert('An error occurred while linking tasks');
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}

function updateLinkedTasksList(linkedTasks) {
    const container = document.getElementById('linked-tasks-list');
    if (!container) return;

    // Remove empty message
    const emptyMsg = document.getElementById('no-linked-tasks-message');
    if (emptyMsg) {
        emptyMsg.remove();
    }

    // Rebuild the list
    container.innerHTML = linkedTasks.map(task => {
        const statusColor = task.status?.color || '#6b7280';
        const r = parseInt(statusColor.slice(1, 3), 16);
        const g = parseInt(statusColor.slice(3, 5), 16);
        const b = parseInt(statusColor.slice(5, 7), 16);
        const escapedTitle = escapeHtml(task.title).replace(/'/g, "\\'");

        return `
            <div class="flex items-center gap-3 p-3 rounded-lg border border-base-300 hover:bg-base-200 transition-colors"
                 id="linked-task-${task.id}">
                <a href="/tasks/${task.uuid}" class="flex-1 flex items-center gap-3">
                    <span class="badge badge-sm font-mono" style="background-color: rgba(${r}, ${g}, ${b}, 0.15); color: ${statusColor}; border: 1px solid rgba(${r}, ${g}, ${b}, 0.3);">
                        ${task.full_number}
                    </span>
                    <span class="flex-1 font-medium truncate">${escapeHtml(task.title)}</span>
                    ${task.status ? `<span class="badge badge-sm" style="background-color: rgba(${r}, ${g}, ${b}, 0.15); color: ${statusColor};">${escapeHtml(task.status.name)}</span>` : ''}
                    ${task.workspace_name ? `<span class="text-xs text-base-content/50">${escapeHtml(task.workspace_name)}</span>` : ''}
                </a>
                <button type="button"
                        class="btn btn-ghost btn-xs text-error"
                        onclick="openUnlinkTaskModal(${task.id}, '${task.uuid}', '${escapedTitle}')"
                        title="Unlink task">
                    <span class="icon-[tabler--link-off] size-4"></span>
                </button>
            </div>
        `;
    }).join('');

    // Show empty message if no tasks
    if (linkedTasks.length === 0) {
        container.innerHTML = `
            <div id="no-linked-tasks-message" class="text-center py-8 text-base-content/60">
                <span class="icon-[tabler--subtask] size-12 mx-auto mb-3 opacity-30"></span>
                <p>No tasks linked to this discussion yet.</p>
                <p class="text-sm mt-1">Click "Link Tasks" to add existing tasks.</p>
            </div>
        `;
    }
}
</script>
