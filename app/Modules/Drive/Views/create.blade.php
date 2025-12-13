@extends('layouts.app')

@section('content')
<div class="p-4 md:p-6">
    <div class="max-w-3xl mx-auto">
        <!-- Breadcrumb -->
        <div class="text-sm breadcrumbs mb-6">
            <ul>
                <li><a href="{{ route('drive.index') }}">Drive</a></li>
                <li>Upload File</li>
            </ul>
        </div>

        <!-- Storage Info -->
        <div class="alert alert-info mb-6">
            <span class="icon-[tabler--info-circle] size-5"></span>
            <div>
                <div class="font-medium">Storage Space</div>
                <div class="text-sm">
                    You have <strong>{{ number_format($storageRemaining / 1073741824, 2) }} GB</strong> remaining out of 10 GB.
                    @if($storageRemaining < 1073741824)
                        <span class="text-warning">Storage is running low!</span>
                    @endif
                </div>
            </div>
        </div>

        @if(session('error'))
            <div class="alert alert-error mb-6">
                <span class="icon-[tabler--alert-circle] size-5"></span>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <h2 class="card-title mb-6">
                    <span class="icon-[tabler--upload] size-6 text-primary"></span>
                    Upload New File
                </h2>

                <form action="{{ route('drive.store') }}" method="POST" enctype="multipart/form-data" id="upload-form">
                    @csrf

                    <!-- File Upload -->
                    <div class="form-control mb-6">
                        <label class="label" for="file-input">
                            <span class="label-text font-medium">File <span class="text-error">*</span></span>
                            <span class="label-text-alt">Max 500MB</span>
                        </label>
                        <div id="drop-zone" class="border-2 border-dashed border-base-300 rounded-lg p-8 text-center hover:border-primary transition-colors cursor-pointer">
                            <input type="file" name="file" id="file-input" class="hidden" required />
                            <div id="drop-zone-content">
                                <span class="icon-[tabler--cloud-upload] size-12 text-base-content/30 mx-auto mb-4"></span>
                                <p class="text-base-content/60 mb-2">Drag and drop your file here, or click to browse</p>
                                <p class="text-xs text-base-content/40">Supports all file types up to 500MB</p>
                            </div>
                            <div id="file-preview" class="hidden">
                                <div class="flex items-center justify-center gap-3">
                                    <span id="file-icon" class="icon-[tabler--file] size-10 text-primary"></span>
                                    <div class="text-left">
                                        <div id="file-name" class="font-medium"></div>
                                        <div id="file-size" class="text-sm text-base-content/60"></div>
                                    </div>
                                    <button type="button" onclick="clearFile()" class="btn btn-ghost btn-sm btn-circle">
                                        <span class="icon-[tabler--x] size-4"></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                        @error('file')
                            <span class="label"><span class="label-text-alt text-error">{{ $message }}</span></span>
                        @enderror
                    </div>

                    <!-- File Name -->
                    <div class="form-control mb-4">
                        <label class="label" for="display-name">
                            <span class="label-text font-medium">Display Name <span class="text-error">*</span></span>
                        </label>
                        <input type="text" name="name" id="display-name" value="{{ old('name') }}" class="input input-bordered" placeholder="Enter a name for this file" required />
                        @error('name')
                            <span class="label"><span class="label-text-alt text-error">{{ $message }}</span></span>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div class="form-control mb-4">
                        <label class="label" for="drive-description">
                            <span class="label-text font-medium">Description</span>
                        </label>
                        <textarea name="description" id="drive-description" class="textarea textarea-bordered" rows="3" placeholder="Add a description for this file (optional)">{{ old('description') }}</textarea>
                        @error('description')
                            <span class="label"><span class="label-text-alt text-error">{{ $message }}</span></span>
                        @enderror
                    </div>

                    <!-- Workspace -->
                    <div class="form-control mb-4">
                        <label class="label">
                            <span class="label-text font-medium">
                                Link to Workspace
                                <span class="text-base-content/40 font-normal ml-1">(Optional)</span>
                            </span>
                        </label>
                        <input type="hidden" name="workspace_id" id="workspace-id-input" value="{{ old('workspace_id', $selectedWorkspaceId) }}" />
                        <div class="relative">
                            <div id="workspace-select-container" class="min-h-12 px-4 py-2 border border-base-300 rounded-lg cursor-pointer flex items-center gap-3 bg-base-100 hover:border-primary transition-colors">
                                <span class="icon-[tabler--briefcase] size-5 text-base-content/40"></span>
                                <div class="flex-1" id="workspace-selected-display">
                                    <span class="text-base-content/50">Select a workspace...</span>
                                </div>
                                <span id="workspace-chevron" class="icon-[tabler--chevron-down] size-4 text-base-content/40 transition-transform"></span>
                            </div>
                            <div id="workspace-dropdown" class="absolute z-50 w-full mt-1 bg-base-100 border border-base-300 rounded-lg shadow-lg max-h-72 overflow-hidden hidden">
                                <!-- Search -->
                                <div class="p-2 border-b border-base-200">
                                    <div class="relative">
                                        <span class="icon-[tabler--search] size-4 absolute left-3 top-1/2 -translate-y-1/2 text-base-content/40"></span>
                                        <input type="text" id="workspace-search" class="input input-sm input-bordered w-full pl-9" placeholder="Search workspaces..." autocomplete="off" />
                                    </div>
                                </div>
                                <!-- Options -->
                                <div id="workspace-options" class="overflow-y-auto max-h-52">
                                    <!-- No workspace option -->
                                    <div class="workspace-option flex items-center gap-3 px-4 py-3 hover:bg-base-200 cursor-pointer transition-colors border-b border-base-100"
                                         data-value=""
                                         data-name="No workspace">
                                        <div class="w-9 h-9 rounded-lg bg-base-200 flex items-center justify-center flex-shrink-0">
                                            <span class="icon-[tabler--file] size-5 text-base-content/40"></span>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="font-medium text-sm">Personal File</p>
                                            <p class="text-xs text-base-content/50">Don't link to any workspace</p>
                                        </div>
                                        <span class="workspace-check icon-[tabler--check] size-5 text-primary hidden"></span>
                                    </div>
                                    @foreach($workspaces as $workspace)
                                        <div class="workspace-option flex items-center gap-3 px-4 py-3 hover:bg-base-200 cursor-pointer transition-colors"
                                             data-value="{{ $workspace->uuid }}"
                                             data-name="{{ $workspace->name }}"
                                             data-search="{{ strtolower($workspace->name) }}">
                                            <div class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0 text-white" style="background-color: {{ $workspace->color ?? '#3b82f6' }}">
                                                <span class="icon-[{{ $workspace->type->icon() }}] size-5"></span>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="font-medium text-sm truncate">{{ $workspace->name }}</p>
                                                <p class="text-xs text-base-content/50">{{ $workspace->type->label() }} &bull; {{ $workspace->members->count() }} members</p>
                                            </div>
                                            <span class="workspace-check icon-[tabler--check] size-5 text-primary hidden"></span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <span class="label">
                            <span class="label-text-alt text-base-content/50">
                                <span class="icon-[tabler--info-circle] size-3.5 inline-block mr-1"></span>
                                If selected, this file will appear in the workspace's Files tab
                            </span>
                        </span>
                        @error('workspace_id')
                            <span class="label"><span class="label-text-alt text-error">{{ $message }}</span></span>
                        @enderror
                    </div>

                    <!-- Tags -->
                    <div class="form-control mb-4">
                        <label class="label" for="tag-input">
                            <span class="label-text font-medium">Tags</span>
                        </label>
                        <div id="tags-container" class="flex flex-wrap gap-2 mb-2">
                            <!-- Tags will be added here -->
                        </div>
                        <div class="flex gap-2">
                            <input type="text" id="tag-input" class="input input-bordered flex-1" placeholder="Type a tag and press Enter" />
                            <button type="button" onclick="addTag()" class="btn btn-ghost">
                                <span class="icon-[tabler--plus] size-4"></span>
                                Add
                            </button>
                        </div>
                        @if($existingTags->isNotEmpty())
                            <div class="mt-2">
                                <span class="text-xs text-base-content/60">Existing tags:</span>
                                <div class="flex flex-wrap gap-1 mt-1">
                                    @foreach($existingTags as $tag)
                                        <button type="button" onclick="addExistingTag('{{ $tag->name }}')" class="badge badge-sm hover:badge-primary cursor-pointer" style="background-color: {{ $tag->color }}20; color: {{ $tag->color }}">
                                            {{ $tag->name }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Share With Team Members -->
                    <div class="form-control mb-6">
                        <span class="label">
                            <span class="label-text font-medium">Share with Team Members</span>
                        </span>
                        <p class="text-sm text-base-content/60 mb-3">Selected team members will be able to view this file in their Drive.</p>

                        @if($teamMembers->isEmpty())
                            <div class="text-sm text-base-content/50 italic">No team members available to share with.</div>
                        @else
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 max-h-60 overflow-y-auto p-2 border border-base-200 rounded-lg">
                                @foreach($teamMembers as $member)
                                    <label class="flex items-center gap-3 p-2 rounded-lg hover:bg-base-200 cursor-pointer">
                                        <input type="checkbox" name="share_with[]" value="{{ $member->id }}" class="checkbox checkbox-sm checkbox-primary" {{ in_array($member->id, old('share_with', [])) ? 'checked' : '' }} />
                                        <div class="avatar">
                                            <div class="w-8 h-8 rounded-full">
                                                <img src="{{ $member->avatar_url }}" alt="{{ $member->name }}" />
                                            </div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="font-medium text-sm truncate">{{ $member->name }}</div>
                                            <div class="text-xs text-base-content/60 truncate">{{ $member->email }}</div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <!-- Submit -->
                    <div class="flex justify-end gap-2">
                        <a href="{{ route('drive.index') }}" class="btn btn-ghost">Cancel</a>
                        <button type="submit" class="btn btn-primary" id="submit-btn">
                            <span class="icon-[tabler--upload] size-4"></span>
                            Upload File
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
const dropZone = document.getElementById('drop-zone');
const fileInput = document.getElementById('file-input');
const dropZoneContent = document.getElementById('drop-zone-content');
const filePreview = document.getElementById('file-preview');
const displayNameInput = document.getElementById('display-name');
const tagsContainer = document.getElementById('tags-container');
const tagInput = document.getElementById('tag-input');

let tags = [];

// File drop zone
dropZone.addEventListener('click', () => fileInput.click());

dropZone.addEventListener('dragover', (e) => {
    e.preventDefault();
    dropZone.classList.add('border-primary', 'bg-primary/5');
});

dropZone.addEventListener('dragleave', () => {
    dropZone.classList.remove('border-primary', 'bg-primary/5');
});

dropZone.addEventListener('drop', (e) => {
    e.preventDefault();
    dropZone.classList.remove('border-primary', 'bg-primary/5');
    if (e.dataTransfer.files.length) {
        fileInput.files = e.dataTransfer.files;
        handleFileSelect(e.dataTransfer.files[0]);
    }
});

fileInput.addEventListener('change', (e) => {
    if (e.target.files.length) {
        handleFileSelect(e.target.files[0]);
    }
});

function handleFileSelect(file) {
    // Update preview
    document.getElementById('file-name').textContent = file.name;
    document.getElementById('file-size').textContent = formatSize(file.size);
    document.getElementById('file-icon').className = 'size-10 text-primary ' + getFileIconClass(file.type);

    dropZoneContent.classList.add('hidden');
    filePreview.classList.remove('hidden');

    // Auto-fill display name if empty
    if (!displayNameInput.value) {
        // Remove extension from filename
        const nameWithoutExt = file.name.replace(/\.[^/.]+$/, '');
        displayNameInput.value = nameWithoutExt;
    }
}

function clearFile() {
    fileInput.value = '';
    dropZoneContent.classList.remove('hidden');
    filePreview.classList.add('hidden');
}

function formatSize(bytes) {
    if (bytes >= 1073741824) return (bytes / 1073741824).toFixed(2) + ' GB';
    if (bytes >= 1048576) return (bytes / 1048576).toFixed(2) + ' MB';
    if (bytes >= 1024) return (bytes / 1024).toFixed(2) + ' KB';
    return bytes + ' B';
}

function getFileIconClass(mimeType) {
    if (mimeType.startsWith('image/')) return 'icon-[tabler--photo]';
    if (mimeType.startsWith('video/')) return 'icon-[tabler--video]';
    if (mimeType.startsWith('audio/')) return 'icon-[tabler--music]';
    if (mimeType === 'application/pdf') return 'icon-[tabler--file-type-pdf]';
    if (mimeType.includes('word')) return 'icon-[tabler--file-type-doc]';
    if (mimeType.includes('excel') || mimeType.includes('spreadsheet')) return 'icon-[tabler--file-type-xls]';
    if (mimeType.includes('zip') || mimeType.includes('rar')) return 'icon-[tabler--file-zip]';
    return 'icon-[tabler--file]';
}

// Tags
function addTag() {
    const value = tagInput.value.trim();
    if (value && !tags.includes(value)) {
        tags.push(value);
        renderTags();
    }
    tagInput.value = '';
}

function addExistingTag(name) {
    if (!tags.includes(name)) {
        tags.push(name);
        renderTags();
    }
}

function removeTag(index) {
    tags.splice(index, 1);
    renderTags();
}

function renderTags() {
    tagsContainer.innerHTML = tags.map((tag, index) => `
        <span class="badge badge-lg gap-1">
            ${tag}
            <input type="hidden" name="tags[]" value="${tag}" />
            <button type="button" onclick="removeTag(${index})" class="btn btn-ghost btn-xs btn-circle">
                <span class="icon-[tabler--x] size-3"></span>
            </button>
        </span>
    `).join('');
}

tagInput.addEventListener('keypress', (e) => {
    if (e.key === 'Enter') {
        e.preventDefault();
        addTag();
    }
});

// Form submission with loading state
document.getElementById('upload-form').addEventListener('submit', function() {
    const btn = document.getElementById('submit-btn');
    btn.disabled = true;
    btn.innerHTML = '<span class="loading loading-spinner loading-sm"></span> Uploading...';
});

// Workspace Selector
const workspaceContainer = document.getElementById('workspace-select-container');
const workspaceDropdown = document.getElementById('workspace-dropdown');
const workspaceSearch = document.getElementById('workspace-search');
const workspaceOptions = document.querySelectorAll('.workspace-option');
const workspaceIdInput = document.getElementById('workspace-id-input');
const workspaceSelectedDisplay = document.getElementById('workspace-selected-display');
const workspaceChevron = document.getElementById('workspace-chevron');

let isWorkspaceDropdownOpen = false;

// Toggle dropdown
workspaceContainer.addEventListener('click', () => {
    isWorkspaceDropdownOpen = !isWorkspaceDropdownOpen;
    workspaceDropdown.classList.toggle('hidden', !isWorkspaceDropdownOpen);
    workspaceChevron.classList.toggle('rotate-180', isWorkspaceDropdownOpen);
    if (isWorkspaceDropdownOpen) {
        workspaceSearch.focus();
    }
});

// Close dropdown when clicking outside
document.addEventListener('click', (e) => {
    if (!workspaceContainer.contains(e.target) && !workspaceDropdown.contains(e.target)) {
        isWorkspaceDropdownOpen = false;
        workspaceDropdown.classList.add('hidden');
        workspaceChevron.classList.remove('rotate-180');
    }
});

// Search functionality
workspaceSearch.addEventListener('input', (e) => {
    const query = e.target.value.toLowerCase();
    workspaceOptions.forEach(option => {
        const searchText = option.dataset.search || option.dataset.name.toLowerCase();
        option.style.display = searchText.includes(query) || query === '' ? '' : 'none';
    });
});

// Select option
workspaceOptions.forEach(option => {
    option.addEventListener('click', () => {
        const value = option.dataset.value;
        const name = option.dataset.name;

        // Update hidden input
        workspaceIdInput.value = value;

        // Update display
        if (value === '') {
            workspaceSelectedDisplay.innerHTML = '<span class="text-base-content/50">Select a workspace...</span>';
        } else {
            const icon = option.querySelector('.w-9').cloneNode(true);
            icon.classList.remove('w-9', 'h-9');
            icon.classList.add('w-6', 'h-6');
            workspaceSelectedDisplay.innerHTML = `
                <div class="flex items-center gap-2">
                    ${icon.outerHTML}
                    <span class="font-medium text-sm">${name}</span>
                </div>
            `;
        }

        // Update checkmarks
        workspaceOptions.forEach(opt => {
            opt.querySelector('.workspace-check').classList.toggle('hidden', opt.dataset.value !== value);
        });

        // Close dropdown
        isWorkspaceDropdownOpen = false;
        workspaceDropdown.classList.add('hidden');
        workspaceChevron.classList.remove('rotate-180');
        workspaceSearch.value = '';
        workspaceOptions.forEach(opt => opt.style.display = '');
    });
});

// Initialize with pre-selected value
const initialValue = workspaceIdInput.value;
if (initialValue) {
    const initialOption = document.querySelector(`.workspace-option[data-value="${initialValue}"]`);
    if (initialOption) {
        initialOption.click();
    }
}
</script>
@endpush
@endsection
