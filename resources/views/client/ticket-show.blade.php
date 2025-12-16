<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Ticket #{{ $task->task_number }} - {{ $task->workspace->name }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    <!-- Styles / Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-base-200">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-base-100 border-b border-base-200 sticky top-0 z-10">
            <div class="max-w-4xl mx-auto px-4 py-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center text-white" style="background-color: {{ $task->workspace->color ?? '#3b82f6' }}">
                        <span class="icon-[tabler--ticket] size-5"></span>
                    </div>
                    <div>
                        <h1 class="font-semibold text-base-content">{{ $task->workspace->name }}</h1>
                        <p class="text-sm text-base-content/60">Support Portal</p>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-4xl mx-auto px-4 py-6">
            <!-- Success Message -->
            @if(session('success'))
            <div class="alert alert-success mb-6">
                <span class="icon-[tabler--check] size-5"></span>
                <span>{{ session('success') }}</span>
            </div>
            @endif

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
                            <span class="badge" style="background-color: {{ $task->status->color }}20; color: {{ $task->status->color }}; border-color: {{ $task->status->color }}40;">
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
                        @if($task->department)
                        <div>
                            <p class="text-xs text-base-content/60 mb-1">Department</p>
                            <p class="font-medium text-sm">{{ $task->department->name }}</p>
                        </div>
                        @endif
                        @if($task->assignee)
                        <div>
                            <p class="text-xs text-base-content/60 mb-1">Assigned To</p>
                            <p class="font-medium text-sm">{{ $task->assignee->name }}</p>
                        </div>
                        @endif
                        <div>
                            <p class="text-xs text-base-content/60 mb-1">Status</p>
                            <p class="font-medium text-sm">{{ $task->status?->name ?? 'Open' }}</p>
                        </div>
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
                        <form action="{{ route('client.ticket.reply', $task->uuid) }}" method="POST">
                            @csrf
                            <input type="hidden" name="token" value="{{ $token }}">

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
                            <span>This ticket has been closed. If you need further assistance, please create a new ticket.</span>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="py-6 text-center text-sm text-base-content/50">
            <p>&copy; {{ date('Y') }} {{ $task->workspace->name }}. Powered by {{ config('app.name', 'NewDone') }}.</p>
        </footer>
    </div>
</body>
</html>
