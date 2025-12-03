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
                <div class="card-body">
                    <p class="text-base-content/70">{{ $workspace->description }}</p>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Quick Actions -->
                <div class="card bg-base-100 shadow">
                    <div class="card-body">
                        <h2 class="card-title text-lg mb-4">Quick Actions</h2>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                            <a href="#" class="btn btn-ghost flex-col h-auto py-4">
                                <span class="icon-[tabler--list-check] size-6 text-primary"></span>
                                <span class="text-sm mt-1">Tasks</span>
                            </a>
                            <a href="{{ route('workflow.index', $workspace) }}" class="btn btn-ghost flex-col h-auto py-4">
                                <span class="icon-[tabler--git-branch] size-6 text-success"></span>
                                <span class="text-sm mt-1">Workflows</span>
                            </a>
                            <a href="#" class="btn btn-ghost flex-col h-auto py-4">
                                <span class="icon-[tabler--file-text] size-6 text-warning"></span>
                                <span class="text-sm mt-1">Documents</span>
                            </a>
                            <a href="#" class="btn btn-ghost flex-col h-auto py-4">
                                <span class="icon-[tabler--calendar] size-6 text-info"></span>
                                <span class="text-sm mt-1">Schedule</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="card bg-base-100 shadow">
                    <div class="card-body">
                        <h2 class="card-title text-lg mb-4">Recent Activity</h2>
                        <div class="text-center py-8 text-base-content/50">
                            <span class="icon-[tabler--activity] size-12 mb-2"></span>
                            <p>No recent activity</p>
                        </div>
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
                            @if($workspace->settings['start_date'] ?? null)
                            <div class="flex justify-between">
                                <span class="text-base-content/60">Start Date</span>
                                <span>{{ \Carbon\Carbon::parse($workspace->settings['start_date'])->format('M d, Y') }}</span>
                            </div>
                            @endif
                            @if($workspace->settings['end_date'] ?? null)
                            <div class="flex justify-between">
                                <span class="text-base-content/60">End Date</span>
                                <span>{{ \Carbon\Carbon::parse($workspace->settings['end_date'])->format('M d, Y') }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Members -->
                <div class="card bg-base-100 shadow">
                    <div class="card-body">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="card-title text-lg">Members</h2>
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
                                    <p class="text-xs text-base-content/60">{{ $member->pivot->role->label() }}</p>
                                </div>
                            </div>
                            @endforeach
                            @if($workspace->members->count() > 5)
                            <a href="{{ route('workspace.members.index', $workspace) }}" class="btn btn-ghost btn-sm w-full">
                                View all {{ $workspace->members->count() }} members
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
