{{-- Priorities Drawer --}}
@php
    $priorities = $workspace->priorities()->orderBy('sort_order')->get();
@endphp

<div id="priorities-drawer" class="fixed inset-0 z-50 hidden">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-black/50 transition-opacity" onclick="closePrioritiesDrawer()"></div>

    <!-- Drawer Panel -->
    <div class="absolute right-0 top-0 h-full w-full max-w-xl bg-base-100 shadow-xl transform translate-x-full transition-transform duration-300" id="priorities-drawer-panel">
        <!-- Drawer Header -->
        <div class="flex items-center justify-between p-4 border-b border-base-200">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-error/10 flex items-center justify-center">
                    <span class="icon-[tabler--flag] size-5 text-error"></span>
                </div>
                <div>
                    <h3 class="font-semibold text-lg">Priorities</h3>
                    <p class="text-sm text-base-content/60">Configure priority levels for tickets</p>
                </div>
            </div>
            <button type="button" class="btn btn-ghost btn-sm btn-square" onclick="closePrioritiesDrawer()">
                <span class="icon-[tabler--x] size-5"></span>
            </button>
        </div>

        <!-- Drawer Content -->
        <div class="overflow-y-auto h-[calc(100vh-160px)] p-4 space-y-6">

            <!-- Priorities List -->
            <div>
                <div class="flex items-center justify-between mb-3">
                    <h4 class="font-medium text-sm flex items-center gap-2">
                        <span class="icon-[tabler--list] size-4"></span>
                        Priority Levels
                        <span class="badge badge-ghost badge-sm" id="priorities-count">{{ $priorities->count() }}</span>
                    </h4>
                    <button type="button" class="btn btn-primary btn-xs gap-1" onclick="showAddPriorityForm()">
                        <span class="icon-[tabler--plus] size-4"></span>
                        Add Priority
                    </button>
                </div>

                <!-- Add Priority Form (Hidden by default) -->
                <div id="add-priority-form-container" class="hidden mb-4">
                    <div class="card bg-base-200">
                        <div class="card-body p-4">
                            <h5 class="font-medium text-sm mb-3 flex items-center gap-2" id="priority-form-title">
                                <span class="icon-[tabler--plus] size-4" id="priority-form-icon"></span>
                                <span id="priority-form-title-text">Add New Priority</span>
                            </h5>
                            <form id="priority-form" onsubmit="submitPriorityForm(event)">
                                <input type="hidden" name="action" id="priority-action" value="add">
                                <input type="hidden" name="edit_id" id="priority-edit-id" value="">
                                <div class="flex gap-3">
                                    <div class="form-control flex-1">
                                        <input type="text" name="name" id="priority-name" class="input input-bordered input-sm" placeholder="Priority name" required>
                                    </div>
                                    <div class="form-control">
                                        <input type="color" name="color" id="priority-color" class="w-10 h-8 rounded cursor-pointer border border-base-300" value="#6b7280">
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-sm" id="priority-submit-btn">
                                        <span class="icon-[tabler--check] size-4"></span>
                                    </button>
                                    <button type="button" class="btn btn-ghost btn-sm" onclick="hideAddPriorityForm()">
                                        <span class="icon-[tabler--x] size-4"></span>
                                    </button>
                                </div>
                                <div id="priority-form-error" class="text-error text-xs mt-2 hidden"></div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Drag hint -->
                <p class="text-xs text-base-content/50 mb-2 flex items-center gap-1">
                    <span class="icon-[tabler--grip-vertical] size-4"></span>
                    Drag to reorder priorities
                </p>

                <!-- Priorities Table (Sortable) -->
                <div class="space-y-2" id="priorities-list">
                    @foreach($priorities as $priority)
                        <div class="flex items-center justify-between p-3 bg-base-200 rounded-lg group priority-item cursor-move"
                             data-id="{{ $priority->id }}"
                             data-name="{{ $priority->name }}"
                             data-color="{{ $priority->color }}"
                             draggable="true"
                             ondragstart="handlePriorityDragStart(event)"
                             ondragend="handlePriorityDragEnd(event)"
                             ondragover="handlePriorityDragOver(event)"
                             ondrop="handlePriorityDrop(event)">
                            <div class="flex items-center gap-3">
                                <span class="icon-[tabler--grip-vertical] size-4 text-base-content/30 cursor-move"></span>
                                <div class="w-4 h-4 rounded-full priority-color-dot" style="background-color: {{ $priority->color }}"></div>
                                <span class="font-medium text-sm priority-name-text">{{ $priority->name }}</span>
                            </div>
                            <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button type="button" class="btn btn-ghost btn-xs btn-square" onclick="editPriority(this.closest('.priority-item'))" title="Edit">
                                    <span class="icon-[tabler--edit] size-4"></span>
                                </button>
                                <button type="button" class="btn btn-ghost btn-xs btn-square text-error priority-delete-btn" onclick="confirmDeletePriority(this.closest('.priority-item'))" title="Delete">
                                    <span class="icon-[tabler--trash] size-4"></span>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div id="no-priorities-msg" class="text-center py-8 text-base-content/50 {{ $priorities->count() > 0 ? 'hidden' : '' }}">
                    <span class="icon-[tabler--flag-off] size-12 mb-2 opacity-50"></span>
                    <p class="text-sm">No priorities configured</p>
                    <p class="text-xs">Add your first priority above</p>
                </div>
            </div>

            <!-- Info Alert -->
            <div class="alert alert-info">
                <span class="icon-[tabler--info-circle] size-5"></span>
                <span class="text-sm">Priorities help categorize tickets by urgency. They can be used in SLA rules to determine response times.</span>
            </div>
        </div>

        <!-- Drawer Footer -->
        <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-base-200 bg-base-100">
            <div class="flex gap-3">
                <button type="button" class="btn btn-primary flex-1" onclick="closePrioritiesDrawer()">
                    <span class="icon-[tabler--check] size-5"></span>
                    Done
                </button>
                <button type="button" class="btn btn-ghost flex-1" onclick="closePrioritiesDrawer()">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Priority Confirmation Modal -->
