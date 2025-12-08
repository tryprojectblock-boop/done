@extends('admin::layouts.app')

@section('title', 'Edit Admin User')
@section('page-title', 'Edit Admin User')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <!-- Breadcrumb -->
    <div class="text-sm breadcrumbs">
        <ul>
            <li><a href="{{ route('backoffice.settings.admins.index') }}">Admin Users</a></li>
            <li>Edit Admin</li>
        </ul>
    </div>

    <!-- Header -->
    <div>
        <h1 class="text-2xl font-bold text-base-content">Edit Admin User</h1>
        <p class="text-base-content/60">Update administrator account details</p>
    </div>

    <form action="{{ route('backoffice.settings.admins.update', $admin) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="card bg-base-100 shadow">
            <div class="card-body space-y-4">
                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-medium">Full Name</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name', $admin->name) }}" class="input input-bordered @error('name') input-error @enderror" required />
                    @error('name')
                        <label class="label">
                            <span class="label-text-alt text-error">{{ $message }}</span>
                        </label>
                    @enderror
                </div>

                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-medium">Email Address</span>
                    </label>
                    <input type="email" name="email" value="{{ old('email', $admin->email) }}" class="input input-bordered @error('email') input-error @enderror" required />
                    @error('email')
                        <label class="label">
                            <span class="label-text-alt text-error">{{ $message }}</span>
                        </label>
                    @enderror
                </div>

                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-medium">New Password</span>
                    </label>
                    <input type="password" name="password" class="input input-bordered @error('password') input-error @enderror" />
                    <label class="label">
                        <span class="label-text-alt text-base-content/60">Leave blank to keep current password</span>
                    </label>
                    @error('password')
                        <label class="label">
                            <span class="label-text-alt text-error">{{ $message }}</span>
                        </label>
                    @enderror
                </div>

                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-medium">Confirm New Password</span>
                    </label>
                    <input type="password" name="password_confirmation" class="input input-bordered" />
                </div>

                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-medium">Role</span>
                    </label>
                    <select name="role" class="select select-bordered @error('role') select-error @enderror" required {{ $admin->id === auth('admin')->id() ? 'disabled' : '' }}>
                        <option value="member" {{ old('role', $admin->role->value) === 'member' ? 'selected' : '' }}>Member</option>
                        <option value="administrator" {{ old('role', $admin->role->value) === 'administrator' ? 'selected' : '' }}>Administrator</option>
                    </select>
                    @if($admin->id === auth('admin')->id())
                        <input type="hidden" name="role" value="{{ $admin->role->value }}" />
                        <label class="label">
                            <span class="label-text-alt text-warning">You cannot change your own role</span>
                        </label>
                    @else
                        <label class="label">
                            <span class="label-text-alt text-base-content/60">Administrators can manage other admin users</span>
                        </label>
                    @endif
                    @error('role')
                        <label class="label">
                            <span class="label-text-alt text-error">{{ $message }}</span>
                        </label>
                    @enderror
                </div>

                <div class="form-control">
                    <label class="label cursor-pointer justify-start gap-4">
                        <input type="checkbox" name="is_active" value="1" class="toggle toggle-primary" {{ old('is_active', $admin->is_active) ? 'checked' : '' }} {{ $admin->id === auth('admin')->id() ? 'disabled' : '' }} />
                        <div>
                            <span class="label-text font-medium">Active</span>
                            <p class="text-xs text-base-content/60">Allow this admin to login</p>
                        </div>
                    </label>
                    @if($admin->id === auth('admin')->id())
                        <input type="hidden" name="is_active" value="1" />
                        <label class="label">
                            <span class="label-text-alt text-warning">You cannot deactivate your own account</span>
                        </label>
                    @endif
                </div>

                <!-- Account Info -->
                <div class="divider"></div>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-base-content/60">Created</p>
                        <p class="font-medium">{{ $admin->created_at->format('M d, Y H:i') }}</p>
                    </div>
                    <div>
                        <p class="text-base-content/60">Last Login</p>
                        <p class="font-medium">{{ $admin->last_login_at ? $admin->last_login_at->format('M d, Y H:i') : 'Never' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-2 mt-6">
            <a href="{{ route('backoffice.settings.admins.index') }}" class="btn btn-ghost">Cancel</a>
            <button type="submit" class="btn btn-primary">
                <span class="icon-[tabler--device-floppy] size-5"></span>
                Save Changes
            </button>
        </div>
    </form>
</div>
@endsection
