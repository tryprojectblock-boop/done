@extends('layouts.app')

@push('styles')
<style>
    .flatpickr-calendar {
        z-index: 9999 !important;
    }
    /* Style the altInput created by flatpickr */
    .flatpickr-alt-input {
        background: transparent !important;
        border: 0 !important;
        outline: none !important;
        font-family: ui-monospace, monospace;
        text-align: center;
        flex-grow: 1;
        width: 100%;
    }
    .flatpickr-alt-input:focus {
        outline: none !important;
        box-shadow: none !important;
    }
</style>
@endpush

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
                <span>Working Hours</span>
            </div>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <a href="{{ route('workspace.show', $workspace) }}" class="btn btn-ghost btn-sm btn-square">
                        <span class="icon-[tabler--arrow-left] size-5"></span>
                    </a>
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-lg bg-primary/10 flex items-center justify-center">
                            <span class="icon-[tabler--clock-hour-4] size-6 text-primary"></span>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-base-content">Working Hours</h1>
                            <p class="text-sm text-base-content/60">Configure your team's working hours for SLA calculations</p>
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
            $settings = $inboxSettings;
            $hourFormat = $settings->hour_format ?? '12';
            $dateFormat = $settings->date_format ?? 'MM/DD/YYYY';
        @endphp

        <!-- Settings Form -->
        <form action="{{ route('workspace.update-working-hours', $workspace) }}" method="POST">
            @csrf

            <div class="space-y-6">
                <!-- Format Settings Card -->
                <div class="card bg-base-100 shadow">
                    <div class="card-body">
                        <h2 class="card-title text-lg mb-4">
                            <span class="icon-[tabler--settings] size-5"></span>
                            Format Settings
                        </h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Hour Format -->
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium">Hour Format</span>
                                </label>
                                <div class="flex gap-4">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" name="hour_format" value="12" class="radio radio-primary radio-sm" {{ $hourFormat === '12' ? 'checked' : '' }}>
                                        <span class="label-text">12 Hour (AM/PM)</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" name="hour_format" value="24" class="radio radio-primary radio-sm" {{ $hourFormat === '24' ? 'checked' : '' }}>
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
                                    <option value="MM/DD/YYYY" {{ $dateFormat === 'MM/DD/YYYY' ? 'selected' : '' }}>MM/DD/YYYY (12/25/2024)</option>
                                    <option value="DD/MM/YYYY" {{ $dateFormat === 'DD/MM/YYYY' ? 'selected' : '' }}>DD/MM/YYYY (25/12/2024)</option>
                                    <option value="YYYY-MM-DD" {{ $dateFormat === 'YYYY-MM-DD' ? 'selected' : '' }}>YYYY-MM-DD (2024-12-25)</option>
                                    <option value="DD MMM YYYY" {{ $dateFormat === 'DD MMM YYYY' ? 'selected' : '' }}>DD MMM YYYY (25 Dec 2024)</option>
                                    <option value="MMM DD, YYYY" {{ $dateFormat === 'MMM DD, YYYY' ? 'selected' : '' }}>MMM DD, YYYY (Dec 25, 2024)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Daily Schedule Card -->
                <div class="card bg-base-100 shadow">
                    <div class="card-body">
                        <h2 class="card-title text-lg mb-4">
                            <span class="icon-[tabler--calendar-week] size-5"></span>
                            Daily Schedule
                        </h2>

                        @php
                            $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                            $dayLabels = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                        @endphp

                        <div class="space-y-3">
                            @foreach($days as $index => $day)
                            @php
                                $dayData = $workingHours[$day] ?? null;
                                $isEnabled = $dayData ? $dayData->is_enabled : in_array($day, ['monday', 'tuesday', 'wednesday', 'thursday', 'friday']);
                                $startTime = $dayData ? substr($dayData->start_time, 0, 5) : '09:00';
                                $endTime = $dayData ? substr($dayData->end_time, 0, 5) : '17:00';
                                $totalHours = $dayData ? $dayData->total_hours : 8;
                            @endphp
                            <div class="p-4 bg-base-200 rounded-xl border border-base-300 hover:border-primary/30 transition-colors">
                                <div class="flex items-center justify-between mb-3">
                                    <label class="flex items-center gap-3 cursor-pointer">
                                        <input type="checkbox" name="days[{{ $day }}][enabled]" value="1" class="toggle toggle-primary toggle-sm day-toggle" data-day="{{ $day }}" {{ $isEnabled ? 'checked' : '' }}>
                                        <span class="font-semibold text-base">{{ $dayLabels[$index] }}</span>
                                    </label>
                                    <div class="day-hours-display flex items-center gap-2" id="{{ $day }}-hours-display">
                                        @if($isEnabled)
                                            <span class="badge badge-primary badge-lg font-bold">{{ $totalHours }}h</span>
                                        @else
                                            <span class="badge badge-ghost badge-lg">Off</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="day-time-inputs {{ $isEnabled ? '' : 'hidden' }}" id="{{ $day }}-inputs">
                                    <div class="flex items-center gap-3 bg-base-100 p-3 rounded-lg">
                                        <!-- Start Time -->
                                        <div class="flex-1">
                                            <label class="label py-0.5">
                                                <span class="label-text text-xs font-medium text-base-content/60">Start Time</span>
                                            </label>
                                            <div class="input input-sm flex items-center gap-2">
                                                <span class="icon-[tabler--clock] text-primary size-4 shrink-0"></span>
                                                <input type="text" name="days[{{ $day }}][start]" value="{{ $startTime }}" class="grow bg-transparent border-0 focus:outline-none font-mono text-center" id="flatpickr-time-{{ $day }}-start" data-day="{{ $day }}" placeholder="09:00">
                                            </div>
                                        </div>

                                        <!-- Arrow -->
                                        <div class="flex items-center pt-5">
                                            <span class="icon-[tabler--arrow-right] size-5 text-base-content/40"></span>
                                        </div>

                                        <!-- End Time -->
                                        <div class="flex-1">
                                            <label class="label py-0.5">
                                                <span class="label-text text-xs font-medium text-base-content/60">End Time</span>
                                            </label>
                                            <div class="input input-sm flex items-center gap-2">
                                                <span class="icon-[tabler--clock-off] text-error size-4 shrink-0"></span>
                                                <input type="text" name="days[{{ $day }}][end]" value="{{ $endTime }}" class="grow bg-transparent border-0 focus:outline-none font-mono text-center" id="flatpickr-time-{{ $day }}-end" data-day="{{ $day }}" placeholder="17:00">
                                            </div>
                                        </div>

                                        <!-- Equals -->
                                        <div class="flex items-center pt-5">
                                            <span class="icon-[tabler--equal] size-5 text-base-content/40"></span>
                                        </div>

                                        <!-- Total Hours -->
                                        <div class="w-20">
                                            <label class="label py-0.5">
                                                <span class="label-text text-xs font-medium text-base-content/60">Hours</span>
                                            </label>
                                            <div class="input input-sm flex items-center justify-center bg-primary/5 border-primary/20">
                                                <span class="font-mono font-semibold text-primary" id="{{ $day }}-total-hours-display">{{ $totalHours }}</span>
                                            </div>
                                            <input type="hidden" name="days[{{ $day }}][hours]" value="{{ $totalHours }}" id="{{ $day }}-total-hours">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <!-- Weekly Summary -->
                        <div class="mt-6 p-4 bg-primary/10 rounded-lg">
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="font-medium text-primary">Total Weekly Hours</span>
                                    <p class="text-xs text-base-content/60">Sum of all working days</p>
                                </div>
                                <div class="text-2xl font-bold text-primary" id="total-weekly-hours">0h</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Info Alert -->
                <div class="alert alert-info">
                    <span class="icon-[tabler--info-circle] size-5"></span>
                    <span class="text-sm">Working hours are used to calculate SLA response times and exclude non-working hours from time tracking.</span>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-start gap-3">
                    <button type="submit" class="btn btn-primary">
                        <span class="icon-[tabler--check] size-5"></span>
                        Save Working Hours
                    </button>
                    <a href="{{ route('workspace.show', $workspace) }}" class="btn btn-ghost">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
