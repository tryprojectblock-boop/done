@extends('layouts.app')

@section('content')
<div class="min-h-screen p-4 md:p-6">
    <div class="max-w-3xl mx-auto">
        <!-- Page Header -->
        <div class="mb-6">
            <div class="flex items-center gap-2 text-sm text-base-content/60 mb-2">
                <a href="{{ route('settings.index') }}" class="hover:text-primary">Settings</a>
                <span class="icon-[tabler--chevron-right] size-4"></span>
                <span>Integrations</span>
            </div>
            <h1 class="text-2xl font-bold text-base-content">Integrations</h1>
            <p class="text-base-content/60">Configure third-party integrations for your organization</p>
        </div>

        <div class="mb-6">
            @include('partials.alerts')
        </div>

        <!-- Google Calendar Integration Card -->
        <div class="card bg-base-100 shadow mb-6">
            <div class="card-body">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="card-title text-lg">
                        <span class="icon-[tabler--brand-google] size-5 text-error"></span>
                        Google Calendar Sync
                    </h2>
                    @if(!empty($integrationSettings['google_client_id']) && !empty($integrationSettings['google_client_secret']))
                        <span class="badge badge-{{ $integrationSettings['gmail_sync_enabled'] ? 'success' : 'warning' }}">
                            {{ $integrationSettings['gmail_sync_enabled'] ? 'Enabled' : 'Disabled' }}
                        </span>
                    @else
                        <span class="badge badge-ghost">Not Configured</span>
                    @endif
                </div>

                <p class="text-sm text-base-content/60 mb-4">
                    Enable two-way sync between Project Block and Google Calendar. When enabled, team members can connect their Google accounts to sync tasks with their personal calendars.
                </p>

                <!-- Features List -->
                <div class="space-y-2 mb-6">
                    <div class="flex items-center gap-2 text-sm">
                        <span class="icon-[tabler--check] size-4 text-success"></span>
                        <span>Tasks with due dates sync to Google Calendar</span>
                    </div>
                    <div class="flex items-center gap-2 text-sm">
                        <span class="icon-[tabler--check] size-4 text-success"></span>
                        <span>Google Calendar events sync back as tasks</span>
                    </div>
                    <div class="flex items-center gap-2 text-sm">
                        <span class="icon-[tabler--check] size-4 text-success"></span>
                        <span>Each team member connects their own Google account</span>
                    </div>
                </div>

                <!-- Google API Credentials Form -->
                <form action="{{ route('settings.integrations.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="bg-base-200 rounded-lg p-4 mb-4">
                        <h3 class="font-semibold text-base-content mb-3">
                            <span class="icon-[tabler--key] size-4 inline-block mr-1"></span>
                            Google API Credentials
                        </h3>

                        <div class="alert alert-info mb-4">
                            <span class="icon-[tabler--info-circle] size-5"></span>
                            <div>
                                <p class="text-sm">To get Google API credentials:</p>
                                <ol class="text-sm list-decimal list-inside mt-1 space-y-1">
                                    <li>Go to the <a href="https://console.cloud.google.com/apis/credentials" target="_blank" class="link link-primary">Google Cloud Console</a></li>
                                    <li>Create a new project or select an existing one</li>
                                    <li>Enable the Google Calendar API</li>
                                    <li>Create OAuth 2.0 credentials (Web application type)</li>
                                    <li>Add the redirect URI shown below to your authorized redirect URIs</li>
                                </ol>
                            </div>
                        </div>

                        <div class="form-control mb-4">
                            <label class="label">
                                <span class="label-text font-medium">Client ID</span>
                            </label>
                            <input
                                type="text"
                                name="google_client_id"
                                value="{{ $integrationSettings['google_client_id'] }}"
                                class="input input-bordered font-mono text-sm"
                                placeholder="xxxxxxxxxxxx-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx.apps.googleusercontent.com"
                            />
                            @error('google_client_id')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                        </div>

                        <div class="form-control mb-4">
                            <label class="label">
                                <span class="label-text font-medium">Client Secret</span>
                            </label>
                            <input
                                type="password"
                                name="google_client_secret"
                                value="{{ $integrationSettings['google_client_secret'] }}"
                                class="input input-bordered font-mono text-sm"
                                placeholder="GOCSPX-xxxxxxxxxxxxxxxxxxxxxxxxxx"
                            />
                            <label class="label">
                                <span class="label-text-alt text-base-content/60">Your client secret is stored securely</span>
                            </label>
                            @error('google_client_secret')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                        </div>

                        <div class="form-control mb-4">
                            <label class="label">
                                <span class="label-text font-medium">Redirect URI</span>
                            </label>
                            <input
                                type="url"
                                name="google_redirect_uri"
                                value="{{ $integrationSettings['google_redirect_uri'] }}"
                                class="input input-bordered font-mono text-sm"
                                placeholder="{{ url('/auth/google/callback') }}"
                            />
                            <label class="label">
                                <span class="label-text-alt text-base-content/60">Add this URI to your Google Cloud Console's authorized redirect URIs</span>
                            </label>
                            @error('google_redirect_uri')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <button type="submit" class="btn btn-primary">
                            <span class="icon-[tabler--device-floppy] size-5"></span>
                            Save Credentials
                        </button>

                        @if(!empty($integrationSettings['google_client_id']) && !empty($integrationSettings['google_client_secret']))
                            </form>
                            <form action="{{ route('settings.integrations.toggle-gmail-sync') }}" method="POST" class="inline">
                                @csrf
                                @if($integrationSettings['gmail_sync_enabled'])
                                    <button type="submit" class="btn btn-warning btn-outline" onclick="return confirm('Are you sure you want to disable Gmail Calendar Sync? Team members will no longer be able to sync with Google Calendar.')">
                                        <span class="icon-[tabler--unlink] size-5"></span>
                                        Disable Sync
                                    </button>
                                @else
                                    <button type="submit" class="btn btn-success">
                                        <span class="icon-[tabler--link] size-5"></span>
                                        Enable Sync
                                    </button>
                                @endif
                            </form>
                        @else
                        </form>
                        @endif
                    </div>
                </div>
            </div>

        <!-- How It Works Card -->
        <div class="card bg-base-100 shadow mb-6">
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
                            <span class="font-medium text-base-content">Configure Google API</span>
                            <p class="text-sm text-base-content/60">As an admin, set up your Google API credentials above to enable the integration.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4">
                        <div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center text-primary-content font-bold flex-shrink-0">
                            2
                        </div>
                        <div>
                            <span class="font-medium text-base-content">Enable Gmail Sync</span>
                            <p class="text-sm text-base-content/60">Click "Enable Sync" to allow team members to connect their Google accounts.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4">
                        <div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center text-primary-content font-bold flex-shrink-0">
                            3
                        </div>
                        <div>
                            <span class="font-medium text-base-content">Team Members Connect</span>
                            <p class="text-sm text-base-content/60">Each team member can connect their Google account from their profile settings.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4">
                        <div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center text-primary-content font-bold flex-shrink-0">
                            4
                        </div>
                        <div>
                            <span class="font-medium text-base-content">Automatic Sync</span>
                            <p class="text-sm text-base-content/60">Tasks with due dates automatically sync to Google Calendar, and calendar events sync back to Project Block.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Coming Soon -->
        <div class="card bg-base-100 shadow opacity-60">
            <div class="card-body">
                <h2 class="card-title text-lg mb-2">
                    <span class="icon-[tabler--plug] size-5"></span>
                    More Integrations Coming Soon
                </h2>
                <p class="text-sm text-base-content/60">
                    We're working on integrations with Slack, Microsoft Outlook, Zapier, and more. Stay tuned!
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
