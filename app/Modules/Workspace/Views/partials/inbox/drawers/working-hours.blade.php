{{-- Working Hours Drawer --}}
@php
    $inboxSettings = $workspace->settings['inbox'] ?? [];
@endphp

<div id="working-hours-drawer" class="fixed inset-0 z-50 hidden">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-black/50 transition-opacity" onclick="closeWorkingHoursDrawer()"></div>

    <!-- Drawer Panel -->
    <div class="absolute right-0 top-0 h-full w-full max-w-xl bg-base-100 shadow-xl transform translate-x-full transition-transform duration-300" id="working-hours-drawer-panel">
        <!-- Drawer Header -->
        <div class="flex items-center justify-between p-4 border-b border-base-200">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center">
                    <span class="icon-[tabler--clock-hour-4] size-5 text-primary"></span>
                </div>
                <div>
                    <h3 class="font-semibold text-lg">Working Hours</h3>
                    <p class="text-sm text-base-content/60">Configure your team's working hours</p>
                </div>
            </div>
            <button type="button" class="btn btn-ghost btn-sm btn-square" onclick="closeWorkingHoursDrawer()">
                <span class="icon-[tabler--x] size-5"></span>
            </button>
        </div>

        <!-- Drawer Content -->
        <form action="{{ route('workspace.update-working-hours', $workspace) }}" method="POST" id="working-hours-form">
            @csrf
            <div class="overflow-y-auto h-[calc(100vh-160px)] p-4 space-y-6">

                <!-- Hour Format -->
                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-medium">Hour Format</span>
                    </label>
                    <div class="flex gap-4">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="hour_format" value="12" class="radio radio-primary radio-sm" {{ ($inboxSettings['working_hours']['hour_format'] ?? '12') === '12' ? 'checked' : '' }}>
                            <span class="label-text">12 Hour (AM/PM)</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="hour_format" value="24" class="radio radio-primary radio-sm" {{ ($inboxSettings['working_hours']['hour_format'] ?? '12') === '24' ? 'checked' : '' }}>
                            <span class="label-text">24 Hour</span>
                        </label>
                    </div>
                </div>

                <!-- Date Format -->
                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-medium">Date Format</span>
                    </label>
                    <select name="date_format" class="select select-bordered select-sm">
                        <option value="MM/DD/YYYY" {{ ($inboxSettings['working_hours']['date_format'] ?? 'MM/DD/YYYY') === 'MM/DD/YYYY' ? 'selected' : '' }}>MM/DD/YYYY (12/25/2024)</option>
                        <option value="DD/MM/YYYY" {{ ($inboxSettings['working_hours']['date_format'] ?? '') === 'DD/MM/YYYY' ? 'selected' : '' }}>DD/MM/YYYY (25/12/2024)</option>
                        <option value="YYYY-MM-DD" {{ ($inboxSettings['working_hours']['date_format'] ?? '') === 'YYYY-MM-DD' ? 'selected' : '' }}>YYYY-MM-DD (2024-12-25)</option>
                        <option value="DD MMM YYYY" {{ ($inboxSettings['working_hours']['date_format'] ?? '') === 'DD MMM YYYY' ? 'selected' : '' }}>DD MMM YYYY (25 Dec 2024)</option>
                        <option value="MMM DD, YYYY" {{ ($inboxSettings['working_hours']['date_format'] ?? '') === 'MMM DD, YYYY' ? 'selected' : '' }}>MMM DD, YYYY (Dec 25, 2024)</option>
                    </select>
                </div>

                <div class="divider text-sm text-base-content/50">Daily Schedule</div>

                <!-- Days of Week -->
                @php
                    $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                    $dayLabels = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                    $workingHours = $inboxSettings['working_hours']['days'] ?? [];
                @endphp

                <div class="space-y-3">
                    @foreach($days as $index => $day)
                    @php
                        $dayData = $workingHours[$day] ?? ['enabled' => in_array($day, ['monday', 'tuesday', 'wednesday', 'thursday', 'friday']), 'start' => '09:00', 'end' => '17:00', 'hours' => 8];
                    @endphp
                    <div class="p-3 bg-base-200 rounded-lg">
                        <div class="flex items-center justify-between mb-3">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="days[{{ $day }}][enabled]" value="1" class="checkbox checkbox-primary checkbox-sm day-toggle" data-day="{{ $day }}" {{ $dayData['enabled'] ? 'checked' : '' }}>
                                <span class="font-medium">{{ $dayLabels[$index] }}</span>
                            </label>
                            <div class="day-hours-display text-sm text-base-content/60" id="{{ $day }}-hours-display">
                                @if($dayData['enabled'])
                                    {{ $dayData['hours'] ?? 8 }}h
                                @else
                                    <span class="text-base-content/40">Off</span>
                                @endif
                            </div>
                        </div>

                        <div class="day-time-inputs grid grid-cols-3 gap-3 {{ $dayData['enabled'] ? '' : 'hidden' }}" id="{{ $day }}-inputs">
                            <!-- Start Time -->
                            <div class="form-control">
                                <label class="label py-1">
                                    <span class="label-text text-xs">Start Time</span>
                                </label>
                                <input type="text" name="days[{{ $day }}][start]" value="{{ $dayData['start'] ?? '09:00' }}" class="input input-bordered input-sm time-input" id="flatpickr-time-{{ $day }}-start" data-day="{{ $day }}" placeholder="HH:MM">
                            </div>

                            <!-- End Time -->
                            <div class="form-control">
                                <label class="label py-1">
                                    <span class="label-text text-xs">End Time</span>
                                </label>
                                <input type="text" name="days[{{ $day }}][end]" value="{{ $dayData['end'] ?? '17:00' }}" class="input input-bordered input-sm time-input" id="flatpickr-time-{{ $day }}-end" data-day="{{ $day }}" placeholder="HH:MM">
                            </div>

                            <!-- Total Hours -->
                            <div class="form-control">
                                <label class="label py-1">
                                    <span class="label-text text-xs">Total Hours</span>
                                </label>
                                <input type="number" name="days[{{ $day }}][hours]" value="{{ $dayData['hours'] ?? 8 }}" min="0" max="24" step="0.5" class="input input-bordered input-sm" id="{{ $day }}-total-hours" readonly>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Weekly Summary -->
                <div class="p-4 bg-primary/10 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="font-medium text-primary">Total Weekly Hours</span>
                            <p class="text-xs text-base-content/60">Sum of all working days</p>
                        </div>
                        <div class="text-2xl font-bold text-primary" id="total-weekly-hours">40h</div>
                    </div>
                </div>

                <!-- Timezone Note -->
                <div class="alert alert-info">
                    <span class="icon-[tabler--info-circle] size-5"></span>
                    <span class="text-sm">Working hours are used to calculate SLA response times and exclude non-working hours from time tracking.</span>
                </div>
            </div>

            <!-- Drawer Footer -->
            <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-base-200 bg-base-100">
                <div class="flex gap-3">
                    <button type="submit" class="btn btn-primary flex-1">
                        <span class="icon-[tabler--check] size-5"></span>
                        Save Working Hours
                    </button>
                    <button type="button" class="btn btn-ghost flex-1" onclick="closeWorkingHoursDrawer()">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
