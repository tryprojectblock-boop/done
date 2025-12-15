{{-- Holidays Drawer --}}
@php
    $holidays = $workspace->holidays()->orderBy('date')->get();
@endphp

<div id="holidays-drawer" class="fixed inset-0 z-50 hidden">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-black/50 transition-opacity" onclick="closeHolidaysDrawer()"></div>

    <!-- Drawer Panel -->
    <div class="absolute right-0 top-0 h-full w-full max-w-xl bg-base-100 shadow-xl transform translate-x-full transition-transform duration-300" id="holidays-drawer-panel">
        <!-- Drawer Header -->
        <div class="flex items-center justify-between p-4 border-b border-base-200">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-warning/10 flex items-center justify-center">
                    <span class="icon-[tabler--calendar-off] size-5 text-warning"></span>
                </div>
                <div>
                    <h3 class="font-semibold text-lg">Holidays</h3>
                    <p class="text-sm text-base-content/60">Configure holidays and reduced working hours days</p>
                </div>
            </div>
            <button type="button" class="btn btn-ghost btn-sm btn-square" onclick="closeHolidaysDrawer()">
                <span class="icon-[tabler--x] size-5"></span>
            </button>
        </div>

        <!-- Drawer Content -->
        <div class="overflow-y-auto h-[calc(100vh-160px)] p-4 space-y-6">

            <!-- Add Holiday Form -->
            <div class="card bg-base-200">
                <div class="card-body p-4">
                    <h4 class="font-medium text-sm mb-3 flex items-center gap-2" id="holiday-form-title">
                        <span class="icon-[tabler--plus] size-4" id="holiday-form-icon"></span>
                        <span id="holiday-form-title-text">Add Holiday</span>
                    </h4>
                    <form id="holiday-form" onsubmit="submitHolidayForm(event)">
                        <input type="hidden" name="action" id="holiday-action" value="add">
                        <input type="hidden" name="edit_id" id="holiday-edit-id" value="">
                        <div class="space-y-4">
                            <!-- Holiday Name -->
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium">Holiday Name <span class="text-error">*</span></span>
                                </label>
                                <input type="text" name="name" id="holiday-name" class="input input-bordered input-sm" placeholder="e.g., Christmas Day, New Year" required>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <!-- Date -->
                                <div class="form-control">
                                    <label class="label">
                                        <span class="label-text font-medium">Date <span class="text-error">*</span></span>
                                    </label>
                                    <input type="text" name="date" id="holiday-date" class="input input-bordered input-sm" placeholder="Select date" required>
                                </div>

                                <!-- Working Hours -->
                                <div class="form-control">
                                    <label class="label">
                                        <span class="label-text font-medium">Working Hours</span>
                                    </label>
                                    <input type="number" name="working_hours" id="holiday-working-hours" class="input input-bordered input-sm" value="0" min="0" max="24" step="0.5">
                                    <label class="label">
                                        <span class="label-text-alt text-base-content/50">0 = Full holiday</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Submit Buttons -->
                            <div class="flex gap-2">
                                <button type="submit" class="btn btn-primary btn-sm flex-1 gap-2" id="holiday-submit-btn">
                                    <span class="icon-[tabler--plus] size-4" id="holiday-submit-icon"></span>
                                    <span id="holiday-submit-text">Add Holiday</span>
                                </button>
                                <button type="button" class="btn btn-ghost btn-sm hidden" id="holiday-cancel-edit-btn" onclick="cancelEditHoliday()">
                                    Cancel
                                </button>
                            </div>
                            <div id="holiday-form-error" class="text-error text-xs hidden"></div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Existing Holidays List -->
            <div>
                <h4 class="font-medium text-sm mb-3 flex items-center gap-2">
                    <span class="icon-[tabler--list] size-4"></span>
                    Configured Holidays
                    <span class="badge badge-ghost badge-sm" id="holidays-count">{{ $holidays->count() }}</span>
                </h4>

                <div class="space-y-2 {{ $holidays->count() === 0 ? 'hidden' : '' }}" id="holidays-list">
                    @foreach($holidays as $holiday)
                        <div class="flex items-center justify-between p-3 bg-base-200 rounded-lg group holiday-item"
                             data-id="{{ $holiday->id }}"
                             data-name="{{ $holiday->name }}"
                             data-date="{{ $holiday->date->format('Y-m-d') }}"
                             data-working-hours="{{ $holiday->working_hours }}">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg {{ $holiday->isFullHoliday() ? 'bg-warning/20' : 'bg-info/20' }} flex items-center justify-center">
                                    <span class="icon-[tabler--{{ $holiday->isFullHoliday() ? 'calendar-off' : 'calendar-time' }}] size-5 {{ $holiday->isFullHoliday() ? 'text-warning' : 'text-info' }}"></span>
                                </div>
                                <div>
                                    <p class="font-medium text-sm">{{ $holiday->name }}</p>
                                    <div class="flex items-center gap-2 text-xs text-base-content/50">
                                        <span class="flex items-center gap-1">
                                            <span class="icon-[tabler--calendar] size-3"></span>
                                            {{ $holiday->date->format('M d, Y') }}
                                        </span>
                                        @if($holiday->isFullHoliday())
                                            <span class="badge badge-warning badge-xs">Full Holiday</span>
                                        @else
                                            <span class="badge badge-info badge-xs">{{ $holiday->working_hours }}h work</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button type="button" class="btn btn-ghost btn-xs btn-square" onclick="editHoliday({{ $holiday->id }})" title="Edit">
                                    <span class="icon-[tabler--edit] size-4"></span>
                                </button>
                                <button type="button" class="btn btn-ghost btn-xs btn-square text-error" onclick="confirmDeleteHoliday({{ $holiday->id }}, '{{ addslashes($holiday->name) }}')" title="Delete">
                                    <span class="icon-[tabler--trash] size-4"></span>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="text-center py-8 text-base-content/50 {{ $holidays->count() > 0 ? 'hidden' : '' }}" id="no-holidays-msg">
                    <span class="icon-[tabler--calendar-off] size-12 mb-2 opacity-50"></span>
                    <p class="text-sm">No holidays configured</p>
                    <p class="text-xs">Add your first holiday above</p>
                </div>
            </div>

            <!-- Info Alert -->
            <div class="alert alert-info">
                <span class="icon-[tabler--info-circle] size-5"></span>
                <span class="text-sm">Holidays affect SLA calculations. Set working hours to 0 for full holidays, or specify reduced hours for partial working days.</span>
            </div>
        </div>

        <!-- Drawer Footer -->
        <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-base-200 bg-base-100">
            <div class="flex gap-3">
                <button type="button" class="btn btn-primary flex-1" onclick="closeHolidaysDrawer()">
                    <span class="icon-[tabler--check] size-5"></span>
                    Done
                </button>
                <button type="button" class="btn btn-ghost flex-1" onclick="closeHolidaysDrawer()">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Holiday Confirmation Modal -->
