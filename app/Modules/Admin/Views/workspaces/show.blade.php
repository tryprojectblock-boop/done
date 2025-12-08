@extends('admin::layouts.app')

@section('title', $workspace->name)
@section('page-title', 'Workspace Details')

@section('content')
<div class="space-y-6">
    <!-- Breadcrumb -->
    <div class="text-sm breadcrumbs">
        <ul>
            <li><a href="{{ route('backoffice.workspaces.index') }}">Workspaces</a></li>
            <li>{{ $workspace->name }}</li>
        </ul>
    </div>

    <!-- Workspace Header -->
    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <div class="flex flex-col md:flex-row md:items-center gap-4">
                <div class="avatar placeholder">
                    <div class="bg-info text-info-content rounded-lg w-16 h-16">
                        <span class="text-2xl">{{ substr($workspace->name, 0, 2) }}</span>
                    </div>
                </div>
                <div class="flex-1">
                    <h1 class="text-2xl font-bold">{{ $workspace->name }}</h1>
                    @if($workspace->description)
                        <p class="text-base-content/60 mt-1">{{ $workspace->description }}</p>
                    @endif
                    <div class="flex flex-wrap gap-2 mt-2">
                        @if($workspace->archived_at)
                            <span class="badge badge-warning">Archived</span>
                        @else
                            <span class="badge badge-success">Active</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="card bg-base-100 shadow">
            <div class="card-body py-4">
                <p class="text-base-content/60 text-sm">Client</p>
                @if($workspace->owner?->company)
                    <a href="{{ route('backoffice.clients.show', $workspace->owner->company) }}" class="text-lg font-bold link link-primary">
                        {{ $workspace->owner->company->name }}
                    </a>
                @else
                    <p class="text-lg font-bold text-base-content/50">N/A</p>
                @endif
            </div>
        </div>
        <div class="card bg-base-100 shadow">
            <div class="card-body py-4">
                <p class="text-base-content/60 text-sm">Members</p>
                <p class="text-2xl font-bold">{{ $workspace->members->count() }}</p>
            </div>
        </div>
        <div class="card bg-base-100 shadow">
            <div class="card-body py-4">
                <p class="text-base-content/60 text-sm">Created</p>
                <p class="text-lg font-bold">{{ $workspace->created_at->format('M d, Y') }}</p>
            </div>
        </div>
    </div>

    <!-- Members -->
    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <h2 class="card-title text-lg mb-4">Members ({{ $workspace->members->count() }})</h2>
            <div class="overflow-x-auto">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Role</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($workspace->members as $member)
                            <tr>
                                <td>
                                    <div class="flex items-center gap-2">
                                        <div class="avatar">
                                            <div class="w-8 h-8 rounded-full">
                                                <img src="{{ $member->avatar_url }}" alt="{{ $member->name }}" />
                                            </div>
                                        </div>
                                        <div>
                                            <div class="font-medium">{{ $member->name }}</div>
                                            <div class="text-xs text-base-content/60">{{ $member->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge badge-{{ $member->role_color }} badge-sm">{{ $member->role_label }}</span></td>
                                <td>
                                    <span class="badge {{ $member->status === 'active' ? 'badge-success' : 'badge-warning' }} badge-sm">
                                        {{ ucfirst($member->status) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-base-content/50">No members</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