const days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
const timePickerInstances = {};

// Get current hour format setting
function getHourFormat() {
    const format12 = document.querySelector('input[name="hour_format"][value="12"]');
    return format12 && format12.checked ? '12' : '24';
}

// Parse time string to minutes (handles both 24h "HH:MM" and 12h "HH:MM AM/PM" formats)
function parseTimeToMinutes(timeStr) {
    if (!timeStr) return NaN;

    // Clean up the string
    timeStr = timeStr.trim().toUpperCase();

    // Check for AM/PM format
    const isPM = timeStr.includes('PM');
    const isAM = timeStr.includes('AM');

    // Remove AM/PM and extra spaces
    timeStr = timeStr.replace(/\s*(AM|PM)\s*/gi, '').trim();

    // Split by colon
    const parts = timeStr.split(':');
    if (parts.length < 2) return NaN;

    let hours = parseInt(parts[0], 10);
    const minutes = parseInt(parts[1], 10);

    if (isNaN(hours) || isNaN(minutes)) return NaN;

    // Convert 12-hour to 24-hour if AM/PM was present
    if (isPM && hours < 12) {
        hours += 12;
    } else if (isAM && hours === 12) {
        hours = 0;
    }

    return hours * 60 + minutes;
}

// Calculate hours from start and end time
function calculateDayHours(day) {
    const startInput = document.querySelector('input[name="days[' + day + '][start]"]');
    const endInput = document.querySelector('input[name="days[' + day + '][end]"]');
    const hoursInput = document.getElementById(day + '-total-hours');
    const hoursDisplay = document.getElementById(day + '-total-hours-display');
    const badgeDisplay = document.getElementById(day + '-hours-display');
    const toggle = document.querySelector('.day-toggle[data-day="' + day + '"]');

    if (!startInput || !endInput || !hoursInput) return;

    const startTime = startInput.value;
    const endTime = endInput.value;

    if (startTime && endTime) {
        let startMinutes = parseTimeToMinutes(startTime);
        let endMinutes = parseTimeToMinutes(endTime);

        if (isNaN(startMinutes) || isNaN(endMinutes)) return;

        // Handle overnight shifts (end time is next day)
        if (endMinutes < startMinutes) {
            endMinutes += 24 * 60;
        }

        const diffMinutes = endMinutes - startMinutes;
        const hours = Math.round(diffMinutes / 60 * 10) / 10;

        hoursInput.value = hours;
        if (hoursDisplay) {
            hoursDisplay.textContent = hours;
        }

        if (toggle && toggle.checked && badgeDisplay) {
            badgeDisplay.innerHTML = '<span class="badge badge-primary badge-lg font-bold">' + hours + 'h</span>';
        }
    }

    calculateTotalWeeklyHours();
}

