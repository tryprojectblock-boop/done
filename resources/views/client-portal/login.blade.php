<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Login - Client Portal - {{ config('app.name', 'NewDone') }}</title>

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
                    <span class="icon-[tabler--ticket] size-8 text-primary-content"></span>
                </div>
                <h1 class="text-2xl font-bold text-base-content">Client Portal</h1>
                <p class="text-base-content/60 mt-1">Sign in to manage your support tickets</p>
            </div>

            <!-- Flash Messages -->
            @if(session('success'))
            <div class="alert alert-success mb-4">
                <span class="icon-[tabler--check] size-5"></span>
                <span>{{ session('success') }}</span>
            </div>
            @endif

            @if(session('error'))
            <div class="alert alert-error mb-4">
                <span class="icon-[tabler--alert-circle] size-5"></span>
                <span>{{ session('error') }}</span>
            </div>
            @endif

            <!-- Login Form -->
            <form action="{{ route('client-portal.login.submit') }}" method="POST">
                @csrf

                <div class="form-control mb-4">
                    <label class="label" for="email">
                        <span class="label-text">Email Address</span>
                    </label>
                    <div class="input-group">
                        <span class="bg-base-200">
                            <span class="icon-[tabler--mail] size-5 text-base-content/50"></span>
                        </span>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old('email') }}"
                            class="input input-bordered w-full @error('email') input-error @enderror"
                            placeholder="you@example.com"
                            required
                            autofocus
                        />
                    </div>
                    @error('email')
                    <label class="label">
                        <span class="label-text-alt text-error">{{ $message }}</span>
                    </label>
                    @enderror
                </div>

                <div class="form-control mb-6">
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
                            placeholder="Enter your password"
                            required
                        />
                    </div>
                    @error('password')
                    <label class="label">
                        <span class="label-text-alt text-error">{{ $message }}</span>
                    </label>
                    @enderror
                </div>

                <div class="form-control mb-6">
                    <label class="label cursor-pointer justify-start gap-3">
                        <input type="checkbox" name="remember" class="checkbox checkbox-primary checkbox-sm" />
                        <span class="label-text">Remember me</span>
                    </label>
                </div>

                <button type="submit" class="btn btn-primary w-full gap-2">
                    <span class="icon-[tabler--login] size-5"></span>
                    Sign In
                </button>
            </form>

            <!-- Footer -->
            <div class="divider my-6">OR</div>
            <p class="text-center text-sm text-base-content/60">
                Don't have an account? Check your email for an invitation link.
            </p>
        </div>
    </div>
</body>
</html>
