@extends('layouts.app')

@section('content')
<div class="p-4 md:p-6">
    <div class="max-w-6xl mx-auto">
        <!-- Breadcrumb -->
        <div class="flex items-center gap-2 text-sm text-base-content/60 mb-4">
            <a href="{{ route('workspace.index') }}" class="hover:text-primary">Workspaces</a>
            <span class="icon-[tabler--chevron-right] size-4"></span>
            <span>{{ $workspace->name }}</span>
            <span class="badge badge-warning badge-sm ml-2">Guest Access</span>
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
                            <div class="bg-warning text-warning-content rounded-lg w-16 h-16 flex items-center justify-center">
                                <span class="text-2xl font-bold">{{ substr($workspace->name, 0, 1) }}</span>
                            </div>
                        @endif
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2 flex-wrap">
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

        <!-- Guest Notice -->
        <div class="alert alert-warning mb-6">
            <span class="icon-[tabler--info-circle] size-5"></span>
            <div>
                <h3 class="font-bold">Guest Access</h3>
                <p class="text-sm">You have limited access to this workspace. You can view shared content but cannot make changes.</p>
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
                        <h2 class="text-xl font-semibold mb-2">Shared Content</h2>
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

                <!-- Back Button -->
                <a href="{{ route('workspace.index') }}" class="btn btn-ghost w-full">
                    <span class="icon-[tabler--arrow-left] size-5"></span>
                    Back to Workspaces
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
