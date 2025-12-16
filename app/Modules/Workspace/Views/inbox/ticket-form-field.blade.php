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
                <a href="{{ route('workspace.inbox.ticket-form', $workspace) }}" class="hover:text-primary">Ticket Form</a>
                <span class="icon-[tabler--chevron-right] size-4"></span>
                <span>{{ isset($field) ? 'Edit Field' : 'Add Field' }}</span>
            </div>
            <div class="flex items-center gap-4">
                <a href="{{ route('workspace.inbox.ticket-form', $workspace) }}" class="btn btn-ghost btn-sm btn-square">
                    <span class="icon-[tabler--arrow-left] size-5"></span>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-base-content">{{ isset($field) ? 'Edit Field' : 'Add Custom Field' }}</h1>
                    <p class="text-sm text-base-content/60">{{ isset($field) ? 'Update field settings' : 'Add a new field to your ticket form' }}</p>
                </div>
            </div>
        </div>

        @if(session('error'))
        <div class="alert alert-error mb-4">
            <span class="icon-[tabler--alert-circle] size-5"></span>
            <span>{{ session('error') }}</span>
        </div>
        @endif

        <form action="{{ isset($field) ? route('workspace.ticket-form.fields.update', [$workspace, $field]) : route('workspace.ticket-form.fields.store', $workspace) }}" method="POST" class="space-y-6">
            @csrf
            @if(isset($field))
                @method('PUT')
            @endif

            <!-- Field Type Selection -->
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <h3 class="font-semibold mb-4 flex items-center gap-2">
                        <span class="icon-[tabler--category] size-5"></span>
                        Field Type
                    </h3>

                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                        @foreach($fieldTypes as $type => $config)
                        <label class="cursor-pointer">
                            <input type="radio" name="type" value="{{ $type }}" class="peer hidden" {{ (old('type', $field->type ?? '') == $type) ? 'checked' : '' }} required>
                            <div class="p-4 border-2 border-base-300 rounded-lg peer-checked:border-primary peer-checked:bg-primary/5 hover:border-base-content/30 transition">
                                <div class="flex flex-col items-center text-center">
                                    <div class="w-10 h-10 rounded-lg bg-base-200 peer-checked:bg-primary/20 flex items-center justify-center mb-2">
                                        <span class="icon-[{{ $config['icon'] }}] size-5"></span>
                                    </div>
                                    <span class="font-medium text-sm">{{ $config['label'] }}</span>
                                    <span class="text-xs text-base-content/50 mt-1">{{ $config['description'] }}</span>
                                </div>
                            </div>
                        </label>
                        @endforeach
                    </div>

                    @error('type')
                    <p class="text-error text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Field Settings -->
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <h3 class="font-semibold mb-4 flex items-center gap-2">
                        <span class="icon-[tabler--settings] size-5"></span>
                        Field Settings
                    </h3>

                    <div class="space-y-4">
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Label <span class="text-error">*</span></span>
                            </label>
                            <input type="text" name="label" value="{{ old('label', $field->label ?? '') }}" class="input input-bordered" placeholder="e.g., Company Name" required>
                            @error('label')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                            @enderror
                        </div>

                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Placeholder</span>
                            </label>
                            <input type="text" name="placeholder" value="{{ old('placeholder', $field->placeholder ?? '') }}" class="input input-bordered" placeholder="e.g., Enter your company name">
                        </div>

                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Help Text</span>
                            </label>
                            <input type="text" name="help_text" value="{{ old('help_text', $field->help_text ?? '') }}" class="input input-bordered" placeholder="Optional help text shown below the field">
                        </div>

                        <div class="form-control">
                            <label class="label cursor-pointer justify-start gap-3">
                                <input type="checkbox" name="is_required" value="1" class="checkbox checkbox-primary checkbox-sm" {{ old('is_required', $field->is_required ?? false) ? 'checked' : '' }}>
                                <span class="label-text font-medium">Required field</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dropdown Options (shown only for select type) -->
            <div class="card bg-base-100 shadow" id="options-card" style="display: none;">
                <div class="card-body">
                    <h3 class="font-semibold mb-4 flex items-center gap-2">
                        <span class="icon-[tabler--list] size-5"></span>
                        Dropdown Options
                    </h3>

                    <div id="options-container" class="space-y-2">
                        @if(isset($field) && $field->type === 'select' && is_array($field->options))
                            @foreach($field->options as $index => $option)
                            <div class="flex items-center gap-2 option-row">
                                <span class="icon-[tabler--grip-vertical] size-4 text-base-content/30 cursor-grab"></span>
                                <input type="text" name="options[]" value="{{ is_array($option) ? ($option['label'] ?? $option['value'] ?? '') : $option }}" class="input input-bordered input-sm flex-1" placeholder="Option label">
                                <button type="button" class="btn btn-ghost btn-sm btn-square text-error" onclick="removeOption(this)">
                                    <span class="icon-[tabler--trash] size-4"></span>
                                </button>
                            </div>
                            @endforeach
                        @endif
                    </div>

                    <button type="button" onclick="addOption()" class="btn btn-ghost btn-sm mt-2">
                        <span class="icon-[tabler--plus] size-4"></span>
                        Add Option
                    </button>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-between">
                <a href="{{ route('workspace.inbox.ticket-form', $workspace) }}" class="btn btn-ghost">
                    Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <span class="icon-[tabler--device-floppy] size-5"></span>
                    {{ isset($field) ? 'Update Field' : 'Add Field' }}
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Show/hide options card based on field type
document.querySelectorAll('input[name="type"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const optionsCard = document.getElementById('options-card');
        if (this.value === 'select') {
            optionsCard.style.display = 'block';
            // Add initial option if empty
            if (document.querySelectorAll('.option-row').length === 0) {
                addOption();
            }
        } else {
            optionsCard.style.display = 'none';
        }
    });
});

// Initial check
const checkedType = document.querySelector('input[name="type"]:checked');
if (checkedType && checkedType.value === 'select') {
    document.getElementById('options-card').style.display = 'block';
}

function addOption() {
    const container = document.getElementById('options-container');
    const row = document.createElement('div');
    row.className = 'flex items-center gap-2 option-row';
    row.innerHTML = `
        <span class="icon-[tabler--grip-vertical] size-4 text-base-content/30 cursor-grab"></span>
        <input type="text" name="options[]" class="input input-bordered input-sm flex-1" placeholder="Option label">
        <button type="button" class="btn btn-ghost btn-sm btn-square text-error" onclick="removeOption(this)">
            <span class="icon-[tabler--trash] size-4"></span>
        </button>
    `;
    container.appendChild(row);
}

function removeOption(btn) {
    btn.closest('.option-row').remove();
}
</script>
@endsection