// Define days array at the top
const days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
const timePickerInstances = {};

function openWorkingHoursDrawer() {
    const drawer = document.getElementById('working-hours-drawer');
    const panel = document.getElementById('working-hours-drawer-panel');

    drawer.classList.remove('hidden');
    document.body.style.overflow = 'hidden';

    setTimeout(() => {
        panel.classList.remove('translate-x-full');
    }, 10);

    // Initialize time pickers after drawer is visible
    setTimeout(() => {
        initializeTimePickers();
    }, 50);

    // Calculate all days on open
    days.forEach(day => calculateDayHours(day));
    calculateTotalWeeklyHours();
}

function closeWorkingHoursDrawer() {
    const drawer = document.getElementById('working-hours-drawer');
    const panel = document.getElementById('working-hours-drawer-panel');

    panel.classList.add('translate-x-full');

    setTimeout(() => {
        drawer.classList.add('hidden');
        document.body.style.overflow = '';
    }, 300);
}

// Calculate hours from start and end time
function calculateDayHours(day) {
    const startInput = document.querySelector('input[name="days[' + day + '][start]"]');
    const endInput = document.querySelector('input[name="days[' + day + '][end]"]');
    const hoursInput = document.getElementById(day + '-total-hours');
    const display = document.getElementById(day + '-hours-display');
    const toggle = document.querySelector('.day-toggle[data-day="' + day + '"]');

    if (!startInput || !endInput || !hoursInput) return;

    const startTime = startInput.value;
    const endTime = endInput.value;

    if (startTime && endTime) {
        const [startHour, startMin] = startTime.split(':').map(Number);
        const [endHour, endMin] = endTime.split(':').map(Number);

        let startMinutes = startHour * 60 + startMin;
        let endMinutes = endHour * 60 + endMin;

        // Handle overnight shifts (end time is next day)
        if (endMinutes < startMinutes) {
            endMinutes += 24 * 60;
        }

        const diffMinutes = endMinutes - startMinutes;
        const hours = Math.round(diffMinutes / 60 * 10) / 10; // Round to 1 decimal

        hoursInput.value = hours;

        if (toggle && toggle.checked) {
            display.innerHTML = hours + 'h';
        }
    }

    calculateTotalWeeklyHours();
}

