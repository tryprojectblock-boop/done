@extends('layouts.app')

@section('content')
<div class="min-h-screen p-4 md:p-6">
    <div class="max-w-xl mx-auto">
        <!-- Page Header -->
        <div class="mb-6">
            <div class="flex items-center gap-2 text-sm text-base-content/60 mb-2">
                <a href="{{ route('profile.index') }}" class="hover:text-primary">My Profile</a>
                <span class="icon-[tabler--chevron-right] size-4"></span>
                <span>Change Password</span>
            </div>
            <h1 class="text-2xl font-bold text-base-content">Change Password</h1>
            <p class="text-base-content/60">Update your password to keep your account secure</p>
        </div>

        <!-- Success Message -->
        @if(session('success'))
            <div class="alert alert-success mb-6">
                <span class="icon-[tabler--circle-check] size-5"></span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Error Message -->
        @if($errors->any())
            <div class="alert alert-error mb-6">
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

        <!-- Password Form Card -->
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <form action="{{ route('profile.password.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <!-- Current Password -->
                    <div class="mb-6">
                        <label class="label" for="current_password">
                            <span class="label-text font-medium">Current Password <span class="text-error">*</span></span>
                        </label>
                        <div class="relative">
                            <input
                                type="password"
                                id="current_password"
                                name="current_password"
                                class="input input-bordered w-full pr-10 @error('current_password') input-error @enderror"
                                placeholder="Enter your current password"
                                required
                            />
                            <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-base-content/50 hover:text-base-content" onclick="togglePassword('current_password', this)">
                                <span class="icon-[tabler--eye] size-5 show-icon"></span>
                                <span class="icon-[tabler--eye-off] size-5 hide-icon hidden"></span>
                            </button>
                        </div>
                    </div>

                    <!-- New Password -->
                    <div class="mb-6">
                        <label class="label" for="password">
                            <span class="label-text font-medium">New Password <span class="text-error">*</span></span>
                        </label>
                        <div class="relative">
                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="input input-bordered w-full pr-10 @error('password') input-error @enderror"
                                placeholder="Enter your new password"
                                required
                            />
                            <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-base-content/50 hover:text-base-content" onclick="togglePassword('password', this)">
                                <span class="icon-[tabler--eye] size-5 show-icon"></span>
                                <span class="icon-[tabler--eye-off] size-5 hide-icon hidden"></span>
                            </button>
                        </div>
                        <div class="mt-2">
                            <p class="text-xs text-base-content/60 mb-1">Password must contain:</p>
                            <ul class="text-xs text-base-content/50 space-y-1">
                                <li class="flex items-center gap-1">
                                    <span class="icon-[tabler--point] size-3"></span>
                                    At least 8 characters
                                </li>
                                <li class="flex items-center gap-1">
                                    <span class="icon-[tabler--point] size-3"></span>
                                    Uppercase and lowercase letters
                                </li>
                                <li class="flex items-center gap-1">
                                    <span class="icon-[tabler--point] size-3"></span>
                                    At least one number
                                </li>
                                <li class="flex items-center gap-1">
                                    <span class="icon-[tabler--point] size-3"></span>
                                    At least one special character
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Confirm Password -->
                    <div class="mb-8">
                        <label class="label" for="password_confirmation">
                            <span class="label-text font-medium">Confirm Password <span class="text-error">*</span></span>
                        </label>
                        <div class="relative">
                            <input
                                type="password"
                                id="password_confirmation"
                                name="password_confirmation"
                                class="input input-bordered w-full pr-10"
                                placeholder="Confirm your new password"
                                required
                            />
                            <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-base-content/50 hover:text-base-content" onclick="togglePassword('password_confirmation', this)">
                                <span class="icon-[tabler--eye] size-5 show-icon"></span>
                                <span class="icon-[tabler--eye-off] size-5 hide-icon hidden"></span>
                            </button>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end gap-3">
                        <a href="{{ route('profile.index') }}" class="btn btn-ghost">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <span class="icon-[tabler--lock] size-5"></span>
                            Update Password
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Security Tips -->
        <div class="card bg-base-100 shadow mt-6">
            <div class="card-body">
                <h3 class="font-semibold flex items-center gap-2">
                    <span class="icon-[tabler--shield-check] size-5 text-success"></span>
                    Security Tips
                </h3>
                <ul class="text-sm text-base-content/70 space-y-2 mt-2">
                    <li class="flex items-start gap-2">
                        <span class="icon-[tabler--check] size-4 text-success mt-0.5"></span>
                        Use a unique password that you don't use for other accounts
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="icon-[tabler--check] size-4 text-success mt-0.5"></span>
                        Avoid using personal information like birthdays or names
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="icon-[tabler--check] size-4 text-success mt-0.5"></span>
                        Consider using a password manager to generate and store passwords
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function togglePassword(inputId, button) {
    const input = document.getElementById(inputId);
    const showIcon = button.querySelector('.show-icon');
    const hideIcon = button.querySelector('.hide-icon');

    if (input.type === 'password') {
        input.type = 'text';
        showIcon.classList.add('hidden');
        hideIcon.classList.remove('hidden');
    } else {
        input.type = 'password';
        showIcon.classList.remove('hidden');
        hideIcon.classList.add('hidden');
    }
}
</script>
@endpush
@endsection
