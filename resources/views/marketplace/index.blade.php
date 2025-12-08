@extends('layouts.app')

@section('content')
<div class="min-h-screen p-4 md:p-6">
    <div class="max-w-4xl mx-auto">
        <!-- Page Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-base-content">Marketplace</h1>
            <p class="text-base-content/60">Enable and configure additional features for your organization</p>
        </div>

        @if(session('success'))
            <div class="alert alert-success mb-6">
                <span class="icon-[tabler--check] size-5"></span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Features Section -->
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-base-content mb-4">Security Features</h2>

            <!-- Features Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Two-Factor Authentication Card -->
                <a href="{{ route('marketplace.two-factor') }}" class="card bg-base-100 shadow hover:shadow-lg transition-shadow">
                    <div class="card-body">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-lg bg-primary/10 flex items-center justify-center flex-shrink-0">
                                <span class="icon-[tabler--shield-lock] size-6 text-primary"></span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <h3 class="font-semibold text-base-content">Two-Factor Authentication</h3>
                                    <span class="badge badge-{{ $twoFactorStatus['status_color'] }} badge-sm">
                                        {{ $twoFactorStatus['status_label'] }}
                                    </span>
                                </div>
                                <p class="text-sm text-base-content/60">
                                    Add an extra layer of security using Google Authenticator or Microsoft Authenticator
                                </p>
                            </div>
                            <span class="icon-[tabler--chevron-right] size-5 text-base-content/40 flex-shrink-0"></span>
                        </div>
                    </div>
                </a>

                <!-- Coming Soon: SSO Card -->
                <div class="card bg-base-100 shadow opacity-60 cursor-not-allowed">
                    <div class="card-body">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-lg bg-secondary/10 flex items-center justify-center flex-shrink-0">
                                <span class="icon-[tabler--key] size-6 text-secondary"></span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <h3 class="font-semibold text-base-content">Single Sign-On (SSO)</h3>
                                    <span class="badge badge-ghost badge-sm">Coming Soon</span>
                                </div>
                                <p class="text-sm text-base-content/60">
                                    Enable SSO with SAML 2.0 or OAuth providers for seamless authentication
                                </p>
                            </div>
                            <span class="icon-[tabler--chevron-right] size-5 text-base-content/40 flex-shrink-0"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Calendar & Sync Section -->
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-base-content mb-4">Calendar & Sync</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Gmail Calendar Sync Card -->
                <a href="{{ route('marketplace.gmail-sync') }}" class="card bg-base-100 shadow hover:shadow-lg transition-shadow">
                    <div class="card-body">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-lg bg-error/10 flex items-center justify-center flex-shrink-0">
                                <span class="icon-[tabler--brand-google] size-6 text-error"></span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <h3 class="font-semibold text-base-content">Gmail Calendar Sync</h3>
                                    <span class="badge badge-{{ $gmailSyncStatus['status_color'] }} badge-sm">
                                        {{ $gmailSyncStatus['status_label'] }}
                                    </span>
                                </div>
                                <p class="text-sm text-base-content/60">
                                    Two-way sync between Project Block and Google Calendar - tasks sync to calendar, events sync back
                                </p>
                            </div>
                            <span class="icon-[tabler--chevron-right] size-5 text-base-content/40 flex-shrink-0"></span>
                        </div>
                    </div>
                </a>

                <!-- Coming Soon: Outlook Calendar Sync -->
                <div class="card bg-base-100 shadow opacity-60 cursor-not-allowed">
                    <div class="card-body">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-lg bg-info/10 flex items-center justify-center flex-shrink-0">
                                <span class="icon-[tabler--brand-windows] size-6 text-info"></span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <h3 class="font-semibold text-base-content">Outlook Calendar Sync</h3>
                                    <span class="badge badge-ghost badge-sm">Coming Soon</span>
                                </div>
                                <p class="text-sm text-base-content/60">
                                    Sync your tasks and events with Microsoft Outlook Calendar
                                </p>
                            </div>
                            <span class="icon-[tabler--chevron-right] size-5 text-base-content/40 flex-shrink-0"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Integrations Section -->
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-base-content mb-4">Integrations</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Coming Soon: Slack Integration -->
                <div class="card bg-base-100 shadow opacity-60 cursor-not-allowed">
                    <div class="card-body">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-lg bg-purple-500/10 flex items-center justify-center flex-shrink-0">
                                <span class="icon-[tabler--brand-slack] size-6 text-purple-500"></span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <h3 class="font-semibold text-base-content">Slack Integration</h3>
                                    <span class="badge badge-ghost badge-sm">Coming Soon</span>
                                </div>
                                <p class="text-sm text-base-content/60">
                                    Get notifications and updates directly in your Slack channels
                                </p>
                            </div>
                            <span class="icon-[tabler--chevron-right] size-5 text-base-content/40 flex-shrink-0"></span>
                        </div>
                    </div>
                </div>

                <!-- Coming Soon: Zapier Integration -->
                <div class="card bg-base-100 shadow opacity-60 cursor-not-allowed">
                    <div class="card-body">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-lg bg-warning/10 flex items-center justify-center flex-shrink-0">
                                <span class="icon-[tabler--plug] size-6 text-warning"></span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <h3 class="font-semibold text-base-content">Zapier Integration</h3>
                                    <span class="badge badge-ghost badge-sm">Coming Soon</span>
                                </div>
                                <p class="text-sm text-base-content/60">
                                    Connect with thousands of apps through Zapier automation
                                </p>
                            </div>
                            <span class="icon-[tabler--chevron-right] size-5 text-base-content/40 flex-shrink-0"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Info Card -->
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <div class="flex items-start gap-3">
                    <span class="icon-[tabler--info-circle] size-5 text-info flex-shrink-0 mt-0.5"></span>
                    <div>
                        <h3 class="font-semibold text-base-content mb-1">Need a custom integration?</h3>
                        <p class="text-sm text-base-content/60">
                            Contact our support team to discuss custom integrations and enterprise features for your organization.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
