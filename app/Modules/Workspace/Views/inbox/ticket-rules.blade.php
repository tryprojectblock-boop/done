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
                <span>Ticket Rules</span>
            </div>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <a href="{{ route('workspace.show', $workspace) }}" class="btn btn-ghost btn-sm btn-square">
                        <span class="icon-[tabler--arrow-left] size-5"></span>
                    </a>
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-lg bg-info/10 flex items-center justify-center">
                            <span class="icon-[tabler--git-branch] size-6 text-info"></span>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-base-content">Ticket Rules</h1>
                            <p class="text-sm text-base-content/60">Set up automation rules for incoming tickets</p>
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

        @if($departments->count() === 0)
            <div class="alert alert-warning">
                <span class="icon-[tabler--alert-triangle] size-5"></span>
                <span>No departments configured. Please <a href="{{ route('workspace.inbox.departments', $workspace) }}" class="link">add departments</a> first to create ticket rules.</span>
            </div>
        @else
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Add Rule Form -->
                <div class="card bg-base-100 shadow">
                    <div class="card-body">
                        <h2 class="card-title text-lg mb-4" id="rule-form-title">
                            <span class="icon-[tabler--plus] size-5" id="rule-form-icon"></span>
                            <span id="rule-form-title-text">Add Rule</span>
                        </h2>

                        <form id="rule-form" onsubmit="submitRuleForm(event)">
                            <input type="hidden" name="action" id="rule-action" value="add">
                            <input type="hidden" name="edit_id" id="rule-edit-id" value="">

                            <div class="space-y-4">
                                <!-- Department -->
                                @php
                                    $usedDepartmentIds = $ticketRules->pluck('department_id')->toArray();
                                @endphp
                                <div class="form-control">
                                    <label class="label">
                                        <span class="label-text font-medium">Department <span class="text-error">*</span></span>
                                    </label>
                                    <select name="department_id" id="rule-department" data-select='{
                                        "placeholder": "Search and select department...",
                                        "hasSearch": true,
                                        "toggleTag": "<button type=\"button\" aria-expanded=\"false\"></button>",
                                        "toggleClasses": "advance-select-toggle",
                                        "dropdownClasses": "advance-select-menu max-h-52 overflow-y-auto",
                                        "optionClasses": "advance-select-option selected:select-active",
                                        "optionTemplate": "<div class=\"flex justify-between items-center w-full\"><span class=\"text-sm text-base-content\" data-title></span><span class=\"icon-[tabler--check] shrink-0 size-4 text-primary hidden selected:block\"></span></div>",
                                        "extraMarkup": "<span class=\"icon-[tabler--caret-up-down] shrink-0 size-4 text-base-content/90 absolute top-1/2 end-3 -translate-y-1/2\"></span>"
                                    }' class="hidden" required>
                                        <option value="">Select department</option>
                                        @foreach($departments as $department)
                                            @if(in_array($department->id, $usedDepartmentIds))
                                                <option value="{{ $department->id }}" data-used="true" disabled>{{ $department->name }} (has rule)</option>
                                            @else
                                                <option value="{{ $department->id }}">{{ $department->name }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                    <label class="label">
                                        <span class="label-text-alt text-base-content/50">Tickets for this department will be auto-assigned</span>
                                    </label>
                                </div>

                                <!-- Primary Assignee -->
                                <div class="form-control">
                                    <label class="label">
                                        <span class="label-text font-medium">Primary Assignee</span>
                                    </label>
                                    <select name="assigned_user_id" id="rule-assignee" data-select='{
                                        "placeholder": "Search and select assignee...",
                                        "hasSearch": true,
                                        "toggleTag": "<button type=\"button\" aria-expanded=\"false\"></button>",
                                        "toggleClasses": "advance-select-toggle",
                                        "dropdownClasses": "advance-select-menu max-h-52 overflow-y-auto",
                                        "optionClasses": "advance-select-option selected:select-active",
                                        "optionTemplate": "<div class=\"flex justify-between items-center w-full\"><div class=\"flex items-center gap-2\"><div data-icon></div><span class=\"text-sm text-base-content\" data-title></span></div><span class=\"icon-[tabler--check] shrink-0 size-4 text-primary hidden selected:block\"></span></div>",
                                        "extraMarkup": "<span class=\"icon-[tabler--caret-up-down] shrink-0 size-4 text-base-content/90 absolute top-1/2 end-3 -translate-y-1/2\"></span>"
                                    }' class="hidden">
                                        <option value="">No auto-assignment</option>
                                        @foreach($workspace->members as $member)
                                            <option value="{{ $member->id }}" data-select-option='{
                                                "icon": "<div class=\"avatar\"><div class=\"w-6 rounded-full\"><img src=\"{{ $member->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($member->name) . '&background=random' }}\" alt=\"{{ $member->name }}\" /></div></div>"
                                            }'>{{ $member->name }}</option>
                                        @endforeach
                                    </select>
                                    <label class="label">
                                        <span class="label-text-alt text-base-content/50">Tickets will be automatically assigned to this person</span>
                                    </label>
                                </div>

                                <!-- Backup Assignee -->
                                <div class="form-control">
                                    <label class="label">
                                        <span class="label-text font-medium">Backup Assignee</span>
                                    </label>
                                    <select name="backup_user_id" id="rule-backup" data-select='{
                                        "placeholder": "Search and select backup...",
                                        "hasSearch": true,
                                        "toggleTag": "<button type=\"button\" aria-expanded=\"false\"></button>",
                                        "toggleClasses": "advance-select-toggle",
                                        "dropdownClasses": "advance-select-menu max-h-52 overflow-y-auto",
                                        "optionClasses": "advance-select-option selected:select-active",
                                        "optionTemplate": "<div class=\"flex justify-between items-center w-full\"><div class=\"flex items-center gap-2\"><div data-icon></div><span class=\"text-sm text-base-content\" data-title></span></div><span class=\"icon-[tabler--check] shrink-0 size-4 text-primary hidden selected:block\"></span></div>",
                                        "extraMarkup": "<span class=\"icon-[tabler--caret-up-down] shrink-0 size-4 text-base-content/90 absolute top-1/2 end-3 -translate-y-1/2\"></span>"
                                    }' class="hidden">
                                        <option value="">No backup</option>
                                        @foreach($workspace->members as $member)
                                            <option value="{{ $member->id }}" data-select-option='{
                                                "icon": "<div class=\"avatar\"><div class=\"w-6 rounded-full\"><img src=\"{{ $member->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($member->name) . '&background=random' }}\" alt=\"{{ $member->name }}\" /></div></div>"
                                            }'>{{ $member->name }}</option>
                                        @endforeach
                                    </select>
                                    <label class="label">
                                        <span class="label-text-alt text-base-content/50">Fallback when primary assignee is unavailable</span>
                                    </label>
                                </div>

                                <!-- Submit Buttons -->
                                <div class="flex gap-2 pt-2">
                                    <button type="submit" class="btn btn-info flex-1 gap-2" id="rule-submit-btn">
                                        <span class="icon-[tabler--plus] size-5" id="rule-submit-icon"></span>
                                        <span id="rule-submit-text">Add Rule</span>
                                    </button>
                                    <button type="button" class="btn btn-ghost hidden" id="rule-cancel-edit-btn" onclick="cancelEditRule()">
                                        Cancel
                                    </button>
                                </div>
                                <div id="rule-form-error" class="text-error text-sm hidden"></div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Rules List -->
                <div class="card bg-base-100 shadow">
                    <div class="card-body">
                        <h2 class="card-title text-lg mb-4">
                            <span class="icon-[tabler--list] size-5"></span>
                            Existing Rules
                            <span class="badge badge-ghost badge-sm" id="rules-count">{{ $ticketRules->count() }}</span>
                        </h2>

                        <div class="space-y-2 {{ $ticketRules->count() === 0 ? 'hidden' : '' }}" id="rules-list">
                            @foreach($ticketRules as $rule)
                                <div class="flex items-center justify-between p-3 bg-base-200 rounded-lg group rule-item"
                                     data-id="{{ $rule->id }}"
                                     data-department="{{ $rule->department_id }}"
                                     data-assignee="{{ $rule->assigned_user_id }}"
                                     data-backup="{{ $rule->backup_user_id }}">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg bg-info/20 flex items-center justify-center">
                                            <span class="icon-[tabler--git-branch] size-5 text-info"></span>
                                        </div>
                                        <div>
                                            <p class="font-medium text-sm">{{ $rule->department->name ?? 'Unknown' }}</p>
                                            <div class="flex flex-col gap-0.5 text-xs text-base-content/50">
                                                @if($rule->assignedUser)
                                                    <span class="flex items-center gap-1">
                                                        <span class="icon-[tabler--user] size-3"></span>
                                                        {{ $rule->assignedUser->name }}
                                                    </span>
                                                @endif
                                                @if($rule->backupUser)
                                                    <span class="flex items-center gap-1">
                                                        <span class="icon-[tabler--user-share] size-3"></span>
                                                        Backup: {{ $rule->backupUser->name }}
                                                    </span>
                                                @endif
                                                @if(!$rule->assignedUser && !$rule->backupUser)
                                                    <span class="text-base-content/40">No assignees set</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button type="button" class="btn btn-ghost btn-xs btn-square" onclick="editRule({{ $rule->id }})" title="Edit">
                                            <span class="icon-[tabler--edit] size-4"></span>
                                        </button>
                                        <button type="button" class="btn btn-ghost btn-xs btn-square text-error" onclick="confirmDeleteRule({{ $rule->id }}, '{{ addslashes($rule->department->name ?? 'Unknown') }}')" title="Delete">
                                            <span class="icon-[tabler--trash] size-4"></span>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="text-center py-8 text-base-content/50 {{ $ticketRules->count() > 0 ? 'hidden' : '' }}" id="no-rules-msg">
                            <span class="icon-[tabler--git-branch-deleted] size-12 mb-2 opacity-50"></span>
                            <p class="text-sm">No ticket rules configured</p>
                            <p class="text-xs">Add your first rule using the form</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Info Alert -->
            <div class="alert alert-info mt-6">
                <span class="icon-[tabler--info-circle] size-5"></span>
                <span class="text-sm">Ticket rules automatically assign incoming tickets to team members based on the department. This helps distribute workload and ensures tickets are handled by the right people.</span>
            </div>
        @endif
    </div>
