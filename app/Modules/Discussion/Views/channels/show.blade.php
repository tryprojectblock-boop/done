@extends('layouts.app')

@section('content')
<div class="flex min-h-[calc(100vh-4rem)]">
    @include('discussion::channels.partials.sidebar')

    <!-- Main Content Area - Full Width -->
    <main class="flex-1 min-w-0 flex flex-col bg-base-100">
        <!-- Channel Header - Sticky -->
        <div class="border-b border-base-200 px-4 md:px-6 py-2 sticky top-16 z-20 bg-base-100 overflow-visible">
            <!-- Breadcrumb -->
            <div class="flex items-center gap-1 text-xs text-base-content/60 mb-1">
                <a href="{{ route('discussions.index') }}" class="hover:text-primary">Discussions</a>
                <span class="icon-[tabler--chevron-right] size-3"></span>
                <a href="{{ route('channels.index') }}" class="hover:text-primary">Channels</a>
                <span class="icon-[tabler--chevron-right] size-3"></span>
                <span class="text-base-content">{{ $channel->name }}</span>
            </div>
            <div class="flex items-center gap-4">
                <!-- Channel Icon -->
                <div class="w-10 h-10 rounded-lg {{ $channel->color_class }} flex items-center justify-center flex-shrink-0">
                    <span class="icon-[tabler--hash] size-5"></span>
                </div>

                <!-- Channel Info -->
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <h1 class="text-lg font-bold text-base-content truncate">{{ $channel->name }}</h1>
                        <span class="badge {{ $channel->badge_class }} badge-sm">{{ $channel->tag }}</span>
                        <span class="badge {{ $channel->status_badge_class }} badge-sm">{{ $channel->status_label }}</span>
                    </div>
                    @if($channel->description)
                    <p class="text-sm text-base-content/60 truncate hidden sm:block">{{ $channel->description }}</p>
                    @endif
                </div>

                <!-- Actions -->
                <div class="flex items-center gap-2">
                    <!-- Members Avatars with Click to View All -->
                    @if($channel->members->count() > 0)
                    <div class="hidden md:flex items-center">
                        <button type="button" onclick="openModal('membersModal')" class="flex items-center gap-1 hover:bg-base-200 rounded-lg px-2 py-1 transition-colors">
                            <div class="avatar-group -space-x-3">
                                @foreach($channel->members->take(5) as $member)
                                <div class="avatar border-2 border-base-100">
                                    @if($member->avatar_url)
                                    <div class="w-8 h-8">
                                        <img src="{{ $member->avatar_url }}" alt="{{ $member->name }}" />
                                    </div>
                                    @else
                                    <div class="w-8 h-8 bg-primary/10 text-primary text-xs flex items-center justify-center">
                                        {{ substr($member->first_name, 0, 1) }}{{ substr($member->last_name, 0, 1) }}
                                    </div>
                                    @endif
                                </div>
                                @endforeach
                                @if($channel->members->count() > 5)
                                <div class="avatar placeholder border-2 border-base-100">
                                    <div class="w-8 h-8 bg-base-300 text-base-content text-xs">
                                        <span>+{{ $channel->members->count() - 5 }}</span>
                                    </div>
                                </div>
                                @endif
                            </div>
                            <span class="icon-[tabler--chevron-down] size-4 text-base-content/50"></span>
                        </button>
                    </div>
                    @endif

                    <!-- Manage Members (visible to channel managers) -->
                    @if($channel->canManage($user))
                    <a href="{{ route('channels.members', $channel) }}" class="hidden md:flex items-center gap-1 btn btn-ghost btn-sm" title="Manage channel members">
                        <span class="icon-[tabler--user-plus] size-5"></span>
                        <span class="hidden lg:inline">Invite</span>
                    </a>
                    @endif

                    <!-- Add Thread Button - Links to new page -->
                    @if($channel->canPost($user))
                    <a href="{{ route('channels.threads.create', $channel) }}" class="btn btn-primary btn-sm gap-2">
                        <span class="icon-[tabler--plus] size-4"></span>
                        <span class="hidden sm:inline">Add Thread</span>
                    </a>
                    @endif

                    <!-- More Actions Dropdown -->
                    @if($channel->canManage($user) || ($channel->isMember($user) && $user->id !== $channel->created_by))
                    <div class="dropdown relative inline-flex [--auto-close:inside] [--offset:8] [--placement:bottom-end]">
                        <button id="channel-actions-dropdown" type="button" class="dropdown-toggle btn btn-ghost btn-sm btn-square" aria-haspopup="menu" aria-expanded="false" aria-label="Channel actions">
                            <span class="icon-[tabler--dots-vertical] size-5"></span>
                        </button>
                        <ul class="dropdown-menu dropdown-open:opacity-100 hidden min-w-48 shadow-lg" role="menu" aria-orientation="vertical" aria-labelledby="channel-actions-dropdown">
                            @if($channel->canManage($user))
                            <li>
                                <a class="dropdown-item" href="{{ route('channels.edit', $channel) }}">
                                    <span class="icon-[tabler--edit] size-4"></span>
                                    Edit Channel
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item flex items-center gap-2" href="{{ route('channels.members', $channel) }}">
                                    <span class="icon-[tabler--users] size-4"></span>
                                    Manage Members
                                </a>
                            </li>
                            <li>
                                <button type="button" onclick="openModal('deleteChannelModal')" class="dropdown-item text-error w-full text-left">
                                    <span class="icon-[tabler--trash] size-4"></span>
                                    Delete Channel
                                </button>
                            </li>
                            @endif
                            @if($channel->isMember($user) && $user->id !== $channel->created_by)
                            <li>
                                <form action="{{ route('channels.leave', $channel) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-warning w-full text-left">
                                        <span class="icon-[tabler--logout] size-4"></span>
                                        Leave Channel
                                    </button>
                                </form>
                            </li>
                            @endif
                        </ul>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Content Area -->
        <div class="flex-1 p-4 md:p-6 pt-3">
            @if($threads->isEmpty())
            <!-- Member but no threads -->
            <div class="card bg-base-100 shadow">
                <div class="card-body text-center py-12">
                    <div class="text-base-content/50">
                        <span class="icon-[tabler--message-2] size-16 block mx-auto mb-4 opacity-50"></span>
                        <p class="text-xl font-medium mb-2">No threads yet</p>
                        <p class="text-sm mb-6">Start the conversation in {{ $channel->tag }}</p>
                        @if($channel->canPost($user))
                        <a href="{{ route('channels.threads.create', $channel) }}" class="btn btn-primary">
                            <span class="icon-[tabler--plus] size-5"></span>
                            Create First Thread
                        </a>
                        @endif
                    </div>
                </div>
            </div>
            @else
            <!-- Threads List -->
            <div class="space-y-2">
                @foreach($threads as $thread)
                <a href="{{ route('channels.threads.show', [$channel, $thread]) }}" class="card bg-base-100 shadow hover:shadow-lg transition-all block">
                    <div class="card-body p-4">
                        <div class="flex items-start gap-4">
                            <!-- Author Avatar -->
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

                            <!-- Thread Content -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    @if($thread->is_pinned)
                                    <span class="icon-[tabler--pin-filled] size-4 text-warning" title="Pinned"></span>
                                    @endif
                                    <h3 class="font-semibold text-base-content">{{ $thread->title }}</h3>
                                </div>
                                @if($thread->content)
                                <p class="text-sm text-base-content/60 line-clamp-2 mb-2">{{ Str::limit(strip_tags($thread->content), 200) }}</p>
                                @endif
                                <div class="flex items-center gap-3 text-xs text-base-content/50">
                                    <span>{{ $thread->creator->name }}</span>
                                    <span>•</span>
                                    <span>{{ $thread->created_at->diffForHumans() }}</span>
                                    @if($thread->all_replies_count > 0)
                                    <span>•</span>
                                    <span class="flex items-center gap-1">
                                        <span class="icon-[tabler--message] size-3"></span>
                                        {{ $thread->all_replies_count }} {{ Str::plural('reply', $thread->all_replies_count) }}
                                    </span>
                                    @endif
                                </div>
                            </div>

                            <!-- Arrow -->
                            <span class="icon-[tabler--chevron-right] size-5 text-base-content/30 flex-shrink-0"></span>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>

            <!-- Pagination -->
            @if($threads->hasPages())
            <div class="mt-4">
                {{ $threads->links() }}
            </div>
            @endif
            @endif
        </div>
    </main>
