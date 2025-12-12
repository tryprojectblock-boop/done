<a href="{{ route('milestones.show', [$workspace->uuid, $milestone->uuid]) }}" class="card bg-base-100 shadow hover:shadow-lg transition-shadow block">
    <div class="card-body p-4">
        <div class="flex items-center gap-6">
            <!-- Color indicator -->
            @if($milestone->color)
                <div class="w-2 h-16 rounded-full flex-shrink-0" style="background-color: {{ $milestone->color }}"></div>
            @else
                <div class="w-2 h-16 rounded-full flex-shrink-0 bg-primary"></div>
            @endif

            <!-- Main Content -->
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-3 mb-1">
                    <h3 class="font-semibold text-lg text-base-content hover:text-primary truncate">
                        {{ $milestone->title }}
                    </h3>
                    <span class="badge {{ $milestone->status_badge }} badge-sm">{{ $milestone->status_label }}</span>
                    <span class="badge badge-outline badge-sm" style="border-color: {{ $milestone->priority_color }}; color: {{ $milestone->priority_color }};">
                        {{ $milestone->priority_label }}
                    </span>
                    @if($milestone->isOverdue())
                        <span class="badge badge-error badge-sm">Overdue</span>
                    @endif
                </div>

                <!-- Description preview -->
                @if($milestone->description)
                    <p class="text-sm text-base-content/60 truncate mb-2">{{ Str::limit(strip_tags($milestone->description), 100) }}</p>
                @endif

                <!-- Tags inline -->
                @if($milestone->tags->isNotEmpty())
                    <div class="flex flex-wrap gap-1">
                        @foreach($milestone->tags->take(5) as $tag)
                            <span class="badge badge-xs" style="background-color: {{ $tag->color }}20; color: {{ $tag->color }}; border: 1px solid {{ $tag->color }}40;">
                                {{ $tag->name }}
                            </span>
                        @endforeach
                        @if($milestone->tags->count() > 5)
                            <span class="badge badge-ghost badge-xs">+{{ $milestone->tags->count() - 5 }}</span>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Progress Section -->
            <div class="w-32 flex-shrink-0">
                <div class="flex items-center justify-between text-sm mb-1">
                    <span class="text-base-content/60">Progress</span>
                    <span class="font-bold" style="color: {{ $milestone->status_color }}">{{ $milestone->progress }}%</span>
                </div>
                <div class="w-full bg-base-200 rounded-full h-2">
                    <div class="h-2 rounded-full transition-all duration-300" style="width: {{ $milestone->progress }}%; background-color: {{ $milestone->status_color }};"></div>
                </div>
            </div>

            <!-- Stats Section -->
            <div class="flex items-center gap-6 flex-shrink-0 text-sm text-base-content/60">
                <!-- Due Date -->
                @if($milestone->due_date)
                    <div class="flex items-center gap-1 {{ $milestone->isOverdue() ? 'text-error' : '' }}">
                        <span class="icon-[tabler--calendar] size-4"></span>
                        <span>{{ $milestone->due_date->format('M d, Y') }}</span>
                        @if($milestone->days_remaining !== null && !$milestone->isCompleted())
                            @if($milestone->days_remaining < 0)
                                <span class="text-error text-xs">({{ abs($milestone->days_remaining) }}d late)</span>
                            @elseif($milestone->days_remaining === 0)
                                <span class="text-warning text-xs">(Today)</span>
                            @elseif($milestone->days_remaining <= 7)
                                <span class="text-warning text-xs">({{ $milestone->days_remaining }}d)</span>
                            @endif
                        @endif
                    </div>
                @endif

                <!-- Tasks Count -->
                <div class="flex items-center gap-1">
                    <span class="icon-[tabler--checkbox] size-4"></span>
                    <span>{{ $milestone->tasks_count ?? $milestone->tasks->count() }} tasks</span>
                </div>

                <!-- Owner -->
                @if($milestone->owner)
                    <div class="flex items-center gap-2">
                        @include('partials.user-avatar', ['user' => $milestone->owner, 'size' => 'xs'])
                        <span class="hidden md:inline">{{ $milestone->owner->full_name }}</span>
                    </div>
                @endif
            </div>

            <!-- Arrow indicator -->
            <div class="flex-shrink-0">
                <span class="icon-[tabler--chevron-right] size-5 text-base-content/30"></span>
            </div>
        </div>
    </div>
</a>
