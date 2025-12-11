@extends('layouts.app')

@php
    $user = auth()->user();
    $isGuestOnly = $user->role === \App\Models\User::ROLE_GUEST && !$user->company_id;
@endphp

@section('content')
<div class="p-4 md:p-6">
    <div class="max-w-6xl mx-auto">
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
                    <div class="relative">
                        <span class="icon-[tabler--search] size-4 absolute left-3 top-1/2 -translate-y-1/2 text-base-content/50"></span>
                        <input type="text"
                               id="workspace-search"
                               placeholder="Search workspaces..."
                               class="input input-bordered input-sm w-48 pl-9"
                               autocomplete="off" />
                    </div>
                    @if(!$isGuestOnly)
                        <a href="{{ route('workspace.create') }}" class="btn btn-primary">
                            <span class="icon-[tabler--plus] size-5"></span>
                            Add Workspace
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
            $totalCount = $workspaces->count() + $otherCompanyWorkspaces->count() + $guestWorkspaces->count();
        @endphp
        <div class="flex items-center justify-between mb-4">
            <div id="workspace-count" class="text-sm text-base-content/60">
                {{ $totalCount }} {{ Str::plural('workspace', $totalCount) }}
            </div>
            <div class="flex items-center gap-1 bg-base-200 rounded-lg p-1">
                <button type="button" id="view-grid" class="btn btn-sm btn-ghost" title="Grid view">
                    <span class="icon-[tabler--layout-grid] size-4"></span>
                </button>
                <button type="button" id="view-list" class="btn btn-sm btn-ghost" title="List view">
                    <span class="icon-[tabler--list] size-4"></span>
                </button>
            </div>
        </div>

        <!-- Workspaces List -->
        @if($workspaces->isEmpty() && $otherCompanyWorkspaces->isEmpty() && $guestWorkspaces->isEmpty())
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
            @if($workspaces->isNotEmpty())
                <!-- Grid View -->
                <div id="workspaces-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($workspaces as $workspace)
                        @include('workspace::partials.workspace-grid-card', ['workspace' => $workspace, 'isGuest' => false])
                    @endforeach
                </div>

                <!-- List View -->
                <div id="workspaces-list" class="space-y-3 hidden">
                    @foreach($workspaces as $workspace)
                        @include('workspace::partials.workspace-card', ['workspace' => $workspace, 'isGuest' => false])
                    @endforeach
                </div>

                <!-- Pagination -->
                @if($workspaces->hasPages())
                    <div class="mt-6">
                        {{ $workspaces->links() }}
                    </div>
                @endif
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
        }
    }
});
</script>
@endpush
@endsection
