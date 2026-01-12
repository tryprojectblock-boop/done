<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Client Portal') - {{ config('app.name', 'NewDone') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    <!-- Styles / Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-base-200 flex flex-col">
    @php
        $user = Auth::guard('client-portal')->user();
    @endphp

    <!-- Top Navigation -->
    <nav class="navbar bg-base-100 shadow-sm fixed top-0 left-0 right-0 z-[100] px-4 min-h-16">
        <div class="flex-1 gap-2 flex items-center">
            <!-- Logo -->
            <a href="{{ route('client-portal.dashboard') }}" class="flex items-center gap-2 mr-4 flex-shrink-0">
                <span class="icon-[tabler--ticket] size-7 text-primary"></span>
                <span class="text-xl font-bold text-base-content hidden sm:inline">Support Portal</span>
            </a>

            <!-- Navigation Links -->
            <ul class="menu menu-horizontal px-1 gap-1 hidden md:flex">
                <li>
                    <a href="{{ route('client-portal.dashboard') }}" class="{{ request()->routeIs('client-portal.dashboard') ? 'active' : '' }}">
                        <span class="icon-[tabler--layout-dashboard] size-5"></span>
                        My Tickets
                    </a>
                </li>
            </ul>
        </div>

        <div class="flex-none gap-2 flex items-center">
            <!-- New Ticket Button -->
            <a href="{{ route('client-portal.tickets.create') }}" class="btn btn-primary btn-sm gap-2">
                <span class="icon-[tabler--plus] size-4"></span>
                <span class="hidden sm:inline">New Ticket</span>
            </a>

            <!-- Profile Dropdown -->
            <div class="dropdown relative inline-flex [--auto-close:inside] [--offset:8] [--placement:bottom-end]">
                <button id="profile-dropdown" type="button" class="dropdown-toggle btn btn-ghost gap-2 px-2" aria-haspopup="menu" aria-expanded="false" aria-label="Profile menu">
                    <div class="avatar placeholder">
                        <div class="bg-primary text-primary-content rounded-full w-8 h-8">
                            <span class="text-sm font-medium">{{ strtoupper(substr($user->name ?? $user->email, 0, 1)) }}</span>
                        </div>
                    </div>
                    <span class="hidden md:inline text-sm font-medium">{{ $user->first_name ?? explode('@', $user->email)[0] }}</span>
                    <span class="icon-[tabler--chevron-down] dropdown-open:rotate-180 size-4 hidden md:inline transition-transform"></span>
                </button>
                <ul class="dropdown-menu dropdown-open:opacity-100 hidden min-w-56" role="menu" aria-orientation="vertical" aria-labelledby="profile-dropdown">
                    <li class="dropdown-header px-3 py-2 border-b border-base-200 mb-2">
                        <p class="font-medium text-sm">{{ $user->name ?? 'Client' }}</p>
                        <p class="text-xs text-base-content/50">{{ $user->email }}</p>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('client-portal.dashboard') }}">
                            <span class="icon-[tabler--ticket] size-4 me-2"></span>
                            My Tickets
                        </a>
                    </li>
                    <li><hr class="border-base-content/10 my-2"></li>
                    <li>
                        <form action="{{ route('client-portal.logout') }}" method="POST" class="w-full">
                            @csrf
                            <button type="submit" class="dropdown-item text-error w-full text-left">
                                <span class="icon-[tabler--logout] size-4 me-2"></span>
                                Sign Out
                            </button>
                        </form>
                    </li>
                </ul>
            </div>

            <!-- Mobile Menu -->
            <div class="dropdown relative inline-flex md:hidden [--auto-close:inside] [--offset:8] [--placement:bottom-end]">
                <button id="mobile-menu-dropdown" type="button" class="dropdown-toggle btn btn-ghost btn-circle btn-sm" aria-haspopup="menu" aria-expanded="false" aria-label="Mobile menu">
                    <span class="icon-[tabler--menu-2] size-5"></span>
                </button>
                <ul class="dropdown-menu dropdown-open:opacity-100 hidden min-w-56" role="menu" aria-orientation="vertical" aria-labelledby="mobile-menu-dropdown">
                    <li>
                        <a class="dropdown-item" href="{{ route('client-portal.dashboard') }}">
                            <span class="icon-[tabler--layout-dashboard] size-4 me-2"></span>
                            My Tickets
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('client-portal.tickets.create') }}">
                            <span class="icon-[tabler--plus] size-4 me-2"></span>
                            New Ticket
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-1 pt-16">
        <div class="max-w mx-auto px-4 py-6">
            <!-- Flash Messages -->
            @if(session('success'))
            <div class="alert alert-success mb-6">
                <span class="icon-[tabler--check] size-5"></span>
                <span>{{ session('success') }}</span>
            </div>
            @endif

            @if(session('error'))
            <div class="alert alert-error mb-6">
                <span class="icon-[tabler--alert-circle] size-5"></span>
                <span>{{ session('error') }}</span>
            </div>
            @endif

            @yield('content')
        </div>
    </main>

    <!-- Footer -->
    <footer class="py-4 text-center text-sm text-base-content/50 border-t border-base-200 bg-base-100">
        <p>&copy; {{ date('Y') }} {{ config('app.name', 'NewDone') }}. All rights reserved.</p>
    </footer>
</body>
</html>
