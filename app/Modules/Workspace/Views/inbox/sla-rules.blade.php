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
                <span>SLA Rules</span>
            </div>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <a href="{{ route('workspace.show', $workspace) }}" class="btn btn-ghost btn-sm btn-square">
                        <span class="icon-[tabler--arrow-left] size-5"></span>
                    </a>
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-lg bg-warning/10 flex items-center justify-center">
                            <span class="icon-[tabler--alert-triangle] size-6 text-warning"></span>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-base-content">SLA Rules</h1>
                            <p class="text-sm text-base-content/60">Configure SLA escalation and breach rules</p>
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
            // Track existing department+priority combinations
            $existingRules = $slaRules->map(function($rule) {
                return $rule->department_id . '-' . $rule->priority_id;
            })->toArray();
        @endphp

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Add SLA Rule Form -->
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <h2 class="card-title text-lg mb-4" id="sla-rule-form-title">
                        <span class="icon-[tabler--plus] size-5" id="sla-rule-form-icon"></span>
                        <span id="sla-rule-form-title-text">Add SLA Rule</span>
                    </h2>

                    <form id="sla-rule-form" onsubmit="submitSlaRuleForm(event)">
                        <input type="hidden" name="action" id="sla-rule-action" value="add">
                        <input type="hidden" name="edit_id" id="sla-rule-edit-id" value="">

                        <div class="space-y-4">
                            <!-- Department -->
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium">Department <span class="text-error">*</span></span>
                                </label>
                                <select name="department_id" id="sla-rule-department" data-select='{
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
                                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                                    @endforeach
                                </select>
                                <label class="label">
                                    <span class="label-text-alt text-base-content/50">Each department can have SLA rules for each priority</span>
                                </label>
                            </div>

                            <!-- Priority -->
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium">Priority <span class="text-error">*</span></span>
                                </label>
                                <select name="priority_id" id="sla-rule-priority" data-select='{
                                    "placeholder": "Select priority...",
                                    "hasSearch": true,
                                    "toggleTag": "<button type=\"button\" aria-expanded=\"false\"></button>",
                                    "toggleClasses": "advance-select-toggle",
                                    "dropdownClasses": "advance-select-menu max-h-52 overflow-y-auto",
                                    "optionClasses": "advance-select-option selected:select-active",
                                    "optionTemplate": "<div class=\"flex justify-between items-center w-full\"><span class=\"text-sm text-base-content\" data-title></span><span class=\"icon-[tabler--check] shrink-0 size-4 text-primary hidden selected:block\"></span></div>",
                                    "extraMarkup": "<span class=\"icon-[tabler--caret-up-down] shrink-0 size-4 text-base-content/90 absolute top-1/2 end-3 -translate-y-1/2\"></span>"
                                }' class="hidden" required>
                                    <option value="">Select priority</option>
                                    @foreach($priorities as $priority)
                                        <option value="{{ $priority->id }}" data-sla-hours="{{ $prioritySlaHours[$priority->id] ?? 24 }}">{{ $priority->name }}</option>
                                    @endforeach
                                </select>
                                <label class="label">
                                    <span class="label-text-alt text-base-content/50">Resolution hours will be auto-filled from SLA settings</span>
                                </label>
                            </div>

                            <!-- Resolution Hours -->
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium">Resolution Hours <span class="text-error">*</span></span>
                                </label>
                                <input type="number" name="resolution_hours" id="sla-rule-hours" class="input input-bordered" min="1" max="720" value="24" required>
                                <label class="label">
                                    <span class="label-text-alt text-base-content/50">Hours before escalation (auto-filled from SLA settings)</span>
                                </label>
                            </div>

                            <!-- Assigned Member -->
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium">Escalate To</span>
                                </label>
                                <select name="assigned_user_id" id="sla-rule-assignee" data-select='{
                                    "placeholder": "Search and select member...",
                                    "hasSearch": true,
                                    "toggleTag": "<button type=\"button\" aria-expanded=\"false\"></button>",
                                    "toggleClasses": "advance-select-toggle",
                                    "dropdownClasses": "advance-select-menu max-h-52 overflow-y-auto",
                                    "optionClasses": "advance-select-option selected:select-active",
                                    "optionTemplate": "<div class=\"flex justify-between items-center w-full\"><div class=\"flex items-center gap-2\"><div data-icon></div><span class=\"text-sm text-base-content\" data-title></span></div><span class=\"icon-[tabler--check] shrink-0 size-4 text-primary hidden selected:block\"></span></div>",
                                    "extraMarkup": "<span class=\"icon-[tabler--caret-up-down] shrink-0 size-4 text-base-content/90 absolute top-1/2 end-3 -translate-y-1/2\"></span>"
                                }' class="hidden">
                                    <option value="">No escalation assignee</option>
                                    @foreach($workspace->members as $member)
                                        <option value="{{ $member->id }}" data-select-option='{
                                            "icon": "<div class=\"avatar\"><div class=\"w-6 rounded-full\"><img src=\"{{ $member->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($member->name) . '&background=random' }}\" alt=\"{{ $member->name }}\" /></div></div>"
                                        }'>{{ $member->name }}</option>
                                    @endforeach
                                </select>
                                <label class="label">
                                    <span class="label-text-alt text-base-content/50">Person to notify when SLA is breached</span>
                                </label>
                            </div>

                            <!-- Escalation Notes -->
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium">Escalation Notes</span>
                                </label>
                                <textarea name="escalation_notes" id="sla-rule-notes" class="textarea textarea-bordered" rows="2" placeholder="Notes about this escalation rule..."></textarea>
                            </div>

                            <!-- Submit Buttons -->
                            <div class="flex gap-2 pt-2">
                                <button type="submit" class="btn btn-warning flex-1 gap-2" id="sla-rule-submit-btn">
                                    <span class="icon-[tabler--plus] size-5" id="sla-rule-submit-icon"></span>
                                    <span id="sla-rule-submit-text">Add Rule</span>
                                </button>
                                <button type="button" class="btn btn-ghost hidden" id="sla-rule-cancel-edit-btn" onclick="cancelEditSlaRule()">
                                    Cancel
                                </button>
                            </div>
                            <div id="sla-rule-form-error" class="text-error text-sm hidden"></div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- SLA Rules List -->
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <h2 class="card-title text-lg mb-4">
                        <span class="icon-[tabler--list] size-5"></span>
                        Existing SLA Rules
                        <span class="badge badge-ghost badge-sm" id="sla-rules-count">{{ $slaRules->count() }}</span>
                    </h2>

                    <div class="space-y-2 {{ $slaRules->count() === 0 ? 'hidden' : '' }}" id="sla-rules-list">
                        @foreach($slaRules as $rule)
                            <div class="flex items-center justify-between p-3 bg-base-200 rounded-lg group sla-rule-item"
                                 data-id="{{ $rule->id }}"
                                 data-department="{{ $rule->department_id }}"
                                 data-priority="{{ $rule->priority_id }}"
                                 data-hours="{{ $rule->resolution_hours }}"
                                 data-assignee="{{ $rule->assigned_user_id }}"
                                 data-notes="{{ $rule->escalation_notes }}">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg bg-warning/20 flex items-center justify-center">
                                        <span class="icon-[tabler--alert-triangle] size-5 text-warning"></span>
                                    </div>
                                    <div>
                                        <p class="font-medium text-sm">
                                            {{ $rule->department->name ?? 'Unknown Department' }}
                                            @if($rule->priority)
                                                <span class="badge badge-ghost badge-xs">{{ $rule->priority->name }}</span>
                                            @endif
                                        </p>
                                        <div class="flex flex-wrap items-center gap-2 text-xs text-base-content/50">
                                            <span class="flex items-center gap-1">
                                                <span class="icon-[tabler--clock] size-3"></span>
                                                {{ $rule->resolution_hours }} hours
                                            </span>
                                            @if($rule->assignedUser)
                                                <span class="flex items-center gap-1">
                                                    <span class="icon-[tabler--user] size-3"></span>
                                                    {{ $rule->assignedUser->name }}
                                                </span>
                                            @endif
                                            @if($rule->escalation_notes)
                                                <span class="flex items-center gap-1 truncate max-w-[100px]" title="{{ $rule->escalation_notes }}">
                                                    <span class="icon-[tabler--note] size-3"></span>
                                                    {{ Str::limit($rule->escalation_notes, 20) }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button type="button" class="btn btn-ghost btn-xs btn-square" onclick="editSlaRule({{ $rule->id }})" title="Edit">
                                        <span class="icon-[tabler--edit] size-4"></span>
                                    </button>
                                    <button type="button" class="btn btn-ghost btn-xs btn-square text-error" onclick="confirmDeleteSlaRule({{ $rule->id }}, '{{ addslashes($rule->department->name ?? 'Unknown') }}')" title="Delete">
                                        <span class="icon-[tabler--trash] size-4"></span>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="text-center py-8 text-base-content/50 {{ $slaRules->count() > 0 ? 'hidden' : '' }}" id="no-sla-rules-msg">
                        <span class="icon-[tabler--alert-triangle-off] size-12 mb-2 opacity-50"></span>
                        <p class="text-sm">No SLA rules configured</p>
                        <p class="text-xs">Add your first rule using the form</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Info Alert -->
        <div class="alert alert-info mt-6">
            <span class="icon-[tabler--info-circle] size-5"></span>
            <span class="text-sm">SLA rules define when tickets should be escalated if not resolved within the specified time. Each department can have one rule with a specific priority level.</span>
        </div>
    </div>
</div>

<!-- Delete SLA Rule Confirmation Modal -->
<div id="delete-sla-rule-modal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50" onclick="closeDeleteSlaRuleModal()"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="bg-base-100 rounded-xl shadow-xl max-w-md w-full p-6 relative z-10">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-12 h-12 rounded-full bg-error/20 flex items-center justify-center">
                    <span class="icon-[tabler--trash] size-6 text-error"></span>
                </div>
                <div>
                    <h3 class="text-lg font-bold">Delete SLA Rule</h3>
                    <p class="text-sm text-base-content/60">This action cannot be undone</p>
                </div>
            </div>
            <p class="text-base-content/70 mb-4">
                Are you sure you want to delete the SLA rule for "<span id="delete-sla-rule-name" class="font-semibold"></span>"?
            </p>
            <div class="flex justify-end gap-2">
                <button type="button" class="btn btn-ghost" onclick="closeDeleteSlaRuleModal()">Cancel</button>
                <button type="button" class="btn btn-error gap-2" onclick="deleteSlaRule()" id="confirm-delete-sla-rule-btn">
                    <span class="icon-[tabler--trash] size-5"></span>
                    Delete Rule
                </button>
            </div>
        </div>
    </div>
</div>

<script>
const slaRulesEndpoint = '{{ route('workspace.save-sla-rules', $workspace) }}';
const csrfToken = '{{ csrf_token() }}';
const prioritySlaHours = @json($prioritySlaHours);
// Track existing department+priority combinations
let existingRules = @json($existingRules);
let deleteSlaRuleId = null;
let currentEditId = null;

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

// Helper function to get HSSelect value
function getSelectValue(selectId) {
    const selectEl = document.getElementById(selectId);
    if (!selectEl) return '';

    // The native select value should always be synced
    return selectEl.value || '';
}

// Auto-fill resolution hours when priority changes
document.addEventListener('DOMContentLoaded', function() {
    const priorityEl = document.getElementById('sla-rule-priority');
    if (priorityEl) {
        // Listen on the underlying select element
        priorityEl.addEventListener('change', function() {
            const priorityId = getSelectValue('sla-rule-priority');
            if (priorityId && prioritySlaHours[priorityId]) {
                document.getElementById('sla-rule-hours').value = prioritySlaHours[priorityId];
            }
        });
    }
});

function resetSlaRuleForm() {
    currentEditId = null;
    document.getElementById('sla-rule-action').value = 'add';
    document.getElementById('sla-rule-edit-id').value = '';
    document.getElementById('sla-rule-hours').value = '24';
    document.getElementById('sla-rule-notes').value = '';
    document.getElementById('sla-rule-form-icon').className = 'icon-[tabler--plus] size-5';
    document.getElementById('sla-rule-form-title-text').textContent = 'Add SLA Rule';
    document.getElementById('sla-rule-submit-icon').className = 'icon-[tabler--plus] size-5';
    document.getElementById('sla-rule-submit-text').textContent = 'Add Rule';
    document.getElementById('sla-rule-cancel-edit-btn').classList.add('hidden');
    document.getElementById('sla-rule-submit-btn').classList.remove('btn-success');
    document.getElementById('sla-rule-submit-btn').classList.add('btn-warning');
    document.getElementById('sla-rule-form-error').classList.add('hidden');

    // Reset all select values using HSSelect API
    setSelectValue('sla-rule-department', '');
    setSelectValue('sla-rule-priority', '');
    setSelectValue('sla-rule-assignee', '');
}

function editSlaRule(ruleId) {
    const item = document.querySelector(`.sla-rule-item[data-id="${ruleId}"]`);
    if (!item) return;

    const departmentId = item.dataset.department;
    const priorityId = item.dataset.priority;
    const hours = item.dataset.hours;
    const assigneeId = item.dataset.assignee;
    const notes = item.dataset.notes;

    // Track the current rule's key for validation
    currentEditId = ruleId;

    document.getElementById('sla-rule-action').value = 'edit';
    document.getElementById('sla-rule-edit-id').value = ruleId;
    document.getElementById('sla-rule-hours').value = hours;
    document.getElementById('sla-rule-notes').value = notes || '';

    // Set select values - need a slight delay for FlyonUI components
    setTimeout(() => {
        setSelectValue('sla-rule-department', departmentId || '');
        setSelectValue('sla-rule-priority', priorityId || '');
        setSelectValue('sla-rule-assignee', assigneeId || '');

        // Try to reinitialize FlyonUI components if available
        if (window.HSStaticMethods && typeof HSStaticMethods.autoInit === 'function') {
            HSStaticMethods.autoInit();
        }
    }, 50);

    document.getElementById('sla-rule-form-icon').className = 'icon-[tabler--edit] size-5';
    document.getElementById('sla-rule-form-title-text').textContent = 'Edit SLA Rule';
    document.getElementById('sla-rule-submit-icon').className = 'icon-[tabler--check] size-5';
    document.getElementById('sla-rule-submit-text').textContent = 'Save Changes';
    document.getElementById('sla-rule-cancel-edit-btn').classList.remove('hidden');
    document.getElementById('sla-rule-submit-btn').classList.remove('btn-warning');
    document.getElementById('sla-rule-submit-btn').classList.add('btn-success');

    document.getElementById('sla-rule-form').scrollIntoView({ behavior: 'smooth', block: 'center' });
}

function cancelEditSlaRule() {
    resetSlaRuleForm();
}

async function submitSlaRuleForm(event) {
    event.preventDefault();

    const action = document.getElementById('sla-rule-action').value;
    const departmentId = getSelectValue('sla-rule-department');
    const priorityId = getSelectValue('sla-rule-priority');
    const hours = document.getElementById('sla-rule-hours').value;
    const assigneeId = getSelectValue('sla-rule-assignee');
    const notes = document.getElementById('sla-rule-notes').value;
    const editId = document.getElementById('sla-rule-edit-id').value;
    const errorDiv = document.getElementById('sla-rule-form-error');

    if (!departmentId) {
        errorDiv.textContent = 'Department is required.';
        errorDiv.classList.remove('hidden');
        return;
    }

    if (!priorityId) {
        errorDiv.textContent = 'Priority is required.';
        errorDiv.classList.remove('hidden');
        return;
    }

    // Check if department+priority combination already exists
    const ruleKey = departmentId + '-' + priorityId;
    if (existingRules.includes(ruleKey)) {
        // When editing, find if the existing rule is the one being edited
        if (action === 'edit' && currentEditId) {
            const currentItem = document.querySelector(`.sla-rule-item[data-id="${currentEditId}"]`);
            if (currentItem) {
                const currentDeptId = currentItem.dataset.department;
                const currentPriorityId = currentItem.dataset.priority;
                const currentKey = currentDeptId + '-' + currentPriorityId;
                // Allow if it's the same combination being edited
                if (ruleKey === currentKey) {
                    // Continue - this is the same rule
                } else {
                    errorDiv.textContent = 'This department already has an SLA rule for this priority.';
                    errorDiv.classList.remove('hidden');
                    return;
                }
            }
        } else {
            errorDiv.textContent = 'This department already has an SLA rule for this priority. Please edit the existing rule.';
            errorDiv.classList.remove('hidden');
            return;
        }
    }

    if (!hours || hours < 1) {
        errorDiv.textContent = 'Resolution hours is required.';
        errorDiv.classList.remove('hidden');
        return;
    }

    const formData = new FormData();
    formData.append('_token', csrfToken);
    formData.append('action', action);
    formData.append('department_id', departmentId);
    formData.append('priority_id', priorityId);
    formData.append('resolution_hours', hours);
    formData.append('assigned_user_id', assigneeId);
    formData.append('escalation_notes', notes);
    if (action === 'edit') {
        formData.append('edit_id', editId);
    }

    try {
        const response = await fetch(slaRulesEndpoint, {
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
            showToast(data.message || 'SLA rule saved successfully.', 'success');
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

function confirmDeleteSlaRule(ruleId, name) {
    deleteSlaRuleId = ruleId;
    document.getElementById('delete-sla-rule-name').textContent = name;
    document.getElementById('delete-sla-rule-modal').classList.remove('hidden');
}

function closeDeleteSlaRuleModal() {
    document.getElementById('delete-sla-rule-modal').classList.add('hidden');
    deleteSlaRuleId = null;
}

async function deleteSlaRule() {
    if (deleteSlaRuleId === null) return;

    const formData = new FormData();
    formData.append('_token', csrfToken);
    formData.append('action', 'delete');
    formData.append('delete_id', deleteSlaRuleId);

    try {
        const response = await fetch(slaRulesEndpoint, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            }
        });

        const data = await response.json();

        if (data.success) {
            const item = document.querySelector(`.sla-rule-item[data-id="${deleteSlaRuleId}"]`);
            if (item) item.remove();

            const list = document.getElementById('sla-rules-list');
            const noMsg = document.getElementById('no-sla-rules-msg');
            const countBadge = document.getElementById('sla-rules-count');
            const count = list ? list.querySelectorAll('.sla-rule-item').length : 0;

            if (countBadge) countBadge.textContent = count;

            if (count === 0) {
                list?.classList.add('hidden');
                noMsg?.classList.remove('hidden');
            }

            showToast(data.message || 'SLA rule deleted successfully.', 'success');
        } else {
            showToast(data.message || 'An error occurred.', 'error');
        }
    } catch (error) {
        showToast('An error occurred. Please try again.', 'error');
    }

    closeDeleteSlaRuleModal();
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
        const deleteModal = document.getElementById('delete-sla-rule-modal');
        if (deleteModal && !deleteModal.classList.contains('hidden')) {
            closeDeleteSlaRuleModal();
        }
    }
});
</script>
@endsection
