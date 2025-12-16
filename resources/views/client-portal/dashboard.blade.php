@extends('client-portal.layouts.app')

@section('title', 'Dashboard')

@section('content')
<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="card bg-base-100 shadow">
        <div class="card-body py-4">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center">
                    <span class="icon-[tabler--ticket] size-6 text-primary"></span>
                </div>
                <div>
                    <p class="text-sm text-base-content/60">Total Tickets</p>
                    <p class="text-2xl font-bold">{{ $stats['total'] }}</p>
                </div>
            </div>
        </div>
    </div>
    <div class="card bg-base-100 shadow">
        <div class="card-body py-4">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-success/10 flex items-center justify-center">
                    <span class="icon-[tabler--clock] size-6 text-success"></span>
                </div>
                <div>
                    <p class="text-sm text-base-content/60">Open Tickets</p>
                    <p class="text-2xl font-bold">{{ $stats['open'] }}</p>
                </div>
            </div>
        </div>
    </div>
    <div class="card bg-base-100 shadow">
        <div class="card-body py-4">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-slate-500/10 flex items-center justify-center">
                    <span class="icon-[tabler--check] size-6 text-slate-500"></span>
                </div>
                <div>
                    <p class="text-sm text-base-content/60">Closed Tickets</p>
                    <p class="text-2xl font-bold">{{ $stats['closed'] }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters and Search -->
<div class="card bg-base-100 shadow mb-6">
    <div class="card-body py-4">
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
            <!-- Filter Tabs -->
            <div class="tabs tabs-boxed">
                <a href="{{ route('client-portal.dashboard', ['filter' => 'all']) }}" class="tab {{ $filter === 'all' ? 'tab-active' : '' }}">
                    All
                </a>
                <a href="{{ route('client-portal.dashboard', ['filter' => 'open']) }}" class="tab {{ $filter === 'open' ? 'tab-active' : '' }}">
                    Open
                </a>
                <a href="{{ route('client-portal.dashboard', ['filter' => 'closed']) }}" class="tab {{ $filter === 'closed' ? 'tab-active' : '' }}">
                    Closed
                </a>
            </div>

            <!-- Search -->
            <form action="{{ route('client-portal.dashboard') }}" method="GET" class="flex gap-2">
                <input type="hidden" name="filter" value="{{ $filter }}">
                <div class="input-group">
                    <input
                        type="text"
                        name="search"
                        value="{{ $search }}"
                        class="input input-bordered input-sm w-full sm:w-64"
                        placeholder="Search tickets..."
                    />
                    <button type="submit" class="btn btn-sm btn-square">
                        <span class="icon-[tabler--search] size-4"></span>
                    </button>
                </div>
                @if($search)
                <a href="{{ route('client-portal.dashboard', ['filter' => $filter]) }}" class="btn btn-ghost btn-sm">
                    Clear
                </a>
                @endif
            </form>
        </div>
    </div>
</div>

<!-- Tickets List -->
<div class="card bg-base-100 shadow">
    <div class="card-body p-0">
        @if($tickets->count() > 0)
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Subject</th>
                        <th>Department</th>
                        <th>Status</th>
                        <th>Priority</th>
                        <th>Created</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tickets as $ticket)
                    <tr class="hover">
                        <td>
                            <span class="font-mono text-sm">{{ $ticket->task_number }}</span>
                        </td>
                        <td>
                            <div class="max-w-xs">
                                <a href="{{ route('client-portal.tickets.show', $ticket->uuid) }}" class="font-medium hover:text-primary truncate block">
                                    {{ $ticket->title }}
                                </a>
                                <p class="text-xs text-base-content/50 truncate">{{ $ticket->workspace->name }}</p>
                            </div>
                        </td>
                        <td>
                            @if($ticket->department)
                            <span class="badge badge-sm badge-ghost">{{ $ticket->department->name }}</span>
                            @else
                            <span class="text-base-content/40">-</span>
                            @endif
                        </td>
                        <td>
                            @if($ticket->status)
                            <span class="badge badge-sm" style="background-color: {{ $ticket->status->background_color }}; color: {{ $ticket->status->text_color }};">
                                {{ $ticket->status->name }}
                            </span>
                            @else
                            <span class="badge badge-sm badge-ghost">Unknown</span>
                            @endif
                        </td>
                        <td>
                            @if($ticket->workspacePriority)
                            <span class="badge badge-sm" style="background-color: {{ $ticket->workspacePriority->color }}20; color: {{ $ticket->workspacePriority->color }};">
                                {{ $ticket->workspacePriority->name }}
                            </span>
                            @else
                            <span class="text-base-content/40">-</span>
                            @endif
                        </td>
                        <td>
                            <span class="text-sm text-base-content/60">{{ $ticket->created_at->diffForHumans() }}</span>
                        </td>
                        <td>
                            <a href="{{ route('client-portal.tickets.show', $ticket->uuid) }}" class="btn btn-ghost btn-sm btn-square">
                                <span class="icon-[tabler--chevron-right] size-5"></span>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($tickets->hasPages())
        <div class="p-4 border-t border-base-200">
            {{ $tickets->appends(['filter' => $filter, 'search' => $search])->links() }}
        </div>
        @endif
        @else
        <div class="text-center py-12">
            <div class="w-16 h-16 rounded-2xl bg-base-200 flex items-center justify-center mx-auto mb-4">
                <span class="icon-[tabler--ticket-off] size-8 text-base-content/40"></span>
            </div>
            <h3 class="text-lg font-medium text-base-content mb-1">No tickets found</h3>
            <p class="text-base-content/60 mb-4">
                @if($search)
                    No tickets match your search "{{ $search }}".
                @elseif($filter !== 'all')
                    You don't have any {{ $filter }} tickets.
                @else
                    You haven't created any support tickets yet.
                @endif
            </p>
            <a href="{{ route('client-portal.tickets.create') }}" class="btn btn-primary gap-2">
                <span class="icon-[tabler--plus] size-4"></span>
                Create Your First Ticket
            </a>
        </div>
        @endif
    </div>
</div>
@endsection
