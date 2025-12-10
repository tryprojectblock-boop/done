@extends('layouts.app')

@section('content')
<div class="min-h-screen p-4 md:p-6">
    <div class="max-w-2xl mx-auto">
        <!-- Breadcrumb -->
        <div class="text-sm breadcrumbs mb-4">
            <ul>
                <li><a href="{{ route('marketplace.index') }}">Marketplace</a></li>
                <li>Gmail Calendar Sync</li>
            </ul>
        </div>

        <!-- Page Header -->
        <div class="mb-6">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-12 h-12 rounded-lg bg-error/10 flex items-center justify-center">
                    <span class="icon-[tabler--brand-google] size-6 text-error"></span>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-base-content">Gmail Calendar Sync</h1>
                    <span class="badge badge-{{ $gmailSyncStatus['status_color'] }}">
                        {{ $gmailSyncStatus['status_label'] }}
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
                    About Gmail Calendar Sync
                </h2>
                <p class="text-base-content/70 mb-4">
                    Gmail Calendar Sync enables two-way synchronization between Project Block and Google Calendar.
                    When enabled, your tasks with due dates will automatically appear in Google Calendar,
                    and events created in Google Calendar can be synced back to Project Block.
                </p>

                <div class="space-y-3">
                    <div class="flex items-start gap-3">
                        <span class="icon-[tabler--check] size-5 text-success flex-shrink-0 mt-0.5"></span>
                        <div>
                            <span class="font-medium text-base-content">Two-Way Sync</span>
                            <p class="text-sm text-base-content/60">Changes in either platform are automatically reflected in the other</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="icon-[tabler--check] size-5 text-success flex-shrink-0 mt-0.5"></span>
                        <div>
                            <span class="font-medium text-base-content">Task Due Dates</span>
                            <p class="text-sm text-base-content/60">Tasks with due dates appear as events in your Google Calendar</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="icon-[tabler--check] size-5 text-success flex-shrink-0 mt-0.5"></span>
                        <div>
                            <span class="font-medium text-base-content">Per-User Connection</span>
                            <p class="text-sm text-base-content/60">Each team member connects their own Google account for personal calendar sync</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="icon-[tabler--check] size-5 text-success flex-shrink-0 mt-0.5"></span>
                        <div>
                            <span class="font-medium text-base-content">Real-Time Updates</span>
                            <p class="text-sm text-base-content/60">Events sync automatically without manual intervention</p>
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

                @if(!$gmailSyncStatus['installed'])
                    <!-- Not Configured State -->
                    <div class="alert alert-warning mb-4">
                        <span class="icon-[tabler--alert-triangle] size-5"></span>
                        <div>
                            <h3 class="font-bold">Google API Not Configured</h3>
                            <p class="text-sm">Please configure Google API credentials in your integration settings to enable this feature.</p>
                        </div>
                    </div>

                    <div class="bg-base-200 rounded-lg p-4 mb-4">
                        <p class="text-sm text-base-content/70 mb-2">To set up Google Calendar Sync:</p>
                        <ol class="text-sm text-base-content/60 space-y-1 list-decimal list-inside">
                            <li>Go to Settings > Integrations</li>
                            <li>Configure your Google API credentials</li>
                            <li>Enable Gmail Calendar Sync</li>
                        </ol>
                    </div>

                    <a href="{{ route('settings.integrations') }}" class="btn btn-primary">
                        <span class="icon-[tabler--settings] size-5"></span>
                        Go to Integration Settings
                    </a>
                @elseif($gmailSyncStatus['enabled'])
                    <!-- Enabled State -->
                    <div class="alert alert-success mb-4">
                        <span class="icon-[tabler--calendar-check] size-5"></span>
                        <div>
                            <h3 class="font-bold">Gmail Calendar Sync is Enabled</h3>
                            <p class="text-sm">Team members can now connect their Google accounts to sync calendars.</p>
                        </div>
                    </div>

                    <div class="bg-base-200 rounded-lg p-4 mb-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="font-medium text-base-content">Google Calendar Integration</span>
                                <p class="text-sm text-base-content/60">Organization members can connect their Google accounts</p>
                            </div>
                            <span class="badge badge-success">Active</span>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3">
                        <button type="button" class="btn btn-error btn-outline"
                            data-confirm
                            data-confirm-action="{{ route('marketplace.gmail-sync.disable') }}"
                            data-confirm-title="Disable Gmail Calendar Sync"
                            data-confirm-content="<p class='text-base-content/70'>Are you sure you want to disable Gmail Calendar Sync?</p><p class='text-base-content/70 mt-2'>This will disconnect all users from Google Calendar.</p>"
                            data-confirm-button="Disable Sync"
                            data-confirm-icon="tabler--unlink"
                            data-confirm-class="btn-error"
                            data-confirm-icon-class="text-error"
                            data-confirm-title-icon="tabler--alert-triangle"
                            data-confirm-method="POST">
                            <span class="icon-[tabler--unlink] size-5"></span>
                            Disable Sync
                        </button>
                    </div>
                @else
                    <!-- Disabled State -->
                    <div class="alert alert-info mb-4">
                        <span class="icon-[tabler--calendar] size-5"></span>
                        <div>
                            <h3 class="font-bold">Gmail Calendar Sync is Disabled</h3>
                            <p class="text-sm">Enable this feature to allow team members to sync their Google Calendars.</p>
                        </div>
                    </div>

                    <div class="bg-base-200 rounded-lg p-4 mb-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="font-medium text-base-content">Google Calendar Integration</span>
                                <p class="text-sm text-base-content/60">Organization members cannot connect Google accounts</p>
                            </div>
                            <span class="badge badge-ghost">Inactive</span>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3">
                        <form action="{{ route('marketplace.gmail-sync.enable') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-primary">
                                <span class="icon-[tabler--link] size-5"></span>
                                Enable Sync
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
                            <span class="font-medium text-base-content">Enable Gmail Sync</span>
                            <p class="text-sm text-base-content/60">As an admin, enable Gmail Calendar Sync for your organization using the button above.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4">
                        <div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center text-primary-content font-bold flex-shrink-0">
                            2
                        </div>
                        <div>
                            <span class="font-medium text-base-content">Team Members Connect</span>
                            <p class="text-sm text-base-content/60">Each team member can connect their Google account from their profile settings.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4">
                        <div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center text-primary-content font-bold flex-shrink-0">
                            3
                        </div>
                        <div>
                            <span class="font-medium text-base-content">Automatic Sync</span>
                            <p class="text-sm text-base-content/60">Tasks with due dates will automatically sync to Google Calendar, and calendar events sync back to Project Block.</p>
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
