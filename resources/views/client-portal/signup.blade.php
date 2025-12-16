<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Set Up Your Account - Client Portal - {{ config('app.name', 'NewDone') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    <!-- Styles / Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-base-200 flex items-center justify-center p-4">
    <div class="card bg-base-100 shadow-xl w-full max-w-md">
        <div class="card-body">
            <!-- Header -->
            <div class="text-center mb-6">
                <div class="w-16 h-16 rounded-2xl bg-primary flex items-center justify-center mx-auto mb-4">
                    <span class="icon-[tabler--user-plus] size-8 text-primary-content"></span>
                </div>
                <h1 class="text-2xl font-bold text-base-content">Set Up Your Account</h1>
                <p class="text-base-content/60 mt-1">Create a password to access the client portal</p>
            </div>

            <!-- User Info -->
            <div class="bg-base-200 rounded-lg p-4 mb-6">
                <div class="flex items-center gap-3">
                    <div class="avatar">
                        <div class="w-10 h-10 rounded-full">
                            <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" />
                        </div>
                    </div>
                    <div>
                        <p class="font-medium">{{ $user->name }}</p>
                        <p class="text-sm text-base-content/60">{{ $user->email }}</p>
                    </div>
                </div>
            </div>

            <!-- Signup Form -->
            <form action="{{ route('client-portal.signup.complete', $token) }}" method="POST">
                @csrf

                <div class="form-control mb-4">
                    <label class="label" for="password">
                        <span class="label-text">Password</span>
                    </label>
                    <div class="input-group">
                        <span class="bg-base-200">
                            <span class="icon-[tabler--lock] size-5 text-base-content/50"></span>
                        </span>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="input input-bordered w-full @error('password') input-error @enderror"
                            placeholder="Create a strong password"
                            required
                            autofocus
                        />
                    </div>
                    @error('password')
                    <label class="label">
                        <span class="label-text-alt text-error">{{ $message }}</span>
                    </label>
                    @enderror
                    <label class="label">
                        <span class="label-text-alt text-base-content/60">Must be at least 8 characters</span>
                    </label>
                </div>

                <div class="form-control mb-6">
                    <label class="label" for="password_confirmation">
                        <span class="label-text">Confirm Password</span>
                    </label>
                    <div class="input-group">
                        <span class="bg-base-200">
                            <span class="icon-[tabler--lock-check] size-5 text-base-content/50"></span>
                        </span>
                        <input
                            type="password"
                            id="password_confirmation"
                            name="password_confirmation"
                            class="input input-bordered w-full"
                            placeholder="Confirm your password"
                            required
                        />
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-full gap-2">
                    <span class="icon-[tabler--check] size-5"></span>
                    Complete Setup
                </button>
            </form>

            <!-- Footer -->
            <p class="text-center text-sm text-base-content/60 mt-6">
                Already have an account?
                <a href="{{ route('client-portal.login') }}" class="link link-primary">Sign in</a>
            </p>
        </div>
    </div>
</body>
</html>
