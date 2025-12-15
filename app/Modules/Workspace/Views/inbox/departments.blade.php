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
                <span>Departments</span>
            </div>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <a href="{{ route('workspace.show', $workspace) }}" class="btn btn-ghost btn-sm btn-square">
                        <span class="icon-[tabler--arrow-left] size-5"></span>
                    </a>
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-lg bg-secondary/10 flex items-center justify-center">
                            <span class="icon-[tabler--building] size-6 text-secondary"></span>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-base-content">Departments</h1>
                            <p class="text-sm text-base-content/60">Create departments to organize and route tickets</p>
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

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Add Department Form -->
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <h2 class="card-title text-lg mb-4" id="dept-form-title">
                        <span class="icon-[tabler--plus] size-5" id="dept-form-icon"></span>
                        <span id="dept-form-title-text">Add Department</span>
                    </h2>

                    <form id="department-form" onsubmit="submitDepartmentForm(event)">
                        <input type="hidden" name="action" id="dept-action" value="add">
                        <input type="hidden" name="edit_id" id="dept-edit-id" value="">

                        <div class="space-y-4">
                            <!-- Department Name -->
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium">Department Name <span class="text-error">*</span></span>
                                </label>
                                <input type="text" name="name" id="dept-name" class="input input-bordered" placeholder="e.g., Sales, Support, Billing" required>
                            </div>

                            <!-- Public View -->
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium">Visible to Customers</span>
                                </label>
                                <div class="flex gap-4">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" name="public_view" value="1" class="radio radio-primary radio-sm" checked>
                                        <span class="label-text">Yes - Customers can select</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" name="public_view" value="0" class="radio radio-primary radio-sm">
                                        <span class="label-text">No - Internal only</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Department Incharge -->
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium">Department Incharge</span>
                                </label>
                                <select name="incharge_id" id="dept-incharge" data-select='{
                                    "placeholder": "Search and select incharge...",
                                    "hasSearch": true,
                                    "toggleTag": "<button type=\"button\" aria-expanded=\"false\"></button>",
                                    "toggleClasses": "advance-select-toggle",
                                    "dropdownClasses": "advance-select-menu max-h-52 overflow-y-auto",
                                    "optionClasses": "advance-select-option selected:select-active",
                                    "optionTemplate": "<div class=\"flex justify-between items-center w-full\"><div class=\"flex items-center gap-3\"><div data-icon></div><div class=\"text-sm text-base-content\" data-title></div></div><span class=\"icon-[tabler--check] shrink-0 size-4 text-primary hidden selected:block\"></span></div>",
                                    "extraMarkup": "<span class=\"icon-[tabler--caret-up-down] shrink-0 size-4 text-base-content/90 absolute top-1/2 end-3 -translate-y-1/2\"></span>"
                                }' class="hidden">
                                    <option value="">No incharge assigned</option>
                                    @foreach($workspace->members as $member)
                                        <option value="{{ $member->id }}" data-select-option='{
                                            "icon": "<div class=\"avatar\"><div class=\"w-6 rounded-full\"><img src=\"{{ $member->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($member->name) . '&background=random' }}\" alt=\"{{ $member->name }}\" /></div></div>"
                                        }'>{{ $member->name }}</option>
                                    @endforeach
                                </select>
                                <label class="label">
                                    <span class="label-text-alt text-base-content/50">Person responsible for this department</span>
                                </label>
                            </div>

                            <!-- Submit Buttons -->
                            <div class="flex gap-2 pt-2">
                                <button type="submit" class="btn btn-primary flex-1 gap-2" id="dept-submit-btn">
                                    <span class="icon-[tabler--plus] size-5" id="dept-submit-icon"></span>
                                    <span id="dept-submit-text">Add Department</span>
                                </button>
                                <button type="button" class="btn btn-ghost hidden" id="dept-cancel-edit-btn" onclick="cancelEditDepartment()">
                                    Cancel
                                </button>
                            </div>
                            <div id="dept-form-error" class="text-error text-sm hidden"></div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Departments List -->
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <h2 class="card-title text-lg mb-4">
                        <span class="icon-[tabler--list] size-5"></span>
                        Existing Departments
                        <span class="badge badge-ghost badge-sm" id="departments-count">{{ $departments->count() }}</span>
                    </h2>

                    <div class="space-y-2 {{ $departments->count() === 0 ? 'hidden' : '' }}" id="departments-list">
                        @foreach($departments as $department)
                            <div class="flex items-center justify-between p-3 bg-base-200 rounded-lg group department-item"
                                 data-id="{{ $department->id }}"
                                 data-name="{{ $department->name }}"
                                 data-public="{{ $department->is_public ? '1' : '0' }}"
                                 data-incharge="{{ $department->incharge_id }}">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg bg-secondary/20 flex items-center justify-center">
                                        <span class="icon-[tabler--building] size-5 text-secondary"></span>
                                    </div>
                                    <div>
                                        <p class="font-medium">{{ $department->name }}</p>
                                        <div class="flex items-center gap-2 text-xs text-base-content/50">
                                            @if($department->is_public)
                                                <span class="badge badge-success badge-xs">Public</span>
                                            @else
                                                <span class="badge badge-ghost badge-xs">Internal</span>
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
                        <span class="icon-[tabler--building-off] size-12 mb-2 opacity-50"></span>
                        <p class="text-sm">No departments configured</p>
                        <p class="text-xs">Add your first department using the form</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Info Alert -->
        <div class="alert alert-info mt-6">
            <span class="icon-[tabler--info-circle] size-5"></span>
            <span class="text-sm">Departments help organize tickets and can be used for automatic ticket routing based on rules you configure.</span>
        </div>
    </div>
