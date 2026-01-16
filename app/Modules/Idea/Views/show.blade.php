@extends('layouts.app')

@php
    $currentUser = auth()->user();
@endphp

@section('content')
<div class="p-4 md:p-6">
    <div class="max-w mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center gap-2 text-sm text-base-content/60 mb-2">
                <a href="{{ route('dashboard') }}" class="hover:text-primary">Dashboard</a>
                <span class="icon-[tabler--chevron-right] size-4"></span>
                <a href="{{ route('ideas.index') }}" class="hover:text-primary">Ideas</a>
                <span class="icon-[tabler--chevron-right] size-4"></span>
                <span>{{ Str::limit($idea->title, 30) }}</span>
            </div>

            <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
                <div class="flex-1">
                    <h1 class="text-2xl font-bold text-base-content">{{ $idea->title }}</h1>
                    <p class="text-base-content/60 mt-1">
                        Created by {{ $idea->creator->name }} on {{ $idea->created_at->format('M d, Y') }}
                    </p>
                </div>

                <div class="flex flex-wrap gap-2">
                    @if($idea->canEdit($user))
                        <a href="{{ route('ideas.edit', $idea->uuid) }}" class="btn btn-ghost btn-sm">
                            <span class="icon-[tabler--edit] size-4"></span>
                            Edit
                        </a>
                    @endif

                    @if($idea->canDelete($user))
                        <form action="{{ route('ideas.destroy', $idea->uuid) }}" method="POST" class="inline"
                              onsubmit="return confirm('Are you sure you want to delete this idea?')">
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

        <!-- Success/Error Messages -->
        @if(session('success'))
            <div class="alert alert-success mb-4">
                <span class="icon-[tabler--check] size-5"></span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-error mb-4">
                <span class="icon-[tabler--x] size-5"></span>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Idea Content -->
                <div class="card bg-base-100 shadow">
                    <div class="card-body">
                        <!-- Vote and Title Section -->
                        <div class="flex gap-4">
                            <!-- Vote Section -->
                            <div class="flex flex-col items-center gap-1">
                                <form action="{{ route('ideas.vote', $idea->uuid) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="vote" value="1">
                                    <button type="submit" class="btn btn-ghost btn-sm btn-square {{ $idea->getUserVote($currentUser) === 1 ? 'text-success bg-success/10' : '' }}">
                                        <span class="icon-[tabler--chevron-up] size-6"></span>
                                    </button>
                                </form>
                                <span class="font-bold text-xl {{ $idea->votes_count > 0 ? 'text-success' : ($idea->votes_count < 0 ? 'text-error' : '') }}">
                                    {{ $idea->votes_count }}
                                </span>
                                <form action="{{ route('ideas.vote', $idea->uuid) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="vote" value="-1">
                                    <button type="submit" class="btn btn-ghost btn-sm btn-square {{ $idea->getUserVote($currentUser) === -1 ? 'text-error bg-error/10' : '' }}">
                                        <span class="icon-[tabler--chevron-down] size-6"></span>
                                    </button>
                                </form>
                            </div>

                            <!-- Content -->
                            <div class="flex-1">
                                @if($idea->short_description)
                                    <p class="text-base-content/70 text-lg mb-4">{{ $idea->short_description }}</p>
                                @endif

                                @if($idea->description)
                                    <div class="prose prose-sm max-w-none">
                                        {!! $idea->description !!}
                                    </div>
                                @else
                                    <p class="text-base-content/60 italic">No detailed description provided.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Discussion Section -->
                <div class="card bg-base-100 shadow">
                    <div class="card-body">
                        <h2 class="card-title text-lg">
                            <span class="icon-[tabler--message-circle] size-5"></span>
                            Discussion
                            <span class="badge badge-sm">{{ $idea->comments_count }}</span>
                        </h2>

                        <!-- Add Comment Form -->
                        <form action="{{ route('ideas.comments.store', $idea) }}" method="POST" class="mb-4" id="comment-form">
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
                                        placeholder="Share your thoughts on this idea..."
                                        height="100px"
                                    />
                                    <div class="flex justify-end mt-2">
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
                            @forelse($idea->comments()->whereNull('parent_id')->with(['user', 'replies.user'])->get() as $comment)
                                @include('idea::partials.comment', ['comment' => $comment, 'idea' => $idea])
                            @empty
                                <p class="text-base-content/60 text-center py-4">No comments yet. Start the discussion!</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Status & Info Card -->
                <div class="card bg-base-100 shadow">
                    <div class="card-body">
                        <h2 class="card-title text-lg">Details</h2>

                        <div class="space-y-4">
                            <!-- Status -->
                            <div>
                                <label class="text-sm text-base-content/60">Status</label>
                                @if($idea->canChangeStatus($user))
                                    <form action="{{ route('ideas.change-status', $idea->uuid) }}" method="POST" class="mt-1">
                                        @csrf
                                        @method('PATCH')
                                        <select name="status" class="select select-bordered select-sm w-full" onchange="this.form.submit()">
                                            @foreach($statuses as $value => $label)
                                                <option value="{{ $value }}" {{ $idea->status->value === $value ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </form>
                                @else
                                    <div class="mt-1">
                                        <span class="badge" style="background-color: {{ $idea->status->color() }}20; color: {{ $idea->status->color() }}">
                                            <span class="icon-[{{ $idea->status->icon() }}] size-3 mr-1"></span>
                                            {{ $idea->status->label() }}
                                        </span>
                                    </div>
                                @endif
                            </div>

                            <!-- Priority -->
                            <div>
                                <label class="text-sm text-base-content/60">Priority</label>
                                <div class="mt-1">
                                    <span class="flex items-center gap-1" style="color: {{ $idea->priority->color() }}">
                                        <span class="icon-[{{ $idea->priority->icon() }}] size-4"></span>
                                        {{ $idea->priority->label() }}
                                    </span>
                                </div>
                            </div>

                            <!-- Workspace -->
                            @if($idea->workspace)
                                <div>
                                    <label class="text-sm text-base-content/60">Workspace</label>
                                    <div class="mt-1">
                                        <a href="{{ route('workspace.show', $idea->workspace) }}" class="link link-primary">
                                            {{ $idea->workspace->name }}
                                        </a>
                                    </div>
                                </div>
                            @endif

                            <!-- Votes -->
                            <div>
                                <label class="text-sm text-base-content/60">Votes</label>
                                <div class="mt-1 flex items-center gap-2">
                                    <span class="font-bold text-lg {{ $idea->votes_count > 0 ? 'text-success' : ($idea->votes_count < 0 ? 'text-error' : '') }}">
                                        {{ $idea->votes_count }}
                                    </span>
                                    <span class="text-base-content/60 text-sm">
                                        ({{ $idea->votes()->where('vote', 1)->count() }} up, {{ $idea->votes()->where('vote', -1)->count() }} down)
                                    </span>
                                </div>
                            </div>

                            <!-- Created -->
                            <div>
                                <label class="text-sm text-base-content/60">Created</label>
                                <div class="mt-1">{{ $idea->created_at->format('M d, Y \a\t g:i A') }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Members Card -->
                <div class="card bg-base-100 shadow">
                    <div class="card-body">
                        <h2 class="card-title text-lg">
                            <span class="icon-[tabler--users] size-5"></span>
                            Members
                            <span class="badge badge-sm">{{ $idea->members->count() + 1 }}</span>
                        </h2>

                        <div class="space-y-2">
                            <!-- Creator -->
                            <div class="flex items-center gap-3 p-2 rounded-lg bg-base-200">
                                <div class="avatar">
                                    <div class="w-8 rounded-full">
                                        <img src="{{ $idea->creator->avatar_url }}" alt="{{ $idea->creator->name }}" />
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <p class="font-medium text-sm">{{ $idea->creator->name }}</p>
                                    <p class="text-xs text-base-content/50">Creator</p>
                                </div>
                            </div>

                            <!-- Members -->
                            @forelse($idea->members as $member)
                                <div class="flex items-center gap-3 p-2 rounded-lg hover:bg-base-200">
                                    <div class="avatar">
                                        <div class="w-8 rounded-full">
                                            <img src="{{ $member->avatar_url }}" alt="{{ $member->name }}" />
                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <p class="font-medium text-sm">{{ $member->name }}</p>
                                    </div>
                                </div>
                            @empty
                                <p class="text-base-content/60 text-sm">No additional members</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
