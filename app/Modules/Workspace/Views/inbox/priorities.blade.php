@extends('layouts.app')

@section('content')
<div class="p-4 md:p-6">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center gap-2 text-sm text-base-content/60 mb-2">
                <a href="{{ route('dashboard') }}" class="hover:text-primary">Dashboard</a>
                <span class="icon-[tabler--chevron-right] size-4"></span>
                <a href="{{ route('workspace.show', $workspace) }}" class="hover:text-primary">{{ $workspace->name }}</a>
                <span class="icon-[tabler--chevron-right] size-4"></span>
                <span>Priorities</span>
            </div>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <a href="{{ route('workspace.show', $workspace) }}" class="btn btn-ghost btn-sm btn-square">
                        <span class="icon-[tabler--arrow-left] size-5"></span>
                    </a>
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-lg bg-error/10 flex items-center justify-center">
                            <span class="icon-[tabler--flag] size-6 text-error"></span>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-base-content">Priorities</h1>
                            <p class="text-sm text-base-content/60">Configure priority levels for ticket classification</p>
                        </div>
                    </div>
                </div>
            </div>
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

        @php
            $colors = [
                'red' => ['bg' => '#fef2f2', 'text' => '#dc2626', 'label' => 'Red'],
                'orange' => ['bg' => '#fff7ed', 'text' => '#ea580c', 'label' => 'Orange'],
                'amber' => ['bg' => '#fffbeb', 'text' => '#d97706', 'label' => 'Amber'],
                'yellow' => ['bg' => '#fefce8', 'text' => '#ca8a04', 'label' => 'Yellow'],
                'lime' => ['bg' => '#f7fee7', 'text' => '#65a30d', 'label' => 'Lime'],
                'green' => ['bg' => '#f0fdf4', 'text' => '#16a34a', 'label' => 'Green'],
                'emerald' => ['bg' => '#ecfdf5', 'text' => '#059669', 'label' => 'Emerald'],
                'teal' => ['bg' => '#f0fdfa', 'text' => '#0d9488', 'label' => 'Teal'],
                'cyan' => ['bg' => '#ecfeff', 'text' => '#0891b2', 'label' => 'Cyan'],
                'sky' => ['bg' => '#f0f9ff', 'text' => '#0284c7', 'label' => 'Sky'],
                'blue' => ['bg' => '#eff6ff', 'text' => '#2563eb', 'label' => 'Blue'],
                'indigo' => ['bg' => '#eef2ff', 'text' => '#4f46e5', 'label' => 'Indigo'],
                'violet' => ['bg' => '#f5f3ff', 'text' => '#7c3aed', 'label' => 'Violet'],
                'purple' => ['bg' => '#faf5ff', 'text' => '#9333ea', 'label' => 'Purple'],
                'fuchsia' => ['bg' => '#fdf4ff', 'text' => '#c026d3', 'label' => 'Fuchsia'],
                'pink' => ['bg' => '#fdf2f8', 'text' => '#db2777', 'label' => 'Pink'],
                'rose' => ['bg' => '#fff1f2', 'text' => '#e11d48', 'label' => 'Rose'],
                'slate' => ['bg' => '#f8fafc', 'text' => '#475569', 'label' => 'Slate'],
            ];
        @endphp

        <!-- Customize Priorities Card -->
        <div class="card bg-base-100 shadow mb-6">
            <div class="card-body">
                <h2 class="card-title text-lg mb-4">
                    <span class="icon-[tabler--flag] size-5"></span>
                    Customize Priorities
                </h2>
                <p class="text-sm text-base-content/60 mb-4">Define priority levels with unique colors. Priorities help classify tickets by urgency for SLA calculations.</p>

                <!-- Column Headers -->
                <div class="grid grid-cols-12 gap-3 px-3 py-2 text-sm font-medium text-base-content/60">
                    <div class="col-span-1"></div>
                    <div class="col-span-4">Priority Name</div>
                    <div class="col-span-2 text-center">Color</div>
                    <div class="col-span-3">Preview</div>
                    <div class="col-span-2 text-center">Action</div>
                </div>

                <!-- Priority List -->
                <div id="priority-list" class="space-y-2">
                    @foreach($priorities as $index => $priority)
                    <div class="priority-row" data-id="{{ $priority->id }}" data-index="{{ $index }}">
                        <div class="grid grid-cols-12 gap-3 items-center p-3 bg-base-200/50 rounded-lg border border-base-300">
                            <!-- Drag Handle -->
                            <div class="col-span-1 flex justify-center text-base-content/40 cursor-grab">
                                <span class="icon-[tabler--grip-vertical] size-5"></span>
                            </div>

                            <!-- Priority Name -->
                            <div class="col-span-4">
                                <input type="text" class="input input-bordered input-sm w-full priority-name" placeholder="Priority name" value="{{ $priority->name }}" maxlength="40">
                            </div>

                            <!-- Color Picker -->
                            <div class="col-span-2 flex justify-center">
                                <div class="relative">
                                    <button type="button" class="btn btn-sm color-picker-btn" style="background-color: {{ $colors[$priority->color]['bg'] ?? '#f8fafc' }}; color: {{ $colors[$priority->color]['text'] ?? '#475569' }};">
                                        <span class="icon-[tabler--palette] size-4"></span>
                                    </button>
                                    <input type="hidden" class="priority-color-hidden" value="{{ $priority->color }}">
                                    <div class="color-picker-dropdown hidden absolute top-full mt-1 right-0 bg-base-100 rounded-lg shadow-lg p-3 w-64 z-50 border border-base-300">
                                        <div class="grid grid-cols-6 gap-2">
                                            @foreach($colors as $key => $color)
                                            <label class="cursor-pointer">
                                                <input type="radio" name="priority_color_{{ $priority->id }}" value="{{ $key }}" class="hidden peer priority-color" {{ $priority->color === $key ? 'checked' : '' }}>
                                                <span class="block w-8 h-8 rounded-full peer-checked:ring-2 peer-checked:ring-offset-2 peer-checked:ring-primary transition-all hover:scale-110" style="background-color: {{ $color['text'] }}" title="{{ $color['label'] }}"></span>
                                            </label>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Preview -->
                            <div class="col-span-3">
                                <span class="priority-preview px-3 py-1.5 rounded-lg text-sm font-medium inline-flex items-center gap-1.5" style="background-color: {{ $colors[$priority->color]['bg'] ?? '#f8fafc' }}; color: {{ $colors[$priority->color]['text'] ?? '#475569' }};">
                                    <span class="icon-[tabler--flag-filled] size-3.5"></span>
                                    {{ $priority->name }}
                                </span>
                            </div>

                            <!-- Actions -->
                            <div class="col-span-2 flex justify-center gap-1">
                                <button type="button" class="btn btn-ghost btn-sm btn-square save-priority-btn hidden" title="Save changes">
                                    <span class="icon-[tabler--check] size-4 text-success"></span>
                                </button>
                                <button type="button" class="btn btn-ghost btn-sm btn-square text-error delete-priority-btn" title="Delete">
                                    <span class="icon-[tabler--trash] size-4"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Add Priority Button -->
                <div id="add-priority-row" class="flex items-center justify-center py-3 border-2 border-dashed border-base-300 rounded-lg hover:border-primary/50 transition-colors mt-2">
                    <button type="button" id="add-priority-btn" class="btn btn-circle btn-primary btn-sm">
                        <span class="icon-[tabler--plus] size-5"></span>
                    </button>
                    <span class="text-sm text-base-content/60 ml-2">Add new priority</span>
                </div>

                <!-- Info Note -->
                <div class="mt-4 p-3 bg-info/10 border border-info/20 rounded-lg">
                    <p class="text-sm flex items-center gap-2">
                        <span class="icon-[tabler--info-circle] size-4 text-info"></span>
                        <span>Priorities are used in SLA calculations to determine response and resolution times for tickets.</span>
                    </p>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex justify-start gap-3">
            <button type="button" class="btn btn-primary" id="save-all-btn">
                <span class="icon-[tabler--device-floppy] size-5"></span>
                Save Priorities
            </button>
            <a href="{{ route('workspace.show', $workspace) }}" class="btn btn-ghost">Cancel</a>
        </div>
    </div>
