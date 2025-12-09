<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Complete Your Profile - {{ config('app.name', 'NewDone') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    <!-- Styles / Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-base-200 flex items-center justify-center p-4">
    <div class="w-full max-w-lg">
        <!-- Logo -->
        <div class="text-center mb-8">
            <div class="flex items-center justify-center gap-2 mb-2">
                <span class="icon-[tabler--checkbox] size-10 text-primary"></span>
                <span class="text-3xl font-bold text-base-content">{{ config('app.name') }}</span>
            </div>
            <p class="text-base-content/60">Complete your profile to join the team</p>
        </div>

        <!-- Signup Card -->
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <!-- Welcome Message -->
                <div class="text-center mb-6">
                    <h2 class="text-xl font-bold text-base-content">Welcome, {{ $user->first_name }}!</h2>
                    <p class="text-base-content/60 text-sm mt-1">
                        You've been invited to join as
                        <span class="badge badge-{{ $user->role_color }} badge-sm">{{ $user->role_label }}</span>
                    </p>
                </div>

                <form id="signup-form" enctype="multipart/form-data">
                    <!-- Profile Image -->
                    <div class="flex flex-col items-center mb-6">
                        <div class="avatar placeholder mb-3">
                            <div id="avatar-preview" class="bg-primary text-primary-content rounded-full w-24 h-24 flex items-center justify-center overflow-hidden">
                                <span id="avatar-initials" class="text-3xl font-semibold">{{ substr($user->first_name, 0, 1) }}</span>
                                <img id="avatar-img" src="" alt="" class="w-full h-full object-cover hidden" />
                            </div>
                        </div>
                        <label class="btn btn-ghost btn-sm gap-1">
                            <span class="icon-[tabler--camera] size-4"></span>
                            Upload Photo
                            <input type="file" name="avatar" id="avatar-input" accept="image/*" class="hidden" onchange="previewAvatar(this)" />
                        </label>
                        <p class="text-xs text-base-content/50 mt-1">Optional - Max 5MB</p>
                    </div>

                    <!-- Name Fields -->
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="label" for="first_name"><span class="label-text">First Name <span class="text-error">*</span></span></label>
                            <input type="text" name="first_name" id="first_name" value="{{ $user->first_name }}"
                                   class="input input-bordered w-full" required
                                   pattern="[A-Za-z\s\-']+" title="Only letters allowed" />
                        </div>
                        <div>
                            <label class="label" for="last_name"><span class="label-text">Last Name <span class="text-error">*</span></span></label>
                            <input type="text" name="last_name" id="last_name" value="{{ $user->last_name }}"
                                   class="input input-bordered w-full" required
                                   pattern="[A-Za-z\s\-']+" title="Only letters allowed" />
                        </div>
                    </div>

                    <!-- Email (Read Only) -->
                    <div class="mb-4">
                        <label class="label" for="email"><span class="label-text">Email Address</span></label>
                        <input type="email" id="email" value="{{ $user->email }}" class="input input-bordered w-full bg-base-200" readonly disabled />
                    </div>

                    <!-- Role (Read Only) -->
                    <div class="mb-4">
                        <label class="label" for="role"><span class="label-text">Role</span></label>
                        <input type="text" id="role" value="{{ $user->role_label }}" class="input input-bordered w-full bg-base-200" readonly disabled />
                    </div>

                    <!-- Password -->
                    <div class="mb-4">
                        <label class="label" for="password"><span class="label-text">Password <span class="text-error">*</span></span></label>
                        <div class="relative">
                            <input type="password" name="password" id="password"
                                   class="input input-bordered w-full pr-10" required minlength="8"
                                   placeholder="Minimum 8 characters" />
                            <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-base-content/50 hover:text-base-content" onclick="togglePassword('password', this)">
                                <span class="icon-[tabler--eye] size-5"></span>
                            </button>
                        </div>
                    </div>

                    <!-- Confirm Password -->
                    <div class="mb-4">
                        <label class="label" for="password_confirmation"><span class="label-text">Confirm Password <span class="text-error">*</span></span></label>
                        <div class="relative">
                            <input type="password" name="password_confirmation" id="password_confirmation"
                                   class="input input-bordered w-full pr-10" required minlength="8" />
                            <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-base-content/50 hover:text-base-content" onclick="togglePassword('password_confirmation', this)">
                                <span class="icon-[tabler--eye] size-5"></span>
                            </button>
                        </div>
                    </div>

                    <!-- About Yourself -->
                    <div class="mb-4">
                        <label class="label" for="description"><span class="label-text">About Yourself</span></label>
                        <textarea name="description" id="description" class="textarea textarea-bordered w-full h-24"
                                  placeholder="Tell us a bit about yourself..." maxlength="500"></textarea>
                        <div class="label">
                            <span class="label-text-alt text-base-content/50">Optional - Max 500 characters</span>
                        </div>
                    </div>

                    <!-- Timezone -->
                    <div class="mb-6">
                        <label class="label" for="timezone"><span class="label-text">Timezone <span class="text-error">*</span></span></label>
                        <select name="timezone" id="timezone" class="select select-bordered w-full" required>
                            <option value="">Select your timezone</option>
                            @foreach($timezones as $tz)
                                <option value="{{ $tz }}" {{ $tz === 'UTC' ? 'selected' : '' }}>{{ $tz }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Error Display -->
                    <div id="signup-errors" class="alert alert-error mb-4 hidden">
                        <span class="icon-[tabler--alert-circle] size-5"></span>
                        <div id="signup-errors-content"></div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" id="submit-btn" class="btn btn-primary w-full gap-1">
                        <span class="icon-[tabler--check] size-5"></span>
                        Complete Setup & Join Team
                    </button>
                </form>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-6 text-sm text-base-content/50">
            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </div>
    </div>

    <script>
        function previewAvatar(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const avatarImg = document.getElementById('avatar-img');
                    const avatarInitials = document.getElementById('avatar-initials');

                    avatarImg.src = e.target.result;
                    avatarImg.classList.remove('hidden');
                    avatarInitials.classList.add('hidden');
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        function togglePassword(fieldId, btn) {
            const field = document.getElementById(fieldId);
            const icon = btn.querySelector('span');

            if (field.type === 'password') {
                field.type = 'text';
                icon.className = 'icon-[tabler--eye-off] size-5';
            } else {
                field.type = 'password';
                icon.className = 'icon-[tabler--eye] size-5';
            }
        }

        // Update initials when name changes
        document.getElementById('first_name').addEventListener('input', updateInitials);
        document.getElementById('last_name').addEventListener('input', updateInitials);

        function updateInitials() {
            const firstName = document.getElementById('first_name').value;
            const lastName = document.getElementById('last_name').value;
            const initials = (firstName.charAt(0) + lastName.charAt(0)).toUpperCase() || '?';
            document.getElementById('avatar-initials').textContent = initials;
        }

        // Auto-detect timezone
        try {
            const detectedTz = Intl.DateTimeFormat().resolvedOptions().timeZone;
            const tzSelect = document.getElementById('timezone');
            if (tzSelect.querySelector(`option[value="${detectedTz}"]`)) {
                tzSelect.value = detectedTz;
            }
        } catch (e) {}

        // Form submission
        document.getElementById('signup-form').addEventListener('submit', async function(e) {
            e.preventDefault();

            const form = e.target;
            const submitBtn = document.getElementById('submit-btn');
            const errorsDiv = document.getElementById('signup-errors');
            const errorsContent = document.getElementById('signup-errors-content');

            // Hide previous errors
            errorsDiv.classList.add('hidden');

            // Validate passwords match
            const password = document.getElementById('password').value;
            const passwordConfirm = document.getElementById('password_confirmation').value;

            if (password !== passwordConfirm) {
                errorsContent.textContent = 'Passwords do not match.';
                errorsDiv.classList.remove('hidden');
                return;
            }

            // Disable submit button
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="loading loading-spinner loading-sm"></span> Setting up your account...';

            try {
                const formData = new FormData(form);

                const response = await fetch('{{ route("team.signup.complete", $token) }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: formData,
                });

                const data = await response.json();

                if (data.success) {
                    submitBtn.innerHTML = '<span class="icon-[tabler--check] size-5"></span> Success! Redirecting...';
                    submitBtn.classList.remove('btn-primary');
                    submitBtn.classList.add('btn-success');

                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 1000);
                } else {
                    let errorHtml = '';
                    if (data.errors) {
                        errorHtml = '<ul class="list-disc ml-4">';
                        for (const key in data.errors) {
                            data.errors[key].forEach(error => {
                                errorHtml += `<li>${error}</li>`;
                            });
                        }
                        errorHtml += '</ul>';
                    } else {
                        errorHtml = data.error || data.message || 'An error occurred';
                    }
                    errorsContent.innerHTML = errorHtml;
                    errorsDiv.classList.remove('hidden');

                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<span class="icon-[tabler--check] size-5"></span> Complete Setup & Join Team';
                }
            } catch (error) {
                errorsContent.textContent = 'An error occurred. Please try again.';
                errorsDiv.classList.remove('hidden');

                submitBtn.disabled = false;
                submitBtn.innerHTML = '<span class="icon-[tabler--check] size-5"></span> Complete Setup & Join Team';
            }
        });
    </script>
</body>
</html>
