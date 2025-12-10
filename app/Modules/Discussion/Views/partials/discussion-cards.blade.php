<div class="flex flex-col gap-4">
    @foreach($discussions as $discussion)
    <a href="{{ route('discussions.show', $discussion->uuid) }}" class="card bg-base-100 shadow hover:shadow-lg transition-shadow">
        <div class="card-body p-4">
            <!-- Header: Privacy Icon, Type Badge -->
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-2">
                    @if($discussion->isPrivate())
                        <span class="icon-[tabler--lock] size-4 text-base-content/50" title="Private"></span>
                    @else
                        <span class="icon-[tabler--world] size-4 text-success" title="Public"></span>
                    @endif
                    @if($discussion->type)
                        <span class="badge badge-sm" style="background-color: {{ $discussion->type->color() }}20; color: {{ $discussion->type->color() }}">
                            <span class="icon-[{{ $discussion->type->icon() }}] size-3 mr-1"></span>
                            {{ $discussion->type->label() }}
                        </span>
                    @endif
                </div>
                <div class="flex items-center gap-1 text-xs text-base-content/50">
                    <span class="icon-[tabler--message] size-4"></span>
                    {{ $discussion->comments_count }}
                </div>
            </div>

            <!-- Title -->
            <h3 class="font-medium text-base-content line-clamp-2">
                {{ $discussion->title }}
            </h3>

            <!-- Workspace -->
            <div class="text-xs text-base-content/50 mt-1">
                <span class="icon-[tabler--briefcase] size-3 inline"></span>
                {{ $discussion->workspace?->name ?? 'General' }}
            </div>

            <!-- Footer: Creator, Last Activity, Participants -->
            <div class="flex items-center justify-between mt-auto pt-3 border-t border-base-200">
                <div class="flex items-center gap-2">
                    <div class="avatar" title="{{ $discussion->creator->name }}">
                        <div class="w-6 h-6 rounded-full">
                            <img src="{{ $discussion->creator->avatar_url }}" alt="{{ $discussion->creator->name }}" />
                        </div>
                    </div>
                    <span class="text-xs text-base-content/60">{{ $discussion->creator->name }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="flex items-center gap-1 text-xs text-base-content/50">
                        <span class="icon-[tabler--clock] size-3"></span>
                        {{ $discussion->last_activity_at->diffForHumans() }}
                    </span>
                    @if($discussion->participants->isNotEmpty())
                        <div class="avatar-group -space-x-2">
                            @foreach($discussion->participants->take(3) as $participant)
                                <div class="avatar border-2 border-base-100" title="{{ $participant->name }}">
                                    <div class="w-5 rounded-full">
                                        <img src="{{ $participant->avatar_url }}" alt="{{ $participant->name }}" />
                                    </div>
                                </div>
                            @endforeach
                            @if($discussion->participants->count() > 3)
                                <div class="avatar placeholder border-2 border-base-100">
                                    <div class="bg-neutral text-neutral-content w-5 rounded-full">
                                        <span class="text-xs">+{{ $discussion->participants->count() - 3 }}</span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </a>
    @endforeach
</div>
