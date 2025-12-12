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
                <div class="w-12 h-12 rounded-lg flex items-center justify-center" style="background-color: rgba(245, 158, 11, 0.1);">
                    <span class="icon-[tabler--plane-departure] size-6" style="color: #f59e0b;"></span>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-base-content">Out of Office</h1>
                    <span class="badge badge-{{ $outOfOfficeStatus['status_color'] }}">
                        {{ $outOfOfficeStatus['status_label'] }}
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
                    About Out of Office
                </h2>
                <p class="text-base-content/70 mb-4">
                    The Out of Office feature allows team members to set their availability status when they're away
                    from work. When enabled, the system can automatically respond to task comments on their behalf,
                    keeping collaborators informed about their absence.
                </p>

                <div class="space-y-3">
                    <div class="flex items-start gap-3">
                        <span class="icon-[tabler--check] size-5 text-success flex-shrink-0 mt-0.5"></span>
                        <div>
                            <span class="font-medium text-base-content">Status Display</span>
                            <p class="text-sm text-base-content/60">Show "Out of Office" badge on user avatars throughout the app</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="icon-[tabler--check] size-5 text-success flex-shrink-0 mt-0.5"></span>
                        <div>
                            <span class="font-medium text-base-content">Date Range</span>
                            <p class="text-sm text-base-content/60">Set specific start and end dates for your absence</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="icon-[tabler--check] size-5 text-success flex-shrink-0 mt-0.5"></span>
                        <div>
                            <span class="font-medium text-base-content">Custom Message</span>
                            <p class="text-sm text-base-content/60">Display a personalized message about your absence</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="icon-[tabler--check] size-5 text-success flex-shrink-0 mt-0.5"></span>
                        <div>
                            <span class="font-medium text-base-content">Auto-Response</span>
                            <p class="text-sm text-base-content/60">Automatically post a response when someone comments on your tasks</p>
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

                @if($outOfOfficeStatus['enabled'])
                    <!-- Enabled State -->
                    <div class="alert alert-success mb-4">
                        <span class="icon-[tabler--plane-departure] size-5"></span>
                        <div>
                            <h3 class="font-bold">Out of Office is Enabled</h3>
                            <p class="text-sm">Team members can set their out of office status in their profile settings.</p>
                        </div>
                    </div>

                    <div class="bg-base-200 rounded-lg p-4 mb-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="font-medium text-base-content">Out of Office Feature</span>
                                <p class="text-sm text-base-content/60">Available for all team members</p>
                            </div>
                            <span class="badge badge-success">Active</span>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3">
                        <button type="button" class="btn btn-error btn-outline"
                            data-confirm
                            data-confirm-action="{{ route('marketplace.out-of-office.disable') }}"
                            data-confirm-title="Disable Out of Office"
                            data-confirm-content="<p class='text-base-content/70'>Are you sure you want to disable the Out of Office feature?</p><p class='text-base-content/70 mt-2'>Existing out of office settings will be deactivated for all users.</p>"
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
                        <span class="icon-[tabler--plane-departure] size-5"></span>
                        <div>
                            <h3 class="font-bold">Out of Office is Disabled</h3>
                            <p class="text-sm">Enable this feature to allow team members to set their out of office status.</p>
                        </div>
                    </div>

                    <div class="bg-base-200 rounded-lg p-4 mb-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="font-medium text-base-content">Out of Office Feature</span>
                                <p class="text-sm text-base-content/60">Currently disabled for your organization</p>
                            </div>
                            <span class="badge badge-ghost">Inactive</span>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3">
                        <form action="{{ route('marketplace.out-of-office.enable') }}" method="POST">
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
                            <span class="font-medium text-base-content">Enable the Feature</span>
                            <p class="text-sm text-base-content/60">Admin enables the Out of Office feature from the Marketplace.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4">
                        <div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center text-primary-content font-bold flex-shrink-0">
                            2
                        </div>
                        <div>
                            <span class="font-medium text-base-content">Set Your Status</span>
                            <p class="text-sm text-base-content/60">Team members go to their Profile page and set start date, end date, and messages.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4">
                        <div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center text-primary-content font-bold flex-shrink-0">
                            3
                        </div>
                        <div>
                            <span class="font-medium text-base-content">Visual Indicator</span>
                            <p class="text-sm text-base-content/60">An "Out of Office" badge appears on the user's avatar throughout the application.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4">
                        <div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center text-primary-content font-bold flex-shrink-0">
                            4
                        </div>
                        <div>
                            <span class="font-medium text-base-content">Auto-Response</span>
                            <p class="text-sm text-base-content/60">When someone comments on the user's tasks, the system automatically posts the configured auto-response message.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
