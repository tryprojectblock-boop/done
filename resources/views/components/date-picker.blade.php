@props([
    'name' => 'date',
    'id' => null,
    'value' => null,
    'placeholder' => 'Click to select date & time',
    'label' => null,
    'required' => false,
    'showTime' => true,
    'minDate' => null,
])

@php
    $pickerId = $id ?? 'date-picker-' . uniqid();
@endphp

<div class="form-control">
    @if($label)
        <label class="label">
            <span class="label-text font-medium">{{ $label }} @if($required)<span class="text-error">*</span>@endif</span>
        </label>
    @endif

    <!-- Date Display Input -->
    <div class="relative w-full" x-data="datePicker('{{ $pickerId }}', '{{ $value }}', {{ $showTime ? 'true' : 'false' }})">
        <input type="text"
               id="{{ $pickerId }}-display"
               readonly
               :class="{'input-primary border-primary': hasValue}"
               class="input input-bordered w-full cursor-pointer"
               placeholder="{{ $placeholder }}"
               @click="toggle()"
               x-model="displayValue">
        <span class="icon-[tabler--calendar] absolute right-3 top-1/2 -translate-y-1/2 size-5 text-base-content/50 pointer-events-none"></span>

        <!-- Hidden input for form submission -->
        <input type="hidden" name="{{ $name }}" id="{{ $pickerId }}-value" x-model="formValue">

        <!-- Absolute Positioned Date Picker Dropdown -->
        <div x-show="isOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-1"
             @click.away="close()"
             class="absolute z-50 left-0 top-full mt-2 p-4 bg-white border border-base-300 rounded-xl shadow-xl w-80">

            <!-- Year and Month Selectors -->
            <div class="flex gap-2 mb-4">
                <select x-model="viewYear" @change="renderCalendar()" class="select select-bordered select-sm flex-1">
                    <template x-for="year in years" :key="year">
                        <option :value="year" x-text="year"></option>
                    </template>
                </select>
                <select x-model="viewMonth" @change="renderCalendar()" class="select select-bordered select-sm flex-1">
                    <option value="0">January</option>
                    <option value="1">February</option>
                    <option value="2">March</option>
                    <option value="3">April</option>
                    <option value="4">May</option>
                    <option value="5">June</option>
                    <option value="6">July</option>
                    <option value="7">August</option>
                    <option value="8">September</option>
                    <option value="9">October</option>
                    <option value="10">November</option>
                    <option value="11">December</option>
                </select>
            </div>

            <!-- Calendar Grid -->
            <div class="mb-4">
                <!-- Weekday Headers -->
                <div class="grid grid-cols-7 gap-1 mb-2">
                    <div class="text-center text-xs font-semibold text-base-content/50 py-1">Sun</div>
                    <div class="text-center text-xs font-semibold text-base-content/50 py-1">Mon</div>
                    <div class="text-center text-xs font-semibold text-base-content/50 py-1">Tue</div>
                    <div class="text-center text-xs font-semibold text-base-content/50 py-1">Wed</div>
                    <div class="text-center text-xs font-semibold text-base-content/50 py-1">Thu</div>
                    <div class="text-center text-xs font-semibold text-base-content/50 py-1">Fri</div>
                    <div class="text-center text-xs font-semibold text-base-content/50 py-1">Sat</div>
                </div>
                <!-- Days Grid -->
                <div class="grid grid-cols-7 gap-1">
                    <template x-for="day in calendarDays" :key="day.key">
                        <button type="button"
                                @click="selectDay(day)"
                                :disabled="day.disabled"
                                :class="{
                                    'bg-primary text-white shadow-lg shadow-primary/40': day.isSelected,
                                    'border-2 border-primary bg-primary/5': day.isToday && !day.isSelected,
                                    'text-base-content/30': day.isOtherMonth,
                                    'text-base-content/30 cursor-not-allowed': day.disabled,
                                    'hover:bg-primary/10': !day.disabled && !day.isSelected
                                }"
                                class="w-9 h-9 flex items-center justify-center rounded-lg text-sm font-medium transition-all">
                            <span x-text="day.date"></span>
                        </button>
                    </template>
                </div>
            </div>

            <!-- Time Picker -->
            @if($showTime)
            <div class="flex items-center gap-2 pt-3 border-t border-base-200">
                <span class="icon-[tabler--clock] size-5 text-base-content/50"></span>
                <input type="number" x-model="hour" min="1" max="12" class="input input-bordered input-sm w-16 text-center">
                <span class="text-lg font-bold">:</span>
                <input type="number" x-model="minute" min="0" max="59" class="input input-bordered input-sm w-16 text-center">
                <select x-model="ampm" class="select select-bordered select-sm">
                    <option value="AM">AM</option>
                    <option value="PM">PM</option>
                </select>
            </div>
            @endif

            <!-- Actions -->
            <div class="flex justify-between items-center mt-4 pt-3 border-t border-base-200">
                <button type="button" @click="clear()" class="btn btn-ghost btn-sm text-error">
                    <span class="icon-[tabler--x] size-4"></span>
                    Clear
                </button>
                <div class="flex gap-2">
                    <button type="button" @click="close()" class="btn btn-ghost btn-sm">Cancel</button>
                    <button type="button" @click="apply()" class="btn btn-primary btn-sm">Apply</button>
                </div>
            </div>
        </div>
    </div>