</div>

<!-- Delete Priority Confirmation Modal -->
<div id="delete-priority-modal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50" onclick="closeDeleteModal()"></div>
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
            </p>
            <div class="flex justify-end gap-2">
                <button type="button" class="btn btn-ghost" onclick="closeDeleteModal()">Cancel</button>
                <button type="button" class="btn btn-error gap-2" onclick="confirmDelete()" id="confirm-delete-btn">
                    <span class="icon-[tabler--trash] size-5"></span>
                    Delete Priority
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js" integrity="sha256-ipiJrswvAR4VAx/th+6zWsdeYmVae0iJuiR+6OqHJHQ=" crossorigin="anonymous"></script>
<script>
const prioritiesEndpoint = '{{ route('workspace.save-priorities', $workspace) }}';
const csrfToken = '{{ csrf_token() }}';
const colors = @json($colors);
let priorityIndex = {{ $priorities->count() }};
let deleteTargetId = null;

// Initialize Sortable for drag-and-drop reordering
const priorityList = document.getElementById('priority-list');
new Sortable(priorityList, {
    animation: 150,
    handle: '.cursor-grab',
    ghostClass: 'opacity-50',
    chosenClass: 'bg-primary/10',
    dragClass: 'shadow-lg',
    onEnd: function(evt) {
        // Update sort order indices after drag
        updateSortOrder();
    }
});

