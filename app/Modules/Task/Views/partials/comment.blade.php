@php
    $user = auth()->user();
    $editEditorId = 'edit-editor-' . $comment->id;
    $replyEditorId = 'reply-editor-' . $comment->id;
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

            <div class="prose prose-sm max-w-none comment-content" id="comment-content-{{ $comment->id }}">
                {!! $comment->content !!}
            </div>

            <!-- Edit Form (hidden by default) -->
            <div id="comment-edit-{{ $comment->id }}" class="hidden mt-2">
                <form action="{{ route('tasks.comments.update', $comment) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="border border-base-300 rounded-lg overflow-hidden bg-white">
                        <div id="{{ $editEditorId }}"
                             class="quill-editor quill-mini"
                             data-quill-id="{{ $editEditorId }}"
                             data-placeholder="Edit your comment..."
                             data-upload-url="{{ route('upload.image') }}"
                             data-mentions-url="{{ route('mentions.search') }}"
                             data-csrf="{{ csrf_token() }}"
                             data-initial-content="{{ json_encode($comment->content) }}"
                             data-enable-mentions="true"
                             data-enable-emoji="true"
                             style="min-height: 80px;"></div>
                    </div>
                    <input type="hidden" name="content" id="{{ $editEditorId }}-input" value="{{ e($comment->content) }}">
                    <div class="flex justify-end gap-2 mt-2">
                        <button type="button" class="btn btn-ghost btn-xs" onclick="cancelEdit({{ $comment->id }})">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-xs">Save</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Action Buttons Row -->
        <div class="flex items-center gap-2 mt-1">
            <button type="button" class="inline-flex items-center gap-1 text-xs text-base-content/50 hover:text-primary transition-colors" onclick="toggleReply({{ $comment->id }}, '{{ $replyEditorId }}')">
                <span class="icon-[tabler--message] size-4"></span>
                Reply
            </button>
            @if($comment->canEdit($user))
                <button type="button" class="inline-flex items-center gap-1 text-xs text-base-content/50 hover:text-primary transition-colors" onclick="editComment({{ $comment->id }}, '{{ $editEditorId }}')">
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
        <div id="reply-form-{{ $comment->id }}" class="hidden mt-2">
            <form action="{{ route('tasks.comments.store', $comment->task) }}" method="POST">
                @csrf
                <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                <div class="border border-base-300 rounded-lg overflow-hidden bg-white">
                    <div id="{{ $replyEditorId }}"
                         class="quill-editor quill-mini"
                         data-quill-id="{{ $replyEditorId }}"
                         data-placeholder="Write a reply..."
                         data-upload-url="{{ route('upload.image') }}"
                         data-mentions-url="{{ route('mentions.search') }}"
                         data-csrf="{{ csrf_token() }}"
                         data-initial-content=""
                         data-enable-mentions="true"
                         data-enable-emoji="true"
                         style="min-height: 60px;"></div>
                </div>
                <input type="hidden" name="content" id="{{ $replyEditorId }}-input" value="">
                <div class="flex justify-end gap-2 mt-2">
                    <button type="button" class="btn btn-ghost btn-xs" onclick="toggleReply({{ $comment->id }}, '{{ $replyEditorId }}')">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-xs">Reply</button>
                </div>
            </form>
        </div>

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
function editComment(id, editorId) {
    document.getElementById('comment-content-' + id).classList.add('hidden');
    document.getElementById('comment-edit-' + id).classList.remove('hidden');
    // Initialize Quill editor if not already initialized
    initMiniQuill(editorId);
}

function cancelEdit(id) {
    document.getElementById('comment-content-' + id).classList.remove('hidden');
    document.getElementById('comment-edit-' + id).classList.add('hidden');
}

function toggleReply(id, editorId) {
    const form = document.getElementById('reply-form-' + id);
    form.classList.toggle('hidden');
    // Initialize Quill editor if not already initialized
    if (!form.classList.contains('hidden')) {
        initMiniQuill(editorId);
    }
}

function initMiniQuill(editorId) {
    const el = document.getElementById(editorId);
    if (!el || el.quillInstance) return;

    const placeholder = el.dataset.placeholder || 'Write something...';
    const uploadUrl = el.dataset.uploadUrl;
    const csrfToken = el.dataset.csrf;
    let initialContent = '';
    try {
        initialContent = JSON.parse(el.dataset.initialContent || '""');
    } catch (e) {
        initialContent = el.dataset.initialContent || '';
    }

    if (typeof window.initQuillEditor === 'function') {
        window.initQuillEditor(editorId, placeholder, uploadUrl, csrfToken, initialContent);
    }
}
</script>
@endpush
@endonce
