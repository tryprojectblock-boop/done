@extends('layouts.guest')

@section('content')
<div class="card bg-base-100 shadow-xl">
    <div class="card-body">
        <h2 class="card-title justify-center text-2xl text-center text-primary-color mb-2">Forgot Password</h2>
        <p class="text-center text-base-content/60 mb-3 text-secondary-color">Enter your email address and we'll send you a link to reset your password.</p>

        <div id="forgot-password-form">
            <form id="forgotPasswordForm" class="space-y-4">
                <div class="form-control">
                    <label class="label" for="email">
                        <span class="label-text label-text-alt">Email Address</span>
                    </label>
                    <input type="email" id="email" name="email" class="input input-bordered w-full" placeholder="Enter your email" required />
                    <div id="email-error" class="text-error text-sm mt-1 hidden"></div>
                </div>

                <button type="submit" id="submitBtn" class="btn border-0 w-full btn-primary-color">
                    <span class="loading loading-spinner loading-sm hidden" id="loading"></span>
                    <span id="btnText">Send Reset Link</span>
                </button>
            </form>

            <div id="success-message" class="hidden">
                <div class="alert alert-success">
                    <span class="icon-[tabler--check] size-5"></span>
                    <span>If an account exists with this email, you will receive a password reset link.</span>
                </div>
            </div>
        </div>

        <div class="divider mt-3">OR</div>

        <div class="text-center">
            <a href="{{ route('login') }}" class="link text-link-color">Back to Login</a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('forgotPasswordForm');
    const submitBtn = document.getElementById('submitBtn');
    const loading = document.getElementById('loading');
    const btnText = document.getElementById('btnText');
    const emailError = document.getElementById('email-error');
    const successMessage = document.getElementById('success-message');

    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        // Reset errors
        emailError.classList.add('hidden');
        emailError.textContent = '';

        // Show loading
        loading.classList.remove('hidden');
        btnText.textContent = 'Sending...';
        submitBtn.disabled = true;

        const formData = new FormData(form);

        try {
            const response = await fetch('/api/v1/auth/forgot-password', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    email: formData.get('email')
                })
            });

            const data = await response.json();

            if (response.ok && data.success) {
                form.classList.add('hidden');
                successMessage.classList.remove('hidden');
            } else {
                if (data.errors && data.errors.email) {
                    emailError.textContent = data.errors.email[0];
                    emailError.classList.remove('hidden');
                } else if (data.message) {
                    emailError.textContent = data.message;
                    emailError.classList.remove('hidden');
                }
            }
        } catch (error) {
            emailError.textContent = 'An error occurred. Please try again.';
            emailError.classList.remove('hidden');
        } finally {
            loading.classList.add('hidden');
            btnText.textContent = 'Send Reset Link';
            submitBtn.disabled = false;
        }
    });
});
</script>
@endpush
