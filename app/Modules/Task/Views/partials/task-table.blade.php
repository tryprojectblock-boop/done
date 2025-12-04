<div class="card bg-base-100 shadow">
    <div class="overflow-x-auto">
        <table class="table">
            <thead>
                <tr>
                    <th>Task</th>
                    <th>Status</th>
                    <th>Priority</th>
                    <th>Assignee</th>
                    <th>Due Date</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($tasks as $task)
                <tr class="hover cursor-pointer" onclick="window.location='{{ route('tasks.show', $task) }}'">
                    <td>
                        <div class="flex items-center gap-3">
                            <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-base-200">
                                @if($task->types && count($task->types) > 0)
                                    @php $firstType = $task->types[0]; @endphp
                                    <span class="icon-[{{ $firstType->icon() }}] size-5 text-base-content/70"></span>
                                @else
                                    <span class="icon-[tabler--checkbox] size-5 text-base-content/70"></span>
                                @endif
                            </div>
                            <div>
                                <div class="font-medium {{ $task->isClosed() ? 'line-through text-base-content/60' : '' }}">
                                    {{ $task->title }}
                                    @if($task->isClosed())
                                        <span class="badge badge-neutral badge-xs ml-1">Closed</span>
                                    @endif
                                </div>
                                <div class="text-xs text-base-content/50">{{ $task->workspace->name }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        @if($task->isClosed())
                            <span class="badge badge-neutral border-0">
                                Closed
                            </span>
                        @elseif($task->status)
                            <span class="badge border-0" style="background-color: {{ $task->status->background_color }}20; color: {{ $task->status->background_color }};">
                                {{ $task->status->name }}
                            </span>
                        @else
                            <span class="text-base-content/40">-</span>
                        @endif
                    </td>
                    <td>
                        @if($task->priority)
                            <div class="flex items-center gap-1">
                                <span class="icon-[{{ $task->priority->icon() }}] size-4" style="color: {{ $task->priority->color() }}"></span>
                                <span style="color: {{ $task->priority->color() }}">{{ $task->priority->label() }}</span>
                            </div>
                        @else
                            <span class="text-base-content/40">-</span>
                        @endif
                    </td>
                    <td>
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
                    </td>
                    <td class="text-sm">
                        @if($task->due_date)
                            <span class="flex items-center gap-1 {{ $task->isOverdue() ? 'text-error font-medium' : 'text-base-content/60' }}">
                                <span class="icon-[tabler--calendar] size-4"></span>
                                {{ $task->due_date->format('M d, Y') }}
                                @if($task->isOverdue())
                                    <span class="badge badge-error badge-xs">Overdue</span>
                                @endif
                            </span>
                        @else
                            <span class="text-base-content/40">-</span>
                        @endif
                    </td>
                    <td class="text-right">
                        <div class="flex items-center justify-end gap-1" onclick="event.stopPropagation()">
                            <a href="{{ route('tasks.show', $task) }}" class="btn btn-ghost btn-sm btn-circle" title="View Task">
                                <span class="icon-[tabler--eye] size-4"></span>
                            </a>
                            @if($task->canEdit(auth()->user()))
                                <a href="{{ route('tasks.edit', $task) }}" class="btn btn-ghost btn-sm btn-circle" title="Edit Task">
                                    <span class="icon-[tabler--edit] size-4"></span>
                                </a>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