<div id="delete-priority-modal" class="fixed inset-0 z-[60] hidden">
    <div class="absolute inset-0 bg-black/50" onclick="closeDeletePriorityModal()"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="bg-base-100 rounded-xl shadow-xl max-w-md w-full p-6 relative z-10">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-12 h-12 rounded-full bg-error/20 flex items-center justify-center">
                    <span class="icon-[tabler--trash] size-6 text-error"></span>
                </div>
                <div>
                    <h3 class="text-lg font-bold">Delete Priority</h3>
                    <p class="text-sm text-base-content/60">This action cannot be undone</p>
                </div>
            </div>
            <p class="text-base-content/70 mb-4">
                Are you sure you want to delete the priority "<span id="delete-priority-name" class="font-semibold"></span>"?
                Tickets with this priority will need to be reassigned.
            </p>
            <div class="flex justify-end gap-2">
                <button type="button" class="btn btn-ghost" onclick="closeDeletePriorityModal()">Cancel</button>
                <button type="button" class="btn btn-error gap-2" onclick="deletePriority()" id="confirm-delete-priority-btn">
                    <span class="icon-[tabler--trash] size-5"></span>
                    Delete Priority
                </button>
            </div>
        </div>
    </div>
</div>

<script>
const prioritiesEndpoint = '{{ route('workspace.save-priorities', $workspace) }}';
const csrfToken = '{{ csrf_token() }}';
let draggedPriorityItem = null;
let deletePriorityId = null;
let editPriorityId = null;

// Predefined color palette for priorities
const priorityColorPalette = [
    '#22c55e', // green
    '#eab308', // yellow
    '#f97316', // orange
    '#ef4444', // red
    '#3b82f6', // blue
    '#8b5cf6', // purple
    '#ec4899', // pink
    '#14b8a6', // teal
    '#6366f1', // indigo
    '#84cc16', // lime
    '#f59e0b', // amber
    '#06b6d4', // cyan
    '#a855f7', // violet
    '#10b981', // emerald
    '#f43f5e', // rose
    '#0ea5e9', // sky
    '#d946ef', // fuchsia
    '#64748b', // slate
];

async function openPrioritiesDrawer() {
    const drawer = document.getElementById('priorities-drawer');
    const panel = document.getElementById('priorities-drawer-panel');

    drawer.classList.remove('hidden');
    document.body.style.overflow = 'hidden';

    setTimeout(() => {
        panel.classList.remove('translate-x-full');
    }, 10);

    // Check if priorities list is empty and initialize defaults
    const list = document.getElementById('priorities-list');
    const noMsg = document.getElementById('no-priorities-msg');
    const hasItems = list && list.querySelectorAll('.priority-item').length > 0;

    if (!hasItems) {
        try {
            const formData = new FormData();
            formData.append('_token', csrfToken);
            formData.append('action', 'init_defaults');

            const response = await fetch(prioritiesEndpoint, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                }
            });

            const data = await response.json();

            if (data.success && data.priorities && data.priorities.length > 0) {
                // Add priorities to list
                data.priorities.forEach(priority => {
                    addPriorityToList(priority);
                });

                // Hide no priorities message
                if (noMsg) {
                    noMsg.classList.add('hidden');
                }
            }
        } catch (error) {
            console.error('Failed to initialize default priorities:', error);
        }
    }

    updateDeleteButtonsVisibility();
}

function closePrioritiesDrawer() {
    const drawer = document.getElementById('priorities-drawer');
    const panel = document.getElementById('priorities-drawer-panel');

    panel.classList.add('translate-x-full');

    setTimeout(() => {
        drawer.classList.add('hidden');
        document.body.style.overflow = '';
    }, 300);

    hideAddPriorityForm();
}

function getNextAvailableColor() {
    // Get all colors currently in use
    const usedColors = [];
    document.querySelectorAll('#priorities-list .priority-item').forEach(item => {
        usedColors.push(item.dataset.color.toLowerCase());
    });

    // Find first color from palette not in use
    for (const color of priorityColorPalette) {
        if (!usedColors.includes(color.toLowerCase())) {
            return color;
        }
    }

    // If all colors used, return a random one from palette
    return priorityColorPalette[Math.floor(Math.random() * priorityColorPalette.length)];
}

function showAddPriorityForm() {
    const container = document.getElementById('add-priority-form-container');
    container.classList.remove('hidden');

    // Reset to add mode
    document.getElementById('priority-action').value = 'add';
    document.getElementById('priority-edit-id').value = '';
    editPriorityId = null;
    document.getElementById('priority-name').value = '';
    document.getElementById('priority-color').value = getNextAvailableColor();
    document.getElementById('priority-form-icon').className = 'icon-[tabler--plus] size-4';
    document.getElementById('priority-form-title-text').textContent = 'Add New Priority';
    document.getElementById('priority-form-error').classList.add('hidden');

    document.getElementById('priority-name').focus();
}

function hideAddPriorityForm() {
    const container = document.getElementById('add-priority-form-container');
    container.classList.add('hidden');

    // Reset form
    document.getElementById('priority-form').reset();
    document.getElementById('priority-action').value = 'add';
    document.getElementById('priority-edit-id').value = '';
    editPriorityId = null;
    document.getElementById('priority-form-error').classList.add('hidden');
}

function editPriority(item) {
    if (!item) return;

    const name = item.dataset.name;
    const color = item.dataset.color;
    const id = item.dataset.id;

    // Show form in edit mode
    const container = document.getElementById('add-priority-form-container');
    container.classList.remove('hidden');

    document.getElementById('priority-action').value = 'edit';
    document.getElementById('priority-edit-id').value = id;
    editPriorityId = id;
    document.getElementById('priority-name').value = name;
    document.getElementById('priority-color').value = color;
    document.getElementById('priority-form-icon').className = 'icon-[tabler--edit] size-4';
    document.getElementById('priority-form-title-text').textContent = 'Edit Priority';
    document.getElementById('priority-form-error').classList.add('hidden');

    document.getElementById('priority-name').focus();
}

