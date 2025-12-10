@extends('layouts.app')

@section('content')
<div class="p-4 md:p-6">
    <div class="max-w-7xl mx-auto">
        @if($isFirstLogin)
            <!-- First Time User - Onboarding -->
            <div class="mb-8">
                <div class="card bg-gradient-to-r from-primary to-primary/80 text-primary-content shadow-xl">
                    <div class="card-body">
                        <div class="flex flex-col lg:flex-row items-center gap-6">
                            <div class="flex-1">
                                <h1 class="text-3xl font-bold mb-2">Welcome to NewDone, {{ auth()->user()->first_name }}!</h1>
                                <p class="text-primary-content/80 mb-4">
                                    We're excited to have you on board. Let's get you started with a quick tour of the platform.
                                </p>
                                <div class="flex flex-wrap gap-3">
                                    <button class="btn btn-secondary" onclick="document.getElementById('onboarding-video-modal').showModal()">
                                        <span class="icon-[tabler--player-play] size-5"></span>
                                        Watch Getting Started Video
                                    </button>
                                    <a href="/workspace/create" class="btn btn-ghost bg-white/20 hover:bg-white/30">
                                        <span class="icon-[tabler--plus] size-5"></span>
                                        Create Your First Workspace
                                    </a>
                                </div>
                            </div>
                            <div class="hidden lg:block">
                                <div class="size-32 rounded-full bg-white/20 flex items-center justify-center">
                                    <span class="icon-[tabler--rocket] size-16 text-white"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Onboarding Steps -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                <div class="card bg-base-100 shadow">
                    <div class="card-body">
                        <div class="flex items-start gap-4">
                            <div class="size-12 rounded-full bg-primary/10 flex items-center justify-center flex-shrink-0">
                                <span class="icon-[tabler--briefcase] size-6 text-primary"></span>
                            </div>
                            <div>
                                <h3 class="font-semibold mb-1">1. Create a Workspace</h3>
                                <p class="text-sm text-base-content/60 mb-3">Set up your first workspace to organize your tasks and team.</p>
                                <a href="/workspace/create" class="btn btn-primary btn-sm">Create Workspace</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card bg-base-100 shadow">
                    <div class="card-body">
                        <div class="flex items-start gap-4">
                            <div class="size-12 rounded-full bg-success/10 flex items-center justify-center flex-shrink-0">
                                <span class="icon-[tabler--user-plus] size-6 text-success"></span>
                            </div>
                            <div>
                                <h3 class="font-semibold mb-1">2. Invite Your Team</h3>
                                <p class="text-sm text-base-content/60 mb-3">Bring your team members on board to collaborate together.</p>
                                <a href="/users/invite" class="btn btn-success btn-sm">Invite Members</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card bg-base-100 shadow">
                    <div class="card-body">
                        <div class="flex items-start gap-4">
                            <div class="size-12 rounded-full bg-info/10 flex items-center justify-center flex-shrink-0">
                                <span class="icon-[tabler--checkbox] size-6 text-info"></span>
                            </div>
                            <div>
                                <h3 class="font-semibold mb-1">3. Add Your First Task</h3>
                                <p class="text-sm text-base-content/60 mb-3">Start tracking your work by creating your first task.</p>
                                <a href="/tasks/create" class="btn btn-info btn-sm">Add Task</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Onboarding Video Modal -->
            <dialog id="onboarding-video-modal" class="modal">
                <div class="modal-box max-w-4xl">
                    <form method="dialog">
                        <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">
                            <span class="icon-[tabler--x] size-5"></span>
                        </button>
                    </form>
                    <h3 class="font-bold text-lg mb-4">Getting Started with NewDone</h3>
                    <div class="aspect-video bg-base-200 rounded-lg flex items-center justify-center">
                        <!-- Replace with actual video embed -->
                        <div class="text-center">
                            <span class="icon-[tabler--player-play] size-16 text-base-content/30"></span>
                            <p class="text-base-content/50 mt-2">Onboarding video will be displayed here</p>
                        </div>
                    </div>
                    <div class="modal-action">
                        <form method="dialog">
                            <button class="btn">Close</button>
                        </form>
                    </div>
                </div>
                <form method="dialog" class="modal-backdrop">
                    <button>close</button>
                </form>
            </dialog>
        @else
            <!-- Regular Dashboard - Todo List -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-base-content">Good {{ $greeting }}, {{ auth()->user()->first_name }}!</h1>
                    <p class="text-base-content/60">
                        @if(auth()->user()->company)
                            <span class="font-medium">{{ auth()->user()->company->name }}</span> &middot;
                        @endif
                        Here's what's on your plate today
                    </p>
                </div>
                <div class="flex gap-2">
                    <a href="/tasks/create" class="btn btn-primary btn-sm">
                        <span class="icon-[tabler--plus] size-4"></span>
                        Add Task
                    </a>
                </div>
            </div>

            <!-- Stats Overview -->
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
                <a href="{{ route('tasks.index') }}" class="card bg-base-100 shadow hover:shadow-lg transition-shadow">
                    <div class="card-body p-4">
                        <div class="flex items-center gap-3">
                            <div class="size-10 rounded-lg bg-primary/10 flex items-center justify-center">
                                <span class="icon-[tabler--list-check] size-5 text-primary"></span>
                            </div>
                            <div>
                                <p class="text-2xl font-bold">{{ $stats['total_tasks'] ?? 0 }}</p>
                                <p class="text-xs text-base-content/60">Total Tasks</p>
                            </div>
                        </div>
                    </div>
                </a>
                <div class="card bg-base-100 shadow">
                    <div class="card-body p-4">
                        <div class="flex items-center gap-3">
                            <div class="size-10 rounded-lg bg-warning/10 flex items-center justify-center">
                                <span class="icon-[tabler--clock] size-5 text-warning"></span>
                            </div>
                            <div>
                                <p class="text-2xl font-bold">{{ $stats['pending_tasks'] ?? 0 }}</p>
                                <p class="text-xs text-base-content/60">Pending</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card bg-base-100 shadow">
                    <div class="card-body p-4">
                        <div class="flex items-center gap-3">
                            <div class="size-10 rounded-lg bg-info/10 flex items-center justify-center">
                                <span class="icon-[tabler--progress] size-5 text-info"></span>
                            </div>
                            <div>
                                <p class="text-2xl font-bold">{{ $stats['in_progress_tasks'] ?? 0 }}</p>
                                <p class="text-xs text-base-content/60">In Progress</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card bg-base-100 shadow">
                    <div class="card-body p-4">
                        <div class="flex items-center gap-3">
                            <div class="size-10 rounded-lg bg-success/10 flex items-center justify-center">
                                <span class="icon-[tabler--circle-check] size-5 text-success"></span>
                            </div>
                            <div>
                                <p class="text-2xl font-bold">{{ $stats['completed_tasks'] ?? 0 }}</p>
                                <p class="text-xs text-base-content/60">Completed</p>
                            </div>
                        </div>
                    </div>
                </div>
                <a href="{{ route('ideas.index') }}" class="card bg-base-100 shadow hover:shadow-lg transition-shadow">
                    <div class="card-body p-4">
                        <div class="flex items-center gap-3">
                            <div class="size-10 rounded-lg bg-accent/10 flex items-center justify-center">
                                <span class="icon-[tabler--bulb] size-5 text-accent"></span>
                            </div>
                            <div>
                                <p class="text-2xl font-bold">{{ $stats['ideas_count'] ?? 0 }}</p>
                                <p class="text-xs text-base-content/60">Ideas</p>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- My Tasks Today -->
                <div class="lg:col-span-2">
                    <div class="card bg-base-100 shadow">
                        <div class="card-body">
                            <div class="flex items-center justify-between mb-4">
                                <h2 class="card-title text-lg">
                                    <span class="icon-[tabler--checkbox] size-5 text-primary"></span>
                                    My Tasks
                                </h2>
                                <a href="/tasks" class="btn btn-ghost btn-sm">View All</a>
                            </div>

                            @if(empty($tasks))
                                <div class="text-center py-8">
                                    <div class="size-16 rounded-full bg-base-200 flex items-center justify-center mx-auto mb-4">
                                        <span class="icon-[tabler--clipboard-list] size-8 text-base-content/30"></span>
                                    </div>
                                    <p class="text-base-content/60 mb-4">No tasks yet. Create your first task to get started!</p>
                                    <a href="/tasks/create" class="btn btn-primary btn-sm">
                                        <span class="icon-[tabler--plus] size-4"></span>
                                        Add Task
                                    </a>
                                </div>
                            @else
                                <div class="space-y-2">
                                    @foreach($tasks as $task)
                                        <a href="{{ route('tasks.show', $task['uuid']) }}" class="flex items-center gap-3 p-3 rounded-lg hover:bg-base-200 transition-colors">
                                            <div class="size-4 rounded border-2 {{ $task['completed'] ? 'bg-primary border-primary' : 'border-base-300' }} flex items-center justify-center">
                                                @if($task['completed'])
                                                    <span class="icon-[tabler--check] size-3 text-primary-content"></span>
                                                @endif
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="font-medium truncate {{ $task['completed'] ? 'line-through text-base-content/50' : '' }}">{{ $task['title'] }}</p>
                                                <p class="text-xs text-base-content/50">{{ $task['workspace'] ?? 'No workspace' }}</p>
                                            </div>
                                            @if(isset($task['status']) && $task['status'])
                                                <span class="badge badge-sm border" style="background-color: {{ $task['status_color'] }}20; color: {{ $task['status_color'] }}; border-color: {{ $task['status_color'] }}40;">
                                                    {{ $task['status'] }}
                                                </span>
                                            @endif
                                            @if(isset($task['due_date']) && $task['due_date'])
                                                <span class="badge badge-sm {{ $task['overdue'] ? 'badge-error' : 'badge-ghost' }}">
                                                    {{ $task['due_date'] }}
                                                </span>
                                            @endif
                                            @if(isset($task['priority']) && $task['priority'])
                                                <span class="badge badge-sm border" style="background-color: {{ $task['priority_color'] }}20; color: {{ $task['priority_color'] }}; border-color: {{ $task['priority_color'] }}40;">
                                                    {{ $task['priority_label'] }}
                                                </span>
                                            @endif
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Right Sidebar -->
                <div class="space-y-6">
                    <!-- Upcoming -->
                    <div class="card bg-base-100 shadow">
                        <div class="card-body">
                            <h2 class="card-title text-lg mb-4">
                                <span class="icon-[tabler--calendar] size-5 text-info"></span>
                                Upcoming
                            </h2>
                            @if(empty($upcoming))
                                <p class="text-sm text-base-content/60 text-center py-4">No upcoming events</p>
                            @else
                                <div class="space-y-3">
                                    @foreach($upcoming as $event)
                                        <div class="flex items-start gap-3">
                                            <div class="size-8 rounded bg-info/10 flex items-center justify-center flex-shrink-0">
                                                <span class="icon-[tabler--calendar-event] size-4 text-info"></span>
                                            </div>
                                            <div>
                                                <p class="font-medium text-sm">{{ $event['title'] }}</p>
                                                <p class="text-xs text-base-content/50">{{ $event['date'] }}</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="card bg-base-100 shadow">
                        <div class="card-body">
                            <h2 class="card-title text-lg mb-4">
                                <span class="icon-[tabler--activity] size-5 text-success"></span>
                                Recent Activity
                            </h2>
                            @if(empty($activities))
                                <p class="text-sm text-base-content/60 text-center py-4">No recent activity</p>
                            @else
                                <div class="space-y-3">
                                    @foreach($activities as $activity)
                                        <div class="flex items-start gap-3">
                                            <div class="avatar">
                                                <div class="w-8 h-8 rounded-full">
                                                    <img src="{{ $activity['avatar_url'] }}" alt="{{ $activity['user'] }}" />
                                                </div>
                                            </div>
                                            <div>
                                                <p class="text-sm"><strong>{{ $activity['user'] }}</strong> {{ $activity['action'] }}</p>
                                                <p class="text-xs text-base-content/50">{{ $activity['time'] }}</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
