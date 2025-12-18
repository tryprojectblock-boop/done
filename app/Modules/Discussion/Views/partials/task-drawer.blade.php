{{-- Task Creation Drawer - Creates a task from discussion comment --}}
{{-- Required variables: $discussion, $workspaces --}}

<!-- Drawer Overlay -->
<div id="task-drawer-overlay" class="fixed inset-0 bg-black/50 z-[100] hidden" onclick="closeTaskDrawer()"></div>

<!-- Drawer Panel -->
<div id="task-drawer" class="fixed top-0 right-0 h-full w-full max-w-xl bg-base-100 shadow-2xl z-[110] transform translate-x-full transition-transform duration-300 ease-in-out flex flex-col">
    <!-- Drawer Header (Fixed) -->
    <div class="flex items-center justify-between p-6 border-b border-base-300 bg-base-100">
        <div>
            <h2 class="text-xl font-bold">Create Task from Comment</h2>
            <p class="text-sm text-base-content/60">Fill in the details to create a new task</p>
        </div>
        <button type="button" onclick="closeTaskDrawer()" class="btn btn-ghost btn-sm btn-circle">
            <span class="icon-[tabler--x] size-5"></span>
        </button>
    </div>

    <!-- Scrollable Content -->
    <div class="flex-1 overflow-y-auto p-6">
        <!-- Task Form -->
        <form id="task-drawer-form" action="{{ route('discussions.create-task', $discussion) }}" method="POST">
            @csrf
            <input type="hidden" name="comment_id" id="task-comment-id">
            <input type="hidden" name="discussion_id" value="{{ $discussion->id }}">

            <div class="space-y-4">
                <!-- Task Title -->
                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-medium">Task Title <span class="text-error">*</span></span>
                    </label>
                    <input type="text" name="title" id="task-drawer-title" class="input input-bordered w-full" placeholder="Enter task title" required>
                </div>

                <!-- Workspace -->
                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-medium">Workspace <span class="text-error">*</span></span>
                    </label>
                    <input type="hidden" id="task-drawer-default-workspace" value="{{ $discussion->workspace_id }}">
                    <select name="workspace_id" id="task-drawer-workspace" data-select='{
                        "placeholder": "Select a workspace...",
                        "hasSearch": true,
                        "toggleTag": "<button type=\"button\" aria-expanded=\"false\"></button>",
                        "toggleClasses": "advance-select-toggle",
                        "dropdownClasses": "advance-select-menu max-h-52 overflow-y-auto",
                        "optionClasses": "advance-select-option selected:active",
                        "optionTemplate": "<div class=\"flex justify-between items-center w-full\"><span data-title></span><span class=\"icon-[tabler--check] shrink-0 size-4 text-primary hidden selected:block\"></span></div>",
                        "extraMarkup": "<span class=\"icon-[tabler--caret-up-down] shrink-0 size-4 text-base-content/90 absolute top-1/2 end-3 -translate-y-1/2\"></span>"
                    }' class="hidden" required>
                        <option value="">Select a workspace</option>
                        @foreach($workspaces as $workspace)
                            <option value="{{ $workspace->id }}" {{ $discussion->workspace_id == $workspace->id ? 'selected' : '' }}>
                                {{ $workspace->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Assignee -->
                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-medium">Assignee</span>
                    </label>
                    <div id="task-drawer-assignee-container">
                        <select name="assignee_id" id="task-drawer-assignee" data-select='{
                            "placeholder": "Select assignee...",
                            "hasSearch": true,
                            "toggleTag": "<button type=\"button\" aria-expanded=\"false\"></button>",
                            "toggleClasses": "advance-select-toggle",
                            "dropdownClasses": "advance-select-menu max-h-52 overflow-y-auto",
                            "optionClasses": "advance-select-option selected:active",
                            "optionTemplate": "<div class=\"flex justify-between items-center w-full\"><span data-title></span><span class=\"icon-[tabler--check] shrink-0 size-4 text-primary hidden selected:block\"></span></div>",
                            "extraMarkup": "<span class=\"icon-[tabler--caret-up-down] shrink-0 size-4 text-base-content/90 absolute top-1/2 end-3 -translate-y-1/2\"></span>"
                        }' class="hidden">
                            <option value="">Unassigned</option>
                        </select>
                    </div>
                </div>

                <!-- Priority & Due Date -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Priority</span>
                        </label>
                        <div id="task-drawer-priority-container">
                            <select name="workspace_priority_id" id="task-drawer-priority" data-select='{
                                "placeholder": "Select priority...",
                                "hasSearch": true,
                                "toggleTag": "<button type=\"button\" aria-expanded=\"false\"></button>",
                                "toggleClasses": "advance-select-toggle",
                                "dropdownClasses": "advance-select-menu max-h-52 overflow-y-auto",
                                "optionClasses": "advance-select-option selected:active",
                                "optionTemplate": "<div class=\"flex justify-between items-center w-full\"><span data-title></span><span class=\"icon-[tabler--check] shrink-0 size-4 text-primary hidden selected:block\"></span></div>",
                                "extraMarkup": "<span class=\"icon-[tabler--caret-up-down] shrink-0 size-4 text-base-content/90 absolute top-1/2 end-3 -translate-y-1/2\"></span>"
                            }' class="hidden">
                                <option value="">No Priority</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Due Date</span>
                        </label>
                        <input type="text" name="due_date" id="task-drawer-due-date" class="input input-bordered w-full" placeholder="Select due date" data-datepicker>
                    </div>
                </div>

                <!-- Description (from comment) -->
                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-medium">Description</span>
                    </label>
                    <div id="task-drawer-editor-container">
                        <x-quill-editor
                            name="description"
                            id="task-drawer-description"
                            placeholder="Task description..."
                            height="150px"
                        />
                    </div>
                    <label class="label">
                        <span class="label-text-alt text-base-content/50">Pre-filled from the comment content</span>
                    </label>
                </div>

                <!-- Source Info -->
                <div class="bg-base-200 rounded-lg p-3">
                    <p class="text-xs text-base-content/60">
                        <span class="icon-[tabler--link] size-3 inline"></span>
                        This task will be linked to the discussion: <strong>{{ $discussion->title }}</strong>
                    </p>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-3 mt-6 pt-4 border-t border-base-300">
                <button type="button" onclick="closeTaskDrawer()" class="btn btn-ghost flex-1">Cancel</button>
                <button type="submit" class="btn btn-primary flex-1" id="task-drawer-submit">
                    <span class="icon-[tabler--plus] size-5"></span>
                    Create Task
                </button>
            </div>
        </form>
    </div>
