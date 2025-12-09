<div class="comment-card {{ $comment->is_resolved ? 'resolved' : '' }}"
     data-comment-id="{{ $comment->id }}"
     data-selection-id="{{ $comment->selection_id }}">
    <!-- Comment Header -->
    <div class="flex items-start gap-3 mb-2">
        <div class="avatar">
            <div class="w-8 rounded-full">
                <img src="{{ $comment->user->avatar_url }}" alt="{{ $comment->user->name }}" />
            </div>
        </div>
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2">
                <span class="font-medium text-sm">{{ $comment->user->name }}</span>
                @if($comment->is_resolved)
                    <span class="badge badge-success badge-xs">Resolved</span>
                @endif
            </div>
            <span class="text-xs text-base-content/50" title="{{ $comment->created_at->format('M d, Y g:i A') }}">
                {{ $comment->created_at->diffForHumans() }}
            </span>
        </div>
        <div class="dropdown relative inline-flex [--auto-close:inside] [--offset:8] [--placement:bottom-end]">
            <button type="button" class="dropdown-toggle btn btn-ghost btn-xs btn-circle" aria-haspopup="menu" aria-expanded="false">
                <span class="icon-[tabler--dots] size-4"></span>
            </button>
            <ul class="dropdown-menu dropdown-open:opacity-100 hidden min-w-32" role="menu">
                @if(!$comment->is_resolved)
                    <li>
                        <button type="button" class="dropdown-item" data-action="resolve" data-comment-id="{{ $comment->id }}">
                            <span class="icon-[tabler--check] size-4"></span>
                            Resolve
                        </button>
                    </li>
                @else
                    <li>
                        <button type="button" class="dropdown-item" data-action="unresolve" data-comment-id="{{ $comment->id }}">
                            <span class="icon-[tabler--refresh] size-4"></span>
                            Reopen
                        </button>
                    </li>
                @endif
                @if($comment->user_id === auth()->id() || $comment->document->canDelete(auth()->user()))
                    <li>
                        <button type="button" class="dropdown-item text-error" data-action="delete" data-comment-id="{{ $comment->id }}">
                            <span class="icon-[tabler--trash] size-4"></span>
                            Delete
                        </button>
                    </li>
                @endif
            </ul>
        </div>
    </div>

    <!-- Selected Text Quote -->
    @if($comment->selection_text)
        <div class="bg-base-300/50 rounded px-2 py-1 mb-2 text-xs text-base-content/70 italic border-l-2 border-warning">
            "{{ Str::limit($comment->selection_text, 100) }}"
        </div>
    @endif

    <!-- Comment Content -->
    <div class="text-sm mb-3">
        {!! nl2br(e($comment->content)) !!}
    </div>

    <!-- Replies -->
    <div class="replies-container space-y-3 ml-4 border-l-2 border-base-300 pl-3">
        @foreach($comment->replies as $reply)
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
        @endforeach
    </div>

    <!-- Reply Form (only show if not resolved) -->
    @if(!$comment->is_resolved)
        <div class="mt-2">
            <button type="button" data-action="reply" class="btn btn-soft btn-primary btn-xs">
                <span class="icon-[tabler--corner-down-right] size-3"></span>
                Reply
            </button>
            <form class="reply-form hidden mt-2" data-comment-id="{{ $comment->id }}">
                <textarea class="textarea textarea-bordered textarea-sm w-full" rows="2" placeholder="Write a reply..." required></textarea>
                <div class="flex gap-2 mt-2">
                    <button type="submit" class="btn btn-primary btn-xs">Post</button>
                    <button type="button" class="btn btn-ghost btn-xs cancel-reply">Cancel</button>
                </div>
            </form>
        </div>
    @endif
</div>
