@extends('layouts.app')

@php
    $currentUser = auth()->user();
@endphp

@section('content')
<div class="p-4 md:p-6">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center gap-2 text-sm text-base-content/60 mb-2">
                <a href="{{ route('dashboard') }}" class="hover:text-primary">Dashboard</a>
                <span class="icon-[tabler--chevron-right] size-4"></span>
                <a href="{{ route('discussions.index') }}" class="hover:text-primary">Discussions</a>
                <span class="icon-[tabler--chevron-right] size-4"></span>
                <span>{{ Str::limit($discussion->title, 30) }}</span>
            </div>

            <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
                <div class="flex-1">
                    <div class="flex items-center gap-2 flex-wrap">
                        @if($discussion->isPrivate())
                            <span class="icon-[tabler--lock] size-5 text-base-content/50" title="Private Discussion"></span>
                        @endif
                        <h1 class="text-2xl font-bold text-base-content">{{ $discussion->title }}</h1>
                        @if($discussion->type)
                            <span class="badge" style="background-color: {{ $discussion->type->color() }}20; color: {{ $discussion->type->color() }}">
                                <span class="icon-[{{ $discussion->type->icon() }}] size-3 mr-1"></span>
                                {{ $discussion->type->label() }}
                            </span>
                        @endif
                    </div>
                    <p class="text-base-content/60 mt-1">
                        Created by {{ $discussion->creator->name }} on {{ $discussion->created_at->format('M d, Y') }}
                        {{--@if($discussion->workspace)
                            in <a href="{{ route('workspace.show', $discussion->workspace) }}" class="link link-primary">{{ $discussion->workspace->name }}</a>
                        @endif--}}
                    </p>
                </div>

                <div class="flex flex-wrap gap-2">
                    @if($discussion->canEdit($user))
                        <a href="{{ route('discussions.edit', $discussion->uuid) }}" class="btn btn-ghost btn-sm">
                            <span class="icon-[tabler--edit] size-4"></span>
                            Edit
                        </a>
                    @endif

                    @if($discussion->canDelete($user))
                        <form action="{{ route('discussions.destroy', $discussion->uuid) }}" method="POST" class="inline"
                              onsubmit="return confirm('Are you sure you want to delete this discussion?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-ghost btn-sm text-error">
                                <span class="icon-[tabler--trash] size-4"></span>
                                Delete
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>


        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Discussion Details -->
                <div class="card bg-base-100 shadow">
                    <div class="card-body">
                        @if($discussion->details)
                            <div class="prose prose-sm max-w-none">
                                {!! $discussion->details !!}
                            </div>
                        @else
                            <p class="text-base-content/60 italic">No details provided.</p>
                        @endif

                        <!-- Attachments -->
                        @if($discussion->attachments->isNotEmpty())
                            <div class="divider"></div>
                            <h3 class="font-medium mb-3">
                                <span class="icon-[tabler--paperclip] size-4 inline"></span>
                                Attachments ({{ $discussion->attachments->count() }})
                            </h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                @foreach($discussion->attachments as $attachment)
                                    <a href="{{ $attachment->url }}" target="_blank" download
                                       class="flex items-center gap-3 p-3 rounded-lg border border-base-300 hover:bg-base-200 transition-colors">
                                        @if($attachment->isImage())
                                            <img src="{{ $attachment->url }}" alt="{{ $attachment->original_filename }}" class="w-10 h-10 object-cover rounded">
                                        @else
                                            <span class="icon-[{{ $attachment->icon }}] size-8 text-base-content/50"></span>
                                        @endif
                                        <div class="flex-1 min-w-0">
                                            <p class="font-medium text-sm truncate">{{ $attachment->original_filename }}</p>
                                            <p class="text-xs text-base-content/50">{{ $attachment->human_size }}</p>
                                        </div>
                                        <span class="icon-[tabler--download] size-4 text-base-content/50"></span>
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Comments Section -->
                <div class="card bg-base-100 shadow">
                    <div class="card-body">
                        <h2 class="card-title text-lg">
                            <span class="icon-[tabler--message-circle] size-5"></span>
                            Comments
                            <span class="badge badge-sm">{{ $discussion->comments_count }}</span>
                        </h2>

                        <!-- Add Comment Form -->
                        <form action="{{ route('discussions.comments.store', $discussion) }}" method="POST" enctype="multipart/form-data" class="mb-6">
                            @csrf
                            <div class="flex gap-3">
                                <div class="avatar">
                                    <div class="w-10 h-10 rounded-full overflow-hidden">
                                        <img src="{{ $currentUser->avatar_url }}" alt="{{ $currentUser->name }}" class="w-full h-full object-cover" />
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <x-quill-editor
                                        name="content"
                                        id="comment-editor"
                                        placeholder="Write a comment..."
                                        height="100px"
                                    />
                                    <div class="flex items-center justify-between mt-2">
                                        <input type="file" name="attachments[]" multiple class="file-input file-input-bordered file-input-sm w-auto max-w-xs">
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
                            @forelse($discussion->comments()->with(['user', 'replies.user', 'attachments'])->get() as $comment)
                                @include('discussion::partials.comment', ['comment' => $comment, 'discussion' => $discussion])
                            @empty
                                <p class="text-base-content/60 text-center py-4">No comments yet. Start the conversation!</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Info Card -->
                <div class="card bg-base-100 shadow">
                    <div class="card-body">
                        <h2 class="card-title text-lg">Details</h2>

                        <div class="space-y-4">
                            <!-- Visibility -->
                            <div>
                                <label class="text-sm text-base-content/60">Visibility</label>
                                <div class="mt-1 flex items-center gap-2">
                                    @if($discussion->is_public)
                                        <span class="icon-[tabler--world] size-4 text-success"></span>
                                        <span class="text-success">Public</span>
                                    @else
                                        <span class="icon-[tabler--lock] size-4 text-base-content/50"></span>
                                        <span>Private</span>
                                    @endif
                                </div>
                            </div>

                            <!-- Type -->
                            @if($discussion->type)
                                <div>
                                    <label class="text-sm text-base-content/60">Type</label>
                                    <div class="mt-1">
                                        <span class="badge" style="background-color: {{ $discussion->type->color() }}20; color: {{ $discussion->type->color() }}">
                                            <span class="icon-[{{ $discussion->type->icon() }}] size-3 mr-1"></span>
                                            {{ $discussion->type->label() }}
                                        </span>
                                    </div>
                                </div>
                            @endif

                            <!-- Workspace -->
                            @if($discussion->workspace)
                                <div>
                                    <label class="text-sm text-base-content/60">Workspace</label>
                                    <div class="mt-1">
                                        <a href="{{ route('workspace.show', $discussion->workspace) }}" class="link link-primary">
                                            {{ $discussion->workspace->name }}
                                        </a>
                                    </div>
                                </div>
                            @endif

                            <!-- Comments -->
                            <div>
                                <label class="text-sm text-base-content/60">Comments</label>
                                <div class="mt-1 font-medium">{{ $discussion->comments_count }}</div>
                            </div>

                            <!-- Created -->
                            <div>
                                <label class="text-sm text-base-content/60">Created</label>
                                <div class="mt-1">{{ $discussion->created_at->format('M d, Y \a\t g:i A') }}</div>
                            </div>

                            <!-- Last Activity -->
                            <div>
                                <label class="text-sm text-base-content/60">Last Activity</label>
                                <div class="mt-1">{{ $discussion->last_activity_at->diffForHumans() }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Participants Card -->
                <div class="card bg-base-100 shadow">
                    <div class="card-body">
                        @php
                            // Count unique participants: if creator is not in participants list, add 1 for creator
                            $creatorInParticipants = $discussion->participants->contains('id', $discussion->created_by);
                            $totalParticipants = $creatorInParticipants
                                ? $discussion->participants->count()
                                : $discussion->participants->count() + 1;
                        @endphp
                        <h2 class="card-title text-lg">
                            <span class="icon-[tabler--users] size-5"></span>
                            Participants
                            <span class="badge badge-sm">{{ $totalParticipants }}</span>
                        </h2>

                        <div class="space-y-2">
                            <!-- Creator -->
                            <div class="flex items-center gap-3 p-2 rounded-lg bg-base-200">
                                <div class="avatar">
                                    <div class="w-8 rounded-full">
                                        <img src="{{ $discussion->creator->avatar_url }}" alt="{{ $discussion->creator->name }}" />
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <p class="font-medium text-sm">{{ $discussion->creator->name }}</p>
                                    <p class="text-xs text-base-content/50">Creator</p>
                                </div>
                            </div>

                            <!-- Other Participants -->
                            @foreach($discussion->participants as $participant)
                                @if($participant->id !== $discussion->created_by)
                                    <div class="flex items-center gap-3 p-2 rounded-lg hover:bg-base-200">
                                        <div class="avatar">
                                            <div class="w-8 rounded-full">
                                                <img src="{{ $participant->avatar_url }}" alt="{{ $participant->name }}" />
                                            </div>
                                        </div>
                                        <div class="flex-1">
                                            <p class="font-medium text-sm">{{ $participant->name }}</p>
                                            @if($participant->role === 'guest')
                                                <p class="text-xs text-base-content/50">Guest</p>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            @endforeach

                            @if($discussion->participants->count() === 0 || ($discussion->participants->count() === 1 && $discussion->participants->first()->id === $discussion->created_by))
                                <p class="text-base-content/60 text-sm">No additional participants</p>
                            @endif

                            @if($discussion->canInvite($user))
                                <a href="{{ route('discussions.edit', $discussion->uuid) }}" class="btn btn-ghost btn-sm w-full mt-2">
                                    <span class="icon-[tabler--user-plus] size-4"></span>
                                    Invite More
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Task Creation Drawer --}}
@include('discussion::partials.task-drawer', ['discussion' => $discussion, 'workspaces' => $workspaces])

@endsection
