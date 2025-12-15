{{-- SLA Settings Drawer --}}
@php
    $priorities = $workspace->priorities()->orderBy('sort_order')->get();
    $slaSettings = $workspace->slaSettings()->with('priority')->get()->keyBy('priority_id');
@endphp

<div id="sla-settings-drawer" class="fixed inset-0 z-50 hidden">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-black/50 transition-opacity" onclick="closeSlaSettingsDrawer()"></div>

    <!-- Drawer Panel -->
    <div class="absolute right-0 top-0 h-full w-full max-w-2xl bg-base-100 shadow-xl transform translate-x-full transition-transform duration-300" id="sla-settings-drawer-panel">
        <!-- Drawer Header -->
        <div class="flex items-center justify-between p-4 border-b border-base-200">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center">
                    <span class="icon-[tabler--clock-hour-4] size-5 text-primary"></span>
                </div>
                <div>
                    <h3 class="font-semibold text-lg">SLA Settings</h3>
                    <p class="text-sm text-base-content/60">Configure response and resolution times by priority</p>
                </div>
            </div>
            <button type="button" class="btn btn-ghost btn-sm btn-square" onclick="closeSlaSettingsDrawer()">
                <span class="icon-[tabler--x] size-5"></span>
            </button>
        </div>

        <!-- Drawer Content -->
        <div class="overflow-y-auto h-[calc(100vh-160px)] p-4 space-y-6">
            <form id="sla-settings-form" onsubmit="submitSlaSettingsForm(event)">
                @if($priorities->count() > 0)
                    <div class="space-y-4">
                        @foreach($priorities as $priority)
                            @php
                                $sla = $slaSettings->get($priority->id);
                            @endphp
                            <div class="card bg-base-200">
                                <div class="card-body p-4">
                                    <div class="flex items-center gap-2 mb-4">
                                        <div class="w-4 h-4 rounded-full" style="background-color: {{ $priority->color }}"></div>
                                        <h4 class="font-semibold">{{ $priority->name }} Priority</h4>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <!-- First Reply Time -->
                                        <div class="space-y-2">
                                            <label class="label">
                                                <span class="label-text font-medium text-sm">First Reply Time</span>
                                            </label>
                                            <div class="flex gap-1">
                                                <div class="form-control flex-1">
                                                    <div class="input-group input-group-sm">
                                                        <input type="number" name="sla[{{ $priority->id }}][first_reply_days]"
                                                               class="input input-bordered input-sm w-full"
                                                               value="{{ $sla?->first_reply_days ?? 0 }}" min="0" max="30">
                                                        <span class="text-xs">d</span>
                                                    </div>
                                                </div>
                                                <div class="form-control flex-1">
                                                    <div class="input-group input-group-sm">
                                                        <input type="number" name="sla[{{ $priority->id }}][first_reply_hours]"
                                                               class="input input-bordered input-sm w-full"
                                                               value="{{ $sla?->first_reply_hours ?? 1 }}" min="0" max="23">
                                                        <span class="text-xs">h</span>
                                                    </div>
                                                </div>
                                                <div class="form-control flex-1">
                                                    <div class="input-group input-group-sm">
                                                        <input type="number" name="sla[{{ $priority->id }}][first_reply_minutes]"
                                                               class="input input-bordered input-sm w-full"
                                                               value="{{ $sla?->first_reply_minutes ?? 0 }}" min="0" max="59">
                                                        <span class="text-xs">m</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Next Reply Time -->
                                        <div class="space-y-2">
                                            <label class="label">
                                                <span class="label-text font-medium text-sm">Next Reply Time</span>
                                            </label>
                                            <div class="flex gap-1">
                                                <div class="form-control flex-1">
                                                    <div class="input-group input-group-sm">
                                                        <input type="number" name="sla[{{ $priority->id }}][next_reply_days]"
                                                               class="input input-bordered input-sm w-full"
                                                               value="{{ $sla?->next_reply_days ?? 0 }}" min="0" max="30">
                                                        <span class="text-xs">d</span>
                                                    </div>
                                                </div>
                                                <div class="form-control flex-1">
                                                    <div class="input-group input-group-sm">
                                                        <input type="number" name="sla[{{ $priority->id }}][next_reply_hours]"
                                                               class="input input-bordered input-sm w-full"
                                                               value="{{ $sla?->next_reply_hours ?? 4 }}" min="0" max="23">
                                                        <span class="text-xs">h</span>
                                                    </div>
                                                </div>
                                                <div class="form-control flex-1">
                                                    <div class="input-group input-group-sm">
                                                        <input type="number" name="sla[{{ $priority->id }}][next_reply_minutes]"
                                                               class="input input-bordered input-sm w-full"
                                                               value="{{ $sla?->next_reply_minutes ?? 0 }}" min="0" max="59">
                                                        <span class="text-xs">m</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Resolution Time -->
                                        <div class="space-y-2">
                                            <label class="label">
                                                <span class="label-text font-medium text-sm">Resolution Time</span>
                                            </label>
                                            <div class="flex gap-1">
                                                <div class="form-control flex-1">
                                                    <div class="input-group input-group-sm">
                                                        <input type="number" name="sla[{{ $priority->id }}][resolution_days]"
                                                               class="input input-bordered input-sm w-full"
                                                               value="{{ $sla?->resolution_days ?? 1 }}" min="0" max="30">
                                                        <span class="text-xs">d</span>
                                                    </div>
                                                </div>
                                                <div class="form-control flex-1">
                                                    <div class="input-group input-group-sm">
                                                        <input type="number" name="sla[{{ $priority->id }}][resolution_hours]"
                                                               class="input input-bordered input-sm w-full"
                                                               value="{{ $sla?->resolution_hours ?? 0 }}" min="0" max="23">
                                                        <span class="text-xs">h</span>
                                                    </div>
                                                </div>
                                                <div class="form-control flex-1">
                                                    <div class="input-group input-group-sm">
                                                        <input type="number" name="sla[{{ $priority->id }}][resolution_minutes]"
                                                               class="input input-bordered input-sm w-full"
                                                               value="{{ $sla?->resolution_minutes ?? 0 }}" min="0" max="59">
                                                        <span class="text-xs">m</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8 text-base-content/50">
                        <span class="icon-[tabler--flag-off] size-12 mb-2 opacity-50"></span>
                        <p class="text-sm">No priorities configured</p>
                        <p class="text-xs">Configure priorities first to set up SLA times</p>
                    </div>
                @endif

                <div id="sla-settings-error" class="text-error text-sm mt-4 hidden"></div>
            </form>

            <!-- Info Alert -->
            <div class="alert alert-info mt-4">
                <span class="icon-[tabler--info-circle] size-5"></span>
                <div class="text-sm">
                    <p><strong>First Reply Time:</strong> Time to send the first response to a ticket.</p>
                    <p><strong>Next Reply Time:</strong> Time between subsequent responses.</p>
                    <p><strong>Resolution Time:</strong> Time to fully resolve and close the ticket.</p>
                </div>
            </div>
        </div>

        <!-- Drawer Footer -->
        <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-base-200 bg-base-100">
            <div class="flex gap-3">
                @if($priorities->count() > 0)
                    <button type="submit" form="sla-settings-form" class="btn btn-primary flex-1">
                        <span class="icon-[tabler--check] size-5"></span>
                        Save SLA Settings
                    </button>
                @endif
                <button type="button" class="btn btn-ghost flex-1" onclick="closeSlaSettingsDrawer()">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
