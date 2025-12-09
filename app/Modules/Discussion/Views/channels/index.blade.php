@extends('layouts.app')

@section('content')
<div class="p-4 md:p-6">
    <div class="max-w-7xl mx-auto">
        <!-- Page Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-base-content">Discussions</h1>
                <p class="text-base-content/60">Collaborate and share ideas with your team</p>
            </div>
            @if($user->isAdminOrHigher())
            <a href="{{ route('channels.create') }}" class="btn btn-primary">
                <span class="icon-[tabler--plus] size-5"></span>
                Create Channel
            </a>
            @endif
        </div>

        <!-- Tabs: Discussion | Team Channel -->
        <div class="tabs tabs-bordered mb-6">
            <a href="{{ route('discussions.index') }}" class="tab tab-lg tab-bordered gap-2">
                <span class="icon-[tabler--message-circle] size-5"></span>
                Discussion
            </a>
            <a href="{{ route('channels.index') }}" class="tab tab-lg tab-bordered tab-active gap-2">
                <span class="icon-[tabler--hash] size-5"></span>
                Team Channel
            </a>
        </div>

        <!-- Channels List - Single Column Layout -->
        @if($channels->isEmpty())
            <div class="card bg-base-100 shadow">
                <div class="card-body text-center py-12">
                    <div class="text-base-content/50">
                        <span class="icon-[tabler--hash] size-12 block mx-auto mb-4"></span>
                        <p class="text-lg font-medium">No team channels yet</p>
                        <p class="text-sm">
                            @if($user->isAdminOrHigher())
                                Create a channel to start team discussions
                            @else
                                Contact your administrator to create team channels
                            @endif
                        </p>
                    </div>
                    @if($user->isAdminOrHigher())
                    <div class="mt-4">
                        <a href="{{ route('channels.create') }}" class="btn btn-primary">
                            <span class="icon-[tabler--plus] size-5"></span>
                            Create Channel
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        @else
            <div class="space-y-3">
                @foreach($channels as $channel)
                <a href="{{ route('channels.show', $channel) }}" class="card bg-base-100 shadow hover:shadow-lg transition-all block">
                    <div class="card-body p-4">
                        <div class="flex items-center gap-4">
                            <!-- Channel Icon -->
                            <div class="w-12 h-12 rounded-lg {{ $channel->color_class }} flex items-center justify-center flex-shrink-0">
                                <span class="icon-[tabler--hash] size-6"></span>
                            </div>

                            <!-- Channel Info -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <h3 class="font-semibold text-base-content truncate">{{ $channel->name }}</h3>
                                    <span class="badge {{ $channel->badge_class }} badge-sm">{{ $channel->tag }}</span>
                                    <span class="badge {{ $channel->status_badge_class }} badge-sm">{{ $channel->status_label }}</span>
                                </div>
                                @if($channel->description)
                                    <p class="text-sm text-base-content/60 truncate">{{ $channel->description }}</p>
                                @endif
                            </div>

                            <!-- Stats -->
                            <div class="flex items-center gap-6 text-sm text-base-content/60">
                                <div class="flex items-center gap-1" title="Members">
                                    <span class="icon-[tabler--users] size-4"></span>
                                    <span>{{ $channel->members_count }}</span>
                                </div>
                                <div class="flex items-center gap-1" title="Threads">
                                    <span class="icon-[tabler--message] size-4"></span>
                                    <span>{{ $channel->threads_count }}</span>
                                </div>
                                @if($channel->last_activity_at)
                                <div class="text-xs text-base-content/40 hidden md:block" title="Last activity">
                                    {{ $channel->last_activity_at->diffForHumans() }}
                                </div>
                                @endif
                            </div>

                            <!-- Arrow icon -->
                            <span class="icon-[tabler--chevron-right] size-5 text-base-content/30"></span>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
