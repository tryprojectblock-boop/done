@extends('layouts.app')

@section('content')
<div class="min-h-screen p-4 md:p-6">
    <div class="max-w-2xl mx-auto">
        <!-- Breadcrumb -->
        <div class="text-sm breadcrumbs mb-4">
            <ul>
                <li><a href="{{ route('marketplace.index') }}">Marketplace</a></li>
                <li>Google Drive</li>
            </ul>
        </div>

        <!-- Page Header -->
        <div class="mb-6">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-12 h-12 rounded-lg flex items-center justify-center" style="background-color: #4285f420;">
                    <span class="icon-[tabler--brand-google-drive] size-6" style="color: #4285f4;"></span>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-base-content">Google Drive</h1>
                    <span class="badge badge-{{ $googleDriveStatus['status_color'] }}">
                        {{ $googleDriveStatus['status_label'] }}
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
                    About Google Drive Integration
                </h2>
                <p class="text-base-content/70 mb-4">
                    Google Drive integration allows you to connect your Google Drive account to access and manage files directly from your workspace.
                    When enabled, you can browse, upload, and organize files from Google Drive alongside your Block Drive files.
                </p>

                <div class="space-y-3">
                    <div class="flex items-start gap-3">
                        <span class="icon-[tabler--check] size-5 text-success flex-shrink-0 mt-0.5"></span>
                        <div>
                            <span class="font-medium text-base-content">Access Google Drive Files</span>
                            <p class="text-sm text-base-content/60">Browse and access files stored in your Google Drive</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="icon-[tabler--check] size-5 text-success flex-shrink-0 mt-0.5"></span>
                        <div>
                            <span class="font-medium text-base-content">Upload Files</span>
                            <p class="text-sm text-base-content/60">Upload files directly to your Google Drive from the workspace</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="icon-[tabler--check] size-5 text-success flex-shrink-0 mt-0.5"></span>
                        <div>
                            <span class="font-medium text-base-content">Per-User Connection</span>
                            <p class="text-sm text-base-content/60">Each team member connects their own Google account for personal Drive access</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="icon-[tabler--check] size-5 text-success flex-shrink-0 mt-0.5"></span>
                        <div>
                            <span class="font-medium text-base-content">Unified File Management</span>
                            <p class="text-sm text-base-content/60">Manage Block Drive and Google Drive files from a single interface</p>
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

                @if(!$googleDriveStatus['installed'])
                    <!-- Not Configured State -->
                    <div class="alert alert-warning mb-4">
                        <span class="icon-[tabler--alert-triangle] size-5"></span>
                        <div>
                            <h3 class="font-bold">Google API Not Configured</h3>
                            <p class="text-sm">Please configure Google API credentials in your integration settings to enable this feature.</p>
                        </div>
                    </div>

                    <div class="bg-base-200 rounded-lg p-4 mb-4">
                        <p class="text-sm text-base-content/70 mb-2">To set up Google Drive:</p>
                        <ol class="text-sm text-base-content/60 space-y-1 list-decimal list-inside">
                            <li>Go to Settings > Integrations</li>
                            <li>Configure your Google API credentials</li>
                            <li>Return here to enable Google Drive</li>
                        </ol>
                    </div>

                    <a href="{{ route('settings.integrations') }}" class="btn btn-primary">
                        <span class="icon-[tabler--settings] size-5"></span>
                        Go to Integration Settings
                    </a>
                @elseif($googleDriveStatus['enabled'])
                    <!-- Enabled State -->
                    <div class="alert alert-success mb-4">
                        <span class="icon-[tabler--cloud-check] size-5"></span>
                        <div>
                            <h3 class="font-bold">Google Drive is Enabled</h3>
                            <p class="text-sm">Team members can now connect their Google accounts to access Google Drive.</p>
                        </div>
                    </div>

                    <div class="bg-base-200 rounded-lg p-4 mb-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="font-medium text-base-content">Google Drive Integration</span>
                                <p class="text-sm text-base-content/60">Organization members can connect their Google accounts</p>
                            </div>
                            <span class="badge badge-success">Active</span>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3">
                        <a href="{{ route('drive.index') }}" class="btn btn-primary">
                            <span class="icon-[tabler--folder] size-5"></span>
                            Go to Drive
                        </a>
                        <button type="button" class="btn btn-error btn-outline"
                            data-confirm
                            data-confirm-action="{{ route('marketplace.google-drive.disable') }}"
                            data-confirm-title="Disable Google Drive"
                            data-confirm-content="<p class='text-base-content/70'>Are you sure you want to disable Google Drive integration?</p><p class='text-base-content/70 mt-2'>Team members will no longer be able to access Google Drive files.</p>"
                            data-confirm-button="Disable"
                            data-confirm-icon="tabler--unlink"
                            data-confirm-class="btn-error"
                            data-confirm-icon-class="text-error"
                            data-confirm-title-icon="tabler--alert-triangle"
                            data-confirm-method="POST">
                            <span class="icon-[tabler--unlink] size-5"></span>
                            Disable Google Drive
                        </button>
                    </div>
                @else
                    <!-- Disabled State -->
                    <div class="alert alert-info mb-4">
                        <span class="icon-[tabler--cloud] size-5"></span>
                        <div>
                            <h3 class="font-bold">Google Drive is Disabled</h3>
                            <p class="text-sm">Enable this feature to allow team members to connect their Google Drive accounts.</p>
                        </div>
                    </div>

                    <div class="bg-base-200 rounded-lg p-4 mb-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="font-medium text-base-content">Google Drive Integration</span>
                                <p class="text-sm text-base-content/60">Organization members cannot connect Google accounts</p>
                            </div>
                            <span class="badge badge-ghost">Inactive</span>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3">
                        <form action="{{ route('marketplace.google-drive.enable') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-primary">
                                <span class="icon-[tabler--link] size-5"></span>
                                Enable Google Drive
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
                            <span class="font-medium text-base-content">Enable Google Drive</span>
                            <p class="text-sm text-base-content/60">As an admin, enable Google Drive integration for your organization using the button above.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4">
                        <div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center text-primary-content font-bold flex-shrink-0">
                            2
                        </div>
                        <div>
                            <span class="font-medium text-base-content">Team Members Connect</span>
                            <p class="text-sm text-base-content/60">Each team member can connect their Google account from the Drive page.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4">
                        <div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center text-primary-content font-bold flex-shrink-0">
                            3
                        </div>
                        <div>
                            <span class="font-medium text-base-content">Access Your Files</span>
                            <p class="text-sm text-base-content/60">Browse and manage both Block Drive and Google Drive files from a unified interface.</p>
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
