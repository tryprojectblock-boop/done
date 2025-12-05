@if($tasksByDate->isEmpty())
    <div class="card bg-base-100 shadow">
        <div class="card-body text-center py-12">
            <div class="text-base-content/50">
                <span class="icon-[tabler--calendar-off] size-12 block mx-auto mb-4"></span>
                <p class="text-lg font-medium">No tasks scheduled</p>
                <p class="text-sm">Tasks with due dates will appear here</p>
            </div>
            <div class="mt-4">
                <a href="{{ route('tasks.create') }}" class="btn btn-primary">
                    <span class="icon-[tabler--plus] size-4"></span>
                    Create Task
                </a>
            </div>
        </div>
    </div>
@else
    <div class="space-y-6">
        @foreach($tasksByDate as $date => $dateTasks)
            @php
                $dateObj = \Carbon\Carbon::parse($date);
                $isToday = $dateObj->isToday();
                $isPast = $dateObj->isPast() && !$isToday;
                $isTomorrow = $dateObj->isTomorrow();
            @endphp

            <!-- Date Card -->
            <div class="card bg-base-100 shadow">
                <div class="card-body p-0">
                    <!-- Date Header -->
                    <div class="flex items-center gap-4 p-4 border-b border-base-200 {{ $isToday ? 'bg-primary/5' : '' }}">
                        <div class="flex flex-col items-center justify-center w-16 h-16 rounded-lg {{ $isToday ? 'bg-primary text-primary-content' : ($isPast ? 'bg-base-200' : 'bg-base-200') }}">
                            <span class="text-xs uppercase font-medium {{ $isToday ? '' : 'text-base-content/60' }}">{{ $dateObj->format('M') }}</span>
                            <span class="text-2xl font-bold">{{ $dateObj->format('d') }}</span>
                            <span class="text-xs {{ $isToday ? '' : 'text-base-content/60' }}">{{ $dateObj->format('D') }}</span>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-lg">
                                @if($isToday)
                                    Today
                                @elseif($isTomorrow)
                                    Tomorrow
                                @else
                                    {{ $dateObj->format('l') }}
                                @endif
                            </h3>
                            <p class="text-sm text-base-content/60">
                                {{ $dateObj->format('F j, Y') }}
                                <span class="mx-2">-</span>
                                <span class="badge badge-sm {{ $isToday ? 'badge-primary' : 'badge-ghost' }}">
                                    {{ $dateTasks->count() }} {{ Str::plural('task', $dateTasks->count()) }}
                                </span>
                                @if($isPast && $dateTasks->where('closed_at', null)->count() > 0)
                                    <span class="badge badge-sm badge-error ml-1">
                                        {{ $dateTasks->where('closed_at', null)->count() }} overdue
                                    </span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <!-- Tasks List -->
                    <div class="divide-y divide-base-200">
                        @foreach($dateTasks as $task)
                            <div class="p-4 hover:bg-base-50 transition-colors cursor-pointer task-item"
                                 data-task-uuid="{{ $task->uuid }}"
                                 onclick="openTaskDrawer('{{ $task->uuid }}')">
                                <div class="flex items-start gap-4">
                                    <!-- Status Indicator -->
                                    <div class="flex-shrink-0 mt-1">
                                        @if($task->isClosed())
                                            <span class="icon-[tabler--circle-check-filled] size-5 text-success"></span>
                                        @elseif($task->isOverdue())
                                            <span class="icon-[tabler--alert-circle-filled] size-5 text-error"></span>
                                        @else
                                            <span class="icon-[tabler--circle] size-5 text-base-content/30"></span>
                                        @endif
                                    </div>

                                    <!-- Task Content -->
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="text-xs text-base-content/50 font-mono">{{ $task->task_number }}</span>
                                            @if($task->status)
                                                <span class="badge badge-xs" style="background-color: {{ $task->status->color }}20; color: {{ $task->status->color }}">
                                                    {{ $task->status->name }}
                                                </span>
                                            @endif
                                            @if($task->priority)
                                                <span class="badge badge-xs" style="color: {{ $task->priority->color() }}">
                                                    <span class="icon-[{{ $task->priority->icon() }}] size-3 mr-1"></span>
                                                    {{ $task->priority->label() }}
                                                </span>
                                            @endif
                                        </div>

                                        <h4 class="font-medium {{ $task->isClosed() ? 'line-through text-base-content/50' : '' }}">
                                            {{ $task->title }}
                                        </h4>

                                        @if($task->description)
                                            <p class="text-sm text-base-content/60 line-clamp-2 mt-1">
                                                {{ Str::limit(strip_tags($task->description), 100) }}
                                            </p>
                                        @endif

                                        <!-- Task Meta -->
                                        <div class="flex items-center flex-wrap gap-3 mt-2 text-xs text-base-content/50">
                                            @if($task->workspace)
                                                <span class="flex items-center gap-1">
                                                    <span class="icon-[tabler--briefcase] size-3.5"></span>
                                                    {{ $task->workspace->name }}
                                                </span>
                                            @endif

                                            @if($task->tags->isNotEmpty())
                                                <div class="flex items-center gap-1">
                                                    @foreach($task->tags->take(2) as $tag)
                                                        <span class="badge badge-xs" style="background-color: {{ $tag->color }}20; color: {{ $tag->color }}">
                                                            {{ $tag->name }}
                                                        </span>
                                                    @endforeach
                                                    @if($task->tags->count() > 2)
                                                        <span class="text-base-content/40">+{{ $task->tags->count() - 2 }}</span>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Assignee -->
                                    <div class="flex-shrink-0">
                                        @if($task->assignee)
                                            <div class="tooltip" data-tip="{{ $task->assignee->name }}">
                                                <div class="avatar">
                                                    <div class="w-8 h-8 rounded-full">
                                                        <img src="{{ $task->assignee->avatar_url }}" alt="{{ $task->assignee->name }}" />
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <div class="tooltip" data-tip="Unassigned">
                                                <div class="avatar placeholder">
                                                    <div class="bg-base-200 text-base-content/50 rounded-full w-8 h-8">
                                                        <span class="icon-[tabler--user] size-4"></span>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif
