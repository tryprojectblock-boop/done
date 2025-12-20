<div class="card bg-base-100 shadow hover:shadow-lg transition-shadow">
    <div class="card-body">
        <!-- Header -->
        <div class="flex items-start justify-between">
            <div class="flex items-center gap-3">
                <div class="avatar">
                    <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center">
                        @if($entry->user->avatar_url)
                            <img src="{{ $entry->user->avatar_url }}" alt="{{ $entry->user->name }}" class="rounded-full" />
                        @else
                            <span class="text-sm font-medium text-primary">{{ substr($entry->user->name, 0, 2) }}</span>
                        @endif
                    </div>
                </div>
                <div>
                    <h3 class="font-semibold text-base-content">{{ $entry->user->name }}</h3>
                    <p class="text-xs text-base-content/50">{{ $entry->created_at->format('g:i A') }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                @if($entry->mood)
                    <span class="text-2xl" title="{{ $entry->getMoodLabel() }}">{{ $entry->getMoodEmoji() }}</span>
                @endif
                @if($entry->has_blockers)
                    <span class="badge badge-error badge-sm">Blocked</span>
                @endif
            </div>
        </div>

        <!-- Responses -->
        <div class="mt-4 space-y-3">
            @if($entry->getYesterdayResponse())
                <div>
                    <span class="text-xs font-medium text-base-content/50 uppercase">Yesterday</span>
                    <p class="text-sm text-base-content mt-1">{{ Str::limit($entry->getYesterdayResponse(), 150) }}</p>
                </div>
            @endif

            @if($entry->getTodayResponse())
                <div>
                    <span class="text-xs font-medium text-base-content/50 uppercase">Today</span>
                    <p class="text-sm text-base-content mt-1">{{ Str::limit($entry->getTodayResponse(), 150) }}</p>
                </div>
            @endif

            @if($entry->getBlockersResponse())
                <div>
                    <span class="text-xs font-medium text-error uppercase">Blockers</span>
                    <p class="text-sm text-base-content mt-1">{{ Str::limit($entry->getBlockersResponse(), 150) }}</p>
                </div>
            @endif
        </div>

        <!-- Actions (for own entries from today) -->
        @if($entry->user_id === auth()->id() && $entry->standup_date->isToday())
            <div class="mt-4 pt-3 border-t border-base-200">
                <a href="{{ route('standups.edit', ['workspace' => $workspace, 'entry' => $entry]) }}"
                   class="btn btn-ghost btn-sm">
                    <span class="icon-[tabler--edit] size-4"></span>
                    Edit
                </a>
            </div>
        @endif
    </div>
</div>
