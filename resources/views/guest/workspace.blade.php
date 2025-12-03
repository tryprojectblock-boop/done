<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $workspace->name }} - Guest Portal - {{ config('app.name', 'NewDone') }}</title>

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
            <a href="{{ route('guest.portal.index') }}" class="flex items-center gap-2">
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
                        <a href="{{ route('guest.portal.index') }}">
                            <span class="icon-[tabler--home] size-4"></span>
                            Dashboard
                        </a>
                    </li>
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
        <div class="max-w-6xl mx-auto">
            <!-- Breadcrumb -->
            <div class="breadcrumbs text-sm mb-4">
                <ul>
                    <li><a href="{{ route('guest.portal.index') }}">Dashboard</a></li>
                    <li>{{ $workspace->name }}</li>
                </ul>
            </div>

            <!-- Workspace Header -->
            <div class="card bg-base-100 shadow mb-6">
                <div class="card-body">
                    <div class="flex items-start gap-4">
                        <div class="avatar placeholder">
                            @if($workspace->getLogoUrl())
                                <div class="w-16 h-16 rounded-lg">
                                    <img src="{{ $workspace->getLogoUrl() }}" alt="{{ $workspace->name }}" />
                                </div>
                            @else
                                <div class="bg-primary text-primary-content rounded-lg w-16 h-16 flex items-center justify-center">
                                    <span class="text-2xl font-bold">{{ substr($workspace->name, 0, 1) }}</span>
                                </div>
                            @endif
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <h1 class="text-2xl font-bold">{{ $workspace->name }}</h1>
                                <span class="badge badge-warning">Guest Access</span>
                            </div>
                            @if($workspace->description)
                                <p class="text-base-content/60 mt-1">{{ $workspace->description }}</p>
                            @endif
                            <div class="flex items-center gap-4 mt-2 text-sm text-base-content/60">
                                <span>
                                    <span class="icon-[tabler--user] size-4 inline-block align-middle"></span>
                                    Owner: {{ $workspace->owner->name }}
                                </span>
                                <span>
                                    <span class="icon-[tabler--users] size-4 inline-block align-middle"></span>
                                    {{ $workspace->members->count() }} members
                                </span>
                                @if($workspace->workflow)
                                <span>
                                    <span class="icon-[tabler--git-branch] size-4 inline-block align-middle"></span>
                                    {{ $workspace->workflow->name }}
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Placeholder for Tasks/Content -->
                    <div class="card bg-base-100 shadow">
                        <div class="card-body text-center py-12">
                            <div class="flex justify-center mb-4">
                                <span class="icon-[tabler--list-check] size-16 text-base-content/20"></span>
                            </div>
                            <h2 class="text-xl font-semibold mb-2">Workspace Content</h2>
                            <p class="text-base-content/60 max-w-md mx-auto">
                                Tasks, discussions, and files shared with you will appear here.
                                This feature is coming soon.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Workspace Info -->
                    <div class="card bg-base-100 shadow">
                        <div class="card-body">
                            <h2 class="card-title text-lg mb-4">Workspace Info</h2>
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-base-content/60">Owner</span>
                                    <span class="font-medium">{{ $workspace->owner->name }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-base-content/60">Created</span>
                                    <span>{{ $workspace->created_at->format('M d, Y') }}</span>
                                </div>
                                @if($workspace->workflow)
                                <div class="flex justify-between">
                                    <span class="text-base-content/60">Workflow</span>
                                    <span>{{ $workspace->workflow->name }}</span>
                                </div>
                                @endif
                                <div class="flex justify-between">
                                    <span class="text-base-content/60">Your Role</span>
                                    <span class="badge badge-warning">Guest</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Team Members -->
                    <div class="card bg-base-100 shadow">
                        <div class="card-body">
                            <div class="flex items-center justify-between mb-4">
                                <h2 class="card-title text-lg">Team</h2>
                                <span class="badge badge-ghost">{{ $workspace->members->count() }}</span>
                            </div>
                            <div class="space-y-3">
                                @foreach($workspace->members->take(5) as $member)
                                <div class="flex items-center gap-3">
                                    <div class="avatar placeholder">
                                        <div class="bg-primary text-primary-content rounded-full w-8">
                                            <span class="text-xs">{{ substr($member->name, 0, 1) }}</span>
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-medium text-sm truncate">{{ $member->name }}</p>
                                        @php
                                            $role = $member->pivot->role;
                                            $roleLabel = $role instanceof \App\Modules\Workspace\Enums\WorkspaceRole ? $role->label() : ucfirst((string)$role);
                                        @endphp
                                        <p class="text-xs text-base-content/60">{{ $roleLabel }}</p>
                                    </div>
                                </div>
                                @endforeach
                                @if($workspace->members->count() > 5)
                                <p class="text-sm text-base-content/60 text-center">
                                    +{{ $workspace->members->count() - 5 }} more members
                                </p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
