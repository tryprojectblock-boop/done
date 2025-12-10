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
                    <button type="button" id="toggle-sections-btn" class="btn btn-ghost btn-sm gap-1" title="Toggle Sections">
                        <span class="icon-[tabler--list] size-4"></span>
                        <span class="hidden sm:inline text-xs">Sections</span>
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
        <!-- Left Sidebar: Document Sections/Pages -->
        <div id="pages-sidebar" class="w-56 bg-base-100 border-r border-base-300 h-full overflow-hidden flex flex-col transition-all duration-300">
            <!-- Sidebar Header -->
            <div class="p-3 border-b border-base-300">
                <div class="flex items-center justify-between">
                    <h3 class="font-semibold text-sm flex items-center gap-2">
                        <span class="icon-[tabler--list] size-4"></span>
                        Sections
                    </h3>
                    <div class="flex items-center gap-1">
                        @if($canEdit)
                        <button type="button" id="add-section-btn" class="btn btn-ghost btn-xs btn-circle" title="Add Section">
                            <span class="icon-[tabler--plus] size-4"></span>
                        </button>
                        @endif
                        <button type="button" id="toggle-pages-sidebar" class="btn btn-ghost btn-xs btn-circle" title="Hide Sidebar">
                            <span class="icon-[tabler--layout-sidebar-left-collapse] size-4"></span>
                        </button>
                    </div>
                </div>
            </div>
            <!-- Sections/Pages List -->
            <div id="sections-list" class="flex-1 overflow-y-auto py-2">
                <!-- Sections will be dynamically generated -->
                <div class="px-3 py-4 text-center text-base-content/50 text-sm" id="no-sections">
                    <span class="icon-[tabler--file-text] size-8 block mx-auto mb-2 opacity-50"></span>
                    <p>No sections yet</p>
                    <p class="text-xs mt-1">Add headings to create sections</p>
                </div>
            </div>
            <!-- Current Position Indicator -->
            <div id="current-position" class="p-3 border-t border-base-300 bg-base-200/50">
                <div class="flex items-center justify-between text-xs mb-2">
                    <span class="flex items-center gap-1 text-base-content/60">
                        <span class="icon-[tabler--file-text] size-4"></span>
                        Page
                    </span>
                    <span class="font-bold text-primary" id="current-page-num">1</span>
                </div>
                <div class="flex items-center gap-2 text-xs text-base-content/60 mb-2">
                    <span class="icon-[tabler--map-pin] size-3"></span>
                    <span class="truncate"><strong id="current-section-name" class="text-base-content">Top</strong></span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="flex-1 bg-base-300 rounded-full h-1.5">
                        <div id="scroll-progress" class="bg-primary rounded-full h-1.5 transition-all" style="width: 0%"></div>
                    </div>
                    <span id="scroll-percent" class="text-xs text-base-content/50">0%</span>
                </div>
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
                     data-upload-url="{{ route('upload.image') }}"
                     data-csrf="{{ csrf_token() }}"
                     data-initial-content="{{ json_encode($document->content ?? '') }}">
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

    // ==================== SECTIONS/PAGES SIDEBAR ELEMENTS ====================
    const pagesSidebar = document.getElementById('pages-sidebar');
    const togglePagesSidebarBtn = document.getElementById('toggle-pages-sidebar');
    const showPagesSidebarBtn = document.getElementById('show-pages-sidebar');
    const sectionsList = document.getElementById('sections-list');
    const noSections = document.getElementById('no-sections');
    const currentSectionName = document.getElementById('current-section-name');
    const scrollProgress = document.getElementById('scroll-progress');
    const scrollPercent = document.getElementById('scroll-percent');
    const addSectionBtn = document.getElementById('add-section-btn');
    const toggleSectionsBtn = document.getElementById('toggle-sections-btn');
    const currentPageNum = document.getElementById('current-page-num');

    // Page height constant (A4 at 96dpi = 1056px, minus padding)
    const PAGE_HEIGHT = 936; // 1056 - 120 (top+bottom padding)

    let sections = [];
    let activeSection = null;

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

    // ==================== SECTIONS FUNCTIONALITY (after Quill init) ====================

    // Extract sections from headings in document
    function extractSections() {
        if (!quill || !quill.root) return;

        const editor = quill.root;
        const headings = editor.querySelectorAll('h1, h2, h3');
        sections = [];

        headings.forEach((heading, index) => {
            const text = heading.textContent.trim();
            if (text) {
                // Add unique ID if not present
                if (!heading.id) {
                    heading.id = `section-${index}`;
                }
                sections.push({
                    id: heading.id,
                    text: text,
                    level: heading.tagName.toLowerCase(),
                    element: heading,
                    top: heading.offsetTop
                });
            }
        });

        renderSections();
    }

    // Render sections in sidebar
    function renderSections() {
        if (!sectionsList || !noSections) return;

        if (sections.length === 0) {
            noSections.classList.remove('hidden');
            sectionsList.innerHTML = '';
            sectionsList.appendChild(noSections);
            return;
        }

        noSections.classList.add('hidden');
        sectionsList.innerHTML = '';

        sections.forEach((section, index) => {
            const item = document.createElement('div');
            item.className = `section-item ${section.level}`;
            item.dataset.sectionId = section.id;

            const icon = section.level === 'h1' ? 'file-text' :
                        section.level === 'h2' ? 'hash' : 'point';

            item.innerHTML = `
                <span class="section-icon icon-[tabler--${icon}] size-4"></span>
                <span class="section-title" title="${section.text}">${section.text}</span>
                <span class="section-page">${index + 1}</span>
            `;

            item.addEventListener('click', () => scrollToSection(section.id));
            sectionsList.appendChild(item);
        });
    }

    // Scroll to section
    function scrollToSection(sectionId) {
        const section = sections.find(s => s.id === sectionId);
        if (section && section.element) {
            section.element.scrollIntoView({ behavior: 'smooth', block: 'start' });
            updateActiveSection(sectionId);
        }
    }

    // Update active section highlight
    function updateActiveSection(sectionId) {
        document.querySelectorAll('.section-item').forEach(item => {
            item.classList.remove('active');
            if (item.dataset.sectionId === sectionId) {
                item.classList.add('active');
            }
        });

        const section = sections.find(s => s.id === sectionId);
        if (section && currentSectionName) {
            currentSectionName.textContent = section.text;
            activeSection = sectionId;
        }
    }

    // Track scroll position and update active section
    function handleScroll() {
        if (!editorContainer) return;

        const scrollTop = editorContainer.scrollTop;
        const scrollHeight = editorContainer.scrollHeight - editorContainer.clientHeight;
        const percent = scrollHeight > 0 ? Math.round((scrollTop / scrollHeight) * 100) : 0;

        if (scrollProgress) scrollProgress.style.width = `${percent}%`;
        if (scrollPercent) scrollPercent.textContent = `${percent}%`;

        // Calculate current page based on scroll position
        const currentPage = Math.max(1, Math.ceil((scrollTop + 100) / PAGE_HEIGHT));
        if (currentPageNum) currentPageNum.textContent = currentPage;

        // Find current section based on scroll position
        let currentSection = null;
        for (let i = sections.length - 1; i >= 0; i--) {
            const section = sections[i];
            if (section.element) {
                const rect = section.element.getBoundingClientRect();
                const containerRect = editorContainer.getBoundingClientRect();
                if (rect.top <= containerRect.top + 100) {
                    currentSection = section;
                    break;
                }
            }
        }

        if (currentSection && currentSection.id !== activeSection) {
            updateActiveSection(currentSection.id);
        } else if (!currentSection && sections.length > 0) {
            // At the top, before first section
            if (currentSectionName) currentSectionName.textContent = 'Top';
            document.querySelectorAll('.section-item').forEach(item => item.classList.remove('active'));
            activeSection = null;
        }
    }

    // Debounce scroll handler
    let scrollTimeout;
    editorContainer?.addEventListener('scroll', function() {
        clearTimeout(scrollTimeout);
        scrollTimeout = setTimeout(handleScroll, 50);
    });

    // Add section button - inserts a heading at cursor
    addSectionBtn?.addEventListener('click', function() {
        const range = quill.getSelection();
        if (range) {
            quill.insertText(range.index, '\n');
            quill.formatLine(range.index + 1, 1, 'header', 2);
            quill.setSelection(range.index + 1, 0);
            quill.focus();

            // Re-extract sections after a short delay
            setTimeout(extractSections, 100);
        }
    });

    // Re-extract sections when content changes
    quill.on('text-change', function() {
        clearTimeout(window.sectionExtractTimeout);
        window.sectionExtractTimeout = setTimeout(extractSections, 500);
    });

    // Initial extraction after content loads
    setTimeout(() => {
        extractSections();
        handleScroll();
    }, 200);

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

@endsection
