<div class="card bg-base-100 shadow">
    <div class="overflow-x-auto">
        <table class="table">
            <thead>
                <tr>
                    <th class="w-20">Votes</th>
                    <th>Idea</th>
                    <th>Status</th>
                    <th>Priority</th>
                    <th>Creator</th>
                    <th>Comments</th>
                    <th>Created</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($ideas as $idea)
                <tr class="hover cursor-pointer" onclick="window.location='{{ route('ideas.show', $idea->uuid) }}'">
                    <td onclick="event.stopPropagation()">
                        <div class="flex items-center gap-1">
                            <form action="{{ route('ideas.vote', $idea->uuid) }}" method="POST">
                                @csrf
                                <input type="hidden" name="vote" value="1">
                                <button type="submit" class="btn btn-ghost btn-xs btn-square {{ $idea->getUserVote(auth()->user()) === 1 ? 'text-success' : '' }}">
                                    <span class="icon-[tabler--chevron-up] size-4"></span>
                                </button>
                            </form>
                            <span class="font-bold text-base min-w-6 text-center {{ $idea->votes_count > 0 ? 'text-success' : ($idea->votes_count < 0 ? 'text-error' : '') }}">
                                {{ $idea->votes_count }}
                            </span>
                            <form action="{{ route('ideas.vote', $idea->uuid) }}" method="POST">
                                @csrf
                                <input type="hidden" name="vote" value="-1">
                                <button type="submit" class="btn btn-ghost btn-xs btn-square {{ $idea->getUserVote(auth()->user()) === -1 ? 'text-error' : '' }}">
                                    <span class="icon-[tabler--chevron-down] size-4"></span>
                                </button>
                            </form>
                        </div>
                    </td>
                    <td>
                        <div class="flex items-center gap-3">
                            <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-base-200">
                                <span class="icon-[tabler--bulb] size-5 text-warning"></span>
                            </div>
                            <div>
                                <div class="font-medium">{{ Str::limit($idea->title, 40) }}</div>
                                @if($idea->workspace)
                                    <div class="text-xs text-base-content/50">{{ $idea->workspace->name }}</div>
                                @else
                                    <div class="text-xs text-base-content/50">General</div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="badge badge-sm border-0" style="background-color: {{ $idea->status->color() }}20; color: {{ $idea->status->color() }};">
                            <span class="icon-[{{ $idea->status->icon() }}] size-3 mr-1"></span>
                            {{ $idea->status->label() }}
                        </span>
                    </td>
                    <td>
                        <div class="flex items-center gap-1" style="color: {{ $idea->priority->color() }}">
                            <span class="icon-[{{ $idea->priority->icon() }}] size-4"></span>
                            <span>{{ $idea->priority->label() }}</span>
                        </div>
                    </td>
                    <td>
                        <div class="flex items-center gap-2">
                            <div class="avatar">
                                <div class="w-8 h-8 rounded-full">
                                    <img src="{{ $idea->creator->avatar_url }}" alt="{{ $idea->creator->name }}" />
                                </div>
                            </div>
                            <span class="text-sm">{{ $idea->creator->name }}</span>
                        </div>
                    </td>
                    <td>
                        <div class="flex items-center gap-1 text-sm">
                            <span class="icon-[tabler--message] size-4 text-base-content/50"></span>
                            {{ $idea->comments_count }}
                        </div>
                    </td>
                    <td class="text-sm text-base-content/60">
                        {{ $idea->created_at->diffForHumans() }}
                    </td>
                    <td class="text-right">
                        <div class="flex items-center justify-end gap-1" onclick="event.stopPropagation()">
                            <a href="{{ route('ideas.show', $idea->uuid) }}" class="btn btn-ghost btn-sm btn-circle" title="View Idea">
                                <span class="icon-[tabler--eye] size-4"></span>
                            </a>
                            @if($idea->canEdit(auth()->user()))
                                <a href="{{ route('ideas.edit', $idea->uuid) }}" class="btn btn-ghost btn-sm btn-circle" title="Edit Idea">
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
