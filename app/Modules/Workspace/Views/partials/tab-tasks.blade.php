@php
    $isInbox = $workspace->type->value === 'inbox';
    $taskLabel = $isInbox ? 'Ticket' : 'Task';
    $tasksLabel = $isInbox ? 'Tickets' : 'Tasks';
    $taskLabelLower = $isInbox ? 'ticket' : 'task';
    $tasksLabelLower = $isInbox ? 'tickets' : 'tasks';
@endphp
<div class="space-y-4">
    <!-- Header with Actions -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold">{{ $tasksLabel }}</h2>
            <p class="text-sm text-base-content/60">{{ $tasks->count() }} {{ $tasks->count() === 1 ? $taskLabelLower : $tasksLabelLower }} in this workspace</p>
        </div>
        <a href="{{ route('tasks.create', ['workspace' => $workspace->uuid]) }}" class="btn btn-primary btn-sm">
            <span class="icon-[tabler--plus] size-4"></span>
            New {{ $taskLabel }}
        </a>
    </div>

    @if($tasks->count() > 0)
        <!-- Task Stats -->
        @php
            $openTasks = $tasks->whereNull('closed_at')->count();
            $closedTasks = $tasks->whereNotNull('closed_at')->count();
            $overdueTasks = $tasks->filter(fn($t) => $t->isOverdue())->count();
        @endphp
        <div class="stats stats-horizontal shadow w-full">
            <div class="stat">
                <div class="stat-figure text-primary">
                    <span class="icon-[tabler--list-check] size-8"></span>
                </div>
                <div class="stat-title">Open</div>
                <div class="stat-value text-primary">{{ $openTasks }}</div>
            </div>
            <div class="stat">
                <div class="stat-figure text-success">
                    <span class="icon-[tabler--check] size-8"></span>
                </div>
                <div class="stat-title">Completed</div>
                <div class="stat-value text-success">{{ $closedTasks }}</div>
            </div>
            <div class="stat">
                <div class="stat-figure text-error">
                    <span class="icon-[tabler--alert-triangle] size-8"></span>
                </div>
                <div class="stat-title">Overdue</div>
                <div class="stat-value text-error">{{ $overdueTasks }}</div>
            </div>
        </div>

        <!-- Tasks List -->
        <div class="card bg-base-100 shadow">
            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                        <tr>
                            <th class="w-16">#</th>
                            <th>{{ $taskLabel }}</th>
                            <th>Status</th>
                            <th>Priority</th>
                            <th>Assignee</th>
                            <th>Due Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tasks as $task)
                            <tr class="hover cursor-pointer" onclick="window.location='{{ route('tasks.show', $task) }}'">
                                <td class="font-mono text-base-content/60">{{ $task->task_number }}</td>
                                <td>
                                    <div class="flex items-center gap-2">
                                        @if($task->isClosed())
                                            <span class="icon-[tabler--check] size-4 text-success"></span>
                                        @elseif($task->isOverdue())
                                            <span class="icon-[tabler--alert-triangle] size-4 text-error"></span>
                                        @endif
                                        <span class="font-medium {{ $task->isClosed() ? 'line-through text-base-content/50' : '' }}">{{ $task->title }}</span>
                                    </div>
                                </td>
                                <td>
                                    @if($task->status)
                                        <span class="badge badge-sm border" style="background-color: {{ $task->status->background_color }}20; color: {{ $task->status->background_color }}; border-color: {{ $task->status->background_color }}40;">
                                            {{ $task->status->name }}
                                        </span>
                                    @else
                                        <span class="badge badge-ghost badge-sm">No Status</span>
                                    @endif
                                </td>
                                <td>
                                    @if($task->priority)
                                        <span class="badge badge-sm border" style="background-color: {{ $task->priority->color() }}20; color: {{ $task->priority->color() }}; border-color: {{ $task->priority->color() }}40;">
                                            <span class="icon-[{{ $task->priority->icon() }}] size-3 mr-1"></span>
                                            {{ $task->priority->label() }}
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($task->assignee)
                                        <div class="flex items-center gap-2">
                                            <div class="avatar">
                                                <div class="w-6 rounded-full">
                                                    <img src="{{ $task->assignee->avatar_url }}" alt="{{ $task->assignee->name }}" />
                                                </div>
                                            </div>
                                            <span class="text-sm">{{ $task->assignee->name }}</span>
                                        </div>
                                    @else
                                        <span class="text-base-content/40">Unassigned</span>
                                    @endif
                                </td>
                                <td>
                                    @if($task->due_date)
                                        <span class="{{ $task->isOverdue() ? 'text-error font-medium' : '' }}">
                                            {{ $task->due_date->format('M d, Y') }}
                                        </span>
                                    @else
                                        <span class="text-base-content/40">No due date</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- View All Link -->
        <div class="text-center">
            <a href="{{ route('tasks.index', ['workspace' => $workspace->uuid]) }}" class="btn btn-ghost btn-sm">
                View all {{ $tasksLabelLower }}
                <span class="icon-[tabler--arrow-right] size-4"></span>
            </a>
        </div>
    @else
        <!-- Empty State -->
        <div class="card bg-base-100 shadow">
            <div class="card-body items-center text-center py-12">
                <span class="icon-[{{ $isInbox ? 'tabler--ticket' : 'tabler--list-check' }}] size-16 text-base-content/20 mb-4"></span>
                <h3 class="text-lg font-semibold">No {{ $tasksLabelLower }} yet</h3>
                <p class="text-base-content/60 mb-4">Create your first {{ $taskLabelLower }} to start tracking work in this workspace.</p>
                <a href="{{ route('tasks.create', ['workspace' => $workspace->uuid]) }}" class="btn btn-primary">
                    <span class="icon-[tabler--plus] size-4"></span>
                    Create First {{ $taskLabel }}
                </a>
            </div>
        </div>
    @endif
</div>
