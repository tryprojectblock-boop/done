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
<body class="min-h-screen bg-base-200">
    <!-- Navbar -->
    <nav class="navbar bg-base-100 shadow-sm fixed top-0 z-50">
        <div class="flex-1">
            <a href="/guest/portal" class="flex items-center gap-2 px-4">
                <span class="icon-[tabler--checkbox] size-8 text-primary"></span>
                <span class="text-xl font-bold">{{ config('app.name') }}</span>
            </a>
        </div>
        <div class="flex-none gap-2">
            <div class="dropdown dropdown-end">
                <div tabindex="0" role="button" class="btn btn-ghost btn-circle avatar placeholder">
                    @if($guest->avatar_url)
                        <div class="w-10 rounded-full">
                            <img src="{{ $guest->avatar_url }}" alt="{{ $guest->full_name }}" />
                        </div>
                    @else
                        <div class="bg-neutral text-neutral-content rounded-full w-10">
                            <span>{{ $guest->initials }}</span>
                        </div>
                    @endif
                </div>
                <ul tabindex="0" class="dropdown-menu dropdown-content mt-3 z-[100] p-2 shadow bg-base-100 rounded-box w-52">
                    <li class="menu-title px-4 py-2">
                        <span class="text-sm font-medium">{{ $guest->full_name }}</span>
                        <span class="text-xs text-base-content/60 block">{{ $guest->email }}</span>
                    </li>
                    <li><hr class="my-1"></li>
                    <li>
                        <form action="{{ route('guest.portal.logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="text-error">
                                <span class="icon-[tabler--logout] size-4"></span>
                                Logout
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="pt-20 p-4 md:p-6">
        <div class="max-w-4xl mx-auto">
            <!-- Welcome Card -->
            <div class="card bg-base-100 shadow mb-6">
                <div class="card-body">
                    <div class="flex items-center gap-4">
                        <div class="avatar placeholder">
                            @if($guest->avatar_url)
                                <div class="w-16 h-16 rounded-full">
                                    <img src="{{ $guest->avatar_url }}" alt="{{ $guest->full_name }}" />
                                </div>
                            @else
                                <div class="bg-primary text-primary-content rounded-full w-16 h-16 flex items-center justify-center">
                                    <span class="text-2xl font-bold">{{ $guest->initials }}</span>
                                </div>
                            @endif
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold">Welcome, {{ $guest->first_name }}!</h1>
                            <p class="text-base-content/60">
                                <span class="badge badge-{{ $guest->type_color }}">{{ $guest->type_label }}</span>
                                @if($guest->client_portal_access)
                                    <span class="badge badge-success ml-2">Portal Access Enabled</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Portal Content -->
            <div class="card bg-base-100 shadow">
                <div class="card-body text-center py-12">
                    <div class="flex justify-center mb-4">
                        <span class="icon-[tabler--building] size-16 text-base-content/20"></span>
                    </div>
                    <h2 class="text-xl font-semibold mb-2">Guest Portal</h2>
                    <p class="text-base-content/60 max-w-md mx-auto">
                        The guest portal is under development. Soon you'll be able to view shared projects,
                        tasks, and collaborate with the team here.
                    </p>
                </div>
            </div>

            <!-- Guest Info -->
            <div class="card bg-base-100 shadow mt-6">
                <div class="card-body">
                    <h3 class="card-title text-lg mb-4">Your Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <span class="text-base-content/60 text-sm">Name</span>
                            <p class="font-medium">{{ $guest->full_name }}</p>
                        </div>
                        <div>
                            <span class="text-base-content/60 text-sm">Email</span>
                            <p class="font-medium">{{ $guest->email }}</p>
                        </div>
                        @if($guest->company_name)
                        <div>
                            <span class="text-base-content/60 text-sm">Company</span>
                            <p class="font-medium">{{ $guest->company_name }}</p>
                        </div>
                        @endif
                        @if($guest->position)
                        <div>
                            <span class="text-base-content/60 text-sm">Position</span>
                            <p class="font-medium">{{ $guest->position }}</p>
                        </div>
                        @endif
                        <div>
                            <span class="text-base-content/60 text-sm">Timezone</span>
                            <p class="font-medium">{{ $guest->timezone ?? 'UTC' }}</p>
                        </div>
                        <div>
                            <span class="text-base-content/60 text-sm">Member Since</span>
                            <p class="font-medium">{{ $guest->accepted_at ? $guest->accepted_at->format('M d, Y') : $guest->created_at->format('M d, Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
