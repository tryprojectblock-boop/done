{{-- Task Item for Calendar List View --}}
<div class="flex items-center gap-4 p-4 hover:bg-base-200/50 cursor-pointer transition-colors"
     onclick="openTaskDrawer('{{ $task->uuid }}')">
    {{-- Status Indicator --}}
    <div class="flex-shrink-0">
        @if($task->isClosed())
            <span class="icon-[tabler--circle-check-filled] size-5 text-success"></span>
        @elseif($task->isOverdue())
            <span class="icon-[tabler--alert-circle-filled] size-5 text-error"></span>
        @else
            <span class="icon-[tabler--circle] size-5 text-base-content/30"></span>
        @endif
    </div>

    {{-- Task Info --}}
    <div class="flex-1 min-w-0">
        <div class="flex items-center gap-2 mb-1">
            <span class="text-xs text-base-content/50 font-mono">{{ $task->task_number }}</span>
            @if($task->isOverdue() && !$task->isClosed())
                <span class="badge badge-xs badge-error">Overdue</span>
            @endif
        </div>
        <h4 class="font-medium text-base-content truncate">{{ $task->title }}</h4>
        <div class="flex items-center gap-3 mt-1 text-sm text-base-content/60">
            {{-- Workspace --}}
            @if($task->workspace)
                <span class="flex items-center gap-1">
                    <span class="icon-[tabler--briefcase] size-3.5"></span>
                    {{ Str::limit($task->workspace->name, 20) }}
                </span>
            @endif
            {{-- Due Time (if has time component) --}}
            @if($task->due_date && $task->due_date->format('H:i') !== '00:00')
                <span class="flex items-center gap-1">
                    <span class="icon-[tabler--clock] size-3.5"></span>
                    {{ $task->due_date->format('g:i A') }}
                </span>
            @endif
        </div>
    </div>

    {{-- Status Badge --}}
    @if($task->status)
        <div class="flex-shrink-0">
            <span class="badge border text-xs"
                  style="background-color: {{ $task->status->background_color }}20; color: {{ $task->status->background_color }}; border-color: {{ $task->status->background_color }}40;">
                {{ $task->status->name }}
            </span>
        </div>
    @endif

    {{-- Priority Badge --}}
    @if($task->priority)
        <div class="flex-shrink-0">
            @php
                $priorityColors = [
                    'critical' => '#dc2626',
                    'high' => '#f97316',
                    'medium' => '#eab308',
                    'low' => '#22c55e',
                ];
                $priorityColor = $priorityColors[$task->priority->value] ?? '#6b7280';
            @endphp
            <span class="badge border text-xs"
                  style="background-color: {{ $priorityColor }}20; color: {{ $priorityColor }}; border-color: {{ $priorityColor }}40;">
                {{ $task->priority->label() }}
            </span>
        </div>
    @endif

    {{-- Assignee Avatar --}}
    <div class="flex-shrink-0">
        @if($task->assignee)
            <div class="avatar" title="{{ $task->assignee->name }}">
                <div class="w-8 h-8 rounded-full ring ring-base-200 ring-offset-base-100 ring-offset-1">
                    <img src="{{ $task->assignee->avatar_url }}" alt="{{ $task->assignee->name }}" />
                </div>
            </div>
        @else
            <div class="avatar placeholder" title="Unassigned">
                <div class="bg-base-200 text-base-content/50 rounded-full w-8 h-8">
                    <span class="icon-[tabler--user] size-4"></span>
                </div>
            </div>
        @endif
    </div>

    {{-- Chevron --}}
    <div class="flex-shrink-0 text-base-content/30">
        <span class="icon-[tabler--chevron-right] size-5"></span>
    </div>
</div>
