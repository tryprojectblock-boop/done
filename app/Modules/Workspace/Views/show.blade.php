@extends('layouts.app')

@section('content')
@php
    // Check if inbox checklist is completed or email is verified
    $inboxChecklistCompleted = false;
    $inboxEmailVerified = false;
    if ($workspace->type->value === 'inbox' && $workspace->inboxSettings) {
        $inboxSettings = $workspace->inboxSettings;
        $inboxEmailVerified = $inboxSettings->email_verified ?? false;
        $inboxChecklistCompleted =
            $inboxSettings->working_hours_configured_at !== null &&
            $inboxSettings->departments_configured_at !== null &&
            $inboxSettings->priorities_configured_at !== null &&
            $inboxSettings->holidays_configured_at !== null &&
            $inboxSettings->sla_configured_at !== null &&
            $inboxSettings->ticket_rules_configured_at !== null &&
            $inboxSettings->sla_rules_configured_at !== null &&
            $inboxSettings->idle_rules_configured_at !== null &&
            $inboxSettings->email_templates_configured_at !== null;
    }
    // Show tabs if email is verified OR checklist is complete
    $showInboxTabs = $inboxEmailVerified || $inboxChecklistCompleted;

    // Use "Ticket" for inbox workspaces, "Task" for others
    $isInbox = $workspace->type->value === 'inbox';
    $taskLabel = $isInbox ? 'Ticket' : 'Task';
    $tasksLabel = $isInbox ? 'Tickets' : 'Tasks';
