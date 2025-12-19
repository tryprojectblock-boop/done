@php
    $isInbox = $workspace->type->value === 'inbox';
    $taskLabel = $isInbox ? 'Ticket' : 'Task';
    $tasksLabel = $isInbox ? 'Tickets' : 'Tasks';
    $taskLabelLower = $isInbox ? 'ticket' : 'task';
    $tasksLabelLower = $isInbox ? 'tickets' : 'tasks';
    $filterStatuses = $workspace->workflow?->statuses ?? collect();
    $workspaceUsers = $workspace->members ?? collect();
    $currentFilters = $taskFilters ?? [];
@endphp
<div class="space-y-4">
    @php
        $stats = $taskStats ?? ['total' => 0, 'open' => 0, 'closed' => 0, 'overdue' => 0];
        $totalTasks = $stats['total'];
    @endphp

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h2 class="text-lg font-semibold">{{ $tasksLabel }}</h2>
            <p class="text-sm text-base-content/60">{{ $totalTasks }} {{ $totalTasks === 1 ? $taskLabelLower : $tasksLabelLower }} in this workspace</p>
        </div>
    </div>

    <!-- Task Stats -->
    <div class="stats stats-horizontal shadow w-full">
        <div class="stat">
            <div class="stat-figure text-primary">
                <span class="icon-[tabler--list-check] size-8"></span>
            </div>
            <div class="stat-title">Open</div>
            <div class="stat-value text-primary">{{ $stats['open'] }}</div>
        </div>
        <div class="stat">
            <div class="stat-figure text-success">
                <span class="icon-[tabler--check] size-8"></span>
            </div>
            <div class="stat-title">Completed</div>
            <div class="stat-value text-success">{{ $stats['closed'] }}</div>
        </div>
        <div class="stat">
            <div class="stat-figure text-error">
                <span class="icon-[tabler--alert-triangle] size-8"></span>
            </div>
            <div class="stat-title">Overdue</div>
            <div class="stat-value text-error">{{ $stats['overdue'] }}</div>
        </div>
    </div>

    <!-- Task Type Tabs -->
    @php
        $activeTaskTab = $currentFilters['task_filter'] ?? 'all';
    @endphp
    <div class="flex flex-col gap-4">
        <div class="inline-flex p-1 bg-base-200 rounded-xl">
            <a href="{{ route('workspace.show', ['workspace' => $workspace, 'tab' => 'tasks']) }}"
               class="flex items-center gap-2 px-5 py-2.5 rounded-lg font-medium transition-all duration-200 {{ $activeTaskTab === 'all' || $activeTaskTab === '' ? 'bg-primary text-primary-content shadow-sm' : 'text-base-content/60 hover:text-primary hover:bg-primary/10' }}">
                <span class="icon-[tabler--list-check] size-5"></span>
                <span>All {{ $tasksLabel }}</span>
                <span class="badge badge-sm {{ $activeTaskTab === 'all' || $activeTaskTab === '' ? 'bg-primary-content/20 text-primary-content border-0' : '' }}">{{ $stats['open'] }}</span>
            </a>
            <a href="{{ route('workspace.show', ['workspace' => $workspace, 'tab' => 'tasks', 'task_filter' => 'overdue']) }}"
               class="flex items-center gap-2 px-5 py-2.5 rounded-lg font-medium transition-all duration-200 {{ $activeTaskTab === 'overdue' ? 'bg-error text-error-content shadow-sm' : 'text-base-content/60 hover:text-error hover:bg-error/10' }}">
                <span class="icon-[tabler--alert-triangle] size-5"></span>
                <span>Overdue</span>
                <span class="badge badge-sm {{ $activeTaskTab === 'overdue' ? 'bg-error-content/20 text-error-content border-0' : 'badge-error' }}">{{ $stats['overdue'] }}</span>
            </a>
            <a href="{{ route('workspace.show', ['workspace' => $workspace, 'tab' => 'tasks', 'task_filter' => 'closed']) }}"
               class="flex items-center gap-2 px-5 py-2.5 rounded-lg font-medium transition-all duration-200 {{ $activeTaskTab === 'closed' ? 'bg-success text-success-content shadow-sm' : 'text-base-content/60 hover:text-success hover:bg-success/10' }}">
                <span class="icon-[tabler--circle-check] size-5"></span>
                <span>Closed</span>
                <span class="badge badge-sm {{ $activeTaskTab === 'closed' ? 'bg-success-content/20 text-success-content border-0' : 'badge-success' }}">{{ $stats['closed'] }}</span>
            </a>
        </div>

        <!-- Filters -->
        <form id="task-filter-form" action="{{ route('workspace.show', $workspace) }}" method="GET" class="flex flex-wrap items-center gap-2">
            <input type="hidden" name="tab" value="tasks" />
            <input type="hidden" name="task_filter" value="{{ $activeTaskTab }}" />
            @include('task::partials.task-filters', [
                'showWorkspaceFilter' => false,
                'showTaskFilter' => false,
                'filters' => $currentFilters,
                'statuses' => $filterStatuses,
                'users' => $workspaceUsers,
                'formAction' => route('workspace.show', $workspace) . '?tab=tasks',
                'formId' => 'task-filter-form',
            ])
        </form>
    </div>

    @if($tasks->count() > 0)
        <!-- Tasks List -->
        <div class="card bg-base-100 shadow">
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th class="w-64 max-w-64">{{ $taskLabel }}</th>
                            <th>Status</th>
                            <th>Priority</th>
                            <th>Progress</th>
                            <th>Assignee</th>
                            <th>Due Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tasks as $task)
                        <tr class="hover">
                            <td class="cursor-pointer max-w-64" onclick="window.location='{{ route('tasks.show', $task) }}'">
                                <div class="flex items-center gap-3">
                                    <div class="flex-shrink-0 flex items-center justify-center w-10 h-10 rounded-lg bg-base-200">
                                        @if($task->types && count($task->types) > 0)
                                            @php $firstType = $task->types[0]; @endphp
                                            <span class="icon-[{{ $firstType->icon() }}] size-5 text-base-content/70"></span>
                                        @else
                                            <span class="icon-[tabler--checkbox] size-5 text-base-content/70"></span>
                                        @endif
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="font-medium truncate {{ $task->isClosed() ? 'line-through text-base-content/60' : '' }}" title="{{ $task->title }}">
                                            {{ $task->title }}
                                            @if($task->isClosed())
                                                <span class="badge badge-neutral badge-xs ml-1">Closed</span>
                                            @endif
                                        </div>
                                        <div class="text-xs text-base-content/50">{{ $task->task_number }}</div>
                                    </div>
                                </div>
                            </td>
                            <!-- Status - Inline Editable -->
                            <td>
                                @if($task->isClosed())
                                    <span class="badge badge-neutral border-0">Closed</span>
                                @elseif($task->canChangeStatus(auth()->user()))
                                    <div class="dropdown dropdown-end">
                                        <label tabindex="0" class="cursor-pointer">
                                            @if($task->status)
                                                <span class="badge border-0 hover:opacity-80 transition-opacity" style="background-color: {{ $task->status->background_color }}20; color: {{ $task->status->background_color }};">
                                                    {{ $task->status->name }}
                                                </span>
                                            @else
                                                <span class="text-base-content/40 hover:text-base-content/60 transition-colors">Click to set</span>
                                            @endif
                                        </label>
                                        <div tabindex="0" class="dropdown-menu dropdown-open:opacity-100 hidden min-w-48 z-50 p-2">
                                            <div class="px-2 pb-2">
                                                <input type="text" placeholder="Search status..." class="input input-bordered input-sm w-full ws-status-search" data-task="{{ $task->id }}">
                                            </div>
                                            <div class="ws-status-list max-h-48 overflow-y-auto" data-task="{{ $task->id }}">
                                                @php
                                                    $currentStatus = $task->status;
                                                    if ($currentStatus && $currentStatus->allowed_transitions !== null) {
                                                        $allowedIds = $currentStatus->allowed_transitions;
                                                        $filteredStatuses = $filterStatuses->filter(function ($status) use ($allowedIds, $currentStatus) {
                                                            return $status->id === $currentStatus->id || in_array($status->id, $allowedIds);
                                                        });
                                                    } else {
                                                        $filteredStatuses = $filterStatuses;
                                                    }
                                                    $availableTransitions = $filteredStatuses->filter(fn($s) => $s->id !== $task->status_id);
                                                @endphp
                                                @if($availableTransitions->isEmpty() && $currentStatus)
                                                    <div class="px-3 py-2 text-sm text-base-content/60 italic">
                                                        <span class="icon-[tabler--lock] size-4 inline-block align-middle mr-1"></span>
                                                        No transitions available
                                                    </div>
                                                @else
                                                    @foreach($filteredStatuses as $status)
                                                        <form action="{{ route('tasks.update-status', $task) }}" method="POST" class="ws-status-item" data-name="{{ strtolower($status->name) }}">
                                                            @csrf
                                                            @method('PATCH')
                                                            <input type="hidden" name="status_id" value="{{ $status->id }}">
                                                            <button type="submit" class="dropdown-item w-full text-left flex items-center gap-2 {{ $task->status_id == $status->id ? 'bg-primary/10' : '' }}">
                                                                <span class="w-3 h-3 rounded-full" style="background-color: {{ $status->background_color }}"></span>
                                                                {{ $status->name }}
                                                                @if($task->status_id == $status->id)
                                                                    <span class="icon-[tabler--check] size-4 text-primary ml-auto"></span>
                                                                @endif
                                                            </button>
                                                        </form>
                                                    @endforeach
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @elseif($task->status)
                                    <span class="badge border-0" style="background-color: {{ $task->status->background_color }}20; color: {{ $task->status->background_color }};">
                                        {{ $task->status->name }}
                                    </span>
                                @else
                                    <span class="text-base-content/40">-</span>
                                @endif
                            </td>
                            <!-- Priority - Inline Editable -->
                            <td>
                                @if($task->isClosed())
                                    @if($task->priority)
                                        <div class="flex items-center gap-1">
                                            <span class="icon-[{{ $task->priority->icon() }}] size-4" style="color: {{ $task->priority->color() }}"></span>
                                            <span style="color: {{ $task->priority->color() }}">{{ $task->priority->label() }}</span>
                                        </div>
                                    @else
                                        <span class="text-base-content/40">-</span>
                                    @endif
                                @elseif($task->canEdit(auth()->user()))
                                    <div class="dropdown dropdown-end">
                                        <label tabindex="0" class="cursor-pointer">
                                            @if($task->priority)
                                                <div class="flex items-center gap-1 hover:opacity-80 transition-opacity">
                                                    <span class="icon-[{{ $task->priority->icon() }}] size-4" style="color: {{ $task->priority->color() }}"></span>
                                                    <span style="color: {{ $task->priority->color() }}">{{ $task->priority->label() }}</span>
                                                </div>
                                            @else
                                                <span class="text-base-content/40 hover:text-base-content/60 transition-colors">Click to set</span>
                                            @endif
                                        </label>
                                        <div tabindex="0" class="dropdown-menu dropdown-open:opacity-100 hidden min-w-44 z-50 p-2">
                                            <div class="px-2 pb-2">
                                                <input type="text" placeholder="Search priority..." class="input input-bordered input-sm w-full ws-priority-search" data-task="{{ $task->id }}">
                                            </div>
                                            <div class="ws-priority-list max-h-48 overflow-y-auto" data-task="{{ $task->id }}">
                                                @foreach(\App\Modules\Task\Enums\TaskPriority::cases() as $priority)
                                                    <form action="{{ route('tasks.update-priority', $task) }}" method="POST" class="ws-priority-item" data-name="{{ strtolower($priority->label()) }}">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="priority" value="{{ $priority->value }}">
                                                        <button type="submit" class="dropdown-item w-full text-left flex items-center gap-2 {{ $task->priority == $priority ? 'bg-primary/10' : '' }}">
                                                            <span class="icon-[{{ $priority->icon() }}] size-4" style="color: {{ $priority->color() }}"></span>
                                                            <span style="color: {{ $priority->color() }}">{{ $priority->label() }}</span>
                                                            @if($task->priority == $priority)
                                                                <span class="icon-[tabler--check] size-4 text-primary ml-auto"></span>
                                                            @endif
                                                        </button>
                                                    </form>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @elseif($task->priority)
                                    <div class="flex items-center gap-1">
                                        <span class="icon-[{{ $task->priority->icon() }}] size-4" style="color: {{ $task->priority->color() }}"></span>
                                        <span style="color: {{ $task->priority->color() }}">{{ $task->priority->label() }}</span>
                                    </div>
                                @else
                                    <span class="text-base-content/40">-</span>
                                @endif
                            </td>
                            <!-- Progress -->
                            <td>
                                <div class="flex items-center gap-2 min-w-[80px]">
                                    <div class="flex-1 bg-base-200 rounded-full h-1.5">
                                        <div class="bg-primary h-1.5 rounded-full" style="width: {{ $task->progress ?? 0 }}%"></div>
                                    </div>
                                    <span class="text-xs text-base-content/60">{{ $task->progress ?? 0 }}%</span>
                                </div>
                            </td>
                            <!-- Assignee - Inline Editable -->
                            <td>
                                @if($task->isClosed())
                                    @if($task->assignee)
                                        <div class="flex items-center gap-2">
                                            <div class="avatar">
                                                <div class="w-8 h-8 rounded-full">
                                                    <img src="{{ $task->assignee->avatar_url }}" alt="{{ $task->assignee->name }}" />
                                                </div>
                                            </div>
                                            <span class="text-sm">{{ $task->assignee->name }}</span>
                                        </div>
                                    @else
                                        <span class="text-base-content/40">Unassigned</span>
                                    @endif
                                @elseif($task->canEdit(auth()->user()))
                                    <div class="dropdown dropdown-end">
                                        <label tabindex="0" class="cursor-pointer">
                                            @if($task->assignee)
                                                <div class="flex items-center gap-2 hover:opacity-80 transition-opacity">
                                                    <div class="avatar">
                                                        <div class="w-8 h-8 rounded-full">
                                                            <img src="{{ $task->assignee->avatar_url }}" alt="{{ $task->assignee->name }}" />
                                                        </div>
                                                    </div>
                                                    <span class="text-sm">{{ $task->assignee->name }}</span>
                                                </div>
                                            @else
                                                <span class="text-base-content/40 hover:text-base-content/60 transition-colors">Click to assign</span>
                                            @endif
                                        </label>
                                        <div tabindex="0" class="dropdown-menu dropdown-open:opacity-100 hidden min-w-56 z-50 p-2">
                                            <div class="px-2 pb-2">
                                                <input type="text" placeholder="Search assignee..." class="input input-bordered input-sm w-full ws-assignee-search" data-task="{{ $task->id }}">
                                            </div>
                                            <div class="ws-assignee-list max-h-48 overflow-y-auto" data-task="{{ $task->id }}">
                                                <form action="{{ route('tasks.update-assignee', $task) }}" method="POST" class="ws-assignee-item" data-name="unassigned">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="assignee_id" value="">
                                                    <button type="submit" class="dropdown-item w-full text-left flex items-center gap-2 {{ !$task->assignee_id ? 'bg-primary/10' : '' }}">
                                                        <div class="avatar placeholder">
                                                            <div class="bg-base-300 text-base-content rounded-full w-8 h-8 flex items-center justify-center">
                                                                <span class="icon-[tabler--user-off] size-4"></span>
                                                            </div>
                                                        </div>
                                                        <span>Unassigned</span>
                                                        @if(!$task->assignee_id)
                                                            <span class="icon-[tabler--check] size-4 text-primary ml-auto"></span>
                                                        @endif
                                                    </button>
                                                </form>
                                                <div class="border-t border-base-200 my-1"></div>
                                                @foreach($workspaceUsers as $user)
                                                    <form action="{{ route('tasks.update-assignee', $task) }}" method="POST" class="ws-assignee-item" data-name="{{ strtolower($user->name) }}">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="assignee_id" value="{{ $user->id }}">
                                                        <button type="submit" class="dropdown-item w-full text-left flex items-center gap-2 {{ $task->assignee_id == $user->id ? 'bg-primary/10' : '' }}">
                                                            <div class="avatar">
                                                                <div class="w-8 h-8 rounded-full">
                                                                    <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" />
                                                                </div>
                                                            </div>
                                                            <span class="text-sm">{{ $user->name }}</span>
                                                            @if($task->assignee_id == $user->id)
                                                                <span class="icon-[tabler--check] size-4 text-primary ml-auto"></span>
                                                            @endif
                                                        </button>
                                                    </form>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @elseif($task->assignee)
                                    <div class="flex items-center gap-2">
                                        <div class="avatar">
                                            <div class="w-8 h-8 rounded-full">
                                                <img src="{{ $task->assignee->avatar_url }}" alt="{{ $task->assignee->name }}" />
                                            </div>
                                        </div>
                                        <span class="text-sm">{{ $task->assignee->name }}</span>
                                    </div>
                                @else
                                    <span class="text-base-content/40">Unassigned</span>
                                @endif
                            </td>
                            <!-- Due Date - Inline Editable -->
                            <td class="text-sm">
                                @if($task->isClosed())
                                    @if($task->due_date)
                                        <span class="flex items-center gap-1 {{ $task->isOverdue() ? 'text-error font-medium' : 'text-base-content/60' }}">
                                            <span class="icon-[tabler--calendar] size-4"></span>
                                            {{ $task->due_date->format('M d, Y') }}
                                        </span>
                                    @else
                                        <span class="text-base-content/40">-</span>
                                    @endif
                                @elseif($task->canEdit(auth()->user()))
                                    <div class="relative inline-block">
                                        <label class="cursor-pointer" onclick="openWsDueDatePicker('{{ $task->id }}', '{{ $task->due_date?->format('Y-m-d') }}', event)">
                                            @if($task->due_date)
                                                <span class="flex items-center gap-1 hover:opacity-80 transition-opacity {{ $task->isOverdue() ? 'text-error font-medium' : 'text-base-content/60' }}">
                                                    <span class="icon-[tabler--calendar] size-4"></span>
                                                    {{ $task->due_date->format('M d, Y') }}
                                                    @if($task->isOverdue())
                                                        <span class="badge badge-error badge-xs">Overdue</span>
                                                    @endif
                                                </span>
                                            @else
                                                <span class="text-base-content/40 hover:text-base-content/60 transition-colors">Click to set</span>
                                            @endif
                                        </label>
                                        <div id="ws-due-date-picker-{{ $task->id }}" class="hidden fixed z-[9999] p-4 bg-base-100 border border-base-300 rounded-xl shadow-2xl w-80">
                                            <form action="{{ route('tasks.update-due-date', $task) }}" method="POST" id="ws-due-date-form-{{ $task->id }}">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="due_date" id="ws-due-date-value-{{ $task->id }}" value="{{ $task->due_date?->format('Y-m-d H:i') }}">
                                                <div class="flex gap-2 mb-4">
                                                    <select id="ws-due-date-year-{{ $task->id }}" class="select select-bordered select-sm flex-1" onchange="renderWsCalendar('{{ $task->id }}')"></select>
                                                    <select id="ws-due-date-month-{{ $task->id }}" class="select select-bordered select-sm flex-1" onchange="renderWsCalendar('{{ $task->id }}')">
                                                        <option value="0">January</option>
                                                        <option value="1">February</option>
                                                        <option value="2">March</option>
                                                        <option value="3">April</option>
                                                        <option value="4">May</option>
                                                        <option value="5">June</option>
                                                        <option value="6">July</option>
                                                        <option value="7">August</option>
                                                        <option value="8">September</option>
                                                        <option value="9">October</option>
                                                        <option value="10">November</option>
                                                        <option value="11">December</option>
                                                    </select>
                                                </div>
                                                <div class="mb-4">
                                                    <div class="grid grid-cols-7 gap-1 mb-2">
                                                        <div class="text-center text-xs font-semibold text-base-content/50 py-1">Sun</div>
                                                        <div class="text-center text-xs font-semibold text-base-content/50 py-1">Mon</div>
                                                        <div class="text-center text-xs font-semibold text-base-content/50 py-1">Tue</div>
                                                        <div class="text-center text-xs font-semibold text-base-content/50 py-1">Wed</div>
                                                        <div class="text-center text-xs font-semibold text-base-content/50 py-1">Thu</div>
                                                        <div class="text-center text-xs font-semibold text-base-content/50 py-1">Fri</div>
                                                        <div class="text-center text-xs font-semibold text-base-content/50 py-1">Sat</div>
                                                    </div>
                                                    <div id="ws-due-date-days-{{ $task->id }}" class="grid grid-cols-7 gap-1"></div>
                                                </div>
                                                <div class="flex items-center gap-2 pt-3 border-t border-base-200">
                                                    <span class="icon-[tabler--clock] size-5 text-base-content/50"></span>
                                                    <input type="number" id="ws-due-date-hour-{{ $task->id }}" min="1" max="12" value="{{ $task->due_date ? $task->due_date->format('g') : '12' }}" class="input input-bordered input-sm w-16 text-center">
                                                    <span class="text-lg font-bold">:</span>
                                                    <input type="number" id="ws-due-date-minute-{{ $task->id }}" min="0" max="59" value="{{ $task->due_date ? $task->due_date->format('i') : '00' }}" class="input input-bordered input-sm w-16 text-center">
                                                    <select id="ws-due-date-ampm-{{ $task->id }}" class="select select-bordered select-sm">
                                                        <option value="AM" {{ $task->due_date && $task->due_date->format('A') == 'AM' ? 'selected' : '' }}>AM</option>
                                                        <option value="PM" {{ $task->due_date && $task->due_date->format('A') == 'PM' ? 'selected' : '' }}>PM</option>
                                                    </select>
                                                </div>
                                                <div class="flex justify-between items-center mt-4 pt-3 border-t border-base-200">
                                                    <button type="button" onclick="clearWsDueDate('{{ $task->id }}')" class="btn btn-ghost btn-sm text-error">
                                                        <span class="icon-[tabler--x] size-4"></span>
                                                        Clear
                                                    </button>
                                                    <div class="flex gap-2">
                                                        <button type="button" onclick="closeWsDueDatePicker('{{ $task->id }}')" class="btn btn-ghost btn-sm">Cancel</button>
                                                        <button type="button" onclick="applyWsDueDate('{{ $task->id }}')" class="btn btn-primary btn-sm">Apply</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                @elseif($task->due_date)
                                    <span class="flex items-center gap-1 {{ $task->isOverdue() ? 'text-error font-medium' : 'text-base-content/60' }}">
                                        <span class="icon-[tabler--calendar] size-4"></span>
                                        {{ $task->due_date->format('M d, Y') }}
                                    </span>
                                @else
                                    <span class="text-base-content/40">-</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        @if($tasks->hasPages())
            <div class="flex justify-center mt-4">
                {{ $tasks->links() }}
            </div>
        @endif
    @else
        <!-- Empty State -->
        <div class="card bg-base-100 shadow">
            <div class="card-body items-center text-center py-12">
                @php
                    $hasFilters = !empty(array_filter($currentFilters ?? []));
                @endphp
                @if($hasFilters)
                    <span class="icon-[tabler--filter-off] size-16 text-base-content/20 mb-4"></span>
                    <h3 class="text-lg font-semibold">No {{ $tasksLabelLower }} found</h3>
                    <p class="text-base-content/60 mb-4">Try adjusting your filters to see more results.</p>
                    <a href="{{ route('workspace.show', ['workspace' => $workspace, 'tab' => 'tasks']) }}" class="btn btn-ghost">
                        <span class="icon-[tabler--x] size-4"></span>
                        Clear Filters
                    </a>
                @else
                    <span class="icon-[{{ $isInbox ? 'tabler--ticket' : 'tabler--list-check' }}] size-16 text-base-content/20 mb-4"></span>
                    <h3 class="text-lg font-semibold">No {{ $tasksLabelLower }} yet</h3>
                    <p class="text-base-content/60 mb-4">Create your first {{ $taskLabelLower }} to start tracking work in this workspace.</p>
                    <a href="{{ route('tasks.create', ['workspace' => $workspace->uuid]) }}" class="btn btn-primary">
                        <span class="icon-[tabler--plus] size-4"></span>
                        Create First {{ $taskLabel }}
                    </a>
                @endif
            </div>
        </div>
    @endif
