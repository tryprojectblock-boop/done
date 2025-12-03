@extends('layouts.app')

@section('content')
<div class="p-4 md:p-6">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center gap-2 text-sm text-base-content/60 mb-2">
                <a href="{{ route('dashboard') }}" class="hover:text-primary">Dashboard</a>
                <span class="icon-[tabler--chevron-right] size-4"></span>
                <a href="{{ route('workspace.index') }}" class="hover:text-primary">Workspaces</a>
                <span class="icon-[tabler--chevron-right] size-4"></span>
                <span>Create</span>
            </div>
            <h1 class="text-2xl font-bold text-base-content">Create Workspace</h1>
            <p class="text-base-content/60">Set up a new workspace for your team</p>
        </div>

        <!-- Error Messages -->
        @if($errors->any())
            <div class="alert alert-error mb-4">
                <span class="icon-[tabler--alert-circle] size-5"></span>
                <div>
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            </div>
        @endif

        <form action="{{ route('workspace.store') }}" method="POST" id="workspace-form">
            @csrf

            <!-- Card 1: Workspace Type Selection -->
            <div class="card bg-base-100 shadow mb-6">
                <div class="card-body">
                    <h2 class="card-title text-lg mb-4">
                        <span class="icon-[tabler--layout-grid] size-5"></span>
                        Select Workspace Type
                    </h2>
                    <p class="text-sm text-base-content/60 mb-4">Choose the type of workspace that best fits your needs.</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Classic Workspace -->
                        <label class="cursor-pointer">
                            <input type="radio" name="type" value="classic" class="hidden peer" {{ old('type', 'classic') === 'classic' ? 'checked' : '' }}>
                            <div class="card bg-base-200 border-2 border-transparent peer-checked:border-primary peer-checked:bg-primary/5 transition-all hover:border-base-300">
                                <div class="card-body">
                                    <div class="flex items-center gap-3 mb-2">
                                        <div class="w-12 h-12 rounded-lg bg-blue-500 flex items-center justify-center text-white">
                                            <span class="icon-[tabler--briefcase] size-6"></span>
                                        </div>
                                        <div>
                                            <h3 class="font-semibold text-base-content">Classic Workspace</h3>
                                            <span class="badge badge-primary badge-sm">Recommended</span>
                                        </div>
                                    </div>
                                    <p class="text-sm text-base-content/60">Perfect for small teams. Includes message boards, to-dos, docs & files, chat, and scheduling.</p>
                                    <div class="flex flex-wrap gap-1 mt-3">
                                        <span class="badge badge-ghost badge-xs">Message Board</span>
                                        <span class="badge badge-ghost badge-xs">To-dos</span>
                                        <span class="badge badge-ghost badge-xs">Docs & Files</span>
                                        <span class="badge badge-ghost badge-xs">Chat</span>
                                        <span class="badge badge-ghost badge-xs">Schedule</span>
                                    </div>
                                </div>
                            </div>
                        </label>

                        <!-- Product Workspace -->
                        <label class="cursor-not-allowed opacity-60">
                            <input type="radio" name="type" value="product" class="hidden peer" disabled>
                            <div class="card bg-base-200 border-2 border-transparent transition-all">
                                <div class="card-body">
                                    <div class="flex items-center gap-3 mb-2">
                                        <div class="w-12 h-12 rounded-lg bg-purple-500 flex items-center justify-center text-white">
                                            <span class="icon-[tabler--rocket] size-6"></span>
                                        </div>
                                        <div>
                                            <h3 class="font-semibold text-base-content">Product Workspace</h3>
                                            <span class="badge badge-warning badge-sm">Coming Soon</span>
                                        </div>
                                    </div>
                                    <p class="text-sm text-base-content/60">For product teams. Includes backlog, epics, sprints, roadmap, user stories, and changelog.</p>
                                    <div class="flex flex-wrap gap-1 mt-3">
                                        <span class="badge badge-ghost badge-xs">Backlog</span>
                                        <span class="badge badge-ghost badge-xs">Epics</span>
                                        <span class="badge badge-ghost badge-xs">Sprints</span>
                                        <span class="badge badge-ghost badge-xs">Roadmap</span>
                                        <span class="badge badge-ghost badge-xs">Changelog</span>
                                    </div>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Card 2: Workspace Details -->
            <div class="card bg-base-100 shadow mb-6">
                <div class="card-body">
                    <h2 class="card-title text-lg mb-4">
                        <span class="icon-[tabler--settings] size-5"></span>
                        Workspace Details
                    </h2>

                    <!-- Workspace Name -->
                    <div class="form-control mb-4">
                        <label class="label">
                            <span class="label-text font-medium">Workspace Name <span class="text-error">*</span></span>
                        </label>
                        <input type="text" name="name" id="workspace-name" class="input input-bordered @error('name') input-error @enderror" placeholder="e.g. Marketing Team, Product Launch 2024" value="{{ old('name') }}" required maxlength="100">
                        @error('name')
                            <label class="label">
                                <span class="label-text-alt text-error">{{ $message }}</span>
                            </label>
                        @enderror
                    </div>

                    <!-- Workflow Selection -->
                    <div class="form-control mb-4">
                        <label class="label">
                            <span class="label-text font-medium">Workflow <span class="text-error">*</span></span>
                        </label>
                        <select name="workflow_id" id="workflow-select" class="select select-bordered @error('workflow_id') select-error @enderror" required>
                            <option value="">Select a workflow...</option>
                            @foreach($workflows as $workflow)
                                <option value="{{ $workflow->id }}" {{ old('workflow_id') == $workflow->id ? 'selected' : '' }}>
                                    {{ $workflow->name }}
                                    @if($workflow->isBuiltIn()) (Built-in) @endif
                                </option>
                            @endforeach
                        </select>
                        <label class="label">
                            <span class="label-text-alt text-base-content/60">Choose a workflow to manage task statuses in this workspace</span>
                        </label>
                        @error('workflow_id')
                            <label class="label">
                                <span class="label-text-alt text-error">{{ $message }}</span>
                            </label>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div class="form-control mb-4">
                        <label class="label">
                            <span class="label-text font-medium">Short Description <span class="text-base-content/50 font-normal">(Optional)</span></span>
                        </label>
                        <textarea name="description" class="textarea textarea-bordered" placeholder="Briefly describe what this workspace is for..." rows="3" maxlength="500">{{ old('description') }}</textarea>
                    </div>

                    <!-- Date Range -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Start Date <span class="text-base-content/50 font-normal">(Optional)</span></span>
                            </label>
                            <input type="date" name="start_date" class="input input-bordered" value="{{ old('start_date') }}">
                        </div>
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">End Date <span class="text-base-content/50 font-normal">(Optional)</span></span>
                            </label>
                            <input type="date" name="end_date" class="input input-bordered" value="{{ old('end_date') }}">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 3: Invite Team Members -->
            <div class="card bg-base-100 shadow mb-6">
                <div class="card-body">
                    <h2 class="card-title text-lg mb-4">
                        <span class="icon-[tabler--users] size-5"></span>
                        Invite Team Members
                        <span class="text-base-content/50 font-normal text-sm">(Optional)</span>
                    </h2>
                    <p class="text-sm text-base-content/60 mb-4">Add team members to collaborate in this workspace. You can always add more later.</p>

                    <!-- Member List -->
                    <div id="members-list" class="space-y-3">
                        <!-- Members will be added here dynamically -->
                    </div>

                    <!-- Add Member Row -->
                    <div class="flex flex-col md:flex-row gap-3 mt-4 p-4 bg-base-200 rounded-lg">
                        <div class="flex-1">
                            <select id="member-select" class="select select-bordered w-full">
                                <option value="">Select a team member...</option>
                                @foreach($teamMembers as $member)
                                    <option value="{{ $member->id }}" data-name="{{ $member->name }}" data-email="{{ $member->email }}">
                                        {{ $member->name }} ({{ $member->email }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="w-full md:w-48">
                            <select id="member-role" class="select select-bordered w-full">
                                <option value="">Select role...</option>
                                <option value="admin">Admin</option>
                                <option value="member">Member</option>
                                <option value="reviewer">Reviewer</option>
                            </select>
                        </div>
                        <button type="button" id="add-member-btn" class="btn btn-primary">
                            <span class="icon-[tabler--plus] size-5"></span>
                            Add
                        </button>
                    </div>

                    <!-- Role descriptions -->
                    <div class="mt-4 p-3 bg-info/10 border border-info/20 rounded-lg">
                        <p class="text-sm text-base-content/70">
                            <strong>Admin:</strong> Can manage members and settings.
                            <strong>Member:</strong> Can create and manage their own content.
                            <strong>Reviewer:</strong> Can review and comment on items.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Card 4: Invite Guests -->
            <div class="card bg-base-100 shadow mb-6">
                <div class="card-body">
                    <h2 class="card-title text-lg mb-4">
                        <span class="icon-[tabler--user-plus] size-5"></span>
                        Invite Guests
                        <span class="text-base-content/50 font-normal text-sm">(Optional)</span>
                    </h2>
                    <p class="text-sm text-base-content/60 mb-4">Add existing guests or invite new ones by email. Guests have limited access to this workspace.</p>

                    <!-- Guest List -->
                    <div id="guests-list" class="space-y-3">
                        <!-- Guests will be added here dynamically -->
                    </div>

                    @if($existingGuests->count() > 0)
                    <!-- Select Existing Guest -->
                    <div class="flex flex-col md:flex-row gap-3 mt-4 p-4 bg-base-200 rounded-lg">
                        <div class="flex-1">
                            <select id="guest-select" class="select select-bordered w-full">
                                <option value="">Select an existing guest...</option>
                                @foreach($existingGuests as $guest)
                                    <option value="{{ $guest->id }}"
                                            data-name="{{ $guest->full_name }}"
                                            data-email="{{ $guest->email }}">
                                        {{ $guest->full_name }} ({{ $guest->email }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button type="button" id="add-guest-select-btn" class="btn btn-primary">
                            <span class="icon-[tabler--plus] size-5"></span>
                            Add
                        </button>
                    </div>

                    <div class="divider text-sm text-base-content/50">OR invite new guest by email</div>
                    @endif

                    <!-- Invite by Email -->
                    <div class="flex flex-col md:flex-row gap-3 {{ $existingGuests->count() > 0 ? '' : 'mt-4' }} p-4 bg-base-200 rounded-lg">
                        <div class="flex-1">
                            <input type="email" id="guest-email" class="input input-bordered w-full" placeholder="Enter guest email address...">
                        </div>
                        <button type="button" id="add-guest-btn" class="btn btn-outline btn-primary">
                            <span class="icon-[tabler--send] size-5"></span>
                            Invite
                        </button>
                    </div>

                    <div class="mt-4 p-3 bg-warning/10 border border-warning/20 rounded-lg">
                        <p class="text-sm text-base-content/70 flex items-center gap-2">
                            <span class="icon-[tabler--info-circle] size-4"></span>
                            Guests have limited access to view and comment on items they are invited to.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-start gap-3">
                <button type="submit" class="btn btn-primary" id="submit-btn">
                    <span class="icon-[tabler--check] size-5"></span>
                    Create Workspace
                </button>
                <a href="{{ route('workspace.index') }}" class="btn btn-ghost">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const membersList = document.getElementById('members-list');
    const memberSelect = document.getElementById('member-select');
    const memberRole = document.getElementById('member-role');
    const addMemberBtn = document.getElementById('add-member-btn');

    const guestsList = document.getElementById('guests-list');
    const guestSelect = document.getElementById('guest-select');
    const addGuestSelectBtn = document.getElementById('add-guest-select-btn');
    const guestEmail = document.getElementById('guest-email');
    const addGuestBtn = document.getElementById('add-guest-btn');

    let memberIndex = 0;
    let guestIndex = 0;
    const addedMembers = new Set();
    const addedGuests = new Set(); // Track by email
    const addedGuestIds = new Set(); // Track by ID

    // Add member
    addMemberBtn.addEventListener('click', function() {
        const userId = memberSelect.value;
        const selectedOption = memberSelect.options[memberSelect.selectedIndex];

        if (!userId) {
            alert('Please select a team member');
            return;
        }

        const role = memberRole.value;
        if (!role) {
            alert('Please select a role');
            return;
        }

        if (addedMembers.has(userId)) {
            alert('This member has already been added');
            return;
        }

        const name = selectedOption.dataset.name;
        const email = selectedOption.dataset.email;
        const roleLabel = memberRole.options[memberRole.selectedIndex].text;

        const memberRow = document.createElement('div');
        memberRow.className = 'flex items-center justify-between p-3 bg-base-200 rounded-lg';
        memberRow.dataset.userId = userId;
        memberRow.innerHTML = `
            <div class="flex items-center gap-3">
                <div class="avatar placeholder">
                    <div class="bg-primary text-primary-content rounded-full w-10">
                        <span class="text-sm">${name.charAt(0).toUpperCase()}</span>
                    </div>
                </div>
                <div>
                    <p class="font-medium text-base-content">${name}</p>
                    <p class="text-sm text-base-content/60">${email}</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="badge badge-primary">${roleLabel}</span>
                <input type="hidden" name="members[${memberIndex}][user_id]" value="${userId}">
                <input type="hidden" name="members[${memberIndex}][role]" value="${role}">
                <button type="button" class="btn btn-ghost btn-sm btn-square text-error remove-member">
                    <span class="icon-[tabler--x] size-4"></span>
                </button>
            </div>
        `;

        membersList.appendChild(memberRow);
        addedMembers.add(userId);
        memberIndex++;

        // Reset select
        memberSelect.value = '';

        // Remove member event
        memberRow.querySelector('.remove-member').addEventListener('click', function() {
            addedMembers.delete(userId);
            memberRow.remove();
        });
    });

    // Add existing guest from dropdown
    if (addGuestSelectBtn) {
        addGuestSelectBtn.addEventListener('click', function() {
            if (!guestSelect) return;

            const guestId = guestSelect.value;
            const selectedOption = guestSelect.options[guestSelect.selectedIndex];

            if (!guestId) {
                alert('Please select a guest');
                return;
            }

            if (addedGuestIds.has(guestId)) {
                alert('This guest has already been added');
                return;
            }

            const name = selectedOption.dataset.name;
            const email = selectedOption.dataset.email;

            const guestRow = document.createElement('div');
            guestRow.className = 'flex items-center justify-between p-3 bg-base-200 rounded-lg';
            guestRow.dataset.guestId = guestId;
            guestRow.innerHTML = `
                <div class="flex items-center gap-3">
                    <div class="avatar placeholder">
                        <div class="bg-warning text-warning-content rounded-full w-10">
                            <span class="text-sm">${name.charAt(0).toUpperCase()}</span>
                        </div>
                    </div>
                    <div>
                        <p class="font-medium text-base-content">${name}</p>
                        <p class="text-sm text-base-content/60">${email}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="badge badge-warning">Guest</span>
                    <input type="hidden" name="guest_ids[]" value="${guestId}">
                    <button type="button" class="btn btn-ghost btn-sm btn-square text-error remove-guest">
                        <span class="icon-[tabler--x] size-4"></span>
                    </button>
                </div>
            `;

            guestsList.appendChild(guestRow);
            addedGuestIds.add(guestId);
            addedGuests.add(email.toLowerCase());
            guestIndex++;

            // Reset select
            guestSelect.value = '';

            // Remove guest event
            guestRow.querySelector('.remove-guest').addEventListener('click', function() {
                addedGuestIds.delete(guestId);
                addedGuests.delete(email.toLowerCase());
                guestRow.remove();
            });
        });
    }

    // Add guest by email
    addGuestBtn.addEventListener('click', function() {
        const email = guestEmail.value.trim();

        if (!email) {
            alert('Please enter an email address');
            return;
        }

        // Basic email validation
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            alert('Please enter a valid email address');
            return;
        }

        if (addedGuests.has(email.toLowerCase())) {
            alert('This guest has already been added');
            return;
        }

        const guestRow = document.createElement('div');
        guestRow.className = 'flex items-center justify-between p-3 bg-base-200 rounded-lg';
        guestRow.dataset.email = email.toLowerCase();
        guestRow.innerHTML = `
            <div class="flex items-center gap-3">
                <div class="avatar placeholder">
                    <div class="bg-warning text-warning-content rounded-full w-10">
                        <span class="icon-[tabler--mail] size-5"></span>
                    </div>
                </div>
                <div>
                    <p class="font-medium text-base-content">${email}</p>
                    <p class="text-sm text-base-content/60">Will receive an invitation email</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="badge badge-warning">New Invite</span>
                <input type="hidden" name="guest_emails[]" value="${email}">
                <button type="button" class="btn btn-ghost btn-sm btn-square text-error remove-guest">
                    <span class="icon-[tabler--x] size-4"></span>
                </button>
            </div>
        `;

        guestsList.appendChild(guestRow);
        addedGuests.add(email.toLowerCase());
        guestIndex++;

        // Reset input
        guestEmail.value = '';

        // Remove guest event
        guestRow.querySelector('.remove-guest').addEventListener('click', function() {
            addedGuests.delete(email.toLowerCase());
            guestRow.remove();
        });
    });

    // Allow adding guest on Enter key
    guestEmail.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            addGuestBtn.click();
        }
    });
});
</script>
@endsection
