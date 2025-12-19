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

        <!-- Tab Navigation -->
        <div class="mb-6">
            <div class="tabs tabs-bordered" role="tablist">
                <button type="button"
                        class="tab tab-bordered tab-active"
                        id="tab-discussion-btn"
                        role="tab"
                        aria-selected="true"
                        onclick="switchTab('discussion')">
                    <span class="icon-[tabler--message-circle] size-4 mr-2"></span>
                    Discussion
                </button>
                <button type="button"
                        class="tab tab-bordered"
                        id="tab-tasks-btn"
                        role="tab"
                        aria-selected="false"
                        onclick="switchTab('tasks')">
                    <span class="icon-[tabler--subtask] size-4 mr-2"></span>
                    Linked Tasks
                    <span class="badge badge-sm ml-2" id="linked-tasks-count">{{ $discussion->tasks->count() }}</span>
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Discussion Tab Content -->
                <div id="tab-discussion-content">
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
                        @php
                            $topLevelComments = $discussion->comments()
                                ->whereNull('parent_id')
                                ->with(['user', 'replies.user', 'attachments', 'replies.attachments'])
                                ->orderBy('created_at', 'desc')
                                ->get();
                            $lastCommentTs = $topLevelComments->isNotEmpty()
                                ? $topLevelComments->first()->created_at->toIso8601String()
                                : now()->toIso8601String();
                        @endphp
                        <div id="discussion-comments-container"
                             class="space-y-4"
                             data-poll-url="{{ route('discussions.comments.poll', $discussion) }}"
                             data-last-ts="{{ $lastCommentTs }}">
                            @forelse($topLevelComments as $comment)
                                @include('discussion::partials.comment', ['comment' => $comment, 'discussion' => $discussion])
                            @empty
                                <p id="no-comments-message" class="text-base-content/60 text-center py-4">No comments yet. Start the conversation!</p>
                            @endforelse
                        </div>
                    </div>
                </div>
                </div><!-- End Discussion Tab Content -->

                <!-- Linked Tasks Tab Content -->
                <div id="tab-tasks-content" class="hidden">
                    <div class="card bg-base-100 shadow">
                        <div class="card-body">
                            <div class="flex items-center justify-between mb-4">
                                <h2 class="card-title text-lg">
                                    <span class="icon-[tabler--subtask] size-5"></span>
                                    Linked Tasks
                                </h2>
                                <button type="button" class="btn btn-primary btn-sm" onclick="openLinkTasksModal()">
                                    <span class="icon-[tabler--link] size-4"></span>
                                    Link Tasks
                                </button>
                            </div>

                            <!-- Linked Tasks List -->
                            <div id="linked-tasks-list" class="space-y-2">
                                @forelse($discussion->tasks()->with(['workspace', 'status'])->get() as $task)
                                    @php
                                        $statusColor = $task->status?->color ?? '#6b7280';
                                        $r = hexdec(substr($statusColor, 1, 2));
                                        $g = hexdec(substr($statusColor, 3, 2));
                                        $b = hexdec(substr($statusColor, 5, 2));
                                    @endphp
                                    <div class="flex items-center gap-3 p-3 rounded-lg border border-base-300 hover:bg-base-200 transition-colors"
                                         id="linked-task-{{ $task->id }}">
                                        <a href="{{ route('tasks.show', $task->uuid) }}" class="flex-1 flex items-center gap-3">
                                            <span class="badge badge-sm font-mono" style="background-color: rgba({{ $r }}, {{ $g }}, {{ $b }}, 0.15); color: {{ $statusColor }}; border: 1px solid rgba({{ $r }}, {{ $g }}, {{ $b }}, 0.3);">
                                                {{ ($task->workspace?->prefix ?? 'T') . '-' . $task->task_number }}
                                            </span>
                                            <span class="flex-1 font-medium truncate">{{ $task->title }}</span>
                                            @if($task->status)
                                                <span class="badge badge-sm" style="background-color: rgba({{ $r }}, {{ $g }}, {{ $b }}, 0.15); color: {{ $statusColor }};">
                                                    {{ $task->status->name }}
                                                </span>
                                            @endif
                                            @if($task->workspace)
                                                <span class="text-xs text-base-content/50">{{ $task->workspace->name }}</span>
                                            @endif
                                        </a>
                                        <button type="button"
                                                class="btn btn-ghost btn-xs text-error"
                                                onclick="openUnlinkTaskModal({{ $task->id }}, '{{ $task->uuid }}', '{{ addslashes($task->title) }}')"
                                                title="Unlink task">
                                            <span class="icon-[tabler--link-off] size-4"></span>
                                        </button>
                                    </div>
                                @empty
                                    <div id="no-linked-tasks-message" class="text-center py-8 text-base-content/60">
                                        <span class="icon-[tabler--subtask] size-12 mx-auto mb-3 opacity-30"></span>
                                        <p>No tasks linked to this discussion yet.</p>
                                        <p class="text-sm mt-1">Click "Link Tasks" to add existing tasks.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div><!-- End Linked Tasks Tab Content -->
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

{{-- Link Tasks Modal --}}
@include('discussion::partials.link-tasks-modal', ['discussion' => $discussion, 'workspaces' => $workspaces])

{{-- Unlink Task Confirmation Modal --}}
@include('discussion::partials.unlink-task-modal', ['discussion' => $discussion])

@endsection

@push('scripts')
<script>
/**
 * Tab Switching
 */
