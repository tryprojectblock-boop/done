@extends('layouts.app')

@section('content')
<div class="p-4 md:p-6">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center gap-2 text-sm text-base-content/60 mb-2">
                <a href="{{ route('dashboard') }}" class="hover:text-primary">Dashboard</a>
                <span class="icon-[tabler--chevron-right] size-4"></span>
                <a href="{{ route('workspace.index') }}" class="hover:text-primary">Workspaces</a>
                <span class="icon-[tabler--chevron-right] size-4"></span>
                <span>{{ $workspace->name }}</span>
            </div>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <!-- Workspace Icon/Color -->
                    <div class="w-16 h-16 rounded-lg flex items-center justify-center text-white" style="background-color: {{ $workspace->color ?? '#3b82f6' }}">
                        <span class="icon-[{{ $workspace->type->icon() }}] size-8"></span>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-base-content">{{ $workspace->name }}</h1>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="badge badge-primary">{{ $workspace->type->label() }}</span>
                            <span class="badge badge-{{ $workspace->status->color() }}">{{ $workspace->status->label() }}</span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('workspace.settings', $workspace) }}" class="btn btn-ghost">
                        <span class="icon-[tabler--settings] size-5"></span>
                        Settings
                    </a>
                </div>
            </div>
        </div>

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

        <!-- Description -->
        @if($workspace->description)
            <div class="card bg-base-100 shadow mb-6">
                <div class="card-body py-3">
                    <p class="text-base-content/70">{{ $workspace->description }}</p>
                </div>
            </div>
        @endif

        <!-- Module Tabs -->
        <div class="tabs tabs-bordered mb-6">
            <a href="{{ route('workspace.show', $workspace) }}"
               class="tab tab-lg {{ !request()->has('tab') || request('tab') === 'overview' ? 'tab-active' : '' }}">
                <span class="icon-[tabler--home] size-5 mr-2"></span>
                Overview
            </a>
            <a href="{{ route('workspace.show', ['workspace' => $workspace, 'tab' => 'tasks']) }}"
               class="tab tab-lg {{ request('tab') === 'tasks' ? 'tab-active' : '' }}">
                <span class="icon-[tabler--list-check] size-5 mr-2"></span>
                Tasks
                <span class="badge badge-warning badge-xs ml-2">Soon</span>
            </a>
            <a href="{{ route('workspace.show', ['workspace' => $workspace, 'tab' => 'discussions']) }}"
               class="tab tab-lg {{ request('tab') === 'discussions' ? 'tab-active' : '' }}">
                <span class="icon-[tabler--messages] size-5 mr-2"></span>
                Discussions
                <span class="badge badge-warning badge-xs ml-2">Soon</span>
            </a>
            <a href="{{ route('workspace.show', ['workspace' => $workspace, 'tab' => 'milestones']) }}"
               class="tab tab-lg {{ request('tab') === 'milestones' ? 'tab-active' : '' }}">
                <span class="icon-[tabler--flag] size-5 mr-2"></span>
                Milestones
                <span class="badge badge-warning badge-xs ml-2">Soon</span>
            </a>
            <a href="{{ route('workspace.show', ['workspace' => $workspace, 'tab' => 'files']) }}"
               class="tab tab-lg {{ request('tab') === 'files' ? 'tab-active' : '' }}">
                <span class="icon-[tabler--files] size-5 mr-2"></span>
                Files
                <span class="badge badge-warning badge-xs ml-2">Soon</span>
            </a>
            <a href="{{ route('workspace.show', ['workspace' => $workspace, 'tab' => 'time']) }}"
               class="tab tab-lg {{ request('tab') === 'time' ? 'tab-active' : '' }}">
                <span class="icon-[tabler--clock] size-5 mr-2"></span>
                Time
                <span class="badge badge-warning badge-xs ml-2">Soon</span>
            </a>
            <a href="{{ route('workspace.show', ['workspace' => $workspace, 'tab' => 'people']) }}"
               class="tab tab-lg {{ request('tab') === 'people' ? 'tab-active' : '' }}">
                <span class="icon-[tabler--users] size-5 mr-2"></span>
                People
                <span class="badge badge-ghost badge-xs ml-2">{{ $workspace->members->count() + $workspace->guests->count() }}</span>
            </a>
        </div>

        <!-- Tab Content -->
        @if(!request()->has('tab') || request('tab') === 'overview')
            @include('workspace::partials.tab-overview')
        @elseif(request('tab') === 'tasks')
            @include('workspace::partials.tab-coming-soon', ['title' => 'Tasks', 'icon' => 'tabler--list-check', 'description' => 'Manage tasks with workflows, assignments, and progress tracking.'])
        @elseif(request('tab') === 'discussions')
            @include('workspace::partials.tab-coming-soon', ['title' => 'Discussions', 'icon' => 'tabler--messages', 'description' => 'Team discussions and message boards for collaboration.'])
        @elseif(request('tab') === 'milestones')
            @include('workspace::partials.tab-coming-soon', ['title' => 'Milestones', 'icon' => 'tabler--flag', 'description' => 'Track project milestones and key deliverables.'])
        @elseif(request('tab') === 'files')
            @include('workspace::partials.tab-coming-soon', ['title' => 'Files', 'icon' => 'tabler--files', 'description' => 'Store and share files with your team.'])
        @elseif(request('tab') === 'time')
            @include('workspace::partials.tab-coming-soon', ['title' => 'Time Management', 'icon' => 'tabler--clock', 'description' => 'Track time spent on tasks and projects.'])
        @elseif(request('tab') === 'people')
            @include('workspace::partials.tab-people')
        @endif
    </div>
</div>
@endsection
