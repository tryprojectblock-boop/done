@extends('layouts.app')

@section('content')
<div class="min-h-screen p-4 md:p-6">
    <div class="max-w-2xl mx-auto">
        <!-- Back Button -->
        <div class="mb-4">
            <a href="{{ route('marketplace.index') }}" class="btn btn-ghost btn-sm gap-1">
                <span class="icon-[tabler--arrow-left] size-4"></span>
                Back to Marketplace
            </a>
        </div>

        <!-- Page Header -->
        <div class="mb-6">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-12 h-12 rounded-lg flex items-center justify-center" style="background-color: rgba(16, 185, 129, 0.1);">
                    <span class="icon-[tabler--checkbox] size-6" style="color: #10b981;"></span>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-base-content">Daily Standup</h1>
                    <span class="badge badge-{{ $dailyStandupStatus['status_color'] }}">
                        {{ $dailyStandupStatus['status_label'] }}
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
                    About Daily Standup
                </h2>
                <p class="text-base-content/70 mb-4">
                    The Daily Standup feature enables async daily check-ins for your team. Members can share what they worked on,
                    what they're planning, and any blockers they're facing. Track team mood and member status at a glance.
                </p>

                <div class="space-y-3">
                    <div class="flex items-start gap-3">
                        <span class="icon-[tabler--check] size-5 text-success flex-shrink-0 mt-0.5"></span>
                        <div>
                            <span class="font-medium text-base-content">Daily Check-ins</span>
                            <p class="text-sm text-base-content/60">Team members answer standup questions: yesterday, today, blockers</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="icon-[tabler--check] size-5 text-success flex-shrink-0 mt-0.5"></span>
                        <div>
                            <span class="font-medium text-base-content">Mood Tracking</span>
                            <p class="text-sm text-base-content/60">Capture team sentiment with emoji-based mood indicators</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="icon-[tabler--check] size-5 text-success flex-shrink-0 mt-0.5"></span>
                        <div>
                            <span class="font-medium text-base-content">Blocker Summary</span>
                            <p class="text-sm text-base-content/60">Quickly identify and address team blockers in one view</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="icon-[tabler--check] size-5 text-success flex-shrink-0 mt-0.5"></span>
                        <div>
                            <span class="font-medium text-base-content">On-Track Gauge</span>
                            <p class="text-sm text-base-content/60">Visual gauge showing team health on workspace overview</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="icon-[tabler--check] size-5 text-success flex-shrink-0 mt-0.5"></span>
                        <div>
                            <span class="font-medium text-base-content">Smart Reminders</span>
                            <p class="text-sm text-base-content/60">Email and in-app reminders to submit daily standups</p>
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

                @if($dailyStandupStatus['enabled'])
                    <!-- Enabled State -->
                    <div class="alert alert-success mb-4">
                        <span class="icon-[tabler--checkbox] size-5"></span>
                        <div>
                            <h3 class="font-bold">Daily Standup is Enabled</h3>
                            <p class="text-sm">Workspaces can now enable daily standups for their teams.</p>
                        </div>
                    </div>

                    <div class="bg-base-200 rounded-lg p-4 mb-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="font-medium text-base-content">Daily Standup Feature</span>
                                <p class="text-sm text-base-content/60">Available for all workspaces</p>
                            </div>
                            <span class="badge badge-success">Active</span>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3">
                        <button type="button" class="btn btn-error btn-outline"
                            data-confirm
                            data-confirm-action="{{ route('marketplace.daily-standup.disable') }}"
                            data-confirm-title="Disable Daily Standup"
                            data-confirm-content="<p class='text-base-content/70'>Are you sure you want to disable the Daily Standup feature?</p><p class='text-base-content/70 mt-2'>Existing standups will be preserved but the feature will be disabled.</p>"
                            data-confirm-button="Disable Feature"
                            data-confirm-icon="tabler--power"
                            data-confirm-class="btn-error"
                            data-confirm-icon-class="text-error"
                            data-confirm-title-icon="tabler--alert-triangle"
                            data-confirm-method="POST">
                            <span class="icon-[tabler--power] size-5"></span>
                            Disable Feature
                        </button>
                    </div>
                @else
                    <!-- Disabled State -->
                    <div class="alert alert-warning mb-4">
                        <span class="icon-[tabler--checkbox] size-5"></span>
                        <div>
                            <h3 class="font-bold">Daily Standup is Disabled</h3>
                            <p class="text-sm">Enable this feature to allow workspaces to use daily standups.</p>
                        </div>
                    </div>

                    <div class="bg-base-200 rounded-lg p-4 mb-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="font-medium text-base-content">Daily Standup Feature</span>
                                <p class="text-sm text-base-content/60">Currently disabled for your organization</p>
                            </div>
                            <span class="badge badge-ghost">Inactive</span>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3">
                        <form action="{{ route('marketplace.daily-standup.enable') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-primary">
                                <span class="icon-[tabler--power] size-5"></span>
                                Enable Feature
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
                            <span class="font-medium text-base-content">Enable at Company Level</span>
                            <p class="text-sm text-base-content/60">Admin enables Daily Standup from the Marketplace for all workspaces.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4">
                        <div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center text-primary-content font-bold flex-shrink-0">
                            2
                        </div>
                        <div>
                            <span class="font-medium text-base-content">Enable per Workspace</span>
                            <p class="text-sm text-base-content/60">Workspace owners/admins enable standups and customize the template questions.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4">
                        <div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center text-primary-content font-bold flex-shrink-0">
                            3
                        </div>
                        <div>
                            <span class="font-medium text-base-content">Submit Daily Check-ins</span>
                            <p class="text-sm text-base-content/60">Team members submit their standup answers and select their mood each day.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4">
                        <div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center text-primary-content font-bold flex-shrink-0">
                            4
                        </div>
                        <div>
                            <span class="font-medium text-base-content">Track Team Health</span>
                            <p class="text-sm text-base-content/60">View team standups, blockers, mood trends, and on-track status in the Tracker tab.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