<div id="delete-holiday-modal" class="fixed inset-0 z-[60] hidden">
    <div class="absolute inset-0 bg-black/50" onclick="closeDeleteHolidayModal()"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="bg-base-100 rounded-xl shadow-xl max-w-md w-full p-6 relative z-10">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-12 h-12 rounded-full bg-error/20 flex items-center justify-center">
                    <span class="icon-[tabler--trash] size-6 text-error"></span>
                </div>
                <div>
                    <h3 class="text-lg font-bold">Delete Holiday</h3>
                    <p class="text-sm text-base-content/60">This action cannot be undone</p>
                </div>
            </div>
            <p class="text-base-content/70 mb-4">
                Are you sure you want to delete the holiday "<span id="delete-holiday-name" class="font-semibold"></span>"?
            </p>
            <div class="flex justify-end gap-2">
                <button type="button" class="btn btn-ghost" onclick="closeDeleteHolidayModal()">Cancel</button>
                <button type="button" class="btn btn-error gap-2" onclick="deleteHoliday()" id="confirm-delete-holiday-btn">
                    <span class="icon-[tabler--trash] size-5"></span>
                    Delete Holiday
                </button>
            </div>
        </div>
    </div>
</div>

<script>
const holidaysEndpoint = '{{ route('workspace.save-holidays', $workspace) }}';
const holidayCsrfToken = '{{ csrf_token() }}';
let deleteHolidayId = null;
let holidayDatePicker = null;