@endphp
<div class="p-4 md:p-6">
    <div class="flex items-center gap-2 text-sm text-base-content/60 mb-[30px]">
            <a href="{{ route('dashboard') }}" class="hover:text-primary">Dashboard</a>
            <span class="icon-[tabler--chevron-right] size-4"></span>
            <a href="{{ route('workspace.index') }}" class="hover:text-primary">Workspaces</a>
            <span class="icon-[tabler--chevron-right] size-4"></span>
            <span>{{ $workspace->name }}</span>
        </div>
        <div class="max-w mx-auto bg-white py-9">
            <!-- Header -->
            <div class="mb-6 p-5 pt-0">
                <div class="w-full">
                    <div class="flex items-center gap-4">
                        <!-- Workspace Icon/Color -->
                        <div>
                            <h1 class="text-[32px] leading-9 font-semibold text-[#17151C] pb-4">{{ $workspace->name }}</h1>
                        </div>
                    </div>
                    <!-- Member Avatars and Invite Button -->
                    <div class="flex items-center gap-3 mt-2">
                        <!-- Member Avatar Group -->
                        <div class="flex -space-x-2">
                            @foreach($workspace->members->take(5) as $member)
                            <div class="avatar border-2 border-white rounded-full" title="{{ $member->name }}">
                                <div class="w-8 rounded-full">
                                    <img src="{{ $member->avatar_url }}" alt="{{ $member->name }}" />
                                </div>
                            </div>
                            @endforeach
                            @if($workspace->members->count() > 5)
                            <div class="avatar placeholder border-2 border-white rounded-full">
                                <div class="bg-base-200 text-base-content/70 w-8 rounded-full">
                                    <span class="text-xs font-medium">+{{ $workspace->members->count() - 5 }}</span>
                                </div>
                            </div>
                            @endif
                        </div>
                        <!-- Invite Team Member Button -->
                        @if($workspace->isOwner(auth()->user()) || $workspace->getMemberRole(auth()->user())?->isAdmin())
                        <button type="button" onclick="openInviteModal()" class="btn btn-circle btn-sm btn-ghost border border-dashed border-base-300 hover:border-primary hover:bg-primary/10" title="Add Team Member">
                            <span class="icon-[tabler--plus] size-4"></span>
                        </button>
                            <label>Invite Team member</label>
                        @endif
                    </div>
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2 mt-1">
                                <span class="badge badge-{{ $workspace->type->badgeColor() }}">{{ $workspace->type->label() }}</span>
                                <span class="badge badge-{{ $workspace->status->color() }}">{{ $workspace->status->label() }}</span>
                            </div>
                        <div class="flex items-center gap-[9px]">
                        @if($workspace->status !== \App\Modules\Workspace\Enums\WorkspaceStatus::ARCHIVED)
                        @if($workspace->type->value !== 'inbox' || $showInboxTabs)
                        <!-- Add Task/Ticket Button -->
                        <a href="{{ route('tasks.create', ['workspace' => $workspace->uuid]) }}" class="btn btn-primary btn-no-shadow py-2 pl-3 pr-4">
                            <span class="icon-[tabler--plus] size-5"></span>
                            Add {{ $taskLabel }}
                        </a>

                        <!-- Add File Button -->
                        <a href="{{ route('drive.create', ['workspace_id' => $workspace->uuid]) }}" class="btn btn-outline btn-no-shadow border-[#B8B7BB]">
                            <svg width="14" height="17" viewBox="0 0 14 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M0 14.1667V2.5C0 1.83696 0.263581 1.20126 0.732422 0.732422C1.20126 0.263581 1.83696 0 2.5 0H7.15495L7.32015 0.00813802C7.7015 0.04614 8.05998 0.214899 8.33333 0.488281L12.8451 5L12.9557 5.12207C13.1987 5.41858 13.3332 5.79165 13.3333 6.17838V14.1667C13.3333 14.8297 13.0698 15.4654 12.6009 15.9342C12.1321 16.4031 11.4964 16.6667 10.8333 16.6667H2.5C1.83696 16.6667 1.20126 16.4031 0.732422 15.9342C0.263581 15.4654 0 14.8297 0 14.1667ZM5.83333 11.6667V10H4.16667C3.70643 10 3.33333 9.6269 3.33333 9.16667C3.33333 8.70643 3.70643 8.33333 4.16667 8.33333H5.83333V6.66667C5.83333 6.20643 6.20643 5.83333 6.66667 5.83333C7.1269 5.83333 7.5 6.20643 7.5 6.66667V8.33333H9.16667C9.6269 8.33333 10 8.70643 10 9.16667C10 9.6269 9.6269 10 9.16667 10H7.5V11.6667C7.5 12.1269 7.1269 12.5 6.66667 12.5C6.20643 12.5 5.83333 12.1269 5.83333 11.6667ZM1.66667 14.1667L1.67074 14.2489C1.68964 14.4397 1.77402 14.6191 1.91081 14.7559C2.06709 14.9121 2.27899 15 2.5 15H10.8333C11.0543 15 11.2662 14.9121 11.4225 14.7559C11.5788 14.5996 11.6667 14.3877 11.6667 14.1667V6.17838L7.15495 1.66667H2.5C2.27899 1.66667 2.06709 1.75453 1.91081 1.91081C1.75453 2.06709 1.66667 2.27899 1.66667 2.5V14.1667Z" fill="#17151C"/>
                            </svg>
                            <span class="text-[#17151C]">Add File</span>
                        </a>
                        @endif

                        <!-- Settings Dropdown (only for workspace owner or admin) -->
                        @if($workspace->isOwner(auth()->user()) || auth()->user()->isAdminOrHigher())
                        <div class="dropdown dropdown-end">
                            <button id="workspace-settings-dropdown" type="button" class="dropdown-toggle btn btn-primary btn-no-shadow" aria-haspopup="menu" aria-expanded="false" aria-label="Settings">
                                <span class="icon-[tabler--settings] size-5"></span>
                                Settings
                                <span class="icon-[tabler--chevron-down] dropdown-open:rotate-180 size-4"></span>
                            </button>
                            <ul class="dropdown-menu dropdown-open:opacity-100 hidden min-w-48" role="menu" aria-orientation="vertical" aria-labelledby="workspace-settings-dropdown">
                                <li>
                                    <a class="dropdown-item" href="{{ route('workspace.settings', $workspace) }}">
                                        <span class="icon-[tabler--settings] size-5"></span>
                                        Workspace Settings
                                    </a>
                                </li>
                                <li class="dropdown-divider"></li>
                                <li>
                                    <button type="button" class="dropdown-item" onclick="openModal('archiveModal')">
                                        <span class="icon-[tabler--archive] size-5"></span>
                                        Archive Workflow
                                    </button>
                                </li>
                                <li>
                                    <button type="button" class="dropdown-item text-error" onclick="openModal('deleteModal')">
                                        <span class="icon-[tabler--trash] size-5"></span>
                                        Delete Workflow
                                    </button>
                                </li>
                            </ul>
                        </div>
                        @endif
                        @else
                        <!-- Archived workspace actions -->
                        <button type="button" class="btn btn-success" onclick="openModal('restoreModal')">
                            <span class="icon-[tabler--archive-off] size-5"></span>
                            Restore Workflow
                        </button>
                        <button type="button" class="btn btn-error btn-outline" onclick="openModal('deleteModal')">
                            <span class="icon-[tabler--trash] size-5"></span>
                            Delete
                        </button>
                        @endif
                    </div>
                </div>
            </div>
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

        <!-- Archived Banner -->
        @if($workspace->status === \App\Modules\Workspace\Enums\WorkspaceStatus::ARCHIVED)
        <div class="alert alert-warning mb-4">
            <span class="icon-[tabler--archive] size-6"></span>
            <div>
                <h4 class="font-bold">This workspace is archived</h4>
                <p class="text-sm">This workspace is in read-only mode. You cannot add tasks, discussions, or make changes. Restore the workflow to enable editing.</p>
            </div>
        </div>
        @endif

        <!-- Description -->
        @if($workspace->description)
            <div class="card bg-base-100 shadow mb-6">
                <div class="card-body py-3">
                    <p class="text-base-content/70">{{ $workspace->description }}</p>
                </div>
            </div>
        @endif

        <!-- Module Tabs (Pill Style) -->
        <div class="inline-flex rounded-xl mb-[30px] flex-wrap gap-1 tablist-layout px-5!">
            <a href="{{ route('workspace.show', $workspace) }}"
               class="flex items-center gap-2 px-4 py-2.5 rounded-lg font-medium transition-all duration-200 {{ !request()->has('tab') || request('tab') === 'overview' ? 'bg-primary text-primary-content shadow-sm' : 'text-base-content/60 hover:text-primary hover:bg-primary/10' }}">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M2.50065 8.33203C2.04041 8.33203 1.66732 7.95894 1.66732 7.4987C1.66732 7.03846 2.04041 6.66537 2.50065 6.66537L17.5007 6.66536C17.9609 6.66536 18.334 7.03846 18.334 7.4987C18.334 7.95894 17.9609 8.33203 17.5007 8.33203L2.50065 8.33203Z" fill="#3BA5FF"/>
                <path d="M2.49935 13.332C2.03911 13.332 1.66602 12.9589 1.66602 12.4987C1.66602 12.0385 2.03911 11.6654 2.49935 11.6654L10.8327 11.6654C11.2929 11.6654 11.666 12.0385 11.666 12.4987C11.666 12.9589 11.2929 13.332 10.8327 13.332L2.49935 13.332Z" fill="#3BA5FF"/>
                </svg>
                <span>Overview</span>
            </a>
            @if($workspace->type->value !== 'inbox' || $showInboxTabs)
            {{-- Workspace tabs (shown for non-inbox OR verified inbox email OR completed checklist) --}}
            <a href="{{ route('workspace.show', ['workspace' => $workspace, 'tab' => 'tasks']) }}"
               class="flex items-center gap-2 px-4 py-2.5 rounded-lg font-medium transition-all duration-200 {{ request('tab') === 'tasks' ? 'bg-primary text-primary-content shadow-sm' : 'text-base-content/60 hover:text-primary hover:bg-primary/10' }}">
                <span class="icon-[{{ $isInbox ? 'tabler--ticket' : 'tabler--list-check' }}] size-5"></span>
                <span>{{ $tasksLabel }}</span>
                @if($tasks->count() > 0)
                    <span class="tab-badge badge badge-sm {{ request('tab') === 'tasks' ? 'bg-primary-content/20 text-primary-content border-0' : 'badge-ghost' }}">{{ $tasks->count() }}</span>
                @endif
            </a>
            <a href="{{ route('workspace.show', ['workspace' => $workspace, 'tab' => 'board']) }}"
               class="flex items-center gap-2 px-4 py-2.5 rounded-lg font-medium transition-all duration-200 {{ request('tab') === 'board' ? 'bg-primary text-primary-content shadow-sm' : 'text-base-content/60 hover:text-primary hover:bg-primary/10' }}">
                <span class="icon-[tabler--layout-kanban] size-5"></span>
                <span>Board</span>
            </a>
            <a href="{{ route('workspace.show', ['workspace' => $workspace, 'tab' => 'discussions']) }}"
               class="flex items-center gap-2 px-4 py-2.5 rounded-lg font-medium transition-all duration-200 {{ request('tab') === 'discussions' ? 'bg-primary text-primary-content shadow-sm' : 'text-base-content/60 hover:text-primary hover:bg-primary/10' }}">
                <span class="icon-[tabler--messages] size-5"></span>
                <span>Discussions</span>
                @if($discussions->count() > 0)
                    <span class="tab-badge badge badge-sm {{ request('tab') === 'discussions' ? 'bg-primary-content/20 text-primary-content border-0' : 'badge-ghost' }}">{{ $discussions->count() }}</span>
                @endif
            </a>
            @php
                $milestonesEnabled = auth()->user()->company->isMilestonesEnabled();
                $hasMilestones = $workspace->milestones()->exists();
                $showMilestonesTab = $milestonesEnabled || $hasMilestones;
            @endphp
            @if($showMilestonesTab)
            <a href="{{ route('milestones.index', $workspace->uuid) }}"
               class="flex items-center gap-2 px-4 py-2.5 rounded-lg font-medium transition-all duration-200 {{ request('tab') === 'milestones' ? 'bg-primary text-primary-content shadow-sm' : 'text-base-content/60 hover:text-primary hover:bg-primary/10' }}">
                <span class="icon-[tabler--flag] size-5"></span>
                <span>Milestones</span>
                @if(!$milestonesEnabled)
                    <span class="badge badge-sm badge-ghost">Disabled</span>
                @endif
            </a>
            @endif
            @if($workspace->isStandupEnabled())
            <div class="dropdown">
                <button type="button" class="dropdown-toggle flex items-center gap-2 px-4 py-2.5 rounded-lg font-medium transition-all duration-200 text-base-content/60 hover:text-primary hover:bg-primary/10" aria-haspopup="menu" aria-expanded="false">
                    <span class="icon-[tabler--checkbox] size-5"></span>
                    <span>Standup</span>
                    <span class="icon-[tabler--chevron-down] dropdown-open:rotate-180 size-4 transition-transform"></span>
                </button>
                <ul class="dropdown-menu dropdown-open:opacity-100 hidden min-w-44" role="menu">
                    <li>
                        <a class="dropdown-item" href="{{ route('standups.index', $workspace) }}">
                            <span class="icon-[tabler--checkbox] size-5"></span>
                            Daily Standup
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('standups.tracker.index', $workspace) }}">
                            <span class="icon-[tabler--chart-dots] size-5"></span>
                            Tracker
                        </a>
                    </li>
                </ul>
            </div>
            @endif
            <a href="{{ route('workspace.show', ['workspace' => $workspace, 'tab' => 'files']) }}"
               class="flex items-center gap-2 px-4 py-2.5 rounded-lg font-medium transition-all duration-200 {{ request('tab') === 'files' ? 'bg-primary text-primary-content shadow-sm' : 'text-base-content/60 hover:text-primary hover:bg-primary/10' }}">
                <span class="icon-[tabler--files] size-5"></span>
                <span>Files</span>
                @if($files->count() > 0)
                    <span class="badge badge-sm {{ request('tab') === 'files' ? 'bg-primary-content/20 text-primary-content border-0' : 'badge-ghost' }}">{{ $files->count() }}</span>
                @endif
            </a>
            <a href="{{ route('workspace.show', ['workspace' => $workspace, 'tab' => 'time']) }}"
               class="flex items-center gap-2 px-4 py-2.5 rounded-lg font-medium transition-all duration-200 {{ request('tab') === 'time' ? 'bg-primary text-primary-content shadow-sm' : 'text-base-content/60 hover:text-primary hover:bg-primary/10' }}">
                <span class="icon-[tabler--clock] size-5"></span>
                <span>Time</span>
                <span class="badge badge-sm badge-warning">Soon</span>
            </a>
            @endif
            {{-- People tab - available for all workspace types --}}
            <a href="{{ route('workspace.show', ['workspace' => $workspace, 'tab' => 'people']) }}"
               class="flex items-center gap-2 px-4 py-2.5 rounded-lg font-medium transition-all duration-200 {{ request('tab') === 'people' ? 'bg-primary text-primary-content shadow-sm' : 'text-base-content/60 hover:text-primary hover:bg-primary/10' }}">
                <span class="icon-[tabler--users] size-5"></span>
                <span>People</span>
                <span class="tab-badge badge badge-sm {{ request('tab') === 'people' ? 'bg-primary-content/20 text-primary-content border-0' : 'badge-ghost' }}">{{ $workspace->members->count() + $workspace->guests->count() }}</span>
            </a>
        </div>

        <!-- Tab Content -->
        @if(!request()->has('tab') || request('tab') === 'overview')
            @include('workspace::partials.tab-overview')
        @elseif(request('tab') === 'tasks')
            @include('workspace::partials.tab-tasks')
        @elseif(request('tab') === 'board')
            @include('workspace::partials.tab-board')
        @elseif(request('tab') === 'discussions')
            @include('workspace::partials.tab-discussions')
        @elseif(request('tab') === 'milestones')
            @include('workspace::partials.tab-coming-soon', ['title' => 'Milestones', 'icon' => 'tabler--flag', 'description' => 'Track project milestones and key deliverables.'])
        @elseif(request('tab') === 'files')
            @include('workspace::partials.tab-files')
        @elseif(request('tab') === 'time')
            @include('workspace::partials.tab-coming-soon', ['title' => 'Time Management', 'icon' => 'tabler--clock', 'description' => 'Track time spent on tasks and projects.'])
        @elseif(request('tab') === 'people')
            @include('workspace::partials.tab-people')
        @endif
    </div>