function updateSortOrder() {
    const rows = document.querySelectorAll('.priority-row');
    rows.forEach((row, index) => {
        row.dataset.index = index;
    });
}

// Initialize color picker events for existing rows
document.querySelectorAll('.priority-row').forEach(row => attachRowEvents(row));

// Add new priority button
document.getElementById('add-priority-btn').addEventListener('click', addNewPriority);

// Save all button
document.getElementById('save-all-btn').addEventListener('click', saveAllPriorities);

function addNewPriority() {
    const list = document.getElementById('priority-list');
    const defaultColor = 'blue';
    const row = document.createElement('div');
    row.className = 'priority-row';
    row.dataset.id = 'new_' + priorityIndex;
    row.dataset.index = priorityIndex;

    row.innerHTML = `
        <div class="grid grid-cols-12 gap-3 items-center p-3 bg-base-200/50 rounded-lg border border-base-300 border-primary/50">
            <!-- Drag Handle -->
            <div class="col-span-1 flex justify-center text-base-content/40 cursor-grab">
                <span class="icon-[tabler--grip-vertical] size-5"></span>
            </div>

            <!-- Priority Name -->
            <div class="col-span-4">
                <input type="text" class="input input-bordered input-sm w-full priority-name" placeholder="Enter priority name" value="" maxlength="40" autofocus>
            </div>

            <!-- Color Picker -->
            <div class="col-span-2 flex justify-center">
                <div class="relative">
                    <button type="button" class="btn btn-sm color-picker-btn" style="background-color: ${colors[defaultColor].bg}; color: ${colors[defaultColor].text};">
                        <span class="icon-[tabler--palette] size-4"></span>
                    </button>
                    <input type="hidden" class="priority-color-hidden" value="${defaultColor}">
                    <div class="color-picker-dropdown hidden absolute top-full mt-1 right-0 bg-base-100 rounded-lg shadow-lg p-3 w-64 z-50 border border-base-300">
                        <div class="grid grid-cols-6 gap-2">
                            ${Object.entries(colors).map(([key, color]) => `
                                <label class="cursor-pointer">
                                    <input type="radio" name="priority_color_new_${priorityIndex}" value="${key}" class="hidden peer priority-color" ${key === defaultColor ? 'checked' : ''}>
                                    <span class="block w-8 h-8 rounded-full peer-checked:ring-2 peer-checked:ring-offset-2 peer-checked:ring-primary transition-all hover:scale-110" style="background-color: ${color.text}" title="${color.label}"></span>
                                </label>
                            `).join('')}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Preview -->
            <div class="col-span-3">
                <span class="priority-preview px-3 py-1.5 rounded-lg text-sm font-medium inline-flex items-center gap-1.5" style="background-color: ${colors[defaultColor].bg}; color: ${colors[defaultColor].text};">
                    <span class="icon-[tabler--flag-filled] size-3.5"></span>
                    New Priority
                </span>
            </div>

            <!-- Actions -->
            <div class="col-span-2 flex justify-center gap-1">
                <button type="button" class="btn btn-ghost btn-sm btn-square save-priority-btn hidden" title="Save changes">
                    <span class="icon-[tabler--check] size-4 text-success"></span>
                </button>
                <button type="button" class="btn btn-ghost btn-sm btn-square text-error delete-priority-btn" title="Delete">
                    <span class="icon-[tabler--trash] size-4"></span>
                </button>
            </div>
        </div>
    `;

    list.appendChild(row);
    attachRowEvents(row);
    row.querySelector('.priority-name').focus();
    priorityIndex++;
}

