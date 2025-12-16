@extends('client-portal.layouts.app')

@section('title', 'Create Ticket')

@section('content')
<!-- Breadcrumb -->
<div class="text-sm breadcrumbs mb-4">
    <ul>
        <li><a href="{{ route('client-portal.dashboard') }}">Dashboard</a></li>
        <li>Create Ticket</li>
    </ul>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Form Card -->
    <div class="lg:col-span-2">
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <h2 class="card-title mb-4">
                    <span class="icon-[tabler--ticket] size-6 text-primary"></span>
                    Create New Support Ticket
                </h2>

                <form action="{{ route('client-portal.tickets.store') }}" method="POST">
                    @csrf

                    <!-- Workspace Selection -->
                    @if($workspaces->count() > 1)
                    <div class="form-control mb-4">
                        <label class="label" for="workspace_id">
                            <span class="label-text">Select Workspace</span>
                        </label>
                        <select
                            id="workspace_id"
                            name="workspace_id"
                            class="select select-bordered w-full @error('workspace_id') select-error @enderror"
                            required
                        >
                            <option value="">Choose a workspace...</option>
                            @foreach($workspaces as $workspace)
                            <option value="{{ $workspace->id }}" {{ old('workspace_id') == $workspace->id ? 'selected' : '' }}>
                                {{ $workspace->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('workspace_id')
                        <label class="label">
                            <span class="label-text-alt text-error">{{ $message }}</span>
                        </label>
                        @enderror
                    </div>
                    @else
                    <input type="hidden" name="workspace_id" value="{{ $workspaces->first()?->id }}">
                    @endif

                    <!-- Subject -->
                    <div class="form-control mb-4">
                        <label class="label" for="title">
                            <span class="label-text">Subject <span class="text-error">*</span></span>
                        </label>
                        <input
                            type="text"
                            id="title"
                            name="title"
                            value="{{ old('title') }}"
                            class="input input-bordered w-full @error('title') input-error @enderror"
                            placeholder="Brief description of your issue"
                            required
                        />
                        @error('title')
                        <label class="label">
                            <span class="label-text-alt text-error">{{ $message }}</span>
                        </label>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div class="form-control mb-4">
                        <label class="label" for="description">
                            <span class="label-text">Description <span class="text-error">*</span></span>
                        </label>
                        <textarea
                            id="description"
                            name="description"
                            rows="6"
                            class="textarea textarea-bordered w-full @error('description') textarea-error @enderror"
                            placeholder="Please provide as much detail as possible about your issue..."
                            required
                        >{{ old('description') }}</textarea>
                        @error('description')
                        <label class="label">
                            <span class="label-text-alt text-error">{{ $message }}</span>
                        </label>
                        @enderror
                    </div>

                    <!-- Department & Priority Row -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <!-- Department -->
                        <div class="form-control">
                            <label class="label" for="department_id">
                                <span class="label-text">Department</span>
                                <span class="label-text-alt text-base-content/50">Optional</span>
                            </label>
                            <select
                                id="department_id"
                                name="department_id"
                                class="select select-bordered w-full @error('department_id') select-error @enderror"
                            >
                                <option value="">Select department...</option>
                                @foreach($workspaces as $workspace)
                                    @if($workspace->departments && $workspace->departments->count() > 0)
                                        @if($workspaces->count() > 1)
                                        <optgroup label="{{ $workspace->name }}">
                                        @endif
                                            @foreach($workspace->departments as $department)
                                            <option value="{{ $department->id }}" data-workspace="{{ $workspace->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                                {{ $department->name }}
                                            </option>
                                            @endforeach
                                        @if($workspaces->count() > 1)
                                        </optgroup>
                                        @endif
                                    @endif
                                @endforeach
                            </select>
                            @error('department_id')
                            <label class="label">
                                <span class="label-text-alt text-error">{{ $message }}</span>
                            </label>
                            @enderror
                        </div>

                        <!-- Priority -->
                        <div class="form-control">
                            <label class="label" for="workspace_priority_id">
                                <span class="label-text">Priority</span>
                                <span class="label-text-alt text-base-content/50">Optional</span>
                            </label>
                            <select
                                id="workspace_priority_id"
                                name="workspace_priority_id"
                                class="select select-bordered w-full @error('workspace_priority_id') select-error @enderror"
                            >
                                <option value="">Select priority...</option>
                                @foreach($workspaces as $workspace)
                                    @if($workspace->priorities && $workspace->priorities->count() > 0)
                                        @if($workspaces->count() > 1)
                                        <optgroup label="{{ $workspace->name }}">
                                        @endif
                                            @foreach($workspace->priorities as $priority)
                                            <option value="{{ $priority->id }}" data-workspace="{{ $workspace->id }}" {{ old('workspace_priority_id') == $priority->id ? 'selected' : '' }}>
                                                {{ $priority->name }}
                                            </option>
                                            @endforeach
                                        @if($workspaces->count() > 1)
                                        </optgroup>
                                        @endif
                                    @endif
                                @endforeach
                            </select>
                            @error('workspace_priority_id')
                            <label class="label">
                                <span class="label-text-alt text-error">{{ $message }}</span>
                            </label>
                            @enderror
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="flex justify-end gap-3">
                        <a href="{{ route('client-portal.dashboard') }}" class="btn btn-ghost">Cancel</a>
                        <button type="submit" class="btn btn-primary gap-2">
                            <span class="icon-[tabler--send] size-4"></span>
                            Submit Ticket
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Help Card -->
    <div class="lg:col-span-1">
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <h3 class="font-semibold flex items-center gap-2 mb-4">
                    <span class="icon-[tabler--help-circle] size-5 text-info"></span>
                    Tips for a Great Ticket
                </h3>
                <ul class="space-y-3 text-sm text-base-content/70">
                    <li class="flex gap-2">
                        <span class="icon-[tabler--check] size-4 text-success mt-0.5 flex-shrink-0"></span>
                        <span>Use a clear, descriptive subject line</span>
                    </li>
                    <li class="flex gap-2">
                        <span class="icon-[tabler--check] size-4 text-success mt-0.5 flex-shrink-0"></span>
                        <span>Include specific error messages if applicable</span>
                    </li>
                    <li class="flex gap-2">
                        <span class="icon-[tabler--check] size-4 text-success mt-0.5 flex-shrink-0"></span>
                        <span>Describe the steps to reproduce the issue</span>
                    </li>
                    <li class="flex gap-2">
                        <span class="icon-[tabler--check] size-4 text-success mt-0.5 flex-shrink-0"></span>
                        <span>Mention what you expected to happen</span>
                    </li>
                    <li class="flex gap-2">
                        <span class="icon-[tabler--check] size-4 text-success mt-0.5 flex-shrink-0"></span>
                        <span>Select the appropriate department for faster routing</span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Support Info -->
        <div class="card bg-primary/5 border border-primary/20 mt-4">
            <div class="card-body">
                <h3 class="font-semibold flex items-center gap-2 mb-2">
                    <span class="icon-[tabler--clock] size-5 text-primary"></span>
                    Response Time
                </h3>
                <p class="text-sm text-base-content/70">
                    Our support team typically responds within 24 hours during business days. For urgent issues, please indicate this in your ticket.
                </p>
            </div>
        </div>
    </div>
</div>

@if($workspaces->count() > 1)
<script>
    // Filter departments and priorities based on selected workspace
    document.getElementById('workspace_id')?.addEventListener('change', function() {
        const workspaceId = this.value;

        // Filter departments
        const departmentSelect = document.getElementById('department_id');
        Array.from(departmentSelect.options).forEach(option => {
            if (option.dataset.workspace) {
                option.style.display = option.dataset.workspace === workspaceId ? '' : 'none';
            }
        });
        departmentSelect.value = '';

        // Filter priorities
        const prioritySelect = document.getElementById('workspace_priority_id');
        Array.from(prioritySelect.options).forEach(option => {
            if (option.dataset.workspace) {
                option.style.display = option.dataset.workspace === workspaceId ? '' : 'none';
            }
        });
        prioritySelect.value = '';
    });
</script>
@endif
@endsection