const slaSettingsEndpoint = '{{ route('workspace.save-sla-settings', $workspace) }}';
const slaCsrfToken = '{{ csrf_token() }}';

function openSlaSettingsDrawer() {
    const drawer = document.getElementById('sla-settings-drawer');
    const panel = document.getElementById('sla-settings-drawer-panel');

    drawer.classList.remove('hidden');
    document.body.style.overflow = 'hidden';

    setTimeout(() => {
        panel.classList.remove('translate-x-full');
    }, 10);
}

function closeSlaSettingsDrawer() {
    const drawer = document.getElementById('sla-settings-drawer');
    const panel = document.getElementById('sla-settings-drawer-panel');

    panel.classList.add('translate-x-full');

    setTimeout(() => {
        drawer.classList.add('hidden');
        document.body.style.overflow = '';
    }, 300);
}

async function submitSlaSettingsForm(event) {
    event.preventDefault();

    const form = document.getElementById('sla-settings-form');
    const formData = new FormData(form);
    formData.append('_token', slaCsrfToken);

    const errorDiv = document.getElementById('sla-settings-error');

    try {
        const response = await fetch(slaSettingsEndpoint, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            }
        });

        const data = await response.json();

        if (data.success) {
            showSlaToast(data.message || 'SLA settings saved successfully.', 'success');
            closeSlaSettingsDrawer();
        } else {
            errorDiv.textContent = data.message || 'An error occurred.';
            errorDiv.classList.remove('hidden');
        }
    } catch (error) {
        errorDiv.textContent = 'An error occurred. Please try again.';
        errorDiv.classList.remove('hidden');
    }
}

function showSlaToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = 'toast toast-top toast-end z-[70]';
    toast.innerHTML = `
        <div class="alert alert-${type}">
            <span class="icon-[tabler--${type === 'success' ? 'check' : 'x'}] size-5"></span>
            <span>${message}</span>
        </div>
    `;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

// Close on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const drawer = document.getElementById('sla-settings-drawer');
        if (drawer && !drawer.classList.contains('hidden')) {
            closeSlaSettingsDrawer();
        }
    }
});
</script>