// Toggle day inputs visibility
document.querySelectorAll('.day-toggle').forEach(toggle => {
    toggle.addEventListener('change', function() {
        const day = this.dataset.day;
        const inputs = document.getElementById(day + '-inputs');
        const display = document.getElementById(day + '-hours-display');

        if (this.checked) {
            inputs.classList.remove('hidden');
            calculateDayHours(day);
        } else {
            inputs.classList.add('hidden');
            display.innerHTML = '<span class="text-base-content/40">Off</span>';
        }

        calculateTotalWeeklyHours();
    });
});

// Auto-calculate when start or end time changes
document.querySelectorAll('.time-input').forEach(input => {
    input.addEventListener('change', function() {
        const day = this.dataset.day;
        calculateDayHours(day);
    });
});

function calculateTotalWeeklyHours() {
    let total = 0;
    const days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

    days.forEach(day => {
        const toggle = document.querySelector('.day-toggle[data-day="' + day + '"]');
        if (toggle && toggle.checked) {
            const hoursInput = document.getElementById(day + '-total-hours');
            if (hoursInput) {
                total += parseFloat(hoursInput.value) || 0;
            }
        }
    });

    // Round to 1 decimal place
    total = Math.round(total * 10) / 10;
    document.getElementById('total-weekly-hours').textContent = total + 'h';
}

// Close on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const drawer = document.getElementById('working-hours-drawer');
        if (drawer && !drawer.classList.contains('hidden')) {
            closeWorkingHoursDrawer();
        }
    }
});

// Get current hour format setting
function getHourFormat() {
    const format12 = document.querySelector('input[name="hour_format"][value="12"]');
    return format12 && format12.checked ? '12' : '24';
}

// Initialize or reinitialize all time pickers
function initializeTimePickers() {
    if (typeof flatpickr === 'undefined') return;

    const is24Hour = getHourFormat() === '24';

    days.forEach(day => {
        const startInput = document.getElementById('flatpickr-time-' + day + '-start');
        const endInput = document.getElementById('flatpickr-time-' + day + '-end');

        // Destroy existing instances
        if (timePickerInstances[day + '-start']) {
            timePickerInstances[day + '-start'].destroy();
        }
        if (timePickerInstances[day + '-end']) {
            timePickerInstances[day + '-end'].destroy();
        }

        // Create new instances with correct format
        if (startInput) {
            timePickerInstances[day + '-start'] = flatpickr(startInput, {
                enableTime: true,
                noCalendar: true,
                dateFormat: is24Hour ? 'H:i' : 'h:i K',
                time_24hr: is24Hour,
                minuteIncrement: 15,
                defaultDate: startInput.value || '09:00',
                appendTo: document.getElementById('working-hours-drawer-panel'),
                static: true,
                onChange: function(selectedDates, dateStr) {
                    // Convert to 24h format for internal use
                    if (!is24Hour && selectedDates[0]) {
                        const hours = selectedDates[0].getHours().toString().padStart(2, '0');
                        const mins = selectedDates[0].getMinutes().toString().padStart(2, '0');
                        startInput.dataset.time24 = hours + ':' + mins;
                    }
                    calculateDayHours(day);
                }
            });
        }

        if (endInput) {
            timePickerInstances[day + '-end'] = flatpickr(endInput, {
                enableTime: true,
                noCalendar: true,
                dateFormat: is24Hour ? 'H:i' : 'h:i K',
                time_24hr: is24Hour,
                minuteIncrement: 15,
                defaultDate: endInput.value || '17:00',
                appendTo: document.getElementById('working-hours-drawer-panel'),
                static: true,
                onChange: function(selectedDates, dateStr) {
                    // Convert to 24h format for internal use
                    if (!is24Hour && selectedDates[0]) {
                        const hours = selectedDates[0].getHours().toString().padStart(2, '0');
                        const mins = selectedDates[0].getMinutes().toString().padStart(2, '0');
                        endInput.dataset.time24 = hours + ':' + mins;
                    }
                    calculateDayHours(day);
                }
            });
        }
    });
}

// Listen for hour format changes
document.querySelectorAll('input[name="hour_format"]').forEach(radio => {
    radio.addEventListener('change', function() {
        initializeTimePickers();
    });
});

// Initialize on page load - just calculate hours, don't init pickers yet (done on drawer open)
document.addEventListener('DOMContentLoaded', function() {
    days.forEach(day => {
        const toggle = document.querySelector('.day-toggle[data-day="' + day + '"]');
        if (toggle && toggle.checked) {
            calculateDayHours(day);
        }
    });
    calculateTotalWeeklyHours();
});
</script>
