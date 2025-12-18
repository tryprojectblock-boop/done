@extends('layouts.app')

@section('content')
<div class="p-4 md:p-6">
    <div class="max-w-5xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center gap-2 text-sm text-base-content/60 mb-2">
                <a href="{{ route('dashboard') }}" class="hover:text-primary">Dashboard</a>
                <span class="icon-[tabler--chevron-right] size-4"></span>
                <a href="{{ route('workflows.index') }}" class="hover:text-primary">Workflows</a>
                <span class="icon-[tabler--chevron-right] size-4"></span>
                <span>Status Rules</span>
            </div>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-base-content">Status Transition Rules</h1>
                    <p class="text-base-content/60">Define which statuses can transition to other statuses for <span class="font-medium">{{ $workflow->name }}</span></p>
                </div>
                <a href="{{ route('workflows.edit', $workflow) }}" class="btn btn-ghost">
                    <span class="icon-[tabler--arrow-left] size-5"></span>
                    Back to Edit
                </a>
            </div>
        </div>

        <!-- Messages -->
        <div class="mb-4">
            @include('partials.alerts')
        </div>

        <!-- Info Box -->
        <div class="mb-6 p-4 bg-info/10 border border-info/20 rounded-lg">
            <h4 class="font-semibold text-info mb-2 flex items-center gap-2">
                <span class="icon-[tabler--info-circle] size-5"></span>
                About Status Rules
            </h4>
            <p class="text-sm text-base-content/70">
                Status rules control which statuses a task can transition to from its current status.
                For example, if a task is "Open", you can define that it can only move to "In Progress" or "Closed", but not directly to "Done".
                If no rules are set for a status, tasks can transition to any other status.
            </p>
        </div>

        <form action="{{ route('workflows.status-rules.update', $workflow) }}" method="POST">
            @csrf
            @method('PUT')

            <!-- Status Rules Cards -->
            <div class="space-y-4">
                @foreach($workflow->statuses as $status)
                <div class="card bg-base-100 shadow">
                    <div class="card-body">
                        <div class="flex items-start gap-4">
                            <!-- From Status -->
                            <div class="flex-shrink-0">
                                <div class="flex items-center gap-3">
                                    <span class="px-3 py-1.5 rounded text-sm font-medium" style="background-color: {{ $status->background_color }}; color: {{ $status->text_color }};">
                                        {{ $status->name }}
                                    </span>
                                    <span class="icon-[tabler--arrow-right] size-5 text-base-content/40"></span>
                                </div>
                                @if(!$status->is_active)
                                    <span class="text-xs text-base-content/50 mt-1 block">(Inactive status)</span>
                                @endif
                            </div>

                            <!-- Arrow and Target Statuses -->
                            <div class="flex-1">
                                <p class="text-sm text-base-content/60 mb-3">Can transition to:</p>
                                <!-- Hidden input to ensure status is always in form submission -->
                                <input type="hidden" name="rules[{{ $status->id }}]" value="">
                                <div class="flex flex-wrap gap-2">
                                    @foreach($workflow->statuses as $targetStatus)
                                        @if($targetStatus->id !== $status->id)
                                        <label class="cursor-pointer transition-status-label">
                                            <input type="checkbox"
                                                   name="rules[{{ $status->id }}][]"
                                                   value="{{ $targetStatus->id }}"
                                                   class="hidden transition-checkbox"
                                                   {{ $status->allowed_transitions === null || in_array($targetStatus->id, $status->allowed_transitions ?? []) ? 'checked' : '' }}>
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded text-sm font-medium border-2 transition-all transition-badge">
                                                <span class="unchecked-icon">
                                                    <span class="icon-[tabler--circle] size-4 text-base-content/30"></span>
                                                </span>
                                                <span class="checked-icon hidden">
                                                    <span class="icon-[tabler--circle-check-filled] size-4 text-success"></span>
                                                </span>
                                                <span class="w-3 h-3 rounded-full" style="background-color: {{ $targetStatus->background_color }};"></span>
                                                {{ $targetStatus->name }}
                                            </span>
                                        </label>
                                        @endif
                                    @endforeach
                                </div>

                                @if($workflow->statuses->count() <= 1)
                                    <p class="text-sm text-base-content/50 italic">No other statuses available</p>
                                @endif
                            </div>

                            <!-- Quick Actions -->
                            <div class="flex-shrink-0 flex flex-col gap-1">
                                <button type="button" class="btn btn-xs btn-ghost select-all-btn" data-status-id="{{ $status->id }}">
                                    <span class="icon-[tabler--checks] size-4"></span>
                                    All
                                </button>
                                <button type="button" class="btn btn-xs btn-ghost select-none-btn" data-status-id="{{ $status->id }}">
                                    <span class="icon-[tabler--x] size-4"></span>
                                    None
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Actions -->
            <div class="flex justify-between mt-6">
                <a href="{{ route('workflows.index') }}" class="btn btn-ghost">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <span class="icon-[tabler--check] size-5"></span>
                    Save Rules
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Update badge styling based on checkbox state
    function updateBadgeStyle(checkbox) {
        const label = checkbox.closest('.transition-status-label');
        const badge = label.querySelector('.transition-badge');
        const uncheckedIcon = label.querySelector('.unchecked-icon');
        const checkedIcon = label.querySelector('.checked-icon');

        if (checkbox.checked) {
            badge.classList.add('border-success', 'bg-success/10');
            badge.classList.remove('border-base-300');
            uncheckedIcon.classList.add('hidden');
            checkedIcon.classList.remove('hidden');
        } else {
            badge.classList.remove('border-success', 'bg-success/10');
            badge.classList.add('border-base-300');
            uncheckedIcon.classList.remove('hidden');
            checkedIcon.classList.add('hidden');
        }
    }

    // Initialize all checkboxes
    document.querySelectorAll('.transition-checkbox').forEach(cb => {
        updateBadgeStyle(cb);
        cb.addEventListener('change', function() {
            updateBadgeStyle(this);
        });
    });

    // Select All buttons
    document.querySelectorAll('.select-all-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const statusId = this.dataset.statusId;
            const card = this.closest('.card');
            card.querySelectorAll(`input[name="rules[${statusId}][]"]`).forEach(cb => {
                cb.checked = true;
                updateBadgeStyle(cb);
            });
        });
    });

    // Select None buttons
    document.querySelectorAll('.select-none-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const statusId = this.dataset.statusId;
            const card = this.closest('.card');
            card.querySelectorAll(`input[name="rules[${statusId}][]"]`).forEach(cb => {
                cb.checked = false;
                updateBadgeStyle(cb);
            });
        });
    });
});
</script>
@endsection
