<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'NewDone') }}</title>

    <!-- Fonts -->
     <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Figtree:ital,wght@0,300..900;1,300..900&display=swap" rel="stylesheet">
    <!-- <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" /> -->

    <!-- Styles / Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-base-200">
    <div class="min-h-screen flex flex-col items-center justify-center p-4">
        <!-- Logo -->
        <div class="mb-8">
            <a href="/" class="flex items-center gap-2">
                <span class="icon-[tabler--checkbox] size-10 text-primary"></span>
                <span class="text-xl md-font text-base-content text-primary-color">{{ config('app.name', 'NewDone') }}</span>
            </a>
        </div>

        <!-- Session Error Alert -->
        @if(session('error'))
        <div class="w-full max-w-md mb-4">
            <div class="alert alert-error">
                <span class="icon-[tabler--alert-circle] size-5"></span>
                <span>{{ session('error') }}</span>
            </div>
        </div>
        @endif

        <!-- Main Content -->
        <div class="w-full max-w-md">
            @yield('content')
        </div>

        <!-- Footer -->
        <div class="mt-8 text-center text-sm text-base-content/50">
            <p>&copy; {{ date('Y') }} {{ config('app.name', 'NewDone') }}. All rights reserved.</p>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
