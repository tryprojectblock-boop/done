<!-- Task Detail Drawer -->
<div id="task-drawer" class="fixed inset-0 z-50 hidden">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-black/50 transition-opacity" onclick="closeTaskDrawer()"></div>

    <!-- Drawer Panel -->
    <div class="absolute right-0 top-0 h-full w-full max-w-lg bg-base-100 shadow-xl transform translate-x-full transition-transform duration-300" id="task-drawer-panel">
        <!-- Drawer Header -->
        <div class="flex items-center justify-between p-4 border-b border-base-200">
            <div class="flex items-center gap-2">
                <span class="icon-[tabler--subtask] size-5 text-primary"></span>
                <span class="font-semibold" id="drawer-task-number">Loading...</span>
            </div>
            <button type="button" class="btn btn-ghost btn-sm btn-square" onclick="closeTaskDrawer()">
                <span class="icon-[tabler--x] size-5"></span>
            </button>
        </div>

        <!-- Drawer Content -->
        <div class="overflow-y-auto h-[calc(100vh-140px)] p-4" id="drawer-content">
            <!-- Loading State -->
            <div id="drawer-loading" class="flex items-center justify-center h-48">
                <span class="loading loading-spinner loading-lg text-primary"></span>
            </div>

            <!-- Task Details (hidden by default) -->
            <div id="drawer-task-details" class="hidden space-y-6">
                <!-- Title & Status -->
                <div>
                    <div class="flex items-start gap-2 mb-2">
                        <span id="drawer-status-icon" class="icon-[tabler--circle] size-5 mt-1 text-base-content/30"></span>
                        <h2 class="text-xl font-semibold flex-1" id="drawer-task-title">Task Title</h2>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span id="drawer-task-status" class="badge"></span>
                        <span id="drawer-task-priority" class="badge"></span>
                        <span id="drawer-overdue-badge" class="badge badge-error hidden">Overdue</span>
                    </div>
                </div>

                <!-- Due Date -->
                <div class="flex items-center gap-3 p-3 bg-base-200 rounded-lg">
                    <span class="icon-[tabler--calendar] size-5 text-base-content/60"></span>
                    <div>
                        <div class="text-xs text-base-content/60">Due Date</div>
                        <div class="font-medium" id="drawer-due-date">-</div>
                    </div>
                </div>

                <!-- Description -->
                <div id="drawer-description-section">
                    <h3 class="text-sm font-semibold text-base-content/70 mb-2">Description</h3>
                    <div class="prose prose-sm max-w-none" id="drawer-description">
                        <p class="text-base-content/60 italic">No description</p>
                    </div>
                </div>

                <!-- Details Grid -->
                <div class="grid grid-cols-2 gap-4">
                    <!-- Workspace -->
                    <div>
                        <div class="text-xs text-base-content/60 mb-1">Workspace</div>
                        <div class="flex items-center gap-2" id="drawer-workspace">
                            <span class="icon-[tabler--briefcase] size-4 text-base-content/50"></span>
                            <span>-</span>
                        </div>
                    </div>

                    <!-- Assignee -->
                    <div>
                        <div class="text-xs text-base-content/60 mb-1">Assignee</div>
                        <div class="flex items-center gap-2" id="drawer-assignee">
                            <div class="avatar placeholder">
                                <div class="bg-base-200 text-base-content/50 rounded-full w-6 h-6">
                                    <span class="icon-[tabler--user] size-3"></span>
                                </div>
                            </div>
                            <span>Unassigned</span>
                        </div>
                    </div>

                    <!-- Creator -->
                    <div>
                        <div class="text-xs text-base-content/60 mb-1">Created by</div>
                        <div class="flex items-center gap-2" id="drawer-creator">
                            <div class="avatar">
                                <div class="w-6 h-6 rounded-full">
                                    <img src="" alt="" id="drawer-creator-avatar" />
                                </div>
                            </div>
                            <span id="drawer-creator-name">-</span>
                        </div>
                    </div>

                    <!-- Created Date -->
                    <div>
                        <div class="text-xs text-base-content/60 mb-1">Created</div>
                        <div id="drawer-created-at">-</div>
                    </div>
                </div>

                <!-- Tags -->
                <div id="drawer-tags-section" class="hidden">
                    <h3 class="text-sm font-semibold text-base-content/70 mb-2">Tags</h3>
                    <div class="flex flex-wrap gap-2" id="drawer-tags"></div>
                </div>

                <!-- Time Tracking -->
                <div id="drawer-time-section" class="hidden">
                    <h3 class="text-sm font-semibold text-base-content/70 mb-2">Time Tracking</h3>
                    <div class="flex items-center gap-4">
                        <div>
                            <div class="text-xs text-base-content/60">Estimated</div>
                            <div class="font-medium" id="drawer-estimated-time">-</div>
                        </div>
                        <div>
                            <div class="text-xs text-base-content/60">Actual</div>
                            <div class="font-medium" id="drawer-actual-time">-</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Error State -->
            <div id="drawer-error" class="hidden text-center py-8">
                <span class="icon-[tabler--alert-circle] size-12 text-error mb-4"></span>
                <p class="text-base-content/60">Failed to load task details</p>
            </div>
        </div>

        <!-- Drawer Footer -->
        <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-base-200 bg-base-100">
            <a href="#" id="drawer-read-more" class="btn btn-primary w-full">
                <span class="icon-[tabler--external-link] size-4"></span>
                View Full Details
            </a>
        </div>
    </div>
