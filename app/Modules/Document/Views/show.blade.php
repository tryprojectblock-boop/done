@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-base-200/50">
    <!-- Document Header -->
    <div class="bg-base-100 border-b border-base-300 sticky top-0 z-20">
        <div class="max-w-full mx-auto px-4 py-3">
            <div class="flex items-center justify-between gap-4">
                <!-- Left: Back & Save Status -->
                <div class="flex items-center gap-3">
                    <a href="{{ route('documents.index') }}" class="btn btn-ghost btn-sm btn-circle">
                        <span class="icon-[tabler--arrow-left] size-5"></span>
                    </a>
                    <span id="save-status" class="text-sm text-base-content/50 flex items-center gap-1">
                        <span class="icon-[tabler--check] size-4"></span>
                        Saved
                    </span>
                </div>

                <!-- Right: Actions -->
                <div class="flex items-center gap-2">
                    @if($canEdit)
                        <button type="button" id="save-btn" class="btn btn-primary btn-sm">
                            <span class="icon-[tabler--device-floppy] size-4"></span>
                            <span class="hidden sm:inline">Save</span>
                        </button>
                    @endif

                    <div class="dropdown relative inline-flex [--auto-close:inside] [--offset:8] [--placement:bottom-end]">
                        <button id="document-actions-dropdown" type="button" class="dropdown-toggle btn btn-ghost btn-sm btn-circle" aria-haspopup="menu" aria-expanded="false" aria-label="Document actions">
                            <span class="icon-[tabler--dots-vertical] size-5"></span>
                        </button>
                        <ul class="dropdown-menu dropdown-open:opacity-100 hidden min-w-48" role="menu" aria-orientation="vertical" aria-labelledby="document-actions-dropdown">
                            <li>
                                <a class="dropdown-item" href="{{ route('documents.versions', $document->uuid) }}">
                                    <span class="icon-[tabler--history] size-4"></span>
                                    Version History
                                </a>
                            </li>
                            @if($canEdit)
                                <li>
                                    <a class="dropdown-item" href="{{ route('documents.edit', $document->uuid) }}">
                                        <span class="icon-[tabler--settings] size-4"></span>
                                        Document Settings
                                    </a>
                                </li>
                            @endif
                            @if($document->canDelete(auth()->user()))
                                <li><hr class="border-base-content/10 my-1"></li>
                                <li>
                                    <form action="{{ route('documents.destroy', $document->uuid) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this document?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dropdown-item text-error w-full text-left">
                                            <span class="icon-[tabler--trash] size-4"></span>
                                            Delete Document
                                        </button>
                                    </form>
                                </li>
                            @endif
                        </ul>
                    </div>

                    <!-- Toggle Comments Sidebar -->
                    <button type="button" id="toggle-comments" class="btn btn-ghost btn-sm btn-circle relative" title="Toggle Comments">
                        <span class="icon-[tabler--message] size-5"></span>
                        @if($document->comments->where('is_resolved', false)->count() > 0)
                            <span class="badge badge-primary badge-xs absolute -top-1 -right-1">{{ $document->comments->where('is_resolved', false)->count() }}</span>
                        @endif
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="flex h-[calc(100vh-57px)]">
        <!-- Editor Area -->
        <div id="editor-container" class="flex-1 overflow-y-auto transition-all duration-300">
            <div class="max-w-4xl mx-auto px-4 py-4">
                <!-- Add Comment Tooltip (appears on text selection) -->
                <div id="add-comment-tooltip" class="fixed z-50 hidden">
                    <button type="button" id="add-comment-btn" class="btn btn-primary btn-sm shadow-lg">
                        <span class="icon-[tabler--message-plus] size-4"></span>
                        Comment
                    </button>
                </div>

                <!-- Document Card with Title & Editor -->
                <div class="card bg-base-100 shadow-lg">
                    <div class="card-body p-0">
                        <!-- Editable Title -->
                        <div class="px-6 pt-6 pb-3 border-b border-base-200">
                            <input type="text"
                                   id="document-title"
                                   value="{{ $document->title }}"
                                   placeholder="Untitled Document"
                                   class="w-full text-2xl font-bold bg-transparent border-0 outline-none focus:ring-0 placeholder:text-base-content/30 {{ !$canEdit ? 'pointer-events-none' : '' }}"
                                   {{ !$canEdit ? 'readonly' : '' }}>
                            @if($document->description)
                                <p class="text-sm text-base-content/60 mt-1 italic">{{ $document->description }}</p>
                            @endif
                        </div>

                        <!-- Document Editor -->
                        <div id="document-editor"
                             class="document-editor"
                             data-document-uuid="{{ $document->uuid }}"
                             data-can-edit="{{ $canEdit ? 'true' : 'false' }}"
                             data-content-url="{{ route('api.documents.content.get', $document->uuid) }}"
                             data-save-url="{{ route('api.documents.content.save', $document->uuid) }}"
                             data-autosave-url="{{ route('api.documents.content.autosave', $document->uuid) }}"
                             data-upload-url="{{ route('upload.image') }}"
                             data-csrf="{{ csrf_token() }}"
                             data-initial-content="{{ json_encode($document->content ?? '') }}">
                        </div>
                    </div>
                </div>

                <!-- Last Edited Info -->
                <div class="mt-3 text-xs text-base-content/50 text-center pb-4">
                    @if($document->last_edited_at)
                        Last edited by {{ $document->lastEditor?->name ?? 'Unknown' }}
                        <span title="{{ $document->last_edited_at->format('M d, Y g:i A') }}">
                            {{ $document->last_edited_at->diffForHumans() }}
                        </span>
                    @else
                        Created {{ $document->created_at->diffForHumans() }}
                    @endif
                </div>
            </div>
        </div>

        <!-- Comments Sidebar -->
        <div id="comments-sidebar" class="w-72 bg-base-100 border-l border-base-300 h-full overflow-hidden flex flex-col transition-all duration-300">
            <!-- Comments Header -->
            <div class="p-3 border-b border-base-300">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="font-semibold text-sm">Comments</h3>
                    <button type="button" id="close-comments" class="btn btn-ghost btn-xs btn-circle md:hidden">
                        <span class="icon-[tabler--x] size-4"></span>
                    </button>
                </div>
                <!-- Filter -->
                <div class="flex gap-1">
                    <button type="button" class="comment-filter-btn btn btn-xs btn-ghost active" data-filter="all">All</button>
                    <button type="button" class="comment-filter-btn btn btn-xs btn-ghost" data-filter="open">Open</button>
                    <button type="button" class="comment-filter-btn btn btn-xs btn-ghost" data-filter="resolved">Resolved</button>
                </div>
            </div>

            <!-- New Comment Form (shown when text is selected) - Positioned at top -->
            <div id="new-comment-form" class="p-3 border-b border-base-300 bg-primary/5 hidden">
                <form id="comment-form" class="space-y-2">
                    <div class="text-xs text-base-content/50">
                        <span class="icon-[tabler--quote] size-3"></span>
                        "<span id="selected-text-preview" class="italic"></span>"
                    </div>
                    <textarea id="comment-content" class="textarea textarea-bordered textarea-sm w-full" rows="2" placeholder="Add a comment..." required></textarea>
                    <input type="hidden" id="selection-start" value="">
                    <input type="hidden" id="selection-end" value="">
                    <input type="hidden" id="selection-text" value="">
                    <input type="hidden" id="selection-id" value="">
                    <div class="flex gap-2">
                        <button type="submit" class="btn btn-primary btn-xs flex-1">
                            <span class="icon-[tabler--send] size-3"></span>
                            Post
                        </button>
                        <button type="button" id="cancel-comment" class="btn btn-ghost btn-xs">Cancel</button>
                    </div>
                </form>
            </div>

            <!-- Comments List -->
            <div id="comments-list" class="flex-1 overflow-y-auto p-3 space-y-3">
                @forelse($document->comments->sortByDesc('created_at') as $comment)
                    @include('document::partials.comment', ['comment' => $comment])
                @empty
                    <div id="no-comments" class="text-center py-6 text-base-content/50">
                        <span class="icon-[tabler--message-off] size-10 block mx-auto mb-2 opacity-50"></span>
                        <p class="font-medium text-sm">No comments yet</p>
                        <p class="text-xs">Select text to add a comment</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