</div>

<!-- Modal Styles -->
<style>
.workspace-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 9999;
    justify-content: center;
    align-items: center;
    background: rgba(0, 0, 0, 0.5);
}
.workspace-modal.open {
    display: flex !important;
}
.workspace-modal-box {
    position: relative;
    z-index: 2;
    animation: workspaceModalSlideIn 0.2s ease-out;
    max-width: 28rem;
    width: 90%;
    border-radius: 1rem;
    padding: 1.5rem;
}
@keyframes workspaceModalSlideIn {
    from {
        opacity: 0;
        transform: scale(0.95) translateY(-10px);
    }
    to {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}
</style>

<!-- Archive Workflow Modal -->
<div id="archiveModal" class="workspace-modal" role="dialog">
    <div class="workspace-modal-box bg-base-100 shadow-xl">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-12 h-12 rounded-full bg-warning/20 flex items-center justify-center">
                <span class="icon-[tabler--archive] size-6 text-warning"></span>
            </div>
            <div>
                <h3 class="text-lg font-bold">Archive Workflow</h3>
                <p class="text-sm text-base-content/60">This action will close all tasks</p>
            </div>
        </div>
        <p class="text-base-content/70 mb-3">
            Are you sure you want to archive this workflow? Archiving will set the workspace status to closed, and all associated tasks will be marked as closed.
        </p>
        <p class="text-sm text-base-content/60 mb-4">
            You can restore the workflow later if needed.
        </p>
        <div class="flex justify-end gap-2">
            <button type="button" class="btn btn-ghost" onclick="closeModal('archiveModal')">Cancel</button>
            <form action="{{ route('workspace.archive', $workspace) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="btn btn-warning">
                    <span class="icon-[tabler--archive] size-5"></span>
                    Archive Workflow
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Restore Workflow Modal -->
<div id="restoreModal" class="workspace-modal" role="dialog">
    <div class="workspace-modal-box bg-base-100 shadow-xl">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-12 h-12 rounded-full bg-success/20 flex items-center justify-center">
                <span class="icon-[tabler--archive-off] size-6 text-success"></span>
            </div>
            <div>
                <h3 class="text-lg font-bold">Restore Workflow</h3>
                <p class="text-sm text-base-content/60">Reactivate this workspace</p>
            </div>
        </div>
        <p class="text-base-content/70 mb-4">
            Are you sure you want to restore this workflow? This will set the workspace status back to active.
        </p>
        <div class="flex justify-end gap-2">
            <button type="button" class="btn btn-ghost" onclick="closeModal('restoreModal')">Cancel</button>
            <form action="{{ route('workspace.restore', $workspace) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="btn btn-success">
                    <span class="icon-[tabler--archive-off] size-5"></span>
                    Restore Workflow
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Delete Workflow Modal -->
<div id="deleteModal" class="workspace-modal" role="dialog">
    <div class="workspace-modal-box bg-base-100 shadow-xl">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-12 h-12 rounded-full bg-error/20 flex items-center justify-center">
                <span class="icon-[tabler--trash] size-6 text-error"></span>
            </div>
            <div>
                <h3 class="text-lg font-bold">Delete Workflow</h3>
                <p class="text-sm text-base-content/60">This action cannot be undone</p>
            </div>
        </div>
        <p class="text-base-content/70 mb-4">
            Are you sure you want to permanently delete this workflow? This will delete all tasks, discussions, milestones, and files associated with this workspace.
        </p>
        <div class="alert alert-error mb-4">
            <span class="icon-[tabler--alert-triangle] size-5"></span>
            <span>This action is irreversible. All data will be permanently lost.</span>
        </div>
        <div class="flex justify-end gap-2">
            <button type="button" class="btn btn-ghost" onclick="closeModal('deleteModal')">Cancel</button>
            <form action="{{ route('workspace.destroy', $workspace) }}" method="POST" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-error">
                    <span class="icon-[tabler--trash] size-5"></span>
                    Delete Permanently
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function openModal(modalId) {
    document.getElementById(modalId).classList.add('open');
    document.body.style.overflow = 'hidden';
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('open');
    document.body.style.overflow = '';
}

// Close modal when clicking outside
document.querySelectorAll('.workspace-modal').forEach(modal => {
    modal.addEventListener('click', function(e) {
        if (e.target === this) {
            closeModal(this.id);
        }
    });
});

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.workspace-modal.open').forEach(modal => {
            closeModal(modal.id);
        });
    }
});
</script>

<!-- Include Invite Member Modal -->
@if($workspace->isOwner(auth()->user()) || $workspace->getMemberRole(auth()->user())?->isAdmin())
    @include('workspace::partials.invite-member-modal')
@endif
@endsection
