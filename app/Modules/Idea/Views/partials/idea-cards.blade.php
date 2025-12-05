<div class="space-y-4">
    @foreach($ideas as $idea)
        <div class="card bg-base-100 shadow hover:shadow-lg transition-shadow">
            <div class="card-body p-4 md:p-6">
                <div class="flex gap-4">
                    <!-- Vote Section -->
                    <div class="flex flex-col items-center gap-1">
                        <form action="{{ route('ideas.vote', $idea->uuid) }}" method="POST">
                            @csrf
                            <input type="hidden" name="vote" value="1">
                            <button type="submit" class="btn btn-ghost btn-sm btn-square {{ $idea->getUserVote(auth()->user()) === 1 ? 'text-success' : '' }}">
                                <span class="icon-[tabler--chevron-up] size-6"></span>
                            </button>
                        </form>
                        <span class="font-bold text-lg {{ $idea->votes_count > 0 ? 'text-success' : ($idea->votes_count < 0 ? 'text-error' : '') }}">
                            {{ $idea->votes_count }}
                        </span>
                        <form action="{{ route('ideas.vote', $idea->uuid) }}" method="POST">
                            @csrf
                            <input type="hidden" name="vote" value="-1">
                            <button type="submit" class="btn btn-ghost btn-sm btn-square {{ $idea->getUserVote(auth()->user()) === -1 ? 'text-error' : '' }}">
                                <span class="icon-[tabler--chevron-down] size-6"></span>
                            </button>
                        </form>
                    </div>

                    <!-- Content Section -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <a href="{{ route('ideas.show', $idea->uuid) }}" class="text-lg font-semibold hover:text-primary transition-colors">
                                    {{ $idea->title }}
                                </a>
                                <div class="flex flex-wrap items-center gap-2 mt-1 text-sm text-base-content/60">
                                    <span class="badge badge-sm" style="background-color: {{ $idea->status->color() }}20; color: {{ $idea->status->color() }}">
                                        <span class="icon-[{{ $idea->status->icon() }}] size-3 mr-1"></span>
                                        {{ $idea->status->label() }}
                                    </span>
                                    <span class="flex items-center gap-1" style="color: {{ $idea->priority->color() }}">
                                        <span class="icon-[{{ $idea->priority->icon() }}] size-4"></span>
                                        {{ $idea->priority->label() }}
                                    </span>
                                    @if($idea->workspace)
                                        <span>in {{ $idea->workspace->name }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        @if($idea->short_description)
                            <p class="text-base-content/70 mt-2 line-clamp-2">{{ $idea->short_description }}</p>
                        @endif

                        <div class="flex flex-wrap items-center gap-4 mt-3 text-sm text-base-content/60">
                            <div class="flex items-center gap-2">
                                <div class="avatar">
                                    <div class="w-6 rounded-full">
                                        <img src="{{ $idea->creator->avatar_url }}" alt="{{ $idea->creator->name }}" />
                                    </div>
                                </div>
                                <span>{{ $idea->creator->name }}</span>
                            </div>
                            <span>{{ $idea->created_at->diffForHumans() }}</span>
                            <span class="flex items-center gap-1">
                                <span class="icon-[tabler--message] size-4"></span>
                                {{ $idea->comments_count }} {{ Str::plural('comment', $idea->comments_count) }}
                            </span>
                            @if($idea->members->isNotEmpty())
                                <div class="flex items-center gap-1">
                                    <span class="icon-[tabler--users] size-4"></span>
                                    <div class="avatar-group -space-x-2">
                                        @foreach($idea->members->take(3) as $member)
                                            <div class="avatar border-2 border-base-100" title="{{ $member->name }}">
                                                <div class="w-5 rounded-full">
                                                    <img src="{{ $member->avatar_url }}" alt="{{ $member->name }}" />
                                                </div>
                                            </div>
                                        @endforeach
                                        @if($idea->members->count() > 3)
                                            <div class="avatar placeholder border-2 border-base-100">
                                                <div class="bg-neutral text-neutral-content w-5 rounded-full">
                                                    <span class="text-xs">+{{ $idea->members->count() - 3 }}</span>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
