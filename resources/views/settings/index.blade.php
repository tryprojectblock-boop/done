@extends('layouts.app')

@section('content')
<div class="min-h-screen p-4 md:p-6">
    <div class="max-w-4xl mx-auto">
        <!-- Page Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-base-content">Settings</h1>
            <p class="text-base-content/60">Manage your account and application preferences</p>
        </div>

        <!-- Settings Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Profile Settings -->
            <a href="{{ route('profile.index') }}" class="card bg-base-100 shadow hover:shadow-lg transition-shadow">
                <div class="card-body">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-lg bg-primary/10 flex items-center justify-center">
                            <span class="icon-[tabler--user] size-6 text-primary"></span>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-base-content">Profile</h3>
                            <p class="text-sm text-base-content/60">Update your personal information and avatar</p>
                        </div>
                        <span class="icon-[tabler--chevron-right] size-5 text-base-content/40"></span>
                    </div>
                </div>
            </a>

            <!-- Password Settings -->
            <a href="{{ route('profile.password') }}" class="card bg-base-100 shadow hover:shadow-lg transition-shadow">
                <div class="card-body">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-lg bg-warning/10 flex items-center justify-center">
                            <span class="icon-[tabler--lock] size-6 text-warning"></span>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-base-content">Password</h3>
                            <p class="text-sm text-base-content/60">Change your password and security settings</p>
                        </div>
                        <span class="icon-[tabler--chevron-right] size-5 text-base-content/40"></span>
                    </div>
                </div>
            </a>

            <!-- Notification Settings -->
            <a href="{{ route('settings.notifications') }}" class="card bg-base-100 shadow hover:shadow-lg transition-shadow">
                <div class="card-body">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-lg bg-info/10 flex items-center justify-center">
                            <span class="icon-[tabler--bell] size-6 text-info"></span>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-base-content">Notifications</h3>
                            <p class="text-sm text-base-content/60">Configure email and browser notifications</p>
                        </div>
                        <span class="icon-[tabler--chevron-right] size-5 text-base-content/40"></span>
                    </div>
                </div>
            </a>

            <!-- Appearance Settings -->
            <a href="{{ route('settings.appearance') }}" class="card bg-base-100 shadow hover:shadow-lg transition-shadow">
                <div class="card-body">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-lg bg-secondary/10 flex items-center justify-center">
                            <span class="icon-[tabler--palette] size-6 text-secondary"></span>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-base-content">Appearance</h3>
                            <p class="text-sm text-base-content/60">Customize theme and display preferences</p>
                        </div>
                        <span class="icon-[tabler--chevron-right] size-5 text-base-content/40"></span>
                    </div>
                </div>
            </a>

            <!-- Activity -->
            <a href="{{ route('profile.activity') }}" class="card bg-base-100 shadow hover:shadow-lg transition-shadow">
                <div class="card-body">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-lg bg-success/10 flex items-center justify-center">
                            <span class="icon-[tabler--activity] size-6 text-success"></span>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-base-content">Activity Log</h3>
                            <p class="text-sm text-base-content/60">View your recent activity and history</p>
                        </div>
                        <span class="icon-[tabler--chevron-right] size-5 text-base-content/40"></span>
                    </div>
                </div>
            </a>

            @if($user->isAdminOrHigher())
            <!-- Company Settings (Admin/Owner only) -->
            <a href="{{ route('settings.company') }}" class="card bg-base-100 shadow hover:shadow-lg transition-shadow">
                <div class="card-body">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-lg bg-error/10 flex items-center justify-center">
                            <span class="icon-[tabler--building] size-6 text-error"></span>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-base-content">Company</h3>
                            <p class="text-sm text-base-content/60">Manage company profile and branding</p>
                        </div>
                        <span class="icon-[tabler--chevron-right] size-5 text-base-content/40"></span>
                    </div>
                </div>
            </a>
            @endif
        </div>

        <!-- Account Info Card -->
        <div class="card bg-base-100 shadow mt-6">
            <div class="card-body">
                <h2 class="card-title text-lg mb-4">
                    <span class="icon-[tabler--info-circle] size-5"></span>
                    Account Information
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-base-content/60">Email:</span>
                        <span class="ml-2 font-medium">{{ $user->email }}</span>
                    </div>
                    <div>
                        <span class="text-base-content/60">Role:</span>
                        <span class="badge badge-{{ $user->role_color }} badge-sm ml-2">{{ $user->role_label }}</span>
                    </div>
                    <div>
                        <span class="text-base-content/60">Company:</span>
                        <span class="ml-2 font-medium">{{ $company->name ?? 'N/A' }}</span>
                    </div>
                    <div>
                        <span class="text-base-content/60">Member since:</span>
                        <span class="ml-2 font-medium">{{ $user->created_at->format('M d, Y') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
