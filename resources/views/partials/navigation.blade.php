@php
    $user = auth()->user();
    // A guest-only user is someone with guest role and no company_id (not a team member anywhere)
    $isGuestOnly = $user->role === \App\Models\User::ROLE_GUEST && !$user->company_id;
    // Check if guest has any workspace access
    $hasGuestWorkspaces = $isGuestOnly && $user->guestWorkspaces()->exists();
@endphp

<nav class="navbar bg-base-100 shadow-sm fixed top-0 left-0 right-0 z-[100] px-4 min-h-16">
    <div class="flex-1 gap-2 flex items-center">
        <!-- Logo -->
        <a href="{{ $isGuestOnly ? '/dashboard' : '/dashboard' }}" class="flex items-center gap-2 mr-4 flex-shrink-0">
            <span class="icon-[tabler--checkbox] size-7 text-primary"></span>
            <span class="text-xl font-bold text-base-content hidden sm:inline">NewDone</span>
        </a>

        @if($isGuestOnly)
            <!-- Guest-Only Navigation (simplified) -->
            <ul class="menu menu-horizontal px-1 gap-1 hidden lg:flex">
                <li>
                    <a href="/dashboard" class="{{ request()->is('dashboard') ? 'active' : '' }}">
                        <span class="icon-[tabler--home] size-5"></span>
                        Home
                    </a>
                </li>
                @if($hasGuestWorkspaces)
                <li>
                    <a href="{{ route('workspace.index') }}" class="{{ request()->is('workspaces*') ? 'active' : '' }}">
                        <span class="icon-[tabler--briefcase] size-5"></span>
                        Workspaces
                    </a>
                </li>
                @endif
            </ul>
        @else
            <!-- Full Navigation for Team Members -->
            <ul class="menu menu-horizontal px-1 gap-1 hidden lg:flex">
                <li>
                    <a href="/dashboard" class="{{ request()->is('dashboard') ? 'active' : '' }}">
                        <span class="icon-[tabler--layout-dashboard] size-5"></span>
                        Dashboard
                    </a>
                </li>
                <li>
                    <a href="{{ route('workspace.index') }}" class="{{ request()->is('workspaces*') ? 'active' : '' }}">
                        <span class="icon-[tabler--briefcase] size-5"></span>
                        Workspaces
                    </a>
                </li>
                <li>
                    <a href="/tasks" class="{{ request()->is('tasks*') ? 'active' : '' }}">
                        <span class="icon-[tabler--checkbox] size-5"></span>
                        Tasks
                    </a>
                </li>
                <li>
                    <a href="/discussions" class="{{ request()->is('discussions*') ? 'active' : '' }}">
                        <span class="icon-[tabler--messages] size-5"></span>
                        Discussion
                    </a>
                </li>
                <li>
                    <a href="{{ route('calendar.index') }}" class="{{ request()->is('calendar*') ? 'active' : '' }}">
                        <span class="icon-[tabler--calendar] size-5"></span>
                        Calendar
                    </a>
                </li>
                <!-- More Dropdown -->
                <li class="dropdown relative inline-flex [--auto-close:inside] [--offset:8] [--placement:bottom-end]">
                    <button id="more-dropdown" type="button" class="dropdown-toggle flex items-center gap-1 px-3 py-2 rounded-lg hover:bg-base-200 transition-colors" aria-haspopup="menu" aria-expanded="false" aria-label="More menu">
                        <span class="icon-[tabler--dots] size-5"></span>
                        More
                        <span class="icon-[tabler--chevron-down] dropdown-open:rotate-180 size-4 transition-transform"></span>
                    </button>
                    <ul class="dropdown-menu dropdown-open:opacity-100 hidden min-w-52" role="menu" aria-orientation="vertical" aria-labelledby="more-dropdown">
                        <li><a class="dropdown-item" href="/guests"><span class="icon-[tabler--users] size-4 me-2"></span>Guest / Client</a></li>
                        <li><a class="dropdown-item" href="/time"><span class="icon-[tabler--clock] size-4 me-2"></span>Time Management</a></li>
                        <li><a class="dropdown-item" href="/ideas"><span class="icon-[tabler--bulb] size-4 me-2"></span>Ideas</a></li>
                        @if($user->isAdminOrHigher())
                        <li><a class="dropdown-item" href="/users"><span class="icon-[tabler--user-circle] size-4 me-2"></span>Users</a></li>
                        @endif
                        <li><a class="dropdown-item" href="/workflows"><span class="icon-[tabler--git-branch] size-4 me-2"></span>Workflows</a></li>
                        <li><a class="dropdown-item" href="{{ route('drive.index') }}"><span class="icon-[tabler--cloud] size-4 me-2"></span>Drive</a></li>
                        <li><a class="dropdown-item" href="/documents"><span class="icon-[tabler--file-text] size-4 me-2"></span>Documents</a></li>
                    </ul>
                </li>
            </ul>
        @endif
    </div>

    <div class="flex-none gap-2 flex items-center">
        @if(!$isGuestOnly)
            <!-- Add Button (only for team members) -->
            <div class="dropdown relative inline-flex [--auto-close:inside] [--offset:8] [--placement:bottom-end]">
                <button id="add-dropdown" type="button" class="dropdown-toggle btn btn-primary btn-sm gap-1" aria-haspopup="menu" aria-expanded="false" aria-label="Add menu">
                    <span class="icon-[tabler--plus] size-4"></span>
                    <span class="hidden sm:inline">Add</span>
                    <span class="icon-[tabler--chevron-down] dropdown-open:rotate-180 size-4 transition-transform"></span>
                </button>
                <ul class="dropdown-menu dropdown-open:opacity-100 hidden min-w-52" role="menu" aria-orientation="vertical" aria-labelledby="add-dropdown">
                    <li><a class="dropdown-item" href="{{ route('tasks.create') }}"><span class="icon-[tabler--checkbox] size-4 me-2 text-primary"></span>Add Task</a></li>
                    <li><a class="dropdown-item" href="{{ route('discussions.create') }}"><span class="icon-[tabler--message-plus] size-4 me-2 text-success"></span>Add Discussion</a></li>
                    <li><a class="dropdown-item" href="{{ route('workspace.create') }}"><span class="icon-[tabler--briefcase] size-4 me-2 text-info"></span>Add Workspace</a></li>
                    <li><a class="dropdown-item" href="#"><span class="icon-[tabler--file-plus] size-4 me-2 text-warning"></span>Add Document</a></li>
                    <li><a class="dropdown-item" href="#"><span class="icon-[tabler--user-plus] size-4 me-2 text-secondary"></span>Add Guest</a></li>
                    <li><a class="dropdown-item" href="{{ route('ideas.create') }}"><span class="icon-[tabler--bulb] size-4 me-2 text-accent"></span>Add Idea</a></li>
                    <li><a class="dropdown-item" href="#"><span class="icon-[tabler--clock-plus] size-4 me-2 text-error"></span>Add Time</a></li>
                </ul>
            </div>

            <!-- Settings (only for team members) -->
            <a href="/settings" class="btn btn-ghost btn-circle btn-sm">
                <span class="icon-[tabler--settings] size-5"></span>
            </a>

            <!-- App Marketplace (only for team members) -->
            <a href="/marketplace" class="btn btn-ghost btn-circle btn-sm hidden sm:flex">
                <span class="icon-[tabler--apps] size-5"></span>
            </a>

            <!-- Notifications -->
            <div class="dropdown relative inline-flex [--auto-close:inside] [--offset:8] [--placement:bottom-end]" x-data="notificationDropdown()" x-init="init()">
                <button id="notifications-dropdown" type="button" class="dropdown-toggle btn btn-ghost btn-circle btn-sm indicator" aria-haspopup="menu" aria-expanded="false" aria-label="Notifications" @click="loadNotifications">
                    <span class="icon-[tabler--bell] size-5"></span>
                    <span x-show="unreadCount > 0" x-text="unreadCount > 9 ? '9+' : unreadCount" class="badge badge-primary badge-xs indicator-item" x-cloak></span>
                </button>
                <div class="dropdown-menu dropdown-open:opacity-100 hidden w-80" role="menu" aria-orientation="vertical" aria-labelledby="notifications-dropdown">
                    <div class="p-4 border-b border-base-200 flex items-center justify-between">
                        <h3 class="font-semibold">Notifications</h3>
                        <button x-show="unreadCount > 0" @click.stop="markAllAsRead" class="text-xs text-primary hover:underline" x-cloak>Mark all read</button>
                    </div>
                    <div class="max-h-64 overflow-y-auto" id="notifications-list">
                        <!-- Loading State -->
                        <div x-show="loading" class="p-4 text-center text-base-content/50">
                            <span class="loading loading-spinner loading-sm"></span>
                        </div>
                        <!-- Empty State -->
                        <div x-show="!loading && loaded && notifications.length === 0" class="p-4 text-center text-base-content/50">
                            <span class="icon-[tabler--bell-off] size-8 mb-2 block mx-auto opacity-50"></span>
                            <p class="text-sm">No notifications yet</p>
                        </div>
                        <!-- Notifications List -->
                        <div x-show="!loading && notifications.length > 0" class="p-2">
                            <template x-for="notification in notifications" :key="notification.id">
                                <div @click="goToNotification(notification)"
                                     class="p-2 hover:bg-base-200 rounded cursor-pointer flex items-start gap-3 mb-1"
                                     :class="notification.is_read ? '' : 'bg-primary/5'">
                                    <div class="size-5 mt-0.5 flex-shrink-0">
                                        <span :class="notification.icon" class="size-5" :style="'color: var(--' + notification.color.replace('text-', '') + ')'"></span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm" :class="notification.is_read ? 'font-medium' : 'font-semibold'" x-text="notification.title"></p>
                                        <p class="text-xs text-base-content/60 truncate" x-text="notification.message"></p>
                                        <span class="text-xs text-base-content/40" x-text="notification.time"></span>
                                    </div>
                                    <span x-show="!notification.is_read" class="w-2 h-2 bg-primary rounded-full flex-shrink-0 mt-2"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                    <div class="p-2 border-t border-base-200">
                        <a href="{{ route('notifications.index') }}" class="btn btn-ghost btn-sm w-full">View All</a>
                    </div>
                </div>
            </div>
        @else
            <!-- Upgrade Button (only for guest-only users) -->
            <button type="button" class="btn btn-success btn-sm gap-2"
                data-confirm
                data-confirm-action="{{ route('guest.upgrade') }}"
                data-confirm-title="Upgrade to Full Version"
                data-confirm-content="<p class='text-base-content/70 mb-4'>You're about to upgrade from a guest account to a full account. This will allow you to:</p><ul class='space-y-2 text-sm'><li class='flex items-center gap-2'><span class='icon-[tabler--check] size-4 text-success'></span>Create and manage your own workspaces</li><li class='flex items-center gap-2'><span class='icon-[tabler--check] size-4 text-success'></span>Invite team members and guests</li><li class='flex items-center gap-2'><span class='icon-[tabler--check] size-4 text-success'></span>Access all features including tasks, discussions, and more</li><li class='flex items-center gap-2'><span class='icon-[tabler--check] size-4 text-success'></span>Set up your own company profile</li></ul><div class='alert alert-info mt-4'><span class='icon-[tabler--info-circle] size-5'></span><span class='text-sm'>You'll keep access to all workspaces you've been invited to as a guest.</span></div>"
                data-confirm-button="Yes, Upgrade My Account"
                data-confirm-icon="tabler--arrow-right"
                data-confirm-class="btn-success"
                data-confirm-icon-class="text-success"
                data-confirm-title-icon="tabler--rocket">
                <span class="icon-[tabler--rocket] size-4"></span>
                <span class="hidden sm:inline">Upgrade</span>
            </button>
        @endif

        <!-- Profile Dropdown -->
        <div class="dropdown relative inline-flex [--auto-close:inside] [--offset:8] [--placement:bottom-end]">
            <button id="profile-dropdown" type="button" class="dropdown-toggle btn btn-ghost gap-2 px-2" aria-haspopup="menu" aria-expanded="false" aria-label="Profile menu">
                <div class="avatar {{ $user->avatar_url ? '' : 'placeholder' }}">
                    @if($user->avatar_url)
                        <div class="w-8 h-8 rounded-full overflow-hidden">
                            <img src="{{ $user->avatar_url }}" alt="{{ $user->full_name }}" class="w-full h-full object-cover" />
                        </div>
                    @else
                        <div class="bg-primary text-primary-content rounded-full w-8 h-8 flex items-center justify-center">
                            <span class="text-sm">{{ $user->initials }}</span>
                        </div>
                    @endif
                </div>
                <span class="hidden md:inline text-sm font-medium">{{ $user->first_name ?? 'User' }}</span>
                @if($isGuestOnly)
                    <span class="badge badge-warning badge-xs hidden md:inline">Guest</span>
                @endif
                <span class="icon-[tabler--chevron-down] dropdown-open:rotate-180 size-4 hidden md:inline transition-transform"></span>
            </button>
            <ul class="dropdown-menu dropdown-open:opacity-100 hidden min-w-56" role="menu" aria-orientation="vertical" aria-labelledby="profile-dropdown">
                <li class="dropdown-header px-3 py-2 border-b border-base-200 mb-2">
                    <p class="text-xs text-base-content/50">{{ $user->email ?? '' }}</p>
                    @if($isGuestOnly)
                        <span class="badge badge-warning badge-xs mt-1">Guest Account</span>
                    @endif
                </li>
                <li><a class="dropdown-item" href="/profile"><span class="icon-[tabler--user] size-4 me-2"></span>My Profile</a></li>
                <li><a class="dropdown-item" href="/profile/password"><span class="icon-[tabler--lock] size-4 me-2"></span>Update Password</a></li>
                @if(!$isGuestOnly)
                    <li><a class="dropdown-item" href="/profile/email-preferences"><span class="icon-[tabler--mail-cog] size-4 me-2"></span>Email Preferences</a></li>
                    <li><a class="dropdown-item" href="/profile/activity"><span class="icon-[tabler--activity] size-4 me-2"></span>My Activity</a></li>
                @endif
                <li><hr class="border-base-content/10 my-2"></li>
                <li>
                    <form action="/logout" method="POST" class="w-full">
                        @csrf
                        <button type="submit" class="dropdown-item text-error w-full text-left">
                            <span class="icon-[tabler--logout] size-4 me-2"></span>
                            Sign Out
                        </button>
                    </form>
                </li>
            </ul>
        </div>

        <!-- Mobile Menu -->
        <div class="dropdown relative inline-flex lg:hidden [--auto-close:inside] [--offset:8] [--placement:bottom-end]">
            <button id="mobile-menu-dropdown" type="button" class="dropdown-toggle btn btn-ghost btn-circle btn-sm" aria-haspopup="menu" aria-expanded="false" aria-label="Mobile menu">
                <span class="icon-[tabler--menu-2] size-5"></span>
            </button>
            <ul class="dropdown-menu dropdown-open:opacity-100 hidden min-w-56" role="menu" aria-orientation="vertical" aria-labelledby="mobile-menu-dropdown">
                @if($isGuestOnly)
                    <li><a class="dropdown-item" href="/dashboard"><span class="icon-[tabler--home] size-4 me-2"></span>Home</a></li>
                    @if($hasGuestWorkspaces)
                    <li><a class="dropdown-item" href="{{ route('workspace.index') }}"><span class="icon-[tabler--briefcase] size-4 me-2"></span>Workspaces</a></li>
                    @endif
                    <li><hr class="border-base-content/10 my-2"></li>
                    <li>
                        <button type="button" class="dropdown-item text-success"
                            data-confirm
                            data-confirm-action="{{ route('guest.upgrade') }}"
                            data-confirm-title="Upgrade to Full Version"
                            data-confirm-content="<p class='text-base-content/70 mb-4'>You're about to upgrade from a guest account to a full account. This will allow you to:</p><ul class='space-y-2 text-sm'><li class='flex items-center gap-2'><span class='icon-[tabler--check] size-4 text-success'></span>Create and manage your own workspaces</li><li class='flex items-center gap-2'><span class='icon-[tabler--check] size-4 text-success'></span>Invite team members and guests</li><li class='flex items-center gap-2'><span class='icon-[tabler--check] size-4 text-success'></span>Access all features including tasks, discussions, and more</li><li class='flex items-center gap-2'><span class='icon-[tabler--check] size-4 text-success'></span>Set up your own company profile</li></ul><div class='alert alert-info mt-4'><span class='icon-[tabler--info-circle] size-5'></span><span class='text-sm'>You'll keep access to all workspaces you've been invited to as a guest.</span></div>"
                            data-confirm-button="Yes, Upgrade My Account"
                            data-confirm-icon="tabler--arrow-right"
                            data-confirm-class="btn-success"
                            data-confirm-icon-class="text-success"
                            data-confirm-title-icon="tabler--rocket">
                            <span class="icon-[tabler--rocket] size-4 me-2"></span>
                            Upgrade to Full Version
                        </button>
                    </li>
                @else
                    <li><a class="dropdown-item" href="/dashboard"><span class="icon-[tabler--layout-dashboard] size-4 me-2"></span>Dashboard</a></li>
                    <li><a class="dropdown-item" href="{{ route('workspace.index') }}"><span class="icon-[tabler--briefcase] size-4 me-2"></span>Workspaces</a></li>
                    <li><a class="dropdown-item" href="/tasks"><span class="icon-[tabler--checkbox] size-4 me-2"></span>Tasks</a></li>
                    <li><a class="dropdown-item" href="/discussions"><span class="icon-[tabler--messages] size-4 me-2"></span>Discussion</a></li>
                    <li><a class="dropdown-item" href="{{ route('calendar.index') }}"><span class="icon-[tabler--calendar] size-4 me-2"></span>Calendar</a></li>
                    <li><hr class="border-base-content/10 my-2"></li>
                    <li><a class="dropdown-item" href="/guests"><span class="icon-[tabler--users] size-4 me-2"></span>Guests</a></li>
                    <li><a class="dropdown-item" href="/time"><span class="icon-[tabler--clock] size-4 me-2"></span>Time</a></li>
                    <li><a class="dropdown-item" href="/ideas"><span class="icon-[tabler--bulb] size-4 me-2"></span>Ideas</a></li>
                    <li><a class="dropdown-item" href="{{ route('drive.index') }}"><span class="icon-[tabler--cloud] size-4 me-2"></span>Drive</a></li>
                    @if($user->isAdminOrHigher())
                    <li><a class="dropdown-item" href="/users"><span class="icon-[tabler--user-circle] size-4 me-2"></span>Users</a></li>
                    @endif
                @endif
            </ul>
        </div>
    </div>
