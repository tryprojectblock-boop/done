<div class="flex flex-col gap-4">
    @foreach($tasks as $task)
    <a href="{{ route('tasks.show', $task) }}" class="card bg-base-100 shadow hover:shadow-lg transition-shadow {{ $task->isClosed() ? 'opacity-70' : '' }}">
        <div class="card-body p-4 gap-0">
            <!-- Header: Type Icon, Task Number, Priority -->
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-2">
                    <div class="flex items-center gap-1.5">
                        @if($task->types && count($task->types) > 0)
                            <span class="icon-[{{ $task->types[0]->icon() }}] size-4 text-base-content/70"></span>
                        @else
                            <span class="icon-[tabler--checkbox] size-4 text-base-content/70"></span>
                        @endif
                    </div>
                   <!-- Title -->
                    <h3 class="font-semibold text-[#17151C] text-base leading-5 {{ $task->isClosed() ? 'line-through text-base-content/60' : '' }}">
                        {{ $task->title }}
                    </h3>
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
            </div>
            <div class="flex items-center">
                <!-- Task Number -->
                <div class="text-sm text-[#525158] flex items-center gap-2 font-sm leading-[18px] font-normal mt-0.5">
                    <span class="font-mono text-xs text-[#525158]">{{ $task->task_number }}</span> 
                    <span class="w-2 h-2 rounded-full bg-[#E0E0E0]"></span> 
                    <span class="text-[#525158]">{{ $task->workspace->name }}</span>
                </div>
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
            <div class="flex items-center justify-between mt-auto pt-4">
                <div class="flex items-center gap-2">
                    @if($task->isClosed())
                        <span class="text-xs leading-4 font-semibold py-1 px-2 rounded-md badge-neutral border-0">
                            Closed
                        </span>
                    @elseif($task->status)
                        <span class="text-xs leading-4 font-semibold py-1 px-2 rounded-md border-0" style="background-color: {{ $task->status->background_color }}20; color: {{ $task->status->background_color }};">
                            {{ $task->status->name }}
                        </span>
                    @endif

                    @if($task->priority)
                        <div class="inline-flex items-center gap-1.5 text-xs leading-4 font-semibold py-1 px-2 rounded-md border-0" style="background-color: {{ $task->priority->color() }}20; color: {{ $task->priority->color() }}">
                            <span class="icon-[tabler--flag-3-filled] color-{{ $task->priority->color() }} size-4"></span>
                            {{ $task->priority->label() }}
                        </div>
                    @endif
                    @if($task->due_date)
                        <span class="flex items-center gap-2 ml-2 text-xs leading-4 font-medium {{ $task->isOverdue() ? 'text-error font-medium' : 'text-base-content/50' }}">
                            <span class="icon-[tabler--calendar] size-3"></span>
                            {{ $task->due_date->format('M d') }}
                        </span>
                    @endif
                </div>
                <div class="flex items-center gap-4">
                    <!-- Assignee -->
                    <div>
                        @if($task->assignee)
                        <div class="avatar">
                            <div title="{{ $task->assignee->name }}" class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-semibold flex-shrink-0">
                                <img src="{{ $task->assignee->avatar_url }}" alt="{{ $task->assignee->name }}" />
                            </div>
                        </div>
                        <span class="text-sm text-[#525158] ml-2 leading-[18px] font-medium">{{ $task->assignee->name }}</span>
                        @endif
                    </div>
                    <!-- Progress bar -->
                    <div>
                        @if($task->progress !== null)
                        <div class="flex items-center gap-3 w-full">
                            <!-- Progress Bar with Tooltip -->
                            <div class="group relative flex-1 w-[120px]">
                                <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                                    @if($task->progress > 0)
                                    <div class="h-full bg-blue-500 rounded-full transition-all duration-300" 
                                        style="width: {{ $task->progress }}%">
                                    </div>
                                    @endif
                                </div>
                                
                                <!-- Tooltip (only if progress > 0) -->
                                @if($task->progress > 0)
                                <span class="absolute -top-8 left-1/2 -translate-x-1/2 px-2 py-1 
                                            bg-gray-900 text-white text-xs rounded 
                                            opacity-0 group-hover:opacity-100 transition-opacity 
                                            pointer-events-none whitespace-nowrap">
                                    {{ $task->progress }}% Complete
                                </span>
                                @endif
                            </div>
                            <!-- Percentage Number -->
                            <span class="text-sm text-[#17151C] font-medium">{{ $task->progress }}%</span>
                        </div>
                        @else
                        <span class="text-sm text-gray-500">-</span>
                        @endif
                    </div>  
                    <!-- View icon button -->
                    <div>
                        <button href="{{ route('tasks.show', $task) }}" class="w-7 h-7 rounded-lg border border-[#B8B7BB] flex items-center justify-center hover:bg-gray-50 transition-colors">
                                <svg class="w-3.5 h-3.5 text-[#525158]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                        </button>
                    </div>  
                </div>
                    <!-- <div class="avatar" title="{{ $task->assignee->name }}">
                        <div class="w-6 h-6 rounded-full">
                            <img src="{{ $task->assignee->avatar_url }}" alt="{{ $task->assignee->name }}" />
                        </div>
                    </div> -->
            </div>
        </div>
    </a>
    @endforeach
</div>