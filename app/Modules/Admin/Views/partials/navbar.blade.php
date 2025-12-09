<div class="navbar bg-base-100 shadow-sm border-b border-base-200">
    <div class="flex-none lg:hidden">
        <label for="admin-drawer" class="btn btn-square btn-ghost drawer-button">
            <span class="icon-[tabler--menu-2] size-5"></span>
        </label>
    </div>

    <div class="flex-1">
        <span class="text-lg font-semibold text-base-content/70">@yield('page-title', 'Dashboard')</span>
    </div>

    <div class="flex-none gap-2">
        <!-- Quick Actions -->
        <div class="dropdown dropdown-end">
            <div tabindex="0" role="button" class="btn btn-ghost btn-circle" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();this.click();}">
                <span class="icon-[tabler--plus] size-5"></span>
            </div>
            <ul tabindex="0" class="dropdown-menu dropdown-content mt-3 z-[1] p-2 shadow bg-base-100 rounded-box w-52">
                <li><a href="{{ route('backoffice.settings.admins.create') }}"><span class="icon-[tabler--user-plus] size-4 me-2"></span>Add Admin User</a></li>
            </ul>
        </div>

        <!-- User Menu -->
        <div class="dropdown dropdown-end">
            <div tabindex="0" role="button" class="btn btn-ghost btn-circle avatar" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();this.click();}">
                <div class="w-10 rounded-full">
                    <img src="{{ auth()->guard('admin')->user()->avatar_url }}" alt="{{ auth()->guard('admin')->user()->name }}" />
                </div>
            </div>
            <ul tabindex="0" class="dropdown-menu dropdown-content mt-3 z-[1] p-2 shadow bg-base-100 rounded-box w-52">
                <li class="menu-title">
                    <span class="text-xs">{{ auth()->guard('admin')->user()->name }}</span>
                    <span class="badge badge-{{ auth()->guard('admin')->user()->role->color() }} badge-xs">{{ auth()->guard('admin')->user()->role->label() }}</span>
                </li>
                <div class="divider my-1"></div>
                <li>
                    <form action="{{ route('backoffice.logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full text-left">
                            <span class="icon-[tabler--logout] size-4 me-2"></span>
                            Logout
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</div>
