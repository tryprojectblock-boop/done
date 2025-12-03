@extends('layouts.app')

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
                    <p class="text-base-content/60">Manage your team workspaces</p>
                </div>
                <a href="{{ route('workspace.create') }}" class="btn btn-primary">
                    <span class="icon-[tabler--plus] size-5"></span>
                    Add Workspace
                </a>
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

        <!-- Workspaces Grid -->
        @if($workspaces->isEmpty())
            <div class="card bg-base-100 shadow">
                <div class="card-body text-center py-12">
                    <div class="flex justify-center mb-4">
                        <span class="icon-[tabler--briefcase] size-16 text-base-content/20"></span>
                    </div>
                    <h3 class="text-lg font-semibold text-base-content">No Workspaces Yet</h3>
                    <p class="text-base-content/60 mb-4">Create your first workspace to start organizing your projects and team.</p>
                    <div>
                        <a href="{{ route('workspace.create') }}" class="btn btn-primary">
                            <span class="icon-[tabler--plus] size-5"></span>
                            Create Workspace
                        </a>
                    </div>
                </div>
            </div>
        @else
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
    </div>
</div>
@endsection
