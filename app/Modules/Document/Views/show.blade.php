@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-base-200/50">
    <!-- Document Header -->
    <div class="bg-base-100 border-b border-base-300 sticky top-0 z-20">
        <div class="max-w-full mx-auto px-4 py-3">
            <div class="flex items-center justify-between gap-4">
                <!-- Left: Back, Sections Toggle & Save Status -->
                <div class="flex items-center gap-2">
                    <a href="{{ route('documents.index') }}" class="btn btn-ghost btn-sm btn-circle" title="Back to Documents">
                        <span class="icon-[tabler--arrow-left] size-5"></span>
                    </a>
                    <button type="button" id="toggle-sections-btn" class="btn btn-ghost btn-sm gap-1" title="Toggle Pages">
                        <span class="icon-[tabler--files] size-4"></span>
                        <span class="hidden sm:inline text-xs">Pages</span>
                    </button>
                    <span id="save-status" class="text-sm text-base-content/50 flex items-center gap-1">
                        <span class="icon-[tabler--check] size-4"></span>
                        Saved
                    </span>
                </div>

                <!-- Right: Actions -->
                <div class="flex items-center gap-2">
                    <!-- Collaborators -->
                    <button type="button" id="collaborators-btn" class="btn btn-soft btn-secondary btn-sm gap-1">
                        <div class="avatar-group -space-x-3">
                            <!-- Creator -->
                            <div class="avatar" title="{{ $document->creator->name }} (Owner)">
                                <div class="w-6 rounded-full ring ring-primary ring-offset-base-100 ring-offset-1">
                                    <img src="{{ $document->creator->avatar_url }}" alt="{{ $document->creator->name }}" />
                                </div>
                            </div>
                            @foreach($document->collaborators->take(3) as $collaborator)
                                <div class="avatar" title="{{ $collaborator->name }}">
                                    <div class="w-6 rounded-full">
                                        <img src="{{ $collaborator->avatar_url }}" alt="{{ $collaborator->name }}" />
                                    </div>
                                </div>
                            @endforeach
                            @if($document->collaborators->count() > 3)
                                <div class="avatar placeholder">
                                    <div class="w-6 rounded-full bg-neutral text-neutral-content text-xs">
                                        <span>+{{ $document->collaborators->count() - 3 }}</span>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <span class="icon-[tabler--chevron-down] size-4 text-base-content/60"></span>
                    </button>

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
        <!-- Left Sidebar: Document Pages/Tabs -->
        <div id="pages-sidebar" class="w-56 bg-base-100 border-r border-base-300 h-full overflow-hidden flex flex-col transition-all duration-300">
            <!-- Sidebar Header -->
            <div class="p-3 border-b border-base-300">
                <div class="flex items-center justify-between">
                    <h3 class="font-semibold text-sm flex items-center gap-2">
                        <span class="icon-[tabler--files] size-4"></span>
                        Pages
                    </h3>
                    <div class="flex items-center gap-1">
                        @if($canEdit)
                        <button type="button" id="add-page-btn" class="btn btn-ghost btn-xs btn-circle" title="Add Page">
                            <span class="icon-[tabler--plus] size-4"></span>
                        </button>
                        @endif
                        <button type="button" id="toggle-pages-sidebar" class="btn btn-ghost btn-xs btn-circle" title="Hide Sidebar">
                            <span class="icon-[tabler--layout-sidebar-left-collapse] size-4"></span>
                        </button>
                    </div>
                </div>
            </div>
            <!-- Pages List -->
            <div id="pages-list" class="flex-1 overflow-y-auto py-2">
                <!-- Pages will be dynamically generated -->
                <div class="px-3 py-4 text-center text-base-content/50 text-sm" id="no-pages">
                    <span class="icon-[tabler--file-text] size-8 block mx-auto mb-2 opacity-50"></span>
                    <p>Loading pages...</p>
                </div>
            </div>
            <!-- Current Page Info -->
            <div id="current-page-info" class="p-3 border-t border-base-300 bg-base-200/50">
                <div class="flex items-center justify-between text-xs mb-1">
                    <span class="text-base-content/60">Current Page</span>
                    <span class="font-bold text-primary" id="current-page-number">1 / 1</span>
                </div>
                <div class="text-xs text-base-content/60 truncate" id="current-page-title">-</div>
            </div>
        </div>

        <!-- Collapsed Sidebar Button -->
        <button type="button" id="show-pages-sidebar" class="hidden h-full w-10 bg-base-100 border-r border-base-300 flex-col items-center pt-3 hover:bg-base-200 transition-colors">
            <span class="icon-[tabler--layout-sidebar-left-expand] size-5 text-base-content/60"></span>
        </button>

        <!-- Editor Area -->
        <div id="editor-container" class="flex-1 overflow-y-auto transition-all duration-300 bg-base-200">
            <!-- Add Comment Tooltip (appears on text selection) -->
            <div id="add-comment-tooltip" class="fixed z-50 hidden">
                <button type="button" id="add-comment-btn" class="btn btn-primary btn-sm shadow-lg">
                    <span class="icon-[tabler--message-plus] size-4"></span>
                    Comment
                </button>
            </div>

            <!-- Document Title Bar -->
            <div class="bg-base-100 border-b border-base-300 px-6 py-3 sticky top-0 z-10">
                <input type="text"
                       id="document-title"
                       value="{{ $document->title }}"
                       placeholder="Untitled Document"
                       class="w-full text-xl font-bold bg-transparent border-0 outline-none focus:ring-0 placeholder:text-base-content/30 {{ !$canEdit ? 'pointer-events-none' : '' }}"
                       {{ !$canEdit ? 'readonly' : '' }}>
                @if($document->description)
                    <p class="text-sm text-base-content/60 mt-1 italic">{{ $document->description }}</p>
                @endif
            </div>

            <!-- Document Editor - Page Style -->
            <div id="document-editor-wrapper" class="p-6">
                <div id="document-editor"
                     class="document-editor bg-base-100 shadow-xl rounded-lg overflow-hidden"
                     data-document-uuid="{{ $document->uuid }}"
                     data-can-edit="{{ $canEdit ? 'true' : 'false' }}"
                     data-content-url="{{ route('api.documents.content.get', $document->uuid) }}"
                     data-save-url="{{ route('api.documents.content.save', $document->uuid) }}"
                     data-autosave-url="{{ route('api.documents.content.autosave', $document->uuid) }}"
                     data-pages-url="{{ route('api.documents.pages.index', $document->uuid) }}"
                     data-upload-url="{{ route('upload.image') }}"
                     data-csrf="{{ csrf_token() }}"
                     data-initial-content="{{ json_encode($document->content ?? '') }}"
                     data-has-pages="{{ $document->pages->count() > 0 ? 'true' : 'false' }}">
                </div>
            </div>

            <!-- Last Edited Info -->
            <div class="text-xs text-base-content/50 text-center pb-6">
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
/* Document Editor Styles - Page-based layout */
#document-editor-wrapper {
    min-height: calc(100vh - 200px);
}

