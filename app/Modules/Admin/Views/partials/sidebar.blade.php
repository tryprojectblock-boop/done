<div class="drawer-side z-40">
    <label for="admin-drawer" aria-label="close sidebar" class="drawer-overlay"></label>
    <aside class="bg-base-100 w-64 min-h-screen border-r border-base-200">
        <!-- Logo -->
        <div class="p-4 border-b border-base-200">
            <a href="{{ route('backoffice.dashboard') }}" class="flex items-center gap-3">
                <div class="w-10 h-10 bg-primary rounded-lg flex items-center justify-center">
                    <span class="icon-[tabler--shield-lock] size-5 text-primary-content"></span>
                </div>
                <div>
                    <div class="font-bold text-base-content">Admin Panel</div>
                    <div class="text-xs text-base-content/60">{{ config('app.name') }}</div>
                </div>
            </a>
        </div>

        <!-- Navigation -->
        <ul class="menu p-4 text-base-content">
            <!-- Dashboard -->
            <li>
                <a href="{{ route('backoffice.dashboard') }}" class="{{ request()->routeIs('backoffice.dashboard') ? 'active' : '' }}">
                    <span class="icon-[tabler--dashboard] size-5"></span>
                    Dashboard
                </a>
            </li>

            <li class="menu-title mt-4">
                <span>Management</span>
            </li>

            <!-- Clients -->
            <li>
                <a href="{{ route('backoffice.clients.index') }}" class="{{ request()->routeIs('backoffice.clients.*') ? 'active' : '' }}">
                    <span class="icon-[tabler--users] size-5"></span>
                    Clients
                </a>
            </li>

            <!-- Workspaces -->
            <li>
                <a href="{{ route('backoffice.workspaces.index') }}" class="{{ request()->routeIs('backoffice.workspaces.*') ? 'active' : '' }}">
                    <span class="icon-[tabler--briefcase] size-5"></span>
                    Workspaces
                </a>
            </li>

            <li class="menu-title mt-4">
                <span>Billing</span>
            </li>

            <!-- Plans & Coupons -->
            <li>
                <a href="{{ route('backoffice.plans.index') }}" class="{{ request()->routeIs('backoffice.plans.*') ? 'active' : '' }}">
                    <span class="icon-[tabler--credit-card] size-5"></span>
                    Plans & Coupons
                </a>
            </li>

            <!-- Invoices & Payments -->
            <li>
                <a href="{{ route('backoffice.invoices.index') }}" class="{{ request()->routeIs('backoffice.invoices.*') ? 'active' : '' }}">
                    <span class="icon-[tabler--receipt] size-5"></span>
                    Invoices & Payments
                </a>
            </li>

            <li class="menu-title mt-4">
                <span>Settings</span>
            </li>

            <!-- App Settings -->
            @if(auth()->guard('admin')->user()->canManageSettings())
            <li>
                <a href="{{ route('backoffice.settings.app') }}" class="{{ request()->routeIs('backoffice.settings.app*') ? 'active' : '' }}">
                    <span class="icon-[tabler--settings] size-5"></span>
                    App Settings
                </a>
            </li>
            @endif

            <!-- Admin Users -->
            @if(auth()->guard('admin')->user()->canManageAdmins())
            <li>
                <a href="{{ route('backoffice.settings.admins.index') }}" class="{{ request()->routeIs('backoffice.settings.admins.*') ? 'active' : '' }}">
                    <span class="icon-[tabler--user-cog] size-5"></span>
                    Admin Users
                </a>
            </li>
            @endif
        </ul>

        <!-- Footer -->
        <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-base-200">
            <div class="text-xs text-base-content/50 text-center">
                Admin Panel v1.0
            </div>
        </div>
    </aside>
</div>
