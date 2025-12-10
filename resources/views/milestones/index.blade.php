@extends('layouts.app')

@section('content')
<div class="p-4 md:p-6">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center gap-2 text-sm text-base-content/60 mb-2">
                <a href="{{ route('dashboard') }}" class="hover:text-primary">Dashboard</a>
                <span class="icon-[tabler--chevron-right] size-4"></span>
                <a href="{{ route('workspace.index') }}" class="hover:text-primary">Workspaces</a>
                <span class="icon-[tabler--chevron-right] size-4"></span>
                <a href="{{ route('workspace.show', $workspace->uuid) }}" class="hover:text-primary">{{ $workspace->name }}</a>
                <span class="icon-[tabler--chevron-right] size-4"></span>
                <span>Milestones</span>
            </div>
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-base-content">Milestones</h1>
                    <p class="text-base-content/60">Track project progress with milestones</p>
                </div>
                @if(auth()->user()->company->isMilestonesEnabled())
                <a href="{{ route('milestones.create', $workspace->uuid) }}" class="btn btn-primary">
                    <span class="icon-[tabler--plus] size-5"></span>
                    Create Milestone
                </a>
                @else
                <div class="badge badge-warning gap-2">
                    <span class="icon-[tabler--alert-triangle] size-4"></span>
                    Module Disabled
                </div>
                @endif
            </div>
        </div>

        <!-- Success/Error Messages -->
        <div class="mb-4">
            @include('partials.alerts')
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-2 md:grid-cols-6 gap-4 mb-6">
            <div class="card bg-base-100 shadow">
                <div class="card-body py-4 px-5">
                    <div class="text-2xl font-bold text-base-content">{{ $stats['total'] }}</div>
                    <div class="text-sm text-base-content/60">Total</div>
                </div>
            </div>
            <div class="card bg-base-200/50 border border-base-300">
                <div class="card-body py-4 px-5">
                    <div class="text-2xl font-bold text-base-content/70">{{ $stats['not_started'] }}</div>
                    <div class="text-sm text-base-content/50">Not Started</div>
                </div>
            </div>
            <div class="card bg-warning/10 border border-warning/20">
                <div class="card-body py-4 px-5">
                    <div class="text-2xl font-bold text-warning">{{ $stats['in_progress'] }}</div>
                    <div class="text-sm text-warning/80">In Progress</div>
                </div>
            </div>
            <div class="card bg-error/10 border border-error/20">
                <div class="card-body py-4 px-5">
                    <div class="text-2xl font-bold text-error">{{ $stats['blocked'] }}</div>
                    <div class="text-sm text-error/80">Blocked</div>
                </div>
            </div>
            <div class="card bg-success/10 border border-success/20">
                <div class="card-body py-4 px-5">
                    <div class="text-2xl font-bold text-success">{{ $stats['completed'] }}</div>
                    <div class="text-sm text-success/80">Completed</div>
                </div>
            </div>
            @if($stats['overdue'] > 0)
            <div class="card bg-red-500/10 border border-red-500/30">
                <div class="card-body py-4 px-5">
                    <div class="text-2xl font-bold text-red-600">{{ $stats['overdue'] }}</div>
                    <div class="text-sm text-red-500/80">Overdue</div>
                </div>
            </div>
            @endif
        </div>

        <!-- Filters -->
        @php
            $hasFilters = request('search') || request('status') || request('owner') || request('due_from') || request('due_to');
        @endphp
        <div class="card bg-base-100 shadow mb-6">
            <div class="card-body py-4">
                <form id="milestone-filter-form" action="{{ route('milestones.index', $workspace->uuid) }}" method="GET" class="flex flex-wrap items-end gap-4">
                    <!-- Search -->
                    <div class="form-control flex-1 min-w-48">
                        <label class="label py-1">
                            <span class="label-text">Search</span>
                        </label>
                        <div class="relative">
                            <input type="text" name="search" id="milestone-search" value="{{ request('search') }}" placeholder="Search milestones..." class="input input-bordered input-sm w-full pr-8">
                            <span class="icon-[tabler--search] size-4 absolute right-3 top-1/2 -translate-y-1/2 text-base-content/40"></span>
                        </div>
                    </div>

                    <!-- Status Filter -->
                    <div class="form-control min-w-36">
                        <label class="label py-1">
                            <span class="label-text">Status</span>
                        </label>
                        <select name="status" class="select select-bordered select-sm filter-auto-submit">
                            <option value="">All Statuses</option>
                            @foreach($statuses as $value => $label)
                                <option value="{{ $value }}" {{ request('status') == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Owner Filter -->
                    <div class="form-control min-w-36">
                        <label class="label py-1">
                            <span class="label-text">Owner</span>
                        </label>
                        <select name="owner" class="select select-bordered select-sm filter-auto-submit">
                            <option value="">All Owners</option>
                            @foreach($members as $member)
                                <option value="{{ $member->id }}" {{ request('owner') == $member->id ? 'selected' : '' }}>{{ $member->full_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Due Date Range -->
                    <div class="form-control min-w-36">
                        <label class="label py-1">
                            <span class="label-text">Due From</span>
                        </label>
                        <input type="date" name="due_from" value="{{ request('due_from') }}" class="input input-bordered input-sm filter-auto-submit">
                    </div>

                    <div class="form-control min-w-36">
                        <label class="label py-1">
                            <span class="label-text">Due To</span>
                        </label>
                        <input type="date" name="due_to" value="{{ request('due_to') }}" class="input input-bordered input-sm filter-auto-submit">
                    </div>

                    @if($hasFilters)
                    <div class="flex gap-2">
                        <a href="{{ route('milestones.index', $workspace->uuid) }}" class="btn btn-ghost btn-sm">
                            <span class="icon-[tabler--x] size-4"></span>
                            Clear
                        </a>
                    </div>
                    @endif
                </form>
            </div>
        </div>

        <!-- Milestones Grid -->
        @if($milestones->isEmpty())
            <div class="card bg-base-100 shadow">
                <div class="card-body text-center py-12">
                    <div class="flex justify-center mb-4">
                        <span class="icon-[tabler--flag] size-16 text-base-content/20"></span>
                    </div>
                    <h3 class="text-lg font-semibold text-base-content">No Milestones Yet</h3>
                    <p class="text-base-content/60 mb-4">Create milestones to track your project progress and deliverables.</p>
                    <div>
                        <a href="{{ route('milestones.create', $workspace->uuid) }}" class="btn btn-primary">
                            <span class="icon-[tabler--plus] size-5"></span>
                            Create Milestone
                        </a>
                    </div>
                </div>
            </div>
        @else
            <div class="space-y-4">
                @foreach($milestones as $milestone)
                    @include('milestones.partials.milestone-card', ['milestone' => $milestone])
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $milestones->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.getElementById('milestone-filter-form');
    const searchInput = document.getElementById('milestone-search');
    const autoSubmitInputs = document.querySelectorAll('.filter-auto-submit');
    let searchTimeout = null;

    // Debounced search - submit after user stops typing
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                filterForm.submit();
            }, 400);
        });

        // Submit on Enter key
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                clearTimeout(searchTimeout);
                filterForm.submit();
            }
        });
    }

    // Auto-submit on select/date change
    autoSubmitInputs.forEach(input => {
        input.addEventListener('change', function() {
            filterForm.submit();
        });
    });
});
</script>
@endpush
@endsection
