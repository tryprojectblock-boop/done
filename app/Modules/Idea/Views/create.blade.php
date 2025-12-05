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
                <span>New Idea</span>
            </div>
            <h1 class="text-2xl font-bold text-base-content">Share an Idea</h1>
            <p class="text-base-content/60">Share your thoughts and suggestions with the team</p>
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

        <form action="{{ route('ideas.store') }}" method="POST" class="space-y-6">
            @csrf

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
                            <label class="label">
                                <span class="label-text font-medium">Idea Title <span class="text-error">*</span></span>
                            </label>
                            <input type="text" name="title" value="{{ old('title') }}"
                                   class="input input-bordered w-full" placeholder="Enter a clear, concise title for your idea" required>
                        </div>

                        <!-- Short Description -->
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Short Description <span class="text-base-content/50 font-normal">(Optional)</span></span>
                            </label>
                            <textarea name="short_description" class="textarea textarea-bordered h-20" maxlength="500" placeholder="Brief summary of your idea (max 500 characters)">{{ old('short_description') }}</textarea>
                            <label class="label">
                                <span class="label-text-alt text-base-content/50">This will be shown in the ideas list</span>
                            </label>
                        </div>

                        <!-- Detailed Description (Quill Rich Text Editor) -->
                        <x-quill-editor
                            name="description"
                            id="idea-description"
                            label="Detailed Description"
                            :value="old('description')"
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
                        <!-- Workspace (Optional) -->
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Workspace <span class="text-base-content/50 font-normal">(Optional)</span></span>
                            </label>
                            <select name="workspace_id" class="select select-bordered w-full">
                                <option value="">No Workspace (General Idea)</option>
                                @foreach($workspaces as $workspace)
                                    <option value="{{ $workspace->id }}" {{ old('workspace_id') == $workspace->id ? 'selected' : '' }}>
                                        {{ $workspace->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Priority -->
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Priority</span>
                            </label>
                            <select name="priority" class="select select-bordered w-full">
                                @foreach($priorities as $value => $label)
                                    <option value="{{ $value }}" {{ old('priority', 'medium') === $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Invite Members (Optional) -->
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Invite Members <span class="text-base-content/50 font-normal">(Optional)</span></span>
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
                                        <div class="member-option flex items-center gap-3 p-3 hover:bg-base-200 cursor-pointer transition-colors" data-id="{{ $member->id }}" data-name="{{ $member->name }}" data-search="{{ strtolower($member->name) }}">
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
                            <!-- Hidden inputs for form submission -->
                            <div id="member-hidden-inputs"></div>
                            <label class="label">
                                <span class="label-text-alt text-base-content/50">Members will be notified and can collaborate on this idea</span>
                            </label>
                        </div>

                        <!-- Invite Guests (Optional) -->
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Invite Guests <span class="text-base-content/50 font-normal">(Optional)</span></span>
                            </label>
                            <div class="relative">
                                <div id="guest-select" class="min-h-12 p-2 border border-base-300 rounded-lg cursor-pointer flex flex-wrap gap-2 items-center">
                                    <div id="selected-guests" class="flex flex-wrap gap-2">
                                        <!-- Selected guests will be shown here -->
                                    </div>
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
                            <!-- Hidden inputs for form submission -->
                            <div id="guest-hidden-inputs"></div>
                            <label class="label">
                                <span class="label-text-alt text-base-content/50">Guests can view and comment on this idea</span>
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
                            <span class="icon-[tabler--bulb] size-5"></span>
                            Share Idea
                        </button>
                        <a href="{{ route('ideas.index') }}" class="btn btn-ghost">
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
    let selectedMembers = [];

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
    let memberHighlightIndex = -1;

    function getVisibleMemberOptions() {
        return Array.from(memberOptions).filter(opt => !opt.classList.contains('hidden'));
    }

    function highlightMemberOption(index) {
        const visible = getVisibleMemberOptions();
        visible.forEach((opt, i) => {
            opt.classList.toggle('bg-base-200', i === index);
        });
        memberHighlightIndex = index;
        // Scroll into view
        if (visible[index]) {
            visible[index].scrollIntoView({ block: 'nearest' });
        }
    }

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

        // Reset highlight
        memberHighlightIndex = -1;
        highlightMemberOption(-1);
    });

    // Keyboard navigation for members
    memberSearch.addEventListener('keydown', function(e) {
        const visible = getVisibleMemberOptions();
        if (visible.length === 0) return;

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            if (memberDropdown.classList.contains('hidden')) {
                showMemberDropdown();
            }
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

    // Multi-select Guests
    const guestSelect = document.getElementById('guest-select');
    const guestDropdown = document.getElementById('guest-dropdown');
    const guestSearch = document.getElementById('guest-search');
    const selectedGuestsContainer = document.getElementById('selected-guests');
    const guestHiddenInputs = document.getElementById('guest-hidden-inputs');
    const guestOptions = document.querySelectorAll('.guest-option');
    const noGuestResults = document.getElementById('no-guest-results');
    let selectedGuests = [];

    // Show guest dropdown
    function showGuestDropdown() {
        guestDropdown.classList.remove('hidden');
        guestSelect.classList.add('ring-2', 'ring-secondary', 'ring-offset-2');
    }

    // Hide guest dropdown
    function hideGuestDropdown() {
        guestDropdown.classList.add('hidden');
        guestSelect.classList.remove('ring-2', 'ring-secondary', 'ring-offset-2');
    }

    // Click on guest container
    if (guestSelect) {
        guestSelect.addEventListener('click', function(e) {
            if (e.target.closest('button')) return;
            if (guestDropdown.classList.contains('hidden')) {
                showGuestDropdown();
            }
            guestSearch.focus();
        });

        // Hide guest dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (guestSelect && !guestSelect.contains(e.target) && guestDropdown && !guestDropdown.contains(e.target)) {
                hideGuestDropdown();
            }
        });
    }

    // Guest search functionality
    let guestHighlightIndex = -1;

    function getVisibleGuestOptions() {
        return Array.from(guestOptions).filter(opt => !opt.classList.contains('hidden'));
    }

    function highlightGuestOption(index) {
        const visible = getVisibleGuestOptions();
        visible.forEach((opt, i) => {
            opt.classList.toggle('bg-base-200', i === index);
        });
        guestHighlightIndex = index;
        // Scroll into view
        if (visible[index]) {
            visible[index].scrollIntoView({ block: 'nearest' });
        }
    }

    if (guestSearch) {
        guestSearch.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            let visibleCount = 0;

            guestOptions.forEach(option => {
                const name = option.dataset.search;
                if (name.includes(searchTerm)) {
                    option.classList.remove('hidden');
                    visibleCount++;
                } else {
                    option.classList.add('hidden');
                }
            });

            if (noGuestResults) {
                noGuestResults.classList.toggle('hidden', visibleCount > 0);
            }

            if (guestDropdown.classList.contains('hidden')) {
                showGuestDropdown();
            }

            // Reset highlight
            guestHighlightIndex = -1;
            highlightGuestOption(-1);
        });

        // Keyboard navigation for guests
        guestSearch.addEventListener('keydown', function(e) {
            const visible = getVisibleGuestOptions();
            if (visible.length === 0) return;

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                if (guestDropdown.classList.contains('hidden')) {
                    showGuestDropdown();
                }
                guestHighlightIndex = Math.min(guestHighlightIndex + 1, visible.length - 1);
                highlightGuestOption(guestHighlightIndex);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                guestHighlightIndex = Math.max(guestHighlightIndex - 1, 0);
                highlightGuestOption(guestHighlightIndex);
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (guestHighlightIndex >= 0 && visible[guestHighlightIndex]) {
                    visible[guestHighlightIndex].click();
                    guestHighlightIndex = -1;
                    highlightGuestOption(-1);
                }
            } else if (e.key === 'Escape') {
                hideGuestDropdown();
                guestHighlightIndex = -1;
            }
        });
    }

    // Select/deselect guest
    guestOptions.forEach(option => {
        option.addEventListener('click', function(e) {
            e.stopPropagation();
            const id = this.dataset.id;
            const name = this.dataset.name;
            const checkIcon = this.querySelector('.guest-check');

            const index = selectedGuests.findIndex(g => g.id === id);
            if (index > -1) {
                selectedGuests.splice(index, 1);
                checkIcon.classList.add('hidden');
                this.classList.remove('bg-secondary/10');
            } else {
                selectedGuests.push({ id, name });
                checkIcon.classList.remove('hidden');
                this.classList.add('bg-secondary/10');
            }

            updateSelectedGuests();
            guestSearch.focus();
        });
    });

    // Update guest UI
    function updateSelectedGuests() {
        selectedGuestsContainer.innerHTML = selectedGuests.map(g => `
            <span class="badge badge-secondary gap-1">
                ${g.name}
                <button type="button" class="btn btn-ghost btn-xs btn-circle size-4" onclick="removeGuest('${g.id}', event)">
                    <span class="icon-[tabler--x] size-3"></span>
                </button>
            </span>
        `).join('');

        guestHiddenInputs.innerHTML = selectedGuests.map(g =>
            `<input type="hidden" name="guest_ids[]" value="${g.id}">`
        ).join('');

        guestSearch.placeholder = selectedGuests.length > 0 ? 'Add more...' : 'Search and select guests...';
    }

    // Remove guest
    window.removeGuest = function(id, event) {
        event.stopPropagation();
        const index = selectedGuests.findIndex(g => g.id === id);
        if (index > -1) {
            selectedGuests.splice(index, 1);
            const option = document.querySelector(`.guest-option[data-id="${id}"]`);
            if (option) {
                option.querySelector('.guest-check').classList.add('hidden');
                option.classList.remove('bg-secondary/10');
            }
            updateSelectedGuests();
        }
    };
});
</script>
@endpush
@endsection