</div>

<style>
.ws-calendar-day {
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.15s ease;
    cursor: pointer;
    border: 2px solid transparent;
    background: transparent;
    color: #374151;
}
.ws-calendar-day:hover:not(:disabled):not(.selected):not(.today) {
    background: hsl(var(--p) / 0.1);
}
.ws-calendar-day.selected {
    background: hsl(var(--su));
    color: hsl(var(--suc));
    border-color: hsl(var(--su));
    box-shadow: 0 4px 12px hsl(var(--su) / 0.4);
    font-weight: 700;
}
.ws-calendar-day.today {
    border: 3px solid #f59e0b;
    background: #f59e0b;
    color: #fff;
    font-weight: 800;
    font-size: 15px;
    box-shadow: 0 2px 8px rgba(245, 158, 11, 0.4);
}
.ws-calendar-day.today:hover:not(.selected) {
    background: #d97706;
}
.ws-calendar-day.today.selected {
    background: hsl(var(--su));
    color: hsl(var(--suc));
    border-color: hsl(var(--su));
}
.ws-calendar-day:disabled {
    color: #d1d5db;
    cursor: not-allowed;
}
.ws-calendar-day.other-month {
    color: #9ca3af;
}
</style>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Search functionality for Status dropdowns
    document.querySelectorAll('.ws-status-search').forEach(input => {
        input.addEventListener('input', function() {
            const search = this.value.toLowerCase();
            const taskId = this.dataset.task;
            const list = document.querySelector(`.ws-status-list[data-task="${taskId}"]`);
            if (list) {
                list.querySelectorAll('.ws-status-item').forEach(item => {
                    const name = item.dataset.name;
                    item.style.display = name.includes(search) ? '' : 'none';
                });
            }
        });
        input.addEventListener('click', e => e.stopPropagation());
    });

    // Search functionality for Priority dropdowns
    document.querySelectorAll('.ws-priority-search').forEach(input => {
        input.addEventListener('input', function() {
            const search = this.value.toLowerCase();
            const taskId = this.dataset.task;
            const list = document.querySelector(`.ws-priority-list[data-task="${taskId}"]`);
            if (list) {
                list.querySelectorAll('.ws-priority-item').forEach(item => {
                    const name = item.dataset.name;
                    item.style.display = name.includes(search) ? '' : 'none';
                });
            }
        });
        input.addEventListener('click', e => e.stopPropagation());
    });

    // Search functionality for Assignee dropdowns
    document.querySelectorAll('.ws-assignee-search').forEach(input => {
        input.addEventListener('input', function() {
            const search = this.value.toLowerCase();
            const taskId = this.dataset.task;
            const list = document.querySelector(`.ws-assignee-list[data-task="${taskId}"]`);
            if (list) {
                list.querySelectorAll('.ws-assignee-item').forEach(item => {
                    const name = item.dataset.name;
                    item.style.display = name.includes(search) ? '' : 'none';
                });
            }
        });
        input.addEventListener('click', e => e.stopPropagation());
    });

    // Close date pickers when clicking outside
    document.addEventListener('click', function(e) {
        document.querySelectorAll('[id^="ws-due-date-picker-"]').forEach(picker => {
            if (!picker.contains(e.target) && !e.target.closest('label[onclick^="openWsDueDatePicker"]')) {
                picker.classList.add('hidden');
            }
        });
    });
});