</div>

<!-- Delete Rule Confirmation Modal -->
<div id="delete-rule-modal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50" onclick="closeDeleteRuleModal()"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="bg-base-100 rounded-xl shadow-xl max-w-md w-full p-6 relative z-10">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-12 h-12 rounded-full bg-error/20 flex items-center justify-center">
                    <span class="icon-[tabler--trash] size-6 text-error"></span>
                </div>
                <div>
                    <h3 class="text-lg font-bold">Delete Rule</h3>
                    <p class="text-sm text-base-content/60">This action cannot be undone</p>
                </div>
            </div>
            <p class="text-base-content/70 mb-4">
                Are you sure you want to delete the rule for "<span id="delete-rule-name" class="font-semibold"></span>"?
            </p>
            <div class="flex justify-end gap-2">
                <button type="button" class="btn btn-ghost" onclick="closeDeleteRuleModal()">Cancel</button>
                <button type="button" class="btn btn-error gap-2" onclick="deleteRule()" id="confirm-delete-rule-btn">
                    <span class="icon-[tabler--trash] size-5"></span>
                    Delete Rule
                </button>
            </div>
        </div>
    </div>
</div>

<script>
const rulesEndpoint = '{{ route('workspace.save-ticket-rules', $workspace) }}';
const csrfToken = '{{ csrf_token() }}';
let deleteRuleId = null;

