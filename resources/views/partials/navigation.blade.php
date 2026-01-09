@php
    $user = auth()->user();
    // A guest-only user is someone with guest role and no company_id (not a team member anywhere)
    $isGuestOnly = $user->role === \App\Models\User::ROLE_GUEST && !$user->company_id;
    // Check if guest has any workspace access
    $guestWorkspaces = $isGuestOnly ? $user->guestWorkspaces()->get() : collect();
    $hasGuestWorkspaces = $guestWorkspaces->isNotEmpty();
    // Check if guest has inbox workspaces (they are "clients")
    $hasInboxWorkspaces = $guestWorkspaces->filter(fn($w) => $w->type->value === 'inbox')->isNotEmpty();
    $guestLabel = $hasInboxWorkspaces ? 'Client' : 'Guest';
    // Plan limits info (passed by PlanLimitsComposer)
    $hasReachedLimit = $planLimits['has_reached_limit'] ?? false;
    $reachedLimits = $planLimits['reached_limits'] ?? [];
    $canCreateWorkspace = $planLimits['can_create_workspace'] ?? true;
    $canAddTeamMember = $planLimits['can_add_team_member'] ?? true;
    $planName = $planLimits['plan_name'] ?? 'Free';
    // Notifications for dropdown (only unread, for non-guests)
    $navNotifications = !$isGuestOnly ? $user->appNotifications()->whereNull('read_at')->take(10)->get() : collect();
    $unreadNotificationCount = !$isGuestOnly ? $user->unread_notification_count : 0;
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
                @if($hasInboxWorkspaces)
                {{-- Client Portal - only show My Tickets, no Workspaces link --}}
                <li>
                    <a href="/dashboard" class="{{ request()->is('dashboard') ? 'active' : '' }}">
                        <span class="icon-[tabler--ticket] size-5"></span>
                        My Tickets
                    </a>
                </li>
                @else
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
                        <li>
                            <a class="dropdown-item {{ !$canAddTeamMember ? 'text-warning' : '' }}" href="/users">
                                <span class="icon-[tabler--user-circle] size-4 me-2"></span>
                                Users
                                @if(!$canAddTeamMember)
                                    <span class="badge badge-warning badge-xs ml-auto">Limit</span>
                                @endif
                            </a>
                        </li>
                        @endif
                        <li><a class="dropdown-item" href="/workflows"><span class="icon-[tabler--git-branch] size-4 me-2"></span>Workflows</a></li>
                        <li><a class="dropdown-item" href="{{ route('drive.index') }}"><span class="icon-[tabler--cloud] size-4 me-2"></span>Drive</a></li>
                        <li><a class="dropdown-item" href="{{ route('documents.index') }}"><span class="icon-[tabler--file-text] size-4 me-2"></span>Documents</a></li>
                    </ul>
                </li>
            </ul>
        @endif
    </div>

    <div class="flex-none gap-2 flex items-center">
        @if(!$isGuestOnly)
            <!-- Upgrade Button (shown when any limit is reached) -->
            @if($hasReachedLimit)
                <a href="{{ route('settings.billing.plans') }}" class="btn btn-warning btn-sm gap-1 animate-pulse">
                    <span class="icon-[tabler--arrow-up-circle] size-4"></span>
                    <span class="hidden sm:inline">Upgrade</span>
                </a>
            @endif

            <!-- Add Button (only for team members) -->
            <div class="dropdown relative inline-flex [--auto-close:inside] [--offset:8] [--placement:bottom-end]">
                <button id="add-dropdown" type="button" class="dropdown-toggle btn btn-primary btn-sm gap-1" aria-haspopup="menu" aria-expanded="false" aria-label="Add menu">
                    <span class="icon-[tabler--plus] size-4"></span>
                    <span class="hidden sm:inline">Add</span>
                    <span class="icon-[tabler--chevron-down] dropdown-open:rotate-180 size-4 transition-transform"></span>
                </button>
                <ul class="dropdown-menu dropdown-open:opacity-100 hidden min-w-60" role="menu" aria-orientation="vertical" aria-labelledby="add-dropdown">
                    <li><a class="dropdown-item" href="{{ route('tasks.create') }}"><span class="icon-[tabler--checkbox] size-4 me-2 text-primary"></span>Add Task</a></li>
                    <li><a class="dropdown-item" href="{{ route('discussions.create') }}"><span class="icon-[tabler--message-plus] size-4 me-2 text-success"></span>Add Discussion</a></li>
                    @if($canCreateWorkspace)
                        <li><a class="dropdown-item" href="{{ route('workspace.create') }}"><span class="icon-[tabler--briefcase] size-4 me-2 text-info"></span>Add Workspace</a></li>
                    @else
                        <li>
                            <a href="{{ route('settings.billing.plans') }}" class="dropdown-item text-warning">
                                <span class="icon-[tabler--briefcase] size-4 me-2"></span>
                                <span class="flex-1">Add Workspace</span>
                                <span class="badge badge-warning badge-xs">Limit</span>
                            </a>
                        </li>
                    @endif
                    <li><a class="dropdown-item" href="{{ route('documents.create') }}"><span class="icon-[tabler--file-plus] size-4 me-2 text-warning"></span>Add Document</a></li>
                    <li><a class="dropdown-item" href="/guests"><span class="icon-[tabler--user-plus] size-4 me-2 text-secondary"></span>Add Guest</a></li>
                    <li><a class="dropdown-item" href="{{ route('ideas.create') }}"><span class="icon-[tabler--bulb] size-4 me-2 text-accent"></span>Add Idea</a></li>
                    <li><a class="dropdown-item" href="#"><span class="icon-[tabler--clock-plus] size-4 me-2 text-error"></span>Add Time</a></li>
                    @if($hasReachedLimit)
                        <li><hr class="border-base-content/10 my-2"></li>
                        <li>
                            <a href="{{ route('settings.billing.plans') }}" class="dropdown-item bg-warning/10 text-warning hover:bg-warning/20">
                                <span class="icon-[tabler--arrow-up-circle] size-4 me-2"></span>
                                Upgrade Plan
                            </a>
                        </li>
                    @endif
                </ul>
            </div>

            <!-- Settings (only for team members) -->
            <a href="/settings" class="btn btn-ghost btn-circle btn-sm nav-btn">
                <span class="icon-[tabler--settings] size-5"></span>
            </a>

            <!-- Store -->
            <button type="button" class="btn btn-ghost btn-circle btn-sm nav-btn hidden sm:flex" title="Store">
                <span class="icon-[tabler--layout-grid] size-5"></span>
            </button>

            <!-- Notifications -->
            <div class="dropdown relative inline-flex [--auto-close:inside] [--offset:8] [--placement:bottom-end]">
                <button id="notifications-dropdown" type="button" class="dropdown-toggle btn btn-ghost btn-circle btn-sm nav-btn indicator" aria-haspopup="menu" aria-expanded="false" aria-label="Notifications">
                    <span class="icon-[tabler--bell] size-5"></span>
                    @if($unreadNotificationCount > 0)
                        <span class="badge badge-primary badge-xs indicator-item">{{ $unreadNotificationCount > 9 ? '9+' : $unreadNotificationCount }}</span>
                    @endif
                </button>
                <div class="dropdown-menu dropdown-open:opacity-100 hidden w-80" role="menu" aria-orientation="vertical" aria-labelledby="notifications-dropdown">
                    <div class="p-3 border-b border-base-200 flex items-center justify-between">
                        <h3 class="font-semibold">Notifications</h3>
                        @if($unreadNotificationCount > 0)
                            <form action="{{ route('notifications.mark-all-read') }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="text-xs text-primary hover:underline">Mark all read</button>
                            </form>
                        @endif
                    </div>
                    <div class="max-h-72 overflow-y-auto" id="notification-list-container">
                        @if($navNotifications->isEmpty())
                            <!-- Empty State -->
                            <div class="p-6 text-center text-base-content/50 empty-state">
                                <span class="icon-[tabler--bell-off] size-10 mb-2 block mx-auto opacity-40"></span>
                                <p class="text-sm">No notifications yet</p>
                            </div>
                        @else
                            <!-- Notifications List (Unread Only) -->
                            <div class="divide-y divide-base-200">
                                @foreach($navNotifications as $notification)
                                    @php
                                        $notifUrl = $notification->data['task_url']
                                            ?? $notification->data['channel_url']
                                            ?? $notification->data['thread_url']
                                            ?? $notification->data['discussion_url']
                                            ?? $notification->data['milestone_url']
                                            ?? $notification->data['idea_url']
                                            ?? route('notifications.index');
                                    @endphp
                                    <a href="{{ $notifUrl }}"
                                       class="block p-3 hover:bg-base-200 bg-primary/5 notification-item"
                                       data-notification-id="{{ $notification->id }}"
                                       onclick="markNotificationRead(event, this)">
                                        <div class="flex items-start gap-3">
                                            <div class="flex-shrink-0 mt-0.5">
                                                <span class="{{ $notification->icon }} {{ $notification->color }} size-5"></span>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-semibold">{{ $notification->title }}</p>
                                                <p class="text-xs text-base-content/60 truncate">{{ $notification->message }}</p>
                                                <span class="text-xs text-base-content/40">{{ $notification->created_at->diffForHumans() }}</span>
                                            </div>
                                            <span class="w-2 h-2 bg-primary rounded-full flex-shrink-0 mt-2"></span>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    <div class="p-2 border-t border-base-200">
                        <a href="{{ route('notifications.index') }}" class="btn btn-ghost btn-sm w-full">View All</a>
                    </div>
                </div>
            </div>

            <!-- Notification Polling Script -->
            <script>
            // Mark notification as read when clicked
            async function markNotificationRead(event, element) {
                const notificationId = element.dataset.notificationId;
                const href = element.href;

                // Send mark as read request (don't wait for it)
                fetch(`/notifications/${notificationId}/read`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    }
                }).catch(err => console.error('Failed to mark as read:', err));

                // Remove from list immediately
                element.style.transition = 'all 0.2s ease-out';
                element.style.opacity = '0';
                element.style.transform = 'translateX(20px)';

                setTimeout(() => {
                    element.remove();

                    // Update badge count
                    const badge = document.querySelector('#notifications-dropdown .badge');
                    if (badge) {
                        const currentCount = parseInt(badge.textContent) || 0;
                        if (currentCount <= 1) {
                            badge.style.display = 'none';
                        } else {
                            badge.textContent = currentCount - 1;
                        }
                    }

                    // Check if list is empty, show empty state
                    const listContainer = document.getElementById('notification-list-container');
                    const remainingItems = listContainer?.querySelectorAll('.notification-item');
                    if (remainingItems?.length === 0) {
                        const divideContainer = listContainer.querySelector('.divide-y');
                        if (divideContainer) divideContainer.remove();

                        const emptyState = document.createElement('div');
                        emptyState.className = 'p-6 text-center text-base-content/50 empty-state';
                        emptyState.innerHTML = `
                            <span class="icon-[tabler--bell-off] size-10 mb-2 block mx-auto opacity-40"></span>
                            <p class="text-sm">No notifications yet</p>
                        `;
                        listContainer.appendChild(emptyState);
                    }
                }, 200);

                // Navigate after a tiny delay
                setTimeout(() => {
                    window.location.href = href;
                }, 50);

                event.preventDefault();
            }

            (function() {
                let lastCount = {{ $unreadNotificationCount }};
                const pollInterval = 30000; // 30 seconds

                async function checkNotifications() {
                    try {
                        const response = await fetch('/notifications/poll?last_count=' + lastCount, {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });

                        if (response.ok) {
                            const data = await response.json();
                            const newCount = data.unread_count || 0;

                            // Update badge
                            const badge = document.querySelector('#notifications-dropdown .badge');
                            if (newCount > 0) {
                                if (badge) {
                                    badge.textContent = newCount > 9 ? '9+' : newCount;
                                    badge.style.display = '';
                                } else {
                                    const btn = document.querySelector('#notifications-dropdown');
                                    const newBadge = document.createElement('span');
                                    newBadge.className = 'badge badge-primary badge-xs indicator-item';
                                    newBadge.textContent = newCount > 9 ? '9+' : newCount;
                                    btn.appendChild(newBadge);
                                }
                            } else if (badge) {
                                badge.style.display = 'none';
                            }

                            // Show toast and update list if new notifications arrived
                            if (newCount > lastCount && data.latest_notification) {
                                showNotificationToast(data.latest_notification);
                                updateNotificationList(data.latest_notification);
                            }

                            lastCount = newCount;
                        }
                    } catch (error) {
                        console.error('Notification poll failed:', error);
                    }
                }

                function updateNotificationList(notification) {
                    const listContainer = document.getElementById('notification-list-container');
                    if (!listContainer) return;

                    // Remove empty state if exists
                    const emptyState = listContainer.querySelector('.empty-state');
                    if (emptyState) {
                        emptyState.remove();
                    }

                    // Create new notification item
                    const newItem = document.createElement('a');
                    newItem.href = notification.url || '/notifications';
                    newItem.className = 'block p-3 hover:bg-base-200 bg-primary/5 animate-fade-in-down';
                    newItem.innerHTML = `
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 mt-0.5">
                                <span class="${notification.icon} ${notification.color} size-5"></span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold">${notification.title}</p>
                                <p class="text-xs text-base-content/60 truncate">${notification.message}</p>
                                <span class="text-xs text-base-content/40">Just now</span>
                            </div>
                            <span class="w-2 h-2 bg-primary rounded-full flex-shrink-0 mt-2"></span>
                        </div>
                    `;

                    // Insert at the top of list
                    let divideContainer = listContainer.querySelector('.divide-y');
                    if (!divideContainer) {
                        divideContainer = document.createElement('div');
                        divideContainer.className = 'divide-y divide-base-200';
                        listContainer.appendChild(divideContainer);
                    }
                    divideContainer.insertBefore(newItem, divideContainer.firstChild);

                    // Keep only 10 items
                    const items = divideContainer.querySelectorAll('a');
                    if (items.length > 10) {
                        items[items.length - 1].remove();
                    }
                }

                function showNotificationToast(notification) {
                    // Play notification sound (optional)
                    // new Audio('/sounds/notification.mp3').play().catch(() => {});

                    const toast = document.createElement('div');
                    toast.className = 'fixed top-20 right-4 z-[200] animate-fade-in-down';
                    toast.innerHTML = `
                        <div class="card bg-base-100 shadow-2xl border border-primary/20 w-80 overflow-hidden cursor-pointer hover:shadow-primary/10 transition-all duration-300" onclick="window.location.href='${notification.url || '/notifications'}'">
                            <div class="bg-gradient-to-r from-primary/10 to-transparent px-4 py-2 flex items-center gap-2 border-b border-base-200">
                                <span class="icon-[tabler--bell-ringing] size-4 text-primary animate-pulse"></span>
                                <span class="text-xs font-semibold text-primary">New Notification</span>
                                <button class="ml-auto btn btn-ghost btn-xs btn-circle hover:bg-base-200" onclick="event.stopPropagation(); this.closest('.fixed').remove();">
                                    <span class="icon-[tabler--x] size-4"></span>
                                </button>
                            </div>
                            <div class="p-4">
                                <div class="flex items-start gap-3">
                                    <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center flex-shrink-0">
                                        <span class="${notification.icon} ${notification.color} size-5"></span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-semibold text-sm text-base-content">${notification.title}</p>
                                        <p class="text-xs text-base-content/60 mt-1 line-clamp-2">${notification.message}</p>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between mt-3 pt-3 border-t border-base-200">
                                    <span class="text-xs text-base-content/40">Just now</span>
                                    <span class="text-xs text-primary font-medium">Click to view →</span>
                                </div>
                            </div>
                        </div>
                    `;
                    document.body.appendChild(toast);

                    // Auto-remove after 6 seconds
                    setTimeout(() => {
                        toast.style.transition = 'all 0.3s ease-out';
                        toast.style.opacity = '0';
                        toast.style.transform = 'translateX(100%)';
                        setTimeout(() => toast.remove(), 300);
                    }, 6000);
                }

                // Start polling
                setInterval(checkNotifications, pollInterval);

                // Also check when tab becomes visible
                document.addEventListener('visibilitychange', () => {
                    if (!document.hidden) {
                        checkNotifications();
                    }
                });
            })();
            </script>
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
            <button id="profile-dropdown" type="button" class="dropdown-toggle nav-btn btn btn-ghost gap-2 px-2" aria-haspopup="menu" aria-expanded="false" aria-label="Profile menu">
                @include('partials.user-avatar', ['user' => $user, 'size' => 'sm', 'showOOO' => true, 'compact' => true])
                <span class="hidden md:inline text-sm font-medium">{{ $user->first_name ?? 'User' }}</span>
                @if($isGuestOnly)
                    <span class="badge badge-{{ $hasInboxWorkspaces ? 'primary' : 'warning' }} badge-xs hidden md:inline">{{ $guestLabel }}</span>
                @endif
                <span class="icon-[tabler--chevron-down] dropdown-open:rotate-180 size-4 hidden md:inline transition-transform"></span>
            </button>
            <ul class="dropdown-menu dropdown-open:opacity-100 hidden min-w-56" role="menu" aria-orientation="vertical" aria-labelledby="profile-dropdown">
                <li class="dropdown-header px-3 py-2 border-b border-base-200 mb-2">
                    <p class="text-xs text-base-content/50">{{ $user->email ?? '' }}</p>
                    @if($isGuestOnly)
                        <span class="badge badge-{{ $hasInboxWorkspaces ? 'primary' : 'warning' }} badge-xs mt-1">{{ $guestLabel }} Account</span>
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
                    @if($hasInboxWorkspaces)
                    {{-- Client Portal - only show My Tickets, no Workspaces link --}}
                    <li><a class="dropdown-item" href="/dashboard"><span class="icon-[tabler--ticket] size-4 me-2"></span>My Tickets</a></li>
                    @else
                    <li><a class="dropdown-item" href="/dashboard"><span class="icon-[tabler--home] size-4 me-2"></span>Home</a></li>
                    @if($hasGuestWorkspaces)
                    <li><a class="dropdown-item" href="{{ route('workspace.index') }}"><span class="icon-[tabler--briefcase] size-4 me-2"></span>Workspaces</a></li>
                    @endif
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
                    <li>
                        <a class="dropdown-item {{ !$canAddTeamMember ? 'text-warning' : '' }}" href="/users">
                            <span class="icon-[tabler--user-circle] size-4 me-2"></span>
                            Users
                            @if(!$canAddTeamMember)
                                <span class="badge badge-warning badge-xs ml-auto">Limit</span>
                            @endif
                        </a>
                    </li>
                    @endif
                    @if($hasReachedLimit)
                    <li><hr class="border-base-content/10 my-2"></li>
                    <li>
                        <a href="{{ route('settings.billing.plans') }}" class="dropdown-item bg-warning/10 text-warning hover:bg-warning/20">
                            <span class="icon-[tabler--arrow-up-circle] size-4 me-2"></span>
                            Upgrade Plan
                        </a>
                    </li>
                    @endif
                @endif
            </ul>
        </div>
    </div>
</nav>