// Initialize Flatpickr for holiday date
function initHolidayDatePicker() {
    if (typeof flatpickr === 'undefined') return;

    // Destroy existing instance if any
    if (holidayDatePicker) {
        holidayDatePicker.destroy();
    }

    holidayDatePicker = flatpickr('#holiday-date', {
        dateFormat: 'Y-m-d',
        altInput: true,
        altFormat: 'F j, Y',
        monthSelectorType: 'dropdown',
        allowInput: false,
        clickOpens: true,
        appendTo: document.getElementById('holidays-drawer-panel'),
        static: true,
    });
}

function openHolidaysDrawer() {
    const drawer = document.getElementById('holidays-drawer');
    const panel = document.getElementById('holidays-drawer-panel');

    drawer.classList.remove('hidden');
    document.body.style.overflow = 'hidden';

    // Initialize flatpickr after drawer is visible
    setTimeout(() => {
        initHolidayDatePicker();
    }, 50);

    setTimeout(() => {
        panel.classList.remove('translate-x-full');
    }, 10);
}

function closeHolidaysDrawer() {
    const drawer = document.getElementById('holidays-drawer');
    const panel = document.getElementById('holidays-drawer-panel');

    panel.classList.add('translate-x-full');

    setTimeout(() => {
        drawer.classList.add('hidden');
        document.body.style.overflow = '';
    }, 300);

    resetHolidayForm();
}

function resetHolidayForm() {
    const form = document.getElementById('holiday-form');
    form.reset();

    document.getElementById('holiday-action').value = 'add';
    document.getElementById('holiday-edit-id').value = '';
    document.getElementById('holiday-working-hours').value = '0';
    document.getElementById('holiday-form-icon').className = 'icon-[tabler--plus] size-4';
    document.getElementById('holiday-form-title-text').textContent = 'Add Holiday';
    document.getElementById('holiday-submit-icon').className = 'icon-[tabler--plus] size-4';
    document.getElementById('holiday-submit-text').textContent = 'Add Holiday';
    document.getElementById('holiday-cancel-edit-btn').classList.add('hidden');
    document.getElementById('holiday-submit-btn').classList.remove('btn-success');
    document.getElementById('holiday-submit-btn').classList.add('btn-primary');
    document.getElementById('holiday-form-error').classList.add('hidden');

    // Clear flatpickr date
    if (holidayDatePicker) {
        holidayDatePicker.clear();
    }
}