async function submitPriorityForm(event) {
    event.preventDefault();

    const action = document.getElementById('priority-action').value;
    const name = document.getElementById('priority-name').value.trim();
    const color = document.getElementById('priority-color').value;
    const editId = document.getElementById('priority-edit-id').value;
    const errorDiv = document.getElementById('priority-form-error');

    if (!name) {
        errorDiv.textContent = 'Priority name is required.';
        errorDiv.classList.remove('hidden');
        return;
    }

    const formData = new FormData();
    formData.append('_token', csrfToken);
    formData.append('action', action);
    formData.append('name', name);
    formData.append('color', color);
    if (action === 'edit') {
        formData.append('edit_id', editId);
    }

    try {
        const response = await fetch(prioritiesEndpoint, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            }
        });

        const data = await response.json();

        if (data.success) {
            if (action === 'add') {
                addPriorityToList(data.priority || { id: Date.now(), name, color });
                document.getElementById('priority-name').value = '';
                // Auto-assign next unique color
                document.getElementById('priority-color').value = getNextAvailableColor();
                document.getElementById('priority-name').focus();
            } else if (action === 'edit') {
                updatePriorityInList(editId, name, color);
                hideAddPriorityForm();
            }
            errorDiv.classList.add('hidden');
            showToast(data.message || 'Priority saved successfully.', 'success');
        } else {
            errorDiv.textContent = data.message || 'An error occurred.';
            errorDiv.classList.remove('hidden');
        }
    } catch (error) {
        errorDiv.textContent = 'An error occurred. Please try again.';
        errorDiv.classList.remove('hidden');
    }
}

function addPriorityToList(priority) {
    const list = document.getElementById('priorities-list');
    const count = list.querySelectorAll('.priority-item').length;

    const item = document.createElement('div');
    item.className = 'flex items-center justify-between p-3 bg-base-200 rounded-lg group priority-item cursor-move';
    item.dataset.id = priority.id;
    item.dataset.name = priority.name;
    item.dataset.color = priority.color;
    item.draggable = true;
    item.ondragstart = handlePriorityDragStart;
    item.ondragend = handlePriorityDragEnd;
    item.ondragover = handlePriorityDragOver;
    item.ondrop = handlePriorityDrop;

    item.innerHTML = `
        <div class="flex items-center gap-3">
            <span class="icon-[tabler--grip-vertical] size-4 text-base-content/30 cursor-move"></span>
            <div class="w-4 h-4 rounded-full priority-color-dot" style="background-color: ${priority.color}"></div>
            <span class="font-medium text-sm priority-name-text">${escapeHtml(priority.name)}</span>
        </div>
        <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
            <button type="button" class="btn btn-ghost btn-xs btn-square" onclick="editPriority(this.closest('.priority-item'))" title="Edit">
                <span class="icon-[tabler--edit] size-4"></span>
            </button>
            <button type="button" class="btn btn-ghost btn-xs btn-square text-error priority-delete-btn" onclick="confirmDeletePriority(this.closest('.priority-item'))" title="Delete">
                <span class="icon-[tabler--trash] size-4"></span>
            </button>
        </div>
    `;

    list.appendChild(item);

    // Update count
    document.getElementById('priorities-count').textContent = count + 1;
    document.getElementById('no-priorities-msg').classList.add('hidden');

    updateDeleteButtonsVisibility();
}

function updatePriorityInList(id, name, color) {
    const item = document.querySelector(`.priority-item[data-id="${id}"]`);
    if (!item) return;

    item.dataset.name = name;
    item.dataset.color = color;
    item.querySelector('.priority-name-text').textContent = name;
    item.querySelector('.priority-color-dot').style.backgroundColor = color;
}

function confirmDeletePriority(item) {
    if (!item) return;

    const list = document.getElementById('priorities-list');
    const count = list.querySelectorAll('.priority-item').length;

    if (count <= 1) {
        showToast('Cannot delete the last priority. At least one priority is required.', 'error');
        return;
    }

    deletePriorityId = item.dataset.id;
    document.getElementById('delete-priority-name').textContent = item.dataset.name;
    document.getElementById('delete-priority-modal').classList.remove('hidden');
}

function closeDeletePriorityModal() {
    document.getElementById('delete-priority-modal').classList.add('hidden');
    deletePriorityId = null;
}

async function deletePriority() {
    if (deletePriorityId === null) return;

    const formData = new FormData();
    formData.append('_token', csrfToken);
    formData.append('action', 'delete');
    formData.append('delete_id', deletePriorityId);

    try {
        const response = await fetch(prioritiesEndpoint, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            }
        });

        const data = await response.json();

        if (data.success) {
            removePriorityFromList(deletePriorityId);
            showToast(data.message || 'Priority deleted successfully.', 'success');
        } else {
            showToast(data.message || 'An error occurred.', 'error');
        }
    } catch (error) {
        showToast('An error occurred. Please try again.', 'error');
    }

    closeDeletePriorityModal();
}

