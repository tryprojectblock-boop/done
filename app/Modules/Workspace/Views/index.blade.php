@extends('layouts.app')

@php
    $user = auth()->user();
    $isGuestOnly = $user->role === \App\Models\User::ROLE_GUEST && !$user->company_id;

    // Separate active and archived workspaces
    $activeWorkspaces = $workspaces->filter(fn($w) => $w->status !== \App\Modules\Workspace\Enums\WorkspaceStatus::ARCHIVED);
    $archivedWorkspaces = $workspaces->filter(fn($w) => $w->status === \App\Modules\Workspace\Enums\WorkspaceStatus::ARCHIVED);
@endphp

@section('content')
<div class="p-4 md:p-6">
    <div class="max-w mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center gap-2 text-sm text-base-content/60 mb-2">
                <a href="{{ route('dashboard') }}" class="hover:text-primary">Dashboard</a>
                <span class="icon-[tabler--chevron-right] size-4"></span>
                <span>Workspaces</span>
            </div>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-base-content">Workspaces</h1>
                    <p class="text-base-content/60">
                        @if($isGuestOnly)
                            Workspaces you have access to as a guest
                        @else
                            Manage your team workspaces
                        @endif
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <!-- Search -->
                    <!-- Search -->
                    <div class="relative">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none" class="absolute w-5 h-5 top-2.5 left-2.5 text-slate-600">
                            <path d="M12.5 8.33333C12.5 6.03215 10.6345 4.16667 8.33333 4.16667C6.03215 4.16667 4.16667 6.03215 4.16667 8.33333C4.16667 10.6345 6.03215 12.5 8.33333 12.5C10.6345 12.5 12.5 10.6345 12.5 8.33333ZM14.1667 8.33333C14.1667 11.555 11.555 14.1667 8.33333 14.1667C5.11167 14.1667 2.5 11.555 2.5 8.33333C2.5 5.11167 5.11167 2.5 8.33333 2.5C11.555 2.5 14.1667 5.11167 14.1667 8.33333Z" fill="#B8B7BB"/>
                            <path d="M11.4939 11.4939C11.799 11.1888 12.2815 11.17 12.6089 11.437L12.6723 11.4939L17.2557 16.0773L17.3126 16.1408C17.5796 16.4681 17.5608 16.9506 17.2557 17.2557C16.9506 17.5608 16.4681 17.5796 16.1408 17.3126L16.0773 17.2557L11.4939 12.6723L11.437 12.6089C11.17 12.2815 11.1888 11.799 11.4939 11.4939Z" fill="#B8B7BB"/>
                        </svg>
                        <input type="text"
                            id="workspace-search"
                            placeholder="Search Workspaces..."
                            class="pl-8 pr-3 py-2 w-3xs items-center gap-1 rounded-md border border-solid border-[#CBCBC9] bg-white outline-none leading-5"
                            autocomplete="off" />
                    </div>
                    @if(!$isGuestOnly)
                        <a href="{{ route('workspace.create') }}"
                        class="btn btn-primary flex pl-2 py-2 pr-3 items-center gap-1 rounded-md noShadow-btn">
                        
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                <path d="M10 2.5C10.4601 2.5 10.8328 2.87292 10.833 3.33301V9.16699H16.667C17.1271 9.16717 17.5 9.53987 17.5 10C17.5 10.4601 17.1271 10.8328 16.667 10.833H10.833V16.667C10.8328 17.1271 10.4601 17.5 10 17.5C9.53987 17.5 9.16717 17.1271 9.16699 16.667V10.833H3.33301C2.87292 10.8328 2.5 10.4601 2.5 10C2.5 9.53987 2.87292 9.16717 3.33301 9.16699H9.16699V3.33301C9.16717 2.87292 9.53987 2.5 10 2.5Z" fill="white"/>
                            </svg>
                            <span class="text-white font-semibold text-base leading-5 whitespace-nowrap">Add Workspace</span>
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Upgrade Required Message -->
        @if(session('upgrade_required'))
            <div class="alert alert-warning mb-4">
                <span class="icon-[tabler--alert-triangle] size-5"></span>
                <div class="flex-1">
                    <span>{{ session('upgrade_required') }}</span>
                </div>
                <a href="{{ route('guest.upgrade') }}" class="btn btn-success btn-sm">
                    <span class="icon-[tabler--rocket] size-4"></span>
                    Upgrade Now
                </a>
            </div>
        @endif

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

        <!-- Count & View Toggle -->
        @php
            $totalCount = $activeWorkspaces->count() + $otherCompanyWorkspaces->count() + $guestWorkspaces->count() + $archivedWorkspaces->count();
        @endphp
        <div class="flex items-center justify-between mb-4">
            <div id="workspace-count" class="text-sm text-base-content/60">
                {{ $totalCount }} {{ Str::plural('workspace', $totalCount) }}
            </div>
            <div class="flex items-center bg-[#EDECF0] rounded-lg p-0.5 grid-active-status">
                <button type="button" id="view-grid" class="btn btn-sm bg-transparent btn-ghost border-none noShadow-btn" title="Grid view">
                <span class="icon-[tabler--layout-grid] size-5 bg-[#B8B7BB]"></span>
                </button>
                <button type="button" id="view-list" class="btn btn-sm bg-transparent btn-ghost border-none noShadow-btn" title="List view">
                <span class="icon-[tabler--list] size-5 bg-[#B8B7BB]"></span>
                </button>
            </div>
        </div>

        <!-- Workspaces List -->
        @if($activeWorkspaces->isEmpty() && $otherCompanyWorkspaces->isEmpty() && $guestWorkspaces->isEmpty() && $archivedWorkspaces->isEmpty())
            <div class="card bg-base-100 shadow">
                <div class="card-body text-center py-12">
                    <div class="flex justify-center mb-4">
                        <span class="icon-[tabler--briefcase] size-16 text-base-content/20"></span>
                    </div>
                    @if($isGuestOnly)
                        <h3 class="text-lg font-semibold text-base-content">No Workspaces Yet</h3>
                        <p class="text-base-content/60 mb-4">You haven't been invited to any workspaces yet. Once someone adds you to a workspace, it will appear here.</p>
                        <div class="flex flex-col items-center gap-4">
                            <div class="text-sm text-base-content/50">Want to create your own workspaces?</div>
                            <a href="{{ route('guest.upgrade') }}" class="btn btn-success">
                                <span class="icon-[tabler--rocket] size-5"></span>
                                Upgrade Your Account
                            </a>
                        </div>
                    @else
                        <h3 class="text-lg font-semibold text-base-content">No Workspaces Yet</h3>
                        <p class="text-base-content/60 mb-4">Create your first workspace to start organizing your projects and team.</p>
                        <div>
                            <a href="{{ route('workspace.create') }}" class="btn btn-primary">
                                <span class="icon-[tabler--plus] size-5"></span>
                                Create Workspace
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        @else
            @if($activeWorkspaces->isNotEmpty())
                <!-- Grid View -->
                <div id="workspaces-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($activeWorkspaces as $workspace)
                        @include('workspace::partials.workspace-grid-card', ['workspace' => $workspace, 'isGuest' => false])
                    @endforeach
                </div>

                <!-- List View -->
                <div id="workspaces-list" class="space-y-3 hidden">
                    @foreach($activeWorkspaces as $workspace)
                        @include('workspace::partials.workspace-card', ['workspace' => $workspace, 'isGuest' => false])
                    @endforeach
                </div>
            @endif
        @endif

        <!-- Other Company Workspaces Section -->
        @if($otherCompanyWorkspaces->isNotEmpty())
            <div class="mt-8">
                <div class="flex items-center gap-2 mb-4">
                    <span class="icon-[tabler--building] size-5 text-info"></span>
                    <h2 class="text-xl font-bold text-base-content">Other Teams</h2>
                    <span class="badge badge-info">{{ $otherCompanyWorkspaces->count() }}</span>
                </div>
                <p class="text-base-content/60 mb-4">Workspaces from other companies you've joined</p>

                <!-- Grid View -->
                <div id="other-company-workspaces-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($otherCompanyWorkspaces as $workspace)
                        @include('workspace::partials.workspace-grid-card', ['workspace' => $workspace, 'isGuest' => false, 'isOtherCompany' => true])
                    @endforeach
                </div>

                <!-- List View -->
                <div id="other-company-workspaces-list" class="space-y-3 hidden">
                    @foreach($otherCompanyWorkspaces as $workspace)
                        @include('workspace::partials.workspace-card', ['workspace' => $workspace, 'isGuest' => false, 'isOtherCompany' => true])
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Guest Workspaces Section -->
        @if($guestWorkspaces->isNotEmpty())
            <div class="mt-8">
                <div class="flex items-center gap-2 mb-4">
                    <span class="icon-[tabler--user-star] size-5 text-warning"></span>
                    <h2 class="text-xl font-bold text-base-content">Guest Access</h2>
                    <span class="badge badge-warning">{{ $guestWorkspaces->count() }}</span>
                </div>
                <p class="text-base-content/60 mb-4">Workspaces you've been invited to as a guest</p>

                <!-- Grid View -->
                <div id="guest-workspaces-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($guestWorkspaces as $workspace)
                        @include('workspace::partials.workspace-grid-card', ['workspace' => $workspace, 'isGuest' => true])
                    @endforeach
                </div>

                <!-- List View -->
                <div id="guest-workspaces-list" class="space-y-3 hidden">
                    @foreach($guestWorkspaces as $workspace)
                        @include('workspace::partials.workspace-card', ['workspace' => $workspace, 'isGuest' => true])
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Archived Workspaces Section -->
        @if($archivedWorkspaces->isNotEmpty())
            <div class="mt-8">
                <div class="flex items-center gap-2 mb-4">
                    <span class="icon-[tabler--archive] size-5 text-base-content/50"></span>
                    <h2 class="text-xl font-bold text-base-content/70">Archived</h2>
                    <span class="badge badge-ghost">{{ $archivedWorkspaces->count() }}</span>
                </div>
                <p class="text-base-content/50 mb-4">Workspaces that have been archived</p>

                <!-- Grid View -->
                <div id="archived-workspaces-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($archivedWorkspaces as $workspace)
                        @include('workspace::partials.workspace-grid-card', ['workspace' => $workspace, 'isGuest' => false])
                    @endforeach
                </div>

                <!-- List View -->
                <div id="archived-workspaces-list" class="space-y-3 hidden">
                    @foreach($archivedWorkspaces as $workspace)
                        @include('workspace::partials.workspace-card', ['workspace' => $workspace, 'isGuest' => false])
                    @endforeach
                </div>
            </div>
        @endif

    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const viewGridBtn = document.getElementById('view-grid');
    const viewListBtn = document.getElementById('view-list');
    const workspacesGrid = document.getElementById('workspaces-grid');
    const workspacesList = document.getElementById('workspaces-list');
    const otherCompanyGrid = document.getElementById('other-company-workspaces-grid');
    const otherCompanyList = document.getElementById('other-company-workspaces-list');
    const guestGrid = document.getElementById('guest-workspaces-grid');
    const guestList = document.getElementById('guest-workspaces-list');
    const archivedGrid = document.getElementById('archived-workspaces-grid');
    const archivedList = document.getElementById('archived-workspaces-list');
    const searchInput = document.getElementById('workspace-search');
    const workspaceCount = document.getElementById('workspace-count');

    // Get saved view preference, default to 'grid'
    const savedView = localStorage.getItem('workspaces-view') || 'grid';
    setView(savedView);

    viewGridBtn.addEventListener('click', () => setView('grid'));
    viewListBtn.addEventListener('click', () => setView('list'));

    // Search functionality
    searchInput.addEventListener('input', function() {
        const query = this.value.toLowerCase().trim();
        filterWorkspaces(query);
    });

    function filterWorkspaces(query) {
        let visibleCount = 0;

        // Filter grid view items
        if (workspacesGrid) {
            workspacesGrid.querySelectorAll(':scope > a').forEach(card => {
                const name = card.querySelector('h3').textContent.toLowerCase();
                const match = name.includes(query);
                card.style.display = match ? '' : 'none';
                if (match) visibleCount++;
            });
        }

        // Filter list view items
        if (workspacesList) {
            workspacesList.querySelectorAll(':scope > a').forEach(card => {
                const name = card.querySelector('h3').textContent.toLowerCase();
                card.style.display = name.includes(query) ? '' : 'none';
            });
        }

        // Filter other company grid view items
        if (otherCompanyGrid) {
            otherCompanyGrid.querySelectorAll(':scope > a').forEach(card => {
                const name = card.querySelector('h3').textContent.toLowerCase();
                const match = name.includes(query);
                card.style.display = match ? '' : 'none';
                if (match) visibleCount++;
            });
        }

        // Filter other company list view items
        if (otherCompanyList) {
            otherCompanyList.querySelectorAll(':scope > a').forEach(card => {
                const name = card.querySelector('h3').textContent.toLowerCase();
                card.style.display = name.includes(query) ? '' : 'none';
            });
        }

        // Filter guest grid view items
        if (guestGrid) {
            guestGrid.querySelectorAll(':scope > a').forEach(card => {
                const name = card.querySelector('h3').textContent.toLowerCase();
                const match = name.includes(query);
                card.style.display = match ? '' : 'none';
                if (match) visibleCount++;
            });
        }

        // Filter guest list view items
        if (guestList) {
            guestList.querySelectorAll(':scope > a').forEach(card => {
                const name = card.querySelector('h3').textContent.toLowerCase();
                card.style.display = name.includes(query) ? '' : 'none';
            });
        }

        // Filter archived grid view items
        if (archivedGrid) {
            archivedGrid.querySelectorAll(':scope > a').forEach(card => {
                const name = card.querySelector('h3').textContent.toLowerCase();
                const match = name.includes(query);
                card.style.display = match ? '' : 'none';
                if (match) visibleCount++;
            });
        }

        // Filter archived list view items
        if (archivedList) {
            archivedList.querySelectorAll(':scope > a').forEach(card => {
                const name = card.querySelector('h3').textContent.toLowerCase();
                card.style.display = name.includes(query) ? '' : 'none';
            });
        }

        // Update count
        if (workspaceCount) {
            workspaceCount.textContent = `${visibleCount} ${visibleCount === 1 ? 'workspace' : 'workspaces'}`;
        }
    }

    function setView(view) {
        localStorage.setItem('workspaces-view', view);

        if (view === 'grid') {
            viewGridBtn.classList.add('btn-active', 'btn-primary');
            viewGridBtn.classList.remove('btn-ghost');
            viewListBtn.classList.remove('btn-active', 'btn-primary');
            viewListBtn.classList.add('btn-ghost');

            if (workspacesGrid) workspacesGrid.classList.remove('hidden');
            if (workspacesList) workspacesList.classList.add('hidden');
            if (otherCompanyGrid) otherCompanyGrid.classList.remove('hidden');
            if (otherCompanyList) otherCompanyList.classList.add('hidden');
            if (guestGrid) guestGrid.classList.remove('hidden');
            if (guestList) guestList.classList.add('hidden');
            if (archivedGrid) archivedGrid.classList.remove('hidden');
            if (archivedList) archivedList.classList.add('hidden');
        } else {
            viewListBtn.classList.add('btn-active', 'btn-primary');
            viewListBtn.classList.remove('btn-ghost');
            viewGridBtn.classList.remove('btn-active', 'btn-primary');
            viewGridBtn.classList.add('btn-ghost');

            if (workspacesGrid) workspacesGrid.classList.add('hidden');
            if (workspacesList) workspacesList.classList.remove('hidden');
            if (otherCompanyGrid) otherCompanyGrid.classList.add('hidden');
            if (otherCompanyList) otherCompanyList.classList.remove('hidden');
            if (guestGrid) guestGrid.classList.add('hidden');
            if (guestList) guestList.classList.remove('hidden');
            if (archivedGrid) archivedGrid.classList.add('hidden');
            if (archivedList) archivedList.classList.remove('hidden');
        }
    }
});
</script>
@endpush
@endsection