</div>

@once
@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('datePicker', (pickerId, initialValue, showTime) => ({
        isOpen: false,
        selectedDate: null,
        viewYear: new Date().getFullYear(),
        viewMonth: new Date().getMonth(),
        years: [],
        calendarDays: [],
        hour: '12',
        minute: '00',
        ampm: 'PM',
        displayValue: '',
        formValue: '',
        hasValue: false,
        showTime: showTime,

        init() {
            // Generate years array
            const currentYear = new Date().getFullYear();
            for (let y = currentYear; y <= currentYear + 10; y++) {
                this.years.push(y);
            }

            // Parse initial value if provided
            if (initialValue) {
                const parsed = new Date(initialValue);
                if (!isNaN(parsed)) {
                    this.selectedDate = parsed;
                    this.viewYear = parsed.getFullYear();
                    this.viewMonth = parsed.getMonth();

                    if (this.showTime) {
                        let hours = parsed.getHours();
                        this.ampm = hours >= 12 ? 'PM' : 'AM';
                        hours = hours % 12 || 12;
                        this.hour = String(hours);
                        this.minute = String(parsed.getMinutes()).padStart(2, '0');
                    }

                    this.updateDisplay();
                }
            }

            this.renderCalendar();
        },

        toggle() {
            this.isOpen = !this.isOpen;
            if (this.isOpen) {
                this.renderCalendar();
            }
        },

        close() {
            this.isOpen = false;
        },

        renderCalendar() {
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            const year = parseInt(this.viewYear);
            const month = parseInt(this.viewMonth);

            const firstDay = new Date(year, month, 1);
            const startDayOfWeek = firstDay.getDay();
            const lastDay = new Date(year, month + 1, 0);
            const daysInMonth = lastDay.getDate();
            const prevMonthLastDay = new Date(year, month, 0).getDate();

            this.calendarDays = [];

            // Previous month days
            for (let i = startDayOfWeek - 1; i >= 0; i--) {
                const day = prevMonthLastDay - i;
                this.calendarDays.push({
                    key: `prev-${day}`,
                    date: day,
                    isOtherMonth: true,
                    disabled: true,
                    isToday: false,
                    isSelected: false
                });
            }

            // Current month days
            for (let day = 1; day <= daysInMonth; day++) {
                const date = new Date(year, month, day);
                const isToday = date.getTime() === today.getTime();
                const isPast = date < today;
                const isSelected = this.selectedDate &&
                    this.selectedDate.getFullYear() === year &&
                    this.selectedDate.getMonth() === month &&
                    this.selectedDate.getDate() === day;

                this.calendarDays.push({
                    key: `curr-${day}`,
                    date: day,
                    fullDate: date,
                    isOtherMonth: false,
                    disabled: isPast,
                    isToday: isToday,
                    isSelected: isSelected
                });
            }

            // Next month days
            const totalCells = Math.ceil((startDayOfWeek + daysInMonth) / 7) * 7;
            const nextMonthDays = totalCells - (startDayOfWeek + daysInMonth);
            for (let day = 1; day <= nextMonthDays; day++) {
                this.calendarDays.push({
                    key: `next-${day}`,
                    date: day,
                    isOtherMonth: true,
                    disabled: true,
                    isToday: false,
                    isSelected: false
                });
            }
        },

        selectDay(day) {
            if (day.disabled || day.isOtherMonth) return;
            this.selectedDate = new Date(this.viewYear, this.viewMonth, day.date);
            this.renderCalendar();
        },

        apply() {
            if (!this.selectedDate) {
                this.close();
                return;
            }

            if (this.showTime) {
                let hours = parseInt(this.hour) || 12;
                const minutes = parseInt(this.minute) || 0;

                if (hours < 1) hours = 1;
                if (hours > 12) hours = 12;

                if (this.ampm === 'PM' && hours !== 12) {
                    hours += 12;
                } else if (this.ampm === 'AM' && hours === 12) {
                    hours = 0;
                }

                this.selectedDate.setHours(hours, minutes, 0, 0);
            }

            this.updateDisplay();
            this.close();
        },

        updateDisplay() {
            if (!this.selectedDate) {
                this.displayValue = '';
                this.formValue = '';
                this.hasValue = false;
                return;
            }

            // Format for display
            const options = this.showTime ? {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: 'numeric',
                minute: '2-digit',
                hour12: true
            } : {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            };
            this.displayValue = this.selectedDate.toLocaleDateString('en-US', options);

            // Format for form submission
            const year = this.selectedDate.getFullYear();
            const month = String(this.selectedDate.getMonth() + 1).padStart(2, '0');
            const day = String(this.selectedDate.getDate()).padStart(2, '0');

            if (this.showTime) {
                const hour24 = String(this.selectedDate.getHours()).padStart(2, '0');
                const min = String(this.selectedDate.getMinutes()).padStart(2, '0');
                this.formValue = `${year}-${month}-${day} ${hour24}:${min}`;
            } else {
                this.formValue = `${year}-${month}-${day}`;
            }

            this.hasValue = true;
        },

        clear() {
            this.selectedDate = null;
            this.displayValue = '';
            this.formValue = '';
            this.hasValue = false;
            this.hour = '12';
            this.minute = '00';
            this.ampm = 'PM';
            this.renderCalendar();
            this.close();
        }
    }));
});
</script>
@endpush
@endonce
