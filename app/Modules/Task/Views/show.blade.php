@extends('layouts.app')

@php
    $user = auth()->user();
@endphp

@section('content')
<div class="p-4 md:p-6">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center gap-2 text-sm text-base-content/60 mb-2">
                <a href="{{ route('dashboard') }}" class="hover:text-primary">Dashboard</a>
                <span class="icon-[tabler--chevron-right] size-4"></span>
                <a href="{{ route('tasks.index') }}" class="hover:text-primary">Tasks</a>
                <span class="icon-[tabler--chevron-right] size-4"></span>
                <span>{{ $task->task_number }}</span>
            </div>

            <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
                <div class="flex-1">
                    <h1 class="text-2xl font-bold text-base-content {{ $task->isClosed() ? 'line-through opacity-60' : '' }}">
                        {{ $task->title }}
                    </h1>
                    <p class="text-base-content/60 mt-1">
                        Created by {{ $task->creator->name }} on {{ $task->created_at->format('M d, Y') }}
                    </p>
                </div>

                <div class="flex flex-wrap gap-2">
                    <!-- Watch Button -->
                    <form action="{{ route('tasks.watch.toggle', $task) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="btn btn-ghost btn-sm">
                            @if($task->isWatcher($user))
                                <span class="icon-[tabler--eye-off] size-4"></span>
                                Unwatch
                            @else
                                <span class="icon-[tabler--eye] size-4"></span>
                                Watch
                            @endif
                        </button>
                    </form>

                    @if($task->canEdit($user))
                        <a href="{{ route('tasks.edit', $task) }}" class="btn btn-ghost btn-sm">
                            <span class="icon-[tabler--edit] size-4"></span>
                            Edit
                        </a>
                    @endif

                    @if($task->canClose($user))
                        @if($task->isClosed())
                            <form action="{{ route('tasks.reopen', $task) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="btn btn-success btn-sm">
                                    <span class="icon-[tabler--refresh] size-4"></span>
                                    Reopen
                                </button>
                            </form>
                        @else
                            <form action="{{ route('tasks.close', $task) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="btn btn-warning btn-sm">
                                    <span class="icon-[tabler--check] size-4"></span>
                                    Close Task
                                </button>
                            </form>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        <!-- Success/Error Messages -->
        @if(session('success'))
            <div class="alert alert-success mb-4">
                <span class="icon-[tabler--check] size-5"></span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-error mb-4">
                <span class="icon-[tabler--x] size-5"></span>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Description -->
                <div class="card bg-base-100 shadow">
                    <div class="card-body">
                        <h2 class="card-title text-lg">
                            <span class="icon-[tabler--file-description] size-5"></span>
                            Description
                        </h2>
                        @if($task->description)
                            <div class="prose prose-sm max-w-none">
                                {!! $task->description !!}
                            </div>
                        @else
                            <p class="text-base-content/60 italic">No description provided.</p>
                        @endif
                    </div>
                </div>

                <!-- Subtasks -->
                @if($task->subtasks->isNotEmpty())
                <div class="card bg-base-100 shadow">
                    <div class="card-body">
                        <h2 class="card-title text-lg">
                            <span class="icon-[tabler--subtask] size-5"></span>
                            Subtasks
                            <span class="badge badge-sm">{{ $task->subtasks->count() }}</span>
                        </h2>
                        <div class="space-y-2">
                            @foreach($task->subtasks as $subtask)
                                <a href="{{ route('tasks.show', $subtask) }}"
                                   class="flex items-center gap-3 p-2 rounded-lg hover:bg-base-200 transition-colors {{ $subtask->isClosed() ? 'opacity-60' : '' }}">
                                    @if($subtask->types && count($subtask->types) > 0)
                                        <span class="icon-[{{ $subtask->types[0]->icon() }}] size-4 text-base-content/70"></span>
                                    @else
                                        <span class="icon-[tabler--checkbox] size-4 text-base-content/70"></span>
                                    @endif
                                    <span class="font-mono text-sm text-base-content/60">{{ $subtask->task_number }}</span>
                                    <span class="{{ $subtask->isClosed() ? 'line-through' : '' }}">{{ $subtask->title }}</span>
                                    @if($subtask->status)
                                        <span class="badge badge-sm ml-auto" style="background-color: {{ $subtask->status->background_color }}20; color: {{ $subtask->status->background_color }}">
                                            {{ $subtask->status->name }}
                                        </span>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                        <div class="mt-3">
                            <a href="{{ route('tasks.create', ['parent_task_id' => $task->id]) }}" class="btn btn-sm btn-ghost">
                                <span class="icon-[tabler--plus] size-4"></span>
                                Add Subtask
                            </a>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Attachments -->
                <div class="card bg-base-100 shadow">
                    <div class="card-body">
                        <h2 class="card-title text-lg">
                            <span class="icon-[tabler--paperclip] size-5"></span>
                            Attachments
                            <span class="badge badge-sm">{{ $task->attachments->count() }}</span>
                        </h2>

                        @if($task->attachments->isNotEmpty())
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                @foreach($task->attachments as $attachment)
                                    <div class="flex items-center gap-3 p-3 bg-base-200 rounded-lg group">
                                        <span class="icon-[{{ $attachment->getIconClass() }}] size-8 text-base-content/60"></span>
                                        <div class="flex-1 min-w-0">
                                            <p class="font-medium truncate">{{ $attachment->original_name }}</p>
                                            <p class="text-xs text-base-content/60">{{ $attachment->getFormattedSize() }}</p>
                                        </div>
                                        <div class="flex gap-1">
                                            <a href="{{ route('tasks.attachments.download', $attachment) }}"
                                               class="btn btn-ghost btn-xs">
                                                <span class="icon-[tabler--download] size-4"></span>
                                            </a>
                                            @if($attachment->uploaded_by === $user->id || $user->isAdminOrHigher())
                                                <form action="{{ route('tasks.attachments.destroy', $attachment) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-ghost btn-xs text-error"
                                                            onclick="return confirm('Delete this attachment?')">
                                                        <span class="icon-[tabler--trash] size-4"></span>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <!-- Upload Form -->
                        <form action="{{ route('tasks.attachments.store', $task) }}" method="POST" enctype="multipart/form-data" class="mt-4">
                            @csrf
                            <div class="flex items-center gap-2">
                                <input type="file" name="files[]" multiple class="file-input file-input-bordered file-input-sm flex-1">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <span class="icon-[tabler--upload] size-4"></span>
                                    Upload
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Comments -->
                <div class="card bg-base-100 shadow">
                    <div class="card-body">
                        <h2 class="card-title text-lg">
                            <span class="icon-[tabler--message-circle] size-5"></span>
                            Comments
                            <span class="badge badge-sm">{{ $task->comments->count() }}</span>
                        </h2>

                        <!-- Add Comment Form -->
                        <form action="{{ route('tasks.comments.store', $task) }}" method="POST" class="mb-4" id="comment-form">
                            @csrf
                            <div class="flex gap-3">
                                <div class="avatar">
                                    <div class="w-10 h-10 rounded-full overflow-hidden">
                                        <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-full h-full object-cover" />
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <x-quill-editor
                                        name="content"
                                        id="comment-editor"
                                        placeholder="Add a comment..."
                                        height="100px"
                                    />
                                    <div class="flex justify-end mt-2">
                                        <button type="submit" class="btn btn-primary btn-sm">
                                            <span class="icon-[tabler--send] size-4"></span>
                                            Comment
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>

                        <!-- Comments List -->
                        <div class="space-y-4">
                            @forelse($task->comments as $comment)
                                @include('task::partials.comment', ['comment' => $comment])
                            @empty
                                <p class="text-base-content/60 text-center py-4">No comments yet. Be the first to comment!</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Activity Log -->
                <div class="card bg-base-100 shadow">
                    <div class="card-body">
                        <h2 class="card-title text-lg">
                            <span class="icon-[tabler--history] size-5"></span>
                            Activity
                        </h2>

                        <div class="relative">
                            <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-base-300"></div>
                            <div class="space-y-4">
                                @forelse($task->activities as $activity)
                                    <div class="flex gap-4 relative">
                                        <div class="w-8 h-8 rounded-full bg-base-200 flex items-center justify-center z-10">
                                            <span class="icon-[{{ $activity->type->icon() }}] size-4 text-base-content/60"></span>
                                        </div>
                                        <div class="flex-1 pb-4">
                                            <p class="text-sm">{{ $activity->getFormattedDescription() }}</p>
                                            <p class="text-xs text-base-content/60">{{ $activity->created_at->diffForHumans() }}</p>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-base-content/60 text-center py-4">No activity recorded yet.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Task Info Card -->
                <div class="card bg-base-100 shadow group">
                    <div class="card-body">
                        <h2 class="card-title text-lg">Details</h2>

                        <div class="divide-y divide-base-200">
                            <!-- Status -->
                            <div class="py-3 first:pt-0">
                                <div class="flex items-center justify-between">
                                    <label class="text-sm font-medium text-base-content/70">Status</label>
                                    @if($task->canChangeStatus($user) && !$task->isClosed())
                                        <button type="button" class="btn btn-soft btn-primary btn-xs btn-circle edit-btn" onclick="toggleEdit('status')" title="Edit status">
                                            <span class="icon-[tabler--pencil] size-3.5"></span>
                                        </button>
                                    @endif
                                </div>
                                <!-- Display Mode -->
                                <div id="status-display" class="mt-2">
                                    @if($task->status)
                                        <span class="badge" style="background-color: {{ $task->status->background_color }}20; color: {{ $task->status->background_color }}">
                                            {{ $task->status->name }}
                                        </span>
                                    @else
                                        <span class="text-base-content/40 text-sm">Not set</span>
                                    @endif
                                </div>
                                <!-- Edit Mode -->
                                @if($task->canChangeStatus($user) && !$task->isClosed())
                                <form id="status-edit" action="{{ route('tasks.update-status', $task) }}" method="POST" class="hidden mt-2">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status_id" class="select select-bordered select-sm w-full">
                                        <option value="">No Status</option>
                                        @foreach($statuses as $status)
                                            <option value="{{ $status->id }}" {{ $task->status_id == $status->id ? 'selected' : '' }}>
                                                {{ $status->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="flex gap-2 mt-2">
                                        <button type="submit" class="btn btn-primary btn-xs">
                                            <span class="icon-[tabler--check] size-3.5"></span>
                                            Save
                                        </button>
                                        <button type="button" class="btn btn-ghost btn-xs" onclick="toggleEdit('status')">Cancel</button>
                                    </div>
                                </form>
                                @endif
                            </div>

                            <!-- Assignee -->
                            <div class="py-3">
                                <div class="flex items-center justify-between">
                                    <label class="text-sm font-medium text-base-content/70">Assignee</label>
                                    @if($task->canEdit($user) && !$task->isClosed())
                                        <button type="button" class="btn btn-soft btn-primary btn-xs btn-circle edit-btn" onclick="toggleEdit('assignee')" title="Edit assignee">
                                            <span class="icon-[tabler--pencil] size-3.5"></span>
                                        </button>
                                    @endif
                                </div>
                                <!-- Display Mode -->
                                <div id="assignee-display" class="mt-2">
                                    @if($task->assignee)
                                        <div class="flex items-center gap-2">
                                            <div class="avatar">
                                                <div class="w-6 rounded-full">
                                                    <img src="{{ $task->assignee->avatar_url }}" alt="{{ $task->assignee->name }}" />
                                                </div>
                                            </div>
                                            <span class="text-sm">{{ $task->assignee->name }}</span>
                                        </div>
                                    @else
                                        <span class="text-base-content/40 text-sm">Unassigned</span>
                                    @endif
                                </div>
                                <!-- Edit Mode -->
                                @if($task->canEdit($user) && !$task->isClosed())
                                <form id="assignee-edit" action="{{ route('tasks.update-assignee', $task) }}" method="POST" class="hidden mt-2">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="assignee_id" id="assignee-input" value="{{ $task->assignee_id }}">

                                    <!-- Search Input -->
                                    <div class="relative">
                                        <input type="text"
                                               id="assignee-search"
                                               class="input input-bordered input-sm w-full pr-8"
                                               placeholder="Search users..."
                                               autocomplete="off"
                                               value="{{ $task->assignee?->name ?? '' }}">
                                        <span class="icon-[tabler--search] size-4 absolute right-3 top-1/2 -translate-y-1/2 text-base-content/40"></span>
                                    </div>

                                    <!-- Dropdown Results -->
                                    <div id="assignee-dropdown" class="absolute z-50 w-full mt-1 bg-base-100 border border-base-300 rounded-lg shadow-lg max-h-48 overflow-y-auto hidden">
                                        <div class="p-1">
                                            <button type="button" class="assignee-option w-full text-left px-3 py-2 text-sm rounded hover:bg-base-200 flex items-center gap-2" data-id="" data-name="Unassigned">
                                                <div class="avatar placeholder">
                                                    <div class="bg-base-300 text-base-content rounded-full w-6 h-6 flex items-center justify-center">
                                                        <span class="icon-[tabler--user-off] size-3"></span>
                                                    </div>
                                                </div>
                                                <span>Unassigned</span>
                                            </button>
                                            @foreach($users as $u)
                                            <button type="button" class="assignee-option w-full text-left px-3 py-2 text-sm rounded hover:bg-base-200 flex items-center gap-2 {{ $task->assignee_id == $u->id ? 'bg-primary/10' : '' }}" data-id="{{ $u->id }}" data-name="{{ $u->name }}">
                                                <div class="avatar placeholder">
                                                    <div class="bg-primary text-primary-content rounded-full w-6 h-6 flex items-center justify-center">
                                                        <span class="text-xs">{{ substr($u->name, 0, 1) }}</span>
                                                    </div>
                                                </div>
                                                <span>{{ $u->name }}</span>
                                                @if($task->assignee_id == $u->id)
                                                <span class="icon-[tabler--check] size-4 text-primary ml-auto"></span>
                                                @endif
                                            </button>
                                            @endforeach
                                        </div>
                                    </div>

                                    <div class="flex gap-2 mt-2">
                                        <button type="submit" class="btn btn-primary btn-xs">
                                            <span class="icon-[tabler--check] size-3.5"></span>
                                            Save
                                        </button>
                                        <button type="button" class="btn btn-ghost btn-xs" onclick="toggleEdit('assignee')">Cancel</button>
                                    </div>
                                </form>
                                @endif
                            </div>

                            <!-- Creator -->
                            <div class="py-3">
                                <label class="text-sm font-medium text-base-content/70">Created By</label>
                                <div class="mt-2">
                                    <div class="flex items-center gap-2">
                                        <div class="avatar">
                                            <div class="w-6 rounded-full">
                                                <img src="{{ $task->creator->avatar_url }}" alt="{{ $task->creator->name }}" />
                                            </div>
                                        </div>
                                        <span class="text-sm">{{ $task->creator->name }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Task Type(s) -->
                            <div class="py-3">
                                <div class="flex items-center justify-between">
                                    <label class="text-sm font-medium text-base-content/70">Type</label>
                                    @if($task->canEdit($user) && !$task->isClosed())
                                        <button type="button" class="btn btn-soft btn-primary btn-xs btn-circle edit-btn" onclick="toggleEdit('type')" title="Edit type">
                                            <span class="icon-[tabler--pencil] size-3.5"></span>
                                        </button>
                                    @endif
                                </div>
                                <!-- Display Mode -->
                                <div id="type-display" class="mt-2 flex flex-wrap gap-1">
                                    @if($task->types && count($task->types) > 0)
                                        @foreach($task->types as $taskType)
                                            <span class="badge badge-sm gap-1">
                                                <span class="icon-[{{ $taskType->icon() }}] size-3"></span>
                                                {{ $taskType->label() }}
                                            </span>
                                        @endforeach
                                    @else
                                        <span class="text-base-content/40 text-sm">Not set</span>
                                    @endif
                                </div>
                                <!-- Edit Mode -->
                                @if($task->canEdit($user) && !$task->isClosed())
                                <form id="type-edit" action="{{ route('tasks.update-type', $task) }}" method="POST" class="hidden mt-2">
                                    @csrf
                                    @method('PATCH')
                                    <div class="space-y-2 p-2 bg-base-200/50 rounded-lg">
                                        @foreach(\App\Modules\Task\Enums\TaskType::cases() as $type)
                                            <label class="flex items-center gap-2 cursor-pointer hover:bg-base-200 p-1 rounded">
                                                <input type="checkbox" name="type[]" value="{{ $type->value }}"
                                                    class="checkbox checkbox-sm checkbox-primary"
                                                    {{ $task->types && in_array($type, $task->types) ? 'checked' : '' }}>
                                                <span class="icon-[{{ $type->icon() }}] size-4 text-base-content/70"></span>
                                                <span class="text-sm">{{ $type->label() }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                    <div class="flex gap-2 mt-2">
                                        <button type="submit" class="btn btn-primary btn-xs">
                                            <span class="icon-[tabler--check] size-3.5"></span>
                                            Save
                                        </button>
                                        <button type="button" class="btn btn-ghost btn-xs" onclick="toggleEdit('type')">Cancel</button>
                                    </div>
                                </form>
                                @endif
                            </div>

                            <!-- Priority -->
                            <div class="py-3">
                                <div class="flex items-center justify-between">
                                    <label class="text-sm font-medium text-base-content/70">Priority</label>
                                    @if($task->canEdit($user) && !$task->isClosed())
                                        <button type="button" class="btn btn-soft btn-primary btn-xs btn-circle edit-btn" onclick="toggleEdit('priority')" title="Edit priority">
                                            <span class="icon-[tabler--pencil] size-3.5"></span>
                                        </button>
                                    @endif
                                </div>
                                <!-- Display Mode -->
                                <div id="priority-display" class="mt-2">
                                    @if($task->priority)
                                        <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-md text-sm" style="background-color: {{ $task->priority->color() }}15; color: {{ $task->priority->color() }}">
                                            <span class="icon-[{{ $task->priority->icon() }}] size-4"></span>
                                            {{ $task->priority->label() }}
                                        </span>
                                    @else
                                        <span class="text-base-content/40 text-sm">Not set</span>
                                    @endif
                                </div>
                                <!-- Edit Mode -->
                                @if($task->canEdit($user) && !$task->isClosed())
                                <form id="priority-edit" action="{{ route('tasks.update-priority', $task) }}" method="POST" class="hidden mt-2">
                                    @csrf
                                    @method('PATCH')
                                    <select name="priority" class="select select-bordered select-sm w-full">
                                        <option value="">No Priority</option>
                                        @foreach(\App\Modules\Task\Enums\TaskPriority::cases() as $priority)
                                            <option value="{{ $priority->value }}" {{ $task->priority == $priority ? 'selected' : '' }}>
                                                {{ $priority->label() }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="flex gap-2 mt-2">
                                        <button type="submit" class="btn btn-primary btn-xs">
                                            <span class="icon-[tabler--check] size-3.5"></span>
                                            Save
                                        </button>
                                        <button type="button" class="btn btn-ghost btn-xs" onclick="toggleEdit('priority')">Cancel</button>
                                    </div>
                                </form>
                                @endif
                            </div>

                            <!-- Due Date -->
                            <div class="py-3">
                                <div class="flex items-center justify-between">
                                    <label class="text-sm font-medium text-base-content/70">Due Date</label>
                                    @if($task->canEdit($user) && !$task->isClosed())
                                        <button type="button" class="btn btn-soft btn-primary btn-xs btn-circle edit-btn" onclick="toggleEdit('due-date')" title="Edit due date">
                                            <span class="icon-[tabler--pencil] size-3.5"></span>
                                        </button>
                                    @endif
                                </div>
                                <!-- Display Mode -->
                                <div id="due-date-display" class="mt-2">
                                    @if($task->due_date)
                                        <span class="inline-flex items-center gap-1.5 {{ $task->isOverdue() ? 'text-error font-medium' : 'text-base-content' }}">
                                            <span class="icon-[tabler--calendar] size-4"></span>
                                            {{ $task->due_date->format('M d, Y') }}
                                            @if($task->isOverdue())
                                                <span class="badge badge-error badge-xs">Overdue</span>
                                            @endif
                                        </span>
                                    @else
                                        <span class="text-base-content/40 text-sm">Not set</span>
                                    @endif
                                </div>
                                <!-- Edit Mode with Calendar -->
                                @if($task->canEdit($user) && !$task->isClosed())
                                <div id="due-date-edit" class="hidden mt-2">
                                    <form action="{{ route('tasks.update-due-date', $task) }}" method="POST" id="due-date-form">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="due_date" id="due-date-input" value="{{ $task->due_date?->format('Y-m-d') }}">

                                        <!-- Calendar Widget -->
                                        <div class="bg-base-200/50 rounded-lg p-3">
                                            <!-- Month/Year Header -->
                                            <div class="flex items-center justify-between mb-3">
                                                <button type="button" onclick="changeMonth(-1)" class="btn btn-ghost btn-xs btn-circle">
                                                    <span class="icon-[tabler--chevron-left] size-4"></span>
                                                </button>
                                                <span id="calendar-month-year" class="font-semibold text-sm"></span>
                                                <button type="button" onclick="changeMonth(1)" class="btn btn-ghost btn-xs btn-circle">
                                                    <span class="icon-[tabler--chevron-right] size-4"></span>
                                                </button>
                                            </div>

                                            <!-- Days of Week -->
                                            <div class="grid grid-cols-7 gap-1 mb-2">
                                                <div class="text-center text-xs font-medium text-base-content/50 py-1">Su</div>
                                                <div class="text-center text-xs font-medium text-base-content/50 py-1">Mo</div>
                                                <div class="text-center text-xs font-medium text-base-content/50 py-1">Tu</div>
                                                <div class="text-center text-xs font-medium text-base-content/50 py-1">We</div>
                                                <div class="text-center text-xs font-medium text-base-content/50 py-1">Th</div>
                                                <div class="text-center text-xs font-medium text-base-content/50 py-1">Fr</div>
                                                <div class="text-center text-xs font-medium text-base-content/50 py-1">Sa</div>
                                            </div>

                                            <!-- Calendar Days -->
                                            <div id="calendar-days" class="grid grid-cols-7 gap-1"></div>

                                            <!-- Quick Actions -->
                                            <div class="flex flex-wrap gap-1 mt-3 pt-3 border-t border-base-300">
                                                <button type="button" onclick="setQuickDate('today', event)" class="btn btn-soft btn-primary btn-xs">Today</button>
                                                <button type="button" onclick="setQuickDate('tomorrow', event)" class="btn btn-soft btn-primary btn-xs">Tomorrow</button>
                                                <button type="button" onclick="setQuickDate('next-week', event)" class="btn btn-soft btn-primary btn-xs">Next Week</button>
                                                <button type="button" onclick="clearDate(event)" class="btn btn-soft btn-error btn-xs">No Due Date</button>
                                            </div>
                                        </div>

                                        <!-- Selected Date Display -->
                                        <div class="mt-2 text-sm text-base-content/70">
                                            Selected: <span id="selected-date-display" class="font-medium">{{ $task->due_date ? $task->due_date->format('M d, Y') : 'No Due Date' }}</span>
                                        </div>

                                        <div class="flex gap-2 mt-2">
                                            <button type="submit" class="btn btn-primary btn-xs">
                                                <span class="icon-[tabler--check] size-3.5"></span>
                                                Save
                                            </button>
                                            <button type="button" class="btn btn-ghost btn-xs" onclick="toggleEdit('due-date')">Cancel</button>
                                        </div>
                                    </form>
                                </div>
                                @endif
                            </div>

                            <!-- Workspace -->
                            <div class="py-3">
                                <label class="text-sm font-medium text-base-content/70">Workspace</label>
                                <div class="mt-2">
                                    <a href="{{ route('workspace.show', $task->workspace) }}" class="inline-flex items-center gap-1.5 text-primary hover:text-primary-focus transition-colors">
                                        <span class="icon-[tabler--folder] size-4"></span>
                                        {{ $task->workspace->name }}
                                    </a>
                                </div>
                            </div>

                            <!-- Parent Task -->
                            @if($task->parentTask)
                            <div class="py-3">
                                <label class="text-sm font-medium text-base-content/70">Parent Task</label>
                                <div class="mt-2">
                                    <a href="{{ route('tasks.show', $task->parentTask) }}" class="inline-flex items-center gap-1.5 text-primary hover:text-primary-focus transition-colors">
                                        <span class="icon-[tabler--subtask] size-4"></span>
                                        <span class="font-mono text-xs bg-base-200 px-1.5 py-0.5 rounded">{{ $task->parentTask->task_number }}</span>
                                        <span class="text-sm">{{ Str::limit($task->parentTask->title, 20) }}</span>
                                    </a>
                                </div>
                            </div>
                            @endif

                            <!-- Estimated Time -->
                            @if($task->estimated_time)
                            <div class="py-3 last:pb-0">
                                <label class="text-sm font-medium text-base-content/70">Estimated Time</label>
                                <div class="mt-2 inline-flex items-center gap-1.5 text-base-content">
                                    <span class="icon-[tabler--clock] size-4 text-base-content/60"></span>
                                    <span class="text-sm">{{ floor($task->estimated_time / 60) }}h {{ $task->estimated_time % 60 }}m</span>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Tags -->
                <div class="card bg-base-100 shadow">
                    <div class="card-body">
                        <h2 class="card-title text-lg">
                            <span class="icon-[tabler--tags] size-5"></span>
                            Tags
                        </h2>

                        <div class="flex flex-wrap gap-2">
                            @forelse($task->tags as $tag)
                                <div class="badge gap-1" style="background-color: {{ $tag->color }}20; color: {{ $tag->color }}">
                                    {{ $tag->name }}
                                    @if($task->canEdit($user))
                                        <form action="{{ route('tasks.tags.detach', [$task, $tag]) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="hover:text-error">
                                                <span class="icon-[tabler--x] size-3"></span>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            @empty
                                <p class="text-base-content/60 text-sm">No tags</p>
                            @endforelse
                        </div>

{{--                        @if($task->canEdit($user) && $tags->diff($task->tags)->isNotEmpty())
                            <form action="{{ route('tasks.tags.attach', $task) }}" method="POST" class="mt-3">
                                @csrf
                                <div class="flex gap-2">
                                    <select name="tag_id" class="select select-bordered select-sm flex-1">
                                        <option value="">Add tag...</option>
                                        @foreach($tags->diff($task->tags) as $tag)
                                            <option value="{{ $tag->id }}">{{ $tag->name }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="btn btn-primary btn-sm">Add</button>
                                </div>
                            </form>
                        @endif--}}
                    </div>
                </div>

                <!-- Watchers -->
                <div class="card bg-base-100 shadow">
                    <div class="card-body">
                        <h2 class="card-title text-lg">
                            <span class="icon-[tabler--eye] size-5"></span>
                            Watchers
                            <span class="badge badge-sm">{{ $task->watchers->count() }}</span>
                        </h2>

                        <div class="flex flex-wrap gap-2">
                            @forelse($task->watchers as $watcher)
                                <div class="badge badge-lg gap-2">
                                    <div class="avatar placeholder">
                                        <div class="bg-neutral text-neutral-content w-5 rounded-full">
                                            <span class="text-xs">{{ substr($watcher->name, 0, 1) }}</span>
                                        </div>
                                    </div>
                                    {{ $watcher->name }}
                                    @if($task->canEdit($user) || $watcher->id === $user->id)
                                        <form action="{{ route('tasks.watchers.destroy', [$task, $watcher->id]) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="hover:text-error">
                                                <span class="icon-[tabler--x] size-3"></span>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            @empty
                                <p class="text-base-content/60 text-sm">No watchers</p>
                            @endforelse
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<style>
    /* Edit button - hidden by default, show on hover */
    .edit-btn {
        opacity: 0 !important;
        transition: all 0.2s ease;
    }
    .group:hover .edit-btn {
        opacity: 0.7 !important;
    }
    .group:hover .edit-btn:hover {
        opacity: 1 !important;
        transform: scale(1.1);
    }

    /* Calendar styles */
    #calendar-days .btn {
        min-height: 28px;
        height: 28px;
        font-size: 0.75rem;
    }
    #calendar-days .btn.calendar-day {
        background-color: oklch(var(--b3));
        color: oklch(var(--bc));
        border: none;
    }
    #calendar-days .btn.calendar-day:hover {
        background-color: oklch(var(--p) / 0.2);
    }
    #calendar-days .btn.calendar-day.is-past {
        color: oklch(var(--bc) / 0.4);
        background-color: oklch(var(--b2));
    }
    #calendar-days .btn.calendar-day.is-today {
        border: 2px solid oklch(var(--p));
        background-color: oklch(var(--p) / 0.1);
    }
    #calendar-days .btn.calendar-day.is-selected {
        background-color: oklch(var(--p));
        color: oklch(var(--pc));
        font-weight: 700;
        box-shadow: 0 0 0 2px oklch(var(--p) / 0.3);
    }
