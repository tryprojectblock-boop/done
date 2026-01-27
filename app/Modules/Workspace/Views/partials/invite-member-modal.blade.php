@php
    $existingMemberIds = $workspace->members->pluck('id')->toArray();
    $companyId = auth()->user()->company_id;

    // Get available members from company_user pivot table (both active and invited)
    $availableMembers = \App\Models\User::query()
        ->join('company_user', 'users.id', '=', 'company_user.user_id')
        ->where('company_user.company_id', $companyId)
        ->whereNotIn('users.id', $existingMemberIds)
        ->whereIn('users.status', [\App\Models\User::STATUS_ACTIVE, \App\Models\User::STATUS_INVITED])
        ->select('users.*', 'company_user.role as company_role')
        ->orderBy('users.status') // Active users first, then invited
        ->orderBy('users.name')
        ->get();
@endphp

<!-- Add Team Member Modal -->
<div id="invite-modal" class="custom-modal">
    <div class="custom-modal-box max-w-lg bg-base-100">
        <!-- Header -->
        <div class="flex items-center justify-between mb-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary to-secondary flex items-center justify-center">
                    <span class="icon-[tabler--user-plus] size-5 text-white"></span>
                </div>
                <div>
                    <h3 class="font-bold text-lg">Add Team Member</h3>
                    <p class="text-xs text-base-content/50">Add existing team members to this workspace</p>
                </div>
            </div>
            <button type="button" onclick="closeInviteModal()" class="btn btn-ghost btn-sm btn-circle hover:bg-error/10 hover:text-error transition-colors">
                <span class="icon-[tabler--x] size-5"></span>
            </button>
        </div>

        <form action="{{ route('workspace.members.invite', $workspace) }}" method="POST" id="invite-form">
            @csrf

            @if($availableMembers->count() > 0)
            <!-- Multi-Select Team Members (Select2 style with chips) -->
            <div class="form-control mb-4">
                <label class="label">
                    <span class="label-text font-medium">Select Team Members <span class="text-error">*</span></span>
                </label>
                <div class="relative">
                    <!-- Selected members chips container -->
                    <div id="modal-selected-members" class="flex flex-wrap gap-2 mb-2 min-h-0 empty:hidden"></div>

                    <div id="modal-member-select-container" class="min-h-12 p-2 border border-base-300 rounded-lg cursor-pointer flex items-center gap-2 bg-base-100 hover:border-primary transition-colors">
                        <span class="icon-[tabler--search] size-5 text-base-content/50"></span>
                        <input type="text" id="modal-member-search" class="flex-1 bg-transparent border-0 outline-none text-sm" placeholder="Search and select team members..." autocomplete="off">
                        <span id="modal-member-chevron" class="icon-[tabler--chevron-down] size-4 text-base-content/50"></span>
                    </div>
                    <div id="modal-member-dropdown" class="absolute z-50 w-full mt-1 bg-base-100 border border-base-300 rounded-lg shadow-lg max-h-60 overflow-y-auto hidden">
                        @foreach($availableMembers as $availableUser)
                            @php
                                $userRoleData = \App\Models\User::ROLES[$availableUser->company_role] ?? null;
                                $userRoleLabel = $userRoleData['label'] ?? ucfirst($availableUser->company_role);
                                $userRoleColor = $userRoleData['color'] ?? 'neutral';
                                $isInvited = $availableUser->status === \App\Models\User::STATUS_INVITED;
                            @endphp
                            <div class="modal-member-option flex items-center gap-3 p-3 hover:bg-base-200 cursor-pointer transition-colors"
                                 data-id="{{ $availableUser->id }}"
                                 data-name="{{ $availableUser->name }}"
                                 data-email="{{ $availableUser->email }}"
                                 data-avatar="{{ $availableUser->avatar_url }}"
                                 data-initials="{{ $availableUser->initials }}"
                                 data-search="{{ strtolower($availableUser->name . ' ' . $availableUser->email) }}">
                                <div class="flex items-center justify-center w-5 h-5 border-2 border-base-300 rounded modal-member-checkbox transition-colors">
                                    <span class="modal-member-check icon-[tabler--check] size-4 text-white hidden"></span>
                                </div>
                                @if($availableUser->avatar_url)
                                    <div class="avatar">
                                        <div class="w-9 rounded-full">
                                            <img src="{{ $availableUser->avatar_url }}" alt="{{ $availableUser->name }}" class="object-cover">
                                        </div>
                                    </div>
                                @else
                                    <div class="w-9 h-9 rounded-full bg-base-200 flex items-center justify-center">
                                        <span class="text-xs font-medium text-base-content/70">{{ $availableUser->initials }}</span>
                                    </div>
                                @endif
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-sm truncate">
                                        {{ $availableUser->name }}
                                        @if($isInvited)
                                            <span class="badge badge-warning badge-xs ml-1">Invited</span>
                                        @endif
                                    </p>
                                    <p class="text-xs text-base-content/50 truncate">{{ $availableUser->email }}</p>
                                </div>
                                <span class="badge badge-{{ $userRoleColor }} badge-sm">{{ $userRoleLabel }}</span>
                            </div>
                        @endforeach
                        <div id="modal-no-member-results" class="p-3 text-center text-base-content/50 text-sm hidden">No members found</div>
                    </div>
                    <!-- Hidden inputs container for selected member IDs -->
                    <div id="modal-member-hidden-inputs"></div>
                </div>
            </div>

            <!-- Role Selection -->
            <div class="form-control mb-6">
                <label class="label" for="invite-role">
                    <span class="label-text font-medium">Workspace Role <span class="text-error">*</span></span>
                </label>
                <select name="role" id="invite-role" class="select select-bordered w-full" required>
                    <option value="">Select a role...</option>
                    <option value="admin">Admin - Can manage members and settings</option>
                    <option value="member" selected>Member - Can create and edit content</option>
                    <option value="reviewer">Reviewer - Can view and comment only</option>
                </select>
            </div>

            <!-- Role descriptions -->
            <div class="mb-6 p-3 bg-info/10 border border-info/20 rounded-lg">
                <p class="text-sm text-base-content/70">
                    <strong>Admin:</strong> Can manage members and settings.
                    <strong>Member:</strong> Can create and manage content.
                    <strong>Reviewer:</strong> Can view and comment only.
                </p>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-base-200">
                <button type="button" class="btn btn-ghost" onclick="closeInviteModal()">Cancel</button>
                <button type="submit" class="btn btn-primary gap-2" id="modal-add-btn" disabled>
                    <span class="icon-[tabler--plus] size-5"></span>
                    <span id="modal-add-btn-text">Add Members</span>
                </button>
            </div>
            @else
            <!-- No available members -->
            <div class="text-center py-8">
                <div class="w-16 h-16 rounded-full bg-base-200 flex items-center justify-center mx-auto mb-4">
                    <span class="icon-[tabler--users-group] size-8 text-base-content/50"></span>
                </div>
                <h4 class="font-medium text-base-content mb-2">All team members added</h4>
                <p class="text-sm text-base-content/60 mb-6">All your team members are already in this workspace. Invite new members to your team first.</p>
                <a href="{{ route('users.index') }}" class="btn btn-primary gap-2">
                    <span class="icon-[tabler--user-plus] size-5"></span>
                    Invite New Team Members
                </a>
            </div>
            @endif
        </form>
    </div>
    <div class="custom-modal-backdrop" onclick="closeInviteModal()"></div>
