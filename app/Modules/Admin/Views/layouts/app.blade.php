<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel') - {{ config('app.name') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    <!-- Styles / Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>
<body class="min-h-screen bg-base-200">
    <!-- Top Header -->
    <header class="bg-base-100 border-b border-base-200 sticky top-0 z-50">
        <div class="container mx-auto px-4">
            <!-- Top Bar -->
            <div class="flex items-center h-16">
                <!-- Logo -->
                <a href="{{ route('backoffice.dashboard') }}" class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-primary rounded-lg flex items-center justify-center">
                        <span class="icon-[tabler--shield-lock] size-5 text-primary-content"></span>
                    </div>
                    <div>
                        <div class="font-bold text-base-content">Admin Panel</div>
                        <div class="text-xs text-base-content/60">{{ config('app.name') }}</div>
                    </div>
                </a>
            </div>

            <!-- Navigation Tabs -->
            <nav class="flex items-center gap-1 -mb-px overflow-x-auto pb-px">
                <a href="{{ route('backoffice.dashboard') }}"
                   class="px-4 py-3 text-sm font-medium border-b-2 transition-colors whitespace-nowrap {{ request()->routeIs('backoffice.dashboard') ? 'border-primary text-primary' : 'border-transparent text-base-content/70 hover:text-base-content hover:border-base-300' }}">
                    <span class="icon-[tabler--dashboard] size-4 inline-block mr-1.5 align-middle"></span>
                    Dashboard
                </a>
                <a href="{{ route('backoffice.clients.index') }}"
                   class="px-4 py-3 text-sm font-medium border-b-2 transition-colors whitespace-nowrap {{ request()->routeIs('backoffice.clients.*') ? 'border-primary text-primary' : 'border-transparent text-base-content/70 hover:text-base-content hover:border-base-300' }}">
                    <span class="icon-[tabler--users] size-4 inline-block mr-1.5 align-middle"></span>
                    Clients
                </a>
                <a href="{{ route('backoffice.workspaces.index') }}"
                   class="px-4 py-3 text-sm font-medium border-b-2 transition-colors whitespace-nowrap {{ request()->routeIs('backoffice.workspaces.*') ? 'border-primary text-primary' : 'border-transparent text-base-content/70 hover:text-base-content hover:border-base-300' }}">
                    <span class="icon-[tabler--briefcase] size-4 inline-block mr-1.5 align-middle"></span>
                    Workspaces
                </a>
                <a href="{{ route('backoffice.plans.index') }}"
                   class="px-4 py-3 text-sm font-medium border-b-2 transition-colors whitespace-nowrap {{ request()->routeIs('backoffice.plans.*') ? 'border-primary text-primary' : 'border-transparent text-base-content/70 hover:text-base-content hover:border-base-300' }}">
                    <span class="icon-[tabler--credit-card] size-4 inline-block mr-1.5 align-middle"></span>
                    Plans & Coupons
                </a>
                <a href="{{ route('backoffice.invoices.index') }}"
                   class="px-4 py-3 text-sm font-medium border-b-2 transition-colors whitespace-nowrap {{ request()->routeIs('backoffice.invoices.*') ? 'border-primary text-primary' : 'border-transparent text-base-content/70 hover:text-base-content hover:border-base-300' }}">
                    <span class="icon-[tabler--receipt] size-4 inline-block mr-1.5 align-middle"></span>
                    Invoices
                </a>

                @if(auth()->guard('admin')->user()->canManageSettings())
                <a href="{{ route('backoffice.settings.app') }}"
                   class="px-4 py-3 text-sm font-medium border-b-2 transition-colors whitespace-nowrap {{ request()->routeIs('backoffice.settings.app*') ? 'border-primary text-primary' : 'border-transparent text-base-content/70 hover:text-base-content hover:border-base-300' }}">
                    <span class="icon-[tabler--adjustments] size-4 inline-block mr-1.5 align-middle"></span>
                    App Settings
                </a>
                @endif
                @if(auth()->guard('admin')->user()->canManageAdmins())
                <a href="{{ route('backoffice.settings.admins.index') }}"
                   class="px-4 py-3 text-sm font-medium border-b-2 transition-colors whitespace-nowrap {{ request()->routeIs('backoffice.settings.admins.*') ? 'border-primary text-primary' : 'border-transparent text-base-content/70 hover:text-base-content hover:border-base-300' }}">
                    <span class="icon-[tabler--user-cog] size-4 inline-block mr-1.5 align-middle"></span>
                    Admin Users
                </a>
                @endif

                <!-- Spacer to push profile to the right -->
                <div class="flex-1"></div>

                <!-- Logout Button -->
                <form action="{{ route('backoffice.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-ghost btn-sm gap-2">
                        <span class="icon-[tabler--logout] size-4"></span>
                        Logout
                    </button>
                </form>
            </nav>
        </div>
    </header>

    <!-- Page Content -->
    <main class="container mx-auto px-4 py-6">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="border-t border-base-200 bg-base-100 mt-auto">
        <div class="container mx-auto px-4 py-4">
            <div class="text-center text-sm text-base-content/50">
                Admin Panel v1.0 &copy; {{ date('Y') }} {{ config('app.name') }}
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