</div>

<!-- Members Modal -->
@if($channel->members->count() > 0)
<div id="membersModal" class="channel-modal">
    <div class="channel-modal-backdrop" onclick="closeModal('membersModal')"></div>
    <div class="channel-modal-box bg-base-100 rounded-lg shadow-xl p-6 max-w-md w-full mx-4">
        <button type="button" onclick="closeModal('membersModal')" class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">
            <span class="icon-[tabler--x] size-5"></span>
        </button>
        <h3 class="font-bold text-lg mb-4">
            <span class="icon-[tabler--users] size-5 inline-block mr-2"></span>
            {{ $channel->members->count() }} {{ Str::plural('Member', $channel->members->count()) }}
        </h3>
        <div class="max-h-96 overflow-y-auto space-y-2">
            @foreach($channel->members as $member)
            <div class="flex items-center gap-3 p-2 rounded-lg hover:bg-base-200 transition-colors">
                <div class="avatar">
                    @if($member->avatar_url)
                    <div class="w-10 h-10 rounded-full">
                        <img src="{{ $member->avatar_url }}" alt="{{ $member->name }}" />
                    </div>
                    @else
                    <div class="w-10 h-10 rounded-full bg-primary/10 text-primary flex items-center justify-center">
                        <span>{{ substr($member->first_name, 0, 1) }}{{ substr($member->last_name, 0, 1) }}</span>
                    </div>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-medium text-base-content truncate">{{ $member->name }}</p>
                    <p class="text-xs text-base-content/60 truncate">{{ $member->email }}</p>
                </div>
                @if($member->id === $channel->created_by)
                <span class="badge badge-primary badge-sm">Creator</span>
                @elseif($member->pivot->role === 'admin')
                <span class="badge badge-secondary badge-sm">Admin</span>
                @endif
            </div>
            @endforeach
        </div>
        <div class="flex justify-end mt-4">
            <button type="button" onclick="closeModal('membersModal')" class="btn btn-ghost">Close</button>
        </div>
    </div>
