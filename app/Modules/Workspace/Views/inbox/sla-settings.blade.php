@extends('layouts.app')

@section('content')
<div class="p-4 md:p-6">
    <div class="max-w-5xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center gap-2 text-sm text-base-content/60 mb-2">
                <a href="{{ route('dashboard') }}" class="hover:text-primary">Dashboard</a>
                <span class="icon-[tabler--chevron-right] size-4"></span>
                <a href="{{ route('workspace.show', $workspace) }}" class="hover:text-primary">{{ $workspace->name }}</a>
                <span class="icon-[tabler--chevron-right] size-4"></span>
                <span>SLA Settings</span>
            </div>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <a href="{{ route('workspace.show', $workspace) }}" class="btn btn-ghost btn-sm btn-square">
                        <span class="icon-[tabler--arrow-left] size-5"></span>
                    </a>
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-lg bg-success/10 flex items-center justify-center">
                            <span class="icon-[tabler--clock-check] size-6 text-success"></span>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-base-content">SLA Settings</h1>
                            <p class="text-sm text-base-content/60">Define service level agreements for response times</p>
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

        @php
            $colorOptions = [
                'red' => '#ef4444', 'orange' => '#f97316', 'amber' => '#f59e0b', 'yellow' => '#eab308',
                'lime' => '#84cc16', 'green' => '#22c55e', 'emerald' => '#10b981', 'teal' => '#14b8a6',
                'cyan' => '#06b6d4', 'sky' => '#0ea5e9', 'blue' => '#3b82f6', 'indigo' => '#6366f1',
                'violet' => '#8b5cf6', 'purple' => '#a855f7', 'fuchsia' => '#d946ef', 'pink' => '#ec4899',
                'rose' => '#f43f5e', 'slate' => '#64748b',
            ];
        @endphp

        <!-- SLA Settings Form -->
        <form action="{{ route('workspace.save-sla-settings', $workspace) }}" method="POST" id="sla-settings-form">
            @csrf

            <div class="space-y-6">
                @if($priorities->count() === 0)
                    <div class="alert alert-warning">
                        <span class="icon-[tabler--alert-triangle] size-5"></span>
                        <span>No priorities configured. Please <a href="{{ route('workspace.inbox.priorities', $workspace) }}" class="link">add priorities</a> first to configure SLA settings.</span>
                    </div>
                @else
                    <!-- SLA Settings by Priority -->
                    @foreach($priorities as $priority)
                        @php
                            $sla = $slaSettings[$priority->id] ?? null;
                            $colorHex = $colorOptions[$priority->color] ?? '#64748b';
                        @endphp
                        <div class="card bg-base-100 shadow">
                            <div class="card-body">
                                <h2 class="card-title text-lg mb-4 flex items-center gap-3">
                                    <span class="badge text-white" style="background-color: {{ $colorHex }};">
                                        {{ $priority->name }}
                                    </span>
                                    Priority SLA
                                </h2>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <!-- First Reply Time -->
                                    <div class="p-4 bg-base-200 rounded-lg">
                                        <h3 class="font-medium text-sm mb-3 flex items-center gap-2">
                                            <span class="icon-[tabler--message-reply] size-4 text-primary"></span>
                                            First Reply Time
                                        </h3>
                                        <div class="grid grid-cols-3 gap-2">
                                            <div class="form-control">
                                                <label class="label py-1">
                                                    <span class="label-text text-xs">Days</span>
                                                </label>
                                                <input type="number" name="sla[{{ $priority->id }}][first_reply_days]" value="{{ $sla->first_reply_days ?? 0 }}" min="0" max="30" class="input input-bordered input-sm">
                                            </div>
                                            <div class="form-control">
                                                <label class="label py-1">
                                                    <span class="label-text text-xs">Hours</span>
                                                </label>
                                                <input type="number" name="sla[{{ $priority->id }}][first_reply_hours]" value="{{ $sla->first_reply_hours ?? 1 }}" min="0" max="23" class="input input-bordered input-sm">
                                            </div>
                                            <div class="form-control">
                                                <label class="label py-1">
                                                    <span class="label-text text-xs">Mins</span>
                                                </label>
                                                <input type="number" name="sla[{{ $priority->id }}][first_reply_minutes]" value="{{ $sla->first_reply_minutes ?? 0 }}" min="0" max="59" class="input input-bordered input-sm">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Next Reply Time -->
                                    <div class="p-4 bg-base-200 rounded-lg">
                                        <h3 class="font-medium text-sm mb-3 flex items-center gap-2">
                                            <span class="icon-[tabler--messages] size-4 text-info"></span>
                                            Next Reply Time
                                        </h3>
                                        <div class="grid grid-cols-3 gap-2">
                                            <div class="form-control">
                                                <label class="label py-1">
                                                    <span class="label-text text-xs">Days</span>
                                                </label>
                                                <input type="number" name="sla[{{ $priority->id }}][next_reply_days]" value="{{ $sla->next_reply_days ?? 0 }}" min="0" max="30" class="input input-bordered input-sm">
                                            </div>
                                            <div class="form-control">
                                                <label class="label py-1">
                                                    <span class="label-text text-xs">Hours</span>
                                                </label>
                                                <input type="number" name="sla[{{ $priority->id }}][next_reply_hours]" value="{{ $sla->next_reply_hours ?? 4 }}" min="0" max="23" class="input input-bordered input-sm">
                                            </div>
                                            <div class="form-control">
                                                <label class="label py-1">
                                                    <span class="label-text text-xs">Mins</span>
                                                </label>
                                                <input type="number" name="sla[{{ $priority->id }}][next_reply_minutes]" value="{{ $sla->next_reply_minutes ?? 0 }}" min="0" max="59" class="input input-bordered input-sm">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Resolution Time -->
                                    <div class="p-4 bg-base-200 rounded-lg">
                                        <h3 class="font-medium text-sm mb-3 flex items-center gap-2">
                                            <span class="icon-[tabler--check] size-4 text-success"></span>
                                            Resolution Time
                                        </h3>
                                        <div class="grid grid-cols-3 gap-2">
                                            <div class="form-control">
                                                <label class="label py-1">
                                                    <span class="label-text text-xs">Days</span>
                                                </label>
                                                <input type="number" name="sla[{{ $priority->id }}][resolution_days]" value="{{ $sla->resolution_days ?? 1 }}" min="0" max="30" class="input input-bordered input-sm">
                                            </div>
                                            <div class="form-control">
                                                <label class="label py-1">
                                                    <span class="label-text text-xs">Hours</span>
                                                </label>
                                                <input type="number" name="sla[{{ $priority->id }}][resolution_hours]" value="{{ $sla->resolution_hours ?? 0 }}" min="0" max="23" class="input input-bordered input-sm">
                                            </div>
                                            <div class="form-control">
                                                <label class="label py-1">
                                                    <span class="label-text text-xs">Mins</span>
                                                </label>
                                                <input type="number" name="sla[{{ $priority->id }}][resolution_minutes]" value="{{ $sla->resolution_minutes ?? 0 }}" min="0" max="59" class="input input-bordered input-sm">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <!-- Info Alert -->
                    <div class="alert alert-info">
                        <span class="icon-[tabler--info-circle] size-5"></span>
                        <div>
                            <p class="text-sm font-medium">SLA Time Calculations</p>
                            <ul class="text-xs mt-1 list-disc list-inside">
                                <li><strong>First Reply Time:</strong> Maximum time to send first response to the customer</li>
                                <li><strong>Next Reply Time:</strong> Maximum time between subsequent responses</li>
                                <li><strong>Resolution Time:</strong> Maximum time to fully resolve the ticket</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-start gap-3">
                        <button type="submit" class="btn btn-primary">
                            <span class="icon-[tabler--check] size-5"></span>
                            Save SLA Settings
                        </button>
                        <a href="{{ route('workspace.show', $workspace) }}" class="btn btn-ghost">Cancel</a>
                    </div>
                @endif
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('sla-settings-form')?.addEventListener('submit', async function(e) {
    e.preventDefault();

    const formData = new FormData(this);

    try {
        const response = await fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            }
        });

        const data = await response.json();

        if (data.success) {
            showToast(data.message || 'SLA settings saved successfully.', 'success');
            // Redirect to workspace overview after short delay
            setTimeout(() => {
                window.location.href = '{{ route('workspace.show', $workspace) }}';
            }, 1000);
        } else {
            showToast(data.message || 'An error occurred.', 'error');
        }
    } catch (error) {
        showToast('An error occurred. Please try again.', 'error');
    }
});

function showToast(message, type = 'success') {
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
</script>
@endsection
