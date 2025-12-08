@extends('layouts.app')

@section('content')
<div class="p-4 md:p-6">
    <div class="max-w-7xl mx-auto">
        <!-- Page Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-base-content">Discussions</h1>
                <p class="text-base-content/60">Collaborate and share ideas with your team</p>
            </div>
            <a href="{{ route('discussions.create') }}" class="btn btn-primary">
                <span class="icon-[tabler--message-plus] size-5"></span>
                New Discussion
            </a>
        </div>

        <!-- Tabs: Discussion | Team Channel -->
        <div class="tabs tabs-bordered mb-6">
            <a href="{{ route('discussions.index') }}" class="tab tab-lg tab-bordered tab-active gap-2">
                <span class="icon-[tabler--message-circle] size-5"></span>
                Discussion
            </a>
            <a href="{{ route('channels.index') }}" class="tab tab-lg tab-bordered gap-2">
                <span class="icon-[tabler--hash] size-5"></span>
                Team Channel
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
                <form action="{{ route('discussions.index') }}" method="GET" class="flex flex-col md:flex-row gap-4">
                    <!-- Search -->
                    <div class="flex-1">
                        <div class="relative">
                            <span class="icon-[tabler--search] size-5 absolute left-3 top-1/2 -translate-y-1/2 text-base-content/50"></span>
                            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search discussions..." class="input input-bordered w-full pl-10" />
                        </div>
                    </div>
                    <!-- Type Filter -->
                    <select name="type" class="select select-bordered w-full md:w-40" onchange="this.form.submit()">
                        <option value="">All Types</option>
                        @foreach($types as $value => $label)
                            <option value="{{ $value }}" {{ ($filters['type'] ?? '') == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    <!-- Privacy Filter -->
                    <select name="is_public" class="select select-bordered w-full md:w-40" onchange="this.form.submit()">
                        <option value="">All Visibility</option>
                        <option value="1" {{ ($filters['is_public'] ?? '') === '1' ? 'selected' : '' }}>Public</option>
                        <option value="0" {{ ($filters['is_public'] ?? '') === '0' ? 'selected' : '' }}>Private</option>
                    </select>
                    <!-- My Discussions -->
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="my_discussions" value="1" class="checkbox checkbox-sm"
                               {{ !empty($filters['my_discussions']) ? 'checked' : '' }} onchange="this.form.submit()">
                        <span class="text-sm">Only mine</span>
                    </label>
                    <button type="submit" class="btn btn-ghost">
                        <span class="icon-[tabler--filter] size-5"></span>
                        Filter
                    </button>
                    @if(!empty(array_filter($filters ?? [])))
                        <a href="{{ route('discussions.index') }}" class="btn btn-ghost text-error">
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
                {{ $discussions->total() }} {{ Str::plural('discussion', $discussions->total()) }} found
            </div>
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2">
                    <select class="select select-bordered select-sm" onchange="window.location.href = this.value">
                        <option value="{{ route('discussions.index', array_merge(request()->query(), ['sort' => 'last_activity_at', 'direction' => 'desc'])) }}" {{ ($filters['sort'] ?? 'last_activity_at') === 'last_activity_at' ? 'selected' : '' }}>
                            Most Recent Activity
                        </option>
                        <option value="{{ route('discussions.index', array_merge(request()->query(), ['sort' => 'created_at', 'direction' => 'desc'])) }}" {{ ($filters['sort'] ?? '') === 'created_at' && ($filters['direction'] ?? '') === 'desc' ? 'selected' : '' }}>
                            Newest First
                        </option>
                        <option value="{{ route('discussions.index', array_merge(request()->query(), ['sort' => 'comments_count', 'direction' => 'desc'])) }}" {{ ($filters['sort'] ?? '') === 'comments_count' ? 'selected' : '' }}>
                            Most Comments
                        </option>
                    </select>
                </div>
                <div class="flex items-center gap-1 border border-base-300 rounded-lg p-1 bg-base-100">
                    <a href="{{ route('discussions.index', array_merge(request()->query(), ['view' => 'card'])) }}"
                       class="btn btn-sm {{ $viewMode === 'card' ? 'btn-primary' : 'btn-ghost' }}">
                        <span class="icon-[tabler--layout-grid] size-4"></span>
                    </a>
                    <a href="{{ route('discussions.index', array_merge(request()->query(), ['view' => 'table'])) }}"
                       class="btn btn-sm {{ $viewMode === 'table' ? 'btn-primary' : 'btn-ghost' }}">
                        <span class="icon-[tabler--list] size-4"></span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Discussions List -->
        @if($discussions->isEmpty())
            <div class="card bg-base-100 shadow">
                <div class="card-body text-center py-12">
                    <div class="text-base-content/50">
                        <span class="icon-[tabler--message-circle] size-12 block mx-auto mb-4"></span>
                        <p class="text-lg font-medium">No discussions yet</p>
                        <p class="text-sm">
                            @if(!empty(array_filter($filters ?? [])))
                                Try adjusting your search or filters
                            @else
                                Start a conversation with your team
                            @endif
                        </p>
                    </div>
                    <div class="mt-4 flex justify-center gap-2">
                        @if(!empty(array_filter($filters ?? [])))
                            <a href="{{ route('discussions.index') }}" class="btn btn-ghost">Clear Filters</a>
                        @endif
                        <a href="{{ route('discussions.create') }}" class="btn btn-primary">
                            <span class="icon-[tabler--plus] size-5"></span>
                            Start a Discussion
                        </a>
                    </div>
                </div>
            </div>
        @else
            @if($viewMode === 'card')
                @include('discussion::partials.discussion-cards', ['discussions' => $discussions])
            @else
                @include('discussion::partials.discussion-table', ['discussions' => $discussions])
            @endif

            <!-- Pagination -->
            @if($discussions->hasPages())
                <div class="mt-6">
                    {{ $discussions->withQueryString()->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
