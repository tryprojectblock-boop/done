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
                <span>Create</span>
            </div>
            <h1 class="text-2xl font-bold text-base-content">Create New Workflow</h1>
            <p class="text-base-content/60">Define a workflow for your organization</p>
        </div>

        <!-- Messages -->
        <div class="mb-4">
            @include('partials.alerts')
        </div>

        <form action="{{ route('workflows.store') }}" method="POST" id="workflow-form">
            @csrf

            <!-- Section 1: Workflow Type -->
            <div class="card bg-base-100 shadow mb-6">
                <div class="card-body">
                    <h2 class="card-title text-lg mb-4">
                        <span class="icon-[tabler--category] size-5"></span>
                        Workflow Type
                    </h2>
                    <p class="text-sm text-base-content/60 mb-4">Choose a workflow type based on the workspace you'll be using it with.</p>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-2" id="workflow-type-selector">
                        <!-- Classic Workflow -->
                        <label class="cursor-pointer block">
                            <input type="radio" name="type" value="classic" class="peer sr-only workflow-type-radio" {{ old('type', 'classic') === 'classic' ? 'checked' : '' }}>
                            <div class="relative h-full rounded-xl border-2 border-base-300 bg-base-100 p-4 transition-all duration-200
                                        hover:border-blue-400 hover:shadow-lg
                                        peer-checked:border-blue-500 peer-checked:bg-blue-50 peer-checked:shadow-lg peer-checked:shadow-blue-500/20
                                        dark:peer-checked:bg-blue-950/40">
                                <!-- Selected Checkmark -->
                                <div class="absolute -top-2 -right-2 scale-0 peer-checked:scale-100 transition-transform duration-200">
                                    <span class="flex items-center justify-center w-7 h-7 rounded-full bg-blue-500 text-white shadow-lg ring-2 ring-white dark:ring-base-100">
                                        <span class="icon-[tabler--check] size-4"></span>
                                    </span>
                                </div>
                                <div class="flex items-center gap-3 mb-3">
                                    <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-900/50 transition-colors">
                                        <span class="icon-[tabler--layout-list] size-7 text-blue-600 dark:text-blue-400"></span>
                                    </div>
                                    <div>
                                        <span class="font-bold text-base text-base-content block">Classic</span>
                                        <span class="text-xs text-blue-600 dark:text-blue-400 font-medium">Classic Workspace</span>
                                    </div>
                                </div>
                                <p class="text-sm text-base-content/60 mb-4">Simple Open/Closed workflow for basic task tracking</p>
                                <div class="flex items-center gap-2 pt-3 border-t border-base-200">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-blue-500 text-white">Open</span>
                                    <span class="icon-[tabler--chevron-right] size-4 text-base-content/30"></span>
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-slate-500 text-white">Closed</span>
                                </div>
                            </div>
                        </label>

                        <!-- Product Workflow -->
                        <label class="cursor-pointer block">
                            <input type="radio" name="type" value="product" class="peer sr-only workflow-type-radio" {{ old('type', 'classic') === 'product' ? 'checked' : '' }}>
                            <div class="relative h-full rounded-xl border-2 border-base-300 bg-base-100 p-4 transition-all duration-200
                                        hover:border-purple-400 hover:shadow-lg
                                        peer-checked:border-purple-500 peer-checked:bg-purple-50 peer-checked:shadow-lg peer-checked:shadow-purple-500/20
                                        dark:peer-checked:bg-purple-950/40">
                                <!-- Selected Checkmark -->
                                <div class="absolute -top-2 -right-2 scale-0 peer-checked:scale-100 transition-transform duration-200">
                                    <span class="flex items-center justify-center w-7 h-7 rounded-full bg-purple-500 text-white shadow-lg ring-2 ring-white dark:ring-base-100">
                                        <span class="icon-[tabler--check] size-4"></span>
                                    </span>
                                </div>
                                <div class="flex items-center gap-3 mb-3">
                                    <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-purple-100 dark:bg-purple-900/50 transition-colors">
                                        <span class="icon-[tabler--box] size-7 text-purple-600 dark:text-purple-400"></span>
                                    </div>
                                    <div>
                                        <span class="font-bold text-base text-base-content block">Product</span>
                                        <span class="text-xs text-purple-600 dark:text-purple-400 font-medium">Product Workspace</span>
                                    </div>
                                </div>
                                <p class="text-sm text-base-content/60 mb-4">Includes Backlog for product planning and prioritization</p>
                                <div class="flex items-center gap-2 pt-3 border-t border-base-200">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-purple-500 text-white">Backlog</span>
                                    <span class="icon-[tabler--chevron-right] size-4 text-base-content/30"></span>
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-blue-500 text-white">Open</span>
                                    <span class="icon-[tabler--chevron-right] size-4 text-base-content/30"></span>
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-slate-500 text-white">Closed</span>
                                </div>
                            </div>
                        </label>

                        <!-- Inbox Workflow -->
                        <label class="cursor-pointer block">
                            <input type="radio" name="type" value="inbox" class="peer sr-only workflow-type-radio" {{ old('type', 'classic') === 'inbox' ? 'checked' : '' }}>
                            <div class="relative h-full rounded-xl border-2 border-base-300 bg-base-100 p-4 transition-all duration-200
                                        hover:border-orange-400 hover:shadow-lg
                                        peer-checked:border-orange-500 peer-checked:bg-orange-50 peer-checked:shadow-lg peer-checked:shadow-orange-500/20
                                        dark:peer-checked:bg-orange-950/40">
                                <!-- Selected Checkmark -->
                                <div class="absolute -top-2 -right-2 scale-0 peer-checked:scale-100 transition-transform duration-200">
                                    <span class="flex items-center justify-center w-7 h-7 rounded-full bg-orange-500 text-white shadow-lg ring-2 ring-white dark:ring-base-100">
                                        <span class="icon-[tabler--check] size-4"></span>
                                    </span>
                                </div>
                                <div class="flex items-center gap-3 mb-3">
                                    <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-orange-100 dark:bg-orange-900/50 transition-colors">
                                        <span class="icon-[tabler--inbox] size-7 text-orange-600 dark:text-orange-400"></span>
                                    </div>
                                    <div>
                                        <span class="font-bold text-base text-base-content block">Inbox</span>
                                        <span class="text-xs text-orange-600 dark:text-orange-400 font-medium">Inbox Workspace</span>
                                    </div>
                                </div>
                                <p class="text-sm text-base-content/60 mb-4">Includes Unassigned status for triage and assignment</p>
                                <div class="flex items-center gap-2 pt-3 border-t border-base-200">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-orange-500 text-white">Unassigned</span>
                                    <span class="icon-[tabler--chevron-right] size-4 text-base-content/30"></span>
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-blue-500 text-white">Open</span>
                                    <span class="icon-[tabler--chevron-right] size-4 text-base-content/30"></span>
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-slate-500 text-white">Closed</span>
                                </div>
                            </div>
                        </label>
                    </div>
                    @error('type')
                        <span class="label">
                            <span class="label-text-alt text-error">{{ $message }}</span>
                        </span>
                    @enderror
                </div>
            </div>

            <!-- Section 2: Workflow Settings -->
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
                        <input type="text" name="name" id="workflow-name" class="input input-bordered @error('name') input-error @enderror" placeholder="e.g. Basic Task Workflow" value="{{ old('name') }}" required maxlength="100">
                        @error('name')
                            <span class="label">
                                <span class="label-text-alt text-error">{{ $message }}</span>
                            </span>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div class="form-control">
                        <label class="label" for="workflow-description">
                            <span class="label-text font-medium">Description <span class="text-base-content/50 font-normal">(Optional)</span></span>
                        </label>
                        <textarea name="description" id="workflow-description" class="textarea textarea-bordered" placeholder="Make it short and sweet…" rows="2" maxlength="500">{{ old('description') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Section 3: Create Statuses -->
            <div class="card bg-base-100 shadow mb-6">
                <div class="card-body">
                    <h2 class="card-title text-lg mb-4">
                        <span class="icon-[tabler--list-check] size-5"></span>
                        Customize Statuses
                    </h2>
                    <p class="text-sm text-base-content/60 mb-4">Default statuses are set based on the selected workflow type. You can customize them below.</p>

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
                        <!-- Default: Open status (Always Active) -->
                        <div class="status-row default-status" data-index="0">
                            <div class="grid grid-cols-12 gap-3 items-center p-3 bg-base-200/50 rounded-lg border border-base-300">
                                <input type="hidden" name="statuses[0][id]" value="">
                                <input type="hidden" name="statuses[0][is_active]" value="1">
                                <input type="hidden" name="statuses[0][color]" value="blue" class="status-color-hidden">

                                <!-- Drag Handle (hidden for fixed position) -->
                                <div class="col-span-1 flex justify-center text-base-content/20">
                                    <span class="icon-[tabler--lock] size-5" title="Fixed position"></span>
                                </div>

                                <!-- Status Name -->
                                <div class="col-span-3">
                                    <input type="text" name="statuses[0][name]" class="input input-bordered input-sm w-full status-name" placeholder="Status name" value="{{ old('statuses.0.name', 'Open') }}" required maxlength="40">
                                </div>

                                <!-- Color Picker -->
                                <div class="col-span-1 flex justify-center">
                                    <div class="relative">
                                        <button type="button" class="btn btn-sm color-picker-btn" style="background-color: {{ $colors['blue']['bg'] }}; color: {{ $colors['blue']['text'] }};">
                                            <span class="icon-[tabler--palette] size-4"></span>
                                        </button>
                                        <div class="color-picker-dropdown hidden absolute top-full mt-1 right-0 bg-base-100 rounded-lg shadow-lg p-3 w-64 z-50 border border-base-300">
                                            <div class="grid grid-cols-6 gap-2">
                                                @foreach($colors as $key => $color)
                                                <label class="cursor-pointer">
                                                    <input type="radio" name="statuses[0][color]" value="{{ $key }}" class="hidden peer status-color" {{ $key === 'blue' ? 'checked' : '' }}>
                                                    <span class="block w-8 h-8 rounded-full peer-checked:ring-2 peer-checked:ring-offset-2 peer-checked:ring-primary transition-all hover:scale-110" style="background-color: {{ $color['bg'] }}" title="{{ $color['label'] }}"></span>
                                                </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Responsibility -->
                                <div class="col-span-2">
                                    <select name="statuses[0][responsibility]" class="select select-bordered select-sm w-full">
                                        <option value="creator" {{ old('statuses.0.responsibility', 'creator') === 'creator' ? 'selected' : '' }}>Creator</option>
                                        <option value="assignee" {{ old('statuses.0.responsibility') === 'assignee' ? 'selected' : '' }}>Assignee</option>
                                    </select>
                                </div>

                                <!-- Active Toggle (Readonly - Always Active) -->
                                <div class="col-span-2 flex justify-center">
                                    <div class="flex items-center gap-2">
                                        <input type="checkbox" class="toggle toggle-sm toggle-success" checked disabled>
                                        <span class="text-sm text-base-content/50">Always Active</span>
                                    </div>
                                </div>

                                <!-- Preview -->
                                <div class="col-span-2">
                                    <span class="status-preview px-2.5 py-1 rounded text-xs font-medium" style="background-color: {{ $colors['blue']['bg'] }}; color: {{ $colors['blue']['text'] }};">Open</span>
                                </div>

                                <!-- Action (no action for default) -->
                                <div class="col-span-1 flex justify-center text-base-content/30">
                                    <span>-</span>
                                </div>
                            </div>
                        </div>

                        <!-- Add Status Button (between Open and Closed) -->
                        <div id="add-status-row" class="flex items-center justify-center py-3 border-2 border-dashed border-base-300 rounded-lg hover:border-primary/50 transition-colors">
                            <button type="button" id="add-status-btn" class="btn btn-circle btn-primary btn-sm">
                                <span class="icon-[tabler--plus] size-5"></span>
                            </button>
                            <span class="text-sm text-base-content/60 ml-2">Add new status</span>
                        </div>

                        <!-- Default: Closed status (Always Inactive) -->
                        <div class="status-row default-status" data-index="1" id="closed-status-row">
                            <div class="grid grid-cols-12 gap-3 items-center p-3 bg-base-200/50 rounded-lg border border-base-300">
                                <input type="hidden" name="statuses[1][id]" value="">
                                <input type="hidden" name="statuses[1][is_active]" value="0">
                                <input type="hidden" name="statuses[1][color]" value="slate" class="status-color-hidden">

                                <!-- Drag Handle (hidden for fixed position) -->
                                <div class="col-span-1 flex justify-center text-base-content/20">
                                    <span class="icon-[tabler--lock] size-5" title="Fixed position"></span>
                                </div>

                                <!-- Status Name -->
                                <div class="col-span-3">
                                    <input type="text" name="statuses[1][name]" class="input input-bordered input-sm w-full status-name" placeholder="Status name" value="{{ old('statuses.1.name', 'Closed') }}" required maxlength="40">
                                </div>

                                <!-- Color Picker -->
                                <div class="col-span-1 flex justify-center">
                                    <div class="relative">
                                        <button type="button" class="btn btn-sm color-picker-btn" style="background-color: {{ $colors['slate']['bg'] }}; color: {{ $colors['slate']['text'] }};">
                                            <span class="icon-[tabler--palette] size-4"></span>
                                        </button>
                                        <div class="color-picker-dropdown hidden absolute top-full mt-1 right-0 bg-base-100 rounded-lg shadow-lg p-3 w-64 z-50 border border-base-300">
                                            <div class="grid grid-cols-6 gap-2">
                                                @foreach($colors as $key => $color)
                                                <label class="cursor-pointer">
                                                    <input type="radio" name="statuses[1][color]" value="{{ $key }}" class="hidden peer status-color" {{ $key === 'slate' ? 'checked' : '' }}>
                                                    <span class="block w-8 h-8 rounded-full peer-checked:ring-2 peer-checked:ring-offset-2 peer-checked:ring-primary transition-all hover:scale-110" style="background-color: {{ $color['bg'] }}" title="{{ $color['label'] }}"></span>
                                                </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Responsibility -->
                                <div class="col-span-2">
                                    <select name="statuses[1][responsibility]" class="select select-bordered select-sm w-full">
                                        <option value="creator" {{ old('statuses.1.responsibility') === 'creator' ? 'selected' : '' }}>Creator</option>
                                        <option value="assignee" {{ old('statuses.1.responsibility', 'assignee') === 'assignee' ? 'selected' : '' }}>Assignee</option>
                                    </select>
                                </div>

                                <!-- Active Toggle (Readonly - Always Inactive) -->
                                <div class="col-span-2 flex justify-center">
                                    <div class="flex items-center gap-2">
                                        <input type="checkbox" class="toggle toggle-sm toggle-success" disabled>
                                        <span class="text-sm text-base-content/50">Always Inactive</span>
                                    </div>
                                </div>

                                <!-- Preview -->
                                <div class="col-span-2">
                                    <span class="status-preview px-2.5 py-1 rounded text-xs font-medium" style="background-color: {{ $colors['slate']['bg'] }}; color: {{ $colors['slate']['text'] }};">Closed</span>
                                </div>

                                <!-- Action (no action for default) -->
                                <div class="col-span-1 flex justify-center text-base-content/30">
                                    <span>-</span>
                                </div>
                            </div>
                        </div>
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
            <div class="flex justify gap-3">
                <button type="submit" class="btn btn-primary" id="submit-btn" disabled>
                    Continue
                    <span class="icon-[tabler--arrow-right] size-5"></span>
                </button>
                <a href="{{ route('workflows.index') }}" class="btn btn-ghost">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js" integrity="sha256-ipiJrswvAR4VAx/th+6zWsdeYmVae0iJuiR+6OqHJHQ=" crossorigin="anonymous"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const colors = @json($colors);
    let statusIndex = 2;
    const statusList = document.getElementById('status-list');
    const addBtn = document.getElementById('add-status-btn');
    const submitBtn = document.getElementById('submit-btn');
    const workflowName = document.getElementById('workflow-name');

    const addStatusRow = document.getElementById('add-status-row');

    // Get closed status row dynamically (it changes when workflow type changes)
    function getClosedStatusRow() {
        return document.getElementById('closed-status-row');
    }

    // Define default statuses for each workflow type
    const workflowTypeStatuses = {
        classic: {
            first: { name: 'Open', color: 'blue', responsibility: 'creator' },
            last: { name: 'Closed', color: 'slate', responsibility: 'assignee' },
            middle: []
        },
        product: {
            first: { name: 'Backlog', color: 'purple', responsibility: 'creator' },
            last: { name: 'Closed', color: 'slate', responsibility: 'assignee' },
            middle: [{ name: 'Open', color: 'blue', responsibility: 'assignee', is_active: true, isDefault: true }]
        },
        inbox: {
            first: { name: 'Unassigned', color: 'orange', responsibility: 'creator' },
            last: { name: 'Closed', color: 'slate', responsibility: 'assignee' },
            middle: [{ name: 'Open', color: 'blue', responsibility: 'assignee', is_active: true, isDefault: true }]
        }
    };

    // Handle workflow type changes
    const typeRadios = document.querySelectorAll('.workflow-type-radio');
    typeRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            const selectedType = this.value;
            resetStatusesToType(selectedType);
        });
    });

    // Initialize Sortable - only for non-default statuses
    new Sortable(statusList, {
        animation: 150,
        handle: '.drag-handle',
        ghostClass: 'opacity-50',
        filter: '.default-status, #add-status-row', // Prevent dragging default statuses and add button
        preventOnFilter: false,
        onMove: function(evt) {
            // Prevent moving items before Open or after Closed
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

    // Initialize existing rows (only non-default ones need full event handling)
    document.querySelectorAll('.status-row').forEach(row => {
        attachRowEvents(row, row.classList.contains('default-status'));
    });

    // Get the next available unique color
    function getNextAvailableColor() {
        const usedColors = [];
        // Check both radio buttons and hidden inputs for used colors
        document.querySelectorAll('.status-row .status-color:checked, .status-row .status-color-hidden').forEach(input => {
            usedColors.push(input.value);
        });

        const colorKeys = Object.keys(colors);
        for (const key of colorKeys) {
            if (!usedColors.includes(key)) {
                return key;
            }
        }
        // If all colors are used, return the first one
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
        statusList.insertBefore(row, getClosedStatusRow());
        attachRowEvents(row, false);
        row.querySelector('.status-name').focus();
        statusIndex++;
        updateIndexes(); // Re-index all rows to ensure Closed is always last
        updateRemoveButtons();
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
            // Close all other dropdowns first
            document.querySelectorAll('.color-picker-dropdown').forEach(d => {
                if (d !== colorDropdown) d.classList.add('hidden');
            });
            colorDropdown.classList.toggle('hidden');
        });

        // Color change
        colorInputs.forEach(input => {
            input.addEventListener('change', function() {
                const color = colors[this.value];
                preview.style.backgroundColor = color.bg;
                preview.style.color = color.text;
                colorBtn.style.backgroundColor = color.bg;
                colorBtn.style.color = color.text;
                // Update hidden input for color
                if (colorHidden) {
                    colorHidden.value = this.value;
                }
                // Close dropdown after selection
                colorDropdown.classList.add('hidden');
            });
        });

        // Only attach active toggle and remove button events for non-default statuses
        if (!isDefault) {
            const activeToggle = row.querySelector('.status-active');
            const activeLabel = row.querySelector('.active-label');
            const removeBtn = row.querySelector('.remove-status');

            // Active toggle
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

            // Remove button
            if (removeBtn) {
                removeBtn.addEventListener('click', function() {
                    row.remove();
                    updateIndexes(); // Re-index after removal
                    updateRemoveButtons();
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

    // Reset statuses based on workflow type
    function resetStatusesToType(type) {
        const config = workflowTypeStatuses[type];
        if (!config) return;

        // Remove all non-add-button rows
        const allStatusRows = statusList.querySelectorAll('.status-row');
        allStatusRows.forEach(row => row.remove());

        // Reset status index
        statusIndex = 0;

        // Create first status (Open/Backlog/Unassigned)
        const firstRow = createDefaultStatusRow(0, config.first, true);
        statusList.insertBefore(firstRow, addStatusRow);
        statusIndex++;

        // Create middle statuses if any
        config.middle.forEach(middleStatus => {
            const middleRow = createMiddleStatusRow(statusIndex, middleStatus, middleStatus.isDefault || false);
            statusList.insertBefore(middleRow, addStatusRow);
            statusIndex++;
        });

        // Create last status (Closed)
        const lastRow = createDefaultStatusRow(statusIndex, config.last, false);
        lastRow.id = 'closed-status-row';
        statusList.appendChild(lastRow);
        statusIndex++;

        // Attach events to all rows
        document.querySelectorAll('.status-row').forEach(row => {
            attachRowEvents(row, row.classList.contains('default-status'));
        });

        updateIndexes();
        updateRemoveButtons();
        validateForm();
    }

    // Create a default status row (first or last)
    function createDefaultStatusRow(index, statusConfig, isFirst) {
        const row = document.createElement('div');
        row.className = 'status-row default-status';
        row.dataset.index = index;

        const color = colors[statusConfig.color];
        const activeValue = isFirst ? '1' : '0';
        const activeLabel = isFirst ? 'Always Active' : 'Always Inactive';
        const checkedAttr = isFirst ? 'checked disabled' : 'disabled';

        row.innerHTML = `
            <div class="grid grid-cols-12 gap-3 items-center p-3 bg-base-200/50 rounded-lg border border-base-300">
                <input type="hidden" name="statuses[${index}][id]" value="">
                <input type="hidden" name="statuses[${index}][is_active]" value="${activeValue}">
                <input type="hidden" name="statuses[${index}][color]" value="${statusConfig.color}" class="status-color-hidden">

                <!-- Drag Handle (hidden for fixed position) -->
                <div class="col-span-1 flex justify-center text-base-content/20">
                    <span class="icon-[tabler--lock] size-5" title="Fixed position"></span>
                </div>

                <!-- Status Name -->
                <div class="col-span-3">
                    <input type="text" name="statuses[${index}][name]" class="input input-bordered input-sm w-full status-name" placeholder="Status name" value="${statusConfig.name}" required maxlength="40">
                </div>

                <!-- Color Picker -->
                <div class="col-span-1 flex justify-center">
                    <div class="relative">
                        <button type="button" class="btn btn-sm color-picker-btn" style="background-color: ${color.bg}; color: ${color.text};">
                            <span class="icon-[tabler--palette] size-4"></span>
                        </button>
                        <div class="color-picker-dropdown hidden absolute top-full mt-1 right-0 bg-base-100 rounded-lg shadow-lg p-3 w-64 z-50 border border-base-300">
                            <div class="grid grid-cols-6 gap-2">
                                ${Object.entries(colors).map(([key, c]) => `
                                    <label class="cursor-pointer">
                                        <input type="radio" name="statuses[${index}][color]" value="${key}" class="hidden peer status-color" ${key === statusConfig.color ? 'checked' : ''}>
                                        <span class="block w-8 h-8 rounded-full peer-checked:ring-2 peer-checked:ring-offset-2 peer-checked:ring-primary transition-all hover:scale-110" style="background-color: ${c.bg}" title="${c.label}"></span>
                                    </label>
                                `).join('')}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Responsibility -->
                <div class="col-span-2">
                    <select name="statuses[${index}][responsibility]" class="select select-bordered select-sm w-full">
                        <option value="assignee" ${statusConfig.responsibility !== 'creator' ? 'selected' : ''}>Assignee</option>
                        <option value="creator" ${statusConfig.responsibility === 'creator' ? 'selected' : ''}>Creator</option>
                    </select>
                </div>

                <!-- Active Toggle (Readonly) -->
                <div class="col-span-2 flex justify-center">
                    <div class="flex items-center gap-2">
                        <input type="checkbox" class="toggle toggle-sm toggle-success" ${checkedAttr}>
                        <span class="text-sm text-base-content/50">${activeLabel}</span>
                    </div>
                </div>

                <!-- Preview -->
                <div class="col-span-2">
                    <span class="status-preview px-2.5 py-1 rounded text-xs font-medium" style="background-color: ${color.bg}; color: ${color.text};">${statusConfig.name}</span>
                </div>

                <!-- Action (no action for default) -->
                <div class="col-span-1 flex justify-center text-base-content/30">
                    <span>-</span>
                </div>
            </div>
        `;

        return row;
    }

    // Create a middle status row (editable or default)
    function createMiddleStatusRow(index, statusConfig, isDefault = false) {
        const row = document.createElement('div');
        row.className = isDefault ? 'status-row default-status' : 'status-row';
        row.dataset.index = index;

        const color = colors[statusConfig.color];

        if (isDefault) {
            // Default middle status (like Open in Product/Inbox) - no delete, no drag, always active
            row.innerHTML = `
                <div class="grid grid-cols-12 gap-3 items-center p-3 bg-base-200/50 rounded-lg border border-base-300">
                    <input type="hidden" name="statuses[${index}][id]" value="">
                    <input type="hidden" name="statuses[${index}][is_active]" value="1">
                    <input type="hidden" name="statuses[${index}][color]" value="${statusConfig.color}" class="status-color-hidden">

                    <!-- Drag Handle (hidden for fixed position) -->
                    <div class="col-span-1 flex justify-center text-base-content/20">
                        <span class="icon-[tabler--lock] size-5" title="Fixed position"></span>
                    </div>

                    <!-- Status Name -->
                    <div class="col-span-3">
                        <input type="text" name="statuses[${index}][name]" class="input input-bordered input-sm w-full status-name" placeholder="Status name" value="${statusConfig.name}" required maxlength="40">
                    </div>

                    <!-- Color Picker -->
                    <div class="col-span-1 flex justify-center">
                        <div class="relative">
                            <button type="button" class="btn btn-sm color-picker-btn" style="background-color: ${color.bg}; color: ${color.text};">
                                <span class="icon-[tabler--palette] size-4"></span>
                            </button>
                            <div class="color-picker-dropdown hidden absolute top-full mt-1 right-0 bg-base-100 rounded-lg shadow-lg p-3 w-64 z-50 border border-base-300">
                                <div class="grid grid-cols-6 gap-2">
                                    ${Object.entries(colors).map(([key, c]) => `
                                        <label class="cursor-pointer">
                                            <input type="radio" name="statuses[${index}][color]" value="${key}" class="hidden peer status-color" ${key === statusConfig.color ? 'checked' : ''}>
                                            <span class="block w-8 h-8 rounded-full peer-checked:ring-2 peer-checked:ring-offset-2 peer-checked:ring-primary transition-all hover:scale-110" style="background-color: ${c.bg}" title="${c.label}"></span>
                                        </label>
                                    `).join('')}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Responsibility -->
                    <div class="col-span-2">
                        <select name="statuses[${index}][responsibility]" class="select select-bordered select-sm w-full">
                            <option value="assignee" ${statusConfig.responsibility !== 'creator' ? 'selected' : ''}>Assignee</option>
                            <option value="creator" ${statusConfig.responsibility === 'creator' ? 'selected' : ''}>Creator</option>
                        </select>
                    </div>

                    <!-- Active Toggle (Readonly - Always Active) -->
                    <div class="col-span-2 flex justify-center">
                        <div class="flex items-center gap-2">
                            <input type="checkbox" class="toggle toggle-sm toggle-success" checked disabled>
                            <span class="text-sm text-base-content/50">Always Active</span>
                        </div>
                    </div>

                    <!-- Preview -->
                    <div class="col-span-2">
                        <span class="status-preview px-2.5 py-1 rounded text-xs font-medium" style="background-color: ${color.bg}; color: ${color.text};">${statusConfig.name}</span>
                    </div>

                    <!-- Action (no action for default) -->
                    <div class="col-span-1 flex justify-center text-base-content/30">
                        <span>-</span>
                    </div>
                </div>
            `;
        } else {
            // Custom middle status - can be deleted and dragged
            row.innerHTML = `
                <div class="grid grid-cols-12 gap-3 items-center p-3 bg-base-200/50 rounded-lg border border-base-300">
                    <input type="hidden" name="statuses[${index}][id]" value="">
                    <input type="hidden" name="statuses[${index}][color]" value="${statusConfig.color}" class="status-color-hidden">

                    <!-- Drag Handle -->
                    <div class="col-span-1 flex justify-center">
                        <div class="cursor-grab active:cursor-grabbing text-base-content/30 hover:text-base-content/60 drag-handle">
                            <span class="icon-[tabler--grip-vertical] size-5"></span>
                        </div>
                    </div>

                    <!-- Status Name -->
                    <div class="col-span-3">
                        <input type="text" name="statuses[${index}][name]" class="input input-bordered input-sm w-full status-name" placeholder="Status name" value="${statusConfig.name}" required maxlength="40">
                    </div>

                    <!-- Color Picker -->
                    <div class="col-span-1 flex justify-center">
                        <div class="relative">
                            <button type="button" class="btn btn-sm color-picker-btn" style="background-color: ${color.bg}; color: ${color.text};">
                                <span class="icon-[tabler--palette] size-4"></span>
                            </button>
                            <div class="color-picker-dropdown hidden absolute top-full mt-1 right-0 bg-base-100 rounded-lg shadow-lg p-3 w-64 z-50 border border-base-300">
                                <div class="grid grid-cols-6 gap-2">
                                    ${Object.entries(colors).map(([key, c]) => `
                                        <label class="cursor-pointer">
                                            <input type="radio" name="statuses[${index}][color]" value="${key}" class="hidden peer status-color" ${key === statusConfig.color ? 'checked' : ''}>
                                            <span class="block w-8 h-8 rounded-full peer-checked:ring-2 peer-checked:ring-offset-2 peer-checked:ring-primary transition-all hover:scale-110" style="background-color: ${c.bg}" title="${c.label}"></span>
                                        </label>
                                    `).join('')}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Responsibility -->
                    <div class="col-span-2">
                        <select name="statuses[${index}][responsibility]" class="select select-bordered select-sm w-full">
                            <option value="assignee" ${statusConfig.responsibility !== 'creator' ? 'selected' : ''}>Assignee</option>
                            <option value="creator" ${statusConfig.responsibility === 'creator' ? 'selected' : ''}>Creator</option>
                        </select>
                    </div>

                    <!-- Active Toggle -->
                    <div class="col-span-2 flex justify-center">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="hidden" name="statuses[${index}][is_active]" value="0">
                            <input type="checkbox" name="statuses[${index}][is_active]" value="1" class="toggle toggle-sm toggle-success status-active" ${statusConfig.is_active ? 'checked' : ''}>
                            <span class="text-sm active-label">${statusConfig.is_active ? 'Active' : 'Inactive'}</span>
                        </label>
                    </div>

                    <!-- Preview -->
                    <div class="col-span-2">
                        <span class="status-preview px-2.5 py-1 rounded text-xs font-medium" style="background-color: ${color.bg}; color: ${color.text};">${statusConfig.name}</span>
                    </div>

                    <!-- Delete -->
                    <div class="col-span-1 flex justify-center">
                        <button type="button" class="btn btn-ghost btn-sm btn-square text-error remove-status" title="Remove">
                            <span class="icon-[tabler--trash] size-4"></span>
                        </button>
                    </div>
                </div>
            `;
        }

        return row;
    }

    function updateRemoveButtons() {
        const rows = statusList.querySelectorAll('.status-row:not(.default-status)');
        rows.forEach(row => {
            const btn = row.querySelector('.remove-status');
            if (btn) {
                // Always allow removal of custom statuses (we always have Open and Closed as defaults)
                btn.disabled = false;
                btn.classList.remove('opacity-50');
            }
        });
    }

    function updateIndexes() {
        const rows = statusList.querySelectorAll('.status-row');
        rows.forEach((row, index) => {
            row.dataset.index = index;
            // Update all input names
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

        // Open status is always active, so we always have at least one active status
        const isValid = name && rows.length > 0 && allNamesValid;
        submitBtn.disabled = !isValid;
    }

    // Initial setup
    updateIndexes(); // Ensure proper ordering on load
    validateForm();
    updateRemoveButtons();
});
</script>
@endsection