</div>

<!-- Delete Department Confirmation Modal -->
<div id="delete-department-modal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50" onclick="closeDeleteDepartmentModal()"></div>
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
                Are you sure you want to delete the department "<span id="delete-department-name" class="font-semibold"></span>"?
            </p>
            <div class="flex justify-end gap-2">
                <button type="button" class="btn btn-ghost" onclick="closeDeleteDepartmentModal()">Cancel</button>
                <button type="button" class="btn btn-error gap-2" onclick="deleteDepartment()" id="confirm-delete-department-btn">
                    <span class="icon-[tabler--trash] size-5"></span>
                    Delete Department
                </button>
            </div>
        </div>
    </div>
</div>

<script>
const departmentsEndpoint = '{{ route('workspace.add-department', $workspace) }}';
const csrfToken = '{{ csrf_token() }}';
let deleteDepartmentId = null;

function resetDepartmentForm() {
    const form = document.getElementById('department-form');
    form.reset();

    document.getElementById('dept-action').value = 'add';
    document.getElementById('dept-edit-id').value = '';
    document.getElementById('dept-form-icon').className = 'icon-[tabler--plus] size-5';
    document.getElementById('dept-form-title-text').textContent = 'Add Department';
    document.getElementById('dept-submit-icon').className = 'icon-[tabler--plus] size-5';
    document.getElementById('dept-submit-text').textContent = 'Add Department';
    document.getElementById('dept-cancel-edit-btn').classList.add('hidden');
    document.getElementById('dept-submit-btn').classList.remove('btn-success');
    document.getElementById('dept-submit-btn').classList.add('btn-secondary');
    document.getElementById('dept-form-error').classList.add('hidden');
}

function editDepartment(departmentId) {
    const item = document.querySelector(`.department-item[data-id="${departmentId}"]`);
    if (!item) return;

    const name = item.dataset.name;
    const isPublic = item.dataset.public;
    const inchargeId = item.dataset.incharge;

    document.getElementById('dept-action').value = 'edit';
    document.getElementById('dept-edit-id').value = departmentId;
    document.getElementById('dept-name').value = name;
    document.querySelector(`input[name="public_view"][value="${isPublic}"]`).checked = true;
    document.getElementById('dept-incharge').value = inchargeId || '';

    document.getElementById('dept-form-icon').className = 'icon-[tabler--edit] size-5';
    document.getElementById('dept-form-title-text').textContent = 'Edit Department';
    document.getElementById('dept-submit-icon').className = 'icon-[tabler--check] size-5';
    document.getElementById('dept-submit-text').textContent = 'Save Changes';
    document.getElementById('dept-cancel-edit-btn').classList.remove('hidden');
    document.getElementById('dept-submit-btn').classList.remove('btn-secondary');
    document.getElementById('dept-submit-btn').classList.add('btn-success');

    document.getElementById('dept-name').scrollIntoView({ behavior: 'smooth', block: 'center' });
    document.getElementById('dept-name').focus();
}

function cancelEditDepartment() {
    resetDepartmentForm();
}