function editHoliday(holidayId) {
    const item = document.querySelector(`.holiday-item[data-id="${holidayId}"]`);
    if (!item) return;

    const name = item.dataset.name;
    const date = item.dataset.date;
    const workingHours = item.dataset.workingHours;

    document.getElementById('holiday-action').value = 'edit';
    document.getElementById('holiday-edit-id').value = holidayId;
    document.getElementById('holiday-name').value = name;

    // Set date using flatpickr if available
    if (holidayDatePicker) {
        holidayDatePicker.setDate(date, true);
    } else {
        document.getElementById('holiday-date').value = date;
    }

    document.getElementById('holiday-working-hours').value = workingHours;
    document.getElementById('holiday-form-icon').className = 'icon-[tabler--edit] size-4';
    document.getElementById('holiday-form-title-text').textContent = 'Edit Holiday';
    document.getElementById('holiday-submit-icon').className = 'icon-[tabler--check] size-4';
    document.getElementById('holiday-submit-text').textContent = 'Save Changes';
    document.getElementById('holiday-cancel-edit-btn').classList.remove('hidden');
    document.getElementById('holiday-submit-btn').classList.remove('btn-primary');
    document.getElementById('holiday-submit-btn').classList.add('btn-success');

    document.querySelector('.card.bg-base-200').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function cancelEditHoliday() {
    resetHolidayForm();
}

async function submitHolidayForm(event) {
    event.preventDefault();

    const action = document.getElementById('holiday-action').value;
    const name = document.getElementById('holiday-name').value.trim();
    const date = document.getElementById('holiday-date').value;
    const workingHours = document.getElementById('holiday-working-hours').value;
    const editId = document.getElementById('holiday-edit-id').value;
    const errorDiv = document.getElementById('holiday-form-error');

    if (!name || !date) {
        errorDiv.textContent = 'Name and date are required.';
        errorDiv.classList.remove('hidden');
        return;
    }

    const formData = new FormData();
    formData.append('_token', holidayCsrfToken);
    formData.append('action', action);
    formData.append('name', name);
    formData.append('date', date);
    formData.append('working_hours', workingHours);
    if (action === 'edit') {
        formData.append('edit_id', editId);
    }

    try {
        const response = await fetch(holidaysEndpoint, {
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
                // Add holiday to list dynamically
                addHolidayToList(data.holiday || { id: Date.now(), name, date, working_hours: workingHours });
                // Reset form for next entry
                document.getElementById('holiday-name').value = '';
                document.getElementById('holiday-working-hours').value = '0';
                // Clear flatpickr date
                if (holidayDatePicker) {
                    holidayDatePicker.clear();
                } else {
                    document.getElementById('holiday-date').value = '';
                }
                document.getElementById('holiday-name').focus();
            } else if (action === 'edit') {
                // Update holiday in list
                updateHolidayInList(editId, name, date, workingHours);
                resetHolidayForm();
            }
            errorDiv.classList.add('hidden');
            showHolidayToast(data.message || 'Holiday saved successfully.', 'success');
        } else {
            errorDiv.textContent = data.message || 'An error occurred.';
            errorDiv.classList.remove('hidden');
        }
    } catch (error) {
        errorDiv.textContent = 'An error occurred. Please try again.';
        errorDiv.classList.remove('hidden');
    }
}

function addHolidayToList(holiday) {
    const list = document.getElementById('holidays-list');
    const noMsg = document.getElementById('no-holidays-msg');

    // Hide "no holidays" message and show list
    if (noMsg) {
        noMsg.classList.add('hidden');
    }
    if (list) {
        list.classList.remove('hidden');
    }

    if (!list) return;

    const isFullHoliday = parseFloat(holiday.working_hours) === 0;
    const dateObj = new Date(holiday.date + 'T00:00:00');
    const formattedDate = dateObj.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });

    const item = document.createElement('div');
    item.className = 'flex items-center justify-between p-3 bg-base-200 rounded-lg group holiday-item';
    item.dataset.id = holiday.id;
    item.dataset.name = holiday.name;
    item.dataset.date = holiday.date;
    item.dataset.workingHours = holiday.working_hours;

    item.innerHTML = `
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg ${isFullHoliday ? 'bg-warning/20' : 'bg-info/20'} flex items-center justify-center">
                <span class="icon-[tabler--${isFullHoliday ? 'calendar-off' : 'calendar-time'}] size-5 ${isFullHoliday ? 'text-warning' : 'text-info'}"></span>
            </div>
            <div>
                <p class="font-medium text-sm">${escapeHtml(holiday.name)}</p>
                <div class="flex items-center gap-2 text-xs text-base-content/50">
                    <span class="flex items-center gap-1">
                        <span class="icon-[tabler--calendar] size-3"></span>
                        ${formattedDate}
                    </span>
                    ${isFullHoliday
                        ? '<span class="badge badge-warning badge-xs">Full Holiday</span>'
                        : `<span class="badge badge-info badge-xs">${holiday.working_hours}h work</span>`
                    }
                </div>
            </div>
        </div>
        <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
            <button type="button" class="btn btn-ghost btn-xs btn-square" onclick="editHoliday(${holiday.id})" title="Edit">
                <span class="icon-[tabler--edit] size-4"></span>
            </button>
            <button type="button" class="btn btn-ghost btn-xs btn-square text-error" onclick="confirmDeleteHoliday(${holiday.id}, '${escapeHtml(holiday.name).replace(/'/g, "\\'")}')" title="Delete">
                <span class="icon-[tabler--trash] size-4"></span>
            </button>
        </div>
    `;

    list.appendChild(item);
    updateHolidaysCount();
}