</div>

@once
@push('scripts')
<script>
// Task Drawer Variables
let taskDrawerDatepickerInitialized = false;

/**
 * Open the task drawer with comment content
 * @param {number} commentId - The comment ID
 * @param {string} commentContent - The HTML content of the comment
 */
function openTaskDrawer(commentId, commentContent) {
    document.getElementById('task-comment-id').value = commentId;

    // Clear title - let user enter their own
    document.getElementById('task-drawer-title').value = '';

    // Show drawer
    document.getElementById('task-drawer-overlay').classList.remove('hidden');
    document.getElementById('task-drawer').classList.remove('translate-x-full');
    document.body.classList.add('overflow-hidden');

    // Initialize HSSelect for workspace dropdown
    initTaskDrawerHSSelect();

    // Initialize datepicker if not already done
    initTaskDrawerDatepicker();

    // Get default workspace from hidden field and set it after a small delay
    // to ensure HSSelect is fully initialized
    const defaultWorkspaceId = document.getElementById('task-drawer-default-workspace').value;
    if (defaultWorkspaceId) {
        setTimeout(() => {
            // Set the workspace value using HSSelect API
            const workspaceSelect = document.getElementById('task-drawer-workspace');
            if (typeof HSSelect !== 'undefined') {
                const hsSelectInstance = HSSelect.getInstance(workspaceSelect);
                if (hsSelectInstance) {
                    hsSelectInstance.setValue(defaultWorkspaceId);
                }
            }
            // Load workspace data (assignees and priorities)
            loadWorkspaceData(defaultWorkspaceId);
        }, 100);
    }

    // Set description in Quill editor after a short delay to ensure editor is ready
    setTimeout(() => {
        setTaskDrawerDescription(commentContent);
    }, 100);
}

/**
 * Close the task drawer and reset the form
 */
function closeTaskDrawer() {
    document.getElementById('task-drawer-overlay').classList.add('hidden');
    document.getElementById('task-drawer').classList.add('translate-x-full');
    document.body.classList.remove('overflow-hidden');

    // Reset form
    document.getElementById('task-drawer-form').reset();
    document.getElementById('task-drawer-assignee').innerHTML = '<option value="">Unassigned</option>';
    document.getElementById('task-drawer-priority').innerHTML = '<option value="">No Priority</option>';

    // Clear Quill editor
    const editorElement = document.getElementById('task-drawer-description');
    if (editorElement && editorElement.quillInstance) {
        editorElement.quillInstance.setText('');
    }
}

/**
 * Decode HTML entities in a string
 * @param {string} html - String with HTML entities
 * @returns {string} - Decoded HTML string
 */
function decodeHtmlEntities(html) {
    const textarea = document.createElement('textarea');
    textarea.innerHTML = html;
    return textarea.value;
}

/**
 * Set content in the description Quill editor
 * @param {string} content - HTML content to set (may contain HTML entities)
 */
function setTaskDrawerDescription(content) {
    const editorElement = document.getElementById('task-drawer-description');

    // Decode HTML entities from the escaped data attribute
    const decodedContent = decodeHtmlEntities(content);

    // Check if Quill is initialized on this element
    if (editorElement && editorElement.quillInstance) {
        editorElement.quillInstance.root.innerHTML = decodedContent;
        // Also update the hidden input
        const hiddenInput = document.getElementById('task-drawer-description-input');
        if (hiddenInput) {
            hiddenInput.value = decodedContent;
        }
    } else if (typeof window.initQuillEditor === 'function') {
        // Try to initialize Quill if not already done
        window.initQuillEditor('task-drawer-description', 'Task description...', '{{ route("upload.image") }}', '{{ csrf_token() }}', decodedContent);
    }
}

