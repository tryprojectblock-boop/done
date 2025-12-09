@extends('admin::layouts.app')

@section('title', 'Integrations')
@section('page-title', 'Integrations')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Header -->
    <div>
        <h1 class="text-2xl font-bold text-base-content">Integrations</h1>
        <p class="text-base-content/60">Configure third-party API integrations for your platform</p>
    </div>

    @include('admin::partials.alerts')

    <form action="{{ route('backoffice.settings.integrations.update') }}" method="POST">
        @csrf
        @method('PUT')

        <!-- Google API Settings -->
        <div class="card bg-base-100 shadow mb-6">
            <div class="card-body">
                <h2 class="card-title text-lg mb-2">
                    <span class="icon-[tabler--brand-google] size-5 text-error"></span>
                    Google API
                </h2>
                <p class="text-sm text-base-content/60 mb-4">
                    Configure Google API credentials to enable Gmail Calendar Sync for all organizations on your platform.
                </p>

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
                    <label class="label" for="google_client_id">
                        <span class="label-text font-medium">Client ID</span>
                    </label>
                    <input type="text" name="google_client_id" id="google_client_id" value="{{ $settings['google_client_id'] }}" class="input input-bordered font-mono text-sm" placeholder="xxxxxxxxxxxx-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx.apps.googleusercontent.com" />
                    @error('google_client_id')
                        <div class="label">
                            <span class="label-text-alt text-error">{{ $message }}</span>
                        </div>
                    @enderror
                </div>

                <div class="form-control mb-4">
                    <label class="label" for="google_client_secret">
                        <span class="label-text font-medium">Client Secret</span>
                    </label>
                    <input type="password" name="google_client_secret" id="google_client_secret" value="{{ $settings['google_client_secret'] }}" class="input input-bordered font-mono text-sm" placeholder="GOCSPX-xxxxxxxxxxxxxxxxxxxxxxxxxx" aria-describedby="google_client_secret_hint" />
                    <div class="label" id="google_client_secret_hint">
                        <span class="label-text-alt text-base-content/60">Your client secret is stored securely and never exposed</span>
                    </div>
                    @error('google_client_secret')
                        <div class="label">
                            <span class="label-text-alt text-error">{{ $message }}</span>
                        </div>
                    @enderror
                </div>

                <div class="form-control mb-4">
                    <label class="label" for="google_redirect_uri">
                        <span class="label-text font-medium">Redirect URI</span>
                    </label>
                    <input type="url" name="google_redirect_uri" id="google_redirect_uri" value="{{ $settings['google_redirect_uri'] }}" class="input input-bordered font-mono text-sm" placeholder="{{ url('/auth/google/callback') }}" aria-describedby="google_redirect_uri_hint" />
                    <div class="label" id="google_redirect_uri_hint">
                        <span class="label-text-alt text-base-content/60">Add this URI to your Google Cloud Console's authorized redirect URIs</span>
                    </div>
                    @error('google_redirect_uri')
                        <div class="label">
                            <span class="label-text-alt text-error">{{ $message }}</span>
                        </div>
                    @enderror
                </div>

                <!-- Connection Status -->
                <div class="bg-base-200 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            @if(!empty($settings['google_client_id']) && !empty($settings['google_client_secret']))
                                <span class="badge badge-success gap-1">
                                    <span class="icon-[tabler--check] size-3"></span>
                                    Configured
                                </span>
                                <span class="text-sm text-base-content/70">Google API credentials are set up</span>
                            @else
                                <span class="badge badge-warning gap-1">
                                    <span class="icon-[tabler--alert-triangle] size-3"></span>
                                    Not Configured
                                </span>
                                <span class="text-sm text-base-content/70">Enter your Google API credentials above</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Coming Soon: Other Integrations -->
        <div class="card bg-base-100 shadow mb-6 opacity-60">
            <div class="card-body">
                <h2 class="card-title text-lg mb-2">
                    <span class="icon-[tabler--brand-slack] size-5 text-purple-500"></span>
                    Slack Integration
                    <span class="badge badge-ghost badge-sm">Coming Soon</span>
                </h2>
                <p class="text-sm text-base-content/60">
                    Enable Slack notifications and integrations for organizations on your platform.
                </p>
            </div>
        </div>

        <div class="card bg-base-100 shadow mb-6 opacity-60">
            <div class="card-body">
                <h2 class="card-title text-lg mb-2">
                    <span class="icon-[tabler--brand-windows] size-5 text-info"></span>
                    Microsoft / Outlook
                    <span class="badge badge-ghost badge-sm">Coming Soon</span>
                </h2>
                <p class="text-sm text-base-content/60">
                    Enable Microsoft 365 and Outlook Calendar integration for organizations on your platform.
                </p>
            </div>
        </div>

        <!-- Submit -->
        <div class="flex justify-end">
            <button type="submit" class="btn btn-primary">
                <span class="icon-[tabler--device-floppy] size-5"></span>
                Save Settings
            </button>
        </div>
    </form>
</div>
@endsection