</div>

<style>
/* Custom Modal Styles */
.custom-modal {
    pointer-events: none;
    opacity: 0;
    visibility: hidden;
    position: fixed;
    inset: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
    transition: opacity 0.2s ease-out, visibility 0.2s ease-out;
}

.custom-modal.modal-open {
    pointer-events: auto;
    opacity: 1;
    visibility: visible;
}

.custom-modal .custom-modal-box {
    position: relative;
    z-index: 10000;
    padding: 1.5rem;
    border-radius: 1rem;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    max-height: 90vh;
    overflow-y: auto;
    transform: scale(0.95);
    transition: transform 0.2s ease-out;
}

.custom-modal.modal-open .custom-modal-box {
    transform: scale(1);
}

.custom-modal .custom-modal-backdrop {
    position: fixed;
    inset: 0;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 9998;
}
</style>

<script>
// Track selected members
let selectedMembers = [];

// Custom modal functions
function openInviteModal() {
    document.getElementById('invite-modal').classList.add('modal-open');
    document.body.style.overflow = 'hidden';
    // Focus on search input
    setTimeout(() => {
        const searchInput = document.getElementById('modal-member-search');
        if (searchInput) searchInput.focus();
    }, 100);
}

function closeInviteModal() {
    document.getElementById('invite-modal').classList.remove('modal-open');
    document.body.style.overflow = '';
    // Reset modal dropdown
    resetModalDropdown();
}

