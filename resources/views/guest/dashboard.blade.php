@extends('layouts.app')

@section('content')
@php
    $guestWorkspaces = auth()->user()->guestWorkspaces()->with('owner')->get();
    $inboxWorkspaces = $guestWorkspaces->filter(fn($w) => $w->type->value === 'inbox');
    $hasInboxWorkspaces = $inboxWorkspaces->isNotEmpty();

    // Get client's tickets from inbox workspaces
    $clientTickets = collect();
    if ($hasInboxWorkspaces) {
        $clientTickets = \App\Modules\Task\Models\Task::where('created_by', auth()->id())
            ->whereIn('workspace_id', $inboxWorkspaces->pluck('id'))
            ->with(['workspace', 'status', 'assignee'])
            ->orderBy('created_at', 'desc')
            ->get();
    }
@endphp

<div class="p-4 md:p-6">
    <div class="max-w-5xl mx-auto">
        @if($hasInboxWorkspaces)
        <!-- Client Portal Welcome Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-base-content mb-2">
                Welcome, {{ auth()->user()->first_name ?? auth()->user()->name }}!
            </h1>
            <p class="text-base-content/60 text-lg mb-4">
                You're logged in as a <span class="badge badge-primary">Client</span>
            </p>
            <p class="text-base-content/70 max-w-lg mx-auto">
                As a client, you have full access to tickets you've been invited to. When someone adds you to a ticket, it will appear below.
            </p>
        </div>

        <!-- Client Portal - Tickets View -->
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-xl font-bold text-base-content flex items-center gap-2">
                            <span class="icon-[tabler--ticket] size-6 text-primary"></span>
                            My Tickets
                        </h2>
                        <p class="text-base-content/60 text-sm">View and track your support tickets</p>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="stats stats-horizontal bg-base-200 shadow-sm">
                            <div class="stat py-2 px-4">
                                <div class="stat-title text-xs">Open</div>
                                <div class="stat-value text-lg text-warning">{{ $clientTickets->whereNull('closed_at')->count() }}</div>
                            </div>
                            <div class="stat py-2 px-4">
                                <div class="stat-title text-xs">Closed</div>
                                <div class="stat-value text-lg text-success">{{ $clientTickets->whereNotNull('closed_at')->count() }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                @if($clientTickets->isNotEmpty())
                    <div class="overflow-x-auto">
                        <table class="table">
                            <thead>
                                <tr class="bg-base-200">
                                    <th>Ticket</th>
                                    <th>Status</th>
                                    <th>Assigned To</th>
                                    <th>Created</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($clientTickets as $ticket)
                                    <tr class="hover:bg-base-200/50">
                                        <td>
                                            <div class="flex flex-col">
                                                <span class="font-semibold text-base-content">
                                                    #{{ $ticket->task_number ?? $ticket->id }}
                                                </span>
                                                <span class="text-sm text-base-content/70 max-w-sm truncate">
                                                    {{ $ticket->title }}
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            @if($ticket->status)
                                                <span class="badge badge-sm" style="background-color: {{ $ticket->status->background_color }}; color: {{ $ticket->status->text_color }};">
                                                    {{ $ticket->status->name }}
                                                </span>
                                            @elseif($ticket->closed_at)
                                                <span class="badge badge-sm badge-ghost">Closed</span>
                                            @else
                                                <span class="badge badge-sm badge-warning">Open</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($ticket->assignee)
                                                <div class="flex items-center gap-2">
                                                    <div class="avatar placeholder">
                                                        <div class="bg-neutral text-neutral-content rounded-full w-6 h-6">
                                                            <span class="text-xs">{{ $ticket->assignee->initials }}</span>
                                                        </div>
                                                    </div>
                                                    <span class="text-sm">{{ $ticket->assignee->name }}</span>
                                                </div>
                                            @else
                                                <span class="text-base-content/40 text-sm">Unassigned</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="text-sm text-base-content/60">
                                                {{ $ticket->created_at->format('M d, Y') }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('tasks.show', $ticket) }}"
                                               class="btn btn-primary btn-sm gap-1">
                                                <span class="icon-[tabler--eye] size-4"></span>
                                                View
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <!-- No Tickets Yet -->
                    <div class="text-center py-16">
                        <div class="flex justify-center mb-4">
                            <div class="w-24 h-24 rounded-full bg-base-200 flex items-center justify-center">
                                <span class="icon-[tabler--ticket-off] size-12 text-base-content/30"></span>
                            </div>
                        </div>
                        <h3 class="text-lg font-semibold text-base-content mb-2">No Tickets Yet</h3>
                        <p class="text-base-content/60 text-sm max-w-md mx-auto">
                            You haven't created any support tickets yet. When you contact support via email, your tickets will appear here.
                        </p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Account Info Footer for Clients -->
        <div class="flex items-center justify-center gap-6 mt-6 text-sm text-base-content/50">
            <div class="flex items-center gap-2">
                <span class="icon-[tabler--mail] size-4"></span>
                {{ auth()->user()->email }}
            </div>
            <a href="/profile" class="flex items-center gap-1 hover:text-primary">
                <span class="icon-[tabler--user] size-4"></span>
                Edit Profile
            </a>
            <a href="/profile/password" class="flex items-center gap-1 hover:text-primary">
                <span class="icon-[tabler--lock] size-4"></span>
                Change Password
            </a>
        </div>

        @else
        <!-- Welcome Section with Workspaces (shown for regular guests, not clients) -->
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body text-center py-12">
                <div class="flex justify-center mb-6">
                    <div class="w-24 h-24 rounded-full bg-warning/20 flex items-center justify-center">
                        <span class="icon-[tabler--user-check] size-12 text-warning"></span>
                    </div>
                </div>

                <h1 class="text-3xl font-bold text-base-content mb-2">
                    Welcome, {{ auth()->user()->first_name }}!
                </h1>
                <p class="text-base-content/60 text-lg mb-6">
                    You're logged in as a <span class="badge badge-warning">Guest</span>
                </p>

                <div class="max-w-lg mx-auto mb-8">
                    <p class="text-base-content/70">
                        As a guest, you have limited access to workspaces you've been invited to.
                        When someone adds you to a workspace, it will appear below.
                    </p>
                </div>

                @if($guestWorkspaces->isNotEmpty())
                    <div class="divider">Your Workspaces</div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        @foreach($guestWorkspaces as $workspace)
                            <a href="{{ route('workspace.guest-view', $workspace) }}"
                               class="block p-4 bg-base-200 hover:bg-warning/10 border border-transparent hover:border-warning/30 rounded-xl transition-all cursor-pointer text-left">
                                <div class="flex items-center gap-3">
                                    <div class="avatar placeholder">
                                        @if($workspace->getLogoUrl())
                                            <div class="w-12 h-12 rounded-lg">
                                                <img src="{{ $workspace->getLogoUrl() }}" alt="{{ $workspace->name }}" />
                                            </div>
                                        @else
                                            <div class="bg-warning text-warning-content rounded-lg w-12 h-12 flex items-center justify-center">
                                                <span class="text-lg font-bold">{{ substr($workspace->name, 0, 1) }}</span>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h3 class="font-semibold text-base-content truncate">{{ $workspace->name }}</h3>
                                        <p class="text-sm text-base-content/60">Owner: {{ $workspace->owner->name }}</p>
                                    </div>
                                    <span class="icon-[tabler--chevron-right] size-5 text-base-content/40"></span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="bg-base-200 rounded-xl p-8 mt-4">
                        <div class="flex justify-center mb-4">
                            <span class="icon-[tabler--inbox] size-16 text-base-content/20"></span>
                        </div>
                        <h3 class="text-lg font-semibold text-base-content mb-2">No Workspaces Yet</h3>
                        <p class="text-base-content/60 text-sm">
                            You haven't been added to any workspaces yet. Once someone invites you to a workspace, it will appear here.
                        </p>
                    </div>
                @endif

                <div class="mt-8 pt-6 border-t border-base-200">
                    <div class="flex items-center justify-center gap-6 text-sm text-base-content/60">
                        <div class="flex items-center gap-2">
                            <span class="icon-[tabler--mail] size-4"></span>
                            {{ auth()->user()->email }}
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="icon-[tabler--calendar] size-4"></span>
                            Joined {{ auth()->user()->created_at->format('M d, Y') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-center gap-4 mt-6">
            <a href="/profile" class="btn btn-ghost">
                <span class="icon-[tabler--user] size-5"></span>
                Edit Profile
            </a>
            <a href="/profile/password" class="btn btn-ghost">
                <span class="icon-[tabler--lock] size-5"></span>
                Change Password
            </a>
        </div>
        @endif
    </div>
</div>
@endsection
