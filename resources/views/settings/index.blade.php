@extends('layouts.app')

@section('content')
<div class="min-h-screen p-4 md:p-6">
    <div class="max-w-4xl mx-auto">
        <!-- Page Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-base-content">Settings</h1>
            <p class="text-base-content/60">Manage your account and application preferences</p>
        </div>

        @if(session('success'))
            <div class="alert alert-success mb-6">
                <span class="icon-[tabler--check] size-5"></span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-error mb-6">
                <span class="icon-[tabler--x] size-5"></span>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Tabs Navigation -->
        <div class="inline-flex p-1 bg-base-200 rounded-xl mb-6">
            <a href="{{ route('settings.index', ['tab' => 'general']) }}"
               class="flex items-center gap-2 px-5 py-2.5 rounded-lg font-medium transition-all duration-200 {{ $tab === 'general' ? 'bg-primary text-primary-content shadow-sm' : 'text-base-content/60 hover:text-primary hover:bg-primary/10' }}">
                <span class="icon-[tabler--settings] size-5"></span>
                <span>General</span>
            </a>
            @if($user->isAdminOrHigher())
            <a href="{{ route('settings.index', ['tab' => 'marketplace']) }}"
               class="flex items-center gap-2 px-5 py-2.5 rounded-lg font-medium transition-all duration-200 {{ $tab === 'marketplace' ? 'bg-primary text-primary-content shadow-sm' : 'text-base-content/60 hover:text-primary hover:bg-primary/10' }}">
                <span class="icon-[tabler--apps] size-5"></span>
                <span>Marketplace</span>
            </a>
            @endif
        </div>

        <!-- General Settings Panel -->
        <div id="panel-general" class="{{ $tab !== 'general' ? 'hidden' : '' }}" role="tabpanel" aria-labelledby="tab-general">
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

                <!-- Billing & Subscription (Admin/Owner only) -->
                <a href="{{ route('settings.billing') }}" class="card bg-base-100 shadow hover:shadow-lg transition-shadow">
                    <div class="card-body">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-lg bg-accent/10 flex items-center justify-center">
                                <span class="icon-[tabler--credit-card] size-6 text-accent"></span>
                            </div>
                            <div class="flex-1">
                                <h3 class="font-semibold text-base-content">Billing & Plans</h3>
                                <p class="text-sm text-base-content/60">Manage subscription and billing</p>
                            </div>
                            <span class="icon-[tabler--chevron-right] size-5 text-base-content/40"></span>
                        </div>
                    </div>
                </a>

                <!-- Integrations (Admin/Owner only) -->
                <a href="{{ route('settings.integrations') }}" class="card bg-base-100 shadow hover:shadow-lg transition-shadow">
                    <div class="card-body">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-lg bg-purple-500/10 flex items-center justify-center">
                                <span class="icon-[tabler--plug-connected] size-6 text-purple-500"></span>
                            </div>
                            <div class="flex-1">
                                <h3 class="font-semibold text-base-content">Integrations</h3>
                                <p class="text-sm text-base-content/60">Configure Google Calendar and other integrations</p>
                            </div>
                            <span class="icon-[tabler--chevron-right] size-5 text-base-content/40"></span>
                        </div>
                    </div>
                </a>

                <!-- Mail Logs (Admin/Owner only) -->
                <a href="{{ route('settings.mail-logs') }}" class="card bg-base-100 shadow hover:shadow-lg transition-shadow">
                    <div class="card-body">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-lg bg-cyan-500/10 flex items-center justify-center">
                                <span class="icon-[tabler--mail] size-6 text-cyan-500"></span>
                            </div>
                            <div class="flex-1">
                                <h3 class="font-semibold text-base-content">Mail Logs</h3>
                                <p class="text-sm text-base-content/60">View all emails triggered by the application</p>
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

        <!-- Marketplace Panel (Admin/Owner only) -->
        @if($user->isAdminOrHigher())
        <div id="panel-marketplace" class="{{ $tab !== 'marketplace' ? 'hidden' : '' }}" role="tabpanel" aria-labelledby="tab-marketplace">

            <!-- Core Modules Section -->
            <div class="mb-10">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center">
                        <span class="icon-[tabler--puzzle] size-5 text-primary"></span>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-base-content">Core Modules</h2>
                        <p class="text-sm text-base-content/50">Essential tools to power your workspace</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <!-- CRM Module -->
                    @include('settings.partials.module-card', [
                        'name' => 'CRM Module',
                        'description' => 'Manage customer relationships, contacts, and client interactions',
                        'icon' => 'tabler--users-group',
                        'color' => '#3b82f6',
                        'status' => 'available',
                        'route' => route('guests.index'),
                        'enabled' => $moduleSettings['crm_enabled'] ?? true,
                    ])

                    <!-- Support Inbox -->
                    @include('settings.partials.module-card', [
                        'name' => 'Support Inbox',
                        'description' => 'Centralized inbox for customer support tickets and inquiries',
                        'icon' => 'tabler--inbox',
                        'color' => '#10b981',
                        'status' => 'coming_soon',
                    ])

                    <!-- Marketing Suite -->
                    @include('settings.partials.module-card', [
                        'name' => 'Marketing Suite',
                        'description' => 'Email campaigns, automation, and marketing analytics',
                        'icon' => 'tabler--speakerphone',
                        'color' => '#f59e0b',
                        'status' => 'coming_soon',
                    ])

                    <!-- Landing Page / A/B Testing -->
                    @include('settings.partials.module-card', [
                        'name' => 'Landing Pages & A/B Testing',
                        'description' => 'Create landing pages and run A/B tests to optimize conversions',
                        'icon' => 'tabler--layout-dashboard',
                        'color' => '#8b5cf6',
                        'status' => 'coming_soon',
                    ])

                    <!-- Reports & Analytics -->
                    @include('settings.partials.module-card', [
                        'name' => 'Reports & Analytics',
                        'description' => 'Comprehensive reporting and business intelligence dashboards',
                        'icon' => 'tabler--chart-bar',
                        'color' => '#06b6d4',
                        'status' => 'coming_soon',
                    ])

                    <!-- Client Portal -->
                    @include('settings.partials.module-card', [
                        'name' => 'Client Portal',
                        'description' => 'Secure portal for clients to view projects and collaborate',
                        'icon' => 'tabler--door-enter',
                        'color' => '#ec4899',
                        'status' => 'coming_soon',
                    ])

                    <!-- Billing & Invoice -->
                    @include('settings.partials.module-card', [
                        'name' => 'Billing & Invoice',
                        'description' => 'Create invoices, track payments, and manage billing',
                        'icon' => 'tabler--receipt',
                        'color' => '#22c55e',
                        'status' => 'coming_soon',
                    ])

                    <!-- Service Module -->
                    @include('settings.partials.module-card', [
                        'name' => 'Service Module',
                        'description' => 'Define and manage your service offerings and packages',
                        'icon' => 'tabler--briefcase',
                        'color' => '#6366f1',
                        'status' => 'coming_soon',
                    ])

                    <!-- Timesheet Management -->
                    @include('settings.partials.module-card', [
                        'name' => 'Timesheet Management',
                        'description' => 'Track time spent on tasks and projects for billing',
                        'icon' => 'tabler--clock-hour-4',
                        'color' => '#f97316',
                        'status' => 'coming_soon',
                    ])

                    <!-- Milestone -->
                    @include('settings.partials.module-card', [
                        'name' => 'Milestones',
                        'description' => 'Track project progress with milestones and deliverables',
                        'icon' => 'tabler--flag',
                        'color' => '#a855f7',
                        'status' => 'available',
                        'route' => route('marketplace.milestones'),
                        'enabled' => $milestonesStatus['enabled'] ?? true,
                    ])

                    <!-- Inventory -->
                    @include('settings.partials.module-card', [
                        'name' => 'Inventory',
                        'description' => 'Track products, stock levels, and inventory management',
                        'icon' => 'tabler--package',
                        'color' => '#64748b',
                        'status' => 'coming_soon',
                    ])

                    <!-- Sales Onboarding -->
                    @include('settings.partials.module-card', [
                        'name' => 'Sales Onboarding',
                        'description' => 'Streamline your sales process and client onboarding',
                        'icon' => 'tabler--rocket',
                        'color' => '#ef4444',
                        'status' => 'coming_soon',
                    ])
                </div>
            </div>

            <!-- Security & Authentication Section -->
            <div class="mb-10">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-success/10 flex items-center justify-center">
                        <span class="icon-[tabler--shield-check] size-5 text-success"></span>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-base-content">Security & Authentication</h2>
                        <p class="text-sm text-base-content/50">Protect your workspace with advanced security</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <!-- Two-Factor Authentication -->
                    <a href="{{ route('marketplace.two-factor') }}" class="group block">
                        <div class="card bg-base-100 border border-base-200 overflow-hidden transition-all duration-300 hover:shadow-xl hover:-translate-y-1 hover:border-primary/30">
                            <div class="h-1 w-full" style="background: linear-gradient(90deg, #3b82f6, #3b82f699);"></div>
                            <div class="card-body p-5">
                                <div class="flex items-start gap-4">
                                    <div class="relative">
                                        <div class="absolute inset-0 rounded-2xl blur-xl opacity-30 transition-opacity duration-300 group-hover:opacity-50" style="background-color: #3b82f6;"></div>
                                        <div class="relative w-14 h-14 rounded-2xl flex items-center justify-center shadow-lg transition-transform duration-300 group-hover:scale-110" style="background: linear-gradient(135deg, #3b82f6, #3b82f6cc);">
                                            <span class="icon-[tabler--shield-lock] size-7 text-white drop-shadow-sm"></span>
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 flex-wrap mb-1">
                                            <h3 class="font-bold text-base-content text-base group-hover:text-primary transition-colors duration-200">Two-Factor Auth</h3>
                                            @if($twoFactorStatus['enabled'])
                                                <span class="badge badge-success badge-xs">Enabled</span>
                                            @else
                                                <span class="badge badge-warning badge-xs">Disabled</span>
                                            @endif
                                        </div>
                                        <p class="text-sm text-base-content/60 line-clamp-2 leading-relaxed">Extra security with Google/Microsoft Authenticator</p>
                                    </div>
                                </div>
                                <div class="mt-4 pt-3 border-t border-base-200 flex items-center justify-between">
                                    <div class="flex items-center gap-2 {{ $twoFactorStatus['enabled'] ? 'text-success' : 'text-warning' }}">
                                        @if($twoFactorStatus['enabled'])
                                            <span class="icon-[tabler--circle-check-filled] size-4"></span>
                                            <span class="text-xs font-medium">Active</span>
                                        @else
                                            <span class="icon-[tabler--circle-x-filled] size-4"></span>
                                            <span class="text-xs font-medium">Inactive</span>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-1 text-primary text-sm font-medium group-hover:gap-2 transition-all duration-200">
                                        <span>Configure</span>
                                        <span class="icon-[tabler--arrow-right] size-4"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>

                    <!-- Block AI -->
                    @include('settings.partials.module-card', [
                        'name' => 'Block AI',
                        'description' => 'AI-powered assistance and automation for your workflows',
                        'icon' => 'tabler--robot',
                        'color' => '#8b5cf6',
                        'status' => 'coming_soon',
                    ])

                    <!-- Block Checkin -->
                    @include('settings.partials.module-card', [
                        'name' => 'Block Checkin',
                        'description' => 'Daily check-ins and team status updates',
                        'icon' => 'tabler--checklist',
                        'color' => '#10b981',
                        'status' => 'coming_soon',
                    ])

                    <!-- Project Checkin -->
                    @include('settings.partials.module-card', [
                        'name' => 'Project Checkin',
                        'description' => 'Regular project status updates and progress tracking',
                        'icon' => 'tabler--clipboard-check',
                        'color' => '#06b6d4',
                        'status' => 'coming_soon',
                    ])

                    <!-- Out of Office -->
                    <a href="{{ route('marketplace.out-of-office') }}" class="group block">
                        <div class="card bg-base-100 border border-base-200 overflow-hidden transition-all duration-300 hover:shadow-xl hover:-translate-y-1 hover:border-primary/30">
                            <div class="h-1 w-full" style="background: linear-gradient(90deg, #f59e0b, #f59e0b99);"></div>
                            <div class="card-body p-5">
                                <div class="flex items-start gap-4">
                                    <div class="relative">
                                        <div class="absolute inset-0 rounded-2xl blur-xl opacity-30 transition-opacity duration-300 group-hover:opacity-50" style="background-color: #f59e0b;"></div>
                                        <div class="relative w-14 h-14 rounded-2xl flex items-center justify-center shadow-lg transition-transform duration-300 group-hover:scale-110" style="background: linear-gradient(135deg, #f59e0b, #f59e0bcc);">
                                            <span class="icon-[tabler--plane-departure] size-7 text-white drop-shadow-sm"></span>
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 flex-wrap mb-1">
                                            <h3 class="font-bold text-base-content text-base group-hover:text-primary transition-colors duration-200">Out of Office</h3>
                                            @if($outOfOfficeStatus['enabled'])
                                                <span class="badge badge-success badge-xs">Enabled</span>
                                            @else
                                                <span class="badge badge-warning badge-xs">Disabled</span>
                                            @endif
                                        </div>
                                        <p class="text-sm text-base-content/60 line-clamp-2 leading-relaxed">Set availability status and auto-respond to comments</p>
                                    </div>
                                </div>
                                <div class="mt-4 pt-3 border-t border-base-200 flex items-center justify-between">
                                    <div class="flex items-center gap-2 {{ $outOfOfficeStatus['enabled'] ? 'text-success' : 'text-warning' }}">
                                        @if($outOfOfficeStatus['enabled'])
                                            <span class="icon-[tabler--circle-check-filled] size-4"></span>
                                            <span class="text-xs font-medium">Active</span>
                                        @else
                                            <span class="icon-[tabler--circle-x-filled] size-4"></span>
                                            <span class="text-xs font-medium">Inactive</span>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-1 text-primary text-sm font-medium group-hover:gap-2 transition-all duration-200">
                                        <span>Configure</span>
                                        <span class="icon-[tabler--arrow-right] size-4"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Calendar & Integrations Section -->
            <div class="mb-10">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-info/10 flex items-center justify-center">
                        <span class="icon-[tabler--calendar] size-5 text-info"></span>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-base-content">Calendar & Integrations</h2>
                        <p class="text-sm text-base-content/50">Connect with your favorite calendar apps</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <!-- Google Calendar -->
                    <a href="{{ route('marketplace.gmail-sync') }}" class="group block">
                        <div class="card bg-base-100 border border-base-200 overflow-hidden transition-all duration-300 hover:shadow-xl hover:-translate-y-1 hover:border-primary/30">
                            <div class="h-1 w-full" style="background: linear-gradient(90deg, #ea4335, #ea433599);"></div>
                            <div class="card-body p-5">
                                <div class="flex items-start gap-4">
                                    <div class="relative">
                                        <div class="absolute inset-0 rounded-2xl blur-xl opacity-30 transition-opacity duration-300 group-hover:opacity-50" style="background-color: #ea4335;"></div>
                                        <div class="relative w-14 h-14 rounded-2xl flex items-center justify-center shadow-lg transition-transform duration-300 group-hover:scale-110" style="background: linear-gradient(135deg, #ea4335, #ea4335cc);">
                                            <span class="icon-[tabler--brand-google] size-7 text-white drop-shadow-sm"></span>
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 flex-wrap mb-1">
                                            <h3 class="font-bold text-base-content text-base group-hover:text-primary transition-colors duration-200">Google Calendar</h3>
                                            @if($gmailSyncStatus['enabled'])
                                                <span class="badge badge-success badge-xs">Enabled</span>
                                            @elseif($gmailSyncStatus['installed'])
                                                <span class="badge badge-warning badge-xs">Disabled</span>
                                            @else
                                                <span class="badge badge-ghost badge-xs">Not Configured</span>
                                            @endif
                                        </div>
                                        <p class="text-sm text-base-content/60 line-clamp-2 leading-relaxed">Two-way sync with Google Calendar</p>
                                    </div>
                                </div>
                                <div class="mt-4 pt-3 border-t border-base-200 flex items-center justify-between">
                                    <div class="flex items-center gap-2 {{ $gmailSyncStatus['enabled'] ? 'text-success' : ($gmailSyncStatus['installed'] ? 'text-warning' : 'text-base-content/50') }}">
                                        @if($gmailSyncStatus['enabled'])
                                            <span class="icon-[tabler--circle-check-filled] size-4"></span>
                                            <span class="text-xs font-medium">Active</span>
                                        @elseif($gmailSyncStatus['installed'])
                                            <span class="icon-[tabler--circle-x-filled] size-4"></span>
                                            <span class="text-xs font-medium">Inactive</span>
                                        @else
                                            <span class="icon-[tabler--settings] size-4"></span>
                                            <span class="text-xs font-medium">Setup Required</span>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-1 text-primary text-sm font-medium group-hover:gap-2 transition-all duration-200">
                                        <span>Configure</span>
                                        <span class="icon-[tabler--arrow-right] size-4"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>

                    <!-- Outlook Calendar Sync -->
                    @include('settings.partials.module-card', [
                        'name' => 'Outlook Calendar',
                        'description' => 'Sync tasks and events with Microsoft Outlook',
                        'icon' => 'tabler--brand-windows',
                        'color' => '#0078d4',
                        'status' => 'coming_soon',
                    ])

                    <!-- Booking -->
                    @include('settings.partials.module-card', [
                        'name' => 'Booking',
                        'description' => 'Online scheduling and appointment booking system',
                        'icon' => 'tabler--calendar-event',
                        'color' => '#f59e0b',
                        'status' => 'coming_soon',
                    ])
                </div>
            </div>

            <!-- Storage & Cloud Section -->
            <div class="mb-10">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-secondary/10 flex items-center justify-center">
                        <span class="icon-[tabler--cloud] size-5 text-secondary"></span>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-base-content">Storage & Cloud Integrations</h2>
                        <p class="text-sm text-base-content/50">Manage files across multiple cloud platforms</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <!-- Drive (Built-in) -->
                    <a href="{{ route('drive.index') }}" class="group block">
                        <div class="card bg-base-100 border border-base-200 overflow-hidden transition-all duration-300 hover:shadow-xl hover:-translate-y-1 hover:border-primary/30">
                            <div class="h-1 w-full" style="background: linear-gradient(90deg, #3b82f6, #3b82f699);"></div>
                            <div class="card-body p-5">
                                <div class="flex items-start gap-4">
                                    <div class="relative">
                                        <div class="absolute inset-0 rounded-2xl blur-xl opacity-30 transition-opacity duration-300 group-hover:opacity-50" style="background-color: #3b82f6;"></div>
                                        <div class="relative w-14 h-14 rounded-2xl flex items-center justify-center shadow-lg transition-transform duration-300 group-hover:scale-110" style="background: linear-gradient(135deg, #3b82f6, #3b82f6cc);">
                                            <span class="icon-[tabler--cloud] size-7 text-white drop-shadow-sm"></span>
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 flex-wrap mb-1">
                                            <h3 class="font-bold text-base-content text-base group-hover:text-primary transition-colors duration-200">Block Drive</h3>
                                        </div>
                                        <p class="text-sm text-base-content/60 line-clamp-2 leading-relaxed">Built-in file storage and management</p>
                                    </div>
                                </div>
                                <div class="mt-4 pt-3 border-t border-base-200 flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="badge badge-success badge-sm">Active</span>
                                    </div>
                                    <div class="flex items-center gap-1 text-primary text-sm font-medium group-hover:gap-2 transition-all duration-200">
                                        <span>Open</span>
                                        <span class="icon-[tabler--arrow-right] size-4"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>

                    <!-- Google Drive -->
                    <a href="{{ route('marketplace.google-drive') }}" class="group block">
                        <div class="card bg-base-100 border border-base-200 overflow-hidden transition-all duration-300 hover:shadow-xl hover:-translate-y-1 hover:border-primary/30">
                            <div class="h-1 w-full" style="background: linear-gradient(90deg, #4285f4, #4285f499);"></div>
                            <div class="card-body p-5">
                                <div class="flex items-start gap-4">
                                    <div class="relative">
                                        <div class="absolute inset-0 rounded-2xl blur-xl opacity-30 transition-opacity duration-300 group-hover:opacity-50" style="background-color: #4285f4;"></div>
                                        <div class="relative w-14 h-14 rounded-2xl flex items-center justify-center shadow-lg transition-transform duration-300 group-hover:scale-110" style="background: linear-gradient(135deg, #4285f4, #4285f4cc);">
                                            <span class="icon-[tabler--brand-google-drive] size-7 text-white drop-shadow-sm"></span>
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 flex-wrap mb-1">
                                            <h3 class="font-bold text-base-content text-base group-hover:text-primary transition-colors duration-200">Google Drive</h3>
                                            @if($googleDriveStatus['enabled'])
                                                <span class="badge badge-success badge-xs">Enabled</span>
                                            @elseif($googleDriveStatus['installed'])
                                                <span class="badge badge-warning badge-xs">Disabled</span>
                                            @else
                                                <span class="badge badge-ghost badge-xs">Not Configured</span>
                                            @endif
                                        </div>
                                        <p class="text-sm text-base-content/60 line-clamp-2 leading-relaxed">Connect and sync files with Google Drive</p>
                                    </div>
                                </div>
                                <div class="mt-4 pt-3 border-t border-base-200 flex items-center justify-between">
                                    <div class="flex items-center gap-2 {{ $googleDriveStatus['enabled'] ? 'text-success' : ($googleDriveStatus['installed'] ? 'text-warning' : 'text-base-content/50') }}">
                                        @if($googleDriveStatus['enabled'])
                                            <span class="icon-[tabler--circle-check-filled] size-4"></span>
                                            <span class="text-xs font-medium">Active</span>
                                        @elseif($googleDriveStatus['installed'])
                                            <span class="icon-[tabler--circle-x-filled] size-4"></span>
                                            <span class="text-xs font-medium">Inactive</span>
                                        @else
                                            <span class="icon-[tabler--settings] size-4"></span>
                                            <span class="text-xs font-medium">Setup Required</span>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-1 text-primary text-sm font-medium group-hover:gap-2 transition-all duration-200">
                                        <span>Configure</span>
                                        <span class="icon-[tabler--arrow-right] size-4"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>

                    <!-- Box.com -->
                    @include('settings.partials.module-card', [
                        'name' => 'Box.com',
                        'description' => 'Enterprise content management with Box',
                        'icon' => 'tabler--box',
                        'color' => '#0061d5',
                        'status' => 'coming_soon',
                    ])

                    <!-- iCloud Drive -->
                    @include('settings.partials.module-card', [
                        'name' => 'iCloud Drive',
                        'description' => 'Sync with Apple iCloud Drive storage',
                        'icon' => 'tabler--brand-apple',
                        'color' => '#555555',
                        'status' => 'coming_soon',
                    ])

                    <!-- OneDrive -->
                    @include('settings.partials.module-card', [
                        'name' => 'OneDrive',
                        'description' => 'Microsoft OneDrive cloud storage integration',
                        'icon' => 'tabler--brand-onedrive',
                        'color' => '#0078d4',
                        'status' => 'coming_soon',
                    ])
                </div>
            </div>

            <!-- Info Card -->
            <div class="card bg-gradient-to-r from-primary/5 to-secondary/5 border border-primary/10">
                <div class="card-body">
                    <div class="flex items-start gap-3">
                        <span class="icon-[tabler--info-circle] size-6 text-primary flex-shrink-0 mt-0.5"></span>
                        <div>
                            <h3 class="font-semibold text-base-content mb-1">How Modules Work</h3>
                            <p class="text-sm text-base-content/60">
                                Modules can be enabled or disabled based on your plan. When a module is disabled, it will not appear in your application navigation.
                                Contact our support team for enterprise features or custom module development.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

@endsection
