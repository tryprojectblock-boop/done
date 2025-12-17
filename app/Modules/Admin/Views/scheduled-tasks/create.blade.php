@extends('admin::layouts.app')

@section('title', 'Add Scheduled Task')
@section('page-title', 'Add Scheduled Task')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center gap-4">
        <a href="{{ route('backoffice.scheduled-tasks.index') }}" class="btn btn-ghost btn-sm btn-circle">
            <span class="icon-[tabler--arrow-left] size-5"></span>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-base-content">Add Scheduled Task</h1>
            <p class="text-base-content/60">Create a new automated background task</p>
        </div>
    </div>

    @include('admin::partials.alerts')

    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <form action="{{ route('backoffice.scheduled-tasks.store') }}" method="POST">
                @csrf

                <!-- Name (unique identifier) -->
                <div class="form-control mb-4">
                    <label class="label">
                        <span class="label-text font-medium">Identifier *</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name') }}" class="input input-bordered @error('name') input-error @enderror" placeholder="e.g., cleanup-temp-files" required>
                    @error('name')
                        <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                    @enderror
                    <label class="label">
                        <span class="label-text-alt">Unique identifier (lowercase, no spaces)</span>
                    </label>
                </div>

                <!-- Display Name -->
                <div class="form-control mb-4">
                    <label class="label">
                        <span class="label-text font-medium">Display Name *</span>
                    </label>
                    <input type="text" name="display_name" value="{{ old('display_name') }}" class="input input-bordered @error('display_name') input-error @enderror" placeholder="e.g., Clean Up Temporary Files" required>
                    @error('display_name')
                        <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                    @enderror
                </div>

                <!-- Description -->
                <div class="form-control mb-4">
                    <label class="label">
                        <span class="label-text font-medium">Description</span>
                    </label>
                    <textarea name="description" class="textarea textarea-bordered" rows="2" placeholder="What does this task do?">{{ old('description') }}</textarea>
                </div>

                <!-- Command -->
                <div class="form-control mb-4">
                    <label class="label">
                        <span class="label-text font-medium">Artisan Command *</span>
                    </label>
                    <select name="command" class="select select-bordered @error('command') select-error @enderror" required>
                        <option value="">Select a command...</option>
                        @foreach($availableCommands as $cmd => $label)
                            <option value="{{ $cmd }}" {{ old('command') === $cmd ? 'selected' : '' }}>{{ $label }} ({{ $cmd }})</option>
                        @endforeach
                    </select>
                    @error('command')
                        <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                    @enderror
                    <label class="label">
                        <span class="label-text-alt">Or enter a custom command below</span>
                    </label>
                    <input type="text" name="custom_command" value="{{ old('custom_command') }}" class="input input-bordered mt-2" placeholder="e.g., custom:command --option=value">
                </div>

                <div class="divider">Schedule</div>

                <!-- Frequency -->
                <div class="form-control mb-4">
                    <label class="label">
                        <span class="label-text font-medium">Frequency *</span>
                    </label>
                    <select name="frequency" id="frequency" class="select select-bordered" onchange="toggleFrequencyOptions()">
                        @foreach($frequencyOptions as $value => $label)
                            <option value="{{ $value }}" {{ old('frequency', 'daily') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Time -->
                <div class="form-control mb-4" id="time-group">
                    <label class="label">
                        <span class="label-text font-medium">Run At</span>
                    </label>
                    <input type="time" name="time" value="{{ old('time', '02:00') }}" class="input input-bordered">
                </div>

                <!-- Day of Week -->
                <div class="form-control mb-4" id="dow-group" style="display: none;">
                    <label class="label">
                        <span class="label-text font-medium">Day of Week</span>
                    </label>
                    <select name="day_of_week" class="select select-bordered">
                        @foreach($dayOfWeekOptions as $value => $label)
                            <option value="{{ $value }}" {{ old('day_of_week') == $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Day of Month -->
                <div class="form-control mb-4" id="dom-group" style="display: none;">
                    <label class="label">
                        <span class="label-text font-medium">Day of Month</span>
                    </label>
                    <input type="number" name="day_of_month" value="{{ old('day_of_month', 1) }}" min="1" max="31" class="input input-bordered">
                </div>

                <!-- Active -->
                <div class="form-control mb-6">
                    <label class="label cursor-pointer justify-start gap-3">
                        <input type="checkbox" name="is_active" value="1" checked class="toggle toggle-success">
                        <span class="label-text font-medium">Enable task immediately</span>
                    </label>
                </div>

                <div class="flex justify-end gap-2">
                    <a href="{{ route('backoffice.scheduled-tasks.index') }}" class="btn btn-ghost">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <span class="icon-[tabler--plus] size-4"></span>
                        Create Task
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function toggleFrequencyOptions() {
    const frequency = document.getElementById('frequency').value;
    document.getElementById('time-group').style.display = frequency === 'hourly' ? 'none' : '';
    document.getElementById('dow-group').style.display = frequency === 'weekly' ? '' : 'none';
    document.getElementById('dom-group').style.display = frequency === 'monthly' ? '' : 'none';
}

// Handle custom command
document.querySelector('select[name="command"]').addEventListener('change', function() {
    const customInput = document.querySelector('input[name="custom_command"]');
    if (this.value) {
        customInput.value = '';
        customInput.disabled = true;
    } else {
        customInput.disabled = false;
    }
});

document.querySelector('input[name="custom_command"]').addEventListener('input', function() {
    const selectInput = document.querySelector('select[name="command"]');
    if (this.value) {
        selectInput.value = '';
    }
});

// On form submit, use custom command if provided
document.querySelector('form').addEventListener('submit', function(e) {
    const customCommand = document.querySelector('input[name="custom_command"]').value;
    const selectCommand = document.querySelector('select[name="command"]');
    if (customCommand) {
        selectCommand.innerHTML = '<option value="' + customCommand + '" selected>' + customCommand + '</option>';
    }
});
</script>
@endpush
@endsection