// Calendar state per task
const wsCalendarState = {};

function openWsDueDatePicker(taskId, currentDate, event) {
    document.querySelectorAll('[id^="ws-due-date-picker-"]').forEach(picker => {
        picker.classList.add('hidden');
    });

    const picker = document.getElementById('ws-due-date-picker-' + taskId);
    if (!picker) return;

    const today = new Date();
    let selectedDate = currentDate ? new Date(currentDate + 'T00:00:00') : null;

    wsCalendarState[taskId] = {
        selectedDate: selectedDate,
        viewYear: selectedDate ? selectedDate.getFullYear() : today.getFullYear(),
        viewMonth: selectedDate ? selectedDate.getMonth() : today.getMonth()
    };

    const yearSelect = document.getElementById('ws-due-date-year-' + taskId);
    if (yearSelect) {
        yearSelect.innerHTML = '';
        const currentYear = today.getFullYear();
        for (let y = currentYear; y <= currentYear + 10; y++) {
            const option = document.createElement('option');
            option.value = y;
            option.textContent = y;
            yearSelect.appendChild(option);
        }
        yearSelect.value = wsCalendarState[taskId].viewYear;
    }

    const monthSelect = document.getElementById('ws-due-date-month-' + taskId);
    if (monthSelect) {
        monthSelect.value = wsCalendarState[taskId].viewMonth;
    }

    renderWsCalendar(taskId);

    const label = event ? event.currentTarget : document.querySelector(`label[onclick*="openWsDueDatePicker('${taskId}'"]`);
    if (label) {
        const rect = label.getBoundingClientRect();
        const pickerHeight = 420;
        const viewportHeight = window.innerHeight;

        if (rect.bottom + pickerHeight > viewportHeight) {
            picker.style.top = (rect.top - pickerHeight - 8) + 'px';
        } else {
            picker.style.top = (rect.bottom + 8) + 'px';
        }

        const pickerWidth = 320;
        let leftPos = rect.right - pickerWidth;
        if (leftPos < 10) leftPos = 10;
        picker.style.left = leftPos + 'px';
    }

    picker.classList.remove('hidden');
}

