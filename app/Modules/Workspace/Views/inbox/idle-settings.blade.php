@extends('layouts.app')

@section('content')
<div class="p-4 md:p-6">
    <div class="max-w-2xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center gap-2 text-sm text-base-content/60 mb-2">
                <a href="{{ route('dashboard') }}" class="hover:text-primary">Dashboard</a>
                <span class="icon-[tabler--chevron-right] size-4"></span>
                <a href="{{ route('workspace.show', $workspace) }}" class="hover:text-primary">{{ $workspace->name }}</a>
                <span class="icon-[tabler--chevron-right] size-4"></span>
                <span>Idle Ticket Settings</span>
            </div>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <a href="{{ route('workspace.show', $workspace) }}" class="btn btn-ghost btn-sm btn-square">
                        <span class="icon-[tabler--arrow-left] size-5"></span>
                    </a>
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-lg bg-neutral/10 flex items-center justify-center">
                            <span class="icon-[tabler--clock-pause] size-6 text-neutral"></span>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-base-content">Idle Ticket Settings</h1>
                            <p class="text-sm text-base-content/60">Set rules for handling inactive tickets</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Success/Error Messages -->
        @if(session('success'))
            <div class="alert alert-success mb-4">
                <span class="icon-[tabler--check] size-5"></span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-error mb-4">
                <span class="icon-[tabler--x] size-5"></span>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Idle Settings Form -->
        <form action="{{ route('workspace.save-idle-settings', $workspace) }}" method="POST">
            @csrf

            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <h2 class="card-title text-lg mb-4">
                        <span class="icon-[tabler--settings] size-5"></span>
                        Idle Ticket Configuration
                    </h2>

                    <div class="space-y-6">
                        <!-- Idle Ticket Hours -->
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">What is the ticket idle time?</span>
                            </label>
                            <div class="flex items-center gap-3">
                                <input type="number"
                                       name="idle_ticket_hours"
                                       id="idle_ticket_hours"
                                       class="input input-bordered w-32"
                                       min="1"
                                       max="8760"
                                       value="{{ $inboxSettings->idle_ticket_hours ?? '' }}"
                                       placeholder="24">
                                <span class="text-base-content/60">hours</span>
                            </div>
                            <label class="label">
                                <span class="label-text-alt text-base-content/50">
                                    Number of hours of inactivity before a ticket is considered idle (max 8760 = 1 year)
                                </span>
                            </label>
                            @error('idle_ticket_hours')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                        </div>

                        <!-- Status on Customer Reply -->
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Update status on customer reply (idle ticket)</span>
                            </label>
                            @if($statuses->count() > 0)
                                <select name="idle_ticket_reply_status_id" id="idle_ticket_reply_status_id" data-select='{
                                    "placeholder": "Search and select status...",
                                    "hasSearch": true,
                                    "toggleTag": "<button type=\"button\" aria-expanded=\"false\"></button>",
                                    "toggleClasses": "advance-select-toggle",
                                    "dropdownClasses": "advance-select-menu max-h-52 overflow-y-auto",
                                    "optionClasses": "advance-select-option selected:select-active",
                                    "optionTemplate": "<div class=\"flex justify-between items-center w-full\"><div class=\"flex items-center gap-2\"><div data-icon></div><span class=\"text-sm text-base-content\" data-title></span></div><span class=\"icon-[tabler--check] shrink-0 size-4 text-primary hidden selected:block\"></span></div>",
                                    "extraMarkup": "<span class=\"icon-[tabler--caret-up-down] shrink-0 size-4 text-base-content/90 absolute top-1/2 end-3 -translate-y-1/2\"></span>"
                                }' class="hidden">
                                    <option value="">No status change</option>
                                    @foreach($statuses as $status)
                                        <option value="{{ $status->id }}"
                                                {{ ($inboxSettings->idle_ticket_reply_status_id ?? '') == $status->id ? 'selected' : '' }}
                                                data-select-option='{ "icon": "<span class=\"badge badge-sm text-white\" style=\"background-color: {{ $status->background_color }}\">{{ substr($status->name, 0, 2) }}</span>" }'>
                                            {{ $status->name }}
                                        </option>
                                    @endforeach
                                </select>
                            @else
                                <div class="alert alert-warning">
                                    <span class="icon-[tabler--alert-triangle] size-5"></span>
                                    <span>No workflow statuses available. Please configure a workflow for this workspace first.</span>
                                </div>
                            @endif
                            <label class="label">
                                <span class="label-text-alt text-base-content/50">
                                    When a customer replies to an idle ticket, automatically update the status to this value
                                </span>
                            </label>
                            @error('idle_ticket_reply_status_id')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex gap-3 mt-6">
                        <button type="submit" class="btn btn-primary gap-2">
                            <span class="icon-[tabler--device-floppy] size-5"></span>
                            Save Settings
                        </button>
                        <a href="{{ route('workspace.show', $workspace) }}" class="btn btn-ghost">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </form>

        <!-- Info Alert -->
        <div class="alert alert-info mt-6">
            <span class="icon-[tabler--info-circle] size-5"></span>
            <div class="text-sm">
                <p><strong>Idle Ticket Rules</strong> help you manage tickets that haven't been updated in a while.</p>
                <ul class="list-disc list-inside mt-2 space-y-1">
                    <li>Set the idle time to define when a ticket is considered inactive</li>
                    <li>Configure automatic status updates when customers reply to idle tickets</li>
                    <li>This helps ensure no customer inquiry goes unnoticed</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
