<div class="reply-item" data-reply-id="{{ $reply->id }}">
    <div class="flex items-start gap-2">
        <div class="avatar">
            <div class="w-6 rounded-full">
                <img src="{{ $reply->user->avatar_url }}" alt="{{ $reply->user->name }}" />
            </div>
        </div>
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2">
                <span class="font-medium text-xs">{{ $reply->user->name }}</span>
                <span class="text-xs text-base-content/50">{{ $reply->created_at->diffForHumans() }}</span>
            </div>
            <p class="text-sm mt-1">{!! nl2br(e($reply->content)) !!}</p>
        </div>
        @if($reply->user_id === auth()->id())
            <button type="button" class="btn btn-ghost btn-xs btn-circle delete-reply-btn" data-reply-id="{{ $reply->id }}">
                <span class="icon-[tabler--x] size-3"></span>
            </button>
        @endif
    </div>
</div>
