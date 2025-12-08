@extends('admin::layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-base-content/60 text-sm">Total Companies</p>
                        <h3 class="text-3xl font-bold text-base-content">{{ number_format($stats['total_companies']) }}</h3>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center">
                        <span class="icon-[tabler--building] size-6 text-primary"></span>
                    </div>
                </div>
                <p class="text-xs text-success mt-2">
                    <span class="icon-[tabler--trending-up] size-4"></span>
                    +{{ $stats['new_companies_this_month'] }} this month
                </p>
            </div>
        </div>

        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-base-content/60 text-sm">Total Users</p>
                        <h3 class="text-3xl font-bold text-base-content">{{ number_format($stats['total_users']) }}</h3>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-info/10 flex items-center justify-center">
                        <span class="icon-[tabler--users] size-6 text-info"></span>
                    </div>
                </div>
                <p class="text-xs text-success mt-2">
                    <span class="icon-[tabler--trending-up] size-4"></span>
                    +{{ $stats['new_users_this_month'] }} this month
                </p>
            </div>
        </div>

        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-base-content/60 text-sm">Active Users</p>
                        <h3 class="text-3xl font-bold text-base-content">{{ number_format($stats['active_users']) }}</h3>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-success/10 flex items-center justify-center">
                        <span class="icon-[tabler--user-check] size-6 text-success"></span>
                    </div>
                </div>
                <p class="text-xs text-base-content/50 mt-2">
                    {{ $stats['total_users'] > 0 ? round(($stats['active_users'] / $stats['total_users']) * 100) : 0 }}% of total users
                </p>
            </div>
        </div>

        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-base-content/60 text-sm">Workspaces</p>
                        <h3 class="text-3xl font-bold text-base-content">{{ number_format($stats['total_workspaces']) }}</h3>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-warning/10 flex items-center justify-center">
                        <span class="icon-[tabler--briefcase] size-6 text-warning"></span>
                    </div>
                </div>
                <p class="text-xs text-base-content/50 mt-2">
                    Across all companies
                </p>
            </div>
        </div>
    </div>

    <!-- Recent Data -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Companies -->
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="card-title text-lg">Recent Companies</h2>
                    <a href="{{ route('backoffice.clients.index') }}" class="btn btn-ghost btn-sm">
                        View All
                        <span class="icon-[tabler--arrow-right] size-4"></span>
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Company</th>
                                <th>Owner</th>
                                <th>Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentCompanies as $company)
                                <tr>
                                    <td>
                                        <a href="{{ route('backoffice.clients.show', $company) }}" class="font-medium hover:text-primary">
                                            {{ $company->name }}
                                        </a>
                                    </td>
                                    <td class="text-base-content/60">{{ $company->owner?->email ?? 'N/A' }}</td>
                                    <td class="text-base-content/60 text-sm">{{ $company->created_at->diffForHumans() }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-base-content/50">No companies yet</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Recent Users -->
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="card-title text-lg">Recent Users</h2>
                    <a href="{{ route('backoffice.clients.index') }}" class="btn btn-ghost btn-sm">
                        View All
                        <span class="icon-[tabler--arrow-right] size-4"></span>
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Company</th>
                                <th>Joined</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentUsers as $user)
                                <tr>
                                    <td>
                                        <div class="flex items-center gap-2">
                                            <div class="avatar">
                                                <div class="w-8 h-8 rounded-full">
                                                    <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" />
                                                </div>
                                            </div>
                                            <div>
                                                <div class="font-medium">{{ $user->name }}</div>
                                                <div class="text-xs text-base-content/60">{{ $user->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-base-content/60">{{ $user->company?->name ?? 'N/A' }}</td>
                                    <td class="text-base-content/60 text-sm">{{ $user->created_at->diffForHumans() }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-base-content/50">No users yet</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
