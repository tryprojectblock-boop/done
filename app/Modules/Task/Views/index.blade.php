@extends('layouts.app')

@section('content')
<div class="p-4 md:p-6">
    <div class="max-w-7xl mx-auto">
        <!-- Page Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-base-content">My Tasks</h1>
                <p class="text-base-content/60">Tasks assigned to you or you're watching</p>
            </div>
            <a href="{{ route('tasks.create') }}" class="btn btn-primary">
                <span class="icon-[tabler--plus] size-5"></span>
                Add Task
            </a>
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

        <!-- Filters & Search -->
        <div class="card bg-base-100 shadow mb-6">
            <div class="card-body p-4">
                <form action="{{ route('tasks.index') }}" method="GET" class="flex flex-col md:flex-row gap-4">
                    <!-- Search -->
                    <div class="flex-1">
                        <div class="relative">
                            <span class="icon-[tabler--search] size-5 absolute left-3 top-1/2 -translate-y-1/2 text-base-content/50"></span>
                            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search tasks..." class="input input-bordered w-full pl-10" />
                        </div>
                    </div>
                    <!-- Workspace Filter -->
                    <select name="workspace_id" class="select select-bordered w-full md:w-48" onchange="this.form.submit()">
                        <option value="">All Workspaces</option>
                        @foreach($workspaces as $workspace)
                            <option value="{{ $workspace->id }}" {{ ($filters['workspace_id'] ?? '') == $workspace->id ? 'selected' : '' }}>
                                {{ $workspace->name }}
                            </option>
                        @endforeach
                    </select>
                    <!-- Status Filter -->
                    <select name="status_id" class="select select-bordered w-full md:w-40" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status->id }}" {{ ($filters['status_id'] ?? '') == $status->id ? 'selected' : '' }}>
                                {{ $status->name }}
                            </option>
                        @endforeach
                    </select>
                    <!-- Priority Filter -->
                    <select name="priority" class="select select-bordered w-full md:w-36" onchange="this.form.submit()">
                        <option value="">All Priorities</option>
                        @foreach(\App\Modules\Task\Enums\TaskPriority::cases() as $priority)
                            <option value="{{ $priority->value }}" {{ ($filters['priority'] ?? '') == $priority->value ? 'selected' : '' }}>
                                {{ $priority->label() }}
                            </option>
                        @endforeach
                    </select>
                    <!-- Open/Closed Filter -->
                    <select name="is_closed" class="select select-bordered w-full md:w-32" onchange="this.form.submit()">
                        <option value="0" {{ isset($filters['is_closed']) && $filters['is_closed'] === false ? 'selected' : '' }}>Open</option>
                        <option value="1" {{ isset($filters['is_closed']) && $filters['is_closed'] === true ? 'selected' : '' }}>Closed</option>
                        <option value="" {{ !isset($filters['is_closed']) ? 'selected' : '' }}>All</option>
                    </select>
                    <button type="submit" class="btn btn-ghost">
                        <span class="icon-[tabler--filter] size-5"></span>
                        Filter
                    </button>
                    @if(!empty(array_filter($filters ?? [])))
                        <a href="{{ route('tasks.index') }}" class="btn btn-ghost text-error">
                            <span class="icon-[tabler--x] size-5"></span>
                            Clear
                        </a>
                    @endif
                </form>
            </div>
        </div>

        <!-- View Toggle -->
        <div class="flex items-center justify-between mb-4">
            <div class="text-sm text-base-content/60">
                {{ $tasks->total() }} {{ Str::plural('task', $tasks->total()) }} found
            </div>
            <div class="flex items-center gap-1 border border-base-300 rounded-lg p-1 bg-base-100">
                <a href="{{ route('tasks.index', array_merge(request()->query(), ['view' => 'card'])) }}"
                   class="btn btn-sm {{ $viewMode === 'card' ? 'btn-primary' : 'btn-ghost' }}">
                    <span class="icon-[tabler--layout-grid] size-4"></span>
                </a>
                <a href="{{ route('tasks.index', array_merge(request()->query(), ['view' => 'table'])) }}"
                   class="btn btn-sm {{ $viewMode === 'table' ? 'btn-primary' : 'btn-ghost' }}">
                    <span class="icon-[tabler--list] size-4"></span>
                </a>
            </div>
        </div>

        <!-- Tasks Content -->
        @if($tasks->isEmpty())
            <div class="card bg-base-100 shadow">
                <div class="card-body text-center py-12">
                    <div class="text-base-content/50">
                        <span class="icon-[tabler--checkbox] size-12 block mx-auto mb-4"></span>
                        <p class="text-lg font-medium">No tasks found</p>
                        <p class="text-sm">
                            @if(!empty(array_filter($filters ?? [])))
                                Try adjusting your search or filters
                            @else
                                Create your first task to get started
                            @endif
                        </p>
                    </div>
                    <div class="mt-4 flex justify-center gap-2">
                        @if(!empty(array_filter($filters ?? [])))
                            <a href="{{ route('tasks.index') }}" class="btn btn-ghost">Clear Filters</a>
                        @endif
                        <a href="{{ route('tasks.create') }}" class="btn btn-primary">
                            <span class="icon-[tabler--plus] size-5"></span>
                            Create Task
                        </a>
                    </div>
                </div>
            </div>
        @else
            @if($viewMode === 'card')
                @include('task::partials.task-cards', ['tasks' => $tasks])
            @else
                @include('task::partials.task-table', ['tasks' => $tasks])
            @endif

            <!-- Pagination -->
            @if($tasks->hasPages())
                <div class="mt-6">
                    {{ $tasks->withQueryString()->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