/**
 * Initialize the flatpickr datepicker for due date
 */
function initTaskDrawerDatepicker() {
    if (taskDrawerDatepickerInitialized) return;

    const dateInput = document.getElementById('task-drawer-due-date');
    if (dateInput && typeof flatpickr !== 'undefined') {
        flatpickr(dateInput, {
            dateFormat: 'Y-m-d',
            allowInput: true,
            disableMobile: true
        });
        taskDrawerDatepickerInitialized = true;
    }
}

/**
 * Initialize HSSelect for workspace dropdown
 */
let taskDrawerHSSelectInitialized = false;
function initTaskDrawerHSSelect() {
    if (taskDrawerHSSelectInitialized) return;

    if (typeof HSSelect !== 'undefined') {
        // Initialize HSSelect components in the drawer
        HSSelect.autoInit();

        // Add change event listener to workspace select
        const workspaceSelect = document.getElementById('task-drawer-workspace');
        if (workspaceSelect) {
            workspaceSelect.addEventListener('change', function() {
                loadWorkspaceData(this.value);
            });
        }

        taskDrawerHSSelectInitialized = true;
    }
}

/**
 * Rebuild a select element with HSSelect styling
 * @param {string} containerId - The container element ID
 * @param {string} selectId - The select element ID
 * @param {string} selectName - The name attribute for the select
 * @param {string} placeholder - Placeholder text
 * @param {Array} options - Array of {value, label} objects
 */
function rebuildHSSelect(containerId, selectId, selectName, placeholder, options) {
    const container = document.getElementById(containerId);
    if (!container) return;

    // Build options HTML
    let optionsHtml = options.map(opt => `<option value="${opt.value}">${opt.label}</option>`).join('');

    // Rebuild the select with data-select attribute
    container.innerHTML = `
        <select name="${selectName}" id="${selectId}" data-select='{
            "placeholder": "${placeholder}",
            "hasSearch": true,
            "toggleTag": "<button type=\\"button\\" aria-expanded=\\"false\\"></button>",
            "toggleClasses": "advance-select-toggle",
            "dropdownClasses": "advance-select-menu max-h-52 overflow-y-auto",
            "optionClasses": "advance-select-option selected:active",
            "optionTemplate": "<div class=\\"flex justify-between items-center w-full\\"><span data-title></span><span class=\\"icon-[tabler--check] shrink-0 size-4 text-primary hidden selected:block\\"></span></div>",
            "extraMarkup": "<span class=\\"icon-[tabler--caret-up-down] shrink-0 size-4 text-base-content/90 absolute top-1/2 end-3 -translate-y-1/2\\"></span>"
        }' class="hidden">
            ${optionsHtml}
        </select>
    `;

    // Reinitialize HSSelect
    if (typeof HSSelect !== 'undefined') {
        HSSelect.autoInit();
    }
}

/**
 * Load workspace members and priorities via AJAX
 * @param {string|number} workspaceId - The workspace ID
 */
function loadWorkspaceData(workspaceId) {
    if (!workspaceId) {
        rebuildHSSelect('task-drawer-assignee-container', 'task-drawer-assignee', 'assignee_id', 'Select assignee...', [{value: '', label: 'Unassigned'}]);
        rebuildHSSelect('task-drawer-priority-container', 'task-drawer-priority', 'workspace_priority_id', 'Select priority...', [{value: '', label: 'No Priority'}]);
        return;
    }

    // Fetch workspace members and priorities
    fetch(`/workspaces/api/${workspaceId}/task-form-data`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            // Build assignee options
            let assigneeOptions = [{value: '', label: 'Unassigned'}];
            if (data.members && data.members.length > 0) {
                data.members.forEach(member => {
                    assigneeOptions.push({value: member.id, label: member.name});
                });
            }
            rebuildHSSelect('task-drawer-assignee-container', 'task-drawer-assignee', 'assignee_id', 'Select assignee...', assigneeOptions);

            // Build priority options
            let priorityOptions = [{value: '', label: 'No Priority'}];
            if (data.priorities && data.priorities.length > 0) {
                data.priorities.forEach(priority => {
                    priorityOptions.push({value: priority.id, label: priority.name});
                });
            }
            rebuildHSSelect('task-drawer-priority-container', 'task-drawer-priority', 'workspace_priority_id', 'Select priority...', priorityOptions);
        })
        .catch(error => {
            console.error('Error loading workspace data:', error);
            rebuildHSSelect('task-drawer-assignee-container', 'task-drawer-assignee', 'assignee_id', 'Select assignee...', [{value: '', label: 'Unassigned'}]);
            rebuildHSSelect('task-drawer-priority-container', 'task-drawer-priority', 'workspace_priority_id', 'Select priority...', [{value: '', label: 'No Priority'}]);
        });
}


// Close drawer on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const drawer = document.getElementById('task-drawer');
        if (drawer && !drawer.classList.contains('translate-x-full')) {
            closeTaskDrawer();
        }
    }
});
</script>
@endpush
@endonce