</style>
<script>
function toggleEdit(field) {
    const displayEl = document.getElementById(field + '-display');
    const editEl = document.getElementById(field + '-edit');

    if (displayEl && editEl) {
        displayEl.classList.toggle('hidden');
        editEl.classList.toggle('hidden');

        // Focus the first input/select in the edit form
        if (!editEl.classList.contains('hidden')) {
            const input = editEl.querySelector('input, select');
            if (input) {
                setTimeout(() => input.focus(), 100);
            }
        }
    }
}

// Close edit mode when clicking outside
document.addEventListener('click', function(e) {
    // Don't close if clicking inside card-body, edit button, or calendar elements
    if (!e.target.closest('.card-body') && !e.target.closest('.edit-btn') && !e.target.closest('#calendar-days') && !e.target.closest('#due-date-edit')) {
        document.querySelectorAll('[id$="-edit"]:not(.hidden)').forEach(form => {
            const field = form.id.replace('-edit', '');
            const display = document.getElementById(field + '-display');
            if (display) {
                form.classList.add('hidden');
                display.classList.remove('hidden');
            }
        });
    }
});

// Close edit mode on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('[id$="-edit"]:not(.hidden)').forEach(form => {
            const field = form.id.replace('-edit', '');
            const display = document.getElementById(field + '-display');
            if (display) {
                form.classList.add('hidden');
                display.classList.remove('hidden');
            }
        });
    }
});