function calculateTotalWeeklyHours() {
    let total = 0;

    days.forEach(day => {
        const toggle = document.querySelector('.day-toggle[data-day="' + day + '"]');
        if (toggle && toggle.checked) {
            const hoursInput = document.getElementById(day + '-total-hours');
            if (hoursInput) {
                total += parseFloat(hoursInput.value) || 0;
            }
        }
    });

    total = Math.round(total * 10) / 10;
    document.getElementById('total-weekly-hours').textContent = total + 'h';
}

// Toggle day inputs visibility
document.querySelectorAll('.day-toggle').forEach(toggle => {
    toggle.addEventListener('change', function() {
        const day = this.dataset.day;
        const inputs = document.getElementById(day + '-inputs');
        const display = document.getElementById(day + '-hours-display');
        const hoursInput = document.getElementById(day + '-total-hours');

        if (this.checked) {
            inputs.classList.remove('hidden');
            calculateDayHours(day);
            const hours = hoursInput ? hoursInput.value : 8;
            display.innerHTML = '<span class="badge badge-primary badge-lg font-bold">' + hours + 'h</span>';
        } else {
            inputs.classList.add('hidden');
            display.innerHTML = '<span class="badge badge-ghost badge-lg">Off</span>';
        }

        calculateTotalWeeklyHours();
    });
});

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

        // Create new instances - always store in H:i format, display based on preference
        if (startInput) {
            timePickerInstances[day + '-start'] = flatpickr(startInput, {
                enableTime: true,
                noCalendar: true,
                dateFormat: 'H:i',
                altInput: true,
                altInputClass: 'flatpickr-alt-input',
                altFormat: is24Hour ? 'H:i' : 'h:i K',
                time_24hr: is24Hour,
                minuteIncrement: 15,
                defaultDate: startInput.value || '09:00',
                onChange: function(selectedDates, dateStr) {
                    calculateDayHours(day);
                }
            });
        }

        if (endInput) {
            timePickerInstances[day + '-end'] = flatpickr(endInput, {
                enableTime: true,
                noCalendar: true,
                dateFormat: 'H:i',
                altInput: true,
                altInputClass: 'flatpickr-alt-input',
                altFormat: is24Hour ? 'H:i' : 'h:i K',
                time_24hr: is24Hour,
                minuteIncrement: 15,
                defaultDate: endInput.value || '17:00',
                onChange: function(selectedDates, dateStr) {
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

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    initializeTimePickers();
    days.forEach(day => {
        const toggle = document.querySelector('.day-toggle[data-day="' + day + '"]');
        if (toggle && toggle.checked) {
            calculateDayHours(day);
        }
    });
    calculateTotalWeeklyHours();
});
</script>
@endsection
