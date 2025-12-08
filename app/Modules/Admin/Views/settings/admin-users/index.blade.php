@extends('admin::layouts.app')

@section('title', 'Admin Users')
@section('page-title', 'Admin Users')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-base-content">Admin Users</h1>
            <p class="text-base-content/60">Manage administrator accounts</p>
        </div>
        @if(auth('admin')->user()->isAdministrator())
            <a href="{{ route('backoffice.settings.admins.create') }}" class="btn btn-primary">
                <span class="icon-[tabler--plus] size-5"></span>
                Add Admin
            </a>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            <span class="icon-[tabler--circle-check] size-5"></span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-error">
            <span class="icon-[tabler--alert-circle] size-5"></span>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Admin Users Table -->
    <div class="card bg-base-100 shadow">
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Admin</th>
                            <th>Role</th>
                            <th>Last Login</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($admins as $admin)
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="avatar placeholder">
                                            <div class="bg-primary text-primary-content rounded-full w-10 h-10">
                                                <span>{{ strtoupper(substr($admin->name, 0, 2)) }}</span>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="font-medium">{{ $admin->name }}</div>
                                            <div class="text-xs text-base-content/60">{{ $admin->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge {{ $admin->role->value === 'administrator' ? 'badge-primary' : 'badge-ghost' }}">
                                        {{ $admin->role->label() }}
                                    </span>
                                </td>
                                <td class="text-sm text-base-content/60">
                                    {{ $admin->last_login_at ? $admin->last_login_at->diffForHumans() : 'Never' }}
                                </td>
                                <td>
                                    <span class="badge {{ $admin->is_active ? 'badge-success' : 'badge-error' }}">
                                        {{ $admin->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="text-sm text-base-content/60">
                                    {{ $admin->created_at->format('M d, Y') }}
                                </td>
                                <td>
                                    @if(auth('admin')->user()->isAdministrator())
                                        <div class="dropdown dropdown-end">
                                            <label tabindex="0" class="btn btn-ghost btn-sm btn-square">
                                                <span class="icon-[tabler--dots-vertical] size-4"></span>
                                            </label>
                                            <ul tabindex="0" class="dropdown-menu dropdown-menu-sm">
                                                <li>
                                                    <a href="{{ route('backoffice.settings.admins.edit', $admin) }}">
                                                        <span class="icon-[tabler--edit] size-4"></span>
                                                        Edit
                                                    </a>
                                                </li>
                                                @if($admin->id !== auth('admin')->id())
                                                    <li>
                                                        <form action="{{ route('backoffice.settings.admins.toggle-status', $admin) }}" method="POST">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button type="submit" class="text-warning">
                                                                <span class="icon-[tabler--{{ $admin->is_active ? 'ban' : 'check' }}] size-4"></span>
                                                                {{ $admin->is_active ? 'Deactivate' : 'Activate' }}
                                                            </button>
                                                        </form>
                                                    </li>
                                                    <li>
                                                        <form action="{{ route('backoffice.settings.admins.destroy', $admin) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this admin?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="text-error">
                                                                <span class="icon-[tabler--trash] size-4"></span>
                                                                Delete
                                                            </button>
                                                        </form>
                                                    </li>
                                                @endif
                                            </ul>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-8">
                                    <span class="icon-[tabler--users-off] size-12 text-base-content/20 mb-2"></span>
                                    <p class="text-base-content/60">No admin users found</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($admins->hasPages())
                <div class="p-4 border-t border-base-200">
                    {{ $admins->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