// Calendar functionality for due date
let currentDate = new Date();
let selectedDate = document.getElementById('due-date-input')?.value ? new Date(document.getElementById('due-date-input').value + 'T00:00:00') : null;

const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

function renderCalendar() {
    const calendarDays = document.getElementById('calendar-days');
    const monthYearEl = document.getElementById('calendar-month-year');

    if (!calendarDays || !monthYearEl) return;

    // Set header
    monthYearEl.textContent = months[currentDate.getMonth()] + ' ' + currentDate.getFullYear();

    // Get first day of month and number of days
    const firstDay = new Date(currentDate.getFullYear(), currentDate.getMonth(), 1);
    const lastDay = new Date(currentDate.getFullYear(), currentDate.getMonth() + 1, 0);
    const startDay = firstDay.getDay();
    const totalDays = lastDay.getDate();

    // Get today for comparison
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    // Build calendar HTML
    let html = '';

    // Empty cells for days before first day of month
    for (let i = 0; i < startDay; i++) {
        html += '<div class="p-1"></div>';
    }

    // Days of the month
    for (let day = 1; day <= totalDays; day++) {
        const date = new Date(currentDate.getFullYear(), currentDate.getMonth(), day);
        const dateStr = formatDate(date);
        const isToday = date.getTime() === today.getTime();
        const isSelected = selectedDate && date.getTime() === selectedDate.getTime();
        const isPast = date < today;

        let classes = 'btn btn-xs w-full aspect-square calendar-day';

        if (isSelected) {
            classes += ' is-selected';
        } else if (isToday) {
            classes += ' is-today';
        } else if (isPast) {
            classes += ' is-past';
        }

        html += `<button type="button" onclick="selectDate('${dateStr}', event)" class="${classes}">${day}</button>`;
    }

    calendarDays.innerHTML = html;
}

