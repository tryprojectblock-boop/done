@extends('layouts.app')

@section('content')
<div class="p-4 md:p-6">
    <div class="max-w mx-auto">
        <!-- Page Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-base-content">Idea Board</h1>
                <p class="text-base-content/60">Share and discuss ideas with your team</p>
            </div>
            <a href="{{ route('ideas.create') }}" class="btn btn-primary">
                <span class="icon-[tabler--bulb] size-5"></span>
                New Idea
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
                <form action="{{ route('ideas.index') }}" method="GET" class="flex flex-col md:flex-row gap-4">
                    <!-- Search -->
                    <div class="flex-1">
                        <div class="relative">
                            <span class="icon-[tabler--search] size-5 absolute left-3 top-1/2 -translate-y-1/2 text-base-content/50"></span>
                            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search ideas..." class="input input-bordered w-full pl-10" />
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
                    <select name="status" class="select select-bordered w-full md:w-40" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        @foreach($statuses as $value => $label)
                            <option value="{{ $value }}" {{ ($filters['status'] ?? '') == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    <!-- Priority Filter -->
                    <select name="priority" class="select select-bordered w-full md:w-36" onchange="this.form.submit()">
                        <option value="">All Priorities</option>
                        @foreach($priorities as $value => $label)
                            <option value="{{ $value }}" {{ ($filters['priority'] ?? '') == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-ghost">
                        <span class="icon-[tabler--filter] size-5"></span>
                        Filter
                    </button>
                    @if(!empty(array_filter($filters ?? [])))
                        <a href="{{ route('ideas.index') }}" class="btn btn-ghost text-error">
                            <span class="icon-[tabler--x] size-5"></span>
                            Clear
                        </a>
                    @endif
                </form>
            </div>
        </div>

        <!-- View Toggle & Sort Options -->
        <div class="flex items-center justify-between mb-4">
            <div class="text-sm text-base-content/60">
                {{ $ideas->total() }} {{ Str::plural('idea', $ideas->total()) }} found
            </div>
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2">
                    <select class="select select-bordered select-sm" onchange="window.location.href = this.value">
                        <option value="{{ route('ideas.index', array_merge(request()->query(), ['sort' => 'created_at', 'direction' => 'desc'])) }}" {{ ($filters['sort'] ?? 'created_at') === 'created_at' && ($filters['direction'] ?? 'desc') === 'desc' ? 'selected' : '' }}>
                            Newest First
                        </option>
                        <option value="{{ route('ideas.index', array_merge(request()->query(), ['sort' => 'created_at', 'direction' => 'asc'])) }}" {{ ($filters['sort'] ?? '') === 'created_at' && ($filters['direction'] ?? '') === 'asc' ? 'selected' : '' }}>
                            Oldest First
                        </option>
                        <option value="{{ route('ideas.index', array_merge(request()->query(), ['sort' => 'votes_count', 'direction' => 'desc'])) }}" {{ ($filters['sort'] ?? '') === 'votes_count' ? 'selected' : '' }}>
                            Most Voted
                        </option>
                        <option value="{{ route('ideas.index', array_merge(request()->query(), ['sort' => 'comments_count', 'direction' => 'desc'])) }}" {{ ($filters['sort'] ?? '') === 'comments_count' ? 'selected' : '' }}>
                            Most Discussed
                        </option>
                    </select>
                </div>
                <div class="flex items-center gap-1 border border-base-300 rounded-lg p-1 bg-base-100">
                    <a href="{{ route('ideas.index', array_merge(request()->query(), ['view' => 'card'])) }}"
                       class="btn btn-sm {{ $viewMode === 'card' ? 'btn-primary' : 'btn-ghost' }}">
                        <span class="icon-[tabler--layout-grid] size-4"></span>
                    </a>
                    <a href="{{ route('ideas.index', array_merge(request()->query(), ['view' => 'table'])) }}"
                       class="btn btn-sm {{ $viewMode === 'table' ? 'btn-primary' : 'btn-ghost' }}">
                        <span class="icon-[tabler--list] size-4"></span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Ideas List -->
        @if($ideas->isEmpty())
            <div class="card bg-base-100 shadow">
                <div class="card-body text-center py-12">
                    <div class="text-base-content/50">
                        <span class="icon-[tabler--bulb] size-12 block mx-auto mb-4"></span>
                        <p class="text-lg font-medium">No ideas yet</p>
                        <p class="text-sm">
                            @if(!empty(array_filter($filters ?? [])))
                                Try adjusting your search or filters
                            @else
                                Share your first idea to get started
                            @endif
                        </p>
                    </div>
                    <div class="mt-4 flex justify-center gap-2">
                        @if(!empty(array_filter($filters ?? [])))
                            <a href="{{ route('ideas.index') }}" class="btn btn-ghost">Clear Filters</a>
                        @endif
                        <a href="{{ route('ideas.create') }}" class="btn btn-primary">
                            <span class="icon-[tabler--plus] size-5"></span>
                            Share an Idea
                        </a>
                    </div>
                </div>
            </div>
        @else
            @if($viewMode === 'card')
                @include('idea::partials.idea-cards', ['ideas' => $ideas])
            @else
                @include('idea::partials.idea-table', ['ideas' => $ideas])
            @endif

            <!-- Pagination -->
            @if($ideas->hasPages())
                <div class="mt-6">
                    {{ $ideas->withQueryString()->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
