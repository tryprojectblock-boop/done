<div class="flex flex-col gap-4">
    @foreach($tasks as $task)
    <a href="{{ route('tasks.show', $task) }}" class="card bg-base-100 shadow hover:shadow-lg transition-shadow {{ $task->isClosed() ? 'opacity-70' : '' }}">
        <div class="card-body p-4">
            <!-- Header: Type Icon, Task Number, Priority -->
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-2">
                    <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-base-200">
                        @if($task->types && count($task->types) > 0)
                            <span class="icon-[{{ $task->types[0]->icon() }}] size-4 text-base-content/70"></span>
                        @else
                            <span class="icon-[tabler--checkbox] size-4 text-base-content/70"></span>
                        @endif
                    </div>
                    <span class="font-mono text-xs text-base-content/60">{{ $task->task_number }}</span>
                    @if($task->types && count($task->types) > 1)
                        <span class="badge badge-ghost badge-xs">+{{ count($task->types) - 1 }}</span>
                    @endif
                    @if($task->isClosed())
                        <span class="badge badge-neutral badge-xs">Closed</span>
                    @endif
                    @if($task->is_private)
                        <span class="badge badge-warning badge-xs gap-0.5" title="Private task">
                            <span class="icon-[tabler--lock] size-3"></span>
                        </span>
                    @endif
                </div>
                @if($task->priority)
                    <div class="flex items-center gap-1 text-xs" style="color: {{ $task->priority->color() }}">
                        <span class="icon-[{{ $task->priority->icon() }}] size-4"></span>
                        {{ $task->priority->label() }}
                    </div>
                @endif
            </div>

            <!-- Title -->
            <h3 class="font-medium text-base-content line-clamp-2 {{ $task->isClosed() ? 'line-through text-base-content/60' : '' }}">
                {{ $task->title }}
            </h3>

            <!-- Workspace -->
            <div class="text-xs text-base-content/50 mt-1">
                {{ $task->workspace->name }}
            </div>

            <!-- Tags -->
            @if($task->tags->isNotEmpty())
                <div class="flex flex-wrap gap-1 mt-2">
                    @foreach($task->tags->take(3) as $tag)
                        <span class="badge badge-sm" style="background-color: {{ $tag->color }}20; color: {{ $tag->color }}">
                            {{ $tag->name }}
                        </span>
                    @endforeach
                    @if($task->tags->count() > 3)
                        <span class="badge badge-sm badge-ghost">+{{ $task->tags->count() - 3 }}</span>
                    @endif
                </div>
            @endif

            <!-- Footer: Status, Due Date, Assignee -->
            <div class="flex items-center justify-between mt-auto pt-3 border-t border-base-200">
                <div class="flex items-center gap-2">
                    @if($task->isClosed())
                        <span class="badge badge-sm badge-neutral border-0">
                            Closed
                        </span>
                    @elseif($task->status)
                        <span class="badge badge-sm border-0" style="background-color: {{ $task->status->background_color }}20; color: {{ $task->status->background_color }};">
                            {{ $task->status->name }}
                        </span>
                    @endif
                    @if($task->due_date)
                        <span class="flex items-center gap-1 text-xs {{ $task->isOverdue() ? 'text-error font-medium' : 'text-base-content/50' }}">
                            <span class="icon-[tabler--calendar] size-3"></span>
                            {{ $task->due_date->format('M d') }}
                        </span>
                    @endif
                </div>
                @if($task->assignee)
                    <div class="avatar" title="{{ $task->assignee->name }}">
                        <div class="w-6 h-6 rounded-full">
                            <img src="{{ $task->assignee->avatar_url }}" alt="{{ $task->assignee->name }}" />
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </a>
    @endforeach
</div>