// Helper function to set HSSelect value programmatically
function setSelectValue(selectId, value) {
    // FlyonUI's HSSelect.getInstance expects a CSS selector string
    const selector = '#' + selectId;

    // Try to get HSSelect instance using selector
    let hsSelectInstance = null;
    if (window.HSSelect && typeof HSSelect.getInstance === 'function') {
        hsSelectInstance = HSSelect.getInstance(selector);
    }

    if (hsSelectInstance && typeof hsSelectInstance.setValue === 'function') {
        hsSelectInstance.setValue(value || '');
        return;
    }

    // Fallback: manually update the native select and UI
    const selectEl = document.getElementById(selectId);
    if (!selectEl) return;

    selectEl.value = value || '';

    // FlyonUI wraps the select in a div containing toggle and dropdown
    const wrapper = selectEl.parentElement;
    if (wrapper) {
        const toggleBtn = wrapper.querySelector('.advance-select-toggle');
        if (toggleBtn) {
            const selectedOption = value ? selectEl.querySelector(`option[value="${value}"]`) : null;

            // Find the title span
            let titleSpan = toggleBtn.querySelector('[data-title]');
            if (!titleSpan) {
                const spans = toggleBtn.querySelectorAll('span');
                for (const span of spans) {
                    if (!span.className.includes('icon-[')) {
                        titleSpan = span;
                        break;
                    }
                }
            }

            if (titleSpan) {
                if (selectedOption && value) {
                    titleSpan.textContent = selectedOption.textContent.trim();
                    titleSpan.classList.remove('text-base-content/50');
                } else {
                    try {
                        const config = JSON.parse(selectEl.dataset.select || '{}');
                        titleSpan.textContent = config.placeholder || 'Select...';
                    } catch (e) {
                        titleSpan.textContent = 'Select...';
                    }
                    titleSpan.classList.add('text-base-content/50');
                }
            }
        }

        // Update dropdown options selection state
        const dropdownItems = wrapper.querySelectorAll('[data-value]');
        dropdownItems.forEach(item => {
            item.classList.toggle('selected', item.dataset.value === String(value));
        });
    }

    selectEl.dispatchEvent(new Event('change', { bubbles: true }));
}

function resetRuleForm() {
    document.getElementById('rule-action').value = 'add';
    document.getElementById('rule-edit-id').value = '';
    document.getElementById('rule-form-icon').className = 'icon-[tabler--plus] size-5';
    document.getElementById('rule-form-title-text').textContent = 'Add Rule';
    document.getElementById('rule-submit-icon').className = 'icon-[tabler--plus] size-5';
    document.getElementById('rule-submit-text').textContent = 'Add Rule';
    document.getElementById('rule-cancel-edit-btn').classList.add('hidden');
    document.getElementById('rule-submit-btn').classList.remove('btn-success');
    document.getElementById('rule-submit-btn').classList.add('btn-info');
    document.getElementById('rule-form-error').classList.add('hidden');

    // Reset all select values using HSSelect API
    setSelectValue('rule-department', '');
    setSelectValue('rule-assignee', '');
    setSelectValue('rule-backup', '');
}

function editRule(ruleId) {
    const item = document.querySelector(`.rule-item[data-id="${ruleId}"]`);
    if (!item) return;

    const departmentId = item.dataset.department;
    const assigneeId = item.dataset.assignee;
    const backupId = item.dataset.backup;

    document.getElementById('rule-action').value = 'edit';
    document.getElementById('rule-edit-id').value = ruleId;

    // Set select values - need a slight delay for FlyonUI components
    setTimeout(() => {
        setSelectValue('rule-department', departmentId);
        setSelectValue('rule-assignee', assigneeId || '');
        setSelectValue('rule-backup', backupId || '');

        // Try to reinitialize FlyonUI components if available
        if (window.HSStaticMethods && typeof HSStaticMethods.autoInit === 'function') {
            HSStaticMethods.autoInit();
        }
    }, 50);

    document.getElementById('rule-form-icon').className = 'icon-[tabler--edit] size-5';
    document.getElementById('rule-form-title-text').textContent = 'Edit Rule';
    document.getElementById('rule-submit-icon').className = 'icon-[tabler--check] size-5';
    document.getElementById('rule-submit-text').textContent = 'Save Changes';
    document.getElementById('rule-cancel-edit-btn').classList.remove('hidden');
    document.getElementById('rule-submit-btn').classList.remove('btn-info');
    document.getElementById('rule-submit-btn').classList.add('btn-success');

    document.getElementById('rule-form').scrollIntoView({ behavior: 'smooth', block: 'center' });
}

