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
                <div class="w-12 h-12 rounded-lg flex items-center justify-center" style="background-color: rgba(168, 85, 247, 0.1);">
                    <span class="icon-[tabler--flag] size-6" style="color: #a855f7;"></span>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-base-content">Milestones</h1>
                    <span class="badge badge-{{ $milestoneStatus['status_color'] }}">
                        {{ $milestoneStatus['status_label'] }}
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
                    About Milestones
                </h2>
                <p class="text-base-content/70 mb-4">
                    Milestones help you track major project deliverables and deadlines. Break down your projects into
                    achievable goals, link tasks to milestones, and monitor progress automatically as your team
                    completes work.
                </p>

                <div class="space-y-3">
                    <div class="flex items-start gap-3">
                        <span class="icon-[tabler--check] size-5 text-success flex-shrink-0 mt-0.5"></span>
                        <div>
                            <span class="font-medium text-base-content">Task Linking</span>
                            <p class="text-sm text-base-content/60">Connect tasks to milestones and see progress update automatically</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="icon-[tabler--check] size-5 text-success flex-shrink-0 mt-0.5"></span>
                        <div>
                            <span class="font-medium text-base-content">Auto Progress Tracking</span>
                            <p class="text-sm text-base-content/60">Progress is calculated automatically based on completed tasks</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="icon-[tabler--check] size-5 text-success flex-shrink-0 mt-0.5"></span>
                        <div>
                            <span class="font-medium text-base-content">Status Management</span>
                            <p class="text-sm text-base-content/60">Track milestones through Not Started, In Progress, Blocked, and Completed states</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="icon-[tabler--check] size-5 text-success flex-shrink-0 mt-0.5"></span>
                        <div>
                            <span class="font-medium text-base-content">Due Date Notifications</span>
                            <p class="text-sm text-base-content/60">Get notified when milestones are approaching their due dates</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="icon-[tabler--check] size-5 text-success flex-shrink-0 mt-0.5"></span>
                        <div>
                            <span class="font-medium text-base-content">Comments & Collaboration</span>
                            <p class="text-sm text-base-content/60">Discuss milestones with your team through comments</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Video Tutorial Card -->
        <div class="card bg-base-100 shadow mb-6">
            <div class="card-body">
                <h2 class="card-title text-lg mb-4">
                    <span class="icon-[tabler--video] size-5"></span>
                    Video Tutorial
                </h2>
                <div class="aspect-video bg-base-200 rounded-lg flex items-center justify-center">
                    <div class="text-center">
                        <span class="icon-[tabler--player-play] size-12 text-base-content/30"></span>
                        <p class="text-base-content/50 mt-2">Video tutorial coming soon</p>
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

                @if($milestoneStatus['enabled'])
                    <!-- Enabled State -->
                    <div class="alert alert-success mb-4">
                        <span class="icon-[tabler--flag] size-5"></span>
                        <div>
                            <h3 class="font-bold">Milestones Module is Enabled</h3>
                            <p class="text-sm">Team members can create and manage milestones in workspaces.</p>
                        </div>
                    </div>

                    <div class="bg-base-200 rounded-lg p-4 mb-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="font-medium text-base-content">Milestones Module</span>
                                <p class="text-sm text-base-content/60">Available in all workspaces</p>
                            </div>
                            <span class="badge badge-success">Active</span>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3">
                        <button type="button" class="btn btn-error btn-outline"
                            data-confirm
                            data-confirm-action="{{ route('marketplace.milestones.disable') }}"
                            data-confirm-title="Disable Milestones Module"
                            data-confirm-content="<p class='text-base-content/70'>Are you sure you want to disable the Milestones module?</p><p class='text-base-content/70 mt-2'>Existing milestones will be preserved but users won't be able to create new ones.</p>"
                            data-confirm-button="Disable Module"
                            data-confirm-icon="tabler--power"
                            data-confirm-class="btn-error"
                            data-confirm-icon-class="text-error"
                            data-confirm-title-icon="tabler--alert-triangle"
                            data-confirm-method="POST">
                            <span class="icon-[tabler--power] size-5"></span>
                            Disable Module
                        </button>
                    </div>
                @else
                    <!-- Disabled State -->
                    <div class="alert alert-warning mb-4">
                        <span class="icon-[tabler--flag] size-5"></span>
                        <div>
                            <h3 class="font-bold">Milestones Module is Disabled</h3>
                            <p class="text-sm">Enable this module to allow team members to create and manage milestones.</p>
                        </div>
                    </div>

                    <div class="bg-base-200 rounded-lg p-4 mb-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="font-medium text-base-content">Milestones Module</span>
                                <p class="text-sm text-base-content/60">Currently disabled for your organization</p>
                            </div>
                            <span class="badge badge-ghost">Inactive</span>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3">
                        <form action="{{ route('marketplace.milestones.enable') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-primary">
                                <span class="icon-[tabler--power] size-5"></span>
                                Enable Module
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
                            <span class="font-medium text-base-content">Create a Milestone</span>
                            <p class="text-sm text-base-content/60">Navigate to any workspace and create a milestone with a title, description, and due date.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4">
                        <div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center text-primary-content font-bold flex-shrink-0">
                            2
                        </div>
                        <div>
                            <span class="font-medium text-base-content">Link Tasks</span>
                            <p class="text-sm text-base-content/60">When creating or editing tasks, select a milestone from the dropdown to link them together.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4">
                        <div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center text-primary-content font-bold flex-shrink-0">
                            3
                        </div>
                        <div>
                            <span class="font-medium text-base-content">Track Progress</span>
                            <p class="text-sm text-base-content/60">As tasks are completed, the milestone progress updates automatically. Get prompted to complete the milestone when all tasks are done.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4">
                        <div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center text-primary-content font-bold flex-shrink-0">
                            4
                        </div>
                        <div>
                            <span class="font-medium text-base-content">Stay Notified</span>
                            <p class="text-sm text-base-content/60">Receive notifications when you're assigned to milestones, when due dates approach, and when milestones are completed.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
