@extends('layouts.app')

@section('content')
<div class="p-4 md:p-6">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center gap-2 text-sm text-base-content/60 mb-2">
                <a href="{{ route('dashboard') }}" class="hover:text-primary">Dashboard</a>
                <span class="icon-[tabler--chevron-right] size-4"></span>
                <a href="{{ route('workflows.index') }}" class="hover:text-primary">Workflows</a>
                <span class="icon-[tabler--chevron-right] size-4"></span>
                <span>Edit</span>
            </div>
            <h1 class="text-2xl font-bold text-base-content">Edit Workflow</h1>
            <p class="text-base-content/60">Update workflow settings</p>
        </div>

        <!-- Messages -->
        <div class="mb-4">
            @include('partials.alerts')
        </div>

        <form action="{{ route('workflows.update', $workflow) }}" method="POST" id="workflow-form">
            @csrf
            @method('PUT')

            <!-- Section 1: Workflow Settings -->
            <div class="card bg-base-100 shadow mb-6">
                <div class="card-body">
                    <h2 class="card-title text-lg mb-4">
                        <span class="icon-[tabler--settings] size-5"></span>
                        Workflow Settings
                    </h2>

                    <!-- Workflow Name -->
                    <div class="form-control mb-4">
                        <label class="label" for="workflow-name">
                            <span class="label-text font-medium">Workflow Name <span class="text-error">*</span></span>
                        </label>
                        <input type="text" name="name" id="workflow-name" class="input input-bordered @error('name') input-error @enderror" placeholder="e.g. Basic Task Workflow" value="{{ old('name', $workflow->name) }}" required maxlength="100">
                        @error('name')
                            <span class="label">
                                <span class="label-text-alt text-error">{{ $message }}</span>
                            </span>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div class="form-control">
                        <label class="label" for="workflow-edit-description">
                            <span class="label-text font-medium">Description <span class="text-base-content/50 font-normal">(Optional)</span></span>
                        </label>
                        <textarea name="description" id="workflow-edit-description" class="textarea textarea-bordered" placeholder="Make it short and sweet…" rows="2" maxlength="500">{{ old('description', $workflow->description) }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Section 2: Statuses -->
            <div class="card bg-base-100 shadow mb-6">
                <div class="card-body">
                    <h2 class="card-title text-lg mb-4">
                        <span class="icon-[tabler--list-check] size-5"></span>
                        Statuses
                    </h2>

                    <!-- Column Headers -->
                    <div class="grid grid-cols-12 gap-3 px-3 py-2 text-sm font-medium text-base-content/60">
                        <div class="col-span-1"></div>
                        <div class="col-span-3">Status Name</div>
                        <div class="col-span-1 text-center">Color</div>
                        <div class="col-span-2">Responsibility</div>
                        <div class="col-span-2 text-center">Active</div>
                        <div class="col-span-2">Preview</div>
                        <div class="col-span-1 text-center">Action</div>
                    </div>

                    <!-- Status List -->
                    <div id="status-list" class="space-y-2">
                        @foreach($workflow->statuses->sortBy('sort_order') as $index => $status)
                        @php
                            $isFirst = $index === 0;
                            $isLast = $index === $workflow->statuses->count() - 1;
                            $isDefault = $isFirst || $isLast;
                        @endphp
                        <div class="status-row {{ $isDefault ? 'default-status' : '' }}" data-index="{{ $index }}" @if($isLast) id="closed-status-row" @endif>
                            <div class="grid grid-cols-12 gap-3 items-center p-3 bg-base-200/50 rounded-lg border border-base-300">
                                <input type="hidden" name="statuses[{{ $index }}][id]" value="{{ $status->id }}">
                                <input type="hidden" name="statuses[{{ $index }}][color]" value="{{ $status->color }}" class="status-color-hidden">
                                @if($isDefault)
                                <input type="hidden" name="statuses[{{ $index }}][is_active]" value="{{ $isFirst ? '1' : '0' }}">
                                @endif

                                <!-- Drag Handle -->
                                <div class="col-span-1 flex justify-center {{ $isDefault ? 'text-base-content/20' : '' }}">
                                    @if($isDefault)
                                        <span class="icon-[tabler--lock] size-5" title="Fixed position"></span>
                                    @else
                                        <div class="cursor-grab active:cursor-grabbing text-base-content/30 hover:text-base-content/60 drag-handle">
                                            <span class="icon-[tabler--grip-vertical] size-5"></span>
                                        </div>
                                    @endif
                                </div>

                                <!-- Status Name -->
                                <div class="col-span-3">
                                    <input type="text" name="statuses[{{ $index }}][name]" class="input input-bordered input-sm w-full status-name" placeholder="Status name" value="{{ old('statuses.'.$index.'.name', $status->name) }}" required maxlength="40">
                                </div>

                                <!-- Color Picker -->
                                <div class="col-span-1 flex justify-center">
                                    <div class="relative">
                                        <button type="button" class="btn btn-sm color-picker-btn" style="background-color: {{ $colors[$status->color]['bg'] ?? '#6b7280' }}; color: {{ $colors[$status->color]['text'] ?? '#ffffff' }};">
                                            <span class="icon-[tabler--palette] size-4"></span>
                                        </button>
                                        <div class="color-picker-dropdown hidden absolute top-full mt-1 right-0 bg-base-100 rounded-lg shadow-lg p-3 w-64 z-50 border border-base-300">
                                            <div class="grid grid-cols-6 gap-2">
                                                @foreach($colors as $key => $color)
                                                <label class="cursor-pointer">
                                                    <input type="radio" name="statuses[{{ $index }}][color]" value="{{ $key }}" class="hidden peer status-color" {{ $status->color === $key ? 'checked' : '' }}>
                                                    <span class="block w-8 h-8 rounded-full peer-checked:ring-2 peer-checked:ring-offset-2 peer-checked:ring-primary transition-all hover:scale-110" style="background-color: {{ $color['bg'] }}" title="{{ $color['label'] }}"></span>
                                                </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Responsibility -->
                                <div class="col-span-2">
                                    <select name="statuses[{{ $index }}][responsibility]" class="select select-bordered select-sm w-full">
                                        <option value="creator" {{ old('statuses.'.$index.'.responsibility', $status->responsibility) === 'creator' ? 'selected' : '' }}>Creator</option>
                                        <option value="assignee" {{ old('statuses.'.$index.'.responsibility', $status->responsibility ?? 'assignee') === 'assignee' ? 'selected' : '' }}>Assignee</option>
                                    </select>
                                </div>

                                <!-- Active Toggle -->
                                <div class="col-span-2 flex justify-center">
                                    @if($isDefault)
                                        <div class="flex items-center gap-2">
                                            <input type="checkbox" class="toggle toggle-sm toggle-success" {{ $isFirst ? 'checked' : '' }} disabled>
                                            <span class="text-sm text-base-content/50">{{ $isFirst ? 'Always Active' : 'Always Inactive' }}</span>
                                        </div>
                                    @else
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="hidden" name="statuses[{{ $index }}][is_active]" value="0">
                                            <input type="checkbox" name="statuses[{{ $index }}][is_active]" value="1" class="toggle toggle-sm toggle-success status-active" {{ $status->is_active ? 'checked' : '' }}>
                                            <span class="text-sm active-label {{ $status->is_active ? '' : 'text-base-content/50' }}">{{ $status->is_active ? 'Active' : 'Inactive' }}</span>
                                        </label>
                                    @endif
                                </div>

                                <!-- Preview -->
                                <div class="col-span-2">
                                    <span class="status-preview px-2.5 py-1 rounded text-xs font-medium" style="background-color: {{ $colors[$status->color]['bg'] ?? '#6b7280' }}; color: {{ $colors[$status->color]['text'] ?? '#ffffff' }};">{{ $status->name }}</span>
                                </div>

                                <!-- Action -->
                                <div class="col-span-1 flex justify-center">
                                    @if($isDefault)
                                        <span class="text-base-content/30">-</span>
                                    @else
                                        <button type="button" class="btn btn-ghost btn-sm btn-square text-error remove-status" title="Remove">
                                            <span class="icon-[tabler--trash] size-4"></span>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @if($isFirst)
                        <!-- Add Status Button (between first and last) -->
                        <div id="add-status-row" class="flex items-center justify-center py-3 border-2 border-dashed border-base-300 rounded-lg hover:border-primary/50 transition-colors">
                            <button type="button" id="add-status-btn" class="btn btn-circle btn-primary btn-sm">
                                <span class="icon-[tabler--plus] size-5"></span>
                            </button>
                            <span class="text-sm text-base-content/60 ml-2">Add new status</span>
                        </div>
                        @endif
                        @endforeach
                    </div>

                    <!-- Inactive Status Note -->
                    <div class="mt-4 p-3 bg-warning/10 border border-warning/20 rounded-lg">
                        <p class="text-sm text-black flex items-center gap-2">
                            <span class="icon-[tabler--info-circle] size-4"></span>
                            <span>Inactive statuses are hidden in task dropdowns and cannot be selected when updating tasks.</span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-between">
                <div class="flex gap-3">
                    <a href="{{ route('workflows.index') }}" class="btn btn-ghost">Cancel</a>
                    <button type="submit" class="btn btn-primary" id="submit-btn">
                        <span class="icon-[tabler--check] size-5"></span>
                        Save Changes
                    </button>
                </div>
                @if(!$workflow->isBuiltIn())
                <button type="button" class="btn btn-error btn-outline"
                    data-delete
                    data-delete-action="{{ route('workflows.destroy', $workflow) }}"
                    data-delete-title="Delete Workflow"
                    data-delete-name="{{ $workflow->name }}"
                    data-delete-warning="This action cannot be undone. All statuses will be deleted.">
                    <span class="icon-[tabler--trash] size-5"></span>
                    Delete Workflow
                </button>
                @endif

            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js" integrity="sha256-ipiJrswvAR4VAx/th+6zWsdeYmVae0iJuiR+6OqHJHQ=" crossorigin="anonymous"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const colors = @json($colors);
    let statusIndex = {{ $workflow->statuses->count() }};
    const statusList = document.getElementById('status-list');
    const addBtn = document.getElementById('add-status-btn');
    const submitBtn = document.getElementById('submit-btn');
    const workflowName = document.getElementById('workflow-name');
    const closedStatusRow = document.getElementById('closed-status-row');

    // Initialize Sortable - only for non-default statuses
    new Sortable(statusList, {
        animation: 150,
        handle: '.drag-handle',
        ghostClass: 'opacity-50',
        filter: '.default-status, #add-status-row',
        preventOnFilter: false,
        onMove: function(evt) {
            const related = evt.related;
            if (related.classList.contains('default-status') || related.id === 'add-status-row') {
                return false;
            }
            return true;
        },
        onEnd: updateIndexes
    });

    // Add status button
    addBtn.addEventListener('click', addStatus);

    // Form validation
    workflowName.addEventListener('input', validateForm);

    // Initialize existing rows
    document.querySelectorAll('.status-row').forEach(row => {
        attachRowEvents(row, row.classList.contains('default-status'));
    });

    // Get the next available unique color
    function getNextAvailableColor() {
        const usedColors = [];
        document.querySelectorAll('.status-row .status-color:checked, .status-row .status-color-hidden').forEach(input => {
            usedColors.push(input.value);
        });

        const colorKeys = Object.keys(colors);
        for (const key of colorKeys) {
            if (!usedColors.includes(key)) {
                return key;
            }
        }
        return colorKeys[0];
    }

    function addStatus() {
        const row = document.createElement('div');
        row.className = 'status-row';
        row.dataset.index = statusIndex;

        const defaultColor = getNextAvailableColor();

        row.innerHTML = `
            <div class="grid grid-cols-12 gap-3 items-center p-3 bg-base-200/50 rounded-lg border border-base-300">
                <input type="hidden" name="statuses[${statusIndex}][id]" value="">
                <input type="hidden" name="statuses[${statusIndex}][color]" value="${defaultColor}" class="status-color-hidden">

                <!-- Drag Handle -->
                <div class="col-span-1 flex justify-center">
                    <div class="cursor-grab active:cursor-grabbing text-base-content/30 hover:text-base-content/60 drag-handle">
                        <span class="icon-[tabler--grip-vertical] size-5"></span>
                    </div>
                </div>

                <!-- Status Name -->
                <div class="col-span-3">
                    <input type="text" name="statuses[${statusIndex}][name]" class="input input-bordered input-sm w-full status-name" placeholder="Status name" required maxlength="40">
                </div>

                <!-- Color Picker -->
                <div class="col-span-1 flex justify-center">
                    <div class="relative">
                        <button type="button" class="btn btn-sm color-picker-btn" style="background-color: ${colors[defaultColor].bg}; color: ${colors[defaultColor].text};">
                            <span class="icon-[tabler--palette] size-4"></span>
                        </button>
                        <div class="color-picker-dropdown hidden absolute top-full mt-1 right-0 bg-base-100 rounded-lg shadow-lg p-3 w-64 z-50 border border-base-300">
                            <div class="grid grid-cols-6 gap-2">
                                ${Object.entries(colors).map(([key, color]) => `
                                    <label class="cursor-pointer">
                                        <input type="radio" name="statuses[${statusIndex}][color]" value="${key}" class="hidden peer status-color" ${key === defaultColor ? 'checked' : ''}>
                                        <span class="block w-8 h-8 rounded-full peer-checked:ring-2 peer-checked:ring-offset-2 peer-checked:ring-primary transition-all hover:scale-110" style="background-color: ${color.bg}" title="${color.label}"></span>
                                    </label>
                                `).join('')}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Responsibility -->
                <div class="col-span-2">
                    <select name="statuses[${statusIndex}][responsibility]" class="select select-bordered select-sm w-full">
                        <option value="assignee" selected>Assignee</option>
                        <option value="creator">Creator</option>
                    </select>
                </div>

                <!-- Active Toggle -->
                <div class="col-span-2 flex justify-center">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="hidden" name="statuses[${statusIndex}][is_active]" value="0">
                        <input type="checkbox" name="statuses[${statusIndex}][is_active]" value="1" class="toggle toggle-sm toggle-success status-active" checked>
                        <span class="text-sm active-label">Active</span>
                    </label>
                </div>

                <!-- Preview -->
                <div class="col-span-2">
                    <span class="status-preview px-2.5 py-1 rounded text-xs font-medium" style="background-color: ${colors[defaultColor].bg}; color: ${colors[defaultColor].text};">New Status</span>
                </div>

                <!-- Delete -->
                <div class="col-span-1 flex justify-center">
                    <button type="button" class="btn btn-ghost btn-sm btn-square text-error remove-status" title="Remove">
                        <span class="icon-[tabler--trash] size-4"></span>
                    </button>
                </div>
            </div>
        `;

        // Insert before the Closed status row
        statusList.insertBefore(row, closedStatusRow);
        attachRowEvents(row, false);
        row.querySelector('.status-name').focus();
        statusIndex++;
        updateIndexes();
        validateForm();
    }

    function attachRowEvents(row, isDefault) {
        const nameInput = row.querySelector('.status-name');
        const colorInputs = row.querySelectorAll('.status-color');
        const preview = row.querySelector('.status-preview');
        const colorBtn = row.querySelector('.color-picker-btn');
        const colorDropdown = row.querySelector('.color-picker-dropdown');
        const colorHidden = row.querySelector('.status-color-hidden');

        // Name change
        nameInput.addEventListener('input', function() {
            preview.textContent = this.value || 'New Status';
            validateForm();
        });

        // Color picker toggle on click
        colorBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            document.querySelectorAll('.color-picker-dropdown').forEach(d => {
                if (d !== colorDropdown) d.classList.add('hidden');
            });
            colorDropdown.classList.toggle('hidden');
        });

        // Color change
        colorInputs.forEach(input => {
            input.addEventListener('change', function() {
                const color = colors[this.value];
                if (color) {
                    preview.style.backgroundColor = color.bg;
                    preview.style.color = color.text;
                    colorBtn.style.backgroundColor = color.bg;
                    colorBtn.style.color = color.text;
                    if (colorHidden) {
                        colorHidden.value = this.value;
                    }
                }
                colorDropdown.classList.add('hidden');
            });
        });

        // Only attach active toggle and remove button events for non-default statuses
        if (!isDefault) {
            const activeToggle = row.querySelector('.status-active');
            const activeLabel = row.querySelector('.active-label');
            const removeBtn = row.querySelector('.remove-status');

            if (activeToggle) {
                activeToggle.addEventListener('change', function() {
                    if (this.checked) {
                        activeLabel.textContent = 'Active';
                        activeLabel.classList.remove('text-base-content/50');
                    } else {
                        activeLabel.textContent = 'Inactive';
                        activeLabel.classList.add('text-base-content/50');
                    }
                    validateForm();
                });
            }

            if (removeBtn) {
                removeBtn.addEventListener('click', function() {
                    row.remove();
                    updateIndexes();
                    validateForm();
                });
            }
        }
    }

    // Close color picker when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.color-picker-btn') && !e.target.closest('.color-picker-dropdown')) {
            document.querySelectorAll('.color-picker-dropdown').forEach(d => d.classList.add('hidden'));
        }
    });

    function updateIndexes() {
        const rows = statusList.querySelectorAll('.status-row');
        rows.forEach((row, index) => {
            row.dataset.index = index;
            row.querySelectorAll('input, select, textarea').forEach(input => {
                if (input.name) {
                    input.name = input.name.replace(/statuses\[\d+\]/, `statuses[${index}]`);
                }
            });
        });
    }

    function validateForm() {
        const name = workflowName.value.trim();
        const rows = statusList.querySelectorAll('.status-row');
        let allNamesValid = true;

        rows.forEach(row => {
            const nameInput = row.querySelector('.status-name');
            if (!nameInput.value.trim()) {
                allNamesValid = false;
            }
        });

        // First status (Open) is always active, so we always have at least one active status
        const isValid = name && rows.length > 0 && allNamesValid;
        submitBtn.disabled = !isValid;
    }

    // Initial setup
    updateIndexes();
    validateForm();
});
</script>
@endsection
