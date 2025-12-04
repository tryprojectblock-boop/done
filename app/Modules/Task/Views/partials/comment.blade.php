@php
    $user = auth()->user();
@endphp

<div class="flex gap-3" id="comment-{{ $comment->id }}">
    <div class="avatar">
        <div class="w-10 h-10 rounded-full overflow-hidden">
            <img src="{{ $comment->user->avatar_url }}" alt="{{ $comment->user->name }}" class="w-full h-full object-cover" />
        </div>
    </div>
    <div class="flex-1">
        <div class="bg-base-200 rounded-lg p-3">
            <div class="flex items-center justify-between mb-1">
                <div class="flex items-center gap-2">
                    <span class="font-medium">{{ $comment->user->name }}</span>
                    <span class="text-xs text-base-content/60">{{ $comment->created_at->diffForHumans() }}</span>
                    @if($comment->is_edited)
                        <span class="text-xs text-base-content/40">(edited)</span>
                    @endif
                </div>
            </div>

            <div class="prose prose-sm max-w-none" id="comment-content-{{ $comment->id }}">
                {!! $comment->content !!}
            </div>

            <!-- Edit Form (hidden by default) -->
            <form action="{{ route('tasks.comments.update', $comment) }}" method="POST"
                  id="comment-edit-{{ $comment->id }}" class="hidden">
                @csrf
                @method('PATCH')
                <textarea name="content" class="textarea textarea-bordered w-full mt-2" rows="3">{{ strip_tags($comment->content) }}</textarea>
                <div class="flex justify-end gap-2 mt-2">
                    <button type="button" class="btn btn-ghost btn-xs" onclick="cancelEdit({{ $comment->id }})">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-xs">Save</button>
                </div>
            </form>
        </div>

        <!-- Action Buttons Row -->
        <div class="flex items-center gap-2 mt-1">
            <button type="button" class="inline-flex items-center gap-1 text-xs text-base-content/50 hover:text-primary transition-colors" onclick="toggleReply({{ $comment->id }})">
                <span class="icon-[tabler--message] size-4"></span>
                Reply
            </button>
            @if($comment->canEdit($user))
                <button type="button" class="inline-flex items-center gap-1 text-xs text-base-content/50 hover:text-primary transition-colors" onclick="editComment({{ $comment->id }})">
                    <span class="icon-[tabler--edit] size-4"></span>
                    Edit
                </button>
            @endif
            @if($comment->canDelete($user))
                <form action="{{ route('tasks.comments.destroy', $comment) }}" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center gap-1 text-xs text-base-content/50 hover:text-error transition-colors" onclick="return confirm('Delete this comment?')">
                        <span class="icon-[tabler--trash] size-4"></span>
                        Delete
                    </button>
                </form>
            @endif
        </div>

        <!-- Reply Form (hidden by default) -->
        <form action="{{ route('tasks.comments.store', $comment->task) }}" method="POST"
              id="reply-form-{{ $comment->id }}" class="hidden mt-2">
            @csrf
            <input type="hidden" name="parent_id" value="{{ $comment->id }}">
            <textarea name="content" class="textarea textarea-bordered textarea-sm w-full" rows="2"
                      placeholder="Write a reply..." required></textarea>
            <div class="flex justify-end gap-2 mt-2">
                <button type="button" class="btn btn-ghost btn-xs" onclick="toggleReply({{ $comment->id }})">Cancel</button>
                <button type="submit" class="btn btn-primary btn-xs">Reply</button>
            </div>
        </form>

        <!-- Replies -->
        @if($comment->replies->isNotEmpty())
            <div class="ml-4 mt-3 space-y-3 border-l-2 border-base-300 pl-4">
                @foreach($comment->replies as $reply)
                    @include('task::partials.comment', ['comment' => $reply])
                @endforeach
            </div>
        @endif
    </div>
</div>

@once
@push('scripts')
<script>
function editComment(id) {
    document.getElementById('comment-content-' + id).classList.add('hidden');
    document.getElementById('comment-edit-' + id).classList.remove('hidden');
}

function cancelEdit(id) {
    document.getElementById('comment-content-' + id).classList.remove('hidden');
    document.getElementById('comment-edit-' + id).classList.add('hidden');
}

function toggleReply(id) {
    document.getElementById('reply-form-' + id).classList.toggle('hidden');
}
</script>
@endpush
@endonce