<style>
/* Document Editor Styles */
.document-editor {
    min-height: 250px;
}

.document-editor .ql-toolbar {
    border: none !important;
    border-bottom: 1px solid oklch(var(--bc) / 0.1) !important;
    background: oklch(var(--b2));
    padding: 8px 12px;
    position: sticky;
    top: 0;
    z-index: 10;
}

.document-editor .ql-container {
    border: none !important;
    font-family: inherit;
    font-size: 0.95rem;
    line-height: 1.6;
}

.document-editor .ql-editor {
    padding: 16px 24px;
    min-height: 250px;
}

.document-editor .ql-editor.ql-blank::before {
    color: oklch(var(--bc) / 0.3);
    font-style: normal;
    left: 24px;
}

/* Read-only mode */
.document-editor.read-only .ql-toolbar {
    display: none;
}

.document-editor.read-only .ql-editor {
    cursor: default;
}

/* Comment highlight in document */
.comment-highlight {
    background-color: oklch(var(--wa) / 0.3);
    border-bottom: 2px solid oklch(var(--wa));
    cursor: pointer;
    transition: background-color 0.2s;
}

.comment-highlight:hover,
.comment-highlight.active {
    background-color: oklch(var(--wa) / 0.5);
}

.comment-highlight.resolved {
    background-color: oklch(var(--su) / 0.2);
    border-bottom-color: oklch(var(--su));
}