function closeWsDueDatePicker(taskId) {
    const picker = document.getElementById('ws-due-date-picker-' + taskId);
    if (picker) picker.classList.add('hidden');
}

function renderWsCalendar(taskId) {
    const state = wsCalendarState[taskId];
    if (!state) return;

    const yearSelect = document.getElementById('ws-due-date-year-' + taskId);
    const monthSelect = document.getElementById('ws-due-date-month-' + taskId);
    const daysContainer = document.getElementById('ws-due-date-days-' + taskId);

    if (!daysContainer) return;

    state.viewYear = parseInt(yearSelect?.value) || state.viewYear;
    state.viewMonth = parseInt(monthSelect?.value) || state.viewMonth;

    const today = new Date();
    today.setHours(0, 0, 0, 0);

    const year = state.viewYear;
    const month = state.viewMonth;

    const firstDay = new Date(year, month, 1);
    const startDayOfWeek = firstDay.getDay();
    const lastDay = new Date(year, month + 1, 0);
    const daysInMonth = lastDay.getDate();
    const prevMonthLastDay = new Date(year, month, 0).getDate();

    daysContainer.innerHTML = '';

    for (let i = startDayOfWeek - 1; i >= 0; i--) {
        const day = prevMonthLastDay - i;
        const btn = createWsCalendarDay(day, true, false, false, false);
        daysContainer.appendChild(btn);
    }

    for (let day = 1; day <= daysInMonth; day++) {
        const date = new Date(year, month, day);
        const isToday = date.getTime() === today.getTime();
        const isPast = date < today;
        const isSelected = state.selectedDate &&
            state.selectedDate.getFullYear() === year &&
            state.selectedDate.getMonth() === month &&
            state.selectedDate.getDate() === day;

        const btn = createWsCalendarDay(day, false, isPast, isSelected, isToday);
        if (!isPast) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                selectWsDay(taskId, day);
            });
        }
        daysContainer.appendChild(btn);
    }

    const totalCells = Math.ceil((startDayOfWeek + daysInMonth) / 7) * 7;
    const nextMonthDays = totalCells - (startDayOfWeek + daysInMonth);
    for (let day = 1; day <= nextMonthDays; day++) {
        const btn = createWsCalendarDay(day, true, false, false, false);
        daysContainer.appendChild(btn);
    }
}

