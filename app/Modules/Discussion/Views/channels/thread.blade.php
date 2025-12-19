@extends('layouts.app')

@section('content')
<div class="flex min-h-[calc(100vh-4rem)]">
    @include('discussion::channels.partials.sidebar')

    <!-- Main Content Area -->
    <main class="flex-1 min-w-0 flex flex-col bg-base-100">
        <!-- Header -->
        <div class="border-b border-base-200 px-4 md:px-6 py-2 sticky top-16 z-20 bg-base-100 overflow-visible">
            <!-- Breadcrumb -->
            <div class="flex items-center gap-1 text-xs text-base-content/60 mb-1">
                <a href="{{ route('discussions.index') }}" class="hover:text-primary">Discussions</a>
                <span class="icon-[tabler--chevron-right] size-3"></span>
                <a href="{{ route('channels.index') }}" class="hover:text-primary">Channels</a>
                <span class="icon-[tabler--chevron-right] size-3"></span>
                <a href="{{ route('channels.show', $channel) }}" class="hover:text-primary">{{ $channel->name }}</a>
                <span class="icon-[tabler--chevron-right] size-3"></span>
                <span class="text-base-content truncate max-w-[200px]">{{ $thread->title }}</span>
            </div>
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-lg {{ $channel->color_class }} flex items-center justify-center flex-shrink-0">
                    <span class="icon-[tabler--message] size-5"></span>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        @if($thread->is_pinned)
                        <span class="icon-[tabler--pin-filled] size-4 text-warning" title="Pinned"></span>
                        @endif
                        <h1 class="text-lg font-bold text-base-content truncate">{{ $thread->title }}</h1>
                    </div>
                    <p class="text-sm text-base-content/60">{{ $channel->tag }} • {{ $thread->creator->name }} • {{ $thread->created_at->diffForHumans() }}</p>
                </div>

                <!-- Thread Actions -->
                @if($thread->canEdit($user) || $thread->canPin($user))
                <div class="dropdown relative inline-flex [--auto-close:inside] [--offset:8] [--placement:bottom-end]">
                    <button id="thread-actions-dropdown" type="button" class="dropdown-toggle btn btn-ghost btn-sm btn-square" aria-haspopup="menu" aria-expanded="false" aria-label="Thread actions">
                        <span class="icon-[tabler--dots-vertical] size-5"></span>
                    </button>
                    <ul class="dropdown-menu dropdown-open:opacity-100 hidden min-w-48 shadow-lg" role="menu" aria-orientation="vertical" aria-labelledby="thread-actions-dropdown">
                        @if($thread->canPin($user))
                        <li>
                            <form action="{{ route('channels.threads.toggle-pin', [$channel, $thread]) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="dropdown-item w-full text-left">
                                    <span class="icon-[tabler--{{ $thread->is_pinned ? 'pinned-off' : 'pin' }}] size-4"></span>
                                    {{ $thread->is_pinned ? 'Unpin' : 'Pin' }} Thread
                                </button>
                            </form>
                        </li>
                        @endif
                        @if($thread->canDelete($user))
                        <li>
                            <button type="button" onclick="openModal('deleteThreadModal')" class="dropdown-item text-error w-full text-left">
                                <span class="icon-[tabler--trash] size-4"></span>
                                Delete Thread
                            </button>
                        </li>
                        @endif
                    </ul>
                </div>
                @endif
            </div>
        </div>

        <!-- Content Area -->
        <div class="flex-1 p-4 md:p-6 pt-3 overflow-y-auto">
            <div class="max-w-3xl mx-auto">
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

                <!-- Thread Content -->
                @if($thread->content)
                <div class="card bg-base-100 shadow mb-6">
                    <div class="card-body">
                        <div class="flex items-start gap-4">
                            <div class="avatar placeholder flex-shrink-0">
                                @if($thread->creator->avatar_url)
                                <div class="w-10 h-10 rounded-full">
                                    <img src="{{ $thread->creator->avatar_url }}" alt="{{ $thread->creator->name }}" />
                                </div>
                                @else
                                <div class="w-10 h-10 rounded-full bg-primary/10 text-primary">
                                    <span>{{ substr($thread->creator->first_name, 0, 1) }}{{ substr($thread->creator->last_name, 0, 1) }}</span>
                                </div>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="font-medium">{{ $thread->creator->name }}</span>
                                    <span class="text-xs text-base-content/50">{{ $thread->created_at->format('M d, Y \a\t g:i A') }}</span>
                                </div>
                                <div class="prose prose-sm max-w-none">
                                    {!! $thread->content !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Attached Tasks -->
                @if($thread->tasks->isNotEmpty())
                <div class="card bg-base-100 shadow mb-6 border-l-4 border-primary">
                    <div class="card-body py-4">
                        <div class="flex items-center gap-2 text-xs text-base-content/60 mb-3">
                            <span class="icon-[tabler--link] size-3"></span>
                            <span>Linked Tasks ({{ $thread->tasks->count() }})</span>
                        </div>
                        <div class="space-y-3">
                            @foreach($thread->tasks as $linkedTask)
                            <div class="flex items-center gap-3 p-2 rounded-lg hover:bg-base-200 transition-colors">
                                <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center flex-shrink-0">
                                    <span class="icon-[tabler--subtask] size-4 text-primary"></span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <a href="{{ route('tasks.show', $linkedTask) }}" class="font-medium text-sm text-primary hover:underline block truncate">
                                        {{ $linkedTask->workspace?->prefix }}-{{ $linkedTask->task_number }}: {{ $linkedTask->title }}
                                    </a>
                                    <div class="flex items-center gap-2 mt-0.5 text-xs text-base-content/50">
                                        @if($linkedTask->status)
                                        @php
                                            $statusColor = $linkedTask->status->color ?? '#6b7280';
                                            $r = hexdec(substr($statusColor, 1, 2));
                                            $g = hexdec(substr($statusColor, 3, 2));
                                            $b = hexdec(substr($statusColor, 5, 2));
                                        @endphp
                                        <span class="badge badge-xs font-medium" style="background-color: rgba({{ $r }}, {{ $g }}, {{ $b }}, 0.15); color: {{ $statusColor }}; border-color: rgba({{ $r }}, {{ $g }}, {{ $b }}, 0.3);">
                                            {{ $linkedTask->status->name }}
                                        </span>
                                        @endif
                                        @if($linkedTask->workspace)
                                        <span>{{ $linkedTask->workspace->name }}</span>
                                        @endif
                                    </div>
                                </div>
                                <a href="{{ route('tasks.show', $linkedTask) }}" class="btn btn-ghost btn-xs">
                                    <span class="icon-[tabler--external-link] size-3"></span>
                                </a>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                <!-- Replies Section -->
                @php
                    $lastReply = $thread->allReplies()->orderBy('created_at', 'desc')->first();
                    $lastReplyTs = $lastReply ? $lastReply->created_at->format('Y-m-d\TH:i:s.u\Z') : now()->format('Y-m-d\TH:i:s.u\Z');
                @endphp
                <div class="mb-6">
                    <h2 class="text-lg font-semibold mb-4 flex items-center gap-2">
                        <span class="icon-[tabler--messages] size-5"></span>
                        Replies
                        <span id="replies-count-badge" class="badge badge-ghost {{ $thread->replies_count > 0 ? '' : 'hidden' }}">{{ $thread->replies_count }}</span>
                    </h2>

                    <div id="thread-replies-container"
                         class="space-y-3"
                         data-poll-url="{{ route('channels.threads.replies.poll', [$channel, $thread]) }}"
                         data-last-ts="{{ $lastReplyTs }}">
                        @if($thread->replies->isEmpty())
                        <div id="no-replies-message" class="card bg-base-100 shadow">
                            <div class="card-body text-center py-8">
                                <div class="text-base-content/50">
                                    <span class="icon-[tabler--message-2] size-8 block mx-auto mb-2"></span>
                                    <p>No replies yet. Be the first to respond!</p>
                                </div>
                            </div>
                        </div>
                        @else
                        @foreach($thread->replies as $reply)
                        @include('discussion::channels.partials.reply', ['reply' => $reply, 'channel' => $channel, 'thread' => $thread])
                        @endforeach
                        @endif
                    </div>
                </div>

                <!-- Reply Form -->
                @if($thread->canReply($user))
                <div class="card bg-base-100 shadow">
                    <div class="card-body">
                        <h3 class="font-semibold mb-3">Add a Reply</h3>
                        <form action="{{ route('channels.threads.replies.store', [$channel, $thread]) }}" method="POST">
                            @csrf
                            <div class="space-y-4">
                                <x-quill-editor
                                    name="content"
                                    id="reply-content"
                                    :value="old('content')"
                                    placeholder="Write your reply..."
                                    height="150px"
                                />
                                <div class="flex justify-end">
                                    <button type="submit" class="btn btn-primary">
                                        <span class="icon-[tabler--send] size-4"></span>
                                        Post Reply
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </main>
</div>

<!-- Delete Thread Confirmation Modal -->
@if($thread->canDelete($user))
<div id="deleteThreadModal" class="channel-modal">
    <div class="channel-modal-backdrop" onclick="closeModal('deleteThreadModal')"></div>
    <div class="channel-modal-box bg-base-100 rounded-lg shadow-xl p-6 max-w-md w-full mx-4">
        <button type="button" onclick="closeModal('deleteThreadModal')" class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">
            <span class="icon-[tabler--x] size-5"></span>
        </button>
        <div class="text-center mb-4">
            <div class="w-16 h-16 rounded-full bg-error/10 flex items-center justify-center mx-auto mb-4">
                <span class="icon-[tabler--trash] size-8 text-error"></span>
            </div>
            <h3 class="font-bold text-lg">Delete Thread</h3>
            <p class="text-base-content/60 mt-2">Are you sure you want to delete <strong>{{ $thread->title }}</strong>?</p>
            <p class="text-sm text-error mt-2">This action cannot be undone. All replies will be permanently deleted.</p>
        </div>
        <div class="flex justify-center gap-3 mt-6">
            <button type="button" onclick="closeModal('deleteThreadModal')" class="btn btn-ghost">Cancel</button>
            <form action="{{ route('channels.threads.destroy', [$channel, $thread]) }}" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-error">
                    <span class="icon-[tabler--trash] size-4"></span>
                    Delete Thread
                </button>
            </form>
        </div>
    </div>
</div>
@endif

<style>
.channel-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 9999;
    justify-content: center;
    align-items: center;
}
.channel-modal.open {
    display: flex !important;
}
.channel-modal-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1;
}
.channel-modal-box {
    position: relative;
    z-index: 2;
    max-height: 90vh;
    overflow-y: auto;
}
</style>

