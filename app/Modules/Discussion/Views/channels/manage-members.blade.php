@extends('layouts.app')

@section('content')
<div class="flex min-h-[calc(100vh-4rem)]">
    @include('discussion::channels.partials.sidebar')

    <!-- Main Content Area -->
    <main class="flex-1 min-w-0 flex flex-col bg-base-100">
        <!-- Header -->
        <div class="border-b border-base-200 px-4 md:px-6 py-2 sticky top-16 z-10 bg-base-100">
            <!-- Breadcrumb -->
            <div class="flex items-center gap-1 text-xs text-base-content/60 mb-1">
                <a href="{{ route('discussions.index') }}" class="hover:text-primary">Discussions</a>
                <span class="icon-[tabler--chevron-right] size-3"></span>
                <a href="{{ route('channels.index') }}" class="hover:text-primary">Channels</a>
                <span class="icon-[tabler--chevron-right] size-3"></span>
                <a href="{{ route('channels.show', $channel) }}" class="hover:text-primary">{{ $channel->name }}</a>
                <span class="icon-[tabler--chevron-right] size-3"></span>
                <span class="text-base-content">Manage Members</span>
            </div>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-lg {{ $channel->color_class }} flex items-center justify-center flex-shrink-0">
                        <span class="icon-[tabler--users] size-5"></span>
                    </div>
                    <div>
                        <h1 class="text-lg font-bold text-base-content">Manage Members</h1>
                        <p class="text-sm text-base-content/60">{{ $channel->name }} - {{ $channel->members->count() }} {{ Str::plural('member', $channel->members->count()) }}</p>
                    </div>
                </div>
                <a href="{{ route('channels.show', $channel) }}" class="btn btn-ghost btn-sm">
                    <span class="icon-[tabler--arrow-left] size-5"></span>
                    Back to Channel
                </a>
            </div>
        </div>

        <!-- Form Content -->
        <div class="flex-1 p-4 md:p-6 pt-3">
            <div class="max-w mx-auto">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Invite Team Members Section -->
                <div class="card bg-base-100 shadow">
                    <div class="card-body">
                        <h2 class="card-title text-base">
                            <span class="icon-[tabler--user-plus] size-5"></span>
                            Invite Team Members
                        </h2>

                        @if($availableMembers->isEmpty())
                        <div class="text-center py-8">
                            <div class="text-base-content/50">
                                <span class="icon-[tabler--users-group] size-12 block mx-auto mb-3 opacity-50"></span>
                                <p class="text-sm">All team members are already in this channel</p>
                            </div>
                        </div>
                        @else
                        <!-- Search Box -->
                        <div class="mb-3">
                            <input type="text" id="member-search" placeholder="Search team members..." class="input input-bordered input-sm w-full" />
                        </div>

                        <div class="border border-base-300 rounded-lg max-h-64 overflow-y-auto">
                            <div class="p-2 space-y-1" id="available-members-list">
                                @foreach($availableMembers as $member)
                                <div class="member-item flex items-center gap-3 p-2 rounded-lg hover:bg-base-200 transition-colors" data-name="{{ strtolower($member->name) }}" data-email="{{ strtolower($member->email) }}">
                                    <div class="avatar placeholder">
                                        @if($member->avatar_url)
                                            <div class="w-10 h-10 rounded-full">
                                                <img src="{{ $member->avatar_url }}" alt="{{ $member->name }}" />
                                            </div>
                                        @else
                                            <div class="w-10 h-10 rounded-full bg-primary/10 text-primary">
                                                <span>{{ substr($member->first_name, 0, 1) }}{{ substr($member->last_name, 0, 1) }}</span>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="font-medium text-sm truncate">{{ $member->name }}</div>
                                        <div class="text-xs text-base-content/50 truncate">{{ $member->email }}</div>
                                    </div>
                                    <span class="badge badge-{{ $member->role_color }} badge-sm">{{ $member->role_label }}</span>
                                    <form action="{{ route('channels.invite-member', $channel) }}" method="POST" class="inline">
                                        @csrf
                                        <input type="hidden" name="user_id" value="{{ $member->id }}" />
                                        <button type="submit" class="btn btn-primary btn-sm gap-1">
                                            <span class="icon-[tabler--plus] size-4"></span>
                                            Invite
                                        </button>
                                    </form>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Current Members Section -->
                <div class="card bg-base-100 shadow">
                    <div class="card-body">
                        <h2 class="card-title text-base">
                            <span class="icon-[tabler--users] size-5"></span>
                            Current Members ({{ $channel->members->count() }})
                        </h2>

                        @if($channel->members->isEmpty())
                        <div class="text-center py-8">
                            <div class="text-base-content/50">
                                <span class="icon-[tabler--user-off] size-12 block mx-auto mb-3 opacity-50"></span>
                                <p class="text-sm">No members in this channel yet</p>
                            </div>
                        </div>
                        @else
                        <div class="space-y-2 max-h-96 overflow-y-auto">
                            @foreach($channel->members as $member)
                            <div class="flex items-center gap-3 p-3 rounded-lg bg-base-200/50">
                                <div class="avatar placeholder">
                                    @if($member->avatar_url)
                                        <div class="w-10 h-10 rounded-full">
                                            <img src="{{ $member->avatar_url }}" alt="{{ $member->name }}" />
                                        </div>
                                    @else
                                        <div class="w-10 h-10 rounded-full bg-primary/10 text-primary">
                                            <span>{{ substr($member->first_name, 0, 1) }}{{ substr($member->last_name, 0, 1) }}</span>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="font-medium text-sm truncate">{{ $member->name }}</div>
                                    <div class="text-xs text-base-content/50 truncate">{{ $member->email }}</div>
                                </div>
                                @if($member->id === $channel->created_by)
                                <span class="badge badge-primary badge-sm">Creator</span>
                                @elseif($member->pivot->role === 'admin')
                                <span class="badge badge-secondary badge-sm">Admin</span>
                                @endif
                                @if($member->id !== $channel->created_by)
                                <button type="button"
                                    class="btn btn-ghost btn-sm text-error"
                                    title="Remove from channel"
                                    data-confirm
                                    data-confirm-action="{{ route('channels.remove-member', [$channel, $member]) }}"
                                    data-confirm-method="DELETE"
                                    data-confirm-title="Remove Member"
                                    data-confirm-content="Are you sure you want to remove <strong>{{ $member->name }}</strong> from this channel?"
                                    data-confirm-button="Remove"
                                    data-confirm-icon="tabler--user-minus"
                                    data-confirm-class="btn-error"
                                    data-confirm-icon-class="text-error"
                                    data-confirm-title-icon="tabler--user-minus">
                                    <span class="icon-[tabler--user-minus] size-4"></span>
                                </button>
                                @endif
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>
                </div>
            </div>
        </div>
    </main>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('member-search');
    const memberItems = document.querySelectorAll('.member-item');

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            memberItems.forEach(item => {
                const name = item.dataset.name || '';
                const email = item.dataset.email || '';
                if (name.includes(searchTerm) || email.includes(searchTerm)) {
                    item.classList.remove('hidden');
                } else {
                    item.classList.add('hidden');
                }
            });
        });
    }
});
</script>
@endpush
@endsection
