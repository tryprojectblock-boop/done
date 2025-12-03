@extends('layouts.app')

@section('content')
<div class="p-4 md:p-6">
    <div class="max-w-3xl mx-auto">
        <!-- Page Header -->
        <div class="mb-6">
            <a href="{{ route('users.index') }}" class="btn btn-ghost btn-sm gap-1 mb-4">
                <span class="icon-[tabler--arrow-left] size-4"></span>
                Back to Team Members
            </a>
            <h1 class="text-2xl font-bold text-base-content">Invite Members</h1>
            <p class="text-base-content/60">Invite up to 8 team members at once. They'll receive an email with a link to complete their signup.</p>
        </div>

        <!-- Invite Form -->
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <form id="invite-form" onsubmit="submitInvites(event)">
                    <div id="members-container">
                        <!-- Member Row Template -->
                        <div class="member-row grid grid-cols-1 md:grid-cols-12 gap-4 mb-4 pb-4 border-b border-base-200" data-index="0">
                            <div class="md:col-span-4">
                                <label class="label"><span class="label-text">First Name <span class="text-error">*</span></span></label>
                                <input type="text" name="members[0][first_name]" placeholder="John" class="input input-bordered w-full" required pattern="[A-Za-z\s\-']+" title="Only letters, spaces, hyphens and apostrophes allowed" />
                            </div>
                            <div class="md:col-span-5">
                                <label class="label"><span class="label-text">Email Address <span class="text-error">*</span></span></label>
                                <input type="email" name="members[0][email]" placeholder="john@example.com" class="input input-bordered w-full" required />
                            </div>
                            <div class="md:col-span-2">
                                <label class="label"><span class="label-text">Role <span class="text-error">*</span></span></label>
                                <select name="members[0][role]" class="select select-bordered w-full" required>
                                    <option value="" disabled selected>Select role</option>
                                    @foreach($roles as $key => $role)
                                        @if($key !== 'owner' && $key !== 'guest' || ($key === 'owner' && auth()->user()->isOwner()))
                                            <option value="{{ $key }}">{{ $role['label'] }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <div class="md:col-span-1 flex items-end justify-center pb-2">
                                <button type="button" class="btn btn-ghost btn-sm btn-circle text-error remove-member-btn hidden" onclick="removeMember(this)" title="Remove">
                                    <span class="icon-[tabler--trash] size-5"></span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Add More Button -->
                    <div id="add-more-container" class="mb-6">
                        <button type="button" id="add-member-btn" class="btn btn-ghost btn-sm gap-1" onclick="addMember()">
                            <span class="icon-[tabler--plus] size-4"></span>
                            Add Another Member
                        </button>
                        <span class="text-sm text-base-content/50 ml-2">(<span id="member-count">1</span>/8 members)</span>
                    </div>

                    <!-- Error Display -->
                    <div id="invite-errors" class="alert alert-error mb-4 hidden">
                        <span class="icon-[tabler--alert-circle] size-5"></span>
                        <div id="invite-errors-content"></div>
                    </div>

                    <!-- Success Display -->
                    <div id="invite-success" class="alert alert-success mb-4 hidden">
                        <span class="icon-[tabler--check] size-5"></span>
                        <div id="invite-success-content"></div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end gap-3">
                        <a href="{{ route('users.index') }}" class="btn btn-ghost">Cancel</a>
                        <button type="submit" id="submit-btn" class="btn btn-primary gap-1">
                            <span class="icon-[tabler--send] size-5"></span>
                            Send Invitations
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Info Box -->
        <div class="mt-6 p-4 bg-info/10 border border-info/20 rounded-lg">
            <h4 class="font-semibold text-info mb-2 flex items-center gap-2">
                <span class="icon-[tabler--info-circle] size-5"></span>
                How it works
            </h4>
            <ul class="text-sm text-base-content/70 space-y-1 ml-7 list-disc">
                <li>Invited members will receive an email with a unique signup link</li>
                <li>The link expires in 7 days</li>
                <li>They can complete their profile including password and timezone</li>
                <li>You can resend invitations from the team members page if needed</li>
            </ul>
        </div>
    </div>
</div>

@push('scripts')
<script>
let memberCount = 1;
const maxMembers = 8;

function addMember() {
    if (memberCount >= maxMembers) {
        alert('Maximum of 8 members can be invited at once.');
        return;
    }

    const container = document.getElementById('members-container');
    const index = memberCount;

    const memberRow = document.createElement('div');
    memberRow.className = 'member-row grid grid-cols-1 md:grid-cols-12 gap-4 mb-4 pb-4 border-b border-base-200';
    memberRow.dataset.index = index;

    memberRow.innerHTML = `
        <div class="md:col-span-4">
            <label class="label"><span class="label-text">First Name <span class="text-error">*</span></span></label>
            <input type="text" name="members[${index}][first_name]" placeholder="John" class="input input-bordered w-full" required pattern="[A-Za-z\\s\\-']+" title="Only letters, spaces, hyphens and apostrophes allowed" />
        </div>
        <div class="md:col-span-5">
            <label class="label"><span class="label-text">Email Address <span class="text-error">*</span></span></label>
            <input type="email" name="members[${index}][email]" placeholder="john@example.com" class="input input-bordered w-full" required />
        </div>
        <div class="md:col-span-2">
            <label class="label"><span class="label-text">Role <span class="text-error">*</span></span></label>
            <select name="members[${index}][role]" class="select select-bordered w-full" required>
                <option value="" disabled selected>Select role</option>
                @foreach($roles as $key => $role)
                    @if($key !== 'owner' && $key !== 'guest' || ($key === 'owner' && auth()->user()->isOwner()))
                        <option value="{{ $key }}">{{ $role['label'] }}</option>
                    @endif
                @endforeach
            </select>
        </div>
        <div class="md:col-span-1 flex items-end justify-center pb-2">
            <button type="button" class="btn btn-ghost btn-sm btn-circle text-error remove-member-btn" onclick="removeMember(this)" title="Remove">
                <span class="icon-[tabler--trash] size-5"></span>
            </button>
        </div>
    `;

    container.appendChild(memberRow);
    memberCount++;
    updateMemberCount();
    updateRemoveButtons();
}

function removeMember(btn) {
    const row = btn.closest('.member-row');
    row.remove();
    memberCount--;
    reindexMembers();
    updateMemberCount();
    updateRemoveButtons();
}

function reindexMembers() {
    const rows = document.querySelectorAll('.member-row');
    rows.forEach((row, index) => {
        row.dataset.index = index;
        row.querySelectorAll('input, select').forEach(input => {
            const name = input.name;
            input.name = name.replace(/members\[\d+\]/, `members[${index}]`);
        });
    });
}

function updateMemberCount() {
    document.getElementById('member-count').textContent = memberCount;

    const addBtn = document.getElementById('add-member-btn');
    if (memberCount >= maxMembers) {
        addBtn.disabled = true;
        addBtn.classList.add('btn-disabled');
    } else {
        addBtn.disabled = false;
        addBtn.classList.remove('btn-disabled');
    }
}

function updateRemoveButtons() {
    const removeButtons = document.querySelectorAll('.remove-member-btn');
    removeButtons.forEach(btn => {
        if (memberCount === 1) {
            btn.classList.add('hidden');
        } else {
            btn.classList.remove('hidden');
        }
    });
}

async function submitInvites(e) {
    e.preventDefault();

    const form = e.target;
    const submitBtn = document.getElementById('submit-btn');
    const errorsDiv = document.getElementById('invite-errors');
    const errorsContent = document.getElementById('invite-errors-content');
    const successDiv = document.getElementById('invite-success');
    const successContent = document.getElementById('invite-success-content');

    // Hide previous messages
    errorsDiv.classList.add('hidden');
    successDiv.classList.add('hidden');

    // Collect form data
    const formData = new FormData(form);
    const members = [];

    for (let i = 0; i < memberCount; i++) {
        const firstName = formData.get(`members[${i}][first_name]`);
        const email = formData.get(`members[${i}][email]`);
        const role = formData.get(`members[${i}][role]`);

        if (firstName && email && role) {
            members.push({ first_name: firstName, email: email, role: role });
        }
    }

    if (members.length === 0) {
        errorsContent.textContent = 'Please add at least one member to invite.';
        errorsDiv.classList.remove('hidden');
        return;
    }

    // Disable submit button
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="loading loading-spinner loading-sm"></span> Sending Invitations...';

    try {
        const response = await fetch('{{ route("users.invite.send") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ members: members }),
        });

        const data = await response.json();

        if (data.success) {
            successContent.innerHTML = `
                <p class="font-medium">${data.message}</p>
                <p class="text-sm mt-1">${data.invited_count} invitation(s) sent successfully.</p>
            `;
            successDiv.classList.remove('hidden');

            // Redirect after 2 seconds
            setTimeout(() => {
                window.location.href = '{{ route("users.index") }}';
            }, 2000);
        } else {
            let errorHtml = '<ul class="list-disc ml-4">';
            if (data.errors) {
                for (const key in data.errors) {
                    data.errors[key].forEach(error => {
                        errorHtml += `<li>${error}</li>`;
                    });
                }
            } else {
                errorHtml += `<li>${data.error || data.message || 'An error occurred'}</li>`;
            }
            errorHtml += '</ul>';
            errorsContent.innerHTML = errorHtml;
            errorsDiv.classList.remove('hidden');
        }
    } catch (error) {
        errorsContent.textContent = 'An error occurred. Please try again.';
        errorsDiv.classList.remove('hidden');
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<span class="icon-[tabler--send] size-5"></span> Send Invitations';
    }
}
</script>
@endpush
@endsection