<script>
function openModal(modalId) {
    document.getElementById(modalId).classList.add('open');
    document.body.style.overflow = 'hidden';
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('open');
    document.body.style.overflow = '';
}

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.channel-modal.open').forEach(function(modal) {
            modal.classList.remove('open');
        });
        document.body.style.overflow = '';
    }
});

// Real-time reply polling
(function initThreadReplyPolling() {
    const container = document.getElementById('thread-replies-container');
    if (!container) return;

    const pollUrl = container.dataset.pollUrl;
    let lastTs = container.dataset.lastTs || '';
    const POLL_INTERVAL = 5000; // 5 seconds
    let isPolling = false;

    async function pollReplies() {
        if (isPolling) return;
        isPolling = true;

        try {
            const url = `${pollUrl}?last_ts=${encodeURIComponent(lastTs)}`;
            const response = await fetch(url, {
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                }
            });

            if (!response.ok) {
                throw new Error('Poll request failed');
            }

            const data = await response.json();

            // Update last timestamp
            if (data.last_ts) {
                lastTs = data.last_ts;
                container.dataset.lastTs = lastTs;
            }

            // Remove "no replies" message if we have new replies
            if (data.replies && data.replies.length > 0) {
                const noRepliesMsg = document.getElementById('no-replies-message');
                if (noRepliesMsg) {
                    noRepliesMsg.remove();
                }
            }

            // Append new replies with animation
            if (data.replies && data.replies.length > 0) {
                data.replies.forEach(reply => {
                    // Check if reply already exists
                    if (!document.getElementById(`thread-reply-${reply.uuid}`)) {
                        const temp = document.createElement('div');
                        temp.innerHTML = reply.html;
                        const replyEl = temp.firstElementChild;
                        if (replyEl) {
                            replyEl.style.opacity = '0';
                            replyEl.style.transform = 'translateY(-10px)';
                            replyEl.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                            container.appendChild(replyEl);
                            // Trigger animation
                            requestAnimationFrame(() => {
                                replyEl.style.opacity = '1';
                                replyEl.style.transform = 'translateY(0)';
                            });
                        }
                    }
                });
            }

            // Update existing replies that have new nested replies
            if (data.updated_replies && data.updated_replies.length > 0) {
                data.updated_replies.forEach(reply => {
                    const existingReply = document.getElementById(`thread-reply-${reply.uuid}`);
                    if (existingReply) {
                        const temp = document.createElement('div');
                        temp.innerHTML = reply.html;
                        const newReplyEl = temp.firstElementChild;
                        if (newReplyEl) {
                            existingReply.replaceWith(newReplyEl);
                            // Brief highlight animation
                            newReplyEl.style.backgroundColor = 'rgba(var(--color-primary), 0.1)';
                            newReplyEl.style.transition = 'background-color 1s ease';
                            setTimeout(() => {
                                newReplyEl.style.backgroundColor = '';
                            }, 1000);
                        }
                    }
                });
            }

            // Update reply count badge
            if (data.count !== undefined) {
                updateReplyCount(data.count);
            }

        } catch (error) {
            console.error('Reply polling error:', error);
        } finally {
            isPolling = false;
            // Schedule next poll
            setTimeout(pollReplies, POLL_INTERVAL);
        }
    }

    function updateReplyCount(count) {
        const badge = document.getElementById('replies-count-badge');
        if (badge) {
            badge.textContent = count;
            if (count > 0) {
                badge.classList.remove('hidden');
            } else {
                badge.classList.add('hidden');
            }
        }
    }

    // Start polling after a short delay
    setTimeout(pollReplies, POLL_INTERVAL);
})();
</script>
@endsection
