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
                    <div class="flex items-center justify-between">
                        <!-- Member Avatars and Invite Button -->
                        <div class="flex items-center gap-4 mt-2">
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
                                    <div class="flex items-center ml-6 font-semibold text-sm leading-5 text-[#17151C]">
                                        <span class="">{{ $workspace->members->count() - 5 }}</span>
                                        <span >+ More</span>
                                    </div>
                                @endif
                            </div>
                            <!-- Invite Team Member Button -->
                            @if($workspace->isOwner(auth()->user()) || $workspace->getMemberRole(auth()->user())?->isAdmin())
                            <button type="button" onclick="openInviteModal()" class="flex items-center gap-1 text-primary text-base leading-5 font-semibold cursor-pointer" title="Add Team Member">
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M12.0833 6.25C12.0833 5.09941 11.1506 4.16667 10 4.16667C8.84941 4.16667 7.91667 5.09941 7.91667 6.25C7.91667 7.40059 8.84941 8.33333 10 8.33333C11.1506 8.33333 12.0833 7.40059 12.0833 6.25ZM13.75 6.25C13.75 8.32107 12.0711 10 10 10C7.92893 10 6.25 8.32107 6.25 6.25C6.25 4.17893 7.92893 2.5 10 2.5C12.0711 2.5 13.75 4.17893 13.75 6.25Z" fill="#3BA5FF"/>
                                    <path d="M2.5 14.9102V14.7026C2.50011 13.7922 3.04259 12.9691 3.87939 12.6104C5.33042 11.9885 6.89302 11.668 8.47168 11.668H10C10.4602 11.668 10.8333 12.0411 10.8333 12.5013C10.8333 12.9615 10.4602 13.3346 10 13.3346H8.47168C7.11873 13.3346 5.77969 13.6098 4.53613 14.1427C4.31213 14.2389 4.16677 14.4589 4.16667 14.7026V14.9102C4.16667 15.4207 4.58065 15.8346 5.09115 15.8346H10C10.4602 15.8346 10.8333 16.2077 10.8333 16.668C10.8333 17.1282 10.4602 17.5013 10 17.5013H5.09115C3.66018 17.5013 2.5 16.3411 2.5 14.9102Z" fill="#3BA5FF"/>
                                    <path d="M17.5013 14.168C17.9615 14.168 18.3346 14.5411 18.3346 15.0013C18.3346 15.4615 17.9615 15.8346 17.5013 15.8346H12.5013C12.0411 15.8346 11.668 15.4615 11.668 15.0013C11.668 14.5411 12.0411 14.168 12.5013 14.168H17.5013Z" fill="#3BA5FF"/>
                                    <path d="M14.168 12.4987C14.168 12.0385 14.5411 11.6654 15.0013 11.6654C15.4615 11.6654 15.8346 12.0385 15.8346 12.4987L15.8346 17.4987C15.8346 17.9589 15.4615 18.332 15.0013 18.332C14.5411 18.332 14.168 17.9589 14.168 17.4987L14.168 12.4987Z" fill="#3BA5FF"/>
                                </svg>
                                <label class="cursor-pointer">Invite Team member</label>
                            </button>
                            @endif
                            <!-- Catch up -->
                            <div class="text-primary flex items-center gap-1">
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M10 9.16797V3.7513C10 3.64079 9.95607 3.53485 9.87793 3.45671C9.79979 3.37857 9.69384 3.33464 9.58333 3.33464C9.47283 3.33464 9.36688 3.37857 9.28874 3.45671C9.2106 3.53485 9.16667 3.64079 9.16667 3.7513V9.16797C9.16667 9.6282 8.79357 10.0013 8.33333 10.0013C7.8731 10.0013 7.5 9.6282 7.5 9.16797V4.58464C7.5 4.47413 7.45607 4.36818 7.37793 4.29004C7.29979 4.2119 7.19384 4.16797 7.08333 4.16797C6.97283 4.16797 6.86688 4.2119 6.78874 4.29004C6.7106 4.36818 6.66667 4.47413 6.66667 4.58464V11.668C6.66667 12.1282 6.29357 12.5013 5.83333 12.5013C5.3731 12.5013 5 12.1282 5 11.668V9.58463C5 9.47413 4.95607 9.36818 4.87793 9.29004C4.81926 9.23137 4.7449 9.19208 4.66471 9.17611L4.58333 9.16797C4.47283 9.16797 4.36688 9.2119 4.28874 9.29004C4.2106 9.36818 4.16667 9.47413 4.16667 9.58463V11.2513C4.16667 12.6879 4.73694 14.066 5.75277 15.0819C6.76859 16.0977 8.14674 16.668 9.58333 16.668C11.0199 16.668 12.3981 16.0977 13.4139 15.0819C14.4297 14.066 15 12.6879 15 11.2513V7.08463C15 6.97413 14.9561 6.86818 14.8779 6.79004C14.7998 6.7119 14.6938 6.66797 14.5833 6.66797C14.4728 6.66797 14.3669 6.7119 14.2887 6.79004C14.2106 6.86818 14.1667 6.97413 14.1667 7.08463V9.16797C14.1667 9.6282 13.7936 10.0013 13.3333 10.0013C12.8731 10.0013 12.5 9.6282 12.5 9.16797V4.58464C12.5 4.47413 12.4561 4.36818 12.3779 4.29004C12.2998 4.2119 12.1938 4.16797 12.0833 4.16797C11.9728 4.16797 11.8669 4.2119 11.7887 4.29004C11.7106 4.36818 11.6667 4.47413 11.6667 4.58464V9.16797C11.6667 9.6282 11.2936 10.0013 10.8333 10.0013C10.3731 10.0013 10 9.6282 10 9.16797ZM14.1667 5.04362C14.3028 5.01583 14.4423 5.0013 14.5833 5.0013C15.1359 5.0013 15.6656 5.22095 16.0563 5.61165C16.447 6.00235 16.6667 6.5321 16.6667 7.08463V11.2513C16.6667 13.1299 15.9207 14.9319 14.5923 16.2603C13.2639 17.5886 11.462 18.3346 9.58333 18.3346C7.70472 18.3346 5.90276 17.5886 4.57438 16.2603C3.246 14.9319 2.5 13.1299 2.5 11.2513V9.58463C2.5 9.0321 2.71965 8.50235 3.11035 8.11165C3.50105 7.72095 4.0308 7.5013 4.58333 7.5013L4.78923 7.51188C4.8602 7.51893 4.93054 7.52944 5 7.54362V4.58464C5 4.0321 5.21965 3.50235 5.61035 3.11165C6.00105 2.72095 6.5308 2.5013 7.08333 2.5013C7.33879 2.5013 7.58882 2.54977 7.82308 2.63883C7.90481 2.50951 8.00046 2.38821 8.11035 2.27832C8.50105 1.88762 9.0308 1.66797 9.58333 1.66797C10.1359 1.66797 10.6656 1.88762 11.0563 2.27832C11.1661 2.38811 11.2611 2.50965 11.3428 2.63883C11.5772 2.5496 11.8276 2.5013 12.0833 2.5013C12.6359 2.5013 13.1656 2.72095 13.5563 3.11165C13.947 3.50235 14.1667 4.0321 14.1667 4.58464V5.04362Z" fill="#3BA5FF"/>
                                </svg>
                                <span class="font-semibold text-base leading-5">
                                    Catch up
                                </span>
                            </div>
                        </div>
                        <div class="flex items-center justify-between gap-2">
                            <!-- <div class="flex items-center gap-2 mt-1">
                                    <span class="badge badge-{{ $workspace->type->badgeColor() }}">{{ $workspace->type->label() }}</span>
                                    <span class="badge badge-{{ $workspace->status->color() }}">{{ $workspace->status->label() }}</span>
                                </div> -->
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
