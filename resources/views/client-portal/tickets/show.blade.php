@extends('client-portal.layouts.app')

@section('title', 'Ticket #' . $task->task_number)

@section('content')
<!-- Breadcrumb -->
<div class="text-sm breadcrumbs mb-4">
    <ul>
        <li><a href="{{ route('client-portal.dashboard') }}">Dashboard</a></li>
        <li>Ticket #{{ $task->task_number }}</li>
    </ul>
</div>

<!-- Ticket Card -->
<div class="card bg-base-100 shadow mb-6">
    <div class="card-body">
        <!-- Ticket Header -->
        <div class="flex flex-wrap items-start justify-between gap-4 mb-4">
            <div>
                <div class="flex items-center gap-2 text-sm text-base-content/60 mb-1">
                    <span>Ticket #{{ $task->task_number }}</span>
                    <span>&bull;</span>
                    <span>{{ $task->created_at->format('M d, Y \a\t g:i A') }}</span>
                </div>
                <h2 class="text-xl font-semibold text-base-content">{{ $task->title }}</h2>
            </div>
            <div class="flex flex-wrap gap-2">
                @if($task->status)
                <span class="badge" style="background-color: {{ $task->status->background_color }}; color: {{ $task->status->text_color }};">
                    {{ $task->status->name }}
                </span>
                @endif
                @if($task->workspacePriority)
                <span class="badge" style="background-color: {{ $task->workspacePriority->color }}20; color: {{ $task->workspacePriority->color }}; border-color: {{ $task->workspacePriority->color }}40;">
                    {{ $task->workspacePriority->name }}
                </span>
                @endif
            </div>
        </div>

        <!-- Ticket Details -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 py-4 border-y border-base-200">
            <div>
                <p class="text-xs text-base-content/60 mb-1">Workspace</p>
                <p class="font-medium text-sm">{{ $task->workspace->name }}</p>
            </div>
            @if($task->department)
            <div>
                <p class="text-xs text-base-content/60 mb-1">Department</p>
                <p class="font-medium text-sm">{{ $task->department->name }}</p>
            </div>
            @endif
            @if($task->assignee)
            <div>
                <p class="text-xs text-base-content/60 mb-1">Assigned To</p>
                <div class="flex items-center gap-2">
                    <div class="avatar">
                        <div class="w-5 h-5 rounded-full">
                            <img src="{{ $task->assignee->avatar_url }}" alt="{{ $task->assignee->name }}" />
                        </div>
                    </div>
                    <span class="font-medium text-sm">{{ $task->assignee->name }}</span>
                </div>
            </div>
            @endif
            <div>
                <p class="text-xs text-base-content/60 mb-1">Last Updated</p>
                <p class="font-medium text-sm">{{ $task->updated_at->diffForHumans() }}</p>
            </div>
        </div>

        <!-- Description -->
        @if($task->description)
        <div class="mt-4">
            <h3 class="text-sm font-medium text-base-content/60 mb-2">Description</h3>
            <div class="prose prose-sm max-w-none">
                {!! $task->description !!}
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Comments Section -->
<div class="card bg-base-100 shadow">
    <div class="card-body">
        <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
            <span class="icon-[tabler--messages] size-5 text-primary"></span>
            Conversation
            <span class="badge badge-sm badge-ghost">{{ $task->comments->count() }}</span>
        </h3>

        <!-- Comments List -->
        <div class="space-y-4 mb-6">
            @forelse($task->comments as $comment)
            <div class="flex gap-3 {{ $comment->user && !$comment->user->is_guest ? 'bg-primary/5 -mx-4 px-4 py-3 rounded-lg' : '' }}">
                <div class="avatar">
                    <div class="w-8 h-8 rounded-full bg-base-200 flex items-center justify-center">
                        @if($comment->user)
                        <img src="{{ $comment->user->avatar_url }}" alt="{{ $comment->user->name }}" class="rounded-full" />
                        @else
                        <span class="icon-[tabler--user] size-4 text-base-content/50"></span>
                        @endif
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="font-medium text-sm">
                            {{ $comment->user?->name ?? 'Customer' }}
                        </span>
                        @if($comment->user && !$comment->user->is_guest)
                        <span class="badge badge-xs badge-primary">Support</span>
                        @endif
                        <span class="text-xs text-base-content/50">
                            {{ $comment->created_at->diffForHumans() }}
                        </span>
                    </div>
                    <div class="prose prose-sm max-w-none text-base-content/80">
                        {!! $comment->content !!}
                    </div>

                    <!-- Replies -->
                    @if($comment->replies && $comment->replies->count() > 0)
                    <div class="mt-3 ml-4 pl-4 border-l-2 border-base-200 space-y-3">
                        @foreach($comment->replies as $reply)
                        <div class="flex gap-3 {{ $reply->user && !$reply->user->is_guest ? 'bg-primary/5 -ml-4 pl-4 pr-3 py-2 rounded-r-lg' : '' }}">
                            <div class="avatar">
                                <div class="w-6 h-6 rounded-full bg-base-200 flex items-center justify-center">
                                    @if($reply->user)
                                    <img src="{{ $reply->user->avatar_url }}" alt="{{ $reply->user->name }}" class="rounded-full" />
                                    @else
                                    <span class="icon-[tabler--user] size-3 text-base-content/50"></span>
                                    @endif
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="font-medium text-xs">{{ $reply->user?->name ?? 'Customer' }}</span>
                                    @if($reply->user && !$reply->user->is_guest)
                                    <span class="badge badge-xs badge-primary">Support</span>
                                    @endif
                                    <span class="text-xs text-base-content/50">{{ $reply->created_at->diffForHumans() }}</span>
                                </div>
                                <div class="prose prose-sm max-w-none text-base-content/80 text-sm">
                                    {!! $reply->content !!}
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
            @empty
            <div class="text-center py-8 text-base-content/50">
                <span class="icon-[tabler--message-off] size-10 mb-2"></span>
                <p>No messages yet. Start the conversation below.</p>
            </div>
            @endforelse
        </div>

        <!-- Reply Form -->
        @if(!$task->isClosed())
        <div class="border-t border-base-200 pt-4">
            <h4 class="text-sm font-medium mb-3">Add a Reply</h4>
            <form action="{{ route('client-portal.tickets.reply', $task->uuid) }}" method="POST">
                @csrf

                <div class="form-control mb-3">
                    <textarea
                        name="content"
                        rows="4"
                        class="textarea textarea-bordered w-full @error('content') textarea-error @enderror"
                        placeholder="Type your message here..."
                        required
                    >{{ old('content') }}</textarea>
                    @error('content')
                    <label class="label">
                        <span class="label-text-alt text-error">{{ $message }}</span>
                    </label>
                    @enderror
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="btn btn-primary gap-2">
                        <span class="icon-[tabler--send] size-4"></span>
                        Send Reply
                    </button>
                </div>
            </form>
        </div>
        @else
        <div class="border-t border-base-200 pt-4">
            <div class="alert alert-info">
                <span class="icon-[tabler--info-circle] size-5"></span>
                <span>This ticket has been closed. If you need further assistance, please <a href="{{ route('client-portal.tickets.create') }}" class="link">create a new ticket</a>.</span>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