</nav>

@once
@push('scripts')
<script>
function notificationDropdown() {
    return {
        notifications: [],
        unreadCount: {{ auth()->user()->unread_notification_count ?? 0 }},
        loading: false,
        loaded: false,

        async loadNotifications() {
            // Skip if already loading
            if (this.loading) return;

            this.loading = true;

            try {
                const response = await fetch('{{ route("notifications.dropdown") }}', {
                    method: 'GET',
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    }
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();
                this.notifications = Array.isArray(data.notifications) ? data.notifications : [];
                this.unreadCount = data.unread_count || 0;
                this.loaded = true;
            } catch (error) {
                console.error('Failed to load notifications:', error);
                this.notifications = [];
                this.loaded = true;
            } finally {
                this.loading = false;
            }
        },

        async markAllAsRead() {
            try {
                await fetch('{{ route("notifications.mark-all-read") }}', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    }
                });
                this.notifications = this.notifications.map(n => ({ ...n, is_read: true }));
                this.unreadCount = 0;
            } catch (error) {
                console.error('Failed to mark all as read:', error);
            }
        },

        async goToNotification(notification) {
            // Mark as read
            if (!notification.is_read) {
                try {
                    await fetch(`/notifications/${notification.id}/read`, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                        }
                    });
                    notification.is_read = true;
                    this.unreadCount = Math.max(0, this.unreadCount - 1);
                } catch (error) {
                    console.error('Failed to mark as read:', error);
                }
            }

            // Navigate to URL if available
            if (notification.url) {
                window.location.href = notification.url;
            }
        },

        init() {
            // Check for new notifications every 30 seconds
            setInterval(async () => {
                try {
                    const response = await fetch('{{ route("notifications.unread-count") }}', {
                        credentials: 'same-origin',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    if (response.ok) {
                        const data = await response.json();
                        if (data.count !== this.unreadCount) {
                            this.unreadCount = data.count;
                            this.loaded = false; // Force reload on next open
                        }
                    }
                } catch (error) {
                    // Silently fail
                }
            }, 30000);
        }
    };
}
</script>
@endpush
@endonce
