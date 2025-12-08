<!-- Fixed Sidebar - Channels List -->
<aside class="w-64 bg-base-100 border-r border-base-200 flex-shrink-0 hidden lg:block">
    <div class="sticky top-16 h-[calc(100vh-4rem)] overflow-y-auto">
        <!-- Sidebar Header -->
        <div class="p-4 border-b border-base-200">
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-base-content">Channels</h2>
                @if($user->isAdminOrHigher())
                <a href="{{ route('channels.create') }}" class="btn btn-ghost btn-xs btn-square" title="Create Channel">
                    <span class="icon-[tabler--plus] size-4"></span>
                </a>
                @endif
            </div>
        </div>

        <!-- Channels List -->
        <nav class="p-2">
            @forelse($allChannels as $ch)
            @php
                $chCanAccess = $ch->canAccess($user);
                $isActive = isset($channel) && $ch->id === $channel->id;
            @endphp
            @if($chCanAccess)
            <a href="{{ route('channels.show', $ch) }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {{ $isActive ? 'bg-primary/10 text-primary' : 'hover:bg-base-200 text-base-content/70' }}">
                <span class="icon-[tabler--hash] size-4 flex-shrink-0"></span>
                <span class="truncate flex-1">{{ $ch->name }}</span>
                @if($ch->is_private)
                <span class="icon-[tabler--lock] size-3 text-base-content/40"></span>
                @endif
            </a>
            @else
            <div class="flex items-center gap-3 px-3 py-2 rounded-lg text-base-content/40 cursor-not-allowed" title="You need to be invited to access this channel">
                <span class="icon-[tabler--hash] size-4 flex-shrink-0"></span>
                <span class="truncate flex-1">{{ $ch->name }}</span>
                <span class="icon-[tabler--lock] size-3"></span>
            </div>
            @endif
            @empty
            <p class="text-sm text-base-content/50 text-center py-4">No channels yet</p>
            @endforelse
        </nav>

        <!-- Back to Discussions -->
        <div class="p-4 border-t border-base-200 mt-auto">
            <a href="{{ route('discussions.index') }}" class="flex items-center gap-2 text-sm text-base-content/60 hover:text-base-content transition-colors">
                <span class="icon-[tabler--arrow-left] size-4"></span>
                Back to Discussions
            </a>
        </div>
    </div>
</aside>
