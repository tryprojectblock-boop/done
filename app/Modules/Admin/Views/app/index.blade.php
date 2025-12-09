@extends('admin::layouts.app')

@section('title', 'App')
@section('page-title', 'App')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div>
        <h1 class="text-2xl font-bold text-base-content">App Features</h1>
        <p class="text-base-content/60">Maintenance mode and system reset options</p>
    </div>

    @include('admin::partials.alerts')

    <!-- Maintenance Mode Section -->
    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <h2 class="card-title text-lg mb-4">
                <span class="icon-[tabler--tool] size-5 text-warning"></span>
                Maintenance Mode
            </h2>

            @if($settings['maintenance_mode'] ?? false)
                <div class="alert alert-warning mb-4">
                    <span class="icon-[tabler--alert-triangle] size-5"></span>
                    <div>
                        <div class="font-medium">Maintenance mode is currently ENABLED</div>
                        @if($settings['maintenance_until'] ?? null)
                            <div class="text-sm">Scheduled until: {{ \Carbon\Carbon::parse($settings['maintenance_until'])->format('M d, Y H:i') }}</div>
                        @endif
                        @if($settings['maintenance_message'] ?? null)
                            <div class="text-sm mt-1">Message: {{ $settings['maintenance_message'] }}</div>
                        @endif
                    </div>
                </div>

                <form action="{{ route('backoffice.app.maintenance.disable') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-success">
                        <span class="icon-[tabler--player-play] size-5"></span>
                        Disable Maintenance Mode
                    </button>
                </form>
            @else
                <p class="text-base-content/60 mb-4">
                    Enable maintenance mode to show a maintenance page to all front-end users. Admin panel remains accessible.
                </p>

                <form action="{{ route('backoffice.app.maintenance.enable') }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div class="form-control">
                            <label class="label" for="maintenance_until">
                                <span class="label-text font-medium">End Date & Time (optional)</span>
                            </label>
                            <input type="datetime-local" name="maintenance_until" id="maintenance_until" class="input input-bordered" aria-describedby="maintenance_until_hint" />
                            <div class="label" id="maintenance_until_hint">
                                <span class="label-text-alt text-base-content/60">Leave empty for indefinite maintenance</span>
                            </div>
                        </div>

                        <div class="form-control">
                            <label class="label" for="maintenance_message">
                                <span class="label-text font-medium">Custom Message (optional)</span>
                            </label>
                            <input type="text" name="maintenance_message" id="maintenance_message" class="input input-bordered" placeholder="We are currently performing maintenance..." />
                        </div>
                    </div>

                    <button type="submit" class="btn btn-warning">
                        <span class="icon-[tabler--tool] size-5"></span>
                        Enable Maintenance Mode
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Factory Reset Section -->
    <div class="card bg-base-100 shadow border-2 border-error/20">
        <div class="card-body">
            <h2 class="card-title text-lg mb-4 text-error">
                <span class="icon-[tabler--refresh-alert] size-5"></span>
                Factory Reset
            </h2>

            <div class="alert alert-error mb-4">
                <span class="icon-[tabler--alert-octagon] size-5"></span>
                <div>
                    <div class="font-bold">Danger Zone</div>
                    <div class="text-sm">This action will permanently delete ALL data and cannot be undone.</div>
                </div>
            </div>

            <div class="stats stats-vertical lg:stats-horizontal shadow mb-4 w-full">
                <div class="stat">
                    <div class="stat-figure text-error">
                        <span class="icon-[tabler--building] size-8"></span>
                    </div>
                    <div class="stat-title">Clients</div>
                    <div class="stat-value text-error">{{ $counts['companies'] }}</div>
                    <div class="stat-desc">Will be deleted</div>
                </div>

                <div class="stat">
                    <div class="stat-figure text-error">
                        <span class="icon-[tabler--users] size-8"></span>
                    </div>
                    <div class="stat-title">Users</div>
                    <div class="stat-value text-error">{{ $counts['users'] }}</div>
                    <div class="stat-desc">Will be deleted</div>
                </div>

                <div class="stat">
                    <div class="stat-figure text-error">
                        <span class="icon-[tabler--briefcase] size-8"></span>
                    </div>
                    <div class="stat-title">Workspaces</div>
                    <div class="stat-value text-error">{{ $counts['workspaces'] }}</div>
                    <div class="stat-desc">Will be deleted</div>
                </div>
            </div>

            <p class="text-base-content/70 mb-4">
                This will reset the application to a clean state by deleting:
            </p>
            <ul class="list-disc list-inside text-base-content/70 mb-4 space-y-1">
                <li>All clients (companies)</li>
                <li>All users and their data</li>
                <li>All workspaces, tasks, and related content</li>
                <li>All uploaded files and media</li>
            </ul>

            <button type="button" class="btn btn-error btn-outline" onclick="factoryResetModal.showModal()">
                <span class="icon-[tabler--refresh-alert] size-5"></span>
                Reset to Factory Mode
            </button>
        </div>
    </div>
</div>

<!-- Factory Reset Confirmation Modal -->
<dialog id="factoryResetModal" class="modal modal-bottom sm:modal-middle">
    <div class="modal-box">
        <h3 class="font-bold text-lg text-error">
            <span class="icon-[tabler--alert-octagon] size-5 inline-block mr-2"></span>
            Confirm Factory Reset
        </h3>
        <div class="py-4">
            <p class="mb-4">You are about to permanently delete:</p>
            <ul class="list-disc list-inside mb-4 space-y-1 text-error">
                <li><strong>{{ $counts['companies'] }}</strong> clients</li>
                <li><strong>{{ $counts['users'] }}</strong> users</li>
                <li><strong>{{ $counts['workspaces'] }}</strong> workspaces</li>
            </ul>
            <p class="font-bold mb-4">This action CANNOT be undone!</p>
            <p class="mb-2">Type <code class="bg-base-200 px-2 py-1 rounded font-mono">RESET</code> to confirm:</p>
            <form action="{{ route('backoffice.app.factory-reset') }}" method="POST" id="factoryResetForm">
                @csrf
                <input type="text"
                    name="confirmation"
                    id="factoryResetConfirmation"
                    class="input input-bordered input-error w-full"
                    placeholder="Type RESET to confirm"
                    autocomplete="off" />
            </form>
        </div>
        <div class="modal-action">
            <form method="dialog">
                <button class="btn btn-ghost">Cancel</button>
            </form>
            <button type="submit" form="factoryResetForm" id="factoryResetSubmit" class="btn btn-error" disabled>
                <span class="icon-[tabler--refresh-alert] size-5"></span>
                Confirm Reset
            </button>
        </div>
    </div>
    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</dialog>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const confirmInput = document.getElementById('factoryResetConfirmation');
    const submitBtn = document.getElementById('factoryResetSubmit');

    if (confirmInput && submitBtn) {
        confirmInput.addEventListener('input', function() {
            submitBtn.disabled = this.value !== 'RESET';
        });
    }
});
</script>
@endpush
@endsection
