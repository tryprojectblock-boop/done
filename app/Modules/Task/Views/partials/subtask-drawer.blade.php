{{-- Subtask Creation Drawer --}}
{{-- Required variables: $task, $users --}}

<!-- Drawer Overlay -->
<div id="subtask-drawer-overlay" class="fixed inset-0 bg-black/50 z-[100] hidden" onclick="closeSubtaskDrawer()"></div>

<!-- Drawer Panel -->
<div id="subtask-drawer" class="fixed top-0 right-0 h-full w-full max-w-xl bg-base-100 shadow-2xl z-[110] transform translate-x-full transition-transform duration-300 ease-in-out flex flex-col">
    <!-- Drawer Header (Fixed) -->
    <div class="flex items-center justify-between p-6 border-b border-base-300 bg-base-100">
        <div>
            <h2 class="text-xl font-bold">Create Subtask</h2>
            <p class="text-sm text-base-content/60">Add a subtask to this task</p>
        </div>
        <button type="button" onclick="closeSubtaskDrawer()" class="btn btn-ghost btn-sm btn-circle">
            <span class="icon-[tabler--x] size-5"></span>
        </button>
    </div>

    <!-- Scrollable Content -->
    <div class="flex-1 overflow-y-auto p-6">
        <!-- Subtask Form -->
        <form id="subtask-drawer-form" onsubmit="submitSubtask(event)">
            @csrf
            <input type="hidden" name="parent_task_uuid" id="subtask-parent-uuid" value="{{ $task->uuid }}">

            <div class="space-y-4">
                <!-- Task Title -->
                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-medium">Title <span class="text-error">*</span></span>
                    </label>
                    <input type="text" name="title" id="subtask-drawer-title" class="input input-bordered w-full" placeholder="Enter subtask title" required>
                </div>

                <!-- Assignee & Priority Row -->
                <div class="grid grid-cols-2 gap-4">
                    <!-- Assignee -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Assignee</span>
                        </label>
                        <div id="subtask-drawer-assignee-container">
                            <select name="assignee_id" id="subtask-drawer-assignee" data-select='{
                                "placeholder": "Select assignee...",
                                "hasSearch": true,
                                "toggleTag": "<button type=\"button\" aria-expanded=\"false\"></button>",
                                "toggleClasses": "advance-select-toggle",
                                "dropdownClasses": "advance-select-menu max-h-52 overflow-y-auto z-[120]",
                                "optionClasses": "advance-select-option selected:active",
                                "optionTemplate": "<div class=\"flex justify-between items-center w-full\"><span data-title></span><span class=\"icon-[tabler--check] shrink-0 size-4 text-primary hidden selected:block\"></span></div>",
                                "extraMarkup": "<span class=\"icon-[tabler--caret-up-down] shrink-0 size-4 text-base-content/90 absolute top-1/2 end-3 -translate-y-1/2\"></span>"
                            }' class="hidden">
                                <option value="">Unassigned</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ $task->assignee_id == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Priority -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Priority</span>
                        </label>
                        <div id="subtask-drawer-priority-container">
                            <select name="priority" id="subtask-drawer-priority" data-select='{
                                "placeholder": "Select priority...",
                                "hasSearch": true,
                                "toggleTag": "<button type=\"button\" aria-expanded=\"false\"></button>",
                                "toggleClasses": "advance-select-toggle",
                                "dropdownClasses": "advance-select-menu max-h-52 overflow-y-auto z-[120]",
                                "optionClasses": "advance-select-option selected:active",
                                "optionTemplate": "<div class=\"flex justify-between items-center w-full\"><span data-title></span><span class=\"icon-[tabler--check] shrink-0 size-4 text-primary hidden selected:block\"></span></div>",
                                "extraMarkup": "<span class=\"icon-[tabler--caret-up-down] shrink-0 size-4 text-base-content/90 absolute top-1/2 end-3 -translate-y-1/2\"></span>"
                            }' class="hidden">
                                @foreach(\App\Modules\Task\Enums\TaskPriority::cases() as $priority)
                                    <option value="{{ $priority->value }}" {{ $task->priority == $priority ? 'selected' : '' }}>
                                        {{ $priority->label() }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Description -->
                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-medium">Description</span>
                    </label>
                    <div id="subtask-drawer-editor-container">
                        <x-quill-editor
                            name="description"
                            id="subtask-drawer-description"
                            placeholder="Subtask description (optional)..."
                            height="150px"
                        />
                    </div>
                </div>

                <!-- Parent Task Info -->
                <div class="bg-base-200 rounded-lg p-3">
                    <p class="text-xs text-base-content/60">
                        <span class="icon-[tabler--subtask] size-3 inline"></span>
                        This will be a subtask of: <strong class="font-mono">{{ $task->task_number }}</strong> - {{ Str::limit($task->title, 40) }}
                    </p>
                </div>

                <!-- Error Message Container -->
                <div id="subtask-drawer-error" class="alert alert-error hidden">
                    <span class="icon-[tabler--alert-circle] size-5"></span>
                    <span id="subtask-drawer-error-text"></span>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-3 mt-6 pt-4 border-t border-base-300">
                <button type="button" onclick="closeSubtaskDrawer()" class="btn btn-ghost flex-1">Cancel</button>
                <button type="submit" class="btn btn-primary flex-1" id="subtask-drawer-submit">
                    <span class="icon-[tabler--plus] size-5"></span>
                    Create Subtask
                </button>
            </div>
        </form>
    </div>
</div>

@once
@push('scripts')
<script>
// Track if HSSelect has been initialized
let subtaskDrawerHSSelectInitialized = false;

/**
 * Open the subtask drawer
 */
function openSubtaskDrawer() {
    // Clear previous data
    document.getElementById('subtask-drawer-title').value = '';
    document.getElementById('subtask-drawer-error').classList.add('hidden');

    // Clear Quill editor
    const editorElement = document.getElementById('subtask-drawer-description');
    if (editorElement && editorElement.quillInstance) {
        editorElement.quillInstance.setText('');
    }

    // Show drawer
    document.getElementById('subtask-drawer-overlay').classList.remove('hidden');
    document.getElementById('subtask-drawer').classList.remove('translate-x-full');
    document.body.classList.add('overflow-hidden');

    // Initialize HSSelect dropdowns
    initSubtaskDrawerHSSelect();

    // Focus title input
    setTimeout(() => {
        document.getElementById('subtask-drawer-title').focus();
    }, 300);
}

/**
 * Initialize HSSelect for dropdowns in the drawer
 */
function initSubtaskDrawerHSSelect() {
    if (subtaskDrawerHSSelectInitialized) return;

    if (typeof HSSelect !== 'undefined') {
        // Initialize all HSSelect components in the drawer
        const drawerSelects = document.querySelectorAll('#subtask-drawer [data-select]');
        drawerSelects.forEach(select => {
            if (!HSSelect.getInstance(select)) {
                HSSelect.autoInit();
            }
        });
        subtaskDrawerHSSelectInitialized = true;
    }
}

/**
 * Close the subtask drawer and reset the form
 */
function closeSubtaskDrawer() {
    document.getElementById('subtask-drawer-overlay').classList.add('hidden');
    document.getElementById('subtask-drawer').classList.add('translate-x-full');
    document.body.classList.remove('overflow-hidden');

    // Reset form
    document.getElementById('subtask-drawer-form').reset();
    document.getElementById('subtask-drawer-error').classList.add('hidden');

    // Clear Quill editor
    const editorElement = document.getElementById('subtask-drawer-description');
    if (editorElement && editorElement.quillInstance) {
        editorElement.quillInstance.setText('');
    }
}

/**
 * Submit subtask via AJAX
 */
async function submitSubtask(event) {
    event.preventDefault();

    const btn = document.getElementById('subtask-drawer-submit');
    const originalText = btn.innerHTML;
    const errorContainer = document.getElementById('subtask-drawer-error');
    const errorText = document.getElementById('subtask-drawer-error-text');

    // Hide previous errors
    errorContainer.classList.add('hidden');

    // Get form data
    const title = document.getElementById('subtask-drawer-title').value.trim();
    const parentUuid = document.getElementById('subtask-parent-uuid').value;

    // Get values from HSSelect dropdowns
    let assigneeId = '';
    let priority = '';

    const assigneeSelect = document.getElementById('subtask-drawer-assignee');
    const prioritySelect = document.getElementById('subtask-drawer-priority');

    if (typeof HSSelect !== 'undefined') {
        const assigneeInstance = HSSelect.getInstance(assigneeSelect);
        const priorityInstance = HSSelect.getInstance(prioritySelect);
        assigneeId = assigneeInstance ? assigneeInstance.value : assigneeSelect.value;
        priority = priorityInstance ? priorityInstance.value : prioritySelect.value;
    } else {
        assigneeId = assigneeSelect.value;
        priority = prioritySelect.value;
    }

    // Get description from Quill editor
    let description = '';
    const editorElement = document.getElementById('subtask-drawer-description');
    if (editorElement && editorElement.quillInstance) {
        description = editorElement.quillInstance.root.innerHTML;
        // Check if it's empty
        if (description === '<p><br></p>') {
            description = '';
        }
    }

    // Validate
    if (!title) {
        errorText.textContent = 'Title is required';
        errorContainer.classList.remove('hidden');
        document.getElementById('subtask-drawer-title').focus();
        return;
    }

    // Show loading state
    btn.disabled = true;
    btn.innerHTML = '<span class="loading loading-spinner loading-sm"></span> Creating...';

    try {
        const response = await fetch(`/tasks/${parentUuid}/subtasks`, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                title: title,
                description: description,
                assignee_id: assigneeId || null,
                priority: priority
            })
        });

        const data = await response.json();

        if (response.ok && data.success) {
            // Add subtask to the list
            addSubtaskToList(data.subtask);

            // Close drawer
            closeSubtaskDrawer();
        } else {
            // Show error
            errorText.textContent = data.message || 'Failed to create subtask';
            errorContainer.classList.remove('hidden');
        }
    } catch (error) {
        console.error('Error creating subtask:', error);
        errorText.textContent = 'An error occurred while creating the subtask';
        errorContainer.classList.remove('hidden');
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}

