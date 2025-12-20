@extends('layouts.app')

@section('content')
@php
    $activeTab = request('tab', 'general');
    $standupEnabled = $workspace->isStandupEnabled();
@endphp
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

        <!-- Settings Tabs -->
        <div class="tabs tabs-bordered mb-6">
            <a href="{{ route('workspace.settings', ['workspace' => $workspace, 'tab' => 'general']) }}"
               class="tab tab-lg {{ $activeTab === 'general' ? 'tab-active' : '' }}">
                <span class="icon-[tabler--adjustments] size-5 mr-2"></span>
                General
            </a>
            @if($standupEnabled)
            <a href="{{ route('workspace.settings', ['workspace' => $workspace, 'tab' => 'standup']) }}"
               class="tab tab-lg {{ $activeTab === 'standup' ? 'tab-active' : '' }}">
                <span class="icon-[tabler--checkbox] size-5 mr-2"></span>
                Standup
            </a>
            @endif
        </div>

        <!-- Tab Content -->
        @if($activeTab === 'general')
            <!-- General Settings Tab -->
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

                @if(session('success'))
                    <div class="alert alert-success">
                        <span class="icon-[tabler--check] size-5"></span>
                        <span>{{ session('success') }}</span>
                    </div>
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
                                    <span class="label-text font-medium">Workspace Type</span>
                                </label>
                                <div class="flex items-center gap-2">
                                    <div class="input input-bordered bg-base-200 flex items-center gap-2 cursor-not-allowed">
                                        <span class="icon-[{{ $workspace->type->icon() }}] size-5 text-base-content/70"></span>
                                        <span class="text-base-content/70">{{ $workspace->type->label() }}</span>
                                    </div>
                                    <span class="badge badge-ghost badge-sm">Read-only</span>
                                </div>
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
        @elseif($activeTab === 'standup' && $standupEnabled)
            <!-- Standup Settings Tab -->
            @php
                $template = $workspace->standupTemplate;
                if (!$template) {
                    $template = \App\Modules\Standup\Models\StandupTemplate::createDefault($workspace, auth()->user());
                }
                $timezones = timezone_identifiers_list();
            @endphp
            <div class="space-y-6">
                @if(session('standup_success'))
                    <div class="alert alert-success">
                        <span class="icon-[tabler--check] size-5"></span>
                        <span>{{ session('standup_success') }}</span>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-error">
                        <span class="icon-[tabler--alert-circle] size-5"></span>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                <!-- Template Settings -->
                <div class="card bg-base-100 shadow">
                    <div class="card-body">
                        <h2 class="card-title text-lg mb-4">
                            <span class="icon-[tabler--template] size-5"></span>
                            Template Settings
                        </h2>

                        <form action="{{ route('standups.template.update', $workspace) }}" method="POST" class="space-y-4">
                            @csrf
                            @method('PUT')

                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium">Template Name</span>
                                </label>
                                <input type="text" name="name" value="{{ old('name', $template->name) }}"
                                       class="input input-bordered @error('name') input-error @enderror" />
                                @error('name')
                                    <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                                @enderror
                            </div>

                            <div class="form-control">
                                <label class="label cursor-pointer justify-start gap-4">
                                    <input type="checkbox" name="is_active" value="1" class="toggle toggle-primary"
                                           {{ old('is_active', $template->is_active) ? 'checked' : '' }} />
                                    <div>
                                        <span class="label-text font-medium">Active</span>
                                        <p class="text-sm text-base-content/60">Enable daily standups for this workspace</p>
                                    </div>
                                </label>
                            </div>

                            <div class="flex justify-end">
                                <button type="submit" class="btn btn-primary">
                                    <span class="icon-[tabler--check] size-5"></span>
                                    Save Template
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Questions -->
                <div class="card bg-base-100 shadow">
                    <div class="card-body">
                        <h2 class="card-title text-lg mb-4">
                            <span class="icon-[tabler--list-check] size-5"></span>
                            Standup Questions
                        </h2>
                        <p class="text-base-content/60 text-sm mb-4">These questions are asked during standup submission.</p>

                        <div class="space-y-3">
                            @foreach($template->getOrderedQuestions() as $question)
                                <div class="flex items-center justify-between p-4 bg-base-200 rounded-lg">
                                    <div class="flex items-center gap-3">
                                        <span class="badge badge-ghost">{{ $question['order'] }}</span>
                                        <div>
                                            <p class="font-medium">{{ $question['question'] }}</p>
                                            <div class="flex items-center gap-2 mt-1">
                                                <span class="badge badge-sm {{ $question['type'] === 'custom' ? 'badge-secondary' : 'badge-ghost' }}">
                                                    {{ ucfirst($question['type']) }}
                                                </span>
                                                @if($question['required'])
                                                    <span class="badge badge-sm badge-warning">Required</span>
                                                @endif
                                                @if($question['is_default'] ?? false)
                                                    <span class="badge badge-sm badge-info">Default</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    @if(!($question['is_default'] ?? false))
                                        <form action="{{ route('standups.template.questions.remove', ['workspace' => $workspace, 'questionId' => $question['id']]) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-ghost btn-sm text-error">
                                                <span class="icon-[tabler--trash] size-4"></span>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        <!-- Add Custom Question -->
                        <div class="mt-6 pt-4 border-t border-base-200">
                            <h3 class="font-medium mb-3">Add Custom Question</h3>
                            <form action="{{ route('standups.template.questions.add', $workspace) }}" method="POST" class="flex flex-col sm:flex-row gap-3">
                                @csrf
                                <input type="text" name="question" placeholder="Enter your custom question..."
                                       class="input input-bordered flex-1" required />
                                <div class="flex items-center gap-3">
                                    <label class="label cursor-pointer gap-2">
                                        <input type="checkbox" name="required" value="1" class="checkbox checkbox-sm" />
                                        <span class="label-text">Required</span>
                                    </label>
                                    <button type="submit" class="btn btn-secondary">
                                        <span class="icon-[tabler--plus] size-4"></span>
                                        Add Question
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Reminder Settings -->
                <div class="card bg-base-100 shadow">
                    <div class="card-body">
                        <h2 class="card-title text-lg mb-4">
                            <span class="icon-[tabler--bell] size-5"></span>
                            Reminder Settings
                        </h2>
                        <p class="text-base-content/60 text-sm mb-4">Configure automatic reminders for team members.</p>

                        <form action="{{ route('standups.template.reminder', $workspace) }}" method="POST" class="space-y-4">
                            @csrf
                            @method('PUT')

                            <div class="form-control">
                                <label class="label cursor-pointer justify-start gap-4">
                                    <input type="checkbox" name="reminder_enabled" value="1" class="toggle toggle-primary"
                                           {{ old('reminder_enabled', $template->reminder_enabled) ? 'checked' : '' }} />
                                    <div>
                                        <span class="label-text font-medium">Enable Reminders</span>
                                        <p class="text-sm text-base-content/60">Send daily reminders to team members who haven't submitted</p>
                                    </div>
                                </label>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="form-control">
                                    <label class="label">
                                        <span class="label-text font-medium">Reminder Time</span>
                                    </label>
                                    <input type="time" name="reminder_time"
                                           value="{{ old('reminder_time', $template->reminder_time?->format('H:i') ?? '09:00') }}"
                                           class="input input-bordered" />
                                </div>

                                <div class="form-control">
                                    <label class="label">
                                        <span class="label-text font-medium">Timezone</span>
                                    </label>
                                    <select name="reminder_timezone" class="select select-bordered">
                                        @foreach($timezones as $tz)
                                            <option value="{{ $tz }}" {{ old('reminder_timezone', $template->reminder_timezone) === $tz ? 'selected' : '' }}>
                                                {{ $tz }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="flex justify-end">
                                <button type="submit" class="btn btn-primary">
                                    <span class="icon-[tabler--check] size-5"></span>
                                    Save Reminder Settings
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
