<div class="card bg-base-100 shadow">
    <div class="overflow-x-auto">
        <table class="table">
            <thead>
                <tr>
                    <th>Discussion</th>
                    <th>Type</th>
                    <th>Creator</th>
                    <th>Comments</th>
                    <th>Participants</th>
                    <th>Last Activity</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($discussions as $discussion)
                <tr class="hover cursor-pointer" onclick="window.location='{{ route('discussions.show', $discussion->uuid) }}'">
                    <td>
                        <div class="flex items-center gap-3">
                            <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-base-200">
                                @if($discussion->isPrivate())
                                    <span class="icon-[tabler--lock] size-5 text-base-content/50"></span>
                                @else
                                    <span class="icon-[tabler--messages] size-5 text-primary"></span>
                                @endif
                            </div>
                            <div>
                                <div class="font-medium flex items-center gap-2">
                                    {{ Str::limit($discussion->title, 40) }}
                                    @if($discussion->is_public)
                                        <span class="badge badge-success badge-xs">Public</span>
                                    @endif
                                </div>
                                @if($discussion->workspace)
                                    <div class="text-xs text-base-content/50">{{ $discussion->workspace->name }}</div>
                                @else
                                    <div class="text-xs text-base-content/50">General</div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td>
                        @if($discussion->type)
                            <span class="badge badge-sm border-0" style="background-color: {{ $discussion->type->color() }}20; color: {{ $discussion->type->color() }};">
                                <span class="icon-[{{ $discussion->type->icon() }}] size-3 mr-1"></span>
                                {{ $discussion->type->label() }}
                            </span>
                        @else
                            <span class="text-base-content/40">-</span>
                        @endif
                    </td>
                    <td>
                        <div class="flex items-center gap-2">
                            <div class="avatar">
                                <div class="w-8 h-8 rounded-full">
                                    <img src="{{ $discussion->creator->avatar_url }}" alt="{{ $discussion->creator->name }}" />
                                </div>
                            </div>
                            <span class="text-sm">{{ $discussion->creator->name }}</span>
                        </div>
                    </td>
                    <td>
                        <div class="flex items-center gap-1 text-sm">
                            <span class="icon-[tabler--message] size-4 text-base-content/50"></span>
                            {{ $discussion->comments_count }}
                        </div>
                    </td>
                    <td>
                        @if($discussion->participants->isNotEmpty())
                            <div class="avatar-group -space-x-2">
                                @foreach($discussion->participants->take(3) as $participant)
                                    <div class="avatar border-2 border-base-100" title="{{ $participant->name }}">
                                        <div class="w-6 rounded-full">
                                            <img src="{{ $participant->avatar_url }}" alt="{{ $participant->name }}" />
                                        </div>
                                    </div>
                                @endforeach
                                @if($discussion->participants->count() > 3)
                                    <div class="avatar placeholder border-2 border-base-100">
                                        <div class="bg-neutral text-neutral-content w-6 rounded-full">
                                            <span class="text-xs">+{{ $discussion->participants->count() - 3 }}</span>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @else
                            <span class="text-base-content/40">-</span>
                        @endif
                    </td>
                    <td class="text-sm">
                        <span class="flex items-center gap-1 text-base-content/60">
                            <span class="icon-[tabler--clock] size-4"></span>
                            {{ $discussion->last_activity_at->diffForHumans() }}
                        </span>
                    </td>
                    <td class="text-right">
                        <div class="flex items-center justify-end gap-1" onclick="event.stopPropagation()">
                            <a href="{{ route('discussions.show', $discussion->uuid) }}" class="btn btn-ghost btn-sm btn-circle" title="View Discussion">
                                <span class="icon-[tabler--eye] size-4"></span>
                            </a>
                            @if($discussion->canEdit(auth()->user()))
                                <a href="{{ route('discussions.edit', $discussion->uuid) }}" class="btn btn-ghost btn-sm btn-circle" title="Edit Discussion">
                                    <span class="icon-[tabler--edit] size-4"></span>
                                </a>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