function updateHolidayInList(holidayId, name, date, workingHours) {
    const item = document.querySelector(`.holiday-item[data-id="${holidayId}"]`);
    if (!item) return;

    const isFullHoliday = parseFloat(workingHours) === 0;
    const dateObj = new Date(date + 'T00:00:00');
    const formattedDate = dateObj.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });

    item.dataset.name = name;
    item.dataset.date = date;
    item.dataset.workingHours = workingHours;

    // Update icon container
    const iconContainer = item.querySelector('.w-10.h-10');
    iconContainer.className = `w-10 h-10 rounded-lg ${isFullHoliday ? 'bg-warning/20' : 'bg-info/20'} flex items-center justify-center`;
    iconContainer.innerHTML = `<span class="icon-[tabler--${isFullHoliday ? 'calendar-off' : 'calendar-time'}] size-5 ${isFullHoliday ? 'text-warning' : 'text-info'}"></span>`;

    // Update name
    item.querySelector('.font-medium').textContent = name;

    // Update date and badge
    const infoDiv = item.querySelector('.text-xs.text-base-content\\/50');
    infoDiv.innerHTML = `
        <span class="flex items-center gap-1">
            <span class="icon-[tabler--calendar] size-3"></span>
            ${formattedDate}
        </span>
        ${isFullHoliday
            ? '<span class="badge badge-warning badge-xs">Full Holiday</span>'
            : `<span class="badge badge-info badge-xs">${workingHours}h work</span>`
        }
    `;
}

function updateHolidaysCount() {
    const list = document.getElementById('holidays-list');
    const count = list ? list.querySelectorAll('.holiday-item').length : 0;
    const countBadge = document.getElementById('holidays-count');
    if (countBadge) {
        countBadge.textContent = count;
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function confirmDeleteHoliday(holidayId, name) {
    deleteHolidayId = holidayId;
    document.getElementById('delete-holiday-name').textContent = name;
    document.getElementById('delete-holiday-modal').classList.remove('hidden');
}

function closeDeleteHolidayModal() {
    document.getElementById('delete-holiday-modal').classList.add('hidden');
    deleteHolidayId = null;
}

async function deleteHoliday() {
    if (deleteHolidayId === null) return;

    const formData = new FormData();
    formData.append('_token', holidayCsrfToken);
    formData.append('action', 'delete');
    formData.append('delete_id', deleteHolidayId);

    try {
        const response = await fetch(holidaysEndpoint, {
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
            const item = document.querySelector(`.holiday-item[data-id="${deleteHolidayId}"]`);
            if (item) {
                item.remove();
            }
            updateHolidaysCount();

            // Show "no holidays" message if list is empty
            const list = document.getElementById('holidays-list');
            const noMsg = document.getElementById('no-holidays-msg');
            if (list && list.querySelectorAll('.holiday-item').length === 0) {
                list.classList.add('hidden');
                if (noMsg) {
                    noMsg.classList.remove('hidden');
                }
            }

            showHolidayToast(data.message || 'Holiday deleted successfully.', 'success');
        } else {
            showHolidayToast(data.message || 'An error occurred.', 'error');
        }
    } catch (error) {
        showHolidayToast('An error occurred. Please try again.', 'error');
    }

    closeDeleteHolidayModal();
}

function showHolidayToast(message, type = 'success') {
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
        const deleteModal = document.getElementById('delete-holiday-modal');
        if (deleteModal && !deleteModal.classList.contains('hidden')) {
            closeDeleteHolidayModal();
            return;
        }

        const drawer = document.getElementById('holidays-drawer');
        if (drawer && !drawer.classList.contains('hidden')) {
            closeHolidaysDrawer();
        }
    }
});
</script>
