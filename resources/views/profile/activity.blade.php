@extends('layouts.app')

@section('content')
<div class="min-h-screen p-4 md:p-6">
    <div class="max-w-4xl mx-auto">
        <!-- Page Header -->
        <div class="mb-6">
            <div class="flex items-center gap-2 text-sm text-base-content/60 mb-2">
                <a href="{{ route('profile.index') }}" class="hover:text-primary">Profile</a>
                <span class="icon-[tabler--chevron-right] size-4"></span>
                <span>Activity</span>
            </div>
            <h1 class="text-2xl font-bold text-base-content">My Activity</h1>
            <p class="text-base-content/60">View your recent activity across the platform</p>
        </div>

        <!-- Filter Tabs -->
        <div class="tabs tabs-boxed bg-base-100 mb-6 p-1 inline-flex">
            <a href="{{ route('profile.activity', ['filter' => 'all']) }}"
               class="tab {{ $filter === 'all' ? 'tab-active' : '' }}">
                <span class="icon-[tabler--activity] size-4 mr-1"></span>
                All Activity
            </a>
            <a href="{{ route('profile.activity', ['filter' => 'tasks']) }}"
               class="tab {{ $filter === 'tasks' ? 'tab-active' : '' }}">
                <span class="icon-[tabler--checkbox] size-4 mr-1"></span>
                Tasks
            </a>
            <a href="{{ route('profile.activity', ['filter' => 'comments']) }}"
               class="tab {{ $filter === 'comments' ? 'tab-active' : '' }}">
                <span class="icon-[tabler--message] size-4 mr-1"></span>
                Comments
            </a>
        </div>

        <!-- Activity Timeline -->
        <div class="space-y-6">
            @forelse($groupedActivities as $date => $activities)
                <div class="card bg-base-100 shadow">
                    <div class="card-body">
                        <!-- Date Header -->
                        <div class="flex items-center gap-2 mb-4">
                            <span class="icon-[tabler--calendar] size-5 text-base-content/60"></span>
                            <h2 class="font-semibold text-base-content">
                                @if(\Carbon\Carbon::parse($date)->isToday())
                                    Today
                                @elseif(\Carbon\Carbon::parse($date)->isYesterday())
                                    Yesterday
                                @else
                                    {{ \Carbon\Carbon::parse($date)->format('F j, Y') }}
                                @endif
                            </h2>
                            <span class="badge badge-ghost badge-sm">{{ $activities->count() }} {{ Str::plural('activity', $activities->count()) }}</span>
                        </div>

                        <!-- Activities List -->
                        <div class="relative">
                            <!-- Timeline Line -->
                            <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-base-300"></div>

                            <div class="space-y-4">
                                @foreach($activities as $activity)
                                    <div class="flex gap-4 relative">
                                        <!-- Icon -->
                                        <div class="w-8 h-8 rounded-full bg-{{ $activity['color'] }}/10 flex items-center justify-center z-10 flex-shrink-0">
                                            <span class="icon-[{{ $activity['icon'] }}] size-4 text-{{ $activity['color'] }}"></span>
                                        </div>

                                        <!-- Content -->
                                        <div class="flex-1 min-w-0 pb-4">
                                            <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-2">
                                                <p class="text-sm text-base-content">{{ $activity['description'] }}</p>
                                                <span class="text-xs text-base-content/50">
                                                    {{ $activity['created_at']->format('g:i A') }}
                                                </span>
                                            </div>

                                            <!-- Link to related item -->
                                            <div class="mt-1">
                                                @if(isset($activity['task']) && $activity['task'])
                                                    <a href="{{ route('tasks.show', $activity['task']) }}"
                                                       class="inline-flex items-center gap-1 text-xs text-primary hover:underline">
                                                        <span class="icon-[tabler--external-link] size-3"></span>
                                                        {{ $activity['task']->task_number }} - {{ Str::limit($activity['task']->title, 40) }}
                                                    </a>
                                                @elseif(isset($activity['idea']) && $activity['idea'])
                                                    <a href="{{ route('ideas.show', $activity['idea']) }}"
                                                       class="inline-flex items-center gap-1 text-xs text-warning hover:underline">
                                                        <span class="icon-[tabler--external-link] size-3"></span>
                                                        {{ Str::limit($activity['idea']->title, 50) }}
                                                    </a>
                                                @elseif(isset($activity['discussion']) && $activity['discussion'])
                                                    <a href="{{ route('discussions.show', $activity['discussion']) }}"
                                                       class="inline-flex items-center gap-1 text-xs text-success hover:underline">
                                                        <span class="icon-[tabler--external-link] size-3"></span>
                                                        {{ Str::limit($activity['discussion']->title, 50) }}
                                                    </a>
                                                @endif
                                            </div>

                                            <!-- Comment preview if it's a comment -->
                                            @if($activity['type'] === 'comment' && isset($activity['content']))
                                                <div class="mt-2 p-2 bg-base-200 rounded text-sm text-base-content/70 line-clamp-2">
                                                    {!! Str::limit(strip_tags($activity['content']), 150) !!}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="card bg-base-100 shadow">
                    <div class="card-body items-center text-center py-12">
                        <span class="icon-[tabler--activity-heartbeat] size-16 text-base-content/20 mb-4"></span>
                        <h3 class="text-lg font-semibold text-base-content/60">No Activity Yet</h3>
                        <p class="text-base-content/50">Your activity will appear here as you interact with tasks, ideas, and discussions.</p>
                    </div>
                </div>
            @endforelse
        </div>

        <!-- Back to Profile -->
        <div class="mt-6">
            <a href="{{ route('profile.index') }}" class="btn btn-ghost">
                <span class="icon-[tabler--arrow-left] size-4"></span>
                Back to Profile
            </a>
        </div>
    </div>
</div>
@endsection
