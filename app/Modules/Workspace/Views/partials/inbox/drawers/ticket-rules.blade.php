{{-- Ticket Rules Drawer (Assign Ticket Level Rule) --}}
@php
    $departments = $workspace->departments()->orderBy('sort_order')->get();
    $ticketRules = $workspace->ticketRules()->with(['department', 'assignedUser', 'backupUser'])->orderBy('sort_order')->get();
    $members = $workspace->members;
@endphp

<div id="ticket-rules-drawer" class="fixed inset-0 z-50 hidden">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-black/50 transition-opacity" onclick="closeTicketRulesDrawer()"></div>

    <!-- Drawer Panel -->
    <div class="absolute right-0 top-0 h-full w-full max-w-2xl bg-base-100 shadow-xl transform translate-x-full transition-transform duration-300" id="ticket-rules-drawer-panel">
        <!-- Drawer Header -->
        <div class="flex items-center justify-between p-4 border-b border-base-200">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-success/10 flex items-center justify-center">
                    <span class="icon-[tabler--route] size-5 text-success"></span>
                </div>
                <div>
                    <h3 class="font-semibold text-lg">Ticket Assignment Rules</h3>
                    <p class="text-sm text-base-content/60">Auto-assign tickets based on department</p>
                </div>
            </div>
            <button type="button" class="btn btn-ghost btn-sm btn-square" onclick="closeTicketRulesDrawer()">
                <span class="icon-[tabler--x] size-5"></span>
            </button>
        </div>

        <!-- Drawer Content -->
        <div class="overflow-y-auto h-[calc(100vh-160px)] p-4 space-y-6">

            <!-- Add Rule Form -->
            <div class="card bg-base-200">
                <div class="card-body p-4">
                    <h4 class="font-medium text-sm mb-3 flex items-center gap-2" id="ticket-rule-form-title">
                        <span class="icon-[tabler--plus] size-4" id="ticket-rule-form-icon"></span>
                        <span id="ticket-rule-form-title-text">Add Assignment Rule</span>
                    </h4>
                    <form id="ticket-rule-form" onsubmit="submitTicketRuleForm(event)">
                        <input type="hidden" name="action" id="ticket-rule-action" value="add">
                        <input type="hidden" name="edit_id" id="ticket-rule-edit-id" value="">
                        <div class="space-y-4">
                            <!-- Department Selection -->
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium">Select Department <span class="text-error">*</span></span>
                                </label>
                                <select name="department_id" id="ticket-rule-department" class="select select-bordered select-sm" required>
                                    <option value="">Choose a department...</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <!-- Assigned User -->
                                <div class="form-control">
                                    <label class="label">
                                        <span class="label-text font-medium">Assign To</span>
                                    </label>
                                    <select name="assigned_user_id" id="ticket-rule-user" class="select select-bordered select-sm">
                                        <option value="">No auto-assignment</option>
                                        @foreach($members as $member)
                                            <option value="{{ $member->id }}">{{ $member->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Backup User -->
                                <div class="form-control">
                                    <label class="label">
                                        <span class="label-text font-medium">Backup User</span>
                                    </label>
                                    <select name="backup_user_id" id="ticket-rule-backup" class="select select-bordered select-sm">
                                        <option value="">No backup</option>
                                        @foreach($members as $member)
                                            <option value="{{ $member->id }}">{{ $member->name }}</option>
                                        @endforeach
                                    </select>
                                    <label class="label">
                                        <span class="label-text-alt text-base-content/50">Used when primary is unavailable</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Submit Buttons -->
                            <div class="flex gap-2">
                                <button type="submit" class="btn btn-primary btn-sm flex-1 gap-2" id="ticket-rule-submit-btn">
                                    <span class="icon-[tabler--plus] size-4" id="ticket-rule-submit-icon"></span>
                                    <span id="ticket-rule-submit-text">Add Rule</span>
                                </button>
                                <button type="button" class="btn btn-ghost btn-sm hidden" id="ticket-rule-cancel-edit-btn" onclick="cancelEditTicketRule()">
                                    Cancel
                                </button>
                            </div>
                            <div id="ticket-rule-form-error" class="text-error text-xs hidden"></div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Existing Rules List -->
            <div>
                <h4 class="font-medium text-sm mb-3 flex items-center gap-2">
                    <span class="icon-[tabler--list] size-4"></span>
                    Assignment Rules
                    @if($ticketRules->count() > 0)
                        <span class="badge badge-ghost badge-sm">{{ $ticketRules->count() }}</span>
                    @endif
                </h4>

                @if($ticketRules->count() > 0)
                    <div class="space-y-2" id="ticket-rules-list">
                        @foreach($ticketRules as $rule)
                            <div class="flex items-center justify-between p-3 bg-base-200 rounded-lg group ticket-rule-item"
                                 data-id="{{ $rule->id }}"
                                 data-department-id="{{ $rule->department_id }}"
                                 data-user-id="{{ $rule->assigned_user_id }}"
                                 data-backup-id="{{ $rule->backup_user_id }}">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg bg-secondary/20 flex items-center justify-center">
                                        <span class="icon-[tabler--building] size-5 text-secondary"></span>
                                    </div>
                                    <div>
                                        <p class="font-medium text-sm">{{ $rule->department?->name ?? 'Unknown Department' }}</p>
                                        <div class="flex items-center gap-2 text-xs text-base-content/50">
                                            @if($rule->assignedUser)
                                                <span class="flex items-center gap-1">
                                                    <span class="icon-[tabler--user] size-3"></span>
                                                    {{ $rule->assignedUser->name }}
                                                </span>
                                            @else
                                                <span class="text-base-content/40">No assignment</span>
                                            @endif
                                            @if($rule->backupUser)
                                                <span class="flex items-center gap-1 text-warning">
                                                    <span class="icon-[tabler--user-shield] size-3"></span>
                                                    {{ $rule->backupUser->name }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button type="button" class="btn btn-ghost btn-xs btn-square" onclick="editTicketRule({{ $rule->id }})" title="Edit">
                                        <span class="icon-[tabler--edit] size-4"></span>
                                    </button>
                                    <button type="button" class="btn btn-ghost btn-xs btn-square text-error" onclick="confirmDeleteTicketRule({{ $rule->id }}, '{{ addslashes($rule->department?->name ?? 'Unknown') }}')" title="Delete">
                                        <span class="icon-[tabler--trash] size-4"></span>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8 text-base-content/50" id="no-ticket-rules-msg">
                        <span class="icon-[tabler--route-off] size-12 mb-2 opacity-50"></span>
                        <p class="text-sm">No assignment rules configured</p>
                        <p class="text-xs">Add a rule to auto-assign tickets</p>
                    </div>
                @endif
            </div>

            <!-- Info Alert -->
            <div class="alert alert-info">
                <span class="icon-[tabler--info-circle] size-5"></span>
                <span class="text-sm">Assignment rules automatically route tickets to specific team members based on the selected department. Backup users receive tickets when the primary assignee is unavailable.</span>
            </div>
        </div>

        <!-- Drawer Footer -->
        <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-base-200 bg-base-100">
            <div class="flex gap-3">
                <button type="button" class="btn btn-primary flex-1" onclick="closeTicketRulesDrawer()">
                    <span class="icon-[tabler--check] size-5"></span>
                    Done
                </button>
                <button type="button" class="btn btn-ghost flex-1" onclick="closeTicketRulesDrawer()">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Ticket Rule Confirmation Modal -->
<div id="delete-ticket-rule-modal" class="fixed inset-0 z-[60] hidden">
    <div class="absolute inset-0 bg-black/50" onclick="closeDeleteTicketRuleModal()"></div>
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
                Are you sure you want to delete the assignment rule for "<span id="delete-ticket-rule-name" class="font-semibold"></span>"?
            </p>
            <div class="flex justify-end gap-2">
                <button type="button" class="btn btn-ghost" onclick="closeDeleteTicketRuleModal()">Cancel</button>
                <button type="button" class="btn btn-error gap-2" onclick="deleteTicketRule()" id="confirm-delete-ticket-rule-btn">
                    <span class="icon-[tabler--trash] size-5"></span>
                    Delete Rule
                </button>
            </div>
        </div>
    </div>
</div>

<script>
const ticketRulesEndpoint = '{{ route('workspace.save-ticket-rules', $workspace) }}';
const ticketRuleCsrfToken = '{{ csrf_token() }}';
let deleteTicketRuleId = null;

function openTicketRulesDrawer() {
    const drawer = document.getElementById('ticket-rules-drawer');
    const panel = document.getElementById('ticket-rules-drawer-panel');

    drawer.classList.remove('hidden');
    document.body.style.overflow = 'hidden';

    setTimeout(() => {
        panel.classList.remove('translate-x-full');
    }, 10);
}

function closeTicketRulesDrawer() {
    const drawer = document.getElementById('ticket-rules-drawer');
    const panel = document.getElementById('ticket-rules-drawer-panel');

    panel.classList.add('translate-x-full');

    setTimeout(() => {
        drawer.classList.add('hidden');
        document.body.style.overflow = '';
    }, 300);

    resetTicketRuleForm();
}

function resetTicketRuleForm() {
    const form = document.getElementById('ticket-rule-form');
    form.reset();

    document.getElementById('ticket-rule-action').value = 'add';
    document.getElementById('ticket-rule-edit-id').value = '';
    document.getElementById('ticket-rule-form-icon').className = 'icon-[tabler--plus] size-4';
    document.getElementById('ticket-rule-form-title-text').textContent = 'Add Assignment Rule';
    document.getElementById('ticket-rule-submit-icon').className = 'icon-[tabler--plus] size-4';
    document.getElementById('ticket-rule-submit-text').textContent = 'Add Rule';
    document.getElementById('ticket-rule-cancel-edit-btn').classList.add('hidden');
    document.getElementById('ticket-rule-submit-btn').classList.remove('btn-success');
    document.getElementById('ticket-rule-submit-btn').classList.add('btn-primary');
    document.getElementById('ticket-rule-form-error').classList.add('hidden');
}

function editTicketRule(ruleId) {
    const item = document.querySelector(`.ticket-rule-item[data-id="${ruleId}"]`);
    if (!item) return;

    const departmentId = item.dataset.departmentId;
    const userId = item.dataset.userId;
    const backupId = item.dataset.backupId;

    document.getElementById('ticket-rule-action').value = 'edit';
    document.getElementById('ticket-rule-edit-id').value = ruleId;
    document.getElementById('ticket-rule-department').value = departmentId;
    document.getElementById('ticket-rule-user').value = userId || '';
    document.getElementById('ticket-rule-backup').value = backupId || '';
    document.getElementById('ticket-rule-form-icon').className = 'icon-[tabler--edit] size-4';
    document.getElementById('ticket-rule-form-title-text').textContent = 'Edit Assignment Rule';
    document.getElementById('ticket-rule-submit-icon').className = 'icon-[tabler--check] size-4';
    document.getElementById('ticket-rule-submit-text').textContent = 'Save Changes';
    document.getElementById('ticket-rule-cancel-edit-btn').classList.remove('hidden');
    document.getElementById('ticket-rule-submit-btn').classList.remove('btn-primary');
    document.getElementById('ticket-rule-submit-btn').classList.add('btn-success');

    document.querySelector('.card.bg-base-200').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function cancelEditTicketRule() {
    resetTicketRuleForm();
}

async function submitTicketRuleForm(event) {
    event.preventDefault();

    const action = document.getElementById('ticket-rule-action').value;
    const departmentId = document.getElementById('ticket-rule-department').value;
    const userId = document.getElementById('ticket-rule-user').value;
    const backupId = document.getElementById('ticket-rule-backup').value;
    const editId = document.getElementById('ticket-rule-edit-id').value;
    const errorDiv = document.getElementById('ticket-rule-form-error');

    if (!departmentId) {
        errorDiv.textContent = 'Please select a department.';
        errorDiv.classList.remove('hidden');
        return;
    }

    const formData = new FormData();
    formData.append('_token', ticketRuleCsrfToken);
    formData.append('action', action);
    formData.append('department_id', departmentId);
    formData.append('assigned_user_id', userId);
    formData.append('backup_user_id', backupId);
    if (action === 'edit') {
        formData.append('edit_id', editId);
    }

    try {
        const response = await fetch(ticketRulesEndpoint, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            }
        });

        const data = await response.json();

        if (data.success) {
            showTicketRuleToast(data.message || 'Rule saved successfully.', 'success');
            window.location.reload();
        } else {
            errorDiv.textContent = data.message || 'An error occurred.';
            errorDiv.classList.remove('hidden');
        }
    } catch (error) {
        errorDiv.textContent = 'An error occurred. Please try again.';
        errorDiv.classList.remove('hidden');
    }
}

function confirmDeleteTicketRule(ruleId, deptName) {
    deleteTicketRuleId = ruleId;
    document.getElementById('delete-ticket-rule-name').textContent = deptName;
    document.getElementById('delete-ticket-rule-modal').classList.remove('hidden');
}

function closeDeleteTicketRuleModal() {
    document.getElementById('delete-ticket-rule-modal').classList.add('hidden');
    deleteTicketRuleId = null;
}

async function deleteTicketRule() {
    if (deleteTicketRuleId === null) return;

    const formData = new FormData();
    formData.append('_token', ticketRuleCsrfToken);
    formData.append('action', 'delete');
    formData.append('delete_id', deleteTicketRuleId);

    try {
        const response = await fetch(ticketRulesEndpoint, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            }
        });

        const data = await response.json();

        if (data.success) {
            showTicketRuleToast(data.message || 'Rule deleted successfully.', 'success');
            window.location.reload();
        } else {
            showTicketRuleToast(data.message || 'An error occurred.', 'error');
        }
    } catch (error) {
        showTicketRuleToast('An error occurred. Please try again.', 'error');
    }

    closeDeleteTicketRuleModal();
}

function showTicketRuleToast(message, type = 'success') {
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
        const deleteModal = document.getElementById('delete-ticket-rule-modal');
        if (deleteModal && !deleteModal.classList.contains('hidden')) {
            closeDeleteTicketRuleModal();
            return;
        }

        const drawer = document.getElementById('ticket-rules-drawer');
        if (drawer && !drawer.classList.contains('hidden')) {
            closeTicketRulesDrawer();
        }
    }
});
</script>
