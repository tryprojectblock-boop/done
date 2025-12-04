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
                <div class="card bg-base-100 shadow">
                    <div class="card-body">
                        <h2 class="card-title text-lg">Details</h2>

                        <div class="space-y-4">
                            <!-- Status -->
                            <div>
                                <label class="text-sm text-base-content/60">Status</label>
                                @if($task->canChangeStatus($user) && !$task->isClosed())
                                    <form action="{{ route('tasks.update-status', $task) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <select name="status_id" class="select select-bordered select-sm w-full" onchange="this.form.submit()">
                                            <option value="">No Status</option>
                                            @foreach($statuses as $status)
                                                <option value="{{ $status->id }}" {{ $task->status_id == $status->id ? 'selected' : '' }}>
                                                    {{ $status->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </form>
                                @else
                                    <div class="mt-1">
                                        @if($task->status)
                                            <span class="badge" style="background-color: {{ $task->status->background_color }}20; color: {{ $task->status->background_color }}">
                                                {{ $task->status->name }}
                                            </span>
                                        @else
                                            <span class="text-base-content/40">Not set</span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                            <!-- Assignee -->
                            <div>
                                <label class="text-sm text-base-content/60">Assignee</label>
                                @if($task->canEdit($user) && !$task->isClosed())
                                    <form action="{{ route('tasks.update-assignee', $task) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <select name="assignee_id" class="select select-bordered select-sm w-full" onchange="this.form.submit()">
                                            <option value="">Unassigned</option>
                                            @foreach($users as $u)
                                                <option value="{{ $u->id }}" {{ $task->assignee_id == $u->id ? 'selected' : '' }}>
                                                    {{ $u->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </form>
                                @else
                                    <div class="mt-1">
                                        @if($task->assignee)
                                            <div class="flex items-center gap-2">
                                                <div class="avatar">
                                                    <div class="w-6 rounded-full">
                                                        <img src="{{ $task->assignee->avatar_url }}" alt="{{ $task->assignee->name }}" />
                                                    </div>
                                                </div>
                                                <span>{{ $task->assignee->name }}</span>
                                            </div>
                                        @else
                                            <span class="text-base-content/40">Unassigned</span>
                                        @endif
                                    </div>
                                @endif
                            </div>

                            <!-- Task Type(s) -->
                            <div>
                                <label class="text-sm text-base-content/60">Type</label>
                                <div class="mt-1 flex flex-wrap gap-1">
                                    @if($task->types && count($task->types) > 0)
                                        @foreach($task->types as $taskType)
                                            <span class="badge badge-sm gap-1">
                                                <span class="icon-[{{ $taskType->icon() }}] size-3"></span>
                                                {{ $taskType->label() }}
                                            </span>
                                        @endforeach
                                    @else
                                        <span class="text-base-content/40">Not set</span>
                                    @endif
                                </div>
                            </div>

                            <!-- Priority -->
                            <div>
                                <label class="text-sm text-base-content/60">Priority</label>
                                <div class="mt-1">
                                    @if($task->priority)
                                        <span class="flex items-center gap-1" style="color: {{ $task->priority->color() }}">
                                            <span class="icon-[{{ $task->priority->icon() }}] size-4"></span>
                                            {{ $task->priority->label() }}
                                        </span>
                                    @else
                                        <span class="text-base-content/40">Not set</span>
                                    @endif
                                </div>
                            </div>

                            <!-- Due Date -->
                            <div>
                                <label class="text-sm text-base-content/60">Due Date</label>
                                <div class="mt-1">
                                    @if($task->due_date)
                                        <span class="flex items-center gap-1 {{ $task->isOverdue() ? 'text-error font-medium' : '' }}">
                                            <span class="icon-[tabler--calendar] size-4"></span>
                                            {{ $task->due_date->format('M d, Y') }}
                                            @if($task->isOverdue())
                                                <span class="badge badge-error badge-xs">Overdue</span>
                                            @endif
                                        </span>
                                    @else
                                        <span class="text-base-content/40">Not set</span>
                                    @endif
                                </div>
                            </div>

                            <!-- Workspace -->
                            <div>
                                <label class="text-sm text-base-content/60">Workspace</label>
                                <div class="mt-1">
                                    <a href="{{ route('workspace.show', $task->workspace) }}" class="link link-primary">
                                        {{ $task->workspace->name }}
                                    </a>
                                </div>
                            </div>

                            <!-- Parent Task -->
                            @if($task->parentTask)
                                <div>
                                    <label class="text-sm text-base-content/60">Parent Task</label>
                                    <div class="mt-1">
                                        <a href="{{ route('tasks.show', $task->parentTask) }}" class="link link-primary flex items-center gap-1">
                                            <span class="font-mono text-sm">{{ $task->parentTask->task_number }}</span>
                                            {{ Str::limit($task->parentTask->title, 25) }}
                                        </a>
                                    </div>
                                </div>
                            @endif

                            <!-- Estimated Time -->
                            @if($task->estimated_time)
                                <div>
                                    <label class="text-sm text-base-content/60">Estimated Time</label>
                                    <div class="mt-1">{{ floor($task->estimated_time / 60) }}h {{ $task->estimated_time % 60 }}m</div>
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

@endsection
