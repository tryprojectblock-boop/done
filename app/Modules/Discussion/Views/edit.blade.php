@extends('layouts.app')

@section('content')
<div class="p-4 md:p-6">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center gap-2 text-sm text-base-content/60 mb-2">
                <a href="{{ route('dashboard') }}" class="hover:text-primary">Dashboard</a>
                <span class="icon-[tabler--chevron-right] size-4"></span>
                <a href="{{ route('discussions.index') }}" class="hover:text-primary">Discussions</a>
                <span class="icon-[tabler--chevron-right] size-4"></span>
                <a href="{{ route('discussions.show', $discussion->uuid) }}" class="hover:text-primary">{{ Str::limit($discussion->title, 20) }}</a>
                <span class="icon-[tabler--chevron-right] size-4"></span>
                <span>Edit</span>
            </div>
            <h1 class="text-2xl font-bold text-base-content">Edit Discussion</h1>
        </div>

        <!-- Error Messages -->
        @if($errors->any())
            <div class="alert alert-error mb-4">
                <span class="icon-[tabler--alert-circle] size-5"></span>
                <div>
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <form action="{{ route('discussions.update', $discussion->uuid) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Card 1: Basic Info -->
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <h2 class="card-title text-lg mb-4">
                        <span class="icon-[tabler--message-circle] size-5"></span>
                        Discussion Details
                    </h2>

                    <div class="space-y-4">
                        <!-- Discussion Title -->
                        <div class="form-control">
                            <label class="label" for="discussion-title">
                                <span class="label-text font-medium">Discussion Title <span class="text-error">*</span></span>
                            </label>
                            <input type="text" name="title" id="discussion-title" value="{{ old('title', $discussion->title) }}"
                                   class="input input-bordered w-full" placeholder="Enter a clear title for your discussion" required>
                        </div>

                        <!-- Discussion Type & Workspace in same row -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Discussion Type -->
                            <div class="form-control">
                                <label class="label" for="discussion-type">
                                    <span class="label-text font-medium">Discussion Type <span class="text-base-content/50 font-normal">(Optional)</span></span>
                                </label>
                                <select name="type" id="discussion-type" data-select='{
                                    "placeholder": "Select a type...",
                                    "hasSearch": true,
                                    "toggleTag": "<button type=\"button\" aria-expanded=\"false\"></button>",
                                    "toggleClasses": "advance-select-toggle",
                                    "dropdownClasses": "advance-select-menu max-h-52 overflow-y-auto",
                                    "optionClasses": "advance-select-option selected:active",
                                    "optionTemplate": "<div class=\"flex justify-between items-center w-full\"><span data-title></span><span class=\"icon-[tabler--check] shrink-0 size-4 text-primary hidden selected:block\"></span></div>",
                                    "extraMarkup": "<span class=\"icon-[tabler--caret-up-down] shrink-0 size-4 text-base-content/90 absolute top-1/2 end-3 -translate-y-1/2\"></span>"
                                }' class="hidden">
                                    <option value="">Select a type</option>
                                    @foreach($types as $value => $label)
                                        <option value="{{ $value }}" {{ old('type', $discussion->type?->value) === $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Workspace -->
                            <div class="form-control">
                                <label class="label" for="discussion-workspace">
                                    <span class="label-text font-medium">Workspace <span class="text-base-content/50 font-normal">(Optional)</span></span>
                                </label>
                                <select name="workspace_id" id="discussion-workspace" data-select='{
                                    "placeholder": "Select a workspace...",
                                    "hasSearch": true,
                                    "toggleTag": "<button type=\"button\" aria-expanded=\"false\"></button>",
                                    "toggleClasses": "advance-select-toggle",
                                    "dropdownClasses": "advance-select-menu max-h-52 overflow-y-auto",
                                    "optionClasses": "advance-select-option selected:active",
                                    "optionTemplate": "<div class=\"flex justify-between items-center w-full\"><span data-title></span><span class=\"icon-[tabler--check] shrink-0 size-4 text-primary hidden selected:block\"></span></div>",
                                    "extraMarkup": "<span class=\"icon-[tabler--caret-up-down] shrink-0 size-4 text-base-content/90 absolute top-1/2 end-3 -translate-y-1/2\"></span>"
                                }' class="hidden">
                                    <option value="">No Workspace (General)</option>
                                    @foreach($workspaces as $workspace)
                                        <option value="{{ $workspace->id }}" {{ old('workspace_id', $discussion->workspace_id) == $workspace->id ? 'selected' : '' }}>
                                            {{ $workspace->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Details -->
                        <x-quill-editor
                            name="details"
                            id="discussion-details"
                            label="Details"
                            :value="old('details', $discussion->details)"
                            placeholder="Add details to your discussion..."
                            height="200px"
                        />

                        <!-- Existing Attachments -->
                        @if($discussion->attachments->isNotEmpty())
                            <div class="form-control">
                                <div class="label">
                                    <span class="label-text font-medium">Current Attachments</span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    @foreach($discussion->attachments as $attachment)
                                        <div class="flex items-center gap-3 p-3 rounded-lg border border-base-300">
                                            <span class="icon-[{{ $attachment->icon }}] size-6 text-base-content/50"></span>
                                            <div class="flex-1 min-w-0">
                                                <p class="font-medium text-sm truncate">{{ $attachment->original_filename }}</p>
                                                <p class="text-xs text-base-content/50">{{ $attachment->human_size }}</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- New File Attachments - Drag & Drop -->
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Add More Attachments <span class="text-base-content/50 font-normal">(Optional)</span></span>
                            </label>
                            <div id="attachment-dropzone" class="border-2 border-dashed border-base-300 rounded-xl p-6 text-center cursor-pointer transition-all duration-200 hover:border-primary hover:bg-primary/5">
                                <input type="file" name="attachments[]" id="discussion-attachments" multiple
                                       class="hidden"
                                       accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.gif,.zip,.rar">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="w-16 h-16 rounded-full bg-primary/10 flex items-center justify-center">
                                        <span class="icon-[tabler--cloud-upload] size-8 text-primary"></span>
                                    </div>
                                    <div>
                                        <p class="text-base font-medium text-base-content">Drag & drop files here</p>
                                        <p class="text-sm text-base-content/60">or <span class="text-primary font-medium">browse</span> to choose files</p>
                                    </div>
                                    <div class="flex flex-wrap justify-center gap-2 mt-2">
                                        <span class="badge badge-ghost badge-sm">PDF</span>
                                        <span class="badge badge-ghost badge-sm">DOC</span>
                                        <span class="badge badge-ghost badge-sm">XLS</span>
                                        <span class="badge badge-ghost badge-sm">PPT</span>
                                        <span class="badge badge-ghost badge-sm">Images</span>
                                        <span class="badge badge-ghost badge-sm">ZIP</span>
                                    </div>
                                    <p class="text-xs text-base-content/50">Max 10MB per file</p>
                                </div>
                            </div>
                            <!-- File Preview List -->
                            <div id="attachment-preview" class="mt-3 space-y-2 hidden"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 2: Participants -->
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <h2 class="card-title text-lg mb-4">
                        <span class="icon-[tabler--users] size-5"></span>
                        Participants
                    </h2>

                    <div class="space-y-4">
                        <!-- Invite Team Members -->
                        <div class="form-control">
                            <label class="label" for="member-search">
                                <span class="label-text font-medium">Team Members</span>
                            </label>
                            <div class="relative">
                                <div id="member-select" class="min-h-12 p-2 border border-base-300 rounded-lg cursor-pointer flex flex-wrap gap-2 items-center">
                                    <div id="selected-members" class="flex flex-wrap gap-2"></div>
                                    <input type="text" id="member-search" class="flex-1 min-w-32 bg-transparent border-0 outline-none text-sm" placeholder="Search and select members..." autocomplete="off">
                                </div>
                                <div id="member-dropdown" class="absolute z-50 w-full mt-1 bg-base-100 border border-base-300 rounded-lg shadow-lg max-h-60 overflow-y-auto hidden">
                                    <!-- Select All Option -->
                                    <label id="select-all-option" class="flex items-center gap-3 p-3 hover:bg-base-200 cursor-pointer transition-colors border-b border-base-300 sticky top-0 bg-base-100 z-10">
                                        <input type="checkbox" id="select-all-members" class="checkbox checkbox-primary checkbox-sm">
                                        <span class="font-medium text-sm">Select All</span>
                                    </label>
                                    @foreach($members as $member)
                                        @php
                                            $memberWorkspaceIds = $member->workspaces->pluck('id')->toArray();
                                        @endphp
                                        <div class="member-option flex items-center gap-3 p-3 hover:bg-base-200 cursor-pointer transition-colors"
                                             data-id="{{ $member->id }}"
                                             data-name="{{ $member->name }}"
                                             data-search="{{ strtolower($member->name) }}"
                                             data-workspaces="{{ implode(',', $memberWorkspaceIds) }}">
                                            <div class="avatar">
                                                <div class="w-8 rounded-full">
                                                    <img src="{{ $member->avatar_url }}" alt="{{ $member->name }}" />
                                                </div>
                                            </div>
                                            <div class="flex-1">
                                                <p class="font-medium text-sm">{{ $member->name }}</p>
                                                <p class="text-xs text-base-content/50">{{ $member->email ?? '' }}</p>
                                            </div>
                                            <span class="member-check icon-[tabler--check] size-5 text-primary hidden"></span>
                                        </div>
                                    @endforeach
                                    <div id="no-member-results" class="p-3 text-center text-base-content/50 text-sm hidden">No members found</div>
                                </div>
                            </div>
                            <div id="member-hidden-inputs"></div>
                        </div>

                        <!-- Invite Guests -->
                        <div class="form-control">
                            <label class="label" for="guest-search">
                                <span class="label-text font-medium">Guests</span>
                            </label>
                            <div class="relative">
                                <div id="guest-select" class="min-h-12 p-2 border border-base-300 rounded-lg cursor-pointer flex flex-wrap gap-2 items-center">
                                    <div id="selected-guests" class="flex flex-wrap gap-2"></div>
                                    <input type="text" id="guest-search" class="flex-1 min-w-32 bg-transparent border-0 outline-none text-sm" placeholder="Search and select guests..." autocomplete="off">
                                </div>
                                <div id="guest-dropdown" class="absolute z-50 w-full mt-1 bg-base-100 border border-base-300 rounded-lg shadow-lg max-h-60 overflow-y-auto hidden">
                                    @forelse($guests as $guest)
                                        <div class="guest-option flex items-center gap-3 p-3 hover:bg-base-200 cursor-pointer transition-colors" data-id="{{ $guest->id }}" data-name="{{ $guest->name }}" data-search="{{ strtolower($guest->name) }}">
                                            <div class="avatar">
                                                <div class="w-8 rounded-full">
                                                    <img src="{{ $guest->avatar_url }}" alt="{{ $guest->name }}" />
                                                </div>
                                            </div>
                                            <div class="flex-1">
                                                <p class="font-medium text-sm">{{ $guest->name }}</p>
                                                <p class="text-xs text-base-content/50">{{ $guest->email ?? '' }}</p>
                                            </div>
                                            <span class="badge badge-ghost badge-sm">Guest</span>
                                            <span class="guest-check icon-[tabler--check] size-5 text-secondary hidden"></span>
                                        </div>
                                    @empty
                                        <div class="p-3 text-center text-base-content/50 text-sm">No guests available</div>
                                    @endforelse
                                    <div id="no-guest-results" class="p-3 text-center text-base-content/50 text-sm hidden">No guests found</div>
                                </div>
                            </div>
                            <div id="guest-hidden-inputs"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <div class="flex flex-col sm:flex-row gap-3 justify-start">
                        <button type="submit" class="btn btn-primary">
                            <span class="icon-[tabler--check] size-5"></span>
                            Save Changes
                        </button>
                        <a href="{{ route('discussions.show', $discussion->uuid) }}" class="btn btn-ghost">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
.workspace-hidden {
    display: none !important;
}
</style>

@php
    // Get current participant IDs (excluding creator)
    $currentMemberIds = $discussion->participants->where('role', '!=', 'guest')->pluck('id')->toArray();
    $currentGuestIds = $discussion->participants->where('role', 'guest')->pluck('id')->toArray();

    // Prepare preselected data for JavaScript
    $preselectedMembersData = $discussion->participants
        ->where('role', '!=', 'guest')
        ->where('id', '!=', $discussion->created_by)
        ->map(function($m) {
            return ['id' => (string)$m->id, 'name' => $m->name];
        })
        ->values();

    $preselectedGuestsData = $discussion->participants
        ->where('role', 'guest')
        ->map(function($g) {
            return ['id' => (string)$g->id, 'name' => $g->name];
        })
        ->values();
@endphp

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Pre-selected members and guests from the discussion
    const preselectedMembers = @json($preselectedMembersData);
    const preselectedGuests = @json($preselectedGuestsData);

    // ==================== DRAG & DROP ATTACHMENTS ====================
    const dropzone = document.getElementById('attachment-dropzone');
    const fileInput = document.getElementById('discussion-attachments');
    const previewContainer = document.getElementById('attachment-preview');
    let selectedFiles = new DataTransfer();

    function getFileIcon(filename) {
        const ext = filename.split('.').pop().toLowerCase();
        const icons = {
            'pdf': 'icon-[tabler--file-type-pdf]',
            'doc': 'icon-[tabler--file-type-doc]',
            'docx': 'icon-[tabler--file-type-docx]',
            'xls': 'icon-[tabler--file-type-xls]',
            'xlsx': 'icon-[tabler--file-type-xls]',
            'ppt': 'icon-[tabler--file-type-ppt]',
            'pptx': 'icon-[tabler--file-type-ppt]',
            'zip': 'icon-[tabler--file-type-zip]',
            'rar': 'icon-[tabler--file-type-zip]',
            'jpg': 'icon-[tabler--photo]',
            'jpeg': 'icon-[tabler--photo]',
            'png': 'icon-[tabler--photo]',
            'gif': 'icon-[tabler--photo]'
        };
        return icons[ext] || 'icon-[tabler--file]';
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    function updatePreviews() {
        if (selectedFiles.files.length === 0) {
            previewContainer.classList.add('hidden');
            previewContainer.innerHTML = '';
            return;
        }

        previewContainer.classList.remove('hidden');
        previewContainer.innerHTML = '';

        Array.from(selectedFiles.files).forEach((file, index) => {
            const isImage = file.type.startsWith('image/');
            const preview = document.createElement('div');
            preview.className = 'flex items-center gap-3 p-3 bg-base-200/50 rounded-lg border border-base-300';

            let thumbnailHtml = '';
            if (isImage) {
                const url = URL.createObjectURL(file);
                thumbnailHtml = `<img src="${url}" class="w-12 h-12 object-cover rounded-lg" alt="${file.name}">`;
            } else {
                thumbnailHtml = `<div class="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center"><span class="${getFileIcon(file.name)} size-6 text-primary"></span></div>`;
            }

            preview.innerHTML = `
                ${thumbnailHtml}
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium truncate">${file.name}</p>
                    <p class="text-xs text-base-content/60">${formatFileSize(file.size)}</p>
                </div>
                <button type="button" class="btn btn-ghost btn-sm btn-circle text-error" data-index="${index}">
                    <span class="icon-[tabler--x] size-5"></span>
                </button>
            `;

            preview.querySelector('button').addEventListener('click', function() {
                removeFile(index);
            });

            previewContainer.appendChild(preview);
        });
    }

    function removeFile(index) {
        const newFiles = new DataTransfer();
        Array.from(selectedFiles.files).forEach((file, i) => {
            if (i !== index) newFiles.items.add(file);
        });
        selectedFiles = newFiles;
        fileInput.files = selectedFiles.files;
        updatePreviews();
    }

    function addFiles(files) {
        Array.from(files).forEach(file => {
            if (file.size <= 10 * 1024 * 1024) {
                selectedFiles.items.add(file);
            }
        });
        fileInput.files = selectedFiles.files;
        updatePreviews();
    }

    dropzone.addEventListener('click', () => fileInput.click());

    dropzone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropzone.classList.add('border-primary', 'bg-primary/10');
    });

    dropzone.addEventListener('dragleave', (e) => {
        e.preventDefault();
        dropzone.classList.remove('border-primary', 'bg-primary/10');
    });

    dropzone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropzone.classList.remove('border-primary', 'bg-primary/10');
        addFiles(e.dataTransfer.files);
    });

    fileInput.addEventListener('change', (e) => {
        addFiles(e.target.files);
    });

    // ==================== MEMBERS ====================
    const memberSelect = document.getElementById('member-select');
    const memberDropdown = document.getElementById('member-dropdown');
    const memberSearch = document.getElementById('member-search');
    const selectedMembersContainer = document.getElementById('selected-members');
    const memberHiddenInputs = document.getElementById('member-hidden-inputs');
    const memberOptions = document.querySelectorAll('.member-option');
    const noMemberResults = document.getElementById('no-member-results');
    const selectAllCheckbox = document.getElementById('select-all-members');
    const workspaceSelect = document.getElementById('discussion-workspace');
    let selectedMembers = [...preselectedMembers];
    let memberHighlightIndex = -1;
    let currentWorkspaceId = workspaceSelect ? workspaceSelect.value : '';

    // Filter members based on selected workspace
    function filterMembersByWorkspace(workspaceId) {
        currentWorkspaceId = workspaceId;
        let visibleCount = 0;

        memberOptions.forEach(option => {
            const memberWorkspaces = option.dataset.workspaces ? option.dataset.workspaces.split(',') : [];

            // If no workspace selected, show all members
            // If workspace selected, show only members in that workspace
            if (!workspaceId || workspaceId === '' || memberWorkspaces.includes(workspaceId)) {
                option.classList.remove('workspace-hidden');
                visibleCount++;
            } else {
                option.classList.add('workspace-hidden');
                // Remove from selection if hidden
                const memberId = option.dataset.id;
                const index = selectedMembers.findIndex(m => m.id === memberId);
                if (index > -1) {
                    selectedMembers.splice(index, 1);
                    option.querySelector('.member-check').classList.add('hidden');
                    option.classList.remove('bg-primary/10');
                }
            }
        });

        updateSelectedMembers();
        updateSelectAllCheckbox();

        // Show no results if all members are hidden
        if (visibleCount === 0) {
            noMemberResults.textContent = 'No members in this workspace';
            noMemberResults.classList.remove('hidden');
        } else {
            noMemberResults.textContent = 'No members found';
            noMemberResults.classList.add('hidden');
        }
    }

    // Listen for workspace changes
    if (workspaceSelect) {
        // Handle both native select and custom select (FlyonUI)
        workspaceSelect.addEventListener('change', function() {
            filterMembersByWorkspace(this.value);
        });

        // For FlyonUI advance-select, also observe for value changes
        const observer = new MutationObserver(function() {
            filterMembersByWorkspace(workspaceSelect.value);
        });
        observer.observe(workspaceSelect, { attributes: true, childList: true, subtree: true });

        // Apply initial filter based on current workspace selection
        if (workspaceSelect.value) {
            filterMembersByWorkspace(workspaceSelect.value);
        }
    }

    // Get members visible in current workspace filter
    function getWorkspaceVisibleMembers() {
        return Array.from(memberOptions).filter(opt => !opt.classList.contains('workspace-hidden'));
    }

    // Select All checkbox state update function
    function updateSelectAllCheckbox() {
        if (selectAllCheckbox) {
            const visibleMembers = getWorkspaceVisibleMembers();
            const totalMembers = visibleMembers.length;
            const selectedCount = selectedMembers.filter(m =>
                visibleMembers.some(opt => opt.dataset.id === m.id)
            ).length;
            selectAllCheckbox.checked = selectedCount === totalMembers && totalMembers > 0;
            selectAllCheckbox.indeterminate = selectedCount > 0 && selectedCount < totalMembers;
        }
    }

    // Select All checkbox click handler
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const visibleMembers = getWorkspaceVisibleMembers();
            if (this.checked) {
                // Select all visible members (respecting workspace filter)
                visibleMembers.forEach(option => {
                    const id = option.dataset.id;
                    const name = option.dataset.name;
                    if (!selectedMembers.find(m => m.id === id)) {
                        selectedMembers.push({ id, name });
                    }
                    option.querySelector('.member-check').classList.remove('hidden');
                    option.classList.add('bg-primary/10');
                });
            } else {
                // Deselect all visible members
                visibleMembers.forEach(option => {
                    const id = option.dataset.id;
                    const index = selectedMembers.findIndex(m => m.id === id);
                    if (index > -1) selectedMembers.splice(index, 1);
                    option.querySelector('.member-check').classList.add('hidden');
                    option.classList.remove('bg-primary/10');
                });
            }
            updateSelectedMembers();
        });
    }

    // Initialize pre-selected members
    selectedMembers.forEach(m => {
        const option = document.querySelector(`.member-option[data-id="${m.id}"]`);
        if (option) {
            option.querySelector('.member-check').classList.remove('hidden');
            option.classList.add('bg-primary/10');
        }
    });
    updateSelectedMembers();
    updateSelectAllCheckbox();

    function showMemberDropdown() { memberDropdown.classList.remove('hidden'); memberSelect.classList.add('ring-2', 'ring-primary', 'ring-offset-2'); }
    function hideMemberDropdown() { memberDropdown.classList.add('hidden'); memberSelect.classList.remove('ring-2', 'ring-primary', 'ring-offset-2'); }
    function getVisibleMemberOptions() { return Array.from(memberOptions).filter(opt => !opt.classList.contains('hidden') && !opt.classList.contains('workspace-hidden')); }
    function highlightMemberOption(index) {
        const visible = getVisibleMemberOptions();
        visible.forEach((opt, i) => opt.classList.toggle('bg-base-200', i === index));
        memberHighlightIndex = index;
        if (visible[index]) visible[index].scrollIntoView({ block: 'nearest' });
    }

    memberSelect.addEventListener('click', function(e) { if (e.target.closest('button')) return; if (memberDropdown.classList.contains('hidden')) showMemberDropdown(); memberSearch.focus(); });
    document.addEventListener('click', function(e) { if (!memberSelect.contains(e.target) && !memberDropdown.contains(e.target)) hideMemberDropdown(); });

    memberSearch.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        let visibleCount = 0;
        memberOptions.forEach(option => {
            const name = option.dataset.search;
            const isWorkspaceVisible = !option.classList.contains('workspace-hidden');
            if (name.includes(searchTerm) && isWorkspaceVisible) {
                option.classList.remove('hidden');
                visibleCount++;
            } else {
                option.classList.add('hidden');
            }
        });
        noMemberResults.classList.toggle('hidden', visibleCount > 0);
        if (memberDropdown.classList.contains('hidden')) showMemberDropdown();
        memberHighlightIndex = -1; highlightMemberOption(-1);
    });

    memberSearch.addEventListener('keydown', function(e) {
        const visible = getVisibleMemberOptions();
        if (visible.length === 0) return;
        if (e.key === 'ArrowDown') { e.preventDefault(); if (memberDropdown.classList.contains('hidden')) showMemberDropdown(); memberHighlightIndex = Math.min(memberHighlightIndex + 1, visible.length - 1); highlightMemberOption(memberHighlightIndex); }
        else if (e.key === 'ArrowUp') { e.preventDefault(); memberHighlightIndex = Math.max(memberHighlightIndex - 1, 0); highlightMemberOption(memberHighlightIndex); }
        else if (e.key === 'Enter') { e.preventDefault(); if (memberHighlightIndex >= 0 && visible[memberHighlightIndex]) { visible[memberHighlightIndex].click(); memberHighlightIndex = -1; highlightMemberOption(-1); } }
        else if (e.key === 'Escape') { hideMemberDropdown(); memberHighlightIndex = -1; }
    });

    memberOptions.forEach(option => {
        option.addEventListener('click', function(e) {
            e.stopPropagation();
            const id = this.dataset.id, name = this.dataset.name;
            const checkIcon = this.querySelector('.member-check');
            const index = selectedMembers.findIndex(m => m.id === id);
            if (index > -1) { selectedMembers.splice(index, 1); checkIcon.classList.add('hidden'); this.classList.remove('bg-primary/10'); }
            else { selectedMembers.push({ id, name }); checkIcon.classList.remove('hidden'); this.classList.add('bg-primary/10'); }
            updateSelectedMembers();
            updateSelectAllCheckbox();
            memberSearch.focus();
        });
    });

    function updateSelectedMembers() {
        selectedMembersContainer.innerHTML = selectedMembers.map(m => `<span class="badge badge-primary gap-1">${m.name}<button type="button" class="btn btn-ghost btn-xs btn-circle size-4" onclick="removeMember('${m.id}', event)"><span class="icon-[tabler--x] size-3"></span></button></span>`).join('');
        memberHiddenInputs.innerHTML = selectedMembers.map(m => `<input type="hidden" name="member_ids[]" value="${m.id}">`).join('');
        memberSearch.placeholder = selectedMembers.length > 0 ? 'Add more...' : 'Search and select members...';
    }

    window.removeMember = function(id, event) {
        event.stopPropagation();
        const index = selectedMembers.findIndex(m => m.id === id);
        if (index > -1) {
            selectedMembers.splice(index, 1);
            const option = document.querySelector(`.member-option[data-id="${id}"]`);
            if (option) { option.querySelector('.member-check').classList.add('hidden'); option.classList.remove('bg-primary/10'); }
            updateSelectedMembers();
            updateSelectAllCheckbox();
        }
    };

    // ==================== GUESTS ====================
    const guestSelect = document.getElementById('guest-select');
    const guestDropdown = document.getElementById('guest-dropdown');
    const guestSearch = document.getElementById('guest-search');
    const selectedGuestsContainer = document.getElementById('selected-guests');
    const guestHiddenInputs = document.getElementById('guest-hidden-inputs');
    const guestOptions = document.querySelectorAll('.guest-option');
    const noGuestResults = document.getElementById('no-guest-results');
    let selectedGuests = [...preselectedGuests];
    let guestHighlightIndex = -1;

    // Initialize pre-selected guests
    selectedGuests.forEach(g => {
        const option = document.querySelector(`.guest-option[data-id="${g.id}"]`);
        if (option) {
            option.querySelector('.guest-check').classList.remove('hidden');
            option.classList.add('bg-secondary/10');
        }
    });
    updateSelectedGuests();

    function showGuestDropdown() { guestDropdown.classList.remove('hidden'); guestSelect.classList.add('ring-2', 'ring-secondary', 'ring-offset-2'); }
    function hideGuestDropdown() { guestDropdown.classList.add('hidden'); guestSelect.classList.remove('ring-2', 'ring-secondary', 'ring-offset-2'); }
    function getVisibleGuestOptions() { return Array.from(guestOptions).filter(opt => !opt.classList.contains('hidden')); }
    function highlightGuestOption(index) {
        const visible = getVisibleGuestOptions();
        visible.forEach((opt, i) => opt.classList.toggle('bg-base-200', i === index));
        guestHighlightIndex = index;
        if (visible[index]) visible[index].scrollIntoView({ block: 'nearest' });
    }

    if (guestSelect) {
        guestSelect.addEventListener('click', function(e) { if (e.target.closest('button')) return; if (guestDropdown.classList.contains('hidden')) showGuestDropdown(); guestSearch.focus(); });
        document.addEventListener('click', function(e) { if (guestSelect && !guestSelect.contains(e.target) && guestDropdown && !guestDropdown.contains(e.target)) hideGuestDropdown(); });
    }

    if (guestSearch) {
        guestSearch.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            let visibleCount = 0;
            guestOptions.forEach(option => { const name = option.dataset.search; if (name.includes(searchTerm)) { option.classList.remove('hidden'); visibleCount++; } else { option.classList.add('hidden'); } });
            if (noGuestResults) noGuestResults.classList.toggle('hidden', visibleCount > 0);
            if (guestDropdown.classList.contains('hidden')) showGuestDropdown();
            guestHighlightIndex = -1; highlightGuestOption(-1);
        });

        guestSearch.addEventListener('keydown', function(e) {
            const visible = getVisibleGuestOptions();
            if (visible.length === 0) return;
            if (e.key === 'ArrowDown') { e.preventDefault(); if (guestDropdown.classList.contains('hidden')) showGuestDropdown(); guestHighlightIndex = Math.min(guestHighlightIndex + 1, visible.length - 1); highlightGuestOption(guestHighlightIndex); }
            else if (e.key === 'ArrowUp') { e.preventDefault(); guestHighlightIndex = Math.max(guestHighlightIndex - 1, 0); highlightGuestOption(guestHighlightIndex); }
            else if (e.key === 'Enter') { e.preventDefault(); if (guestHighlightIndex >= 0 && visible[guestHighlightIndex]) { visible[guestHighlightIndex].click(); guestHighlightIndex = -1; highlightGuestOption(-1); } }
            else if (e.key === 'Escape') { hideGuestDropdown(); guestHighlightIndex = -1; }
        });
    }

    guestOptions.forEach(option => {
        option.addEventListener('click', function(e) {
            e.stopPropagation();
            const id = this.dataset.id, name = this.dataset.name;
            const checkIcon = this.querySelector('.guest-check');
            const index = selectedGuests.findIndex(g => g.id === id);
            if (index > -1) { selectedGuests.splice(index, 1); checkIcon.classList.add('hidden'); this.classList.remove('bg-secondary/10'); }
            else { selectedGuests.push({ id, name }); checkIcon.classList.remove('hidden'); this.classList.add('bg-secondary/10'); }
            updateSelectedGuests();
            guestSearch.focus();
        });
    });

    function updateSelectedGuests() {
        selectedGuestsContainer.innerHTML = selectedGuests.map(g => `<span class="badge badge-secondary gap-1">${g.name}<button type="button" class="btn btn-ghost btn-xs btn-circle size-4" onclick="removeGuest('${g.id}', event)"><span class="icon-[tabler--x] size-3"></span></button></span>`).join('');
        guestHiddenInputs.innerHTML = selectedGuests.map(g => `<input type="hidden" name="guest_ids[]" value="${g.id}">`).join('');
        guestSearch.placeholder = selectedGuests.length > 0 ? 'Add more...' : 'Search and select guests...';
    }

    window.removeGuest = function(id, event) {
        event.stopPropagation();
        const index = selectedGuests.findIndex(g => g.id === id);
        if (index > -1) {
            selectedGuests.splice(index, 1);
            const option = document.querySelector(`.guest-option[data-id="${id}"]`);
            if (option) { option.querySelector('.guest-check').classList.add('hidden'); option.classList.remove('bg-secondary/10'); }
            updateSelectedGuests();
        }
    };
});
</script>
@endpush
@endsection
