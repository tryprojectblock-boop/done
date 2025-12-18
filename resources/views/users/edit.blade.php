@extends('layouts.app')

@section('content')
<div class="p-4 md:p-6">
    <div class="max-w-2xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <a href="{{ route('users.index') }}" class="btn btn-ghost btn-sm gap-1 mb-4">
                <span class="icon-[tabler--arrow-left] size-4"></span>
                Back to Users
            </a>
            <h1 class="text-2xl font-bold text-base-content">Edit User</h1>
            <p class="text-base-content/60">Update {{ $user->full_name }}'s information</p>
        </div>

        <!-- Form -->
        <form id="edit-user-form">
            @csrf
            @method('PUT')
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <!-- User Avatar & Info Header -->
                    <div class="flex items-center gap-4 mb-6 pb-6 border-b border-base-200">
                        @include('partials.user-avatar', ['user' => $user, 'size' => 'lg'])
                        <div>
                            <h3 class="text-lg font-semibold flex items-center gap-2">
                                {{ $user->full_name }}
                                @if($isCompanyOwner)
                                    <span class="icon-[tabler--crown] size-5 text-warning" title="Company Owner"></span>
                                @endif
                            </h3>
                            <p class="text-base-content/60">{{ $user->email }}</p>
                            <span class="badge badge-{{ $user->role_color }} badge-sm mt-1">{{ $user->role_label }}</span>
                        </div>
                    </div>

                    <!-- Read-only Fields -->
                    <div class="bg-base-200/50 rounded-lg p-4 mb-6">
                        <h4 class="text-sm font-medium text-base-content/70 mb-3">Read-only Information</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Email (Read Only) -->
                            <div class="form-control">
                                <span class="label">
                                    <span class="label-text font-medium">Email Address</span>
                                    <span class="label-text-alt">
                                        <span class="icon-[tabler--lock] size-4 text-base-content/50"></span>
                                    </span>
                                </span>
                                <input type="email" value="{{ $user->email }}" class="input input-bordered bg-base-200" readonly disabled>
                            </div>

                            <!-- Joined Date (Read Only) -->
                            <div class="form-control">
                                <span class="label">
                                    <span class="label-text font-medium">Joined</span>
                                    <span class="label-text-alt">
                                        <span class="icon-[tabler--lock] size-4 text-base-content/50"></span>
                                    </span>
                                </span>
                                <input type="text" value="{{ $user->created_at->format('M d, Y') }}" class="input input-bordered bg-base-200" readonly disabled>
                            </div>

                            <!-- Last Login (Read Only) -->
                            <div class="form-control">
                                <span class="label">
                                    <span class="label-text font-medium">Last Login</span>
                                    <span class="label-text-alt">
                                        <span class="icon-[tabler--lock] size-4 text-base-content/50"></span>
                                    </span>
                                </span>
                                <input type="text" value="{{ $user->last_login_at ? $user->last_login_at->format('M d, Y h:i A') : 'Never' }}" class="input input-bordered bg-base-200" readonly disabled>
                            </div>

                            <!-- Timezone (Read Only) -->
                            <div class="form-control">
                                <span class="label">
                                    <span class="label-text font-medium">Timezone</span>
                                    <span class="label-text-alt">
                                        <span class="icon-[tabler--lock] size-4 text-base-content/50"></span>
                                    </span>
                                </span>
                                <input type="text" value="{{ $user->timezone ?? 'UTC' }}" class="input input-bordered bg-base-200" readonly disabled>
                            </div>
                        </div>
                    </div>

                    <div class="divider">Editable Fields</div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- First Name -->
                        <div class="form-control">
                            <label class="label" for="user-first-name">
                                <span class="label-text font-medium">First Name <span class="text-error">*</span></span>
                            </label>
                            <input type="text" name="first_name" id="user-first-name" value="{{ $user->first_name }}" placeholder="John" class="input input-bordered" required pattern="[A-Za-z\s\-']+" title="Only letters, spaces, hyphens and apostrophes allowed">
                        </div>

                        <!-- Last Name -->
                        <div class="form-control">
                            <label class="label" for="user-last-name">
                                <span class="label-text font-medium">Last Name <span class="text-error">*</span></span>
                            </label>
                            <input type="text" name="last_name" id="user-last-name" value="{{ $user->last_name }}" placeholder="Doe" class="input input-bordered" required pattern="[A-Za-z\s\-']+" title="Only letters, spaces, hyphens and apostrophes allowed">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <!-- Role -->
                        <div class="form-control">
                            <label class="label" for="user-role">
                                <span class="label-text font-medium">Role <span class="text-error">*</span></span>
                            </label>
                            @if($isCompanyOwner)
                                <input type="hidden" name="role" value="{{ $user->role }}">
                                <div class="input input-bordered bg-base-200 flex items-center">
                                    <span class="badge badge-{{ $user->role_color }}">{{ $user->role_label }}</span>
                                    <span class="text-xs text-base-content/50 ml-2">(Company owner role cannot be changed)</span>
                                </div>
                            @else
                                <select name="role" id="user-role" class="select select-bordered" required>
                                    @foreach($roles as $key => $role)
                                        <option value="{{ $key }}" {{ $user->role === $key ? 'selected' : '' }}>{{ $role['label'] }}</option>
                                    @endforeach
                                </select>
                                <span class="label">
                                    <span class="label-text-alt text-base-content/50">
                                        @if($user->role === 'owner')
                                            Changing from Owner will revoke full access
                                        @endif
                                    </span>
                                </span>
                            @endif
                        </div>

                        <!-- Status -->
                        <div class="form-control">
                            <label class="label" for="user-status">
                                <span class="label-text font-medium">Status</span>
                            </label>
                            @if($isCompanyOwner)
                                <input type="hidden" name="status" value="{{ $user->status }}">
                                <div class="input input-bordered bg-base-200 flex items-center">
                                    <span class="badge badge-success">Active</span>
                                    <span class="text-xs text-base-content/50 ml-2">(Company owner cannot be suspended)</span>
                                </div>
                            @elseif($user->status === 'invited')
                                <div class="input input-bordered bg-base-200 flex items-center">
                                    <span class="badge badge-warning">Invited</span>
                                    <span class="text-xs text-base-content/50 ml-2">(Pending acceptance)</span>
                                </div>
                            @else
                                <select name="status" id="user-status" class="select select-bordered">
                                    @foreach($statuses as $key => $label)
                                        <option value="{{ $key }}" {{ $user->status === $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <span class="label">
                                    <span class="label-text-alt text-base-content/50">
                                        Suspended users cannot log in
                                    </span>
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Role Permissions Info -->
                    <div class="mt-6">
                        <div class="collapse collapse-arrow bg-base-200/50 rounded-lg">
                            <input type="checkbox" class="peer hidden" id="role-permissions-toggle" />
                            <label for="role-permissions-toggle" class="collapse-title font-medium cursor-pointer">
                                <span class="icon-[tabler--info-circle] size-4 mr-2"></span>
                                Role Permissions Guide
                            </label>
                            <div class="collapse-content">
                                <div class="space-y-3 text-sm">
                                    <div class="flex gap-3">
                                        <span class="badge badge-error badge-sm">Owner</span>
                                        <span class="text-base-content/70">Full access including billing, subscription, and user management</span>
                                    </div>
                                    <div class="flex gap-3">
                                        <span class="badge badge-warning badge-sm">Admin</span>
                                        <span class="text-base-content/70">Manage projects, invite members & guests, workspace settings</span>
                                    </div>
                                    <div class="flex gap-3">
                                        <span class="badge badge-info badge-sm">Member</span>
                                        <span class="text-base-content/70">Day-to-day work: create tasks, comment, upload files</span>
                                    </div>
                                    <div class="flex gap-3">
                                        <span class="badge badge-neutral badge-sm">Guest</span>
                                        <span class="text-base-content/70">Limited access to explicitly shared projects only</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body border-t border-base-200 pt-4">
                    <div class="flex justify-between items-center">
                        <div>
                            @if($isCompanyOwner)
                                <span class="text-sm text-base-content/50">Company owner cannot be removed</span>
                            @elseif($user->id === auth()->id())
                                <span class="text-sm text-base-content/50">You cannot remove yourself</span>
                            @else
                                <button type="button" class="btn btn-error btn-sm" onclick="confirmDelete()">
                                    <span class="icon-[tabler--trash] size-4"></span>
                                    Remove User
                                </button>
                            @endif
                        </div>
                        <div class="flex gap-3">
                            <a href="{{ route('users.index') }}" class="btn btn-ghost">Cancel</a>
                            <button type="submit" id="submit-btn" class="btn btn-primary">
                                <span class="icon-[tabler--check] size-5"></span>
                                Save Changes
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@include('users.partials.delete-user-modal', ['user' => $user])
@endsection

@push('scripts')
<script>
// Form submission
document.getElementById('edit-user-form').addEventListener('submit', function(e) {
    e.preventDefault();

    const submitBtn = document.getElementById('submit-btn');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="loading loading-spinner loading-sm"></span> Saving...';

    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());

    fetch('{{ route("users.update", $user) }}', {
        method: 'PUT',
        body: JSON.stringify(data),
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = '{{ route("users.index") }}';
        } else if (data.errors) {
            let errorMessages = [];
            for (const [field, messages] of Object.entries(data.errors)) {
                errorMessages.push(...messages);
            }
            alert(errorMessages.join('\n'));
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        } else {
            alert(data.error || 'An error occurred');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
});

// Delete user - calls the reusable modal
function confirmDelete() {
    openDeleteModal(
        {{ $user->id }},
        '{{ $user->full_name }}',
        '{{ route("users.work-data", $user) }}',
        '{{ route("users.destroy-with-reassignment", $user) }}',
        '{{ route("users.index") }}'
    );
}
</script>
@endpush
