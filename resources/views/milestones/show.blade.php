@extends('layouts.app')

@section('content')
<div class="p-4 md:p-6">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center gap-2 text-sm text-base-content/60 mb-2">
                <a href="{{ route('dashboard') }}" class="hover:text-primary">Dashboard</a>
                <span class="icon-[tabler--chevron-right] size-4"></span>
                <a href="{{ route('workspace.show', $workspace->uuid) }}" class="hover:text-primary">{{ $workspace->name }}</a>
                <span class="icon-[tabler--chevron-right] size-4"></span>
                <a href="{{ route('milestones.index', $workspace->uuid) }}" class="hover:text-primary">Milestones</a>
                <span class="icon-[tabler--chevron-right] size-4"></span>
                <span class="truncate max-w-48">{{ $milestone->title }}</span>
            </div>
            <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
                <div class="flex-1">
                    <div class="flex items-center gap-3 mb-2">
                        @if($milestone->color)
                            <div class="w-4 h-4 rounded-full flex-shrink-0" style="background-color: {{ $milestone->color }}"></div>
                        @endif
                        <h1 class="text-2xl font-bold text-base-content">{{ $milestone->title }}</h1>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="badge {{ $milestone->status_badge }}">{{ $milestone->status_label }}</span>
                        <span class="badge badge-outline" style="border-color: {{ $milestone->priority_color }}; color: {{ $milestone->priority_color }};">
                            {{ $milestone->priority_label }} Priority
                        </span>
                        @if($milestone->isOverdue())
                            <span class="badge badge-error">Overdue</span>
                        @endif
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('milestones.edit', [$workspace->uuid, $milestone->uuid]) }}" class="btn btn-outline btn-sm">
                        <span class="icon-[tabler--edit] size-4"></span>
                        Edit
                    </a>
                    <div class="dropdown dropdown-end">
                        <label tabindex="0" class="btn btn-ghost btn-sm">
                            <span class="icon-[tabler--dots-vertical] size-4"></span>
                        </label>
                        <ul tabindex="0" class="dropdown-menu dropdown-open:opacity-100 hidden min-w-40">
                            <li>
                                <form action="{{ route('milestones.updateStatus', [$workspace->uuid, $milestone->uuid]) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="status" value="in_progress">
                                    <button type="submit" class="dropdown-item w-full text-left">
                                        <span class="icon-[tabler--player-play] size-4"></span>
                                        Mark In Progress
                                    </button>
                                </form>
                            </li>
                            <li>
                                <form action="{{ route('milestones.updateStatus', [$workspace->uuid, $milestone->uuid]) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="status" value="completed">
                                    <button type="submit" class="dropdown-item w-full text-left">
                                        <span class="icon-[tabler--check] size-4"></span>
                                        Mark Complete
                                    </button>
                                </form>
                            </li>
                            <li class="border-t border-base-200 mt-1 pt-1">
                                <button type="button" class="dropdown-item w-full text-left text-error"
                                    data-delete
                                    data-delete-action="{{ route('milestones.destroy', [$workspace->uuid, $milestone->uuid]) }}"
                                    data-delete-title="Delete Milestone"
                                    data-delete-name="{{ $milestone->title }}">
                                    <span class="icon-[tabler--trash] size-4"></span>
                                    Delete
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Success/Error Messages -->
        <div class="mb-4">
            @include('partials.alerts')
        </div>

        @php
            $stats = $milestone->task_stats;
            $allTasksCompleted = $stats['total'] > 0 && $stats['completed'] === $stats['total'];
        @endphp

        <!-- Auto-complete prompt when all tasks are done -->
        @if($allTasksCompleted && $milestone->status !== 'completed')
        <div class="alert alert-success mb-6 shadow-lg">
            <div class="flex items-center gap-3">
                <span class="icon-[tabler--confetti] size-8"></span>
                <div>
                    <h3 class="font-bold">All tasks completed!</h3>
                    <p class="text-sm">All {{ $stats['total'] }} tasks in this milestone are done. Would you like to mark this milestone as complete?</p>
                </div>
            </div>
            <div class="flex-none">
                <form action="{{ route('milestones.updateStatus', [$workspace->uuid, $milestone->uuid]) }}" method="POST" class="inline">
                    @csrf
                    <input type="hidden" name="status" value="completed">
                    <button type="submit" class="btn btn-success btn-sm gap-2">
                        <span class="icon-[tabler--check] size-4"></span>
                        Mark Complete
                    </button>
                </form>
            </div>
        </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Overview Card -->
                <div class="card bg-base-100 shadow">
                    <div class="card-body">
                        <h2 class="card-title text-lg mb-4">
                            <span class="icon-[tabler--info-circle] size-5"></span>
                            Overview
                        </h2>

                        <!-- Progress -->
                        <div class="mb-4">
                            <div class="flex items-center justify-between text-sm mb-2">
                                <span class="font-medium">Progress</span>
                                <span class="text-lg font-bold" style="color: {{ $milestone->status_color }}">{{ $milestone->progress }}%</span>
                            </div>
                            <div class="w-full bg-base-200 rounded-full h-3">
                                <div class="h-3 rounded-full transition-all duration-300" style="width: {{ $milestone->progress }}%; background-color: {{ $milestone->status_color }};"></div>
                            </div>
                        </div>

                        <!-- Dates -->
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <span class="text-sm text-base-content/60">Start Date</span>
                                <div class="font-medium">
                                    {{ $milestone->start_date ? $milestone->start_date->format('M d, Y') : 'Not set' }}
                                </div>
                            </div>
                            <div>
                                <span class="text-sm text-base-content/60">Due Date</span>
                                <div class="font-medium {{ $milestone->isOverdue() ? 'text-error' : '' }}">
                                    {{ $milestone->due_date ? $milestone->due_date->format('M d, Y') : 'Not set' }}
                                    @if($milestone->days_remaining !== null && !$milestone->isCompleted())
                                        @if($milestone->days_remaining < 0)
                                            <span class="text-error text-sm">({{ abs($milestone->days_remaining) }} days overdue)</span>
                                        @elseif($milestone->days_remaining === 0)
                                            <span class="text-warning text-sm">(Due today)</span>
                                        @else
                                            <span class="text-base-content/60 text-sm">({{ $milestone->days_remaining }} days left)</span>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        @if($milestone->description)
                            <div class="border-t border-base-200 pt-4">
                                <span class="text-sm text-base-content/60 mb-2 block">Description</span>
                                <div class="prose prose-sm max-w-none">
                                    {!! $milestone->description !!}
                                </div>
                            </div>
                        @endif

                        <!-- Tags -->
                        @if($milestone->tags->isNotEmpty())
                            <div class="border-t border-base-200 pt-4">
                                <span class="text-sm text-base-content/60 mb-2 block">Tags</span>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($milestone->tags as $tag)
                                        <span class="badge" style="background-color: {{ $tag->color }}20; color: {{ $tag->color }}; border: 1px solid {{ $tag->color }}40;">
                                            {{ $tag->name }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Tasks Section -->
                <div class="card bg-base-100 shadow">
                    <div class="card-body">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="card-title text-lg">
                                <span class="icon-[tabler--checkbox] size-5"></span>
                                Tasks ({{ $milestone->tasks->count() }})
                            </h2>
                            <!-- Add Task Buttons -->
                            <div class="flex items-center gap-2">
                                <!-- Create New Task -->
                                <a href="{{ route('tasks.create', ['workspace' => $workspace->uuid, 'milestone' => $milestone->id]) }}" class="btn btn-primary btn-sm">
                                    <span class="icon-[tabler--plus] size-4"></span>
                                    Create Task
                                </a>

                                <!-- Link Existing Task -->
                                <div class="dropdown dropdown-end">
                                    <button type="button" tabindex="0" class="btn btn-outline btn-sm">
                                        <span class="icon-[tabler--link] size-4"></span>
                                        Link Existing
                                    </button>
                                    <ul tabindex="0" class="dropdown-menu dropdown-open:opacity-100 hidden w-80 max-h-96 overflow-y-auto p-2">
                                        <li class="menu-title">Available Tasks</li>
                                        @if($availableTasks->isEmpty())
                                            <li class="p-3 text-sm text-base-content/60">No unlinked tasks available.</li>
                                        @else
                                            @foreach($availableTasks as $task)
                                                <li>
                                                    <form action="{{ route('milestones.addTask', [$workspace->uuid, $milestone->uuid]) }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="task_id" value="{{ $task->id }}">
                                                        <button type="submit" class="w-full text-left">
                                                            <div class="flex flex-col">
                                                                <span class="font-medium text-sm truncate">{{ $task->title }}</span>
                                                                <span class="text-xs text-base-content/60">
                                                                    #{{ $task->task_number }} &middot; {{ $task->status?->name ?? 'No Status' }}
                                                                </span>
                                                            </div>
                                                        </button>
                                                    </form>
                                                </li>
                                            @endforeach
                                        @endif
                                    </ul>
                                </div>
                            </div>
                        </div>

                        @if($milestone->tasks->isEmpty())
                            <div class="text-center py-8">
                                <span class="icon-[tabler--checkbox] size-12 text-base-content/20 mx-auto block mb-2"></span>
                                <p class="text-base-content/60">No tasks assigned to this milestone yet.</p>
                            </div>
                        @else
                            <!-- Tasks grouped by status -->
                            @foreach($tasksByStatus as $statusName => $tasks)
                                <div class="mb-4">
                                    <h4 class="text-sm font-semibold text-base-content/70 mb-2">{{ $statusName }} ({{ $tasks->count() }})</h4>
                                    <div class="space-y-2">
                                        @foreach($tasks as $task)
                                            <div class="flex items-center justify-between p-3 bg-base-50 rounded-lg border border-base-200">
                                                <div class="flex items-center gap-3 flex-1 min-w-0">
                                                    <span class="text-xs text-base-content/50">#{{ $task->task_number }}</span>
                                                    <a href="{{ route('tasks.show', $task->uuid) }}" class="font-medium hover:text-primary truncate">
                                                        {{ $task->title }}
                                                    </a>
                                                    @if($task->closed_at)
                                                        <span class="badge badge-success badge-xs">Done</span>
                                                    @endif
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    @if($task->assignee)
                                                        <div class="avatar {{ $task->assignee->avatar_url ? '' : 'placeholder' }}" title="{{ $task->assignee->full_name }}">
                                                            @if($task->assignee->avatar_url)
                                                                <div class="w-6 h-6 rounded-full">
                                                                    <img src="{{ $task->assignee->avatar_url }}" alt="">
                                                                </div>
                                                            @else
                                                                <div class="bg-primary text-primary-content rounded-full w-6 h-6 flex items-center justify-center">
                                                                    <span class="text-xs">{{ $task->assignee->initials }}</span>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    @endif
                                                    <form action="{{ route('milestones.removeTask', [$workspace->uuid, $milestone->uuid, $task->uuid]) }}" method="POST" class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-ghost btn-xs text-error" title="Remove from milestone">
                                                            <span class="icon-[tabler--x] size-4"></span>
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>

                <!-- Comments Section -->
                <div class="card bg-base-100 shadow">
                    <div class="card-body">
                        <h2 class="card-title text-lg mb-4">
                            <span class="icon-[tabler--message] size-5"></span>
                            Discussion ({{ $milestone->comments->count() }})
                        </h2>

                        <!-- Add Comment Form -->
                        <form action="{{ route('milestones.addComment', [$workspace->uuid, $milestone->uuid]) }}" method="POST" class="mb-4">
                            @csrf
                            <div class="flex gap-3">
                                <div class="avatar {{ auth()->user()->avatar_url ? '' : 'placeholder' }} flex-shrink-0">
                                    @if(auth()->user()->avatar_url)
                                        <div class="w-8 h-8 rounded-full">
                                            <img src="{{ auth()->user()->avatar_url }}" alt="">
                                        </div>
                                    @else
                                        <div class="bg-primary text-primary-content rounded-full w-8 h-8 flex items-center justify-center">
                                            <span class="text-xs">{{ auth()->user()->initials }}</span>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <textarea name="content" rows="2" class="textarea textarea-bordered w-full" placeholder="Add a comment..." required></textarea>
                                    <div class="flex justify-end mt-2">
                                        <button type="submit" class="btn btn-primary btn-sm">
                                            <span class="icon-[tabler--send] size-4"></span>
                                            Post Comment
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>

                        <!-- Comments List -->
                        @if($milestone->comments->isNotEmpty())
                            <div class="space-y-4 border-t border-base-200 pt-4">
                                @foreach($milestone->comments as $comment)
                                    <div class="flex gap-3">
                                        <div class="avatar {{ $comment->user->avatar_url ? '' : 'placeholder' }} flex-shrink-0">
                                            @if($comment->user->avatar_url)
                                                <div class="w-8 h-8 rounded-full">
                                                    <img src="{{ $comment->user->avatar_url }}" alt="">
                                                </div>
                                            @else
                                                <div class="bg-primary text-primary-content rounded-full w-8 h-8 flex items-center justify-center">
                                                    <span class="text-xs">{{ $comment->user->initials }}</span>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2 mb-1">
                                                <span class="font-medium text-sm">{{ $comment->user->full_name }}</span>
                                                <span class="text-xs text-base-content/50">{{ $comment->created_at->diffForHumans() }}</span>
                                            </div>
                                            <div class="text-sm text-base-content/80 bg-base-50 rounded-lg p-3">
                                                {!! nl2br(e($comment->content)) !!}
                                            </div>
                                            @if($comment->user_id === auth()->id() || auth()->user()->isAdminOrHigher())
                                                <form action="{{ route('milestones.deleteComment', [$workspace->uuid, $milestone->uuid, $comment->id]) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-xs text-error hover:underline mt-1">Delete</button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Owner Card -->
                <div class="card bg-base-100 shadow">
                    <div class="card-body">
                        <h3 class="font-semibold text-sm text-base-content/60 mb-3">Owner</h3>
                        @if($milestone->owner)
                            <div class="flex items-center gap-3">
                                <div class="avatar {{ $milestone->owner->avatar_url ? '' : 'placeholder' }}">
                                    @if($milestone->owner->avatar_url)
                                        <div class="w-10 h-10 rounded-full">
                                            <img src="{{ $milestone->owner->avatar_url }}" alt="">
                                        </div>
                                    @else
                                        <div class="bg-primary text-primary-content rounded-full w-10 h-10 flex items-center justify-center">
                                            <span class="text-sm">{{ $milestone->owner->initials }}</span>
                                        </div>
                                    @endif
                                </div>
                                <div>
                                    <div class="font-medium">{{ $milestone->owner->full_name }}</div>
                                    <div class="text-xs text-base-content/60">{{ $milestone->owner->email }}</div>
                                </div>
                            </div>
                        @else
                            <p class="text-sm text-base-content/60">No owner assigned</p>
                        @endif

                        <div class="border-t border-base-200 pt-3 mt-3">
                            <h3 class="font-semibold text-sm text-base-content/60 mb-2">Created by</h3>
                            <div class="flex items-center gap-2">
                                <div class="avatar {{ $milestone->creator->avatar_url ? '' : 'placeholder' }}">
                                    @if($milestone->creator->avatar_url)
                                        <div class="w-6 h-6 rounded-full">
                                            <img src="{{ $milestone->creator->avatar_url }}" alt="">
                                        </div>
                                    @else
                                        <div class="bg-base-300 text-base-content rounded-full w-6 h-6 flex items-center justify-center">
                                            <span class="text-xs">{{ $milestone->creator->initials }}</span>
                                        </div>
                                    @endif
                                </div>
                                <span class="text-sm">{{ $milestone->creator->full_name }}</span>
                            </div>
                            <div class="text-xs text-base-content/50 mt-1">{{ $milestone->created_at->format('M d, Y \a\t g:i A') }}</div>
                        </div>
                    </div>
                </div>

                <!-- Task Stats Card -->
                <div class="card bg-base-100 shadow">
                    <div class="card-body">
                        <h3 class="font-semibold text-sm text-base-content/60 mb-3">Task Statistics</h3>
                        @php $stats = $milestone->task_stats; @endphp
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-sm">Total Tasks</span>
                                <span class="font-bold">{{ $stats['total'] }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-success">Completed</span>
                                <span class="font-bold text-success">{{ $stats['completed'] }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-warning">Open</span>
                                <span class="font-bold text-warning">{{ $stats['open'] }}</span>
                            </div>
                            <div class="w-full bg-base-200 rounded-full h-2 mt-2">
                                <div class="bg-success h-2 rounded-full" style="width: {{ $stats['percentage'] }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Attachments Card -->
                <div class="card bg-base-100 shadow">
                    <div class="card-body">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="font-semibold text-sm text-base-content/60 flex items-center gap-2">
                                <span class="icon-[tabler--paperclip] size-4"></span>
                                Attachments ({{ $milestone->attachments->count() }})
                            </h3>
                        </div>

                        <!-- Upload Form -->
                        <form action="{{ route('milestones.uploadAttachment', [$workspace->uuid, $milestone->uuid]) }}" method="POST" enctype="multipart/form-data" class="mb-3">
                            @csrf
                            <div class="flex gap-2">
                                <input type="file" name="file" class="file-input file-input-bordered file-input-sm flex-1" required>
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <span class="icon-[tabler--upload] size-4"></span>
                                </button>
                            </div>
                        </form>

                        <!-- Attachments List -->
                        @if($milestone->attachments->isNotEmpty())
                            <div class="space-y-2">
                                @foreach($milestone->attachments as $attachment)
                                    <div class="flex items-center justify-between p-2 bg-base-50 rounded-lg">
                                        <div class="flex items-center gap-2 flex-1 min-w-0">
                                            <span class="icon-[tabler--paperclip] size-4 text-base-content/60"></span>
                                            <a href="{{ $attachment->getUrl() }}" target="_blank" class="text-sm hover:text-primary truncate">
                                                {{ $attachment->original_filename }}
                                            </a>
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <span class="text-xs text-base-content/50">{{ $attachment->formatted_size }}</span>
                                            @if($attachment->uploaded_by === auth()->id() || auth()->user()->isAdminOrHigher())
                                                <form action="{{ route('milestones.deleteAttachment', [$workspace->uuid, $milestone->uuid, $attachment->id]) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-ghost btn-xs text-error">
                                                        <span class="icon-[tabler--x] size-3"></span>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-base-content/50">No attachments yet.</p>
                        @endif
                    </div>
                </div>

                <!-- Activity Timeline -->
                <div class="card bg-base-100 shadow">
                    <div class="card-body">
                        <h3 class="font-semibold text-sm text-base-content/60 mb-3">Activity</h3>

                        @if($milestone->activities->isEmpty())
                            <p class="text-sm text-base-content/50">No activity yet.</p>
                        @else
                            <div class="space-y-3">
                                @foreach($milestone->activities->take(10) as $activity)
                                    <div class="flex gap-3">
                                        <div class="flex-shrink-0 mt-1">
                                            <span class="icon-[{{ $activity->icon }}] size-4 {{ $activity->color }}"></span>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="text-sm">
                                                <span class="font-medium">{{ $activity->user->full_name }}</span>
                                                <span class="text-base-content/70">{{ $activity->description }}</span>
                                            </div>
                                            <div class="text-xs text-base-content/50">{{ $activity->created_at->diffForHumans() }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
