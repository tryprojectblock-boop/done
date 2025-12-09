@extends('layouts.guest')

@section('content')
<div class="card bg-base-100 shadow-xl">
    <div class="card-body">
        <h2 class="card-title justify-center text-2xl mb-2">Reset Password</h2>
        <p class="text-center text-base-content/60 mb-6">Enter your new password below.</p>

        <div id="reset-password-form">
            <form id="resetPasswordForm" class="space-y-4">
                <input type="hidden" name="token" value="{{ $token }}" />
                <input type="hidden" name="email" value="{{ $email }}" />

                <div class="form-control">
                    <label class="label" for="email-display">
                        <span class="label-text">Email Address</span>
                    </label>
                    <input type="email" id="email-display" class="input input-bordered w-full bg-base-200" value="{{ $email }}" disabled />
                </div>

                <div class="form-control">
                    <label class="label" for="password">
                        <span class="label-text">New Password</span>
                    </label>
                    <input type="password" id="password" name="password" class="input input-bordered w-full" placeholder="Enter new password" required minlength="8" />
                    <div id="password-error" class="text-error text-sm mt-1 hidden"></div>
                </div>

                <div class="form-control">
                    <label class="label" for="password_confirmation">
                        <span class="label-text">Confirm Password</span>
                    </label>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="input input-bordered w-full" placeholder="Confirm new password" required minlength="8" />
                    <div id="password-confirmation-error" class="text-error text-sm mt-1 hidden"></div>
                </div>

                <div id="general-error" class="alert alert-error hidden">
                    <span class="icon-[tabler--alert-circle] size-5"></span>
                    <span id="general-error-text"></span>
                </div>

                <button type="submit" id="submitBtn" class="btn btn-primary w-full">
                    <span class="loading loading-spinner loading-sm hidden" id="loading"></span>
                    <span id="btnText">Reset Password</span>
                </button>
            </form>

            <div id="success-message" class="hidden">
                <div class="alert alert-success mb-4">
                    <span class="icon-[tabler--check] size-5"></span>
                    <span>Your password has been reset successfully!</span>
                </div>
                <a href="{{ route('login') }}" class="btn btn-primary w-full">Go to Login</a>
            </div>
        </div>

        <div class="divider">OR</div>

        <div class="text-center">
            <a href="{{ route('login') }}" class="link link-primary">Back to Login</a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('resetPasswordForm');
    const submitBtn = document.getElementById('submitBtn');
    const loading = document.getElementById('loading');
    const btnText = document.getElementById('btnText');
    const passwordError = document.getElementById('password-error');
    const passwordConfirmationError = document.getElementById('password-confirmation-error');
    const generalError = document.getElementById('general-error');
    const generalErrorText = document.getElementById('general-error-text');
    const successMessage = document.getElementById('success-message');

    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        // Reset errors
        passwordError.classList.add('hidden');
        passwordConfirmationError.classList.add('hidden');
        generalError.classList.add('hidden');
        passwordError.textContent = '';
        passwordConfirmationError.textContent = '';

        const password = document.getElementById('password').value;
        const passwordConfirmation = document.getElementById('password_confirmation').value;

        // Client-side validation
        if (password.length < 8) {
            passwordError.textContent = 'Password must be at least 8 characters.';
            passwordError.classList.remove('hidden');
            return;
        }

        if (password !== passwordConfirmation) {
            passwordConfirmationError.textContent = 'Passwords do not match.';
            passwordConfirmationError.classList.remove('hidden');
            return;
        }

        // Show loading
        loading.classList.remove('hidden');
        btnText.textContent = 'Resetting...';
        submitBtn.disabled = true;

        const formData = new FormData(form);

        try {
            const response = await fetch('/api/v1/auth/reset-password', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    token: formData.get('token'),
                    email: formData.get('email'),
                    password: password,
                    password_confirmation: passwordConfirmation
                })
            });

            const data = await response.json();

            if (response.ok && data.success) {
                form.classList.add('hidden');
                successMessage.classList.remove('hidden');
            } else {
                if (data.errors) {
                    if (data.errors.password) {
                        passwordError.textContent = data.errors.password[0];
                        passwordError.classList.remove('hidden');
                    }
                    if (data.errors.token || data.errors.email) {
                        generalErrorText.textContent = data.errors.token?.[0] || data.errors.email?.[0];
                        generalError.classList.remove('hidden');
                    }
                } else if (data.message) {
                    generalErrorText.textContent = data.message;
                    generalError.classList.remove('hidden');
                }
            }
        } catch (error) {
            generalErrorText.textContent = 'An error occurred. Please try again.';
            generalError.classList.remove('hidden');
        } finally {
            loading.classList.add('hidden');
            btnText.textContent = 'Reset Password';
            submitBtn.disabled = false;
        }
    });
});
</script>
@endpush
