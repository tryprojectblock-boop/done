@extends('layouts.app')

@section('content')
<div class="p-4 md:p-6">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center gap-2 text-sm text-base-content/60 mb-2">
                <a href="{{ route('dashboard') }}" class="hover:text-primary">Dashboard</a>
                <span class="icon-[tabler--chevron-right] size-4"></span>
                <span>Workflows</span>
            </div>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-base-content">Workflows</h1>
                    <p class="text-base-content/60">Manage task workflows for your organization</p>
                </div>
                @if($canManage)
                <a href="{{ route('workflows.create') }}" class="btn btn-primary">
                    <span class="icon-[tabler--plus] size-5"></span>
                    Add Workflow
                </a>
                @endif
            </div>
        </div>

        <!-- Success/Error Messages -->
        <div class="mb-4">
            @include('partials.alerts')
        </div>

        <!-- Workflows Grid -->
        @if($workflows->isEmpty() && $archivedWorkflows->isEmpty())
            <div class="card bg-base-100 shadow">
                <div class="card-body text-center py-12">
                    <div class="flex justify-center mb-4">
                        <span class="icon-[tabler--git-branch] size-16 text-base-content/20"></span>
                    </div>
                    <h3 class="text-lg font-semibold text-base-content">No Workflows Yet</h3>
                    <p class="text-base-content/60 mb-4">Create workflows to define how tasks move through your process.</p>
                    @if($canManage)
                    <div>
                        <a href="{{ route('workflows.create') }}" class="btn btn-primary">
                            <span class="icon-[tabler--plus] size-5"></span>
                            Create Workflow
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        @else
            <!-- Active Workflows -->
            @if($workflows->isNotEmpty())
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
                @foreach($workflows as $workflow)
                <div class="card bg-base-100 shadow hover:shadow-lg transition-shadow">
                    <div class="card-body">
                        <!-- Workflow Header -->
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex-1 min-w-0">
                                <h3 class="font-semibold text-lg text-base-content truncate">{{ $workflow->name }}</h3>
                                @if($workflow->description)
                                    <p class="text-sm text-base-content/60 line-clamp-2 mt-1">{{ $workflow->description }}</p>
                                @endif
                            </div>
                            @if($workflow->isBuiltIn())
                                <span class="badge badge-primary badge-sm">Built-in</span>
                            @endif
                        </div>

                        <!-- Status Tags -->
                        <div class="flex flex-wrap gap-1.5 mb-4">
                            @foreach($workflow->statuses->take(6) as $status)
                                <span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-medium" style="background-color: {{ $status->background_color }}; color: {{ $status->text_color }}">
                                    {{ $status->name }}
                                    @if(!$status->is_active)
                                        <span class="ml-1 opacity-60">(Inactive)</span>
                                    @endif
                                </span>
                            @endforeach
                            @if($workflow->statuses->count() > 6)
                                <div class="relative group">
                                    <span class="badge badge-ghost badge-sm cursor-pointer">+{{ $workflow->statuses->count() - 6 }} more</span>
                                    <div class="absolute top-full left-0 mt-1 hidden group-hover:block z-50 bg-base-100 rounded-lg shadow-lg border border-base-300 p-2 w-48">
                                        <div class="flex flex-wrap gap-1.5">
                                            @foreach($workflow->statuses->skip(6) as $status)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium" style="background-color: {{ $status->background_color }}; color: {{ $status->text_color }}">
                                                    {{ $status->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Stats -->
                        <div class="flex items-center gap-4 text-sm text-base-content/60 mb-4">
                            <span class="flex items-center gap-1">
                                <span class="icon-[tabler--circle-check] size-4 text-success"></span>
                                {{ $workflow->statuses->where('is_active', true)->count() }} Active
                            </span>
                            <span class="flex items-center gap-1">
                                <span class="icon-[tabler--circle-x] size-4 text-base-content/40"></span>
                                {{ $workflow->statuses->where('is_active', false)->count() }} Inactive
                            </span>
                        </div>

                        <!-- Actions -->
                        <div class="card-actions justify-end pt-2 border-t border-base-200">
                            <div class="dropdown dropdown-end">
                                <label tabindex="0" class="btn btn-ghost btn-sm">
                                    <span class="icon-[tabler--dots-vertical] size-4"></span>
                                </label>
                                <ul tabindex="0" class="dropdown-menu dropdown-open:opacity-100 hidden min-w-40 z-50">
                                    @if($canManage)
                                    <li>
                                        <a href="{{ route('workflows.edit', $workflow) }}" class="dropdown-item">
                                            <span class="icon-[tabler--edit] size-4"></span>
                                            Edit
                                        </a>
                                    </li>
                                    @endif
                                    <li>
                                        <form action="{{ route('workflows.duplicate', $workflow) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="dropdown-item w-full text-left">
                                                <span class="icon-[tabler--copy] size-4"></span>
                                                Duplicate
                                            </button>
                                        </form>
                                    </li>
                                    @if($canManage)
                                    <li class="border-t border-base-200 mt-1 pt-1">
                                        <form action="{{ route('workflows.archive', $workflow) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="dropdown-item w-full text-left text-warning">
                                                <span class="icon-[tabler--archive] size-4"></span>
                                                Archive
                                            </button>
                                        </form>
                                    </li>
                                    @if(!$workflow->isBuiltIn())
                                    <li>
                                        <button type="button" class="dropdown-item w-full text-left text-error"
                                            data-delete
                                            data-delete-action="{{ route('workflows.destroy', $workflow) }}"
                                            data-delete-title="Delete Workflow"
                                            data-delete-name="{{ $workflow->name }}"
                                            data-delete-warning="This action cannot be undone. All statuses in this workflow will be deleted.">
                                            <span class="icon-[tabler--trash] size-4"></span>
                                            Delete
                                        </button>
                                    </li>
                                    @endif
                                    @endif
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif

            <!-- Archived Workflows -->
            @if($archivedWorkflows->isNotEmpty())
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <h3 class="text-base font-medium flex items-center gap-2 mb-4">
                        <span class="icon-[tabler--archive] size-5 text-base-content/60"></span>
                        Archived Workflows ({{ $archivedWorkflows->count() }})
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($archivedWorkflows as $workflow)
                        <div class="card bg-base-200/50 border border-base-300">
                            <div class="card-body py-4">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <h4 class="font-medium text-base-content/70">{{ $workflow->name }}</h4>
                                        <p class="text-sm text-base-content/50">{{ $workflow->statuses->count() }} statuses</p>
                                    </div>
                                    @if($canManage)
                                    <div class="flex gap-1">
                                        <form action="{{ route('workflows.restore', $workflow) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-ghost btn-xs" title="Restore">
                                                <span class="icon-[tabler--archive-off] size-4"></span>
                                            </button>
                                        </form>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        @endif

        <!-- Info Box -->
        <div class="mt-6 p-4 bg-info/10 border border-info/20 rounded-lg">
            <h4 class="font-semibold text-info mb-2 flex items-center gap-2">
                <span class="icon-[tabler--info-circle] size-5"></span>
                About Workflows
            </h4>
            <p class="text-sm text-base-content/70">
                A workflow is a set of statuses that define how tasks move from start to finish.
                Create workflows to customize your task management process and track progress effectively.
                Each workflow can be assigned to workspaces to manage tasks.
            </p>
        </div>
    </div>
</div>

@endsection