function attachRowEvents(row) {
    const nameInput = row.querySelector('.priority-name');
    const colorInputs = row.querySelectorAll('.priority-color');
    const preview = row.querySelector('.priority-preview');
    const colorBtn = row.querySelector('.color-picker-btn');
    const colorDropdown = row.querySelector('.color-picker-dropdown');
    const colorHidden = row.querySelector('.priority-color-hidden');
    const deleteBtn = row.querySelector('.delete-priority-btn');

    // Name change - update preview
    nameInput.addEventListener('input', function() {
        const name = this.value || 'New Priority';
        preview.innerHTML = `<span class="icon-[tabler--flag-filled] size-3.5"></span> ${escapeHtml(name)}`;
    });

    // Color picker toggle
    colorBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        // Close other dropdowns
        document.querySelectorAll('.color-picker-dropdown').forEach(d => {
            if (d !== colorDropdown) d.classList.add('hidden');
        });
        colorDropdown.classList.toggle('hidden');
    });

    // Color selection
    colorInputs.forEach(input => {
        input.addEventListener('change', function() {
            const color = this.value;
            colorHidden.value = color;
            colorBtn.style.backgroundColor = colors[color].bg;
            colorBtn.style.color = colors[color].text;
            preview.style.backgroundColor = colors[color].bg;
            preview.style.color = colors[color].text;
            colorDropdown.classList.add('hidden');
        });
    });

    // Delete button
    deleteBtn.addEventListener('click', function() {
        const id = row.dataset.id;
        const name = nameInput.value || 'New Priority';

        if (id.startsWith('new_')) {
            // Just remove from DOM if not saved yet
            row.remove();
        } else {
            // Show confirmation modal
            deleteTargetId = id;
            document.getElementById('delete-priority-name').textContent = name;
            document.getElementById('delete-priority-modal').classList.remove('hidden');
        }
    });
}

// Close dropdown when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('.color-picker-btn') && !e.target.closest('.color-picker-dropdown')) {
        document.querySelectorAll('.color-picker-dropdown').forEach(d => d.classList.add('hidden'));
    }
});

function closeDeleteModal() {
    document.getElementById('delete-priority-modal').classList.add('hidden');
    deleteTargetId = null;
}

async function confirmDelete() {
    if (!deleteTargetId) return;

    const formData = new FormData();
    formData.append('_token', csrfToken);
    formData.append('action', 'delete');
    formData.append('delete_id', deleteTargetId);

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
            const row = document.querySelector(`.priority-row[data-id="${deleteTargetId}"]`);
            if (row) row.remove();
            showToast(data.message || 'Priority deleted successfully.', 'success');
        } else {
            showToast(data.message || 'An error occurred.', 'error');
        }
    } catch (error) {
        showToast('An error occurred. Please try again.', 'error');
    }

    closeDeleteModal();
}

async function saveAllPriorities() {
    const rows = document.querySelectorAll('.priority-row');
    const priorities = [];

    rows.forEach((row, index) => {
        const id = row.dataset.id;
        const name = row.querySelector('.priority-name').value.trim();
        const color = row.querySelector('.priority-color-hidden').value;

        if (name) {
            priorities.push({
                id: id.startsWith('new_') ? null : id,
                name: name,
                color: color,
                sort_order: index
            });
        }
    });

    if (priorities.length === 0) {
        showToast('Please add at least one priority.', 'error');
        return;
    }

    const formData = new FormData();
    formData.append('_token', csrfToken);
    formData.append('action', 'bulk_save');
    formData.append('priorities', JSON.stringify(priorities));

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
            showToast(data.message || 'Priorities saved successfully.', 'success');
            // Redirect to workspace after short delay
            setTimeout(() => {
                window.location.href = '{{ route('workspace.show', $workspace) }}';
            }, 1000);
        } else {
            showToast(data.message || 'An error occurred.', 'error');
        }
    } catch (error) {
        showToast('An error occurred. Please try again.', 'error');
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
            <span>${message}</span>
        </div>
    `;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeDeleteModal();
    }
});
</script>
@endsection