function resetModalDropdown() {
    const searchInput = document.getElementById('modal-member-search');
    const dropdown = document.getElementById('modal-member-dropdown');
    const selectedContainer = document.getElementById('modal-selected-members');
    const hiddenInputsContainer = document.getElementById('modal-member-hidden-inputs');
    const addBtn = document.getElementById('modal-add-btn');
    const addBtnText = document.getElementById('modal-add-btn-text');

    // Clear selected members
    selectedMembers = [];

    if (searchInput) {
        searchInput.value = '';
        searchInput.placeholder = 'Search and select team members...';
    }
    if (dropdown) dropdown.classList.add('hidden');
    if (selectedContainer) selectedContainer.innerHTML = '';
    if (hiddenInputsContainer) hiddenInputsContainer.innerHTML = '';
    if (addBtn) addBtn.disabled = true;
    if (addBtnText) addBtnText.textContent = 'Add Members';

    // Reset all options - uncheck all
    document.querySelectorAll('.modal-member-option').forEach(opt => {
        opt.style.display = 'flex';
        opt.classList.remove('bg-primary/10');
        const checkbox = opt.querySelector('.modal-member-checkbox');
        const check = opt.querySelector('.modal-member-check');
        if (checkbox) {
            checkbox.classList.remove('bg-primary', 'border-primary');
            checkbox.classList.add('border-base-300');
        }
        if (check) check.classList.add('hidden');
    });
    document.getElementById('modal-no-member-results')?.classList.add('hidden');
}

// Add member to selection
function addMemberToSelection(userId, userName, userAvatar, userInitials) {
    if (selectedMembers.find(m => m.id === userId)) return;

    selectedMembers.push({ id: userId, name: userName, avatar: userAvatar, initials: userInitials });
    updateSelectedMembersUI();
}

// Remove member from selection
function removeMemberFromSelection(userId) {
    selectedMembers = selectedMembers.filter(m => m.id !== userId);
    updateSelectedMembersUI();

    // Uncheck the option in dropdown
    const option = document.querySelector(`.modal-member-option[data-id="${userId}"]`);
    if (option) {
        option.classList.remove('bg-primary/10');
        const checkbox = option.querySelector('.modal-member-checkbox');
        const check = option.querySelector('.modal-member-check');
        if (checkbox) {
            checkbox.classList.remove('bg-primary', 'border-primary');
            checkbox.classList.add('border-base-300');
        }
        if (check) check.classList.add('hidden');
    }
}

