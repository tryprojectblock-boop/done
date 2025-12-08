@extends('layouts.app')

@section('content')
<div class="p-4 md:p-6">
    <div class="max-w-7xl mx-auto">
        <!-- Page Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-base-content">Calendar</h1>
                <p class="text-base-content/60">View and manage task schedules</p>
            </div>

            <div class="flex items-center gap-2">
                <!-- View Toggle -->
                <div class="join">
                    <a href="{{ route('calendar.index', array_merge(request()->query(), ['view' => 'list'])) }}"
                       class="join-item btn btn-sm {{ $view === 'list' ? 'btn-primary' : 'btn-ghost' }}">
                        <span class="icon-[tabler--list] size-4"></span>
                        <span class="hidden sm:inline">List</span>
                    </a>
                    <a href="{{ route('calendar.index', array_merge(request()->query(), ['view' => 'calendar'])) }}"
                       class="join-item btn btn-sm {{ $view === 'calendar' ? 'btn-primary' : 'btn-ghost' }}">
                        <span class="icon-[tabler--calendar] size-4"></span>
                        <span class="hidden sm:inline">Calendar</span>
                    </a>
                </div>

                @if(auth()->user()->canSyncGoogleCalendar())
                <!-- Sync with Google Button -->
                <button type="button" onclick="syncGoogleCalendar()" class="btn btn-ghost btn-sm" id="googleSyncBtn">
                    <span class="icon-[tabler--brand-google] size-4"></span>
                    <span class="hidden sm:inline">Sync Google</span>
                </button>
                @elseif(auth()->user()->companyHasGoogleSyncEnabled() && !auth()->user()->hasGoogleConnected())
                <!-- Connect Google Button -->
                <a href="{{ route('google.connect') }}" class="btn btn-ghost btn-sm">
                    <span class="icon-[tabler--brand-google] size-4"></span>
                    <span class="hidden sm:inline">Connect Google</span>
                </a>
                @endif

                <!-- Add Task Button -->
                <a href="{{ route('tasks.create') }}" class="btn btn-primary btn-sm">
                    <span class="icon-[tabler--plus] size-4"></span>
                    <span class="hidden sm:inline">Add Task</span>
                </a>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="card bg-base-100 shadow">
                <div class="card-body p-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center">
                            <span class="icon-[tabler--calendar-event] size-5 text-primary"></span>
                        </div>
                        <div>
                            <div class="text-2xl font-bold">{{ $stats['total'] }}</div>
                            <div class="text-xs text-base-content/60">Total Tasks</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card bg-base-100 shadow">
                <div class="card-body p-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-error/10 flex items-center justify-center">
                            <span class="icon-[tabler--alert-circle] size-5 text-error"></span>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-error">{{ $stats['overdue'] }}</div>
                            <div class="text-xs text-base-content/60">Overdue</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card bg-base-100 shadow">
                <div class="card-body p-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-warning/10 flex items-center justify-center">
                            <span class="icon-[tabler--clock] size-5 text-warning"></span>
                        </div>
                        <div>
                            <div class="text-2xl font-bold">{{ $stats['upcoming'] }}</div>
                            <div class="text-xs text-base-content/60">Upcoming</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card bg-base-100 shadow">
                <div class="card-body p-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-success/10 flex items-center justify-center">
                            <span class="icon-[tabler--check] size-5 text-success"></span>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-success">{{ $stats['completed'] }}</div>
                            <div class="text-xs text-base-content/60">Completed</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card bg-base-100 shadow mb-6">
            <div class="card-body p-4">
                <form action="{{ route('calendar.index') }}" method="GET" class="flex flex-col md:flex-row gap-4">
                    <input type="hidden" name="view" value="{{ $view }}">

                    <!-- Month/Year Navigation -->
                    <div class="flex items-center gap-2">
                        @php
                            $prevMonth = $startOfMonth->copy()->subMonth();
                            $nextMonth = $startOfMonth->copy()->addMonth();
                        @endphp
                        <a href="{{ route('calendar.index', array_merge(request()->query(), ['month' => $prevMonth->month, 'year' => $prevMonth->year])) }}"
                           class="btn btn-ghost btn-sm btn-square">
                            <span class="icon-[tabler--chevron-left] size-5"></span>
                        </a>
                        <select name="month" class="select select-bordered select-sm" onchange="this.form.submit()">
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ $filters['month'] == $m ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::create(null, $m, 1)->format('F') }}
                                </option>
                            @endfor
                        </select>
                        <select name="year" class="select select-bordered select-sm" onchange="this.form.submit()">
                            @for($y = now()->year - 2; $y <= now()->year + 2; $y++)
                                <option value="{{ $y }}" {{ $filters['year'] == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                        <a href="{{ route('calendar.index', array_merge(request()->query(), ['month' => $nextMonth->month, 'year' => $nextMonth->year])) }}"
                           class="btn btn-ghost btn-sm btn-square">
                            <span class="icon-[tabler--chevron-right] size-5"></span>
                        </a>
                        <a href="{{ route('calendar.index', ['view' => $view]) }}"
                           class="btn btn-ghost btn-sm">
                            Today
                        </a>
                    </div>

                    <div class="flex-1"></div>

                    <!-- Workspace Filter -->
                    <select name="workspace_id" class="select select-bordered select-sm w-full md:w-40" onchange="this.form.submit()">
                        <option value="">All Workspaces</option>
                        @foreach($workspaces as $workspace)
                            <option value="{{ $workspace->id }}" {{ $filters['workspace_id'] == $workspace->id ? 'selected' : '' }}>
                                {{ $workspace->name }}
                            </option>
                        @endforeach
                    </select>

                    <!-- Assignee Filter -->
                    <select name="assignee_id" class="select select-bordered select-sm w-full md:w-40" onchange="this.form.submit()">
                        <option value="">All Assignees</option>
                        @foreach($teamMembers as $member)
                            <option value="{{ $member->id }}" {{ $filters['assignee_id'] == $member->id ? 'selected' : '' }}>
                                {{ $member->name }}
                            </option>
                        @endforeach
                    </select>

                    <!-- Status Filter -->
                    <select name="status" class="select select-bordered select-sm w-full md:w-32" onchange="this.form.submit()">
                        <option value="">All Status</option>
                        <option value="open" {{ $filters['status'] === 'open' ? 'selected' : '' }}>Open</option>
                        <option value="closed" {{ $filters['status'] === 'closed' ? 'selected' : '' }}>Closed</option>
                        <option value="overdue" {{ $filters['status'] === 'overdue' ? 'selected' : '' }}>Overdue</option>
                    </select>

                    @if($filters['workspace_id'] || $filters['assignee_id'] || $filters['status'])
                        <a href="{{ route('calendar.index', ['view' => $view, 'month' => $filters['month'], 'year' => $filters['year']]) }}"
                           class="btn btn-ghost btn-sm text-error">
                            <span class="icon-[tabler--x] size-4"></span>
                            Clear
                        </a>
                    @endif
                </form>
            </div>
        </div>

        <!-- List View -->
        @if($view === 'list')
            @include('calendar::partials.list-view')
        @else
            @include('calendar::partials.calendar-view')
        @endif
    </div>
</div>

<!-- Task Detail Drawer -->
@include('calendar::partials.task-drawer')

@push('scripts')
<script>
async function syncGoogleCalendar() {
    const btn = document.getElementById('googleSyncBtn');
    const originalContent = btn.innerHTML;

    // Show loading state
    btn.disabled = true;
    btn.innerHTML = '<span class="loading loading-spinner loading-xs"></span><span class="hidden sm:inline">Syncing...</span>';

    try {
        const response = await fetch('{{ route("google.sync") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        });

        const data = await response.json();

        if (data.success) {
            // Show success toast
            showToast(data.message, 'success');
            // Reload page to show new events
            if (data.data.synced_from_google > 0 || data.data.synced_to_google > 0) {
                setTimeout(() => window.location.reload(), 1500);
            }
        } else {
            showToast(data.message || 'Sync failed', 'error');
        }
    } catch (error) {
        console.error('Sync error:', error);
        showToast('Failed to sync with Google Calendar', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalContent;
    }
}

function showToast(message, type = 'info') {
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `alert alert-${type === 'success' ? 'success' : type === 'error' ? 'error' : 'info'} fixed bottom-4 right-4 w-auto max-w-md z-50 shadow-lg`;
    toast.innerHTML = `
        <span class="icon-[tabler--${type === 'success' ? 'check' : type === 'error' ? 'alert-circle' : 'info-circle'}] size-5"></span>
        <span>${message}</span>
    `;
    document.body.appendChild(toast);

    // Remove after 4 seconds
    setTimeout(() => {
        toast.remove();
    }, 4000);
}
</script>
@endpush

@endsection
