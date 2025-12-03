<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Guest Portal - {{ config('app.name', 'NewDone') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    <!-- Styles / Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-base-200 flex items-center justify-center p-4">
    <div class="w-full max-w-lg text-center">
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <div class="flex justify-center mb-4">
                    <span class="icon-[tabler--check-circle] size-16 text-success"></span>
                </div>

                <h2 class="text-2xl font-bold text-base-content mb-2">Welcome to the Guest Portal!</h2>

                <p class="text-base-content/70 mb-6">
                    Your account has been set up successfully. The guest portal is currently under development.
                </p>

                <div class="bg-base-200 rounded-lg p-4 mb-6">
                    <p class="text-sm text-base-content/60">
                        You will be able to view shared projects, tasks, and collaborate with the team once the portal is ready.
                    </p>
                </div>

                <a href="{{ route('login') }}" class="btn btn-primary">
                    <span class="icon-[tabler--login] size-5"></span>
                    Go to Login
                </a>
            </div>
        </div>
    </div>
</body>
</html>