</div>

@push('scripts')
<script>
let currentTaskUuid = null;

function openTaskDrawer(uuid) {
    currentTaskUuid = uuid;
    const drawer = document.getElementById('task-drawer');
    const panel = document.getElementById('task-drawer-panel');
    const loading = document.getElementById('drawer-loading');
    const details = document.getElementById('drawer-task-details');
    const error = document.getElementById('drawer-error');

    // Show drawer
    drawer.classList.remove('hidden');
    document.body.style.overflow = 'hidden';

    // Animate panel in
    setTimeout(() => {
        panel.classList.remove('translate-x-full');
    }, 10);

    // Show loading
    loading.classList.remove('hidden');
    details.classList.add('hidden');
    error.classList.add('hidden');

    // Fetch task details
    fetch(`{{ url('/calendar/task') }}/${uuid}`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) throw new Error('Task not found');
        return response.json();
    })
    .then(task => {
        populateDrawer(task);
        loading.classList.add('hidden');
        details.classList.remove('hidden');
    })
    .catch(err => {
        console.error('Error loading task:', err);
        loading.classList.add('hidden');
        error.classList.remove('hidden');
    });
}

function closeTaskDrawer() {
    const drawer = document.getElementById('task-drawer');
    const panel = document.getElementById('task-drawer-panel');

    // Animate panel out
    panel.classList.add('translate-x-full');

    // Hide drawer after animation
    setTimeout(() => {
        drawer.classList.add('hidden');
        document.body.style.overflow = '';
    }, 300);

    currentTaskUuid = null;
}