// Update UI for selected members
function updateSelectedMembersUI() {
    const selectedContainer = document.getElementById('modal-selected-members');
    const hiddenInputsContainer = document.getElementById('modal-member-hidden-inputs');
    const addBtn = document.getElementById('modal-add-btn');
    const addBtnText = document.getElementById('modal-add-btn-text');

    // Clear containers
    selectedContainer.innerHTML = '';
    hiddenInputsContainer.innerHTML = '';

    // Add chips for selected members
    selectedMembers.forEach(member => {
        // Add chip
        const chip = document.createElement('div');
        chip.className = 'badge badge-lg gap-2 pr-1 bg-primary/10 border-primary/20';
        chip.innerHTML = `
            <div class="avatar ${member.avatar ? '' : 'placeholder'}">
                ${member.avatar
                    ? `<div class="w-5 rounded-full"><img src="${member.avatar}" alt="${member.name}"></div>`
                    : `<div class="bg-primary text-primary-content rounded-full w-5 h-5 flex items-center justify-center"><span class="text-[10px]">${member.initials}</span></div>`
                }
            </div>
            <span class="text-sm">${member.name}</span>
            <button type="button" class="btn btn-ghost btn-xs btn-circle hover:bg-error/20 hover:text-error" onclick="removeMemberFromSelection('${member.id}')">
                <span class="icon-[tabler--x] size-3"></span>
            </button>
        `;
        selectedContainer.appendChild(chip);

        // Add hidden input
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'user_ids[]';
        input.value = member.id;
        hiddenInputsContainer.appendChild(input);
    });

    // Update button state
    if (addBtn) {
        addBtn.disabled = selectedMembers.length === 0;
    }
    if (addBtnText) {
        if (selectedMembers.length === 0) {
            addBtnText.textContent = 'Add Members';
        } else if (selectedMembers.length === 1) {
            addBtnText.textContent = 'Add 1 Member';
        } else {
            addBtnText.textContent = `Add ${selectedMembers.length} Members`;
        }
    }
}

// Modal searchable dropdown functionality
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('modal-member-select-container');
    const searchInput = document.getElementById('modal-member-search');
    const dropdown = document.getElementById('modal-member-dropdown');
    const noResults = document.getElementById('modal-no-member-results');
    const options = document.querySelectorAll('.modal-member-option');

    if (!container || !searchInput || !dropdown) return;

    // Toggle dropdown on container click
    container.addEventListener('click', function(e) {
        dropdown.classList.toggle('hidden');
        if (!dropdown.classList.contains('hidden')) {
            searchInput.focus();
        }
    });

    // Search filtering
    searchInput.addEventListener('input', function(e) {
        const query = e.target.value.toLowerCase().trim();
        let visibleCount = 0;

        options.forEach(opt => {
            const searchStr = opt.dataset.search || '';
            if (query === '' || searchStr.includes(query)) {
                opt.style.display = 'flex';
                visibleCount++;
            } else {
                opt.style.display = 'none';
            }
        });

        // Show/hide no results
        if (visibleCount === 0 && query !== '') {
            noResults?.classList.remove('hidden');
        } else {
            noResults?.classList.add('hidden');
        }
    });

    // Option selection (toggle)
    options.forEach(opt => {
        opt.addEventListener('click', function(e) {
            e.stopPropagation();

            const userId = this.dataset.id;
            const userName = this.dataset.name;
            const userAvatar = this.dataset.avatar;
            const userInitials = this.dataset.initials;
            const checkbox = this.querySelector('.modal-member-checkbox');
            const check = this.querySelector('.modal-member-check');

            // Check if already selected
            const isSelected = selectedMembers.find(m => m.id === userId);

            if (isSelected) {
                // Deselect
                removeMemberFromSelection(userId);
            } else {
                // Select
                addMemberToSelection(userId, userName, userAvatar, userInitials);
                this.classList.add('bg-primary/10');
                if (checkbox) {
                    checkbox.classList.add('bg-primary', 'border-primary');
                    checkbox.classList.remove('border-base-300');
                }
                if (check) check.classList.remove('hidden');
            }

            // Clear search and keep dropdown open
            searchInput.value = '';
            options.forEach(o => o.style.display = 'flex');
            noResults?.classList.add('hidden');
        });
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        const selectedContainer = document.getElementById('modal-selected-members');
        if (!container.contains(e.target) && !dropdown.contains(e.target) && !selectedContainer?.contains(e.target)) {
            dropdown.classList.add('hidden');
        }
    });

    // Form validation
    const form = document.getElementById('invite-form');
    form?.addEventListener('submit', function(e) {
        if (selectedMembers.length === 0) {
            e.preventDefault();
            searchInput.focus();
            container.classList.add('border-error');
            setTimeout(() => container.classList.remove('border-error'), 2000);
        }
    });
});

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const inviteModal = document.getElementById('invite-modal');
        if (inviteModal && inviteModal.classList.contains('modal-open')) {
            closeInviteModal();
        }
    }
});
</script>