.document-editor {
    min-height: 250px;
    max-width: 900px;
    margin: 0 auto;
}

.document-editor .ql-toolbar {
    border: none !important;
    border-bottom: 1px solid oklch(var(--bc) / 0.1) !important;
    background: oklch(var(--b1));
    padding: 10px 16px;
    position: sticky;
    top: 0;
    z-index: 10;
}

.document-editor .ql-container {
    border: none !important;
    font-family: 'Georgia', 'Times New Roman', serif;
    font-size: 1rem;
    line-height: 1.8;
    background: oklch(var(--b1));
}

/* Page-style editor */
.document-editor .ql-editor {
    padding: 72px 90px;
    min-height: 1123px; /* A4 height */
    background: oklch(var(--b1));
}

.document-editor .ql-editor.ql-blank::before {
    color: oklch(var(--bc) / 0.3);
    font-style: normal;
    left: 90px;
}

/* Page break visual indicator */
.document-editor .ql-editor hr {
    border: none;
    border-top: 1px dashed oklch(var(--bc) / 0.3);
    margin: 60px -90px;
    position: relative;
}

.document-editor .ql-editor hr::after {
    content: 'Page Break';
    position: absolute;
    top: -10px;
    left: 50%;
    transform: translateX(-50%);
    background: oklch(var(--b1));
    padding: 0 12px;
    font-size: 0.7rem;
    color: oklch(var(--bc) / 0.4);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Headings styling */
.document-editor .ql-editor h1 {
    font-size: 2rem;
    font-weight: 700;
    margin-top: 1.5em;
    margin-bottom: 0.5em;
    color: oklch(var(--bc));
}

.document-editor .ql-editor h2 {
    font-size: 1.5rem;
    font-weight: 600;
    margin-top: 1.25em;
    margin-bottom: 0.5em;
    color: oklch(var(--bc) / 0.9);
}

.document-editor .ql-editor h3 {
    font-size: 1.25rem;
    font-weight: 600;
    margin-top: 1em;
    margin-bottom: 0.5em;
    color: oklch(var(--bc) / 0.85);
}

.document-editor .ql-editor p {
    margin-bottom: 1em;
}

.document-editor .ql-editor blockquote {
    border-left: 4px solid oklch(var(--p));
    padding-left: 1em;
    margin: 1.5em 0;
    color: oklch(var(--bc) / 0.8);
    font-style: italic;
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

    #pages-sidebar {
        position: fixed;
        left: 0;
        top: 57px;
        height: calc(100vh - 57px);
        z-index: 30;
        transform: translateX(-100%);
    }

    #pages-sidebar.show-mobile {
        transform: translateX(0);
    }
}

