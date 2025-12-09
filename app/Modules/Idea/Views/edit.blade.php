@extends('layouts.app')

@section('content')
<div class="p-4 md:p-6">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center gap-2 text-sm text-base-content/60 mb-2">
                <a href="{{ route('dashboard') }}" class="hover:text-primary">Dashboard</a>
                <span class="icon-[tabler--chevron-right] size-4"></span>
                <a href="{{ route('ideas.index') }}" class="hover:text-primary">Ideas</a>
                <span class="icon-[tabler--chevron-right] size-4"></span>
                <a href="{{ route('ideas.show', $idea->uuid) }}" class="hover:text-primary">{{ Str::limit($idea->title, 20) }}</a>
                <span class="icon-[tabler--chevron-right] size-4"></span>
                <span>Edit</span>
            </div>
            <h1 class="text-2xl font-bold text-base-content">Edit Idea</h1>
            <p class="text-base-content/60">Update your idea details</p>
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

        <form action="{{ route('ideas.update', $idea->uuid) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Card 1: Basic Info -->
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <h2 class="card-title text-lg mb-4">
                        <span class="icon-[tabler--bulb] size-5"></span>
                        Idea Information
                    </h2>

                    <div class="space-y-4">
                        <!-- Idea Title -->
                        <div class="form-control">
                            <label class="label" for="edit-idea-title">
                                <span class="label-text font-medium">Idea Title <span class="text-error">*</span></span>
                            </label>
                            <input type="text" name="title" id="edit-idea-title" value="{{ old('title', $idea->title) }}"
                                   class="input input-bordered w-full" placeholder="Enter a clear, concise title for your idea" required>
                        </div>

                        <!-- Workspace (Optional) -->
                        <div class="form-control">
                            <label class="label" for="edit-idea-workspace">
                                <span class="label-text font-medium">Workspace <span class="text-base-content/50 font-normal">(Optional)</span></span>
                            </label>
                            <select name="workspace_id" id="edit-idea-workspace" class="select select-bordered w-full">
                                <option value="">No Workspace (General Idea)</option>
                                @foreach($workspaces as $workspace)
                                    <option value="{{ $workspace->id }}" {{ old('workspace_id', $idea->workspace_id) == $workspace->id ? 'selected' : '' }}>
                                        {{ $workspace->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Short Description -->
                        <div class="form-control">
                            <label class="label" for="edit-idea-short-description">
                                <span class="label-text font-medium">Short Description <span class="text-base-content/50 font-normal">(Optional)</span></span>
                            </label>
                            <textarea name="short_description" id="edit-idea-short-description" class="textarea textarea-bordered h-20" maxlength="500" placeholder="Brief summary of your idea (max 500 characters)">{{ old('short_description', $idea->short_description) }}</textarea>
                            <label class="label">
                                <span class="label-text-alt text-base-content/50">This will be shown in the ideas list</span>
                            </label>
                        </div>

                        <!-- Detailed Description (Quill Rich Text Editor) -->
                        <x-quill-editor
                            name="description"
                            id="idea-description"
                            label="Detailed Description"
                            :value="old('description', $idea->description)"
                            placeholder="Describe your idea in detail... You can drag & drop images here"
                            height="250px"
                        />
                    </div>
                </div>
            </div>

            <!-- Card 2: Settings -->
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <h2 class="card-title text-lg mb-4">
                        <span class="icon-[tabler--settings] size-5"></span>
                        Settings
                    </h2>

                    <div class="space-y-4">
                        <!-- Priority -->
                        <div class="form-control">
                            <label class="label" for="edit-idea-priority">
                                <span class="label-text font-medium">Priority</span>
                            </label>
                            <select name="priority" id="edit-idea-priority" class="select select-bordered w-full">
                                @foreach($priorities as $value => $label)
                                    <option value="{{ $value }}" {{ old('priority', $idea->priority->value) === $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Invite Members (Optional) -->
                        <div class="form-control">
                            <label class="label" for="member-search">
                                <span class="label-text font-medium">Members <span class="text-base-content/50 font-normal">(Optional)</span></span>
                            </label>
                            <div class="relative">
                                <div id="member-select" class="min-h-12 p-2 border border-base-300 rounded-lg cursor-pointer flex flex-wrap gap-2 items-center">
                                    <div id="selected-members" class="flex flex-wrap gap-2">
                                        <!-- Selected members will be shown here -->
                                    </div>
                                    <input type="text" id="member-search" class="flex-1 min-w-32 bg-transparent border-0 outline-none text-sm" placeholder="Search and select members..." autocomplete="off">
                                </div>
                                <div id="member-dropdown" class="absolute z-50 w-full mt-1 bg-base-100 border border-base-300 rounded-lg shadow-lg max-h-60 overflow-y-auto hidden">
                                    @foreach($members as $member)
                                        <div class="member-option flex items-center gap-3 p-3 hover:bg-base-200 cursor-pointer transition-colors {{ $idea->members->contains($member->id) ? 'bg-primary/10' : '' }}" data-id="{{ $member->id }}" data-name="{{ $member->name }}" data-search="{{ strtolower($member->name) }}">
                                            <div class="avatar">
                                                <div class="w-8 rounded-full">
                                                    <img src="{{ $member->avatar_url }}" alt="{{ $member->name }}" />
                                                </div>
                                            </div>
                                            <div class="flex-1">
                                                <p class="font-medium text-sm">{{ $member->name }}</p>
                                                <p class="text-xs text-base-content/50">{{ $member->email ?? '' }}</p>
                                            </div>
                                            <span class="member-check icon-[tabler--check] size-5 text-primary {{ $idea->members->contains($member->id) ? '' : 'hidden' }}"></span>
                                        </div>
                                    @endforeach
                                    <div id="no-member-results" class="p-3 text-center text-base-content/50 text-sm hidden">No members found</div>
                                </div>
                            </div>
                            <!-- Hidden inputs for form submission -->
                            <div id="member-hidden-inputs">
                                @foreach($idea->members as $member)
                                    <input type="hidden" name="member_ids[]" value="{{ $member->id }}">
                                @endforeach
                            </div>
                            <label class="label">
                                <span class="label-text-alt text-base-content/50">Members will be notified and can collaborate on this idea</span>
                            </label>
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
                        <a href="{{ route('ideas.show', $idea->uuid) }}" class="btn btn-ghost">
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
    // Multi-select Members
    const memberSelect = document.getElementById('member-select');
    const memberDropdown = document.getElementById('member-dropdown');
    const memberSearch = document.getElementById('member-search');
    const selectedMembersContainer = document.getElementById('selected-members');
    const memberHiddenInputs = document.getElementById('member-hidden-inputs');
    const memberOptions = document.querySelectorAll('.member-option');
    const noMemberResults = document.getElementById('no-member-results');

    // Initialize with existing members
    let selectedMembers = [
        @foreach($idea->members as $member)
            { id: '{{ $member->id }}', name: '{{ $member->name }}' },
        @endforeach
    ];

    // Render initial state
    updateSelectedMembers();

    // Show dropdown
    function showMemberDropdown() {
        memberDropdown.classList.remove('hidden');
        memberSelect.classList.add('ring-2', 'ring-primary', 'ring-offset-2');
    }

    // Hide dropdown
    function hideMemberDropdown() {
        memberDropdown.classList.add('hidden');
        memberSelect.classList.remove('ring-2', 'ring-primary', 'ring-offset-2');
    }

    // Click on container
    memberSelect.addEventListener('click', function(e) {
        if (e.target.closest('button')) return;
        if (memberDropdown.classList.contains('hidden')) {
            showMemberDropdown();
        }
        memberSearch.focus();
    });

    // Hide dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!memberSelect.contains(e.target) && !memberDropdown.contains(e.target)) {
            hideMemberDropdown();
        }
    });

    // Search functionality
    memberSearch.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        let visibleCount = 0;

        memberOptions.forEach(option => {
            const name = option.dataset.search;
            if (name.includes(searchTerm)) {
                option.classList.remove('hidden');
                visibleCount++;
            } else {
                option.classList.add('hidden');
            }
        });

        noMemberResults.classList.toggle('hidden', visibleCount > 0);

        if (memberDropdown.classList.contains('hidden')) {
            showMemberDropdown();
        }
    });

    // Select/deselect member
    memberOptions.forEach(option => {
        option.addEventListener('click', function(e) {
            e.stopPropagation();
            const id = this.dataset.id;
            const name = this.dataset.name;
            const checkIcon = this.querySelector('.member-check');

            const index = selectedMembers.findIndex(m => m.id === id);
            if (index > -1) {
                selectedMembers.splice(index, 1);
                checkIcon.classList.add('hidden');
                this.classList.remove('bg-primary/10');
            } else {
                selectedMembers.push({ id, name });
                checkIcon.classList.remove('hidden');
                this.classList.add('bg-primary/10');
            }

            updateSelectedMembers();
            memberSearch.focus();
        });
    });

    // Update UI
    function updateSelectedMembers() {
        selectedMembersContainer.innerHTML = selectedMembers.map(m => `
            <span class="badge badge-primary gap-1">
                ${m.name}
                <button type="button" class="btn btn-ghost btn-xs btn-circle size-4" onclick="removeMember('${m.id}', event)">
                    <span class="icon-[tabler--x] size-3"></span>
                </button>
            </span>
        `).join('');

        memberHiddenInputs.innerHTML = selectedMembers.map(m =>
            `<input type="hidden" name="member_ids[]" value="${m.id}">`
        ).join('');

        memberSearch.placeholder = selectedMembers.length > 0 ? 'Add more...' : 'Search and select members...';
    }

    // Remove member
    window.removeMember = function(id, event) {
        event.stopPropagation();
        const index = selectedMembers.findIndex(m => m.id === id);
        if (index > -1) {
            selectedMembers.splice(index, 1);
            const option = document.querySelector(`.member-option[data-id="${id}"]`);
            if (option) {
                option.querySelector('.member-check').classList.add('hidden');
                option.classList.remove('bg-primary/10');
            }
            updateSelectedMembers();
        }
    };
});
</script>
@endpush
@endsection
