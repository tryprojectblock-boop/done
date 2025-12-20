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
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center gap-2 text-sm text-base-content/60 mb-2">
                <a href="{{ route('dashboard') }}" class="hover:text-primary">Dashboard</a>
                <span class="icon-[tabler--chevron-right] size-4"></span>
                <a href="{{ route('workspace.index') }}" class="hover:text-primary">Workspaces</a>
                <span class="icon-[tabler--chevron-right] size-4"></span>
                <span>{{ $workspace->name }}</span>
            </div>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <!-- Workspace Icon/Color -->
                    <div class="w-16 h-16 rounded-lg flex items-center justify-center text-white" style="background-color: {{ $workspace->color ?? $workspace->type->themeColor() }}">
                        <span class="icon-[{{ $workspace->type->icon() }}] size-8"></span>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-base-content">{{ $workspace->name }}</h1>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="badge badge-{{ $workspace->type->badgeColor() }}">{{ $workspace->type->label() }}</span>
                            <span class="badge badge-{{ $workspace->status->color() }}">{{ $workspace->status->label() }}</span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    @if($workspace->status !== \App\Modules\Workspace\Enums\WorkspaceStatus::ARCHIVED)
                    @if($workspace->type->value !== 'inbox' || $showInboxTabs)
                    <!-- Add Task/Ticket Button -->
                    <a href="{{ route('tasks.create', ['workspace' => $workspace->uuid]) }}" class="btn btn-primary">
                        <span class="icon-[tabler--plus] size-5"></span>
                        Add {{ $taskLabel }}
                    </a>

                    <!-- Add File Button -->
                    <a href="{{ route('drive.create', ['workspace_id' => $workspace->uuid]) }}" class="btn btn-outline btn-primary">
                        <span class="icon-[tabler--file-upload] size-5"></span>
                        Add File
                    </a>
                    @endif

                    <!-- Settings Dropdown (only for workspace owner or admin) -->
                    @if($workspace->isOwner(auth()->user()) || auth()->user()->isAdminOrHigher())
                    <div class="dropdown dropdown-end">
                        <button id="workspace-settings-dropdown" type="button" class="dropdown-toggle btn btn-ghost" aria-haspopup="menu" aria-expanded="false" aria-label="Settings">
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
        <div class="inline-flex p-1 bg-base-200 rounded-xl mb-6 flex-wrap gap-1">
            <a href="{{ route('workspace.show', $workspace) }}"
               class="flex items-center gap-2 px-4 py-2.5 rounded-lg font-medium transition-all duration-200 {{ !request()->has('tab') || request('tab') === 'overview' ? 'bg-primary text-primary-content shadow-sm' : 'text-base-content/60 hover:text-primary hover:bg-primary/10' }}">
                <span class="icon-[tabler--home] size-5"></span>
                <span>Overview</span>
            </a>
            @if($workspace->type->value !== 'inbox' || $showInboxTabs)
            {{-- Workspace tabs (shown for non-inbox OR verified inbox email OR completed checklist) --}}
            <a href="{{ route('workspace.show', ['workspace' => $workspace, 'tab' => 'tasks']) }}"
               class="flex items-center gap-2 px-4 py-2.5 rounded-lg font-medium transition-all duration-200 {{ request('tab') === 'tasks' ? 'bg-primary text-primary-content shadow-sm' : 'text-base-content/60 hover:text-primary hover:bg-primary/10' }}">
                <span class="icon-[{{ $isInbox ? 'tabler--ticket' : 'tabler--list-check' }}] size-5"></span>
                <span>{{ $tasksLabel }}</span>
                @if($tasks->count() > 0)
                    <span class="badge badge-sm {{ request('tab') === 'tasks' ? 'bg-primary-content/20 text-primary-content border-0' : 'badge-ghost' }}">{{ $tasks->count() }}</span>
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
                    <span class="badge badge-sm {{ request('tab') === 'discussions' ? 'bg-primary-content/20 text-primary-content border-0' : 'badge-ghost' }}">{{ $discussions->count() }}</span>
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
                <span class="badge badge-sm {{ request('tab') === 'people' ? 'bg-primary-content/20 text-primary-content border-0' : 'badge-ghost' }}">{{ $workspace->members->count() + $workspace->guests->count() }}</span>
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
@endsection
