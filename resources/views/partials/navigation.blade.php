<nav class="navbar bg-base-100 shadow-sm fixed top-0 left-0 right-0 z-[100] px-4 min-h-16">
    <div class="flex-1 gap-2 flex items-center">
        <!-- Logo -->
        <a href="/dashboard" class="flex items-center gap-2 mr-4 flex-shrink-0">
            <span class="icon-[tabler--checkbox] size-7 text-primary"></span>
            <span class="text-xl font-bold text-base-content hidden sm:inline">NewDone</span>
        </a>

        <!-- Main Navigation -->
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
                <a href="/calendar" class="{{ request()->is('calendar*') ? 'active' : '' }}">
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
                    <li><a class="dropdown-item" href="/ideas"><span class="icon-[tabler--bulb] size-4 me-2"></span>Ideas / Feedback</a></li>
                    @if(auth()->user()->isAdminOrHigher())
                    <li><a class="dropdown-item" href="/users"><span class="icon-[tabler--user-circle] size-4 me-2"></span>Users</a></li>
                    @endif
                    <li><a class="dropdown-item" href="/workflows"><span class="icon-[tabler--git-branch] size-4 me-2"></span>Workflows</a></li>
                    <li><a class="dropdown-item" href="/drive"><span class="icon-[tabler--cloud] size-4 me-2"></span>Drive</a></li>
                    <li><a class="dropdown-item" href="/documents"><span class="icon-[tabler--file-text] size-4 me-2"></span>Documents</a></li>
                </ul>
            </li>
        </ul>
    </div>

    <div class="flex-none gap-2 flex items-center">
        <!-- Add Button -->
        <div class="dropdown relative inline-flex [--auto-close:inside] [--offset:8] [--placement:bottom-end]">
            <button id="add-dropdown" type="button" class="dropdown-toggle btn btn-primary btn-sm gap-1" aria-haspopup="menu" aria-expanded="false" aria-label="Add menu">
                <span class="icon-[tabler--plus] size-4"></span>
                <span class="hidden sm:inline">Add</span>
                <span class="icon-[tabler--chevron-down] dropdown-open:rotate-180 size-4 transition-transform"></span>
            </button>
            <ul class="dropdown-menu dropdown-open:opacity-100 hidden min-w-52" role="menu" aria-orientation="vertical" aria-labelledby="add-dropdown">
                <li><a class="dropdown-item" href="#"><span class="icon-[tabler--checkbox] size-4 me-2 text-primary"></span>Add Task</a></li>
                <li><a class="dropdown-item" href="#"><span class="icon-[tabler--message-plus] size-4 me-2 text-success"></span>Add Discussion</a></li>
                <li><a class="dropdown-item" href="{{ route('workspace.create') }}"><span class="icon-[tabler--briefcase] size-4 me-2 text-info"></span>Add Workspace</a></li>
                <li><a class="dropdown-item" href="#"><span class="icon-[tabler--file-plus] size-4 me-2 text-warning"></span>Add Document</a></li>
                <li><a class="dropdown-item" href="#"><span class="icon-[tabler--user-plus] size-4 me-2 text-secondary"></span>Add Guest</a></li>
                <li><a class="dropdown-item" href="#"><span class="icon-[tabler--bulb] size-4 me-2 text-accent"></span>Add Idea</a></li>
                <li><a class="dropdown-item" href="#"><span class="icon-[tabler--clock-plus] size-4 me-2 text-error"></span>Add Time</a></li>
            </ul>
        </div>

        <!-- Settings -->
        <a href="/settings" class="btn btn-ghost btn-circle btn-sm">
            <span class="icon-[tabler--settings] size-5"></span>
        </a>

        <!-- App Marketplace -->
        <a href="/marketplace" class="btn btn-ghost btn-circle btn-sm hidden sm:flex">
            <span class="icon-[tabler--apps] size-5"></span>
        </a>

        <!-- Notifications -->
        <div class="dropdown relative inline-flex [--auto-close:inside] [--offset:8] [--placement:bottom-end]">
            <button id="notifications-dropdown" type="button" class="dropdown-toggle btn btn-ghost btn-circle btn-sm indicator" aria-haspopup="menu" aria-expanded="false" aria-label="Notifications">
                <span class="icon-[tabler--bell] size-5"></span>
                <span class="badge badge-primary badge-xs indicator-item">3</span>
            </button>
            <div class="dropdown-menu dropdown-open:opacity-100 hidden w-80" role="menu" aria-orientation="vertical" aria-labelledby="notifications-dropdown">
                <div class="p-4 border-b border-base-200">
                    <h3 class="font-semibold">Notifications</h3>
                </div>
                <ul class="p-2 max-h-64 overflow-y-auto">
                    <li class="p-2 hover:bg-base-200 rounded cursor-pointer">
                        <p class="text-sm">Welcome to NewDone! Start by creating your first workspace.</p>
                        <span class="text-xs text-base-content/50">Just now</span>
                    </li>
                </ul>
                <div class="p-2 border-t border-base-200">
                    <a href="/notifications" class="btn btn-ghost btn-sm w-full">View All</a>
                </div>
            </div>
        </div>

        <!-- Profile Dropdown -->
        <div class="dropdown relative inline-flex [--auto-close:inside] [--offset:8] [--placement:bottom-end]">
            <button id="profile-dropdown" type="button" class="dropdown-toggle btn btn-ghost gap-2 px-2" aria-haspopup="menu" aria-expanded="false" aria-label="Profile menu">
                <div class="avatar {{ auth()->user()->avatar_url ? '' : 'placeholder' }}">
                    @if(auth()->user()->avatar_url)
                        <div class="w-8 h-8 rounded-full overflow-hidden">
                            <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->full_name }}" class="w-full h-full object-cover" />
                        </div>
                    @else
                        <div class="bg-primary text-primary-content rounded-full w-8 h-8 flex items-center justify-center">
                            <span class="text-sm">{{ auth()->user()->initials }}</span>
                        </div>
                    @endif
                </div>
                <span class="hidden md:inline text-sm font-medium">{{ auth()->user()->first_name ?? 'User' }}</span>
                <span class="icon-[tabler--chevron-down] dropdown-open:rotate-180 size-4 hidden md:inline transition-transform"></span>
            </button>
            <ul class="dropdown-menu dropdown-open:opacity-100 hidden min-w-56" role="menu" aria-orientation="vertical" aria-labelledby="profile-dropdown">
                <li class="dropdown-header px-3 py-2 border-b border-base-200 mb-2">
                    <p class="text-xs text-base-content/50">{{ auth()->user()->email ?? '' }}</p>
                </li>
                <li><a class="dropdown-item" href="/profile"><span class="icon-[tabler--user] size-4 me-2"></span>My Profile</a></li>
                <li><a class="dropdown-item" href="/profile/password"><span class="icon-[tabler--lock] size-4 me-2"></span>Update Password</a></li>
                <li><a class="dropdown-item" href="/profile/email-preferences"><span class="icon-[tabler--mail-cog] size-4 me-2"></span>Email Preferences</a></li>
                <li><a class="dropdown-item" href="/profile/activity"><span class="icon-[tabler--activity] size-4 me-2"></span>My Activity</a></li>
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
                <li><a class="dropdown-item" href="/dashboard"><span class="icon-[tabler--layout-dashboard] size-4 me-2"></span>Dashboard</a></li>
                <li><a class="dropdown-item" href="{{ route('workspace.index') }}"><span class="icon-[tabler--briefcase] size-4 me-2"></span>Workspaces</a></li>
                <li><a class="dropdown-item" href="/tasks"><span class="icon-[tabler--checkbox] size-4 me-2"></span>Tasks</a></li>
                <li><a class="dropdown-item" href="/discussions"><span class="icon-[tabler--messages] size-4 me-2"></span>Discussion</a></li>
                <li><a class="dropdown-item" href="/calendar"><span class="icon-[tabler--calendar] size-4 me-2"></span>Calendar</a></li>
                <li><hr class="border-base-content/10 my-2"></li>
                <li><a class="dropdown-item" href="/guests"><span class="icon-[tabler--users] size-4 me-2"></span>Guests</a></li>
                <li><a class="dropdown-item" href="/time"><span class="icon-[tabler--clock] size-4 me-2"></span>Time</a></li>
                <li><a class="dropdown-item" href="/ideas"><span class="icon-[tabler--bulb] size-4 me-2"></span>Ideas</a></li>
                @if(auth()->user()->isAdminOrHigher())
                <li><a class="dropdown-item" href="/users"><span class="icon-[tabler--user-circle] size-4 me-2"></span>Users</a></li>
                @endif
            </ul>
        </div>
    </div>
</nav>
