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

    @include('admin::partials.alerts')

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
                                        <div class="flex items-center gap-1">
                                            <a href="{{ route('backoffice.settings.admins.edit', $admin) }}" class="btn btn-ghost btn-xs" title="Edit">
                                                <span class="icon-[tabler--edit] size-4"></span>
                                            </a>
                                            @if($admin->id !== auth('admin')->id())
                                                <button type="button"
                                                    class="btn btn-ghost btn-xs"
                                                    title="{{ $admin->is_active ? 'Deactivate' : 'Activate' }}"
                                                    data-confirm-modal
                                                    data-confirm-action="{{ route('backoffice.settings.admins.toggle-status', $admin) }}"
                                                    data-confirm-method="PATCH"
                                                    data-confirm-title="{{ $admin->is_active ? 'Deactivate Admin' : 'Activate Admin' }}"
                                                    data-confirm-message="{{ $admin->is_active ? 'Are you sure you want to deactivate ' . $admin->name . '? They will no longer be able to access the admin panel.' : 'Are you sure you want to activate ' . $admin->name . '? They will be able to access the admin panel.' }}"
                                                    data-confirm-button="{{ $admin->is_active ? 'Deactivate' : 'Activate' }}"
                                                    data-confirm-type="{{ $admin->is_active ? 'warning' : 'success' }}">
                                                    <span class="icon-[tabler--{{ $admin->is_active ? 'eye-off' : 'eye' }}] size-4"></span>
                                                </button>
                                                <button type="button"
                                                    class="btn btn-ghost btn-xs text-error"
                                                    title="Delete"
                                                    data-confirm-modal
                                                    data-confirm-action="{{ route('backoffice.settings.admins.destroy', $admin) }}"
                                                    data-confirm-method="DELETE"
                                                    data-confirm-title="Delete Admin User"
                                                    data-confirm-message="Are you sure you want to delete {{ $admin->name }}? This action cannot be undone."
                                                    data-confirm-button="Delete"
                                                    data-confirm-type="danger">
                                                    <span class="icon-[tabler--trash] size-4"></span>
                                                </button>
                                            @endif
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