function cancelEditRule() {
    resetRuleForm();
}

// Helper function to get HSSelect value
function getSelectValue(selectId) {
    const selectEl = document.getElementById(selectId);
    if (!selectEl) return '';

    // The native select value should always be synced
    return selectEl.value || '';
}

async function submitRuleForm(event) {
    event.preventDefault();

    const action = document.getElementById('rule-action').value;
    const departmentId = getSelectValue('rule-department');
    const assigneeId = getSelectValue('rule-assignee');
    const backupId = getSelectValue('rule-backup');
    const editId = document.getElementById('rule-edit-id').value;
    const errorDiv = document.getElementById('rule-form-error');

    if (!departmentId) {
        errorDiv.textContent = 'Department is required.';
        errorDiv.classList.remove('hidden');
        return;
    }

    // Check if department already has a rule (only for add action)
    if (action === 'add') {
        const selectedOption = document.querySelector(`#rule-department option[value="${departmentId}"]`);
        if (selectedOption && selectedOption.dataset.used === 'true') {
            errorDiv.textContent = 'This department already has a rule. Please edit the existing rule instead.';
            errorDiv.classList.remove('hidden');
            return;
        }
    }

    const formData = new FormData();
    formData.append('_token', csrfToken);
    formData.append('action', action);
    formData.append('department_id', departmentId);
    formData.append('assigned_user_id', assigneeId);
    formData.append('backup_user_id', backupId);
    if (action === 'edit') {
        formData.append('edit_id', editId);
    }

    try {
        const response = await fetch(rulesEndpoint, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            }
        });

        const data = await response.json();

        if (data.success) {
            errorDiv.classList.add('hidden');
            showToast(data.message || 'Rule saved successfully.', 'success');
            // Reload to show updated list
            setTimeout(() => window.location.reload(), 500);
        } else {
            errorDiv.textContent = data.message || 'An error occurred.';
            errorDiv.classList.remove('hidden');
        }
    } catch (error) {
        errorDiv.textContent = 'An error occurred. Please try again.';
        errorDiv.classList.remove('hidden');
    }
}

function confirmDeleteRule(ruleId, name) {
    deleteRuleId = ruleId;
    document.getElementById('delete-rule-name').textContent = name;
    document.getElementById('delete-rule-modal').classList.remove('hidden');
}

function closeDeleteRuleModal() {
    document.getElementById('delete-rule-modal').classList.add('hidden');
    deleteRuleId = null;
}

async function deleteRule() {
    if (deleteRuleId === null) return;

    const formData = new FormData();
    formData.append('_token', csrfToken);
    formData.append('action', 'delete');
    formData.append('delete_id', deleteRuleId);

    try {
        const response = await fetch(rulesEndpoint, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            }
        });

        const data = await response.json();

        if (data.success) {
            const item = document.querySelector(`.rule-item[data-id="${deleteRuleId}"]`);
            if (item) item.remove();

            const list = document.getElementById('rules-list');
            const noMsg = document.getElementById('no-rules-msg');
            const countBadge = document.getElementById('rules-count');
            const count = list ? list.querySelectorAll('.rule-item').length : 0;

            if (countBadge) countBadge.textContent = count;

            if (count === 0) {
                list?.classList.add('hidden');
                noMsg?.classList.remove('hidden');
            }

            showToast(data.message || 'Rule deleted successfully.', 'success');
        } else {
            showToast(data.message || 'An error occurred.', 'error');
        }
    } catch (error) {
        showToast('An error occurred. Please try again.', 'error');
    }

    closeDeleteRuleModal();
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

// Close on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const deleteModal = document.getElementById('delete-rule-modal');
        if (deleteModal && !deleteModal.classList.contains('hidden')) {
            closeDeleteRuleModal();
        }
    }
});
</script>
@endsection
