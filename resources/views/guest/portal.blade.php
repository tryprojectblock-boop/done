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
    <nav class="navbar bg-base-100 shadow-sm fixed top-0 left-0 right-0 z-50 px-4">
        <div class="flex-1">
            <a href="/guest/portal" class="flex items-center gap-2">
                <span class="icon-[tabler--checkbox] size-8 text-primary"></span>
                <span class="text-xl font-bold">{{ config('app.name') }}</span>
            </a>
        </div>
        <div class="flex-none flex items-center gap-2">
            <form action="{{ route('guest.portal.logout') }}" method="POST" class="flex">
                @csrf
                <button type="submit" class="btn btn-outline btn-error btn-sm">
                    <span class="icon-[tabler--logout] size-4"></span>
                    Logout
                </button>
            </form>
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

            <!-- Workspaces -->
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="card-title text-lg">
                            <span class="icon-[tabler--layout-grid] size-5"></span>
                            Your Workspaces
                        </h2>
                        <span class="badge badge-ghost">{{ $workspaces->count() }}</span>
                    </div>

                    @if($workspaces->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($workspaces as $workspace)
                            <a href="{{ route('guest.portal.workspace', $workspace) }}" class="card bg-base-100 border border-base-200 hover:border-primary hover:shadow-md transition-all cursor-pointer">
                                <div class="card-body p-4">
                                    <div class="flex items-start gap-3">
                                        <div class="avatar placeholder">
                                            @if($workspace->getLogoUrl())
                                                <div class="w-12 h-12 rounded-lg">
                                                    <img src="{{ $workspace->getLogoUrl() }}" alt="{{ $workspace->name }}" />
                                                </div>
                                            @else
                                                <div class="bg-primary text-primary-content rounded-lg w-12 h-12 flex items-center justify-center">
                                                    <span class="text-lg font-bold">{{ substr($workspace->name, 0, 1) }}</span>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <h3 class="font-semibold truncate">{{ $workspace->name }}</h3>
                                            <p class="text-sm text-base-content/60 truncate">{{ $workspace->description ?? 'No description' }}</p>
                                            <div class="flex items-center gap-2 mt-2">
                                                <span class="badge badge-warning badge-sm">Guest</span>
                                                <span class="text-xs text-base-content/50">by {{ $workspace->owner->name }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <div class="flex justify-center mb-4">
                                <span class="icon-[tabler--folder-off] size-16 text-base-content/20"></span>
                            </div>
                            <h3 class="text-lg font-semibold mb-2">No Workspaces Yet</h3>
                            <p class="text-base-content/60 max-w-md mx-auto">
                                You haven't been added to any workspaces yet. Once you're invited to a workspace, it will appear here.
                            </p>
                        </div>
                    @endif
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
