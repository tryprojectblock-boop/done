{{-- Departments Drawer --}}
@php
    $departments = $workspace->departments()->with('incharge')->orderBy('sort_order')->get();
@endphp

<div id="departments-drawer" class="fixed inset-0 z-50 hidden">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-black/50 transition-opacity" onclick="closeDepartmentsDrawer()"></div>

    <!-- Drawer Panel -->
    <div class="absolute right-0 top-0 h-full w-full max-w-xl bg-base-100 shadow-xl transform translate-x-full transition-transform duration-300" id="departments-drawer-panel">
        <!-- Drawer Header -->
        <div class="flex items-center justify-between p-4 border-b border-base-200">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-secondary/10 flex items-center justify-center">
                    <span class="icon-[tabler--building] size-5 text-secondary"></span>
                </div>
                <div>
                    <h3 class="font-semibold text-lg">Departments</h3>
                    <p class="text-sm text-base-content/60">Organize and route tickets by department</p>
                </div>
            </div>
            <button type="button" class="btn btn-ghost btn-sm btn-square" onclick="closeDepartmentsDrawer()">
                <span class="icon-[tabler--x] size-5"></span>
            </button>
        </div>

        <!-- Drawer Content -->
        <div class="overflow-y-auto h-[calc(100vh-160px)] p-4 space-y-6">

            <!-- Add/Edit Department Form -->
            <div class="card bg-base-200">
                <div class="card-body p-4">
                    <h4 class="font-medium text-sm mb-3 flex items-center gap-2" id="dept-form-title">
                        <span class="icon-[tabler--plus] size-4" id="dept-form-icon"></span>
                        <span id="dept-form-title-text">Add New Department</span>
                    </h4>
                    <form id="department-form" onsubmit="submitDepartmentForm(event)">
                        <input type="hidden" name="action" id="dept-action" value="add">
                        <input type="hidden" name="edit_id" id="dept-edit-id" value="">
                        <div class="space-y-4">
                            <!-- Department Name -->
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium">Department Name <span class="text-error">*</span></span>
                                </label>
                                <input type="text" name="name" id="dept-name" class="input input-bordered input-sm" placeholder="e.g., Sales, Support, Billing" required>
                            </div>

                            <!-- Public View -->
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium">Public View</span>
                                    <span class="label-text-alt text-base-content/50">Visible to customers?</span>
                                </label>
                                <div class="flex gap-4">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" name="public_view" value="1" class="radio radio-primary radio-sm" id="dept-public-yes" checked>
                                        <span class="label-text">Yes</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" name="public_view" value="0" class="radio radio-primary radio-sm" id="dept-public-no">
                                        <span class="label-text">No</span>
                                    </label>
                                </div>
                                <label class="label">
                                    <span class="label-text-alt text-base-content/50">Public departments appear in customer-facing forms</span>
                                </label>
                            </div>

                            <!-- Department Incharge (Searchable Dropdown) -->
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium">Department Incharge</span>
                                </label>
                                <input type="hidden" name="incharge_id" id="dept-incharge-id" value="">
                                <div class="relative" id="dept-incharge-dropdown">
                                    <!-- Selected Display / Search Input -->
                                    <div class="input input-bordered input-sm flex items-center gap-2 cursor-pointer" onclick="handleDeptInchargeClick(event)">
                                        <span class="icon-[tabler--search] size-4 text-base-content/40"></span>
                                        <input type="text"
                                               id="dept-incharge-search"
                                               class="flex-1 bg-transparent border-none outline-none text-sm p-0"
                                               placeholder="Search team member..."
                                               autocomplete="off"
                                               oninput="filterDeptInchargeOptions(this.value)"
                                               onclick="event.stopPropagation(); showDeptInchargeDropdown();">
                                        <div id="dept-incharge-selected" class="hidden items-center gap-2 flex-1">
                                            <div class="avatar">
                                                <div class="w-5 h-5 rounded-full">
                                                    <img id="dept-incharge-avatar" src="" alt="">
                                                </div>
                                            </div>
                                            <span id="dept-incharge-name" class="text-sm truncate"></span>
                                        </div>
                                        <button type="button" id="dept-incharge-clear" class="hidden btn btn-ghost btn-xs btn-circle" onclick="clearDeptIncharge(event)">
                                            <span class="icon-[tabler--x] size-3"></span>
                                        </button>
                                        <span class="icon-[tabler--chevron-down] size-4 text-base-content/40"></span>
                                    </div>

                                    <!-- Dropdown Options -->
                                    <div id="dept-incharge-options" class="absolute top-full left-0 right-0 mt-1 bg-base-100 border border-base-300 rounded-lg shadow-lg z-50 max-h-48 overflow-y-auto hidden">
                                        <div class="p-1">
                                            <div class="dept-incharge-option px-3 py-2 hover:bg-base-200 rounded cursor-pointer flex items-center gap-2 text-base-content/50" data-id="" onclick="selectDeptIncharge('', '', '')">
                                                <span class="icon-[tabler--user-off] size-5"></span>
                                                <span class="text-sm">No incharge (optional)</span>
                                            </div>
                                            @foreach($workspace->members as $member)
                                            <div class="dept-incharge-option px-3 py-2 hover:bg-base-200 rounded cursor-pointer flex items-center gap-2"
                                                 data-id="{{ $member->id }}"
                                                 data-name="{{ $member->name }}"
                                                 data-avatar="{{ $member->avatar_url }}"
                                                 onclick="selectDeptIncharge('{{ $member->id }}', '{{ $member->name }}', '{{ $member->avatar_url }}')">
                                                <div class="avatar">
                                                    <div class="w-6 h-6 rounded-full">
                                                        <img src="{{ $member->avatar_url }}" alt="{{ $member->name }}">
                                                    </div>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-sm font-medium truncate">{{ $member->name }}</p>
                                                    <p class="text-xs text-base-content/50 truncate">{{ $member->email }}</p>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                        <div id="dept-incharge-no-results" class="hidden p-3 text-center text-sm text-base-content/50">
                                            No members found
                                        </div>
                                    </div>
                                </div>
                                <label class="label">
                                    <span class="label-text-alt text-base-content/50">Tickets can be auto-assigned to the incharge</span>
                                </label>
                            </div>

                            <!-- Submit Buttons -->
                            <div class="flex gap-2">
                                <button type="submit" class="btn btn-primary btn-sm flex-1 gap-2" id="dept-submit-btn">
                                    <span class="icon-[tabler--plus] size-4" id="dept-submit-icon"></span>
                                    <span id="dept-submit-text">Add Department</span>
                                </button>
                                <button type="button" class="btn btn-ghost btn-sm hidden" id="dept-cancel-edit-btn" onclick="cancelEditDepartment()">
                                    Cancel
                                </button>
                            </div>
                            <div id="dept-form-error" class="text-error text-xs mt-2 hidden"></div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Existing Departments List -->
            <div>
                <h4 class="font-medium text-sm mb-3 flex items-center gap-2">
                    <span class="icon-[tabler--list] size-4"></span>
                    Existing Departments
                    <span class="badge badge-ghost badge-sm" id="departments-count">{{ $departments->count() }}</span>
                </h4>

                <div class="space-y-2 {{ $departments->count() === 0 ? 'hidden' : '' }}" id="departments-list">
                    @foreach($departments as $department)
                            <div class="flex items-center justify-between p-3 bg-base-200 rounded-lg group dept-item"
                                 data-id="{{ $department->id }}"
                                 data-name="{{ $department->name }}"
                                 data-public="{{ $department->is_public ? '1' : '0' }}"
                                 data-incharge-id="{{ $department->incharge_id ?? '' }}"
                                 data-incharge-name="{{ $department->incharge?->name ?? '' }}"
                                 data-incharge-avatar="{{ $department->incharge?->avatar_url ?? '' }}">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-secondary/20 flex items-center justify-center">
                                        <span class="icon-[tabler--building] size-4 text-secondary"></span>
                                    </div>
                                    <div>
                                        <p class="font-medium text-sm">{{ $department->name }}</p>
                                        <div class="flex items-center gap-2 text-xs text-base-content/50">
                                            @if($department->is_public)
                                                <span class="badge badge-success badge-xs gap-1">
                                                    <span class="icon-[tabler--eye] size-3"></span>
                                                    Public
                                                </span>
                                            @else
                                                <span class="badge badge-ghost badge-xs gap-1">
                                                    <span class="icon-[tabler--eye-off] size-3"></span>
                                                    Private
                                                </span>
                                            @endif
                                            @if($department->incharge)
                                                <span class="flex items-center gap-1">
                                                    <span class="icon-[tabler--user] size-3"></span>
                                                    {{ $department->incharge->name }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button type="button" class="btn btn-ghost btn-xs btn-square" onclick="editDepartment({{ $department->id }})" title="Edit">
                                        <span class="icon-[tabler--edit] size-4"></span>
                                    </button>
                                    <button type="button" class="btn btn-ghost btn-xs btn-square text-error" onclick="confirmDeleteDepartment({{ $department->id }}, '{{ addslashes($department->name) }}')" title="Delete">
                                        <span class="icon-[tabler--trash] size-4"></span>
                                    </button>
                                </div>
                            </div>
                    @endforeach
                </div>
                <div class="text-center py-8 text-base-content/50 {{ $departments->count() > 0 ? 'hidden' : '' }}" id="no-departments-msg">
                    <span class="icon-[tabler--building-community] size-12 mb-2 opacity-50"></span>
                    <p class="text-sm">No departments added yet</p>
                    <p class="text-xs">Add your first department above</p>
                </div>
            </div>

            <!-- Info Alert -->
            <div class="alert alert-info">
                <span class="icon-[tabler--info-circle] size-5"></span>
                <span class="text-sm">Departments help organize tickets and enable automatic routing based on ticket category or customer selection.</span>
            </div>
        </div>

        <!-- Drawer Footer -->
        <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-base-200 bg-base-100">
            <div class="flex gap-3">
                <button type="button" class="btn btn-primary flex-1" onclick="closeDepartmentsDrawer()">
                    <span class="icon-[tabler--check] size-5"></span>
                    Done
                </button>
                <button type="button" class="btn btn-ghost flex-1" onclick="closeDepartmentsDrawer()">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Department Confirmation Modal -->
<div id="delete-dept-modal" class="fixed inset-0 z-[60] hidden">
    <div class="absolute inset-0 bg-black/50" onclick="closeDeleteDeptModal()"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="bg-base-100 rounded-xl shadow-xl max-w-md w-full p-6 relative z-10">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-12 h-12 rounded-full bg-error/20 flex items-center justify-center">
                    <span class="icon-[tabler--trash] size-6 text-error"></span>
                </div>
                <div>
                    <h3 class="text-lg font-bold">Delete Department</h3>
                    <p class="text-sm text-base-content/60">This action cannot be undone</p>
                </div>
            </div>
            <p class="text-base-content/70 mb-4">
                Are you sure you want to delete the department "<span id="delete-dept-name" class="font-semibold"></span>"?
                Any tickets assigned to this department will need to be reassigned.
            </p>
            <div class="flex justify-end gap-2">
                <button type="button" class="btn btn-ghost" onclick="closeDeleteDeptModal()">Cancel</button>
                <button type="button" class="btn btn-error gap-2" onclick="deleteDepartment()">
                    <span class="icon-[tabler--trash] size-5"></span>
                    Delete Department
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Store workspace UUID and endpoints
const workspaceUuid = '{{ $workspace->uuid }}';
const departmentsEndpoint = '{{ route('workspace.add-department', $workspace) }}';
const deptCsrfToken = '{{ csrf_token() }}';
let deleteDeptId = null;

function openDepartmentsDrawer() {
    const drawer = document.getElementById('departments-drawer');
    const panel = document.getElementById('departments-drawer-panel');

    drawer.classList.remove('hidden');
    document.body.style.overflow = 'hidden';

    setTimeout(() => {
        panel.classList.remove('translate-x-full');
    }, 10);
}

function closeDepartmentsDrawer() {
    const drawer = document.getElementById('departments-drawer');
    const panel = document.getElementById('departments-drawer-panel');

    panel.classList.add('translate-x-full');

    setTimeout(() => {
        drawer.classList.add('hidden');
        document.body.style.overflow = '';
    }, 300);

    // Reset form to add mode
    resetDepartmentForm();
}

function resetDepartmentForm() {
    const form = document.getElementById('department-form');
    form.reset();

    document.getElementById('dept-action').value = 'add';
    document.getElementById('dept-edit-id').value = '';
    document.getElementById('dept-form-icon').className = 'icon-[tabler--plus] size-4';
    document.getElementById('dept-form-title-text').textContent = 'Add New Department';
    document.getElementById('dept-submit-icon').className = 'icon-[tabler--plus] size-4';
    document.getElementById('dept-submit-text').textContent = 'Add Department';
    document.getElementById('dept-cancel-edit-btn').classList.add('hidden');
    document.getElementById('dept-submit-btn').classList.remove('btn-success');
    document.getElementById('dept-submit-btn').classList.add('btn-primary');
    document.getElementById('dept-form-error').classList.add('hidden');

    // Reset incharge dropdown
    clearDeptIncharge();

    // Reset public view to Yes
    document.getElementById('dept-public-yes').checked = true;
}

async function submitDepartmentForm(event) {
    event.preventDefault();

    const action = document.getElementById('dept-action').value;
    const name = document.getElementById('dept-name').value.trim();
    const publicView = document.querySelector('input[name="public_view"]:checked')?.value || '1';
    const inchargeId = document.getElementById('dept-incharge-id').value;
    const editId = document.getElementById('dept-edit-id').value;
    const errorDiv = document.getElementById('dept-form-error');

    if (!name) {
        errorDiv.textContent = 'Department name is required.';
        errorDiv.classList.remove('hidden');
        return;
    }

    const formData = new FormData();
    formData.append('_token', deptCsrfToken);
    formData.append('action', action);
    formData.append('name', name);
    formData.append('public_view', publicView);
    formData.append('incharge_id', inchargeId);
    if (action === 'edit') {
        formData.append('edit_id', editId);
    }

    try {
        const response = await fetch(departmentsEndpoint, {
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
                // Add department to list dynamically
                addDepartmentToList(data.department);
                // Reset form for next entry
                document.getElementById('dept-name').value = '';
                clearDeptIncharge();
                document.getElementById('dept-public-yes').checked = true;
                document.getElementById('dept-name').focus();
            } else if (action === 'edit') {
                // Update department in list
                updateDepartmentInList(editId, data.department);
                resetDepartmentForm();
            }
            errorDiv.classList.add('hidden');
            showDeptToast(data.message || 'Department saved successfully.', 'success');
        } else {
            errorDiv.textContent = data.message || 'An error occurred.';
            errorDiv.classList.remove('hidden');
        }
    } catch (error) {
        errorDiv.textContent = 'An error occurred. Please try again.';
        errorDiv.classList.remove('hidden');
    }
}

function addDepartmentToList(department) {
    const list = document.getElementById('departments-list');
    const noMsg = document.getElementById('no-departments-msg');

    // Hide "no departments" message and show list
    if (noMsg) {
        noMsg.classList.add('hidden');
    }
    if (list) {
        list.classList.remove('hidden');
    }

    if (!list) return;

    const item = document.createElement('div');
    item.className = 'flex items-center justify-between p-3 bg-base-200 rounded-lg group dept-item';
    item.dataset.id = department.id;
    item.dataset.name = department.name;
    item.dataset.public = department.is_public ? '1' : '0';
    item.dataset.inchargeId = department.incharge_id || '';
    item.dataset.inchargeName = department.incharge_name || '';
    item.dataset.inchargeAvatar = department.incharge_avatar || '';

    const publicBadge = department.is_public
        ? `<span class="badge badge-success badge-xs gap-1"><span class="icon-[tabler--eye] size-3"></span>Public</span>`
        : `<span class="badge badge-ghost badge-xs gap-1"><span class="icon-[tabler--eye-off] size-3"></span>Private</span>`;

    const inchargeDisplay = department.incharge_name
        ? `<span class="flex items-center gap-1"><span class="icon-[tabler--user] size-3"></span>${escapeHtmlDept(department.incharge_name)}</span>`
        : '';

    item.innerHTML = `
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-secondary/20 flex items-center justify-center">
                <span class="icon-[tabler--building] size-4 text-secondary"></span>
            </div>
            <div>
                <p class="font-medium text-sm">${escapeHtmlDept(department.name)}</p>
                <div class="flex items-center gap-2 text-xs text-base-content/50">
                    ${publicBadge}
                    ${inchargeDisplay}
                </div>
            </div>
        </div>
        <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
            <button type="button" class="btn btn-ghost btn-xs btn-square" onclick="editDepartment(${department.id})" title="Edit">
                <span class="icon-[tabler--edit] size-4"></span>
            </button>
            <button type="button" class="btn btn-ghost btn-xs btn-square text-error" onclick="confirmDeleteDepartment(${department.id}, '${escapeHtmlDept(department.name).replace(/'/g, "\\'")}')" title="Delete">
                <span class="icon-[tabler--trash] size-4"></span>
            </button>
        </div>
    `;

    list.appendChild(item);
    updateDepartmentsCount();
}

function updateDepartmentInList(id, department) {
    const item = document.querySelector(`.dept-item[data-id="${id}"]`);
    if (!item) return;

    item.dataset.name = department.name;
    item.dataset.public = department.is_public ? '1' : '0';
    item.dataset.inchargeId = department.incharge_id || '';
    item.dataset.inchargeName = department.incharge_name || '';
    item.dataset.inchargeAvatar = department.incharge_avatar || '';

    const publicBadge = department.is_public
        ? `<span class="badge badge-success badge-xs gap-1"><span class="icon-[tabler--eye] size-3"></span>Public</span>`
        : `<span class="badge badge-ghost badge-xs gap-1"><span class="icon-[tabler--eye-off] size-3"></span>Private</span>`;

    const inchargeDisplay = department.incharge_name
        ? `<span class="flex items-center gap-1"><span class="icon-[tabler--user] size-3"></span>${escapeHtmlDept(department.incharge_name)}</span>`
        : '';

    // Update the name
    item.querySelector('.font-medium').textContent = department.name;

    // Update badges
    const badgeContainer = item.querySelector('.text-xs.text-base-content\\/50');
    badgeContainer.innerHTML = `${publicBadge}${inchargeDisplay}`;
}

function updateDepartmentsCount() {
    const list = document.getElementById('departments-list');
    const count = list ? list.querySelectorAll('.dept-item').length : 0;
    const countBadge = document.getElementById('departments-count');
    if (countBadge) {
        countBadge.textContent = count;
    }
}

function escapeHtmlDept(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function editDepartment(departmentId) {
    const item = document.querySelector(`.dept-item[data-id="${departmentId}"]`);
    if (!item) return;

    const name = item.dataset.name;
    const publicView = item.dataset.public;
    const inchargeId = item.dataset.inchargeId;
    const inchargeName = item.dataset.inchargeName;
    const inchargeAvatar = item.dataset.inchargeAvatar;

    // Update form for edit mode
    document.getElementById('dept-action').value = 'edit';
    document.getElementById('dept-edit-id').value = departmentId;
    document.getElementById('dept-name').value = name;
    document.getElementById('dept-form-icon').className = 'icon-[tabler--edit] size-4';
    document.getElementById('dept-form-title-text').textContent = 'Edit Department';
    document.getElementById('dept-submit-icon').className = 'icon-[tabler--check] size-4';
    document.getElementById('dept-submit-text').textContent = 'Save Changes';
    document.getElementById('dept-cancel-edit-btn').classList.remove('hidden');
    document.getElementById('dept-submit-btn').classList.remove('btn-primary');
    document.getElementById('dept-submit-btn').classList.add('btn-success');
    document.getElementById('dept-form-error').classList.add('hidden');

    // Set public view
    if (publicView === '1') {
        document.getElementById('dept-public-yes').checked = true;
    } else {
        document.getElementById('dept-public-no').checked = true;
    }

    // Set incharge if exists
    if (inchargeId && inchargeName) {
        selectDeptIncharge(inchargeId, inchargeName, inchargeAvatar);
    } else {
        clearDeptIncharge();
    }

    // Scroll to form
    document.querySelector('.card.bg-base-200').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function cancelEditDepartment() {
    resetDepartmentForm();
}

function confirmDeleteDepartment(departmentId, name) {
    deleteDeptId = departmentId;
    document.getElementById('delete-dept-name').textContent = name;
    document.getElementById('delete-dept-modal').classList.remove('hidden');
}

function closeDeleteDeptModal() {
    document.getElementById('delete-dept-modal').classList.add('hidden');
    deleteDeptId = null;
}

async function deleteDepartment() {
    if (deleteDeptId === null) return;

    const formData = new FormData();
    formData.append('_token', deptCsrfToken);
    formData.append('action', 'delete');
    formData.append('delete_id', deleteDeptId);

    try {
        const response = await fetch(departmentsEndpoint, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            }
        });

        const data = await response.json();

        if (data.success) {
            // Remove item from DOM
            const item = document.querySelector(`.dept-item[data-id="${deleteDeptId}"]`);
            if (item) {
                item.remove();
            }
            updateDepartmentsCount();

            // Show "no departments" message if list is empty
            const list = document.getElementById('departments-list');
            const noMsg = document.getElementById('no-departments-msg');
            if (list && list.querySelectorAll('.dept-item').length === 0) {
                list.classList.add('hidden');
                if (noMsg) {
                    noMsg.classList.remove('hidden');
                }
            }

            showDeptToast(data.message || 'Department deleted successfully.', 'success');
        } else {
            showDeptToast(data.message || 'An error occurred.', 'error');
        }
    } catch (error) {
        showDeptToast('An error occurred. Please try again.', 'error');
    }

    closeDeleteDeptModal();
}

function showDeptToast(message, type = 'success') {
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

// Department Incharge Searchable Dropdown Functions
function handleDeptInchargeClick(event) {
    event.stopPropagation();
    const options = document.getElementById('dept-incharge-options');
    if (options.classList.contains('hidden')) {
        showDeptInchargeDropdown();
        // Focus the search input
        document.getElementById('dept-incharge-search').focus();
    } else {
        hideDeptInchargeDropdown();
    }
}

function showDeptInchargeDropdown() {
    const options = document.getElementById('dept-incharge-options');
    options.classList.remove('hidden');
}

function hideDeptInchargeDropdown() {
    const options = document.getElementById('dept-incharge-options');
    if (options) {
        options.classList.add('hidden');
    }
}

function filterDeptInchargeOptions(searchTerm) {
    const options = document.querySelectorAll('.dept-incharge-option');
    const noResults = document.getElementById('dept-incharge-no-results');
    let hasVisibleOptions = false;

    searchTerm = searchTerm.toLowerCase().trim();

    options.forEach(option => {
        const name = (option.dataset.name || '').toLowerCase();
        const id = option.dataset.id;

        // Always show "No incharge" option when search is empty
        if (id === '' && searchTerm === '') {
            option.classList.remove('hidden');
            hasVisibleOptions = true;
            return;
        }

        // Hide "No incharge" when searching
        if (id === '' && searchTerm !== '') {
            option.classList.add('hidden');
            return;
        }

        if (name.includes(searchTerm)) {
            option.classList.remove('hidden');
            hasVisibleOptions = true;
        } else {
            option.classList.add('hidden');
        }
    });

    if (hasVisibleOptions) {
        noResults.classList.add('hidden');
    } else {
        noResults.classList.remove('hidden');
    }

    showDeptInchargeDropdown();
}

function selectDeptIncharge(id, name, avatar) {
    const hiddenInput = document.getElementById('dept-incharge-id');
    const searchInput = document.getElementById('dept-incharge-search');
    const selectedDisplay = document.getElementById('dept-incharge-selected');
    const avatarImg = document.getElementById('dept-incharge-avatar');
    const nameSpan = document.getElementById('dept-incharge-name');
    const clearBtn = document.getElementById('dept-incharge-clear');

    hiddenInput.value = id;

    if (id && name) {
        // Show selected member
        searchInput.classList.add('hidden');
        selectedDisplay.classList.remove('hidden');
        selectedDisplay.classList.add('flex');
        avatarImg.src = avatar;
        nameSpan.textContent = name;
        clearBtn.classList.remove('hidden');
    } else {
        // Clear selection
        searchInput.classList.remove('hidden');
        searchInput.value = '';
        selectedDisplay.classList.add('hidden');
        selectedDisplay.classList.remove('flex');
        clearBtn.classList.add('hidden');
    }

    hideDeptInchargeDropdown();

    // Reset filter
    document.querySelectorAll('.dept-incharge-option').forEach(opt => {
        opt.classList.remove('hidden');
    });
    document.getElementById('dept-incharge-no-results').classList.add('hidden');
}

function clearDeptIncharge(event) {
    if (event) {
        event.stopPropagation();
    }
    selectDeptIncharge('', '', '');
}

// Close dropdown when clicking outside
document.addEventListener('click', function(e) {
    const dropdown = document.getElementById('dept-incharge-dropdown');
    if (dropdown && !dropdown.contains(e.target)) {
        hideDeptInchargeDropdown();
    }
});

// Close on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        // Check if delete modal is open first
        const deleteModal = document.getElementById('delete-dept-modal');
        if (deleteModal && !deleteModal.classList.contains('hidden')) {
            closeDeleteDeptModal();
            return;
        }

        const drawer = document.getElementById('departments-drawer');
        if (drawer && !drawer.classList.contains('hidden')) {
            const options = document.getElementById('dept-incharge-options');
            if (options && !options.classList.contains('hidden')) {
                hideDeptInchargeDropdown();
            } else {
                closeDepartmentsDrawer();
            }
        }
    }
});
</script>
