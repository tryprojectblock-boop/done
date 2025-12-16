@extends('layouts.app')

@section('content')
<div class="p-4 md:p-6">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center gap-2 text-sm text-base-content/60 mb-2">
                <a href="{{ route('dashboard') }}" class="hover:text-primary">Dashboard</a>
                <span class="icon-[tabler--chevron-right] size-4"></span>
                <a href="{{ route('workspace.index') }}" class="hover:text-primary">Workspaces</a>
                <span class="icon-[tabler--chevron-right] size-4"></span>
                <a href="{{ route('workspace.show', $workspace) }}" class="hover:text-primary">{{ $workspace->name }}</a>
                <span class="icon-[tabler--chevron-right] size-4"></span>
                <span>Settings</span>
            </div>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center text-white" style="background-color: {{ $workspace->color ?? $workspace->type->themeColor() }}">
                        <span class="icon-[tabler--settings] size-6"></span>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-base-content">Workspace Settings</h1>
                        <p class="text-sm text-base-content/60">{{ $workspace->name }}</p>
                    </div>
                </div>
                <a href="{{ route('workspace.show', $workspace) }}" class="btn btn-ghost">
                    <span class="icon-[tabler--arrow-left] size-5"></span>
                    Back to Workspace
                </a>
            </div>
        </div>

        <div class="space-y-6">
            @if($workspace->type->value === 'inbox')
                <!-- Inbox Settings Quick Links -->
                <div class="card bg-base-100 shadow">
                    <div class="card-body">
                        <h2 class="card-title text-lg mb-4">
                            <span class="icon-[tabler--mail-cog] size-5"></span>
                            Inbox Configuration
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <a href="{{ route('workspace.inbox.working-hours', $workspace) }}" class="btn btn-outline btn-sm justify-start gap-2">
                                <span class="icon-[tabler--clock-hour-4] size-4"></span>
                                Working Hours
                            </a>
                            <a href="{{ route('workspace.inbox.departments', $workspace) }}" class="btn btn-outline btn-sm justify-start gap-2">
                                <span class="icon-[tabler--building] size-4"></span>
                                Departments
                            </a>
                            <a href="{{ route('workspace.inbox.priorities', $workspace) }}" class="btn btn-outline btn-sm justify-start gap-2">
                                <span class="icon-[tabler--flag] size-4"></span>
                                Priorities
                            </a>
                            <a href="{{ route('workspace.inbox.holidays', $workspace) }}" class="btn btn-outline btn-sm justify-start gap-2">
                                <span class="icon-[tabler--calendar-off] size-4"></span>
                                Holidays
                            </a>
                            <a href="{{ route('workspace.inbox.sla-settings', $workspace) }}" class="btn btn-outline btn-sm justify-start gap-2">
                                <span class="icon-[tabler--clock-check] size-4"></span>
                                SLA Settings
                            </a>
                            <a href="{{ route('workspace.inbox.ticket-rules', $workspace) }}" class="btn btn-outline btn-sm justify-start gap-2">
                                <span class="icon-[tabler--git-branch] size-4"></span>
                                Ticket Rules
                            </a>
                            <a href="{{ route('workspace.inbox.sla-rules', $workspace) }}" class="btn btn-outline btn-sm justify-start gap-2">
                                <span class="icon-[tabler--alert-triangle] size-4"></span>
                                SLA Rules
                            </a>
                            <a href="{{ route('workspace.inbox.idle-settings', $workspace) }}" class="btn btn-outline btn-sm justify-start gap-2">
                                <span class="icon-[tabler--clock-pause] size-4"></span>
                                Idle Settings
                            </a>
                            <a href="{{ route('workspace.inbox.email-templates', $workspace) }}" class="btn btn-outline btn-sm justify-start gap-2">
                                <span class="icon-[tabler--mail] size-4"></span>
                                Email Templates
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Inbox Setup Checklist -->
                @include('workspace::partials.inbox.setup-checklist')
            @endif

            <!-- General Settings -->
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <h2 class="card-title text-lg mb-4">
                        <span class="icon-[tabler--adjustments] size-5"></span>
                        General Settings
                    </h2>
                    <form action="{{ route('workspace.update', $workspace) }}" method="POST" class="space-y-4">
                        @csrf
                        @method('PUT')
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Workspace Name</span>
                            </label>
                            <input type="text" name="name" value="{{ $workspace->name }}" class="input input-bordered" required>
                        </div>
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Description</span>
                            </label>
                            <textarea name="description" rows="3" class="textarea textarea-bordered">{{ $workspace->description }}</textarea>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" class="btn btn-primary">
                                <span class="icon-[tabler--check] size-5"></span>
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Danger Zone -->
            <div class="card bg-base-100 shadow border border-error/20">
                <div class="card-body">
                    <h2 class="card-title text-lg text-error mb-4">
                        <span class="icon-[tabler--alert-triangle] size-5"></span>
                        Danger Zone
                    </h2>
                    <div class="flex items-center justify-between p-4 bg-error/5 rounded-lg">
                        <div>
                            <p class="font-medium">Archive Workspace</p>
                            <p class="text-sm text-base-content/60">Archive this workspace. It can be restored later.</p>
                        </div>
                        <form action="{{ route('workspace.archive', $workspace) }}" method="POST" onsubmit="return confirm('Are you sure you want to archive this workspace?')">
                            @csrf
                            <button type="submit" class="btn btn-error btn-outline btn-sm">
                                <span class="icon-[tabler--archive] size-4"></span>
                                Archive
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