function removePriorityFromList(id) {
    const item = document.querySelector(`.priority-item[data-id="${id}"]`);
    if (item) {
        item.remove();
    }

    const list = document.getElementById('priorities-list');
    const count = list.querySelectorAll('.priority-item').length;
    document.getElementById('priorities-count').textContent = count;

    if (count === 0) {
        document.getElementById('no-priorities-msg').classList.remove('hidden');
    }

    updateDeleteButtonsVisibility();
}

function updateDeleteButtonsVisibility() {
    const list = document.getElementById('priorities-list');
    const items = list.querySelectorAll('.priority-item');
    const deleteButtons = list.querySelectorAll('.priority-delete-btn');

    deleteButtons.forEach(btn => {
        if (items.length <= 1) {
            btn.classList.add('hidden');
        } else {
            btn.classList.remove('hidden');
        }
    });
}

// Drag and Drop functionality
function handlePriorityDragStart(event) {
    draggedPriorityItem = event.target.closest('.priority-item');
    if (draggedPriorityItem) {
        draggedPriorityItem.classList.add('opacity-50');
        event.dataTransfer.effectAllowed = 'move';
        event.dataTransfer.setData('text/plain', draggedPriorityItem.dataset.index);
    }
}

function handlePriorityDragEnd(event) {
    if (draggedPriorityItem) {
        draggedPriorityItem.classList.remove('opacity-50');
    }
    draggedPriorityItem = null;

    // Remove all drag-over styles
    document.querySelectorAll('.priority-item').forEach(item => {
        item.classList.remove('border-t-2', 'border-primary');
    });
}

function handlePriorityDragOver(event) {
    event.preventDefault();
    event.dataTransfer.dropEffect = 'move';

    const target = event.target.closest('.priority-item');
    if (target && target !== draggedPriorityItem) {
        // Remove previous indicators
        document.querySelectorAll('.priority-item').forEach(item => {
            item.classList.remove('border-t-2', 'border-primary');
        });

        // Add indicator to current target
        target.classList.add('border-t-2', 'border-primary');
    }
}

async function handlePriorityDrop(event) {
    event.preventDefault();

    const target = event.target.closest('.priority-item');
    if (!target || !draggedPriorityItem || target === draggedPriorityItem) return;

    const list = document.getElementById('priorities-list');
    const items = Array.from(list.querySelectorAll('.priority-item'));
    const draggedIndex = items.indexOf(draggedPriorityItem);
    const targetIndex = items.indexOf(target);

    // Reorder in DOM
    if (draggedIndex < targetIndex) {
        target.parentNode.insertBefore(draggedPriorityItem, target.nextSibling);
    } else {
        target.parentNode.insertBefore(draggedPriorityItem, target);
    }

    // Remove drag indicators
    document.querySelectorAll('.priority-item').forEach(item => {
        item.classList.remove('border-t-2', 'border-primary');
    });

    // Save new order to server
    await savePriorityOrder();
}

async function savePriorityOrder() {
    const items = document.querySelectorAll('#priorities-list .priority-item');
    const priorities = Array.from(items).map((item, index) => ({
        id: item.dataset.id,
        name: item.dataset.name,
        color: item.dataset.color,
        order: index + 1
    }));

    const formData = new FormData();
    formData.append('_token', csrfToken);
    formData.append('action', 'reorder');
    formData.append('priorities', JSON.stringify(priorities));

    try {
        await fetch(prioritiesEndpoint, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            }
        });
    } catch (error) {
        console.error('Failed to save priority order:', error);
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = 'toast toast-top toast-end z-[70]';
    toast.innerHTML = `
        <div class="alert alert-${type}">
            <span class="icon-[tabler--${type === 'success' ? 'check' : 'x'}] size-5"></span>
            <span>${escapeHtml(message)}</span>
        </div>
    `;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

// Close on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        // Check if delete modal is open first
        const deleteModal = document.getElementById('delete-priority-modal');
        if (deleteModal && !deleteModal.classList.contains('hidden')) {
            closeDeletePriorityModal();
            return;
        }

        const drawer = document.getElementById('priorities-drawer');
        if (drawer && !drawer.classList.contains('hidden')) {
            closePrioritiesDrawer();
        }
    }
});
</script>