async function submitDepartmentForm(event) {
    event.preventDefault();

    const action = document.getElementById('dept-action').value;
    const name = document.getElementById('dept-name').value.trim();
    const publicView = document.querySelector('input[name="public_view"]:checked').value;
    const inchargeId = document.getElementById('dept-incharge').value;
    const editId = document.getElementById('dept-edit-id').value;
    const errorDiv = document.getElementById('dept-form-error');

    if (!name) {
        errorDiv.textContent = 'Department name is required.';
        errorDiv.classList.remove('hidden');
        return;
    }

    const formData = new FormData();
    formData.append('_token', csrfToken);
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
                addDepartmentToList(data.department);
                document.getElementById('dept-name').value = '';
                document.getElementById('dept-name').focus();
            } else if (action === 'edit') {
                updateDepartmentInList(editId, data.department);
                resetDepartmentForm();
            }
            errorDiv.classList.add('hidden');
            showToast(data.message || 'Department saved successfully.', 'success');
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

    if (noMsg) noMsg.classList.add('hidden');
    if (list) list.classList.remove('hidden');
    if (!list) return;

    const item = document.createElement('div');
    item.className = 'flex items-center justify-between p-3 bg-base-200 rounded-lg group department-item';
    item.dataset.id = department.id;
    item.dataset.name = department.name;
    item.dataset.public = department.is_public ? '1' : '0';
    item.dataset.incharge = department.incharge_id || '';

    item.innerHTML = `
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-secondary/20 flex items-center justify-center">
                <span class="icon-[tabler--building] size-5 text-secondary"></span>
            </div>
            <div>
                <p class="font-medium">${escapeHtml(department.name)}</p>
                <div class="flex items-center gap-2 text-xs text-base-content/50">
                    ${department.is_public
                        ? '<span class="badge badge-success badge-xs">Public</span>'
                        : '<span class="badge badge-ghost badge-xs">Internal</span>'}
                    ${department.incharge_name
                        ? `<span class="flex items-center gap-1"><span class="icon-[tabler--user] size-3"></span>${escapeHtml(department.incharge_name)}</span>`
                        : ''}
                </div>
            </div>
        </div>
        <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
            <button type="button" class="btn btn-ghost btn-xs btn-square" onclick="editDepartment(${department.id})" title="Edit">
                <span class="icon-[tabler--edit] size-4"></span>
            </button>
            <button type="button" class="btn btn-ghost btn-xs btn-square text-error" onclick="confirmDeleteDepartment(${department.id}, '${escapeHtml(department.name).replace(/'/g, "\\'")}')" title="Delete">
                <span class="icon-[tabler--trash] size-4"></span>
            </button>
        </div>
    `;

    list.appendChild(item);
    updateDepartmentsCount();
}

function updateDepartmentInList(departmentId, department) {
    const item = document.querySelector(`.department-item[data-id="${departmentId}"]`);
    if (!item) return;

    item.dataset.name = department.name;
    item.dataset.public = department.is_public ? '1' : '0';
    item.dataset.incharge = department.incharge_id || '';

    item.querySelector('.font-medium').textContent = department.name;

    const infoDiv = item.querySelector('.text-xs.text-base-content\\/50');
    infoDiv.innerHTML = `
        ${department.is_public
            ? '<span class="badge badge-success badge-xs">Public</span>'
            : '<span class="badge badge-ghost badge-xs">Internal</span>'}
        ${department.incharge_name
            ? `<span class="flex items-center gap-1"><span class="icon-[tabler--user] size-3"></span>${escapeHtml(department.incharge_name)}</span>`
            : ''}
    `;
}

function updateDepartmentsCount() {
    const list = document.getElementById('departments-list');
    const count = list ? list.querySelectorAll('.department-item').length : 0;
    const countBadge = document.getElementById('departments-count');
    if (countBadge) countBadge.textContent = count;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function confirmDeleteDepartment(departmentId, name) {
    deleteDepartmentId = departmentId;
    document.getElementById('delete-department-name').textContent = name;
    document.getElementById('delete-department-modal').classList.remove('hidden');
}

function closeDeleteDepartmentModal() {
    document.getElementById('delete-department-modal').classList.add('hidden');
    deleteDepartmentId = null;
}

async function deleteDepartment() {
    if (deleteDepartmentId === null) return;

    const formData = new FormData();
    formData.append('_token', csrfToken);
    formData.append('action', 'delete');
    formData.append('delete_id', deleteDepartmentId);

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
            const item = document.querySelector(`.department-item[data-id="${deleteDepartmentId}"]`);
            if (item) item.remove();
            updateDepartmentsCount();

            const list = document.getElementById('departments-list');
            const noMsg = document.getElementById('no-departments-msg');
            if (list && list.querySelectorAll('.department-item').length === 0) {
                list.classList.add('hidden');
                if (noMsg) noMsg.classList.remove('hidden');
            }

            showToast(data.message || 'Department deleted successfully.', 'success');
        } else {
            showToast(data.message || 'An error occurred.', 'error');
        }
    } catch (error) {
        showToast('An error occurred. Please try again.', 'error');
    }

    closeDeleteDepartmentModal();
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
        const deleteModal = document.getElementById('delete-department-modal');
        if (deleteModal && !deleteModal.classList.contains('hidden')) {
            closeDeleteDepartmentModal();
        }
    }
});
</script>
@endsection
