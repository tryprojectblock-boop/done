<div class="space-y-4">
    <!-- Header with Actions -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold">Discussions</h2>
            <p class="text-sm text-base-content/60">{{ $discussions->count() }} {{ Str::plural('discussion', $discussions->count()) }} in this workspace</p>
        </div>
        <a href="{{ route('discussions.create', ['workspace' => $workspace->uuid]) }}" class="btn btn-primary btn-sm">
            <span class="icon-[tabler--plus] size-4"></span>
            New Discussion
        </a>
    </div>

    @if($discussions->count() > 0)
        <!-- Discussion Stats -->
        @php
            $activeDiscussions = $discussions->whereNull('closed_at')->count();
            $closedDiscussions = $discussions->whereNotNull('closed_at')->count();
        @endphp
        <div class="stats stats-horizontal shadow w-full">
            <div class="stat">
                <div class="stat-figure text-primary">
                    <span class="icon-[tabler--messages] size-8"></span>
                </div>
                <div class="stat-title">Active</div>
                <div class="stat-value text-primary">{{ $activeDiscussions }}</div>
            </div>
            <div class="stat">
                <div class="stat-figure text-success">
                    <span class="icon-[tabler--check] size-8"></span>
                </div>
                <div class="stat-title">Resolved</div>
                <div class="stat-value text-success">{{ $closedDiscussions }}</div>
            </div>
            <div class="stat">
                <div class="stat-figure text-info">
                    <span class="icon-[tabler--users] size-8"></span>
                </div>
                <div class="stat-title">Participants</div>
                <div class="stat-value text-info">{{ $discussions->flatMap(fn($d) => $d->participants)->unique('id')->count() }}</div>
            </div>
        </div>

        <!-- Discussions List -->
        <div class="space-y-3">
            @foreach($discussions as $discussion)
                <a href="{{ route('discussions.show', $discussion) }}" class="card bg-base-100 shadow hover:shadow-md transition-shadow block">
                    <div class="card-body py-4">
                        <div class="flex items-start gap-4">
                            <!-- Creator Avatar -->
                            <div class="avatar">
                                <div class="w-10 rounded-full">
                                    <img src="{{ $discussion->creator->avatar_url }}" alt="{{ $discussion->creator->name }}" />
                                </div>
                            </div>

                            <!-- Discussion Content -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-2">
                                    <div>
                                        <h3 class="font-medium text-base-content {{ $discussion->closed_at ? 'line-through text-base-content/50' : '' }}">
                                            {{ $discussion->title }}
                                        </h3>
                                        <p class="text-sm text-base-content/60 mt-1">
                                            Started by {{ $discussion->creator->name }} · {{ $discussion->created_at->diffForHumans() }}
                                        </p>
                                    </div>
                                    @if($discussion->closed_at)
                                        <span class="badge badge-success badge-sm">Resolved</span>
                                    @endif
                                </div>

                                <!-- Preview/Excerpt -->
                                @if($discussion->content)
                                    <p class="text-sm text-base-content/70 mt-2 line-clamp-2">
                                        {{ Str::limit(strip_tags($discussion->content), 150) }}
                                    </p>
                                @endif

                                <!-- Meta info -->
                                <div class="flex items-center gap-4 mt-3 text-sm text-base-content/60">
                                    @if($discussion->comments_count ?? $discussion->comments?->count() ?? 0 > 0)
                                        <span class="flex items-center gap-1">
                                            <span class="icon-[tabler--message] size-4"></span>
                                            {{ $discussion->comments_count ?? $discussion->comments?->count() ?? 0 }} {{ Str::plural('reply', $discussion->comments_count ?? $discussion->comments?->count() ?? 0) }}
                                        </span>
                                    @endif
                                    @if($discussion->participants->count() > 0)
                                        <span class="flex items-center gap-1">
                                            <span class="icon-[tabler--users] size-4"></span>
                                            {{ $discussion->participants->count() }} {{ Str::plural('participant', $discussion->participants->count()) }}
                                        </span>
                                    @endif
                                    @if($discussion->last_activity_at)
                                        <span class="flex items-center gap-1">
                                            <span class="icon-[tabler--clock] size-4"></span>
                                            Last activity {{ $discussion->last_activity_at->diffForHumans() }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        <!-- View All Link -->
        <div class="text-center">
            <a href="{{ route('discussions.index', ['workspace' => $workspace->uuid]) }}" class="btn btn-ghost btn-sm">
                View all discussions
                <span class="icon-[tabler--arrow-right] size-4"></span>
            </a>
        </div>
    @else
        <!-- Empty State -->
        <div class="card bg-base-100 shadow">
            <div class="card-body items-center text-center py-12">
                <span class="icon-[tabler--messages] size-16 text-base-content/20 mb-4"></span>
                <h3 class="text-lg font-semibold">No discussions yet</h3>
                <p class="text-base-content/60 mb-4">Start a discussion to collaborate with your team on ideas and topics.</p>
                <a href="{{ route('discussions.create', ['workspace' => $workspace->uuid]) }}" class="btn btn-primary">
                    <span class="icon-[tabler--plus] size-4"></span>
                    Start First Discussion
                </a>
            </div>
        </div>
    @endif
</div>