/**
 * Add a new subtask to the subtasks list
 */
function addSubtaskToList(subtask) {
    const listContainer = document.getElementById('subtasks-list');
    const emptyMessage = document.getElementById('subtasks-empty-message');
    const countBadge = document.getElementById('subtasks-count');

    // Remove empty message if present
    if (emptyMessage) {
        emptyMessage.remove();
    }

    // Create subtask element
    const subtaskHtml = `
    <div class="flex items-center border-b border-[#EDECF0] justify-between px-6 py-4 hover:bg-gray-50 transition-colors">
       ${subtask.status ? `
        <span class="px-3 py-1 text-sm font-medium text-green-700 bg-green-100 rounded" style="background-color: ${subtask.status.background_color}20; color: ${subtask.status.background_color}">
            ${escapeHtml(subtask.status.name)}
        </span>
        ` : ''} 
        <a href="/tasks/${subtask.uuid}" class="flex-1 ml-4">
            <span>${escapeHtml(subtask.title)}</span>
        </a>
        <a href="/tasks/${subtask.uuid}" class="p-2 rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors">
            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>
        </a>
    </div>
`;

    // Add to the list
    if (listContainer) {
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = subtaskHtml.trim();
        const newElement = tempDiv.firstChild;
        listContainer.appendChild(newElement);
    }

    // Update count badge
    if (countBadge) {
        const currentCount = parseInt(countBadge.textContent) || 0;
        countBadge.textContent = currentCount + 1;
    }
}

/**
 * Escape HTML entities
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Close drawer on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const drawer = document.getElementById('subtask-drawer');
        if (drawer && !drawer.classList.contains('translate-x-full')) {
            closeSubtaskDrawer();
        }
    }
});
</script>
@endpush
@endonce
