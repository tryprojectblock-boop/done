@extends('layouts.app')

@section('content')
<div class="p-4 md:p-6">
    <div class="max-w mx-auto">
        <!-- Page Header -->
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-[30px]">
            <div>
                <h1 class="text-[32px] font-semibold text-[#17151C] mb-1">My Tasks</h1>
                <p class="text-[#525158] text-base leading-6 font-normal">Tasks assigned to you or you're watching</p>
            </div>
            <a href="{{ route('tasks.create') }}" class="bg-[#3BA5FF] p-2 pr-3 box-no-shadow flex items-center text-white rounded-md">
                <span class="icon-[tabler--plus] size-5 mr-1"></span>
                <span class="text-base leading-5">Add Task</span>
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

        <!-- Task Type Tabs -->
        @php
            $activeTaskTab = $filters['task_filter'] ?? 'all';
            $stats = $taskStats ?? ['total' => 0, 'open' => 0, 'closed' => 0, 'overdue' => 0];
        @endphp

        <!-- Filters & Search -->
        <div class="bg-white mb-[30px] border border-[#EDECF0] rounded-xl">
            <div class="card-body p-4">
                <form id="task-filter-form" action="{{ route('tasks.index') }}" method="GET" class="flex flex-col md:flex-row gap-4">
                    <input type="hidden" name="task_filter" value="{{ $activeTaskTab }}" />
                    <!-- Search -->
                    <div class="flex-1">
                        <div class="relative">
                            <span class="icon-[tabler--search] size-5 absolute left-3 top-1/2 -translate-y-1/2 text-base-content/50"></span>
                            <input type="text" name="search" id="search-input" value="{{ $filters['search'] ?? '' }}" placeholder="Search tasks..." class="input input-bordered border border-[#CBCBC9] w-full pl-10" autocomplete="off" />
                            <span id="search-loading" class="loading loading-spinner loading-sm absolute right-3 top-1/2 -translate-y-1/2 hidden"></span>
                        </div>
                    </div>
                    <!-- Workspace Filter (Searchable) -->
                    <input type="hidden" name="workspace_id" id="workspace-filter-value" value="{{ $filters['workspace_id'] ?? '' }}" />
                    <div class="relative">
                        <button type="button" id="workspace-filter-btn" onclick="toggleWorkspaceDropdown()" class="border border-[#B8B7BB] p-2.5 pl-3 flex items-center gap-1 text-[#17151C] rounded-md leading-4">
                            <svg width="17" height="17" viewBox="0 0 17 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M15 6.66732C15 5.74684 14.2538 5.00065 13.3333 5.00065H3.33333C2.41286 5.00065 1.66667 5.74684 1.66667 6.66732V13.334C1.66667 14.2545 2.41286 15.0006 3.33333 15.0007H13.3333C14.2538 15.0006 15 14.2545 15 13.334V6.66732ZM16.6667 13.334C16.6667 15.1749 15.1743 16.6673 13.3333 16.6673H3.33333C1.49238 16.6673 1.3422e-08 15.1749 0 13.334V6.66732C1.07378e-07 4.82637 1.49238 3.33398 3.33333 3.33398H13.3333C15.1743 3.33398 16.6667 4.82637 16.6667 6.66732V13.334Z" fill="#17151C"/>
                                <path d="M11.7615 7.94521C11.9758 7.53801 12.4797 7.38175 12.8869 7.59609C13.2941 7.81047 13.4504 8.31434 13.2361 8.72158L12.6054 9.92031C11.7703 11.5068 10.125 12.5001 8.33209 12.5001C6.53923 12.5001 4.89386 11.5068 4.05882 9.92031L3.42812 8.72158C3.21378 8.31434 3.37004 7.81047 3.77724 7.59609C4.18448 7.38175 4.68835 7.53801 4.90273 7.94521L5.53343 9.14394C6.08026 10.1829 7.158 10.8334 8.33209 10.8334C9.50618 10.8334 10.5839 10.1829 11.1308 9.14394L11.7615 7.94521Z" fill="#17151C"/>
                                <path d="M10.8307 3.33333C10.8307 2.41286 10.0845 1.66667 9.16406 1.66667H7.4974C6.57692 1.66667 5.83073 2.41286 5.83073 3.33333H10.8307ZM12.4974 4.16667C12.4974 4.6269 12.1243 5 11.6641 5H4.9974C4.53716 5 4.16406 4.6269 4.16406 4.16667V3.33333C4.16406 1.49238 5.65645 0 7.4974 0H9.16406C11.005 0 12.4974 1.49238 12.4974 3.33333V4.16667Z" fill="#17151C"/>
                            </svg>
                            <span id="workspace-filter-label">
                                @if(!empty($filters['workspace_id']))
                                    {{ $workspaces->firstWhere('id', $filters['workspace_id'])?->name ?? 'All Workspaces' }}
                                @else
                                    All Workspaces
                                @endif
                            </span>
                            <span class="icon-[tabler--chevron-down] size-4"></span>
                        </button>
                        <div id="workspace-dropdown" class="hidden absolute left-0 top-full mt-2 w-64 bg-base-100 rounded-xl shadow-xl border border-base-300 z-[100]">
                            <!-- Search Input -->
                            <div class="p-2 border-b border-base-200">
                                <div class="relative">
                                    <span class="icon-[tabler--search] size-4 absolute left-3 top-1/2 -translate-y-1/2 text-base-content/40"></span>
                                    <input type="text"
                                           id="workspace-search"
                                           placeholder="Search workspaces..."
                                           class="input input-sm input-bordered w-full pl-9"
                                           oninput="searchWorkspaces(this.value)"
                                           autocomplete="off" />
                                </div>
                            </div>
                            <!-- Options List -->
                            <ul class="p-2 max-h-64 overflow-y-auto" id="workspace-list">
                                <li class="workspace-option" data-name="all">
                                    <button type="button" onclick="selectWorkspace('', 'All Workspaces')" class="w-full text-left px-3 py-2 rounded-lg hover:bg-base-200 flex items-center gap-2">
                                        <span class="icon-[tabler--layout-grid] size-4 text-base-content/60"></span>
                                        <span>All Workspaces</span>
                                    </button>
                                </li>
                                @if($workspaces->count() > 0)
                                    <li class="border-t border-base-200 my-1"></li>
                                    @foreach($workspaces as $ws)
                                        <li class="workspace-option" data-name="{{ strtolower($ws->name) }}">
                                            <button type="button" onclick="selectWorkspace('{{ $ws->id }}', '{{ $ws->name }}')" class="w-full text-left px-3 py-2 rounded-lg hover:bg-base-200 flex items-center gap-2">
                                                <span class="icon-[tabler--folder] size-4 text-primary"></span>
                                                <span>{{ $ws->name }}</span>
                                            </button>
                                        </li>
                                    @endforeach
                                @endif
                            </ul>
                        </div>
                    </div>
                    <!-- Status Filter (Shows when workspace selected) -->
                    @if(!empty($filters['workspace_id']) && $statuses->count() > 0)
                        <input type="hidden" name="status_id" id="status-filter-value" value="{{ $filters['status_id'] ?? '' }}" />
                        <div class="relative">
                            <button type="button" id="status-filter-btn" onclick="toggleStatusDropdown()" class="btn btn-ghost btn-sm gap-2 border border-base-300 min-w-36">
                                <span class="icon-[tabler--circle-dot] size-4"></span>
                                <span id="status-filter-label">
                                    @if(!empty($filters['status_id']))
                                        {{ $statuses->firstWhere('id', $filters['status_id'])?->name ?? 'All Statuses' }}
                                    @else
                                        All Statuses
                                    @endif
                                </span>
                                <span class="icon-[tabler--chevron-down] size-4"></span>
                            </button>
                            <div id="status-dropdown" class="hidden absolute left-0 top-full mt-2 w-56 bg-base-100 rounded-xl shadow-xl border border-base-300 z-[100]">
                                <ul class="p-2 max-h-64 overflow-y-auto" id="status-list">
                                    <li>
                                        <button type="button" onclick="selectStatus('', 'All Statuses')" class="w-full text-left px-3 py-2 rounded-lg hover:bg-base-200 flex items-center gap-2">
                                            <span class="icon-[tabler--circle-dot] size-4 text-base-content/60"></span>
                                            <span>All Statuses</span>
                                        </button>
                                    </li>
                                    <li class="border-t border-base-200 my-1"></li>
                                    @foreach($statuses as $status)
                                        <li>
                                            <button type="button" onclick="selectStatus('{{ $status->id }}', '{{ $status->name }}')" class="w-full text-left px-3 py-2 rounded-lg hover:bg-base-200 flex items-center gap-2">
                                                <span class="w-3 h-3 rounded-full" style="background-color: {{ $status->background_color }};"></span>
                                                <span>{{ $status->name }}</span>
                                            </button>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif
                    <!-- Assignee Filter (Searchable) - Only visible to owner/admin -->
                    @if($canViewAllAssignees ?? false)
                        <input type="hidden" name="assignee_id" id="assignee-filter-value" value="{{ $filters['assignee_id'] ?? '' }}" />
                        <div class="relative">
                            <button type="button" id="assignee-filter-btn" onclick="toggleAssigneeDropdown()" class="border border-[#B8B7BB] p-2.5 pl-3 flex items-center gap-1 text-[#17151C] rounded-md leading-4">
                                <svg width="14" height="15" viewBox="0 0 14 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M8.7513 3.75C8.7513 2.59941 7.81856 1.66667 6.66797 1.66667C5.51738 1.66667 4.58464 2.59941 4.58464 3.75C4.58464 4.90059 5.51738 5.83333 6.66797 5.83333C7.81856 5.83333 8.7513 4.90059 8.7513 3.75ZM10.418 3.75C10.418 5.82107 8.73904 7.5 6.66797 7.5C4.5969 7.5 2.91797 5.82107 2.91797 3.75C2.91797 1.67893 4.5969 0 6.66797 0C8.73904 0 10.418 1.67893 10.418 3.75Z" fill="#17151C"/>
                                    <path d="M0 12.4383C0 11.3771 0.654499 10.426 1.64551 10.0465L1.92952 9.9375C3.26236 9.42749 4.67806 9.16602 6.10514 9.16602H7.10937L7.6473 9.17822C8.90197 9.23408 10.1414 9.48495 11.3208 9.92285L11.6683 10.0514C12.669 10.423 13.3333 11.3781 13.3333 12.4456C13.3333 13.856 12.19 14.9993 10.7796 14.9993H2.56103C1.14664 14.9993 6.55488e-05 13.8527 0 12.4383ZM1.66667 12.4383C1.66673 12.9322 2.06712 13.3326 2.56103 13.3327H10.7796C11.2695 13.3327 11.6667 12.9355 11.6667 12.4456C11.6666 12.0749 11.4356 11.743 11.0881 11.6139L10.7406 11.4854C9.72363 11.1078 8.65504 10.8914 7.57324 10.8433L7.10937 10.8327H6.10514C4.88166 10.8327 3.66791 11.0571 2.52523 11.4943L2.24121 11.6025C1.8951 11.7351 1.66667 12.0677 1.66667 12.4383Z" fill="#17151C"/>
                                </svg>
                                <span id="assignee-filter-label">
                                    @if(($filters['assignee_id'] ?? '') == auth()->id())
                                        Assigned to Me
                                    @elseif(!empty($filters['assignee_id']))
                                        {{ $users->firstWhere('id', $filters['assignee_id'])?->name ?? 'All Assignees' }}
                                    @else
                                        All Assignees
                                    @endif
                                </span>
                                <span class="icon-[tabler--chevron-down] size-4"></span>
                            </button>
                            <div id="assignee-dropdown" class="hidden absolute left-0 top-full mt-2 w-64 bg-base-100 rounded-xl shadow-xl border border-base-300 z-[100]">
                                <!-- Search Input -->
                                <div class="p-2 border-b border-base-200">
                                    <div class="relative">
                                        <span class="icon-[tabler--search] size-4 absolute left-3 top-1/2 -translate-y-1/2 text-base-content/40"></span>
                                        <input type="text"
                                               id="assignee-search"
                                               placeholder="Search members..."
                                               class="input input-sm input-bordered w-full pl-9"
                                               oninput="searchAssignees(this.value)"
                                               autocomplete="off" />
                                    </div>
                                </div>
                                <!-- Options List -->
                                <ul class="p-2 max-h-64 overflow-y-auto" id="assignee-list">
                                    <li class="assignee-option" data-name="all">
                                        <button type="button" onclick="selectAssignee('', 'All Assignees')" class="w-full text-left px-3 py-2 rounded-lg hover:bg-base-200 flex items-center gap-2">
                                            <span class="icon-[tabler--users] size-4 text-base-content/60"></span>
                                            <span>All Assignees</span>
                                        </button>
                                    </li>
                                    <li class="assignee-option" data-name="assigned to me">
                                        <button type="button" onclick="selectAssignee('{{ auth()->id() }}', 'Assigned to Me')" class="w-full text-left px-3 py-2 rounded-lg hover:bg-base-200 flex items-center gap-2">
                                            <span class="icon-[tabler--user-check] size-4 text-primary"></span>
                                            <span class="font-medium">Assigned to Me</span>
                                        </button>
                                    </li>
                                    @if($users->count() > 0)
                                        <li class="border-t border-base-200 my-1"></li>
                                        <li class="px-3 py-1 text-xs text-base-content/50 uppercase tracking-wide">Team Members</li>
                                        @foreach($users as $member)
                                            <li class="assignee-option" data-name="{{ strtolower($member->name) }}">
                                                <button type="button" onclick="selectAssignee('{{ $member->id }}', '{{ $member->name }}')" class="w-full text-left px-3 py-2 rounded-lg hover:bg-base-200 flex items-center gap-2">
                                                    <div class="avatar">
                                                        <div class="w-5 h-5 rounded-full">
                                                            <img src="{{ $member->avatar_url }}" alt="{{ $member->name }}" />
                                                        </div>
                                                    </div>
                                                    <span>{{ $member->name }}</span>
                                                </button>
                                            </li>
                                        @endforeach
                                    @endif
                                </ul>
                            </div>
                        </div>
                    @endif
                    <!-- Priority Filter (Searchable) -->
                    <input type="hidden" name="priority" id="priority-filter-value" value="{{ $filters['priority'] ?? '' }}" />
                    <div class="relative">
                        <button type="button" id="priority-filter-btn" onclick="togglePriorityDropdown()" class="border border-[#B8B7BB] p-2.5 pl-3 flex items-center gap-1 text-[#17151C] rounded-md leading-4">
                            <svg width="15" height="15" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M0 14.1667V3.33333C6.7111e-09 1.49238 1.49238 0 3.33333 0H13.3333L13.4481 0.00813802C13.7128 0.0449043 13.9466 0.207237 14.0723 0.448405C14.2157 0.723956 14.1942 1.0565 14.0161 1.31104L11.4331 5L14.0161 8.68896C14.1942 8.9435 14.2157 9.27604 14.0723 9.55159C13.9287 9.82711 13.644 10 13.3333 10H2.5C2.03976 10 1.66667 10.3731 1.66667 10.8333V14.1667C1.66667 14.6269 1.29357 15 0.833333 15C0.373096 15 0 14.6269 0 14.1667ZM1.66667 8.47819C1.92755 8.38588 2.2075 8.33333 2.5 8.33333H11.7326L9.73389 5.4777C9.53319 5.19084 9.53319 4.80916 9.73389 4.5223L11.7326 1.66667H3.33333C2.41286 1.66667 1.66667 2.41286 1.66667 3.33333V8.47819Z" fill="#17151C"/>
                            </svg>
                            <span id="priority-filter-label">
                                @if(!empty($filters['priority']))
                                    {{ \App\Modules\Task\Enums\TaskPriority::tryFrom($filters['priority'])?->label() ?? 'All Priorities' }}
                                @else
                                    All Priorities
                                @endif
                            </span>
                            <span class="icon-[tabler--chevron-down] size-4"></span>
                        </button>
                        <div id="priority-dropdown" class="hidden absolute left-0 top-full mt-2 w-48 bg-base-100 rounded-xl shadow-xl border border-base-300 z-[100]">
                            <ul class="p-2" id="priority-list">
                                <li>
                                    <button type="button" onclick="selectPriority('', 'All Priorities')" class="w-full text-left px-3 py-2 rounded-lg hover:bg-base-200 flex items-center gap-2">
                                        <span class="icon-[tabler--flag] size-4 text-base-content/60"></span>
                                        <span>All Priorities</span>
                                    </button>
                                </li>
                                <li class="border-t border-base-200 my-1"></li>
                                @foreach(\App\Modules\Task\Enums\TaskPriority::cases() as $priority)
                                    <li>
                                        <button type="button" onclick="selectPriority('{{ $priority->value }}', '{{ $priority->label() }}')" class="w-full text-left px-3 py-2 rounded-lg hover:bg-base-200 flex items-center gap-2">
                                            <span class="icon-[{{ $priority->icon() }}] size-4" style="color: {{ $priority->color() }}"></span>
                                            <span>{{ $priority->label() }}</span>
                                        </button>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    @php
                        // For regular members, don't count assignee_id in clear button since it's forced
                        $clearableFilters = collect($filters ?? [])->filter(function ($value, $key) use ($canViewAllAssignees) {
                            if (empty($value)) return false;
                            if ($key === 'task_filter' && $value === 'all') return false;
                            if ($key === 'is_closed') return false;
                            if ($key === 'overdue_only') return false;
                            // For regular members, assignee_id is forced so don't count it
                            if (!($canViewAllAssignees ?? false) && $key === 'assignee_id') return false;
                            return true;
                        });
                    @endphp
                    @if($clearableFilters->isNotEmpty())
                        <a href="{{ route('tasks.index') }}" class="btn btn-ghost btn-sm text-error">
                            <span class="icon-[tabler--x] size-4"></span>
                            Clear
                        </a>
                    @endif
                </form>
            </div>
        </div>

        <div class="inline-flex p-1 gap-4 bg-base-200 rounded-xl mb-5">
            <a href="{{ route('tasks.index', array_merge(request()->except('task_filter', 'page'), ['task_filter' => 'all'])) }}"
               class="flex items-center gap-1 p-2 pr-3 rounded-lg font-medium transition-all duration-200 {{ $activeTaskTab === 'all' || $activeTaskTab === '' ? 'bg-primary text-primary-content shadow-sm' : 'text-base-content/60 hover:text-primary hover:bg-primary/10' }}">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M15.0013 1.66602C16.8422 1.66602 18.3346 3.1584 18.3346 4.99935V14.9993C18.3346 16.8403 16.8422 18.3327 15.0013 18.3327H5.0013C3.16035 18.3327 1.66797 16.8403 1.66797 14.9993V4.99935C1.66797 3.1584 3.16035 1.66602 5.0013 1.66602H15.0013ZM5.0013 3.33268C4.08083 3.33268 3.33464 4.07887 3.33464 4.99935V14.9993C3.33464 15.9198 4.08083 16.666 5.0013 16.666H15.0013C15.9218 16.666 16.668 15.9198 16.668 14.9993V4.99935C16.668 4.07887 15.9218 3.33268 15.0013 3.33268H5.0013ZM6.2513 10.416C6.94166 10.416 7.5013 10.9757 7.5013 11.666C7.5013 12.3563 6.94166 12.916 6.2513 12.916C5.56094 12.916 5.0013 12.3563 5.0013 11.666C5.0013 10.9757 5.56094 10.416 6.2513 10.416ZM14.168 10.8327C14.6282 10.8327 15.0013 11.2058 15.0013 11.666C15.0013 12.1263 14.6282 12.4993 14.168 12.4993H10.0013C9.54105 12.4993 9.16797 12.1263 9.16797 11.666C9.16797 11.2058 9.54105 10.8327 10.0013 10.8327H14.168ZM6.2513 6.24935C6.94166 6.24935 7.5013 6.80899 7.5013 7.49935C7.5013 8.18971 6.94166 8.74935 6.2513 8.74935C5.56094 8.74935 5.0013 8.18971 5.0013 7.49935C5.0013 6.80899 5.56094 6.24935 6.2513 6.24935ZM14.168 6.66601C14.6282 6.66601 15.0013 7.03911 15.0013 7.49935C15.0013 7.95958 14.6282 8.33268 14.168 8.33268H10.0013C9.54105 8.33268 9.16797 7.95958 9.16797 7.49935C9.16797 7.03911 9.54105 6.66601 10.0013 6.66601H14.168Z" fill="white"/>
                </svg>
                <span class="leading-5">All Tasks</span>
                <span class="bg-[#69b7fe] w-6 h-6 flex items-center justify-center rounded-full text-xs {{ $activeTaskTab === 'all' || $activeTaskTab === '' ? 'bg-primary-content/20 text-primary-content border-0' : '' }}">{{ $stats['open'] }}</span>
            </a>

            <a href="{{ route('tasks.index', array_merge(request()->except('task_filter', 'page'), ['task_filter' => 'overdue'])) }}"
               class="flex items-center gap-1 p-2 pr-3 rounded-lg font-medium transition-all duration-200 {{ $activeTaskTab === 'overdue' ? 'bg-error text-error-content shadow-sm' : 'text-base-content/60 hover:text-error hover:bg-error/10' }}">
                <span class="icon-[tabler--alert-triangle] size-5"></span>
                <span class="leading-5">Overdue</span>
                <span class="bg-[#fb4141] w-6 h-6 flex items-center justify-center rounded-full text-xs text-white {{ $activeTaskTab === 'overdue' ? 'bg-error-content/20 text-error-content border-0' : 'badge-error' }}">{{ $stats['overdue'] }}</span>
            </a>
            <a href="{{ route('tasks.index', array_merge(request()->except('task_filter', 'page'), ['task_filter' => 'closed'])) }}"
               class="flex items-center gap-1 p-2 pr-3 rounded-lg font-medium transition-all duration-200 {{ $activeTaskTab === 'closed' ? 'bg-success text-success-content shadow-sm' : 'text-base-content/60 hover:text-success hover:bg-success/10' }}">
                <span class="icon-[tabler--circle-check] size-5"></span>
                <span class="leading-5">Closed</span>
                <span class="bg-[#00CA4B] w-6 h-6 flex items-center justify-center rounded-full text-xs text-white {{ $activeTaskTab === 'closed' ? 'bg-success-content/20 text-success-content border-0' : 'badge-success' }}">{{ $stats['closed'] }}</span>
            </a>
        </div>

        <!-- View Toggle -->
        <div class="flex items-end justify-between mb-5">
            <div id="tasks-count" class="text-sm text-[#525158] leading-[18px] font-normal">
                {{ $tasks->total() }} {{ Str::plural('task', $tasks->total()) }} found
            </div>
            <div class="flex items-center gap-0.5 rounded-lg p-1 bg-[#EDECF0]">
                <a href="{{ route('tasks.index', array_merge(request()->query(), ['view' => 'card'])) }}"
                   class="box-no-shadow py-2 px-2 rounded-md {{ $viewMode === 'card' ? 'bg-white' : 'bg-transparent' }}">
                    <svg width="15" height="12" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M4.16667 8.33333C5.54737 8.33333 6.66667 9.45258 6.66667 10.8333V12.5C6.66667 13.8807 5.54737 15 4.16667 15H2.5C1.11929 15 0 13.8807 0 12.5V10.8333C0 9.45258 1.11929 8.33333 2.5 8.33333H4.16667ZM12.5 8.33333C13.8807 8.33333 15 9.45258 15 10.8333V12.5C15 13.8807 13.8807 15 12.5 15H10.8333C9.45258 15 8.33333 13.8807 8.33333 12.5V10.8333C8.33333 9.45258 9.45258 8.33333 10.8333 8.33333H12.5ZM2.5 10C2.03977 10 1.66667 10.3731 1.66667 10.8333V12.5C1.66667 12.9602 2.03977 13.3333 2.5 13.3333H4.16667C4.6269 13.3333 5 12.9602 5 12.5V10.8333C5 10.3731 4.6269 10 4.16667 10H2.5ZM10.8333 10C10.3731 10 10 10.3731 10 10.8333V12.5C10 12.9602 10.3731 13.3333 10.8333 13.3333H12.5C12.9602 13.3333 13.3333 12.9602 13.3333 12.5V10.8333C13.3333 10.3731 12.9602 10 12.5 10H10.8333ZM4.16667 0C5.54737 0 6.66667 1.11929 6.66667 2.5V4.16667C6.66667 5.54737 5.54737 6.66667 4.16667 6.66667H2.5C1.11929 6.66667 0 5.54737 0 4.16667V2.5C0 1.11929 1.11929 0 2.5 0H4.16667ZM12.5 0C13.8807 0 15 1.11929 15 2.5V4.16667C15 5.54737 13.8807 6.66667 12.5 6.66667H10.8333C9.45258 6.66667 8.33333 5.54737 8.33333 4.16667V2.5C8.33333 1.11929 9.45258 0 10.8333 0H12.5ZM2.5 1.66667C2.03977 1.66667 1.66667 2.03977 1.66667 2.5V4.16667C1.66667 4.6269 2.03977 5 2.5 5H4.16667C4.6269 5 5 4.6269 5 4.16667V2.5C5 2.03977 4.6269 1.66667 4.16667 1.66667H2.5ZM10.8333 1.66667C10.3731 1.66667 10 2.03977 10 2.5V4.16667C10 4.6269 10.3731 5 10.8333 5H12.5C12.9602 5 13.3333 4.6269 13.3333 4.16667V2.5C13.3333 2.03977 12.9602 1.66667 12.5 1.66667H10.8333Z" fill="#B8B7BB"/>
                    </svg>
                </a>
                <a href="{{ route('tasks.index', array_merge(request()->query(), ['view' => 'table'])) }}"
                   class="box-no-shadow py-2 px-2 rounded-md {{ $viewMode === 'table' ? 'bg-white' : 'bg-transparent' }}">
                    <svg width="15" height="12" viewBox="0 0 15 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M1.25 9.16602C1.94025 9.16602 2.49982 9.72581 2.5 10.416C2.5 11.1064 1.94036 11.666 1.25 11.666C0.559644 11.666 0 11.1064 0 10.416C0.000175188 9.72581 0.559752 9.16602 1.25 9.16602ZM14.168 9.58301C14.6281 9.58301 15.0008 9.95593 15.001 10.416C15.001 10.8763 14.6282 11.25 14.168 11.25H5.00098C4.54089 11.2498 4.16797 10.8761 4.16797 10.416C4.16814 9.95604 4.541 9.58318 5.00098 9.58301H14.168ZM1.25 4.58301C1.94036 4.58301 2.5 5.14265 2.5 5.83301C2.5 6.52336 1.94036 7.08301 1.25 7.08301C0.559644 7.08301 1.61065e-08 6.52336 0 5.83301C0 5.14265 0.559644 4.58301 1.25 4.58301ZM14.168 5C14.6282 5 15.001 5.37277 15.001 5.83301C15.0009 6.29317 14.6282 6.66602 14.168 6.66602H5.00098C4.54094 6.66584 4.16806 6.29306 4.16797 5.83301C4.16797 5.37288 4.54089 5.00018 5.00098 5H14.168ZM1.25 0C1.94036 1.61105e-08 2.5 0.559644 2.5 1.25C2.49982 1.94021 1.94025 2.5 1.25 2.5C0.559753 2.5 0.000175897 1.94021 0 1.25C0 0.559644 0.559644 0 1.25 0ZM14.168 0.416992C14.6282 0.416993 15.001 0.789763 15.001 1.25C15.0009 1.71016 14.6282 2.08301 14.168 2.08301H5.00098C4.54094 2.08283 4.16806 1.71005 4.16797 1.25C4.16797 0.789871 4.54089 0.417168 5.00098 0.416992H14.168Z" fill="#525158"/>
                    </svg>
                </a>
            </div>
        </div>

        <!-- Tasks Content -->
        <div id="tasks-content">
            @if($tasks->isEmpty())
                <div class="card bg-base-100 shadow">
                    <div class="card-body text-center py-12">
                        <div class="text-base-content/50">
                            <span class="icon-[tabler--checkbox] size-12 block mx-auto mb-4"></span>
                            <p class="text-sm leading-[18px] font-normal">No tasks found</p>
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
            @endif
        </div>

        <!-- Pagination -->
        <div id="tasks-pagination" class="mt-6">
            @if($tasks->hasPages())
                {{ $tasks->withQueryString()->links() }}
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search-input');
    const searchLoading = document.getElementById('search-loading');
    const tasksContent = document.getElementById('tasks-content');
    const tasksCount = document.getElementById('tasks-count');
    const tasksPagination = document.getElementById('tasks-pagination');
    const filterForm = document.getElementById('task-filter-form');

    let searchTimeout = null;
    let currentSearch = searchInput.value;

    // Real-time search
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();

        searchTimeout = setTimeout(() => {
            if (query !== currentSearch) {
                currentSearch = query;
                performSearch();
            }
        }, 300);
    });

    // Prevent form submission on Enter, use AJAX instead
    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            clearTimeout(searchTimeout);
            currentSearch = this.value.trim();
            performSearch();
        }
    });

    async function performSearch() {
        const formData = new FormData(filterForm);
        const params = new URLSearchParams(formData);
        params.set('ajax', '1');

        searchLoading.classList.remove('hidden');

        try {
            const response = await fetch(`{{ route('tasks.index') }}?${params.toString()}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            // Update tasks content
            tasksContent.innerHTML = data.html || `
                <div class="card bg-base-100 shadow">
                    <div class="card-body text-center py-12">
                        <div class="text-base-content/50">
                            <span class="icon-[tabler--checkbox] size-12 block mx-auto mb-4"></span>
                            <p class="text-lg font-medium">No tasks found</p>
                            <p class="text-sm">Try adjusting your search or filters</p>
                        </div>
                    </div>
                </div>
            `;

            // Update count
            tasksCount.textContent = `${data.total} ${data.total === 1 ? 'task' : 'tasks'} found`;

            // Update pagination
            tasksPagination.innerHTML = data.pagination || '';

            // Update URL without reload
            const url = new URL(window.location);
            if (currentSearch) {
                url.searchParams.set('search', currentSearch);
            } else {
                url.searchParams.delete('search');
            }
            window.history.replaceState({}, '', url);

        } catch (error) {
            console.error('Search error:', error);
        } finally {
            searchLoading.classList.add('hidden');
        }
    }
});

// Dropdown states
let workspaceDropdownOpen = false;
let statusDropdownOpen = false;
let assigneeDropdownOpen = false;
let priorityDropdownOpen = false;

// Workspace dropdown
function toggleWorkspaceDropdown() {
    const dropdown = document.getElementById('workspace-dropdown');
    const searchInput = document.getElementById('workspace-search');
    workspaceDropdownOpen = !workspaceDropdownOpen;

    closeAllDropdowns('workspace');

    if (workspaceDropdownOpen) {
        dropdown.classList.remove('hidden');
        searchInput.value = '';
        searchWorkspaces('');
        setTimeout(() => searchInput.focus(), 50);
    } else {
        dropdown.classList.add('hidden');
    }
}

function searchWorkspaces(query) {
    const options = document.querySelectorAll('#workspace-list .workspace-option');
    const lowerQuery = query.toLowerCase().trim();

    options.forEach(option => {
        const name = option.dataset.name || '';
        if (lowerQuery === '' || name.includes(lowerQuery) || name === 'all') {
            option.style.display = '';
        } else {
            option.style.display = 'none';
        }
    });
}

function selectWorkspace(value, label) {
    document.getElementById('workspace-filter-value').value = value;
    document.getElementById('workspace-filter-label').textContent = label;
    document.getElementById('workspace-dropdown').classList.add('hidden');
    workspaceDropdownOpen = false;
    // Clear status filter when workspace changes
    const statusInput = document.getElementById('status-filter-value');
    if (statusInput) statusInput.value = '';
    document.getElementById('task-filter-form').submit();
}

// Status dropdown
function toggleStatusDropdown() {
    const dropdown = document.getElementById('status-dropdown');
    if (!dropdown) return;
    statusDropdownOpen = !statusDropdownOpen;

    closeAllDropdowns('status');

    if (statusDropdownOpen) {
        dropdown.classList.remove('hidden');
    } else {
        dropdown.classList.add('hidden');
    }
}

function selectStatus(value, label) {
    document.getElementById('status-filter-value').value = value;
    document.getElementById('status-filter-label').textContent = label;
    document.getElementById('status-dropdown').classList.add('hidden');
    statusDropdownOpen = false;
    document.getElementById('task-filter-form').submit();
}

// Assignee dropdown
function toggleAssigneeDropdown() {
    const dropdown = document.getElementById('assignee-dropdown');
    const searchInput = document.getElementById('assignee-search');
    assigneeDropdownOpen = !assigneeDropdownOpen;

    closeAllDropdowns('assignee');

    if (assigneeDropdownOpen) {
        dropdown.classList.remove('hidden');
        searchInput.value = '';
        searchAssignees('');
        setTimeout(() => searchInput.focus(), 50);
    } else {
        dropdown.classList.add('hidden');
    }
}

function searchAssignees(query) {
    const options = document.querySelectorAll('#assignee-list .assignee-option');
    const lowerQuery = query.toLowerCase().trim();

    options.forEach(option => {
        const name = option.dataset.name || '';
        if (lowerQuery === '' || name.includes(lowerQuery) || name === 'all' || name === 'assigned to me') {
            option.style.display = '';
        } else {
            option.style.display = 'none';
        }
    });
}

function selectAssignee(value, label) {
    document.getElementById('assignee-filter-value').value = value;
    document.getElementById('assignee-filter-label').textContent = label;
    document.getElementById('assignee-dropdown').classList.add('hidden');
    assigneeDropdownOpen = false;
    document.getElementById('task-filter-form').submit();
}

// Priority dropdown
function togglePriorityDropdown() {
    const dropdown = document.getElementById('priority-dropdown');
    priorityDropdownOpen = !priorityDropdownOpen;

    closeAllDropdowns('priority');

    if (priorityDropdownOpen) {
        dropdown.classList.remove('hidden');
    } else {
        dropdown.classList.add('hidden');
    }
}

function selectPriority(value, label) {
    document.getElementById('priority-filter-value').value = value;
    document.getElementById('priority-filter-label').textContent = label;
    document.getElementById('priority-dropdown').classList.add('hidden');
    priorityDropdownOpen = false;
    document.getElementById('task-filter-form').submit();
}

// Close all dropdowns except the specified one
function closeAllDropdowns(except) {
    if (except !== 'workspace') {
        document.getElementById('workspace-dropdown')?.classList.add('hidden');
        workspaceDropdownOpen = false;
    }
    if (except !== 'status') {
        document.getElementById('status-dropdown')?.classList.add('hidden');
        statusDropdownOpen = false;
    }
    if (except !== 'assignee') {
        document.getElementById('assignee-dropdown')?.classList.add('hidden');
        assigneeDropdownOpen = false;
    }
    if (except !== 'priority') {
        document.getElementById('priority-dropdown')?.classList.add('hidden');
        priorityDropdownOpen = false;
    }
}

// Close dropdowns when clicking outside
document.addEventListener('click', function(e) {
    const workspaceDropdown = document.getElementById('workspace-dropdown');
    const workspaceBtn = document.getElementById('workspace-filter-btn');
    const statusDropdown = document.getElementById('status-dropdown');
    const statusBtn = document.getElementById('status-filter-btn');
    const assigneeDropdown = document.getElementById('assignee-dropdown');
    const assigneeBtn = document.getElementById('assignee-filter-btn');
    const priorityDropdown = document.getElementById('priority-dropdown');
    const priorityBtn = document.getElementById('priority-filter-btn');

    if (workspaceDropdown && workspaceBtn && !workspaceDropdown.contains(e.target) && !workspaceBtn.contains(e.target)) {
        workspaceDropdown.classList.add('hidden');
        workspaceDropdownOpen = false;
    }
    if (statusDropdown && statusBtn && !statusDropdown.contains(e.target) && !statusBtn.contains(e.target)) {
        statusDropdown.classList.add('hidden');
        statusDropdownOpen = false;
    }
    if (assigneeDropdown && assigneeBtn && !assigneeDropdown.contains(e.target) && !assigneeBtn.contains(e.target)) {
        assigneeDropdown.classList.add('hidden');
        assigneeDropdownOpen = false;
    }
    if (priorityDropdown && priorityBtn && !priorityDropdown.contains(e.target) && !priorityBtn.contains(e.target)) {
        priorityDropdown.classList.add('hidden');
        priorityDropdownOpen = false;
    }
});
</script>
@endpush
@endsection
