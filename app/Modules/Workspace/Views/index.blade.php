@extends('layouts.app')

@php
    $user = auth()->user();
    $isGuestOnly = $user->role === \App\Models\User::ROLE_GUEST && !$user->company_id;
@endphp

@section('content')
<div class="p-4 md:p-6">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center gap-2 text-sm text-base-content/60 mb-2">
                <a href="{{ route('dashboard') }}" class="hover:text-primary">Dashboard</a>
                <span class="icon-[tabler--chevron-right] size-4"></span>
                <span>Workspaces</span>
            </div>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-base-content">Workspaces</h1>
                    <p class="text-base-content/60">
                        @if($isGuestOnly)
                            Workspaces you have access to as a guest
                        @else
                            Manage your team workspaces
                        @endif
                    </p>
                </div>
                @if(!$isGuestOnly)
                    <a href="{{ route('workspace.create') }}" class="btn btn-primary">
                        <span class="icon-[tabler--plus] size-5"></span>
                        Add Workspace
                    </a>
                @endif
            </div>
        </div>

        <!-- Upgrade Required Message -->
        @if(session('upgrade_required'))
            <div class="alert alert-warning mb-4">
                <span class="icon-[tabler--alert-triangle] size-5"></span>
                <div class="flex-1">
                    <span>{{ session('upgrade_required') }}</span>
                </div>
                <a href="{{ route('guest.upgrade') }}" class="btn btn-success btn-sm">
                    <span class="icon-[tabler--rocket] size-4"></span>
                    Upgrade Now
                </a>
            </div>
        @endif

        <!-- Success/Error Messages -->
        @if(session('success'))
            <div class="alert alert-success mb-4">
                <span class="icon-[tabler--check] size-5"></span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-error mb-4">
                <span class="icon-[tabler--x] size-5"></span>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Workspaces Grid -->
        @if($workspaces->isEmpty() && $guestWorkspaces->isEmpty())
            <div class="card bg-base-100 shadow">
                <div class="card-body text-center py-12">
                    <div class="flex justify-center mb-4">
                        <span class="icon-[tabler--briefcase] size-16 text-base-content/20"></span>
                    </div>
                    @if($isGuestOnly)
                        <h3 class="text-lg font-semibold text-base-content">No Workspaces Yet</h3>
                        <p class="text-base-content/60 mb-4">You haven't been invited to any workspaces yet. Once someone adds you to a workspace, it will appear here.</p>
                        <div class="flex flex-col items-center gap-4">
                            <div class="text-sm text-base-content/50">Want to create your own workspaces?</div>
                            <a href="{{ route('guest.upgrade') }}" class="btn btn-success">
                                <span class="icon-[tabler--rocket] size-5"></span>
                                Upgrade Your Account
                            </a>
                        </div>
                    @else
                        <h3 class="text-lg font-semibold text-base-content">No Workspaces Yet</h3>
                        <p class="text-base-content/60 mb-4">Create your first workspace to start organizing your projects and team.</p>
                        <div>
                            <a href="{{ route('workspace.create') }}" class="btn btn-primary">
                                <span class="icon-[tabler--plus] size-5"></span>
                                Create Workspace
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        @else
            @if($workspaces->isNotEmpty())
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($workspaces as $workspace)
                <a href="{{ route('workspace.show', $workspace) }}" class="card bg-base-100 shadow hover:shadow-lg transition-shadow group">
                    <div class="card-body">
                        <!-- Workspace Header -->
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <!-- Workspace Icon/Color -->
                                <div class="w-12 h-12 rounded-lg flex items-center justify-center text-white" style="background-color: {{ $workspace->color ?? '#3b82f6' }}">
                                    <span class="icon-[{{ $workspace->type->icon() }}] size-6"></span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-semibold text-lg text-base-content truncate group-hover:text-primary transition-colors">{{ $workspace->name }}</h3>
                                    <span class="badge badge-ghost badge-sm">{{ $workspace->type->label() }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        @if($workspace->description)
                            <p class="text-sm text-base-content/60 line-clamp-2 mb-3">{{ $workspace->description }}</p>
                        @endif

                        <!-- Stats -->
                        <div class="flex items-center gap-4 text-sm text-base-content/60 mt-auto pt-3 border-t border-base-200">
                            <span class="flex items-center gap-1">
                                <span class="icon-[tabler--users] size-4"></span>
                                {{ $workspace->members->count() }} {{ Str::plural('member', $workspace->members->count()) }}
                            </span>
                            <span class="flex items-center gap-1">
                                <span class="icon-[tabler--calendar] size-4"></span>
                                {{ $workspace->created_at->format('M d, Y') }}
                            </span>
                        </div>

                        <!-- Status Badge -->
                        @if($workspace->status->value !== 'active')
                            <div class="absolute top-2 right-2">
                                <span class="badge badge-{{ $workspace->status->color() }} badge-sm">{{ $workspace->status->label() }}</span>
                            </div>
                        @endif
                    </div>
                </a>
                @endforeach
            </div>

            <!-- Pagination -->
            @if($workspaces->hasPages())
                <div class="mt-6">
                    {{ $workspaces->links() }}
                </div>
            @endif
            @endif
        @endif

        <!-- Guest Workspaces Section -->
        @if($guestWorkspaces->isNotEmpty())
            <div class="mt-8">
                <div class="flex items-center gap-2 mb-4">
                    <span class="icon-[tabler--user-star] size-5 text-warning"></span>
                    <h2 class="text-xl font-bold text-base-content">Guest Access</h2>
                    <span class="badge badge-warning">{{ $guestWorkspaces->count() }}</span>
                </div>
                <p class="text-base-content/60 mb-4">Workspaces you've been invited to as a guest</p>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($guestWorkspaces as $workspace)
                    <a href="{{ route('workspace.guest-view', $workspace) }}" class="card bg-base-100 shadow hover:shadow-lg transition-shadow group border-2 border-warning/20">
                        <div class="card-body">
                            <!-- Guest Badge -->
                            <div class="absolute top-2 right-2">
                                <span class="badge badge-warning badge-sm">Guest</span>
                            </div>

                            <!-- Workspace Header -->
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex items-center gap-3">
                                    <!-- Workspace Icon/Color -->
                                    <div class="w-12 h-12 rounded-lg flex items-center justify-center bg-warning text-warning-content">
                                        <span class="icon-[tabler--briefcase] size-6"></span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h3 class="font-semibold text-lg text-base-content truncate group-hover:text-warning transition-colors">{{ $workspace->name }}</h3>
                                        <span class="text-sm text-base-content/60">by {{ $workspace->owner->name }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Description -->
                            @if($workspace->description)
                                <p class="text-sm text-base-content/60 line-clamp-2 mb-3">{{ $workspace->description }}</p>
                            @endif

                            <!-- Stats -->
                            <div class="flex items-center gap-4 text-sm text-base-content/60 mt-auto pt-3 border-t border-base-200">
                                <span class="flex items-center gap-1">
                                    <span class="icon-[tabler--users] size-4"></span>
                                    {{ $workspace->members->count() }} {{ Str::plural('member', $workspace->members->count()) }}
                                </span>
                                <span class="flex items-center gap-1">
                                    <span class="icon-[tabler--eye] size-4"></span>
                                    View Only
                                </span>
                            </div>
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
