{{-- SLA Rules Drawer (Assign SLA) --}}
@php
    $departments = $workspace->departments()->orderBy('sort_order')->get();
    $slaRules = $workspace->slaRules()->with('department')->orderBy('sort_order')->get();

    // Define available statuses
    $statuses = [
        'open' => 'Open',
        'pending' => 'Pending',
        'in_progress' => 'In Progress',
        'waiting_customer' => 'Waiting on Customer',
        'resolved' => 'Resolved',
        'closed' => 'Closed',
    ];
@endphp

<div id="sla-rules-drawer" class="fixed inset-0 z-50 hidden">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-black/50 transition-opacity" onclick="closeSlaRulesDrawer()"></div>

    <!-- Drawer Panel -->
    <div class="absolute right-0 top-0 h-full w-full max-w-2xl bg-base-100 shadow-xl transform translate-x-full transition-transform duration-300" id="sla-rules-drawer-panel">
        <!-- Drawer Header -->
        <div class="flex items-center justify-between p-4 border-b border-base-200">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-accent/10 flex items-center justify-center">
                    <span class="icon-[tabler--clock-exclamation] size-5 text-accent"></span>
                </div>
                <div>
                    <h3 class="font-semibold text-lg">SLA Rules</h3>
                    <p class="text-sm text-base-content/60">Configure SLA escalation rules by department and status</p>
                </div>
            </div>
            <button type="button" class="btn btn-ghost btn-sm btn-square" onclick="closeSlaRulesDrawer()">
                <span class="icon-[tabler--x] size-5"></span>
            </button>
        </div>

        <!-- Drawer Content -->
        <div class="overflow-y-auto h-[calc(100vh-160px)] p-4 space-y-6">

            <!-- Add SLA Rule Form -->
            <div class="card bg-base-200">
                <div class="card-body p-4">
                    <h4 class="font-medium text-sm mb-3 flex items-center gap-2" id="sla-rule-form-title">
                        <span class="icon-[tabler--plus] size-4" id="sla-rule-form-icon"></span>
                        <span id="sla-rule-form-title-text">Add SLA Rule</span>
                    </h4>
                    <form id="sla-rule-form" onsubmit="submitSlaRuleForm(event)">
                        <input type="hidden" name="action" id="sla-rule-action" value="add">
                        <input type="hidden" name="edit_id" id="sla-rule-edit-id" value="">
                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <!-- Department Selection -->
                                <div class="form-control">
                                    <label class="label">
                                        <span class="label-text font-medium">Select Department</span>
                                    </label>
                                    <select name="department_id" id="sla-rule-department" class="select select-bordered select-sm">
                                        <option value="">All Departments</option>
                                        @foreach($departments as $dept)
                                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Status Selection -->
                                <div class="form-control">
                                    <label class="label">
                                        <span class="label-text font-medium">Select Status</span>
                                    </label>
                                    <select name="status" id="sla-rule-status" class="select select-bordered select-sm">
                                        <option value="">All Statuses</option>
                                        @foreach($statuses as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- Resolution Hours -->
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium">Resolution Hours <span class="text-error">*</span></span>
                                </label>
                                <input type="number" name="resolution_hours" id="sla-rule-resolution-hours" class="input input-bordered input-sm" value="24" min="1" max="720" required>
                                <label class="label">
                                    <span class="label-text-alt text-base-content/50">Hours until ticket escalation (max 30 days = 720 hours)</span>
                                </label>
                            </div>

                            <!-- Escalation Notes -->
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium">Escalation Notes</span>
                                </label>
                                <textarea name="escalation_notes" id="sla-rule-escalation-notes" class="textarea textarea-bordered textarea-sm" rows="3" placeholder="Notes to include when this SLA is breached..."></textarea>
                            </div>

                            <!-- Submit Buttons -->
                            <div class="flex gap-2">
                                <button type="submit" class="btn btn-primary btn-sm flex-1 gap-2" id="sla-rule-submit-btn">
                                    <span class="icon-[tabler--plus] size-4" id="sla-rule-submit-icon"></span>
                                    <span id="sla-rule-submit-text">Add Rule</span>
                                </button>
                                <button type="button" class="btn btn-ghost btn-sm hidden" id="sla-rule-cancel-edit-btn" onclick="cancelEditSlaRule()">
                                    Cancel
                                </button>
                            </div>
                            <div id="sla-rule-form-error" class="text-error text-xs hidden"></div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Existing SLA Rules List -->
            <div>
                <h4 class="font-medium text-sm mb-3 flex items-center gap-2">
                    <span class="icon-[tabler--list] size-4"></span>
                    SLA Rules
                    @if($slaRules->count() > 0)
                        <span class="badge badge-ghost badge-sm">{{ $slaRules->count() }}</span>
                    @endif
                </h4>

                @if($slaRules->count() > 0)
                    <div class="space-y-2" id="sla-rules-list">
                        @foreach($slaRules as $rule)
                            <div class="flex items-center justify-between p-3 bg-base-200 rounded-lg group sla-rule-item"
                                 data-id="{{ $rule->id }}"
                                 data-department-id="{{ $rule->department_id }}"
                                 data-status="{{ $rule->status }}"
                                 data-resolution-hours="{{ $rule->resolution_hours }}"
                                 data-escalation-notes="{{ $rule->escalation_notes }}">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg bg-accent/20 flex items-center justify-center">
                                        <span class="icon-[tabler--clock-exclamation] size-5 text-accent"></span>
                                    </div>
                                    <div>
                                        <p class="font-medium text-sm">
                                            {{ $rule->department?->name ?? 'All Departments' }}
                                            @if($rule->status)
                                                <span class="text-base-content/50">-</span>
                                                <span class="badge badge-ghost badge-xs">{{ $statuses[$rule->status] ?? $rule->status }}</span>
                                            @endif
                                        </p>
                                        <div class="flex items-center gap-2 text-xs text-base-content/50">
                                            <span class="flex items-center gap-1">
                                                <span class="icon-[tabler--clock] size-3"></span>
                                                {{ $rule->getFormattedResolutionTime() }}
                                            </span>
                                            @if($rule->escalation_notes)
                                                <span class="flex items-center gap-1" title="{{ $rule->escalation_notes }}">
                                                    <span class="icon-[tabler--note] size-3"></span>
                                                    Has notes
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button type="button" class="btn btn-ghost btn-xs btn-square" onclick="editSlaRule({{ $rule->id }})" title="Edit">
                                        <span class="icon-[tabler--edit] size-4"></span>
                                    </button>
                                    <button type="button" class="btn btn-ghost btn-xs btn-square text-error" onclick="confirmDeleteSlaRule({{ $rule->id }})" title="Delete">
                                        <span class="icon-[tabler--trash] size-4"></span>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8 text-base-content/50" id="no-sla-rules-msg">
                        <span class="icon-[tabler--clock-off] size-12 mb-2 opacity-50"></span>
                        <p class="text-sm">No SLA rules configured</p>
                        <p class="text-xs">Add a rule to set up escalation criteria</p>
                    </div>
                @endif
            </div>

            <!-- Info Alert -->
            <div class="alert alert-info">
                <span class="icon-[tabler--info-circle] size-5"></span>
                <span class="text-sm">SLA rules define when tickets should be escalated based on department, status, and time. Escalation notes are included in notifications when the SLA is breached.</span>
            </div>
        </div>

        <!-- Drawer Footer -->
        <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-base-200 bg-base-100">
            <div class="flex gap-3">
                <button type="button" class="btn btn-primary flex-1" onclick="closeSlaRulesDrawer()">
                    <span class="icon-[tabler--check] size-5"></span>
                    Done
                </button>
                <button type="button" class="btn btn-ghost flex-1" onclick="closeSlaRulesDrawer()">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete SLA Rule Confirmation Modal -->
<div id="delete-sla-rule-modal" class="fixed inset-0 z-[60] hidden">
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
                Are you sure you want to delete this SLA rule?
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
const slaRuleCsrfToken = '{{ csrf_token() }}';
let deleteSlaRuleId = null;

function openSlaRulesDrawer() {
    const drawer = document.getElementById('sla-rules-drawer');
    const panel = document.getElementById('sla-rules-drawer-panel');

    drawer.classList.remove('hidden');
    document.body.style.overflow = 'hidden';

    setTimeout(() => {
        panel.classList.remove('translate-x-full');
    }, 10);
}

function closeSlaRulesDrawer() {
    const drawer = document.getElementById('sla-rules-drawer');
    const panel = document.getElementById('sla-rules-drawer-panel');

    panel.classList.add('translate-x-full');

    setTimeout(() => {
        drawer.classList.add('hidden');
        document.body.style.overflow = '';
    }, 300);

    resetSlaRuleForm();
}

function resetSlaRuleForm() {
    const form = document.getElementById('sla-rule-form');
    form.reset();

    document.getElementById('sla-rule-action').value = 'add';
    document.getElementById('sla-rule-edit-id').value = '';
    document.getElementById('sla-rule-resolution-hours').value = '24';
    document.getElementById('sla-rule-form-icon').className = 'icon-[tabler--plus] size-4';
    document.getElementById('sla-rule-form-title-text').textContent = 'Add SLA Rule';
    document.getElementById('sla-rule-submit-icon').className = 'icon-[tabler--plus] size-4';
    document.getElementById('sla-rule-submit-text').textContent = 'Add Rule';
    document.getElementById('sla-rule-cancel-edit-btn').classList.add('hidden');
    document.getElementById('sla-rule-submit-btn').classList.remove('btn-success');
    document.getElementById('sla-rule-submit-btn').classList.add('btn-primary');
    document.getElementById('sla-rule-form-error').classList.add('hidden');
}

function editSlaRule(ruleId) {
    const item = document.querySelector(`.sla-rule-item[data-id="${ruleId}"]`);
    if (!item) return;

    const departmentId = item.dataset.departmentId;
    const status = item.dataset.status;
    const resolutionHours = item.dataset.resolutionHours;
    const escalationNotes = item.dataset.escalationNotes;

    document.getElementById('sla-rule-action').value = 'edit';
    document.getElementById('sla-rule-edit-id').value = ruleId;
    document.getElementById('sla-rule-department').value = departmentId || '';
    document.getElementById('sla-rule-status').value = status || '';
    document.getElementById('sla-rule-resolution-hours').value = resolutionHours;
    document.getElementById('sla-rule-escalation-notes').value = escalationNotes || '';
    document.getElementById('sla-rule-form-icon').className = 'icon-[tabler--edit] size-4';
    document.getElementById('sla-rule-form-title-text').textContent = 'Edit SLA Rule';
    document.getElementById('sla-rule-submit-icon').className = 'icon-[tabler--check] size-4';
    document.getElementById('sla-rule-submit-text').textContent = 'Save Changes';
    document.getElementById('sla-rule-cancel-edit-btn').classList.remove('hidden');
    document.getElementById('sla-rule-submit-btn').classList.remove('btn-primary');
    document.getElementById('sla-rule-submit-btn').classList.add('btn-success');

    document.querySelector('.card.bg-base-200').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function cancelEditSlaRule() {
    resetSlaRuleForm();
}

async function submitSlaRuleForm(event) {
    event.preventDefault();

    const action = document.getElementById('sla-rule-action').value;
    const departmentId = document.getElementById('sla-rule-department').value;
    const status = document.getElementById('sla-rule-status').value;
    const resolutionHours = document.getElementById('sla-rule-resolution-hours').value;
    const escalationNotes = document.getElementById('sla-rule-escalation-notes').value;
    const editId = document.getElementById('sla-rule-edit-id').value;
    const errorDiv = document.getElementById('sla-rule-form-error');

    if (!resolutionHours || resolutionHours < 1) {
        errorDiv.textContent = 'Please enter valid resolution hours.';
        errorDiv.classList.remove('hidden');
        return;
    }

    const formData = new FormData();
    formData.append('_token', slaRuleCsrfToken);
    formData.append('action', action);
    formData.append('department_id', departmentId);
    formData.append('status', status);
    formData.append('resolution_hours', resolutionHours);
    formData.append('escalation_notes', escalationNotes);
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
            showSlaRuleToast(data.message || 'Rule saved successfully.', 'success');
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

function confirmDeleteSlaRule(ruleId) {
    deleteSlaRuleId = ruleId;
    document.getElementById('delete-sla-rule-modal').classList.remove('hidden');
}

function closeDeleteSlaRuleModal() {
    document.getElementById('delete-sla-rule-modal').classList.add('hidden');
    deleteSlaRuleId = null;
}

async function deleteSlaRule() {
    if (deleteSlaRuleId === null) return;

    const formData = new FormData();
    formData.append('_token', slaRuleCsrfToken);
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
            showSlaRuleToast(data.message || 'Rule deleted successfully.', 'success');
            window.location.reload();
        } else {
            showSlaRuleToast(data.message || 'An error occurred.', 'error');
        }
    } catch (error) {
        showSlaRuleToast('An error occurred. Please try again.', 'error');
    }

    closeDeleteSlaRuleModal();
}

function showSlaRuleToast(message, type = 'success') {
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
            return;
        }

        const drawer = document.getElementById('sla-rules-drawer');
        if (drawer && !drawer.classList.contains('hidden')) {
            closeSlaRulesDrawer();
        }
    }
});
</script>