function createWsCalendarDay(day, isOtherMonth, isPast, isSelected, isToday) {
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.textContent = day;
    btn.className = 'ws-calendar-day';

    if (isOtherMonth) {
        btn.classList.add('other-month');
        btn.disabled = true;
    }
    if (isPast && !isOtherMonth) {
        btn.disabled = true;
    }
    if (isToday) {
        btn.classList.add('today');
    }
    if (isSelected) {
        btn.classList.add('selected');
    }

    return btn;
}

function selectWsDay(taskId, day) {
    const state = wsCalendarState[taskId];
    if (!state) return;

    state.selectedDate = new Date(state.viewYear, state.viewMonth, day);
    renderWsCalendar(taskId);
}

function applyWsDueDate(taskId) {
    const state = wsCalendarState[taskId];
    if (!state || !state.selectedDate) {
        alert('Please select a date first');
        return;
    }

    const hourInput = document.getElementById('ws-due-date-hour-' + taskId);
    const minuteInput = document.getElementById('ws-due-date-minute-' + taskId);
    const ampmSelect = document.getElementById('ws-due-date-ampm-' + taskId);
    const hiddenInput = document.getElementById('ws-due-date-value-' + taskId);

    let hour = parseInt(hourInput?.value) || 12;
    const minute = String(minuteInput?.value || 0).padStart(2, '0');
    const ampm = ampmSelect?.value || 'PM';

    if (ampm === 'PM' && hour !== 12) {
        hour += 12;
    } else if (ampm === 'AM' && hour === 12) {
        hour = 0;
    }

    const y = state.selectedDate.getFullYear();
    const m = String(state.selectedDate.getMonth() + 1).padStart(2, '0');
    const d = String(state.selectedDate.getDate()).padStart(2, '0');
    const h = String(hour).padStart(2, '0');

    if (hiddenInput) {
        hiddenInput.value = `${y}-${m}-${d} ${h}:${minute}`;
    }

    const form = document.getElementById('ws-due-date-form-' + taskId);
    if (form) {
        form.submit();
    }
}

function clearWsDueDate(taskId) {
    const input = document.getElementById('ws-due-date-value-' + taskId);
    if (input) {
        input.value = '';
    }
    const form = document.getElementById('ws-due-date-form-' + taskId);
    if (form) {
        form.submit();
    }
}
</script>
@endpush
