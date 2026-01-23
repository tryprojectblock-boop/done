<div class="flex flex-col gap-4">
    @foreach($tasks as $task)
    <table class="w-full">
    <thead>
        <tr class="border-b border-gray-200">
            <th class="text-left py-4 px-6 text-sm font-medium text-gray-600">Task</th>
            <th class="text-left py-4 px-6 text-sm font-medium text-gray-600">Status</th>
            <th class="text-left py-4 px-6 text-sm font-medium text-gray-600">Priority</th>
            <th class="text-left py-4 px-6 text-sm font-medium text-gray-600">Progress</th>
            <th class="text-left py-4 px-6 text-sm font-medium text-gray-600">Assignee</th>
            <th class="text-left py-4 px-6 text-sm font-medium text-gray-600">Due Date</th>
            <th class="text-left py-4 px-6 text-sm font-medium text-gray-600">Actions</th>
        </tr>
    </thead>
    <tbody class="divide-y divide-gray-100">
        <!-- Row 1 -->
        <tr class="hover:bg-gray-50 transition-colors">
            <td class="py-4 px-6">
                <div class="flex items-start gap-3">
                    @if($task->types && count($task->types) > 0)
                        <span class="icon-[{{ $task->types[0]->icon() }}] w-5 h-5 text-gray-400 mt-0.5 flex-shrink-0"></span>
                    @else
                        <span class="icon-[tabler--checkbox] w-5 h-5 text-gray-400 mt-0.5 flex-shrink-0"></span>
                    @endif
                    <div>
                        <h3 class="font-semibold text-gray-900 text-sm">App Settings - Rating</h3>
                        <p class="text-xs text-gray-500 mt-0.5"><span class="font-mono text-xs text-base-content/60">{{ $task->task_number }}</span> · <span>{{ $task->workspace->name }}</span></p>
                    </div>
                </div>
            </td>
            <td class="py-4 px-6">
                @if($task->isClosed())
                    <span class="badge badge-sm badge-neutral border-0">
                        Closed
                    </span>
                @elseif($task->status)
                    <span class="badge badge-sm border-0" style="background-color: {{ $task->status->background_color }}20; color: {{ $task->status->background_color }};">
                        {{ $task->status->name }}
                    </span>
                @endif
            </td>
            <td class="py-4 px-6">
                @if($task->priority)
                    <span class="badge badge-sm border-0" style="background-color: {{ $task->priority->color() }}20; color: {{ $task->priority->color() }}">
                        <span class="icon-[tabler--flag-3-filled] color-{{ $task->priority->color() }} size-4"></span>
                        {{ $task->priority->label() }}
                    </span>
                @endif
            </td>
            <td class="py-4 px-6">
                
                <span class="text-sm text-gray-600">{{ $task->progress ?? 0 }}%</span>
               
            </td>
            <td class="py-4 px-6">
                <div class="flex items-center gap-2">
                    
                    @if($task->assignee)
                    <div title="{{ $task->assignee->name }}" class="w-7 h-7 avatar rounded-full bg-green-500 flex items-center justify-center text-white text-xs font-semibold flex-shrink-0">
                        <img  clampsrc="{{ $task->assignee->avatar_url }}" alt="{{ $task->assignee->name }}" />
                    </div>
                    <span class="text-sm text-gray-700">Rohit Philip</span>
                    @endif
                </div>
            </td>
            <td class="py-4 px-6">
                <div class="flex items-center gap-2 text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span class="text-sm">Dec 13, 2025</span>
                </div>
            </td>
            <td class="py-4 px-6">
                <a href="{{ route('tasks.show', $task) }}" class="w-8 h-8 rounded-lg border border-gray-200 flex items-center justify-center hover:bg-gray-50 transition-colors">
                    <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                </a>
            </td>
        </tr>
    </tbody>
</table>
    <a href="{{ route('tasks.show', $task) }}" class="card bg-base-100 shadow hover:shadow-lg transition-shadow {{ $task->isClosed() ? 'opacity-70' : '' }}">
        <div class="card-body p-4">
            <!-- Header: Type Icon, Task Number, Priority -->
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-2">
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
                    
                    @if($task->due_date)
                        <span class="flex items-center gap-1 text-xs {{ $task->isOverdue() ? 'text-error font-medium' : 'text-base-content/50' }}">
                            <span class="icon-[tabler--calendar] size-3"></span>
                            {{ $task->due_date->format('M d') }}
                        </span>
                    @endif
                </div>
                
            </div>
        </div>
    </a>
    @endforeach
</div>
