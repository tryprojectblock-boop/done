@extends('layouts.app')

@section('content')
<div class="min-h-screen p-4 md:p-6">
    <div class="max-w mx-auto">
        <!-- Page Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-base-content">Settings</h1>
            <p class="text-base-content/60">Manage your account and application preferences</p>
        </div>

        <!-- Tabs Navigation -->
        <div class="inline-flex p-1 bg-base-200 rounded-xl mb-6 flex-wrap gap-1">
            <a href="{{ route('settings.index', ['tab' => 'general']) }}"
               class="flex items-center gap-2 px-5 py-2.5 rounded-lg font-medium transition-all duration-200 text-base-content/60 hover:text-primary hover:bg-primary/10">
                <span class="icon-[tabler--settings] size-5"></span>
                <span>General</span>
            </a>
            <a href="{{ route('settings.scheduled-tasks') }}"
               class="flex items-center gap-2 px-5 py-2.5 rounded-lg font-medium transition-all duration-200 bg-primary text-primary-content shadow-sm">
                <span class="icon-[tabler--clock-play] size-5"></span>
                <span>Scheduled Tasks</span>
            </a>
            <a href="{{ route('settings.index', ['tab' => 'marketplace']) }}"
               class="flex items-center gap-2 px-5 py-2.5 rounded-lg font-medium transition-all duration-200 text-base-content/60 hover:text-primary hover:bg-primary/10">
                <span class="icon-[tabler--apps] size-5"></span>
                <span>Marketplace</span>
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success mb-6">
                <span class="icon-[tabler--check] size-5"></span>
                <span class="whitespace-pre-line">{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-error mb-6">
                <span class="icon-[tabler--alert-circle] size-5"></span>
                <span class="whitespace-pre-line">{{ session('error') }}</span>
            </div>
        @endif

        <!-- Cron Setup Info -->
        <div class="alert alert-info mb-6">
            <span class="icon-[tabler--info-circle] size-5"></span>
            <div>
                <p class="font-medium">Cron Setup Required</p>
                <p class="text-sm opacity-80">Add this cron job to your server (Plesk Scheduled Tasks) to run every minute:</p>
                <code class="block mt-2 p-2 bg-base-100 rounded text-xs">* * * * * cd {{ base_path() }} && php artisan schedule:run >> /dev/null 2>&1</code>
            </div>
        </div>

        <!-- Tasks List -->
        <div class="space-y-4">
            @forelse($tasks as $task)
                <div class="card bg-base-100 shadow">
                    <div class="card-body">
                        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                            <!-- Task Info -->
                            <div class="flex-1">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg flex items-center justify-center {{ $task->is_active ? 'bg-success/10' : 'bg-base-200' }}">
                                        <span class="icon-[tabler--clock-play] size-5 {{ $task->is_active ? 'text-success' : 'text-base-content/40' }}"></span>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-base-content flex items-center gap-2">
                                            {{ $task->display_name }}
                                            @if(!$task->is_active)
                                                <span class="badge badge-ghost badge-sm">Disabled</span>
                                            @endif
                                        </h3>
                                        <p class="text-sm text-base-content/60">{{ $task->description }}</p>
                                    </div>
                                </div>

                                <!-- Schedule & Last Run Info -->
                                <div class="flex flex-wrap items-center gap-4 mt-3 text-sm">
                                    <div class="flex items-center gap-1 text-base-content/60">
                                        <span class="icon-[tabler--calendar-time] size-4"></span>
                                        <span>{{ $task->schedule_description }}</span>
                                    </div>
                                    @if($task->last_run_at)
                                        <div class="flex items-center gap-1 {{ $task->last_run_status === 'success' ? 'text-success' : 'text-error' }}">
                                            <span class="icon-[tabler--{{ $task->last_run_status === 'success' ? 'check' : 'x' }}] size-4"></span>
                                            <span>Last run: {{ $task->last_run_at->diffForHumans() }}</span>
                                        </div>
                                    @else
                                        <div class="flex items-center gap-1 text-base-content/40">
                                            <span class="icon-[tabler--clock] size-4"></span>
                                            <span>Never run</span>
                                        </div>
                                    @endif
                                    <div class="flex items-center gap-1 text-base-content/40 text-xs font-mono">
                                        <span class="icon-[tabler--terminal] size-4"></span>
                                        <span>{{ $task->full_command }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center gap-2">
                                <!-- Run Now Button -->
                                <form action="{{ route('settings.scheduled-tasks.run', $task) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="btn btn-primary btn-sm gap-1">
                                        <span class="icon-[tabler--player-play] size-4"></span>
                                        Run Now
                                    </button>
                                </form>

                                <!-- Toggle Button -->
                                <form action="{{ route('settings.scheduled-tasks.toggle', $task) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="btn btn-ghost btn-sm gap-1 {{ $task->is_active ? 'text-warning' : 'text-success' }}">
                                        <span class="icon-[tabler--{{ $task->is_active ? 'player-pause' : 'player-play' }}] size-4"></span>
                                        {{ $task->is_active ? 'Disable' : 'Enable' }}
                                    </button>
                                </form>

                                <!-- Settings Button -->
                                <button type="button" class="btn btn-ghost btn-sm" onclick="document.getElementById('settings-{{ $task->id }}').showModal()">
                                    <span class="icon-[tabler--settings] size-4"></span>
                                </button>
                            </div>
                        </div>

                        <!-- Last Run Output (collapsible) -->
                        @if($task->last_run_output)
                            <div class="collapse collapse-arrow bg-base-200 mt-4">
                                <input type="checkbox" />
                                <div class="collapse-title text-sm font-medium">
                                    View Last Run Output
                                </div>
                                <div class="collapse-content">
                                    <pre class="text-xs bg-base-300 p-3 rounded overflow-x-auto whitespace-pre-wrap">{{ $task->last_run_output }}</pre>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Settings Modal -->
                <dialog id="settings-{{ $task->id }}" class="modal">
                    <div class="modal-box">
                        <form method="dialog">
                            <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">
                                <span class="icon-[tabler--x] size-5"></span>
                            </button>
                        </form>
                        <h3 class="font-bold text-lg mb-4">{{ $task->display_name }} Settings</h3>

                        <form action="{{ route('settings.scheduled-tasks.update', $task) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <!-- Frequency -->
                            <div class="form-control mb-4">
                                <label class="label">
                                    <span class="label-text font-medium">Frequency</span>
                                </label>
                                <select name="frequency" class="select select-bordered" onchange="toggleFrequencyOptions(this, {{ $task->id }})">
                                    @foreach($frequencyOptions as $value => $label)
                                        <option value="{{ $value }}" {{ $task->frequency === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Time (for daily/weekly/monthly) -->
                            <div class="form-control mb-4" id="time-{{ $task->id }}" style="{{ $task->frequency === 'hourly' ? 'display:none' : '' }}">
                                <label class="label">
                                    <span class="label-text font-medium">Time</span>
                                </label>
                                <input type="time" name="time" value="{{ $task->time }}" class="input input-bordered">
                            </div>

                            <!-- Day of Week (for weekly) -->
                            <div class="form-control mb-4" id="day-of-week-{{ $task->id }}" style="{{ $task->frequency !== 'weekly' ? 'display:none' : '' }}">
                                <label class="label">
                                    <span class="label-text font-medium">Day of Week</span>
                                </label>
                                <select name="day_of_week" class="select select-bordered">
                                    @foreach($dayOfWeekOptions as $value => $label)
                                        <option value="{{ $value }}" {{ $task->day_of_week === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Day of Month (for monthly) -->
                            <div class="form-control mb-4" id="day-of-month-{{ $task->id }}" style="{{ $task->frequency !== 'monthly' ? 'display:none' : '' }}">
                                <label class="label">
                                    <span class="label-text font-medium">Day of Month</span>
                                </label>
                                <input type="number" name="day_of_month" value="{{ $task->day_of_month ?? 1 }}" min="1" max="31" class="input input-bordered">
                            </div>

                            <!-- Task-specific Options -->
                            @if($task->name === 'prune-notifications')
                                <div class="form-control mb-4">
                                    <label class="label">
                                        <span class="label-text font-medium">Keep notifications for (days)</span>
                                    </label>
                                    <input type="number" name="options[days]" value="{{ $task->options['days'] ?? 30 }}" min="1" max="365" class="input input-bordered">
                                    <label class="label">
                                        <span class="label-text-alt">Notifications older than this will be deleted</span>
                                    </label>
                                </div>
                            @endif

                            <!-- Active Status -->
                            <div class="form-control mb-6">
                                <label class="label cursor-pointer justify-start gap-3">
                                    <input type="checkbox" name="is_active" value="1" {{ $task->is_active ? 'checked' : '' }} class="toggle toggle-success">
                                    <span class="label-text font-medium">Task Enabled</span>
                                </label>
                            </div>

                            <div class="modal-action">
                                <button type="button" class="btn btn-ghost" onclick="document.getElementById('settings-{{ $task->id }}').close()">Cancel</button>
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                            </div>
                        </form>
                    </div>
                    <form method="dialog" class="modal-backdrop">
                        <button>close</button>
                    </form>
                </dialog>
            @empty
                <div class="card bg-base-100 shadow">
                    <div class="card-body text-center py-12">
                        <span class="icon-[tabler--clock-off] size-16 text-base-content/20 mx-auto mb-4"></span>
                        <h3 class="text-lg font-medium text-base-content/60">No Scheduled Tasks</h3>
                        <p class="text-base-content/40">Scheduled tasks will appear here once configured.</p>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</div>

<script>
function toggleFrequencyOptions(select, taskId) {
    const frequency = select.value;
    const timeDiv = document.getElementById('time-' + taskId);
    const dayOfWeekDiv = document.getElementById('day-of-week-' + taskId);
    const dayOfMonthDiv = document.getElementById('day-of-month-' + taskId);

    // Show/hide time input
    timeDiv.style.display = frequency === 'hourly' ? 'none' : '';

    // Show/hide day of week
    dayOfWeekDiv.style.display = frequency === 'weekly' ? '' : 'none';

    // Show/hide day of month
    dayOfMonthDiv.style.display = frequency === 'monthly' ? '' : 'none';
}
</script>
@endsection
