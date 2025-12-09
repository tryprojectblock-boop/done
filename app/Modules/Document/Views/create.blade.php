@extends('layouts.app')

@section('content')
<div class="p-4 md:p-6">
    <div class="max-w-2xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center gap-2 text-sm text-base-content/60 mb-2">
                <a href="{{ route('dashboard') }}" class="hover:text-primary">Dashboard</a>
                <span class="icon-[tabler--chevron-right] size-4"></span>
                <a href="{{ route('documents.index') }}" class="hover:text-primary">Documents</a>
                <span class="icon-[tabler--chevron-right] size-4"></span>
                <span>New Document</span>
            </div>
            <h1 class="text-2xl font-bold text-base-content">Create Document</h1>
            <p class="text-base-content/60">Create a new document to collaborate with your team</p>
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

        <form action="{{ route('documents.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Card: Document Details -->
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <h2 class="card-title text-lg mb-4">
                        <span class="icon-[tabler--file-text] size-5"></span>
                        Document Details
                    </h2>

                    <div class="space-y-4">
                        <!-- Document Title -->
                        <div class="form-control">
                            <label class="label" for="document-title">
                                <span class="label-text font-medium">Document Name <span class="text-error">*</span></span>
                            </label>
                            <input type="text" name="title" id="document-title" value="{{ old('title') }}"
                                   class="input input-bordered w-full" placeholder="Enter document name" required>
                        </div>

                        <!-- Short Description -->
                        <div class="form-control">
                            <label class="label" for="document-description">
                                <span class="label-text font-medium">Short Description <span class="text-base-content/50 font-normal">(Optional)</span></span>
                            </label>
                            <textarea name="description" id="document-description" rows="2"
                                      class="textarea textarea-bordered w-full" placeholder="Brief description of this document">{{ old('description') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card: Collaborators -->
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <h2 class="card-title text-lg mb-4">
                        <span class="icon-[tabler--users] size-5"></span>
                        Collaborators
                    </h2>
                    <p class="text-sm text-base-content/60 mb-4">
                        <span class="icon-[tabler--info-circle] size-4 inline"></span>
                        Invite team members to view or edit this document
                    </p>

                    <div class="space-y-4">
                        <!-- Invite Team Members -->
                        <div class="form-control">
                            <label class="label" for="member-search">
                                <span class="label-text font-medium">Invite Team Members <span class="text-base-content/50 font-normal">(Optional)</span></span>
                            </label>
                            <div class="relative">
                                <div id="member-select" class="min-h-12 p-2 border border-base-300 rounded-lg cursor-pointer flex flex-wrap gap-2 items-center">
                                    <input type="text" id="member-search" class="flex-1 min-w-32 bg-transparent border-0 outline-none text-sm" placeholder="Search and select members..." autocomplete="off">
                                </div>
                                <div id="member-dropdown" class="absolute z-50 w-full mt-1 bg-base-100 border border-base-300 rounded-lg shadow-lg max-h-60 overflow-y-auto hidden">
                                    <!-- Select All Option -->
                                    <label id="select-all-option" class="flex items-center gap-3 p-3 hover:bg-base-200 cursor-pointer transition-colors border-b border-base-300 sticky top-0 bg-base-100 z-10">
                                        <input type="checkbox" id="select-all-members" class="checkbox checkbox-primary checkbox-sm">
                                        <span class="font-medium text-sm">Select All Members</span>
                                        <span class="text-xs text-base-content/50">(as Editor)</span>
                                    </label>
                                    @foreach($members as $member)
                                        <div class="member-option flex items-center gap-3 p-3 hover:bg-base-200 cursor-pointer transition-colors" data-id="{{ $member->id }}" data-name="{{ $member->name }}" data-email="{{ $member->email }}" data-avatar="{{ $member->avatar_url }}" data-search="{{ strtolower($member->name) }}">
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
                        </div>

                        <!-- Selected Members with Role -->
                        <div id="selected-members-container" class="space-y-2 hidden">
                            <label class="label">
                                <span class="label-text font-medium">Selected Collaborators</span>
                            </label>
                            <div id="selected-members-list" class="space-y-2">
                                <!-- Selected members will be added here -->
                            </div>
                        </div>

                        <div id="collaborator-hidden-inputs"></div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <div class="flex flex-col sm:flex-row gap-3 justify-start">
                        <button type="submit" class="btn btn-primary">
                            <span class="icon-[tabler--file-plus] size-5"></span>
                            Create Document
                        </button>
                        <a href="{{ route('documents.index') }}" class="btn btn-ghost">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const memberSelect = document.getElementById('member-select');
    const memberDropdown = document.getElementById('member-dropdown');
    const memberSearch = document.getElementById('member-search');
    const selectedMembersContainer = document.getElementById('selected-members-container');
    const selectedMembersList = document.getElementById('selected-members-list');
    const collaboratorHiddenInputs = document.getElementById('collaborator-hidden-inputs');
    const memberOptions = document.querySelectorAll('.member-option');
    const noMemberResults = document.getElementById('no-member-results');
    const selectAllCheckbox = document.getElementById('select-all-members');

    let selectedMembers = []; // {id, name, email, avatar, role}
    let memberHighlightIndex = -1;

    // Select All checkbox handler
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            if (this.checked) {
                // Select all members not already selected
                memberOptions.forEach(option => {
                    const id = option.dataset.id;
                    if (!selectedMembers.some(m => m.id === id)) {
                        selectedMembers.push({
                            id: id,
                            name: option.dataset.name,
                            email: option.dataset.email,
                            avatar: option.dataset.avatar,
                            role: 'editor'
                        });
                        option.classList.add('hidden');
                    }
                });
            } else {
                // Deselect all - clear the list
                selectedMembers = [];
                memberOptions.forEach(option => {
                    option.classList.remove('hidden');
                });
            }
            updateSelectedMembers();
            updateSelectAllState();
        });
    }

    function updateSelectAllState() {
        if (selectAllCheckbox) {
            const totalMembers = memberOptions.length;
            const selectedCount = selectedMembers.length;
            selectAllCheckbox.checked = selectedCount === totalMembers && totalMembers > 0;
            selectAllCheckbox.indeterminate = selectedCount > 0 && selectedCount < totalMembers;
        }
    }

    function showMemberDropdown() {
        memberDropdown.classList.remove('hidden');
        memberSelect.classList.add('ring-2', 'ring-primary', 'ring-offset-2');
    }

    function hideMemberDropdown() {
        memberDropdown.classList.add('hidden');
        memberSelect.classList.remove('ring-2', 'ring-primary', 'ring-offset-2');
    }

    function getVisibleMemberOptions() {
        return Array.from(memberOptions).filter(opt => !opt.classList.contains('hidden'));
    }

    function highlightMemberOption(index) {
        const visible = getVisibleMemberOptions();
        visible.forEach((opt, i) => opt.classList.toggle('bg-base-200', i === index));
        memberHighlightIndex = index;
        if (visible[index]) visible[index].scrollIntoView({ block: 'nearest' });
    }

    memberSelect.addEventListener('click', function(e) {
        if (e.target.closest('button')) return;
        if (memberDropdown.classList.contains('hidden')) showMemberDropdown();
        memberSearch.focus();
    });

    document.addEventListener('click', function(e) {
        if (!memberSelect.contains(e.target) && !memberDropdown.contains(e.target)) hideMemberDropdown();
    });

    memberSearch.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        let visibleCount = 0;
        memberOptions.forEach(option => {
            const name = option.dataset.search;
            const isSelected = selectedMembers.some(m => m.id === option.dataset.id);
            if (name.includes(searchTerm) && !isSelected) {
                option.classList.remove('hidden');
                visibleCount++;
            } else {
                option.classList.add('hidden');
            }
        });
        noMemberResults.classList.toggle('hidden', visibleCount > 0);
        if (memberDropdown.classList.contains('hidden')) showMemberDropdown();
        memberHighlightIndex = -1;
        highlightMemberOption(-1);
    });

    memberSearch.addEventListener('keydown', function(e) {
        const visible = getVisibleMemberOptions();
        if (visible.length === 0) return;
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            if (memberDropdown.classList.contains('hidden')) showMemberDropdown();
            memberHighlightIndex = Math.min(memberHighlightIndex + 1, visible.length - 1);
            highlightMemberOption(memberHighlightIndex);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            memberHighlightIndex = Math.max(memberHighlightIndex - 1, 0);
            highlightMemberOption(memberHighlightIndex);
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (memberHighlightIndex >= 0 && visible[memberHighlightIndex]) {
                visible[memberHighlightIndex].click();
                memberHighlightIndex = -1;
                highlightMemberOption(-1);
            }
        } else if (e.key === 'Escape') {
            hideMemberDropdown();
            memberHighlightIndex = -1;
        }
    });

    memberOptions.forEach(option => {
        option.addEventListener('click', function(e) {
            e.stopPropagation();
            const id = this.dataset.id;
            const name = this.dataset.name;
            const email = this.dataset.email;
            const avatar = this.dataset.avatar;

            // Check if already selected
            if (selectedMembers.some(m => m.id === id)) return;

            // Add to selected with default role 'editor'
            selectedMembers.push({ id, name, email, avatar, role: 'editor' });

            // Hide from dropdown
            this.classList.add('hidden');

            updateSelectedMembers();
            updateSelectAllState();
            memberSearch.value = '';
            memberSearch.focus();
        });
    });

    function updateSelectedMembers() {
        // Show/hide container
        selectedMembersContainer.classList.toggle('hidden', selectedMembers.length === 0);

        // Render selected members
        selectedMembersList.innerHTML = selectedMembers.map(m => `
            <div class="flex items-center gap-3 p-3 bg-base-200/50 rounded-lg" data-member-id="${m.id}">
                <div class="avatar">
                    <div class="w-10 rounded-full">
                        <img src="${m.avatar}" alt="${m.name}" />
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-medium text-sm">${m.name}</p>
                    <p class="text-xs text-base-content/50">${m.email || ''}</p>
                </div>
                <select class="select select-bordered select-sm w-28" onchange="updateMemberRole('${m.id}', this.value)">
                    <option value="editor" ${m.role === 'editor' ? 'selected' : ''}>Editor</option>
                    <option value="reader" ${m.role === 'reader' ? 'selected' : ''}>Reader</option>
                </select>
                <button type="button" class="btn btn-ghost btn-sm btn-circle text-error" onclick="removeMember('${m.id}')">
                    <span class="icon-[tabler--x] size-5"></span>
                </button>
            </div>
        `).join('');

        // Update hidden inputs
        collaboratorHiddenInputs.innerHTML = selectedMembers.map(m => `
            <input type="hidden" name="collaborators[${m.id}][user_id]" value="${m.id}">
            <input type="hidden" name="collaborators[${m.id}][role]" value="${m.role}">
        `).join('');

        // Update search placeholder
        memberSearch.placeholder = selectedMembers.length > 0 ? 'Add more collaborators...' : 'Search and select members...';
    }

    window.updateMemberRole = function(id, role) {
        const member = selectedMembers.find(m => m.id === id);
        if (member) {
            member.role = role;
            updateSelectedMembers();
        }
    };

    window.removeMember = function(id) {
        const index = selectedMembers.findIndex(m => m.id === id);
        if (index > -1) {
            selectedMembers.splice(index, 1);
            // Show in dropdown again
            const option = document.querySelector(`.member-option[data-id="${id}"]`);
            if (option) {
                option.classList.remove('hidden');
            }
            updateSelectedMembers();
            updateSelectAllState();
        }
    };
});
</script>
@endpush
@endsection
