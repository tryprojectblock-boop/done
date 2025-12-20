<!-- Today's Standup Card (Full Width - Hidden when submitted) -->
@if($workspace->isStandupEnabled())
@php
    $todayStandupEntry = \App\Modules\Standup\Models\StandupEntry::query()
        ->where('workspace_id', $workspace->id)
        ->where('user_id', auth()->id())
        ->whereDate('standup_date', today())
        ->first();
    $hasSubmittedToday = $todayStandupEntry !== null;
@endphp
@if(!$hasSubmittedToday)
<div class="card bg-primary/5 border border-primary/20 mb-6">
    <div class="card-body">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-full bg-primary/20 flex items-center justify-center">
                    <span class="icon-[tabler--calendar-check] size-7 text-primary"></span>
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-base-content">Today's Standup</h2>
                    <p class="text-base-content/60">{{ today()->format('l, F j, Y') }}</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="badge badge-warning gap-1">
                    <span class="icon-[tabler--clock] size-4"></span>
                    Pending
                </span>
                <a href="{{ route('standups.create', $workspace) }}" class="btn btn-primary">
                    <span class="icon-[tabler--plus] size-5"></span>
                    Submit Standup
                </a>
            </div>
        </div>
    </div>
</div>
@endif
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Main Content -->
    <div class="lg:col-span-2 space-y-6">

        @if($workspace->type->value === 'inbox')
            @php
                // Check if all checklist items are completed
                $inboxSettings = $workspace->inboxSettings;
                $checklistComplete = $inboxSettings &&
                    $inboxSettings->working_hours_configured_at !== null &&
                    $inboxSettings->departments_configured_at !== null &&
                    $inboxSettings->priorities_configured_at !== null &&
                    $inboxSettings->holidays_configured_at !== null &&
                    $inboxSettings->sla_configured_at !== null &&
                    $inboxSettings->ticket_rules_configured_at !== null &&
                    $inboxSettings->sla_rules_configured_at !== null &&
                    $inboxSettings->idle_rules_configured_at !== null &&
                    $inboxSettings->email_templates_configured_at !== null &&
                    $inboxSettings->client_portal_enabled;
            @endphp
            @if(!$checklistComplete)
                {{-- Inbox Workspace Setup Checklist (hidden when all complete) --}}
                @include('workspace::partials.inbox.setup-checklist')
            @endif
        @endif

        <!-- Quick Actions -->
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <h2 class="card-title text-lg mb-4">Quick Actions</h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <a href="{{ route('workspace.show', ['workspace' => $workspace, 'tab' => 'tasks']) }}" class="btn btn-ghost flex-col h-auto py-4">
                        <span class="icon-[{{ $workspace->type->value === 'inbox' ? 'tabler--ticket' : 'tabler--list-check' }}] size-6 text-primary"></span>
                        <span class="text-sm mt-1">{{ $workspace->type->value === 'inbox' ? 'Tickets' : 'Tasks' }}</span>
                    </a>
                    <a href="{{ route('workspace.show', ['workspace' => $workspace, 'tab' => 'discussions']) }}" class="btn btn-ghost flex-col h-auto py-4">
                        <span class="icon-[tabler--messages] size-6 text-success"></span>
                        <span class="text-sm mt-1">Discussions</span>
                    </a>
                    <a href="{{ route('workspace.show', ['workspace' => $workspace, 'tab' => 'files']) }}" class="btn btn-ghost flex-col h-auto py-4">
                        <span class="icon-[tabler--files] size-6 text-warning"></span>
                        <span class="text-sm mt-1">Files</span>
                    </a>
                    <a href="{{ route('workspace.show', ['workspace' => $workspace, 'tab' => 'people']) }}" class="btn btn-ghost flex-col h-auto py-4">
                        <span class="icon-[tabler--users] size-6 text-info"></span>
                        <span class="text-sm mt-1">People</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <h2 class="card-title text-lg mb-4">Recent Activity</h2>
                <div class="text-center py-8 text-base-content/50">
                    <span class="icon-[tabler--activity] size-12 mb-2"></span>
                    <p>No recent activity</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="space-y-6">
        <!-- Workspace Info -->
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <h2 class="card-title text-lg mb-4">Workspace Info</h2>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-base-content/60">Owner</span>
                        <div class="flex items-center gap-2">
                            <div class="avatar">
                                <div class="w-6 rounded-full">
                                    <img src="{{ $workspace->owner->avatar_url }}" alt="{{ $workspace->owner->name }}" />
                                </div>
                            </div>
                            <span class="font-medium">{{ $workspace->owner->name }}</span>
                        </div>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-base-content/60">Created</span>
                        <span>{{ $workspace->created_at->format('M d, Y') }}</span>
                    </div>
                    @if($workspace->workflow)
                    <div class="flex justify-between">
                        <span class="text-base-content/60">Workflow</span>
                        <span>{{ $workspace->workflow->name }}</span>
                    </div>
                    @endif
                    @if($workspace->settings['start_date'] ?? null)
                    <div class="flex justify-between">
                        <span class="text-base-content/60">Start Date</span>
                        <span>{{ \Carbon\Carbon::parse($workspace->settings['start_date'])->format('M d, Y') }}</span>
                    </div>
                    @endif
                    @if($workspace->settings['end_date'] ?? null)
                    <div class="flex justify-between">
                        <span class="text-base-content/60">End Date</span>
                        <span>{{ \Carbon\Carbon::parse($workspace->settings['end_date'])->format('M d, Y') }}</span>
                    </div>
                    @endif

                    @if($workspace->type->value === 'inbox' && $workspace->inboxSettings && $workspace->inboxSettings->inbound_email)
                    <!-- Inbound Email for Inbox Workspace -->
                    <div class="pt-3 mt-3 border-t border-base-200">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-base-content/60 text-sm">Inbound Email</span>
                            @if($workspace->inboxSettings->email_verified)
                                <span class="badge badge-success badge-xs gap-1">
                                    <span class="icon-[tabler--check] size-3"></span>
                                    Verified
                                </span>
                            @else
                                <span class="badge badge-warning badge-xs gap-1">
                                    <span class="icon-[tabler--alert-circle] size-3"></span>
                                    Unverified
                                </span>
                            @endif
                        </div>
                        <div class="flex items-center gap-2 p-2 bg-base-200 rounded-lg">
                            <span class="icon-[tabler--mail] size-4 text-orange-500 shrink-0"></span>
                            <code class="text-xs font-mono text-base-content/80 truncate flex-1">{{ $workspace->inboxSettings->inbound_email }}</code>
                            <button type="button" class="btn btn-ghost btn-xs btn-square" onclick="copyInboundEmail()" title="Copy email">
                                <span class="icon-[tabler--copy] size-4"></span>
                            </button>
                        </div>

                        @if(!$workspace->inboxSettings->email_verified)
                        <!-- Verify Email Button -->
                        <button type="button" class="btn btn-outline btn-primary btn-sm w-full mt-3 gap-2" onclick="openVerifyEmailModal()">
                            <span class="icon-[tabler--mail-check] size-4"></span>
                            Verify Email Setup
                        </button>
                        <p class="text-xs text-base-content/50 mt-2">Send a test email to verify your forwarding is working correctly.</p>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Members Preview -->
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="card-title text-lg">Members</h2>
                    <span class="badge badge-ghost">{{ $workspace->members->count() }}</span>
                </div>
                <div class="space-y-3">
                    @foreach($workspace->members->take(5) as $member)
                    <div class="flex items-center gap-3">
                        <div class="avatar">
                            <div class="w-8 rounded-full">
                                <img src="{{ $member->avatar_url }}" alt="{{ $member->name }}" />
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-sm truncate">{{ $member->name }}</p>
                            @php
                                $role = $member->pivot->role;
                                $roleLabel = $role instanceof \App\Modules\Workspace\Enums\WorkspaceRole ? $role->label() : ucfirst((string)$role);
                            @endphp
                            <p class="text-xs text-base-content/60">{{ $roleLabel }}</p>
                        </div>
                    </div>
                    @endforeach
                    @if($workspace->members->count() > 5)
                    <a href="{{ route('workspace.show', ['workspace' => $workspace, 'tab' => 'people']) }}" class="btn btn-ghost btn-sm w-full">
                        View all {{ $workspace->members->count() }} members
                    </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Guests Preview -->
        @if($workspace->guests->count() > 0)
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="card-title text-lg">Guests</h2>
                    <span class="badge badge-warning">{{ $workspace->guests->count() }}</span>
                </div>
                <div class="space-y-3">
                    @foreach($workspace->guests->take(5) as $guest)
                    <div class="flex items-center gap-3">
                        <div class="avatar">
                            <div class="w-8 rounded-full">
                                <img src="{{ $guest->avatar_url }}" alt="{{ $guest->full_name }}" />
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-sm truncate">{{ $guest->full_name }}</p>
                            <p class="text-xs text-base-content/60">{{ $guest->type_label }}</p>
                        </div>
                    </div>
                    @endforeach
                    @if($workspace->guests->count() > 5)
                    <a href="{{ route('workspace.show', ['workspace' => $workspace, 'tab' => 'people']) }}" class="btn btn-ghost btn-sm w-full">
                        View all {{ $workspace->guests->count() }} guests
                    </a>
                    @endif
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

{{-- Inbox Workspace Drawers & Modals --}}
@if($workspace->type->value === 'inbox')
    @include('workspace::partials.inbox.drawers.working-hours')
    @include('workspace::partials.inbox.drawers.departments')
    @include('workspace::partials.inbox.drawers.priorities')
    @include('workspace::partials.inbox.drawers.holidays')
    @include('workspace::partials.inbox.drawers.sla-settings')
    @include('workspace::partials.inbox.drawers.ticket-rules')
    @include('workspace::partials.inbox.drawers.sla-rules')
    {{-- Future drawers will be included here --}}
    {{-- @include('workspace::partials.inbox.drawers.idle-rules') --}}
    {{-- @include('workspace::partials.inbox.drawers.email-templates') --}}
    {{-- @include('workspace::partials.inbox.drawers.form-page') --}}

    @include('workspace::partials.inbox.verify-email-modal')
@endif