function populateDrawer(task) {
    // Task number
    document.getElementById('drawer-task-number').textContent = task.task_number;

    // Title
    document.getElementById('drawer-task-title').textContent = task.title;

    // Status icon
    const statusIcon = document.getElementById('drawer-status-icon');
    if (task.is_closed) {
        statusIcon.className = 'icon-[tabler--circle-check-filled] size-5 mt-1 text-success';
    } else if (task.is_overdue) {
        statusIcon.className = 'icon-[tabler--alert-circle-filled] size-5 mt-1 text-error';
    } else {
        statusIcon.className = 'icon-[tabler--circle] size-5 mt-1 text-base-content/30';
    }

    // Status badge
    const statusBadge = document.getElementById('drawer-task-status');
    if (task.status) {
        statusBadge.textContent = task.status.name;
        statusBadge.style.backgroundColor = task.status.color + '20';
        statusBadge.style.color = task.status.color;
        statusBadge.style.borderColor = task.status.color + '40';
        statusBadge.className = 'badge border';
        statusBadge.classList.remove('hidden');
    } else {
        statusBadge.classList.add('hidden');
    }

    // Priority badge
    const priorityBadge = document.getElementById('drawer-task-priority');
    if (task.priority_label) {
        priorityBadge.textContent = task.priority_label;
        priorityBadge.style.backgroundColor = task.priority_color + '20';
        priorityBadge.style.color = task.priority_color;
        priorityBadge.style.borderColor = task.priority_color + '40';
        priorityBadge.className = 'badge border';
        priorityBadge.classList.remove('hidden');
    } else {
        priorityBadge.classList.add('hidden');
    }

    // Overdue badge
    const overdueBadge = document.getElementById('drawer-overdue-badge');
    if (task.is_overdue && !task.is_closed) {
        overdueBadge.classList.remove('hidden');
    } else {
        overdueBadge.classList.add('hidden');
    }

    // Due date
    document.getElementById('drawer-due-date').textContent = task.due_date || 'Not set';

    // Description
    const descEl = document.getElementById('drawer-description');
    if (task.description) {
        descEl.innerHTML = task.description;
    } else {
        descEl.innerHTML = '<p class="text-base-content/60 italic">No description</p>';
    }

    // Workspace
    const workspaceEl = document.getElementById('drawer-workspace');
    if (task.workspace) {
        workspaceEl.innerHTML = `
            <span class="icon-[tabler--briefcase] size-4 text-base-content/50"></span>
            <span>${task.workspace.name}</span>
        `;
    } else {
        workspaceEl.innerHTML = `
            <span class="icon-[tabler--briefcase] size-4 text-base-content/50"></span>
            <span class="text-base-content/50">-</span>
        `;
    }

    // Assignee
    const assigneeEl = document.getElementById('drawer-assignee');
    if (task.assignee) {
        assigneeEl.innerHTML = `
            <div class="avatar">
                <div class="w-6 h-6 rounded-full">
                    <img src="${task.assignee.avatar_url}" alt="${task.assignee.name}" />
                </div>
            </div>
            <span>${task.assignee.name}</span>
        `;
    } else {
        assigneeEl.innerHTML = `
            <div class="avatar placeholder">
                <div class="bg-base-200 text-base-content/50 rounded-full w-6 h-6">
                    <span class="icon-[tabler--user] size-3"></span>
                </div>
            </div>
            <span class="text-base-content/50">Unassigned</span>
        `;
    }

    // Creator
    document.getElementById('drawer-creator-avatar').src = task.creator.avatar_url;
    document.getElementById('drawer-creator-avatar').alt = task.creator.name;
    document.getElementById('drawer-creator-name').textContent = task.creator.name;

    // Created date
    document.getElementById('drawer-created-at').textContent = task.created_at;

    // Tags
    const tagsSection = document.getElementById('drawer-tags-section');
    const tagsContainer = document.getElementById('drawer-tags');
    if (task.tags && task.tags.length > 0) {
        tagsContainer.innerHTML = task.tags.map(tag => `
            <span class="badge border" style="background-color: ${tag.color}20; color: ${tag.color}; border-color: ${tag.color}40;">
                ${tag.name}
            </span>
        `).join('');
        tagsSection.classList.remove('hidden');
    } else {
        tagsSection.classList.add('hidden');
    }

    // Time tracking
    const timeSection = document.getElementById('drawer-time-section');
    if (task.estimated_time || task.actual_time) {
        document.getElementById('drawer-estimated-time').textContent = task.estimated_time ? formatMinutes(task.estimated_time) : '-';
        document.getElementById('drawer-actual-time').textContent = task.actual_time ? formatMinutes(task.actual_time) : '-';
        timeSection.classList.remove('hidden');
    } else {
        timeSection.classList.add('hidden');
    }

    // Read more link
    document.getElementById('drawer-read-more').href = task.url;
}

function formatMinutes(minutes) {
    if (!minutes) return '-';
    const hours = Math.floor(minutes / 60);
    const mins = minutes % 60;
    if (hours > 0 && mins > 0) {
        return `${hours}h ${mins}m`;
    } else if (hours > 0) {
        return `${hours}h`;
    }
    return `${mins}m`;
}

// Close on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && currentTaskUuid) {
        closeTaskDrawer();
    }
});
</script>
@endpush
