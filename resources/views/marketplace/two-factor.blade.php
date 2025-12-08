@extends('layouts.app')

@section('content')
<div class="min-h-screen p-4 md:p-6">
    <div class="max-w-2xl mx-auto">
        <!-- Breadcrumb -->
        <div class="text-sm breadcrumbs mb-4">
            <ul>
                <li><a href="{{ route('marketplace.index') }}">Marketplace</a></li>
                <li>Two-Factor Authentication</li>
            </ul>
        </div>

        <!-- Page Header -->
        <div class="mb-6">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-12 h-12 rounded-lg bg-primary/10 flex items-center justify-center">
                    <span class="icon-[tabler--shield-lock] size-6 text-primary"></span>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-base-content">Two-Factor Authentication</h1>
                    <span class="badge badge-{{ $twoFactorStatus['status_color'] }}">
                        {{ $twoFactorStatus['status_label'] }}
                    </span>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success mb-6">
                <span class="icon-[tabler--check] size-5"></span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-error mb-6">
                <span class="icon-[tabler--alert-circle] size-5"></span>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Description Card -->
        <div class="card bg-base-100 shadow mb-6">
            <div class="card-body">
                <h2 class="card-title text-lg">
                    <span class="icon-[tabler--info-circle] size-5"></span>
                    About Two-Factor Authentication
                </h2>
                <p class="text-base-content/70 mb-4">
                    Two-Factor Authentication (2FA) adds an extra layer of security to your organization's accounts.
                    When enabled, team members will need to enter a verification code from their authenticator app
                    in addition to their password when logging in.
                </p>

                <div class="space-y-3">
                    <div class="flex items-start gap-3">
                        <span class="icon-[tabler--check] size-5 text-success flex-shrink-0 mt-0.5"></span>
                        <div>
                            <span class="font-medium text-base-content">Supported Authenticator Apps</span>
                            <p class="text-sm text-base-content/60">Google Authenticator, Microsoft Authenticator, Authy, and other TOTP-compatible apps</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="icon-[tabler--check] size-5 text-success flex-shrink-0 mt-0.5"></span>
                        <div>
                            <span class="font-medium text-base-content">Enhanced Security</span>
                            <p class="text-sm text-base-content/60">Protect against unauthorized access even if passwords are compromised</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="icon-[tabler--check] size-5 text-success flex-shrink-0 mt-0.5"></span>
                        <div>
                            <span class="font-medium text-base-content">User-Friendly Setup</span>
                            <p class="text-sm text-base-content/60">Easy QR code scanning setup for all team members</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status and Action Card -->
        <div class="card bg-base-100 shadow mb-6">
            <div class="card-body">
                <h2 class="card-title text-lg mb-4">
                    <span class="icon-[tabler--settings] size-5"></span>
                    Configuration
                </h2>

                @if($twoFactorStatus['enabled'])
                    <!-- Enabled State -->
                    <div class="alert alert-success mb-4">
                        <span class="icon-[tabler--shield-check] size-5"></span>
                        <div>
                            <h3 class="font-bold">Two-Factor Authentication is Enabled</h3>
                            <p class="text-sm">All team members will be required to set up 2FA on their next login.</p>
                        </div>
                    </div>

                    <div class="bg-base-200 rounded-lg p-4 mb-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="font-medium text-base-content">Organization 2FA Requirement</span>
                                <p class="text-sm text-base-content/60">Team members must use 2FA to access the application</p>
                            </div>
                            <span class="badge badge-success">Active</span>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3">
                        <form action="{{ route('marketplace.two-factor.disable') }}" method="POST" onsubmit="return confirm('Are you sure you want to disable Two-Factor Authentication? This will reduce security for your organization.')">
                            @csrf
                            <button type="submit" class="btn btn-error btn-outline">
                                <span class="icon-[tabler--shield-off] size-5"></span>
                                Disable 2FA
                            </button>
                        </form>
                    </div>
                @else
                    <!-- Disabled State -->
                    <div class="alert alert-warning mb-4">
                        <span class="icon-[tabler--alert-triangle] size-5"></span>
                        <div>
                            <h3 class="font-bold">Two-Factor Authentication is Disabled</h3>
                            <p class="text-sm">Enable 2FA to add an extra layer of security for your organization.</p>
                        </div>
                    </div>

                    <div class="bg-base-200 rounded-lg p-4 mb-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="font-medium text-base-content">Organization 2FA Requirement</span>
                                <p class="text-sm text-base-content/60">Team members can optionally use 2FA</p>
                            </div>
                            <span class="badge badge-ghost">Inactive</span>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3">
                        <form action="{{ route('marketplace.two-factor.enable') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-primary">
                                <span class="icon-[tabler--shield-check] size-5"></span>
                                Enable 2FA
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>

        <!-- How It Works Card -->
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <h2 class="card-title text-lg mb-4">
                    <span class="icon-[tabler--help] size-5"></span>
                    How It Works
                </h2>

                <div class="space-y-4">
                    <div class="flex items-start gap-4">
                        <div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center text-primary-content font-bold flex-shrink-0">
                            1
                        </div>
                        <div>
                            <span class="font-medium text-base-content">Enable 2FA for Organization</span>
                            <p class="text-sm text-base-content/60">As an admin, enable Two-Factor Authentication for your organization using the button above.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4">
                        <div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center text-primary-content font-bold flex-shrink-0">
                            2
                        </div>
                        <div>
                            <span class="font-medium text-base-content">Team Members Set Up</span>
                            <p class="text-sm text-base-content/60">On their next login, team members will be prompted to set up 2FA using their preferred authenticator app.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4">
                        <div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center text-primary-content font-bold flex-shrink-0">
                            3
                        </div>
                        <div>
                            <span class="font-medium text-base-content">Secure Access</span>
                            <p class="text-sm text-base-content/60">Each login will require the user's password plus a 6-digit code from their authenticator app.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Back Button -->
        <div class="mt-6">
            <a href="{{ route('marketplace.index') }}" class="btn btn-ghost">
                <span class="icon-[tabler--arrow-left] size-5"></span>
                Back to Marketplace
            </a>
        </div>
    </div>
</div>
@endsection