/* Comments sidebar */
#comments-sidebar.hidden-sidebar {
    width: 0;
    padding: 0;
    overflow: hidden;
    border: none;
}

#editor-container.full-width {
    margin-right: 0;
}

/* Compact comment cards */
.comment-card {
    padding: 0.75rem !important;
}

/* Comment card */
.comment-card {
    background: oklch(var(--b2));
    border-radius: 0.75rem;
    padding: 1rem;
    transition: all 0.2s;
}

.comment-card:hover {
    background: oklch(var(--b3));
}

.comment-card.active {
    ring: 2px solid oklch(var(--p));
    background: oklch(var(--p) / 0.05);
}

.comment-card.resolved {
    opacity: 0.7;
}

/* Filter buttons */
.comment-filter-btn.active {
    background: oklch(var(--p) / 0.1);
    color: oklch(var(--p));
}

/* Mobile responsive */
@media (max-width: 768px) {
    #comments-sidebar {
        position: fixed;
        right: 0;
        top: 57px;
        height: calc(100vh - 57px);
        z-index: 30;
        transform: translateX(100%);
    }

    #comments-sidebar.show-mobile {
        transform: translateX(0);
    }
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const editorEl = document.getElementById('document-editor');
    const canEdit = editorEl.dataset.canEdit === 'true';
    const saveUrl = editorEl.dataset.saveUrl;
    const autosaveUrl = editorEl.dataset.autosaveUrl;
    const uploadUrl = editorEl.dataset.uploadUrl;
    const csrfToken = editorEl.dataset.csrf;
    const documentUuid = editorEl.dataset.documentUuid;

    let initialContent = '';
    try {
        initialContent = JSON.parse(editorEl.dataset.initialContent || '""');
    } catch (e) {
        initialContent = editorEl.dataset.initialContent || '';
    }

    // Elements
    const titleInput = document.getElementById('document-title');
    const saveBtn = document.getElementById('save-btn');
    const saveStatus = document.getElementById('save-status');
    const toggleCommentsBtn = document.getElementById('toggle-comments');
    const closeCommentsBtn = document.getElementById('close-comments');
    const commentsSidebar = document.getElementById('comments-sidebar');
    const editorContainer = document.getElementById('editor-container');
    const addCommentTooltip = document.getElementById('add-comment-tooltip');
    const addCommentBtn = document.getElementById('add-comment-btn');
    const newCommentForm = document.getElementById('new-comment-form');
    const commentForm = document.getElementById('comment-form');
    const cancelCommentBtn = document.getElementById('cancel-comment');
    const commentsList = document.getElementById('comments-list');

    // State
    let lastSavedContent = initialContent;
    let lastSavedTitle = titleInput?.value || '';
    let autoSaveTimer = null;
    let currentSelection = null;

    // Build toolbar
    const toolbarOptions = canEdit ? [
        [{ 'header': [1, 2, 3, false] }],
        ['bold', 'italic', 'underline', 'strike'],
        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
        [{ 'indent': '-1'}, { 'indent': '+1' }],
        ['link', 'image'],
        ['blockquote', 'code-block'],
        [{ 'color': [] }, { 'background': [] }],
        ['clean']
    ] : false;

    // Build modules config
    const modules = {
        toolbar: canEdit ? {
            container: toolbarOptions,
            handlers: {
                image: function() {
                    const input = document.createElement('input');
                    input.setAttribute('type', 'file');
                    input.setAttribute('accept', 'image/*');
                    input.click();

                    input.onchange = async () => {
                        const file = input.files[0];
                        if (file) {
                            await uploadImage(file);
                        }
                    };
                }
            }
        } : false
    };

    // Initialize Quill
    const quill = new Quill(editorEl, {
        theme: 'snow',
        placeholder: canEdit ? 'Start writing your document...' : '',
        readOnly: !canEdit,
        modules: modules
    });

    // Add read-only class if needed
    if (!canEdit) {
        editorEl.classList.add('read-only');
    }

    // Set initial content
    if (initialContent) {
        quill.root.innerHTML = initialContent;
    }

    // ==================== SAVE FUNCTIONALITY ====================

    function updateSaveStatus(status) {
        const icons = {
            saving: '<span class="loading loading-spinner loading-xs"></span>',
            saved: '<span class="icon-[tabler--check] size-4"></span>',
            error: '<span class="icon-[tabler--alert-circle] size-4 text-error"></span>'
        };
        const texts = {
            saving: 'Saving...',
            saved: 'Saved',
            error: 'Save failed'
        };
        saveStatus.innerHTML = icons[status] + ' <span class="ml-1">' + texts[status] + '</span>';
    }

    async function saveDocument(createVersion = false) {
        if (!canEdit) return;

        const content = quill.root.innerHTML;
        const title = titleInput?.value?.trim() || 'Untitled Document';

        if (content === lastSavedContent && title === lastSavedTitle && !createVersion) return;

        updateSaveStatus('saving');

        try {
            const url = createVersion ? saveUrl : autosaveUrl;
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    content: content,
                    title: title
                })
            });

            const result = await response.json();

            if (result.success) {
                lastSavedContent = content;
                lastSavedTitle = title;
                updateSaveStatus('saved');
            } else {
                updateSaveStatus('error');
                console.error('Save failed:', result.error);
            }
        } catch (error) {
            updateSaveStatus('error');
            console.error('Save error:', error);
        }
    }

    // Auto-save on content change (2 second debounce)
    if (canEdit) {
        quill.on('text-change', function() {
            updateSaveStatus('saving');
            clearTimeout(autoSaveTimer);
            autoSaveTimer = setTimeout(() => saveDocument(false), 2000);
        });

        // Title change triggers auto-save
        titleInput?.addEventListener('input', function() {
            updateSaveStatus('saving');
            clearTimeout(autoSaveTimer);
            autoSaveTimer = setTimeout(() => saveDocument(false), 2000);
        });

        // Manual save button
        if (saveBtn) {
            saveBtn.addEventListener('click', () => saveDocument(true));
        }

        // Save before leaving
        window.addEventListener('beforeunload', function(e) {
            if (quill.root.innerHTML !== lastSavedContent || (titleInput && titleInput.value !== lastSavedTitle)) {
                e.preventDefault();
                e.returnValue = '';
            }
        });
    }

    // ==================== IMAGE UPLOAD ====================

    async function uploadImage(file) {
        if (!file.type.startsWith('image/')) {
            alert('Please select an image file');
            return;
        }

        if (file.size > 5 * 1024 * 1024) {
            alert('Image must be less than 5MB');
            return;
        }

        const formData = new FormData();
        formData.append('image', file);

        const range = quill.getSelection(true);
        quill.insertText(range.index, 'Uploading image...', { color: '#999' });

        try {
            const response = await fetch(uploadUrl, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken },
                body: formData
            });

            const result = await response.json();
            quill.deleteText(range.index, 'Uploading image...'.length);

            if (result.success) {
                quill.insertEmbed(range.index, 'image', result.url);
                quill.setSelection(range.index + 1);
            } else {
                alert('Failed to upload image');
            }
        } catch (error) {
            quill.deleteText(range.index, 'Uploading image...'.length);
            console.error('Upload error:', error);
            alert('Failed to upload image');
        }
    }

    // Drag and drop
    if (canEdit) {
        editorEl.addEventListener('dragover', e => e.preventDefault());
        editorEl.addEventListener('drop', async function(e) {
            e.preventDefault();
            const files = e.dataTransfer.files;
            for (const file of files) {
                if (file.type.startsWith('image/')) {
                    await uploadImage(file);
                }
            }
        });
    }

    // ==================== COMMENTS SIDEBAR ====================

    function toggleCommentsSidebar() {
        commentsSidebar.classList.toggle('hidden-sidebar');
        commentsSidebar.classList.toggle('show-mobile');
    }

    toggleCommentsBtn?.addEventListener('click', toggleCommentsSidebar);
    closeCommentsBtn?.addEventListener('click', toggleCommentsSidebar);

    // Comment filters
    document.querySelectorAll('.comment-filter-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.comment-filter-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            const filter = this.dataset.filter;
            document.querySelectorAll('.comment-card').forEach(card => {
                const isResolved = card.classList.contains('resolved');
                if (filter === 'all') {
                    card.style.display = '';
                } else if (filter === 'open') {
                    card.style.display = isResolved ? 'none' : '';
                } else if (filter === 'resolved') {
                    card.style.display = isResolved ? '' : 'none';
                }
            });
        });
    });

    // ==================== TEXT SELECTION & COMMENTS ====================

    let selectionTimeout = null;

    document.addEventListener('mouseup', function(e) {
        if (!e.target.closest('.ql-editor')) {
            hideCommentTooltip();
            return;
        }

        clearTimeout(selectionTimeout);
        selectionTimeout = setTimeout(() => {
            const selection = window.getSelection();
            const text = selection.toString().trim();

            if (text.length > 0 && text.length < 500) {
                const range = selection.getRangeAt(0);
                const rect = range.getBoundingClientRect();

                // Position tooltip above selection
                addCommentTooltip.style.left = (rect.left + rect.width / 2 - 50) + 'px';
                addCommentTooltip.style.top = (rect.top - 45 + window.scrollY) + 'px';
                addCommentTooltip.classList.remove('hidden');

                // Store selection data
                currentSelection = {
                    text: text,
                    start: getTextOffset(quill.root, range.startContainer, range.startOffset),
                    end: getTextOffset(quill.root, range.endContainer, range.endOffset),
                    id: 'comment-' + Date.now()
                };
            } else {
                hideCommentTooltip();
            }
        }, 200);
    });

    function hideCommentTooltip() {
        addCommentTooltip.classList.add('hidden');
    }

    function getTextOffset(root, node, offset) {
        let totalOffset = 0;
        const walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT, null, false);

        while (walker.nextNode()) {
            if (walker.currentNode === node) {
                return totalOffset + offset;
            }
            totalOffset += walker.currentNode.textContent.length;
        }
        return totalOffset;
    }

    // Show comment form
    addCommentBtn?.addEventListener('click', function() {
        if (!currentSelection) return;

        document.getElementById('selection-start').value = currentSelection.start;
        document.getElementById('selection-end').value = currentSelection.end;
        document.getElementById('selection-text').value = currentSelection.text;
        document.getElementById('selection-id').value = currentSelection.id;
        document.getElementById('selected-text-preview').textContent =
            currentSelection.text.length > 50 ? currentSelection.text.substring(0, 50) + '...' : currentSelection.text;
        document.getElementById('comment-content').value = '';

        newCommentForm.classList.remove('hidden');
        document.getElementById('comment-content').focus();
        hideCommentTooltip();

        // Open sidebar if closed
        if (commentsSidebar.classList.contains('hidden-sidebar')) {
            toggleCommentsSidebar();
        }
    });

    cancelCommentBtn?.addEventListener('click', function() {
        newCommentForm.classList.add('hidden');
        currentSelection = null;
    });

    // Submit comment
    commentForm?.addEventListener('submit', async function(e) {
        e.preventDefault();

        const content = document.getElementById('comment-content').value.trim();
        if (!content) return;

        const data = {
            content: content,
            selection_start: document.getElementById('selection-start').value,
            selection_end: document.getElementById('selection-end').value,
            selection_text: document.getElementById('selection-text').value,
            selection_id: document.getElementById('selection-id').value
        };

        try {
            const response = await fetch('{{ route('documents.comments.store', $document->uuid) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success) {
                // Add comment to list
                const noComments = document.getElementById('no-comments');
                if (noComments) noComments.remove();

                commentsList.insertAdjacentHTML('afterbegin', result.html);
                newCommentForm.classList.add('hidden');
                currentSelection = null;

                // Update comment count badge
                updateCommentBadge();
            }
        } catch (error) {
            console.error('Failed to post comment:', error);
        }
    });

    function updateCommentBadge() {
        const openCount = document.querySelectorAll('.comment-card:not(.resolved)').length;
        const badge = toggleCommentsBtn.querySelector('.badge');

        if (openCount > 0) {
            if (badge) {
                badge.textContent = openCount;
            } else {
                toggleCommentsBtn.insertAdjacentHTML('beforeend',
                    `<span class="badge badge-primary badge-xs absolute -top-1 -right-1">${openCount}</span>`);
            }
        } else if (badge) {
            badge.remove();
        }
    }

    // ==================== COMMENT ACTIONS ====================

    // Resolve/unresolve comment
    commentsList?.addEventListener('click', async function(e) {
        const resolveBtn = e.target.closest('[data-action="resolve"]');
        const unresolveBtn = e.target.closest('[data-action="unresolve"]');
        const deleteBtn = e.target.closest('[data-action="delete"]');
        const replyBtn = e.target.closest('[data-action="reply"]');

        if (resolveBtn) {
            const commentId = resolveBtn.dataset.commentId;
            await toggleResolve(commentId, true);
        }

        if (unresolveBtn) {
            const commentId = unresolveBtn.dataset.commentId;
            await toggleResolve(commentId, false);
        }

        if (deleteBtn) {
            const commentId = deleteBtn.dataset.commentId;
            if (confirm('Delete this comment?')) {
                await deleteComment(commentId);
            }
        }

        if (replyBtn) {
            const card = replyBtn.closest('.comment-card');
            const replyForm = card.querySelector('.reply-form');
            replyForm.classList.toggle('hidden');
            if (!replyForm.classList.contains('hidden')) {
                replyForm.querySelector('textarea').focus();
            }
        }

        // Cancel reply button
        const cancelReplyBtn = e.target.closest('.cancel-reply');
        if (cancelReplyBtn) {
            const form = cancelReplyBtn.closest('.reply-form');
            form.classList.add('hidden');
            form.querySelector('textarea').value = '';
        }
    });

    async function toggleResolve(commentId, resolve) {
        const url = resolve
            ? `/document-comments/${commentId}/resolve`
            : `/document-comments/${commentId}/unresolve`;

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            });

            if (response.ok) {
                const card = document.querySelector(`.comment-card[data-comment-id="${commentId}"]`);
                card.classList.toggle('resolved', resolve);

                // Update buttons
                const resolveBtn = card.querySelector('[data-action="resolve"]');
                const unresolveBtn = card.querySelector('[data-action="unresolve"]');

                if (resolve) {
                    resolveBtn?.classList.add('hidden');
                    unresolveBtn?.classList.remove('hidden');
                } else {
                    resolveBtn?.classList.remove('hidden');
                    unresolveBtn?.classList.add('hidden');
                }

                updateCommentBadge();
            }
        } catch (error) {
            console.error('Failed to toggle resolve:', error);
        }
    }

    async function deleteComment(commentId) {
        try {
            const response = await fetch(`/document-comments/${commentId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            });

            if (response.ok) {
                const card = document.querySelector(`.comment-card[data-comment-id="${commentId}"]`);
                card.remove();
                updateCommentBadge();

                if (commentsList.querySelectorAll('.comment-card').length === 0) {
                    commentsList.innerHTML = `
                        <div id="no-comments" class="text-center py-8 text-base-content/50">
                            <span class="icon-[tabler--message-off] size-12 block mx-auto mb-3 opacity-50"></span>
                            <p class="font-medium">No comments yet</p>
                            <p class="text-sm">Select text in the document to add a comment</p>
                        </div>
                    `;
                }
            }
        } catch (error) {
            console.error('Failed to delete comment:', error);
        }
    }

    // Submit reply
    commentsList?.addEventListener('submit', async function(e) {
        if (!e.target.classList.contains('reply-form')) return;
        e.preventDefault();

        const form = e.target;
        const commentId = form.dataset.commentId;
        const textarea = form.querySelector('textarea');
        const content = textarea.value.trim();

        if (!content) return;

        try {
            const response = await fetch(`/document-comments/${commentId}/replies`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ content: content })
            });

            const result = await response.json();

            if (result.success) {
                const repliesContainer = form.closest('.comment-card').querySelector('.replies-container');
                repliesContainer.insertAdjacentHTML('beforeend', result.html);
                textarea.value = '';
                form.classList.add('hidden');
            }
        } catch (error) {
            console.error('Failed to post reply:', error);
        }
    });

    // Click on comment card to highlight in document
    commentsList?.addEventListener('click', function(e) {
        const card = e.target.closest('.comment-card');
        if (!card || e.target.closest('button') || e.target.closest('form')) return;

        document.querySelectorAll('.comment-card').forEach(c => c.classList.remove('active'));
        card.classList.add('active');
    });
});
</script>
@endpush
@endsection