/* Pages/Sections sidebar */
#pages-sidebar.hidden-sidebar {
    width: 0;
    padding: 0;
    overflow: hidden;
    border: none;
}

#show-pages-sidebar.visible {
    display: flex !important;
}

/* Section item */
.section-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 0.75rem;
    margin: 0 0.5rem;
    border-radius: 0.5rem;
    cursor: pointer;
    transition: all 0.2s;
    border-left: 2px solid transparent;
}

.section-item:hover {
    background: oklch(var(--b2));
}

.section-item.active {
    background: oklch(var(--p) / 0.1);
    border-left-color: oklch(var(--p));
}

.section-item.active .section-title {
    color: oklch(var(--p));
    font-weight: 600;
}

.section-item.h1 {
    padding-left: 0.75rem;
}

.section-item.h2 {
    padding-left: 1.25rem;
}

.section-item.h3 {
    padding-left: 1.75rem;
}

.section-icon {
    flex-shrink: 0;
    opacity: 0.5;
}

.section-item.active .section-icon {
    opacity: 1;
    color: oklch(var(--p));
}

.section-title {
    flex: 1;
    font-size: 0.8125rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.section-page {
    font-size: 0.625rem;
    padding: 0.125rem 0.375rem;
    background: oklch(var(--b3));
    border-radius: 0.25rem;
    color: oklch(var(--bc) / 0.6);
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const editorEl = document.getElementById('document-editor');
    if (!editorEl) {
        console.error('Document editor element not found');
        return;
    }

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

    // ==================== PAGES SIDEBAR ELEMENTS ====================
    const pagesSidebar = document.getElementById('pages-sidebar');
    const togglePagesSidebarBtn = document.getElementById('toggle-pages-sidebar');
    const showPagesSidebarBtn = document.getElementById('show-pages-sidebar');
    const pagesList = document.getElementById('pages-list');
    const noPages = document.getElementById('no-pages');
    const addPageBtn = document.getElementById('add-page-btn');
    const toggleSectionsBtn = document.getElementById('toggle-sections-btn');
    const currentPageNumber = document.getElementById('current-page-number');
    const currentPageTitle = document.getElementById('current-page-title');
    const pagesUrl = editorEl.dataset.pagesUrl;
    const hasPages = editorEl.dataset.hasPages === 'true';

    // Pages state
    let pages = [];
    let currentPage = null;
    let pendingPageSave = false;

    // Toggle pages sidebar functions
    function hideSidebar() {
        pagesSidebar?.classList.add('hidden-sidebar');
        showPagesSidebarBtn?.classList.add('visible');
    }

    function showSidebar() {
        pagesSidebar?.classList.remove('hidden-sidebar');
        showPagesSidebarBtn?.classList.remove('visible');
    }

    togglePagesSidebarBtn?.addEventListener('click', hideSidebar);
    showPagesSidebarBtn?.addEventListener('click', showSidebar);

    // Header toggle button
    toggleSectionsBtn?.addEventListener('click', function() {
        if (pagesSidebar?.classList.contains('hidden-sidebar')) {
            showSidebar();
        } else {
            hideSidebar();
        }
    });

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
    let modules = {};
    if (canEdit) {
        modules.toolbar = {
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
        };
    } else {
        modules.toolbar = false;
    }

    // Initialize Quill
    const quill = new Quill(editorEl, {
        theme: 'snow',
        placeholder: canEdit ? 'Start writing your document...' : '',
        readOnly: !canEdit,
        modules: modules
    });

    // Make quill available globally for debugging
    window.quill = quill;

    // Add read-only class if needed
    if (!canEdit) {
        editorEl.classList.add('read-only');
    }

    // Set initial content
    if (initialContent) {
        quill.root.innerHTML = initialContent;
    }

    // ==================== PAGES FUNCTIONALITY (after Quill init) ====================

    // Load pages from server
    async function loadPages() {
        try {
            const response = await fetch(pagesUrl, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            });
            const result = await response.json();

            if (result.success) {
                pages = result.pages;

                // If no pages exist but document has content, migrate to pages
                if (pages.length === 0 && initialContent) {
                    // Create default page with existing content
                    await createPage('Page 1', initialContent);
                } else if (pages.length === 0) {
                    // Create empty first page
                    await createPage('Page 1', '');
                } else {
                    renderPages();
                    // Load first page content
                    if (pages.length > 0) {
                        await switchToPage(pages[0].uuid);
                    }
                }
            }
        } catch (error) {
            console.error('Failed to load pages:', error);
            noPages.innerHTML = `
                <span class="icon-[tabler--alert-circle] size-8 block mx-auto mb-2 text-error"></span>
                <p class="text-error">Failed to load pages</p>
            `;
        }
    }

    // Render pages list in sidebar
    function renderPages() {
        if (!pagesList) return;

        pagesList.innerHTML = '';

        if (pages.length === 0) {
            pagesList.appendChild(noPages);
            return;
        }

        pages.forEach((page, index) => {
            const item = document.createElement('div');
            item.className = 'page-item';
            item.dataset.pageUuid = page.uuid;

            if (currentPage && currentPage.uuid === page.uuid) {
                item.classList.add('active');
            }

            item.innerHTML = `
                <span class="page-icon icon-[tabler--file-text] size-4"></span>
                <span class="page-title" title="${page.title}">${page.title}</span>
                <span class="page-number">${index + 1}</span>
                ${canEdit ? `<button type="button" class="page-edit-btn btn btn-ghost btn-xs btn-circle opacity-0 group-hover:opacity-100" title="Rename page">
                    <span class="icon-[tabler--edit] size-3"></span>
                </button>` : ''}
            `;

            // Add hover class for showing edit button
            item.classList.add('group');

            item.addEventListener('click', (e) => {
                // Don't switch page if clicking the edit button
                if (e.target.closest('.page-edit-btn')) return;
                switchToPage(page.uuid);
            });

            // Edit button click handler
            const editBtn = item.querySelector('.page-edit-btn');
            if (editBtn) {
                editBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    openRenamePageModal(page);
                });
            }

            // Context menu for rename/delete (right-click)
            if (canEdit) {
                item.addEventListener('contextmenu', (e) => {
                    e.preventDefault();
                    showPageContextMenu(e, page);
                });
            }

            // Double-click to rename
            if (canEdit) {
                item.addEventListener('dblclick', (e) => {
                    e.preventDefault();
                    openRenamePageModal(page);
                });
            }

            pagesList.appendChild(item);
        });

        updatePageInfo();
    }

    // Switch to a different page
    async function switchToPage(pageUuid) {
        // Save current page content first
        if (currentPage && pendingPageSave) {
            await savePageContent();
        }

        try {
            const response = await fetch(`${pagesUrl}/${pageUuid}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            });
            const result = await response.json();

            if (result.success) {
                currentPage = result.page;
                quill.root.innerHTML = currentPage.content || '';
                lastSavedContent = currentPage.content || '';

                // Update UI
                renderPages();
                updatePageInfo();
                updateSaveStatus('saved');
            }
        } catch (error) {
            console.error('Failed to load page:', error);
        }
    }

    // Update page info display
    function updatePageInfo() {
        if (currentPageNumber && pages.length > 0) {
            const pageIndex = pages.findIndex(p => currentPage && p.uuid === currentPage.uuid);
            currentPageNumber.textContent = `${pageIndex + 1} / ${pages.length}`;
        }
        if (currentPageTitle && currentPage) {
            currentPageTitle.textContent = currentPage.title;
        }
    }

    // Create a new page
    async function createPage(title, content = '') {
        try {
            const response = await fetch(pagesUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ title, content })
            });
            const result = await response.json();

            if (result.success) {
                pages.push(result.page);
                renderPages();
                await switchToPage(result.page.uuid);
                return result.page;
            }
        } catch (error) {
            console.error('Failed to create page:', error);
        }
        return null;
    }

    // Save current page content
    async function savePageContent(createVersion = false) {
        if (!currentPage || !canEdit) return;

        const content = quill.root.innerHTML;

        if (content === lastSavedContent && !createVersion) {
            pendingPageSave = false;
            return;
        }

        updateSaveStatus('saving');

        try {
            const response = await fetch(`${pagesUrl}/${currentPage.uuid}/content`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ content })
            });
            const result = await response.json();

            if (result.success) {
                lastSavedContent = content;
                currentPage.content = content;
                pendingPageSave = false;
                updateSaveStatus('saved');
            } else {
                updateSaveStatus('error');
            }
        } catch (error) {
            console.error('Failed to save page:', error);
            updateSaveStatus('error');
        }
    }

    // Delete a page
    async function deletePage(pageUuid) {
        if (pages.length <= 1) {
            alert('Cannot delete the last page.');
            return;
        }

        if (!confirm('Are you sure you want to delete this page?')) return;

        try {
            const response = await fetch(`${pagesUrl}/${pageUuid}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            });
            const result = await response.json();

            if (result.success) {
                const deletedIndex = pages.findIndex(p => p.uuid === pageUuid);
                pages = pages.filter(p => p.uuid !== pageUuid);

                // Switch to adjacent page
                if (currentPage && currentPage.uuid === pageUuid) {
                    const newIndex = Math.min(deletedIndex, pages.length - 1);
                    await switchToPage(pages[newIndex].uuid);
                } else {
                    renderPages();
                }
            }
        } catch (error) {
            console.error('Failed to delete page:', error);
        }
    }

    // Rename a page
    async function renamePage(pageUuid, newTitle) {
        try {
            console.log('Renaming page:', pageUuid, 'to:', newTitle);
            const response = await fetch(`${pagesUrl}/${pageUuid}`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ title: newTitle })
            });

            if (!response.ok) {
                const errorText = await response.text();
                console.error('Rename failed:', response.status, errorText);
                alert('Failed to rename page. Please try again.');
                return false;
            }

            const result = await response.json();

            if (result.success) {
                const page = pages.find(p => p.uuid === pageUuid);
                if (page) {
                    page.title = newTitle;
                }
                if (currentPage && currentPage.uuid === pageUuid) {
                    currentPage.title = newTitle;
                }
                renderPages();
                console.log('Page renamed successfully');
                return true;
            } else {
                console.error('Rename failed:', result.error || 'Unknown error');
                alert(result.error || 'Failed to rename page.');
                return false;
            }
        } catch (error) {
            console.error('Failed to rename page:', error);
            alert('Failed to rename page. Please check your connection and try again.');
            return false;
        }
    }

    // Show context menu for page actions
    function showPageContextMenu(e, page) {
        // Remove existing context menu
        document.querySelector('.page-context-menu')?.remove();

        const menu = document.createElement('div');
        menu.className = 'page-context-menu dropdown-menu show';
        menu.style.cssText = `position: fixed; left: ${e.clientX}px; top: ${e.clientY}px; z-index: 9999;`;

        menu.innerHTML = `
            <button class="dropdown-item" data-action="rename">
                <span class="icon-[tabler--edit] size-4"></span>
                Rename
            </button>
            <button class="dropdown-item text-error" data-action="delete" ${pages.length <= 1 ? 'disabled' : ''}>
                <span class="icon-[tabler--trash] size-4"></span>
                Delete
            </button>
        `;

        menu.querySelector('[data-action="rename"]').addEventListener('click', () => {
            menu.remove();
            openRenamePageModal(page);
        });

        menu.querySelector('[data-action="delete"]').addEventListener('click', () => {
            menu.remove();
            deletePage(page.uuid);
        });

        document.body.appendChild(menu);

        // Close on click outside
        setTimeout(() => {
            document.addEventListener('click', function closeMenu() {
                menu.remove();
                document.removeEventListener('click', closeMenu);
            }, { once: true });
        }, 0);
    }

    // Add page button - opens modal
    const addPageModal = document.getElementById('add-page-modal');
    const addPageForm = document.getElementById('add-page-form');
    const pageTitleInput = document.getElementById('page-title-input');

    window.openAddPageModal = function() {
        if (pageTitleInput) pageTitleInput.value = '';
        addPageModal?.classList.add('open');
        setTimeout(() => pageTitleInput?.focus(), 100);
    };

    window.closeAddPageModal = function() {
        addPageModal?.classList.remove('open');
    };

    addPageBtn?.addEventListener('click', openAddPageModal);

    addPageForm?.addEventListener('submit', async function(e) {
        e.preventDefault();
        const title = pageTitleInput.value.trim();
        if (!title) return;

        await createPage(title);
        closeAddPageModal();
    });

    // Rename page modal
    const renamePageModal = document.getElementById('rename-page-modal');
    const renamePageForm = document.getElementById('rename-page-form');
    const renamePageInput = document.getElementById('rename-page-input');
    let renamePageUuid = null;

    window.openRenamePageModal = function(page) {
        renamePageUuid = page.uuid;
        if (renamePageInput) renamePageInput.value = page.title;
        renamePageModal?.classList.add('open');
        setTimeout(() => renamePageInput?.focus(), 100);
    };

    window.closeRenamePageModal = function() {
        renamePageModal?.classList.remove('open');
        renamePageUuid = null;
    };

    renamePageForm?.addEventListener('submit', async function(e) {
        e.preventDefault();
        const newTitle = renamePageInput.value.trim();
        if (!newTitle || !renamePageUuid) return;

        await renamePage(renamePageUuid, newTitle);
        closeRenamePageModal();
    });

    // Track content changes for auto-save
    quill.on('text-change', function() {
        pendingPageSave = true;
        updateSaveStatus('saving');
        clearTimeout(autoSaveTimer);
        autoSaveTimer = setTimeout(() => savePageContent(false), 2000);
    });

    // Initialize pages
    loadPages();

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

    // Manual save button - saves current page
    if (canEdit && saveBtn) {
        saveBtn.addEventListener('click', () => savePageContent(true));
    }

    // Save before leaving
    if (canEdit) {
        window.addEventListener('beforeunload', function(e) {
            if (pendingPageSave) {
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

    // ==================== COLLABORATOR MANAGEMENT ====================

    // Update collaborator role
    document.querySelectorAll('.collaborator-role-select').forEach(select => {
        select.addEventListener('change', async function() {
            const userId = this.dataset.userId;
            const role = this.value;

            try {
                const response = await fetch(`/api/documents/${documentUuid}/collaborators/${userId}`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ role: role })
                });

                const result = await response.json();

                if (!result.success) {
                    alert(result.error || 'Failed to update role');
                    location.reload();
                }
            } catch (error) {
                console.error('Failed to update role:', error);
                alert('Failed to update role');
                location.reload();
            }
        });
    });

    // Remove collaborator
    document.querySelectorAll('.remove-collaborator-btn').forEach(btn => {
        btn.addEventListener('click', async function() {
            const userId = this.dataset.userId;
            const row = this.closest('.collaborator-row');
            const userName = row.querySelector('.text-sm.font-medium').textContent;

            if (!confirm(`Remove ${userName} from this document?`)) return;

            try {
                const response = await fetch(`/api/documents/${documentUuid}/collaborators/${userId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                });

                const result = await response.json();

                if (result.success) {
                    row.remove();
                    // Update avatar group
                    location.reload();
                } else {
                    alert(result.error || 'Failed to remove collaborator');
                }
            } catch (error) {
                console.error('Failed to remove collaborator:', error);
                alert('Failed to remove collaborator');
            }
        });
    });

    // Collaborators modal
    const collaboratorsBtn = document.getElementById('collaborators-btn');
    const collaboratorsModal = document.getElementById('collaborators-modal');
    const inviteModal = document.getElementById('invite-modal');

    window.openCollaboratorsModal = function() {
        collaboratorsModal?.classList.add('open');
    };

    window.closeCollaboratorsModal = function() {
        collaboratorsModal?.classList.remove('open');
    };

    window.openInviteModal = function() {
        closeCollaboratorsModal();
        inviteModal?.classList.add('open');
    };

    window.closeInviteModal = function() {
        inviteModal?.classList.remove('open');
    };

    collaboratorsBtn?.addEventListener('click', openCollaboratorsModal);

    // Invite button click
    document.getElementById('invite-collaborator-btn')?.addEventListener('click', openInviteModal);

    // Close modals on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeCollaboratorsModal();
            closeInviteModal();
            if (typeof closeAddPageModal === 'function') closeAddPageModal();
            if (typeof closeRenamePageModal === 'function') closeRenamePageModal();
        }
    });

    // Invite form submission
    const inviteForm = document.getElementById('invite-collaborator-form');
    inviteForm?.addEventListener('submit', async function(e) {
        e.preventDefault();

        const userId = document.getElementById('invite-user-select').value;
        const role = document.getElementById('invite-role-select').value;

        if (!userId) {
            alert('Please select a team member');
            return;
        }

        try {
            const response = await fetch(`/api/documents/${documentUuid}/collaborators`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ user_id: userId, role: role })
            });

            const result = await response.json();

            if (result.success) {
                closeInviteModal();
                location.reload();
            } else {
                alert(result.error || 'Failed to invite collaborator');
            }
        } catch (error) {
            console.error('Failed to invite collaborator:', error);
            alert('Failed to invite collaborator');
        }
    });
});
</script>
@endpush

<!-- Collaborators Popup Modal -->
<div id="collaborators-modal" class="custom-modal">
    <div class="custom-modal-backdrop" onclick="closeCollaboratorsModal()"></div>
    <div class="custom-modal-box bg-base-100 rounded-xl shadow-2xl w-full max-w-md mx-4">
        <div class="p-4 border-b border-base-200">
            <div class="flex items-center justify-between">
                <h3 class="font-bold text-lg flex items-center gap-2">
                    <span class="icon-[tabler--users] size-5 text-primary"></span>
                    Collaborators
                </h3>
                <div class="flex items-center gap-2">
                    @if($document->canInvite(auth()->user()))
                        <button type="button" id="invite-collaborator-btn" class="btn btn-primary btn-sm">
                            <span class="icon-[tabler--user-plus] size-4"></span>
                            Invite
                        </button>
                    @endif
                    <button type="button" class="btn btn-ghost btn-sm btn-circle" onclick="closeCollaboratorsModal()">
                        <span class="icon-[tabler--x] size-5"></span>
                    </button>
                </div>
            </div>
        </div>
        <div class="max-h-80 overflow-y-auto">
            <!-- Owner -->
            <div class="flex items-center justify-between p-4 hover:bg-base-200/50 border-b border-base-200">
                <div class="flex items-center gap-3">
                    <div class="avatar">
                        <div class="w-10 rounded-full">
                            <img src="{{ $document->creator->avatar_url }}" alt="{{ $document->creator->name }}" />
                        </div>
                    </div>
                    <div>
                        <p class="font-medium">{{ $document->creator->name }}</p>
                        <p class="text-sm text-base-content/60">{{ $document->creator->email }}</p>
                    </div>
                </div>
                <span class="badge badge-primary">Owner</span>
            </div>
            <!-- Collaborators -->
            @foreach($document->collaborators as $collaborator)
                <div class="flex items-center justify-between p-4 hover:bg-base-200/50 border-b border-base-200 last:border-b-0 collaborator-row" data-user-id="{{ $collaborator->id }}">
                    <div class="flex items-center gap-3">
                        <div class="avatar">
                            <div class="w-10 rounded-full">
                                <img src="{{ $collaborator->avatar_url }}" alt="{{ $collaborator->name }}" />
                            </div>
                        </div>
                        <div>
                            <p class="font-medium">{{ $collaborator->name }}</p>
                            <p class="text-sm text-base-content/60">{{ $collaborator->email }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        @if($document->canInvite(auth()->user()))
                            <select class="select select-bordered select-sm collaborator-role-select" data-user-id="{{ $collaborator->id }}">
                                <option value="editor" {{ $collaborator->pivot->role === 'editor' ? 'selected' : '' }}>Editor</option>
                                <option value="reader" {{ $collaborator->pivot->role === 'reader' ? 'selected' : '' }}>Reader</option>
                            </select>
                            <button type="button" class="btn btn-ghost btn-sm btn-circle text-error remove-collaborator-btn" data-user-id="{{ $collaborator->id }}" title="Remove">
                                <span class="icon-[tabler--x] size-5"></span>
                            </button>
                        @else
                            <span class="badge badge-ghost">{{ ucfirst($collaborator->pivot->role) }}</span>
                        @endif
                    </div>
                </div>
            @endforeach
            @if($document->collaborators->isEmpty())
                <div class="p-8 text-center text-base-content/50">
                    <span class="icon-[tabler--users-group] size-12 block mx-auto mb-2 opacity-50"></span>
                    <p class="font-medium">No collaborators yet</p>
                    <p class="text-sm">Invite team members to collaborate</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Invite Collaborator Popup Modal -->
@if($document->canInvite(auth()->user()))
<div id="invite-modal" class="custom-modal">
    <div class="custom-modal-backdrop" onclick="closeInviteModal()"></div>
    <div class="custom-modal-box bg-base-100 rounded-xl shadow-2xl w-full max-w-md mx-4">
        <div class="p-4 border-b border-base-200">
            <div class="flex items-center justify-between">
                <h3 class="font-bold text-lg flex items-center gap-2">
                    <span class="icon-[tabler--user-plus] size-5 text-primary"></span>
                    Invite Collaborator
                </h3>
                <button type="button" class="btn btn-ghost btn-sm btn-circle" onclick="closeInviteModal()">
                    <span class="icon-[tabler--x] size-5"></span>
                </button>
            </div>
        </div>
        <form id="invite-collaborator-form" class="p-4 space-y-4">
            <div class="form-control">
                <label class="label">
                    <span class="label-text font-medium">Team Member</span>
                </label>
                <select id="invite-user-select" class="select select-bordered w-full" required>
                    <option value="">Select a team member...</option>
                    @php
                        $currentCollaboratorIds = $document->collaborators->pluck('id')->toArray();
                        $availableMembers = \App\Models\User::where('company_id', auth()->user()->company_id)
                            ->where('id', '!=', auth()->id())
                            ->where('id', '!=', $document->created_by)
                            ->whereNotIn('id', $currentCollaboratorIds)
                            ->orderBy('name')
                            ->get();
                    @endphp
                    @foreach($availableMembers as $member)
                        <option value="{{ $member->id }}">{{ $member->name }} ({{ $member->email }})</option>
                    @endforeach
                </select>
                @if($availableMembers->isEmpty())
                    <p class="text-sm text-base-content/60 mt-2">All team members have already been added.</p>
                @endif
            </div>

            <div class="form-control">
                <label class="label">
                    <span class="label-text font-medium">Role</span>
                </label>
                <select id="invite-role-select" class="select select-bordered w-full" required>
                    <option value="reader">Reader - Can view and comment</option>
                    <option value="editor">Editor - Can edit the document</option>
                </select>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <button type="button" class="btn btn-ghost" onclick="closeInviteModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" {{ $availableMembers->isEmpty() ? 'disabled' : '' }}>
                    <span class="icon-[tabler--user-plus] size-4"></span>
                    Invite
                </button>
            </div>
        </form>
    </div>
</div>
@endif

<style>
.custom-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 9999;
    justify-content: center;
    align-items: center;
}
.custom-modal.open {
    display: flex !important;
}
.custom-modal-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1;
}
.custom-modal-box {
    position: relative;
    z-index: 2;
    animation: modalSlideIn 0.2s ease-out;
}
@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: scale(0.95) translateY(-10px);
    }
    to {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}
</style>

<!-- Add Page Modal -->
@if($canEdit)
<div id="add-page-modal" class="custom-modal">
    <div class="custom-modal-backdrop" onclick="closeAddPageModal()"></div>
    <div class="custom-modal-box bg-base-100 rounded-xl shadow-2xl w-full max-w-sm mx-4">
        <div class="p-4 border-b border-base-200">
            <div class="flex items-center justify-between">
                <h3 class="font-bold text-lg flex items-center gap-2">
                    <span class="icon-[tabler--file-plus] size-5 text-primary"></span>
                    Add Page
                </h3>
                <button type="button" class="btn btn-ghost btn-sm btn-circle" onclick="closeAddPageModal()">
                    <span class="icon-[tabler--x] size-5"></span>
                </button>
            </div>
        </div>
        <form id="add-page-form" class="p-4 space-y-4">
            <div class="form-control">
                <label class="label">
                    <span class="label-text font-medium">Page Title</span>
                </label>
                <input type="text" id="page-title-input" class="input input-bordered w-full" placeholder="e.g., Introduction, Guide, Appendix..." required autofocus>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <button type="button" class="btn btn-ghost" onclick="closeAddPageModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <span class="icon-[tabler--plus] size-4"></span>
                    Add Page
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Rename Page Modal -->
<div id="rename-page-modal" class="custom-modal">
    <div class="custom-modal-backdrop" onclick="closeRenamePageModal()"></div>
    <div class="custom-modal-box bg-base-100 rounded-xl shadow-2xl w-full max-w-sm mx-4">
        <div class="p-4 border-b border-base-200">
            <div class="flex items-center justify-between">
                <h3 class="font-bold text-lg flex items-center gap-2">
                    <span class="icon-[tabler--edit] size-5 text-primary"></span>
                    Rename Page
                </h3>
                <button type="button" class="btn btn-ghost btn-sm btn-circle" onclick="closeRenamePageModal()">
                    <span class="icon-[tabler--x] size-5"></span>
                </button>
            </div>
        </div>
        <form id="rename-page-form" class="p-4 space-y-4">
            <div class="form-control">
                <label class="label">
                    <span class="label-text font-medium">Page Title</span>
                </label>
                <input type="text" id="rename-page-input" class="input input-bordered w-full" placeholder="Enter new page title..." required autofocus>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <button type="button" class="btn btn-ghost" onclick="closeRenamePageModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <span class="icon-[tabler--check] size-4"></span>
                    Rename
                </button>
            </div>
        </form>
    </div>
</div>
@endif

<style>
/* Page item styles */
.page-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    margin: 2px 8px;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.15s ease;
    font-size: 0.875rem;
}

.page-item:hover {
    background: oklch(var(--b2));
}

.page-item.active {
    background: oklch(var(--p) / 0.15);
    color: oklch(var(--p));
    font-weight: 500;
}

.page-item.active .page-icon {
    color: oklch(var(--p));
}

.page-icon {
    flex-shrink: 0;
    color: oklch(var(--bc) / 0.5);
}

.page-title {
    flex: 1;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.page-number {
    flex-shrink: 0;
    font-size: 0.75rem;
    color: oklch(var(--bc) / 0.4);
    font-weight: 500;
    margin-right: 4px;
}

/* Page edit button - visible on hover */
.page-edit-btn {
    flex-shrink: 0;
    opacity: 0;
    transition: opacity 0.15s ease;
}

.page-item:hover .page-edit-btn,
.page-item.active .page-edit-btn {
    opacity: 1;
}

/* Context menu styles */
.page-context-menu {
    background: oklch(var(--b1));
    border: 1px solid oklch(var(--bc) / 0.1);
    border-radius: 8px;
    padding: 4px;
    min-width: 140px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
}

.page-context-menu .dropdown-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 0.875rem;
}

.page-context-menu .dropdown-item:hover {
    background: oklch(var(--b2));
}

.page-context-menu .dropdown-item:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.page-context-menu .dropdown-item.text-error:hover {
    background: oklch(var(--er) / 0.1);
}
</style>

@endsection