</div>
@endif

<!-- Delete Channel Confirmation Modal -->
@if($channel->canManage($user))
<div id="deleteChannelModal" class="channel-modal">
    <div class="channel-modal-backdrop" onclick="closeModal('deleteChannelModal')"></div>
    <div class="channel-modal-box bg-base-100 rounded-lg shadow-xl p-6 max-w-md w-full mx-4">
        <button type="button" onclick="closeModal('deleteChannelModal')" class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">
            <span class="icon-[tabler--x] size-5"></span>
        </button>
        <div class="text-center mb-4">
            <div class="w-16 h-16 rounded-full bg-error/10 flex items-center justify-center mx-auto mb-4">
                <span class="icon-[tabler--trash] size-8 text-error"></span>
            </div>
            <h3 class="font-bold text-lg">Delete Channel</h3>
            <p class="text-base-content/60 mt-2">Are you sure you want to delete <strong>{{ $channel->name }}</strong>?</p>
            <p class="text-sm text-error mt-2">This action cannot be undone. All threads and replies will be permanently deleted.</p>
        </div>
        <div class="flex justify-center gap-3 mt-6">
            <button type="button" onclick="closeModal('deleteChannelModal')" class="btn btn-ghost">Cancel</button>
            <form action="{{ route('channels.destroy', $channel) }}" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-error">
                    <span class="icon-[tabler--trash] size-4"></span>
                    Delete Channel
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
</script>
@endsection
