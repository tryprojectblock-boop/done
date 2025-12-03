<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Invitation Expired - {{ config('app.name', 'NewDone') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    <!-- Styles / Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-base-200 flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <!-- Logo -->
        <div class="text-center mb-8">
            <div class="flex items-center justify-center gap-2 mb-2">
                <span class="icon-[tabler--checkbox] size-10 text-primary"></span>
                <span class="text-3xl font-bold text-base-content">{{ config('app.name') }}</span>
            </div>
        </div>

        <!-- Expired Card -->
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body text-center">
                <div class="flex justify-center mb-4">
                    <span class="icon-[tabler--clock-x] size-16 text-warning"></span>
                </div>

                <h2 class="text-xl font-bold text-base-content mb-2">Invitation Expired</h2>

                <p class="text-base-content/70 mb-6">
                    The invitation link for <strong>{{ $email }}</strong> has expired.
                    Please contact the team that invited you to request a new invitation.
                </p>

                <div class="bg-base-200 rounded-lg p-4 mb-6">
                    <p class="text-sm text-base-content/60">
                        Invitation links are valid for 7 days for security purposes.
                    </p>
                </div>

                <a href="{{ route('login') }}" class="btn btn-primary w-full">
                    <span class="icon-[tabler--login] size-5"></span>
                    Go to Login
                </a>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-6 text-sm text-base-content/50">
            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </div>
    </div>
</body>
</html>
