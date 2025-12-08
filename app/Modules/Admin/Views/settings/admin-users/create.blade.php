@extends('admin::layouts.app')

@section('title', 'Add Admin User')
@section('page-title', 'Add Admin User')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <!-- Breadcrumb -->
    <div class="text-sm breadcrumbs">
        <ul>
            <li><a href="{{ route('backoffice.settings.admins.index') }}">Admin Users</a></li>
            <li>Add Admin</li>
        </ul>
    </div>

    <!-- Header -->
    <div>
        <h1 class="text-2xl font-bold text-base-content">Add Admin User</h1>
        <p class="text-base-content/60">Create a new administrator account</p>
    </div>

    <form action="{{ route('backoffice.settings.admins.store') }}" method="POST">
        @csrf

        <div class="card bg-base-100 shadow">
            <div class="card-body space-y-4">
                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-medium">Full Name</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name') }}" class="input input-bordered @error('name') input-error @enderror" required />
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
                    <input type="email" name="email" value="{{ old('email') }}" class="input input-bordered @error('email') input-error @enderror" required />
                    @error('email')
                        <label class="label">
                            <span class="label-text-alt text-error">{{ $message }}</span>
                        </label>
                    @enderror
                </div>

                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-medium">Password</span>
                    </label>
                    <input type="password" name="password" class="input input-bordered @error('password') input-error @enderror" required />
                    @error('password')
                        <label class="label">
                            <span class="label-text-alt text-error">{{ $message }}</span>
                        </label>
                    @enderror
                </div>

                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-medium">Confirm Password</span>
                    </label>
                    <input type="password" name="password_confirmation" class="input input-bordered" required />
                </div>

                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-medium">Role</span>
                    </label>
                    <select name="role" class="select select-bordered @error('role') select-error @enderror" required>
                        <option value="member" {{ old('role') === 'member' ? 'selected' : '' }}>Member</option>
                        <option value="administrator" {{ old('role') === 'administrator' ? 'selected' : '' }}>Administrator</option>
                    </select>
                    <label class="label">
                        <span class="label-text-alt text-base-content/60">Administrators can manage other admin users</span>
                    </label>
                    @error('role')
                        <label class="label">
                            <span class="label-text-alt text-error">{{ $message }}</span>
                        </label>
                    @enderror
                </div>

                <div class="form-control">
                    <label class="label cursor-pointer justify-start gap-4">
                        <input type="checkbox" name="is_active" value="1" class="toggle toggle-primary" {{ old('is_active', true) ? 'checked' : '' }} />
                        <div>
                            <span class="label-text font-medium">Active</span>
                            <p class="text-xs text-base-content/60">Allow this admin to login</p>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-2 mt-6">
            <a href="{{ route('backoffice.settings.admins.index') }}" class="btn btn-ghost">Cancel</a>
            <button type="submit" class="btn btn-primary">
                <span class="icon-[tabler--plus] size-5"></span>
                Create Admin
            </button>
        </div>
    </form>
</div>
@endsection
