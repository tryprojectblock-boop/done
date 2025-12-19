@php $user = $user ?? auth()->user(); @endphp
<div class="card bg-base-100 shadow" id="thread-reply-{{ $reply->uuid }}">
    <div class="card-body p-4">
        <div class="flex items-start gap-3">
            <!-- Avatar -->
            <div class="avatar placeholder flex-shrink-0">
                @if($reply->user->avatar_url)
                    <div class="w-10 h-10 rounded-full">
                        <img src="{{ $reply->user->avatar_url }}" alt="{{ $reply->user->name }}" />
                    </div>
                @else
                    <div class="w-10 h-10 rounded-full bg-primary/10 text-primary">
                        <span class="text-sm">{{ substr($reply->user->first_name, 0, 1) }}{{ substr($reply->user->last_name, 0, 1) }}</span>
                    </div>
                @endif
            </div>

            <!-- Content -->
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1">
                    <span class="font-medium text-sm">{{ $reply->user->name }}</span>
                    <span class="text-xs text-base-content/50">{{ $reply->created_at->diffForHumans() }}</span>
                    @if($reply->is_edited)
                        <span class="text-xs text-base-content/40">(edited)</span>
                    @endif
                </div>
                <div class="prose prose-sm max-w-none text-base-content/80">
                    {!! $reply->content !!}
                </div>

                <!-- Reply Actions -->
                <div class="flex items-center gap-2 mt-2">
                    @if($reply->canEdit($user))
                    <button type="button" class="btn btn-ghost btn-xs" onclick="toggleEditForm('{{ $reply->uuid }}')">
                        <span class="icon-[tabler--edit] size-3"></span>
                        Edit
                    </button>
                    @endif
                    @if($reply->canDelete($user))
                    <form action="{{ route('channels.replies.destroy', $reply) }}" method="POST" class="inline" onsubmit="return confirm('Delete this reply?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-ghost btn-xs text-error">
                            <span class="icon-[tabler--trash] size-3"></span>
                            Delete
                        </button>
                    </form>
                    @endif
                </div>

                <!-- Edit Form (hidden by default) -->
                <div id="edit-form-{{ $reply->uuid }}" class="hidden mt-3">
                    <form action="{{ route('channels.replies.update', $reply) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <textarea name="content" class="textarea textarea-bordered w-full h-24 text-sm" required>{{ $reply->content }}</textarea>
                        <div class="flex justify-end gap-2 mt-2">
                            <button type="button" class="btn btn-ghost btn-sm" onclick="toggleEditForm('{{ $reply->uuid }}')">Cancel</button>
                            <button type="submit" class="btn btn-primary btn-sm">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Nested Replies -->
        @if($reply->replies->count() > 0)
        <div class="ml-12 mt-4 space-y-3 border-l-2 border-base-200 pl-4">
            @foreach($reply->replies as $nestedReply)
                @include('discussion::channels.partials.reply', ['reply' => $nestedReply, 'channel' => $channel, 'thread' => $thread, 'user' => $user ?? auth()->user()])
            @endforeach
        </div>
        @endif
    </div>
</div>

@pushOnce('scripts')
<script>
function toggleEditForm(uuid) {
    const form = document.getElementById('edit-form-' + uuid);
    form.classList.toggle('hidden');
}
</script>
@endPushOnce