function formatDate(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

function formatDisplayDate(date) {
    return months[date.getMonth()] + ' ' + date.getDate() + ', ' + date.getFullYear();
}

function selectDate(dateStr, event) {
    if (event) event.stopPropagation();
    selectedDate = new Date(dateStr + 'T00:00:00');
    document.getElementById('due-date-input').value = dateStr;
    document.getElementById('selected-date-display').textContent = formatDisplayDate(selectedDate);
    renderCalendar();
}

function changeMonth(delta) {
    currentDate.setMonth(currentDate.getMonth() + delta);
    renderCalendar();
}

function setQuickDate(type, event) {
    if (event) event.stopPropagation();
    const date = new Date();
    date.setHours(0, 0, 0, 0);

    switch(type) {
        case 'today':
            break;
        case 'tomorrow':
            date.setDate(date.getDate() + 1);
            break;
        case 'next-week':
            date.setDate(date.getDate() + 7);
            break;
    }

    currentDate = new Date(date);
    selectDate(formatDate(date), event);
}

function clearDate(event) {
    if (event) event.stopPropagation();
    selectedDate = null;
    document.getElementById('due-date-input').value = '';
    document.getElementById('selected-date-display').textContent = 'No Due Date';
    renderCalendar();
}

// Initialize calendar when edit mode opens
const originalToggleEdit = toggleEdit;
toggleEdit = function(field) {
    originalToggleEdit(field);

    if (field === 'due-date') {
        const editEl = document.getElementById('due-date-edit');
        if (editEl && !editEl.classList.contains('hidden')) {
            // Reset to current month or selected date's month
            if (selectedDate) {
                currentDate = new Date(selectedDate);
            } else {
                currentDate = new Date();
            }
            renderCalendar();
        }
    }

    // Focus search input when assignee edit opens
    if (field === 'assignee') {
        const editEl = document.getElementById('assignee-edit');
        if (editEl && !editEl.classList.contains('hidden')) {
            setTimeout(() => {
                const searchInput = document.getElementById('assignee-search');
                if (searchInput) {
                    searchInput.focus();
                    searchInput.select();
                }
            }, 100);
        }
    }
};

// Assignee search functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('assignee-search');
    const dropdown = document.getElementById('assignee-dropdown');
    const hiddenInput = document.getElementById('assignee-input');
    const options = document.querySelectorAll('.assignee-option');

    if (!searchInput || !dropdown) return;

    // Show dropdown on focus
    searchInput.addEventListener('focus', function() {
        dropdown.classList.remove('hidden');
        filterOptions('');
    });

    // Filter options on input
    searchInput.addEventListener('input', function() {
        const query = this.value.toLowerCase();
        filterOptions(query);
        dropdown.classList.remove('hidden');
    });

    // Filter function
    function filterOptions(query) {
        options.forEach(option => {
            const name = option.dataset.name.toLowerCase();
            if (name.includes(query) || query === '') {
                option.classList.remove('hidden');
            } else {
                option.classList.add('hidden');
            }
        });
    }

    // Select option on click
    options.forEach(option => {
        option.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();

            const id = this.dataset.id;
            const name = this.dataset.name;

            // Update hidden input
            hiddenInput.value = id;

            // Update search input display
            searchInput.value = name === 'Unassigned' ? '' : name;

            // Update selected styling
            options.forEach(opt => {
                opt.classList.remove('bg-primary/10');
                const checkIcon = opt.querySelector('.icon-\\[tabler--check\\]');
                if (checkIcon) checkIcon.remove();
            });

            this.classList.add('bg-primary/10');
            if (id) {
                const checkSpan = document.createElement('span');
                checkSpan.className = 'icon-[tabler--check] size-4 text-primary ml-auto';
                this.appendChild(checkSpan);
            }

            // Hide dropdown
            dropdown.classList.add('hidden');
        });
    });

    // Hide dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('#assignee-edit')) {
            dropdown.classList.add('hidden');
        }
    });

    // Keyboard navigation
    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            dropdown.classList.add('hidden');
        }
    });
});
</script>
@endpush
@endsection