function switchTab(tab) {
    const discussionBtn = document.getElementById('tab-discussion-btn');
    const tasksBtn = document.getElementById('tab-tasks-btn');
    const discussionContent = document.getElementById('tab-discussion-content');
    const tasksContent = document.getElementById('tab-tasks-content');

    if (tab === 'discussion') {
        discussionBtn.classList.add('tab-active');
        discussionBtn.setAttribute('aria-selected', 'true');
        tasksBtn.classList.remove('tab-active');
        tasksBtn.setAttribute('aria-selected', 'false');
        discussionContent.classList.remove('hidden');
        tasksContent.classList.add('hidden');
    } else {
        tasksBtn.classList.add('tab-active');
        tasksBtn.setAttribute('aria-selected', 'true');
        discussionBtn.classList.remove('tab-active');
        discussionBtn.setAttribute('aria-selected', 'false');
        tasksContent.classList.remove('hidden');
        discussionContent.classList.add('hidden');
    }
}

/**
 * Real-time Discussion Comments Polling
 */
(function initDiscussionCommentPolling() {
    const container = document.getElementById('discussion-comments-container');
    if (!container) {
        console.log('Discussion comments container not found');
        return;
    }

    const pollUrl = container.dataset.pollUrl;
    let lastTs = container.dataset.lastTs || '';
    const POLL_INTERVAL = 5000; // 5 seconds
    let isPolling = false;

    console.log('Discussion comment polling initialized', { pollUrl, lastTs });

    async function pollComments() {
        if (isPolling) return;
        isPolling = true;

        try {
            const url = `${pollUrl}?last_ts=${encodeURIComponent(lastTs)}`;
            console.log('Polling comments:', url);
            const response = await fetch(url, {
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                }
            });

            if (!response.ok) {
                throw new Error('Network response was not ok');
            }

            const data = await response.json();
            console.log('Poll response:', {
                newComments: data.comments?.length || 0,
                updatedComments: data.updated_comments?.length || 0,
                count: data.count,
                last_ts: data.last_ts
            });

            // Update last timestamp
            if (data.last_ts) {
                lastTs = data.last_ts;
                container.dataset.lastTs = lastTs;
            }

            // Append new top-level comments
            if (data.comments && data.comments.length > 0) {
                console.log('New comments found:', data.comments.length);
                // Remove "No comments" message if present
                const noCommentsMsg = document.getElementById('no-comments-message');
                if (noCommentsMsg) {
                    noCommentsMsg.remove();
                }

                data.comments.forEach(comment => {
                    // Check if comment already exists (prevent duplicates)
                    if (!document.getElementById(`discussion-comment-${comment.id}`)) {
                        // Create a temporary container to parse the HTML
                        const temp = document.createElement('div');
                        temp.innerHTML = comment.html;

                        // Prepend the comment at the top with a fade-in animation (newest first)
                        const commentEl = temp.firstElementChild;
                        if (commentEl) {
                            commentEl.style.opacity = '0';
                            commentEl.style.transition = 'opacity 0.3s ease-in';
                            container.prepend(commentEl);

                            // Trigger fade-in
                            requestAnimationFrame(() => {
                                commentEl.style.opacity = '1';
                            });

                            // Initialize any Quill editors in the new comment
                            initNewCommentEditors(commentEl);
                        }
                    }
                });
            }

            // Update existing comments that have new replies
            if (data.updated_comments && data.updated_comments.length > 0) {
                console.log('Updated comments with new replies:', data.updated_comments.length);

                data.updated_comments.forEach(comment => {
                    const existingComment = document.getElementById(`discussion-comment-${comment.id}`);
                    if (existingComment) {
                        // Create a temporary container to parse the HTML
                        const temp = document.createElement('div');
                        temp.innerHTML = comment.html;
                        const newCommentEl = temp.firstElementChild;

                        if (newCommentEl) {
                            // Replace the existing comment with the updated one
                            existingComment.replaceWith(newCommentEl);

                            // Brief highlight animation for the new reply
                            newCommentEl.style.transition = 'background-color 0.5s ease-out';
                            newCommentEl.style.backgroundColor = 'rgba(var(--color-primary), 0.1)';
                            setTimeout(() => {
                                newCommentEl.style.backgroundColor = '';
                            }, 1000);

                            // Initialize any Quill editors in the updated comment
                            initNewCommentEditors(newCommentEl);
                        }
                    }
                });
            }

            // Update comment count badge
            if (data.count !== undefined) {
                updateCommentCount(data.count);
            }

            // Schedule next poll
            setTimeout(pollComments, POLL_INTERVAL);
        } catch (error) {
            console.error('Error polling comments:', error);
            // Retry after interval even on error
            setTimeout(pollComments, POLL_INTERVAL * 2); // Double the interval on error
        } finally {
            isPolling = false;
        }
    }

    function updateCommentCount(count) {
        // Update the badge in the Comments header
        const badge = document.querySelector('.card-title .badge');
        if (badge && count !== undefined) {
            badge.textContent = count;
        }
    }

    function initNewCommentEditors(commentEl) {
        // Initialize any Quill mini editors that might be in the comment
        // This is needed for edit/reply forms in dynamically added comments
        if (typeof HSSelect !== 'undefined') {
            HSSelect.autoInit();
        }
    }

    // Start polling after a short delay
    setTimeout(pollComments, POLL_INTERVAL);
})();
</script>
@endpush
