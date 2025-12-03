<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'NewDone') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    <!-- Styles / Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-base-200 flex flex-col">
    <!-- Top Navigation -->
    @include('partials.navigation')

    <!-- Main Content - padding-top matches navbar height (64px = 4rem) -->
    <main class="flex-1 pt-16">
        @yield('content')
    </main>

    <!-- Footer -->
    @include('partials.footer')

    <!-- Toast Notifications -->
    @include('partials.toast')

    <!-- Global Delete Confirmation Modal -->
    @include('partials.delete-modal')

    @stack('scripts')
</body>
</html>
