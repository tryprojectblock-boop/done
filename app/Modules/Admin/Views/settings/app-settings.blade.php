@extends('admin::layouts.app')

@section('title', 'App Settings')
@section('page-title', 'App Settings')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Header -->
    <div>
        <h1 class="text-2xl font-bold text-base-content">App Settings</h1>
        <p class="text-base-content/60">Configure application-wide settings</p>
    </div>

    @include('admin::partials.alerts')

    <form action="{{ route('backoffice.settings.app.update') }}" method="POST">
        @csrf
        @method('PUT')

        <!-- General Settings -->
        <div class="card bg-base-100 shadow mb-6">
            <div class="card-body">
                <h2 class="card-title text-lg mb-4">
                    <span class="icon-[tabler--settings] size-5"></span>
                    General Settings
                </h2>

                <div class="form-control mb-4">
                    <label class="label" for="app-name">
                        <span class="label-text font-medium">Application Name</span>
                    </label>
                    <input type="text" name="app_name" id="app-name" value="{{ $settings['app_name'] }}" class="input input-bordered" required />
                </div>

                <div class="form-control mb-4">
                    <label class="label" for="support-email">
                        <span class="label-text font-medium">Support Email</span>
                    </label>
                    <input type="email" name="support_email" id="support-email" value="{{ $settings['support_email'] }}" class="input input-bordered" required />
                </div>
            </div>
        </div>

        <!-- Limits Settings -->
        <div class="card bg-base-100 shadow mb-6">
            <div class="card-body">
                <h2 class="card-title text-lg mb-4">
                    <span class="icon-[tabler--adjustments] size-5"></span>
                    Limits & Quotas
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-control">
                        <label class="label" for="default-trial-days">
                            <span class="label-text font-medium">Default Trial Days</span>
                        </label>
                        <input type="number" name="default_trial_days" id="default-trial-days" value="{{ $settings['default_trial_days'] }}" class="input input-bordered" min="0" max="365" />
                    </div>

                    <div class="form-control">
                        <label class="label" for="max-workspaces-per-company">
                            <span class="label-text font-medium">Max Workspaces per Company</span>
                        </label>
                        <input type="number" name="max_workspaces_per_company" id="max-workspaces-per-company" value="{{ $settings['max_workspaces_per_company'] }}" class="input input-bordered" min="1" max="100" />
                    </div>

                    <div class="form-control">
                        <label class="label" for="max-users-per-company">
                            <span class="label-text font-medium">Max Users per Company</span>
                        </label>
                        <input type="number" name="max_users_per_company" id="max-users-per-company" value="{{ $settings['max_users_per_company'] }}" class="input input-bordered" min="1" max="1000" />
                    </div>

                    <div class="form-control">
                        <label class="label" for="max-storage-per-company-gb">
                            <span class="label-text font-medium">Max Storage per Company (GB)</span>
                        </label>
                        <input type="number" name="max_storage_per_company_gb" id="max-storage-per-company-gb" value="{{ $settings['max_storage_per_company_gb'] }}" class="input input-bordered" min="1" max="100" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Feature Toggles -->
        <div class="card bg-base-100 shadow mb-6">
            <div class="card-body">
                <h2 class="card-title text-lg mb-4">
                    <span class="icon-[tabler--toggle-left] size-5"></span>
                    Feature Toggles
                </h2>

                <div class="flex items-center justify-between">
                    <div>
                        <span class="font-medium text-base-content">Registration Enabled</span>
                        <p class="text-sm text-base-content/60">Allow new users to sign up</p>
                    </div>
                    <input type="checkbox" name="registration_enabled" value="1" class="toggle toggle-primary" {{ $settings['registration_enabled'] ? 'checked' : '' }} />
                </div>
            </div>
        </div>

        <!-- Submit -->
        <div class="flex justify-end mb-6">
            <button type="submit" class="btn btn-primary">
                <span class="icon-[tabler--device-floppy] size-5"></span>
                Save Settings
            </button>
        </div>
    </form>

    <!-- System Tools -->
    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <h2 class="card-title text-lg mb-4">
                <span class="icon-[tabler--tool] size-5"></span>
                System Tools
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Scheduled Tasks -->
                <a href="{{ route('backoffice.scheduled-tasks.index') }}" class="card bg-base-200 hover:bg-base-300 transition-colors">
                    <div class="card-body p-4">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-lg bg-violet-500/10 flex items-center justify-center">
                                <span class="icon-[tabler--clock-play] size-6 text-violet-500"></span>
                            </div>
                            <div class="flex-1">
                                <h3 class="font-semibold text-base-content">Scheduled Tasks</h3>
                                <p class="text-sm text-base-content/60">Manage cron jobs and automated tasks</p>
                            </div>
                            <span class="icon-[tabler--chevron-right] size-5 text-base-content/40"></span>
                        </div>
                    </div>
                </a>

                <!-- App Maintenance -->
                <a href="{{ route('backoffice.app.index') }}" class="card bg-base-200 hover:bg-base-300 transition-colors">
                    <div class="card-body p-4">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-lg bg-warning/10 flex items-center justify-center">
                                <span class="icon-[tabler--tool] size-6 text-warning"></span>
                            </div>
                            <div class="flex-1">
                                <h3 class="font-semibold text-base-content">App Maintenance</h3>
                                <p class="text-sm text-base-content/60">Maintenance mode and factory reset</p>
                            </div>
                            <span class="icon-[tabler--chevron-right] size-5 text-base-content/40"></span>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
