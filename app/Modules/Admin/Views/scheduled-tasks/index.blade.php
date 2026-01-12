@extends('admin::layouts.app')

@section('title', 'Scheduled Tasks')
@section('page-title', 'Scheduled Tasks')

@section('content')
<div class="max-w mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-base-content">Scheduled Tasks</h1>
            <p class="text-base-content/60">Manage cron jobs and automated background tasks</p>
        </div>
        <a href="{{ route('backoffice.scheduled-tasks.create') }}" class="btn btn-primary">
            <span class="icon-[tabler--plus] size-5"></span>
            Add Task
        </a>
    </div>

    @include('admin::partials.alerts')

    <!-- Cron Setup Info -->
    <div class="alert alert-info">
        <span class="icon-[tabler--info-circle] size-5"></span>
        <div>
            <p class="font-medium">Cron Setup Required</p>
            <p class="text-sm opacity-80">Add this cron job to your server (runs every minute):</p>
            <code class="block mt-2 p-2 bg-base-100 rounded text-xs select-all">* * * * * cd {{ base_path() }} && php artisan schedule:run >> /dev/null 2>&1</code>
        </div>
    </div>

    <!-- Stats -->
    <div class="stats shadow w-full">
        <div class="stat">
            <div class="stat-figure text-primary">
                <span class="icon-[tabler--clock-play] size-8"></span>
            </div>
            <div class="stat-title">Total Tasks</div>
            <div class="stat-value text-primary">{{ $tasks->count() }}</div>
        </div>
        <div class="stat">
            <div class="stat-figure text-success">
                <span class="icon-[tabler--check] size-8"></span>
            </div>
            <div class="stat-title">Active</div>
            <div class="stat-value text-success">{{ $tasks->where('is_active', true)->count() }}</div>
        </div>
        <div class="stat">
            <div class="stat-figure text-warning">
                <span class="icon-[tabler--player-pause] size-8"></span>
            </div>
            <div class="stat-title">Disabled</div>
            <div class="stat-value text-warning">{{ $tasks->where('is_active', false)->count() }}</div>
        </div>
    </div>

    <!-- Tasks List -->
    @if($tasks->isEmpty())
        <div class="card bg-base-100 shadow">
            <div class="card-body text-center py-12">
                <span class="icon-[tabler--clock-off] size-16 text-base-content/20 mx-auto mb-4"></span>
                <h3 class="text-lg font-medium text-base-content/60">No Scheduled Tasks</h3>
                <p class="text-base-content/40 mb-4">Create your first scheduled task to automate background processes.</p>
                <a href="{{ route('backoffice.scheduled-tasks.create') }}" class="btn btn-primary btn-sm">
                    <span class="icon-[tabler--plus] size-4"></span>
                    Add Task
                </a>
            </div>
        </div>
    @else
        <div class="space-y-4">
            @foreach($tasks as $task)
                <div class="card bg-base-100 shadow {{ !$task->is_active ? 'opacity-60' : '' }}">
                    <div class="card-body">
                        <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-4">
                            <!-- Task Info -->
                            <div class="flex-1">
                                <div class="flex items-start gap-4">
                                    <div class="w-12 h-12 rounded-lg flex items-center justify-center flex-shrink-0 {{ $task->is_active ? 'bg-success/10' : 'bg-base-200' }}">
                                        <span class="icon-[tabler--clock-play] size-6 {{ $task->is_active ? 'text-success' : 'text-base-content/40' }}"></span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h3 class="font-bold text-lg text-base-content flex items-center gap-2 flex-wrap">
                                            {{ $task->display_name }}
                                            @if(!$task->is_active)
                                                <span class="badge badge-ghost badge-sm">Disabled</span>
                                            @else
                                                <span class="badge badge-success badge-sm">Active</span>
                                            @endif
                                        </h3>
                                        <p class="text-sm text-base-content/60 mt-1">{{ $task->description }}</p>

                                        <!-- Meta Info -->
                                        <div class="flex flex-wrap items-center gap-x-4 gap-y-2 mt-3 text-sm">
                                            <div class="flex items-center gap-1 text-base-content/60">
                                                <span class="icon-[tabler--calendar-time] size-4"></span>
                                                <span>{{ $task->schedule_description }}</span>
                                            </div>

                                            @if($task->last_run_at)
                                                <div class="flex items-center gap-1 {{ $task->last_run_status === 'success' ? 'text-success' : 'text-error' }}">
                                                    <span class="icon-[tabler--{{ $task->last_run_status === 'success' ? 'check' : 'x' }}] size-4"></span>
                                                    <span>Last: {{ $task->last_run_at->diffForHumans() }}</span>
                                                    @if($task->last_run_duration)
                                                        <span class="text-base-content/40">({{ $task->last_run_duration }}s)</span>
                                                    @endif
                                                </div>
                                            @else
                                                <div class="flex items-center gap-1 text-base-content/40">
                                                    <span class="icon-[tabler--clock] size-4"></span>
                                                    <span>Never run</span>
                                                </div>
                                            @endif

                                            <div class="flex items-center gap-1 text-base-content/40">
                                                <span class="icon-[tabler--terminal] size-4"></span>
                                                <code class="text-xs font-mono">{{ $task->full_command }}</code>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Last Run Output -->
                                @if($task->last_run_output)
                                    <div class="collapse collapse-arrow bg-base-200 mt-4">
                                        <input type="checkbox" />
                                        <div class="collapse-title text-sm font-medium py-2 min-h-0">
                                            <span class="icon-[tabler--code] size-4 inline-block mr-1"></span>
                                            Last Run Output
                                        </div>
                                        <div class="collapse-content">
                                            <pre class="text-xs bg-base-300 p-3 rounded overflow-x-auto whitespace-pre-wrap max-h-48">{{ $task->last_run_output }}</pre>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <!-- Actions -->
                            <div class="flex flex-wrap items-center gap-2 lg:flex-col lg:items-end">
                                <!-- Run Now -->
                                <form action="{{ route('backoffice.scheduled-tasks.run', $task) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="btn btn-primary btn-sm gap-1">
                                        <span class="icon-[tabler--player-play] size-4"></span>
                                        Run Now
                                    </button>
                                </form>

                                <!-- Toggle -->
                                <form action="{{ route('backoffice.scheduled-tasks.toggle', $task) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="btn btn-ghost btn-sm gap-1 {{ $task->is_active ? 'text-warning' : 'text-success' }}">
                                        <span class="icon-[tabler--{{ $task->is_active ? 'player-pause' : 'player-play' }}] size-4"></span>
                                        {{ $task->is_active ? 'Disable' : 'Enable' }}
                                    </button>
                                </form>

                                <!-- Settings -->
                                <button type="button" class="btn btn-ghost btn-sm" onclick="document.getElementById('modal-{{ $task->id }}').showModal()">
                                    <span class="icon-[tabler--settings] size-4"></span>
                                    Settings
                                </button>

                                <!-- Delete -->
                                <button type="button" class="btn btn-ghost btn-sm text-error" onclick="document.getElementById('delete-{{ $task->id }}').showModal()">
                                    <span class="icon-[tabler--trash] size-4"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Settings Modal -->
                <dialog id="modal-{{ $task->id }}" class="modal">
                    <div class="modal-box max-w-lg">
                        <form method="dialog">
                            <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">
                                <span class="icon-[tabler--x] size-5"></span>
                            </button>
                        </form>
                        <h3 class="font-bold text-lg mb-4">
                            <span class="icon-[tabler--settings] size-5 inline-block mr-2"></span>
                            {{ $task->display_name }}
                        </h3>

                        <form action="{{ route('backoffice.scheduled-tasks.update', $task) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <!-- Display Name -->
                            <div class="form-control mb-4">
                                <label class="label">
                                    <span class="label-text font-medium">Display Name</span>
                                </label>
                                <input type="text" name="display_name" value="{{ $task->display_name }}" class="input input-bordered" required>
                            </div>

                            <!-- Description -->
                            <div class="form-control mb-4">
                                <label class="label">
                                    <span class="label-text font-medium">Description</span>
                                </label>
                                <textarea name="description" class="textarea textarea-bordered" rows="2">{{ $task->description }}</textarea>
                            </div>

                            <!-- Frequency -->
                            <div class="form-control mb-4">
                                <label class="label">
                                    <span class="label-text font-medium">Frequency</span>
                                </label>
                                <select name="frequency" class="select select-bordered" onchange="toggleOptions(this, {{ $task->id }})">
                                    @foreach($frequencyOptions as $value => $label)
                                        <option value="{{ $value }}" {{ $task->frequency === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Time -->
                            <div class="form-control mb-4" id="time-{{ $task->id }}" style="{{ $task->frequency === 'hourly' ? 'display:none' : '' }}">
                                <label class="label">
                                    <span class="label-text font-medium">Time</span>
                                </label>
                                <input type="time" name="time" value="{{ $task->time }}" class="input input-bordered">
                            </div>

                            <!-- Day of Week -->
                            <div class="form-control mb-4" id="dow-{{ $task->id }}" style="{{ $task->frequency !== 'weekly' ? 'display:none' : '' }}">
                                <label class="label">
                                    <span class="label-text font-medium">Day of Week</span>
                                </label>
                                <select name="day_of_week" class="select select-bordered">
                                    @foreach($dayOfWeekOptions as $value => $label)
                                        <option value="{{ $value }}" {{ $task->day_of_week === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Day of Month -->
                            <div class="form-control mb-4" id="dom-{{ $task->id }}" style="{{ $task->frequency !== 'monthly' ? 'display:none' : '' }}">
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

                            <!-- Active -->
                            <div class="form-control mb-6">
                                <label class="label cursor-pointer justify-start gap-3">
                                    <input type="checkbox" name="is_active" value="1" {{ $task->is_active ? 'checked' : '' }} class="toggle toggle-success">
                                    <span class="label-text font-medium">Task Enabled</span>
                                </label>
                            </div>

                            <div class="modal-action">
                                <button type="button" class="btn btn-ghost" onclick="document.getElementById('modal-{{ $task->id }}').close()">Cancel</button>
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                            </div>
                        </form>
                    </div>
                    <form method="dialog" class="modal-backdrop">
                        <button>close</button>
                    </form>
                </dialog>

                <!-- Delete Confirmation Modal -->
                <dialog id="delete-{{ $task->id }}" class="modal">
                    <div class="modal-box">
                        <h3 class="font-bold text-lg text-error">
                            <span class="icon-[tabler--alert-triangle] size-5 inline-block mr-2"></span>
                            Delete Task
                        </h3>
                        <p class="py-4">Are you sure you want to delete <strong>{{ $task->display_name }}</strong>? This action cannot be undone.</p>
                        <div class="modal-action">
                            <form method="dialog">
                                <button class="btn btn-ghost">Cancel</button>
                            </form>
                            <form action="{{ route('backoffice.scheduled-tasks.destroy', $task) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-error">
                                    <span class="icon-[tabler--trash] size-4"></span>
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>
                    <form method="dialog" class="modal-backdrop">
                        <button>close</button>
                    </form>
                </dialog>
            @endforeach
        </div>
    @endif
</div>

@push('scripts')
<script>
function toggleOptions(select, taskId) {
    const frequency = select.value;
    document.getElementById('time-' + taskId).style.display = frequency === 'hourly' ? 'none' : '';
    document.getElementById('dow-' + taskId).style.display = frequency === 'weekly' ? '' : 'none';
    document.getElementById('dom-' + taskId).style.display = frequency === 'monthly' ? '' : 'none';
}
</script>
@endpush
@endsection
