@extends('admin::layouts.app')

@section('title', $company->name . ' Users')
@section('page-title', 'Client Users')

@section('content')
<div class="space-y-6">
    <!-- Breadcrumb -->
    <div class="text-sm breadcrumbs">
        <ul>
            <li><a href="{{ route('backoffice.clients.index') }}">Clients</a></li>
            <li><a href="{{ route('backoffice.clients.show', $company) }}">{{ $company->name }}</a></li>
            <li>Users</li>
        </ul>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            <span class="icon-[tabler--circle-check] size-5"></span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold">{{ $company->name }} - Users</h1>
            <p class="text-base-content/60">{{ $users->total() }} users in this company</p>
        </div>
    </div>

    <!-- Users Table -->
    <div class="card bg-base-100 shadow">
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Last Login</th>
                            <th>Joined</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="avatar">
                                            <div class="w-10 h-10 rounded-full">
                                                <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" />
                                            </div>
                                        </div>
                                        <div>
                                            <div class="font-medium">{{ $user->name }}</div>
                                            <div class="text-sm text-base-content/60">{{ $user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $user->role_color }}">{{ $user->role_label }}</span>
                                </td>
                                <td>
                                    <span class="badge {{ $user->status === 'active' ? 'badge-success' : 'badge-warning' }}">
                                        {{ ucfirst($user->status) }}
                                    </span>
                                </td>
                                <td class="text-sm text-base-content/60">
                                    {{ $user->last_login_at?->diffForHumans() ?? 'Never' }}
                                </td>
                                <td class="text-sm text-base-content/60">
                                    {{ $user->created_at->format('M d, Y') }}
                                </td>
                                <td>
                                    <form action="{{ route('backoffice.users.toggle-status', $user) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-ghost btn-sm" onclick="return confirm('Are you sure?')">
                                            @if($user->status === 'active')
                                                <span class="icon-[tabler--user-x] size-4 text-warning"></span>
                                                Suspend
                                            @else
                                                <span class="icon-[tabler--user-check] size-4 text-success"></span>
                                                Activate
                                            @endif
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-8">
                                    <span class="icon-[tabler--users-off] size-12 text-base-content/20 mb-2"></span>
                                    <p class="text-base-content/60">No users found</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($users->hasPages())
                <div class="p-4 border-t border-base-200">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
