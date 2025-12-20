@extends('layouts.app')

@section('content')
<div class="min-h-screen p-4 md:p-6">
    <div class="max-w-3xl mx-auto">
        <!-- Workspace Header -->
        <div class="flex items-center gap-4 mb-6">
            <a href="{{ route('standups.index', $workspace) }}" class="btn btn-ghost btn-sm">
                <span class="icon-[tabler--arrow-left] size-5"></span>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-base-content">{{ $workspace->name }}</h1>
                <p class="text-base-content/60">Standup Settings</p>
            </div>
        </div>

        <!-- Tabs -->
        @include('standup::partials.tabs', ['activeTab' => 'settings'])

        @if(session('success'))
            <div class="alert alert-success mb-6">
                <span class="icon-[tabler--check] size-5"></span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-error mb-6">
                <span class="icon-[tabler--alert-circle] size-5"></span>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Template Settings -->
        <div class="card bg-base-100 shadow mb-6">
            <div class="card-body">
                <h2 class="card-title text-lg">
                    <span class="icon-[tabler--template] size-5"></span>
                    Template Settings
                </h2>

                <form action="{{ route('standups.template.update', $workspace) }}" method="POST" class="mt-4 space-y-4">
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

                    <div class="flex justify-end pt-4 border-t border-base-200">
                        <button type="submit" class="btn btn-primary">
                            <span class="icon-[tabler--check] size-5"></span>
                            Save Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Questions -->
        <div class="card bg-base-100 shadow mb-6">
            <div class="card-body">
                <h2 class="card-title text-lg">
                    <span class="icon-[tabler--list-check] size-5"></span>
                    Template Questions
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
                    <form action="{{ route('standups.template.questions.add', $workspace) }}" method="POST" class="space-y-4">
                        @csrf
                        <div class="form-control">
                            <input type="text" name="question" placeholder="Enter your custom question..."
                                   class="input input-bordered" required />
                        </div>
                        <div class="form-control">
                            <label class="label cursor-pointer justify-start gap-4">
                                <input type="checkbox" name="required" value="1" class="checkbox checkbox-sm" />
                                <span class="label-text">Required question</span>
                            </label>
                        </div>
                        <button type="submit" class="btn btn-secondary btn-sm">
                            <span class="icon-[tabler--plus] size-4"></span>
                            Add Question
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Reminder Settings -->
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <h2 class="card-title text-lg">
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

                    <div class="flex justify-end pt-4 border-t border-base-200">
                        <button type="submit" class="btn btn-primary">
                            <span class="icon-[tabler--check] size-5"></span>
                            Save Reminder Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
