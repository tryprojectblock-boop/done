@extends('admin::layouts.app')

@section('title', 'Workspaces')
@section('page-title', 'Workspaces')

@section('content')
<div class="space-y-6">
    <!-- Header with Filters -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-base-content">Workspaces</h1>
            <p class="text-base-content/60">View all workspaces across clients</p>
        </div>
        <div class="flex items-center gap-3">
            <div class="relative">
                <span class="icon-[tabler--search] size-4 absolute left-3 top-1/2 -translate-y-1/2 text-base-content/40"></span>
                <input type="text" id="search-filter" placeholder="Search by name, client, owner..." class="input input-bordered input-sm pl-9 w-64" />
            </div>
            <select id="status-filter" class="select select-bordered select-sm">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="archived">Archived</option>
            </select>
            <button type="button" id="clear-filters" class="btn btn-ghost btn-sm hidden">
                <span class="icon-[tabler--x] size-4"></span>
                Clear
            </button>
        </div>
    </div>

    <!-- Workspaces Table -->
    <div class="card bg-base-100 shadow">
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table" id="workspaces-table">
                    <thead>
                        <tr>
                            <th>Workspace Name</th>
                            <th>Client</th>
                            <th>Workspace Owner</th>
                            <th>Total Team Members</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($workspaces as $workspace)
                            <tr class="workspace-row"
                                data-name="{{ strtolower($workspace->name) }}"
                                data-client="{{ strtolower($workspace->owner?->company?->name ?? '') }}"
                                data-owner="{{ strtolower($workspace->owner?->name ?? '') }}"
                                data-status="{{ $workspace->status ?? 'active' }}">
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="avatar placeholder">
                                            <div class="bg-info text-info-content rounded w-10 h-10">
                                                <span>{{ strtoupper(substr($workspace->name, 0, 2)) }}</span>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="font-medium">{{ $workspace->name }}</div>
                                            @if($workspace->description)
                                                <div class="text-xs text-base-content/50 truncate max-w-[200px]">{{ $workspace->description }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($workspace->owner?->company)
                                        <a href="{{ route('backoffice.clients.show', $workspace->owner->company) }}" class="link link-primary">
                                            {{ $workspace->owner->company->name }}
                                        </a>
                                    @else
                                        <span class="text-base-content/50">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @if($workspace->owner)
                                        <div class="flex items-center gap-2">
                                            <div class="avatar">
                                                <div class="w-8 h-8 rounded-full">
                                                    <img src="{{ $workspace->owner->avatar_url }}" alt="{{ $workspace->owner->name }}" />
                                                </div>
                                            </div>
                                            <span>{{ $workspace->owner->name }}</span>
                                        </div>
                                    @else
                                        <span class="text-base-content/50">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-ghost">{{ $workspace->members_count ?? 0 }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('backoffice.workspaces.show', $workspace) }}" class="btn btn-ghost btn-sm">
                                        <span class="icon-[tabler--eye] size-4"></span>
                                        View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr id="no-workspaces-row">
                                <td colspan="5" class="text-center py-8">
                                    <span class="icon-[tabler--briefcase-off] size-12 text-base-content/20 block mx-auto mb-2"></span>
                                    <p class="text-base-content/60">No workspaces found</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- No results message (hidden by default) -->
            <div id="no-results" class="hidden text-center py-8">
                <span class="icon-[tabler--search-off] size-12 text-base-content/20 block mx-auto mb-2"></span>
                <p class="text-base-content/60">No workspaces match your filters</p>
            </div>

            @if($workspaces->hasPages())
                <div class="p-4 border-t border-base-200">
                    {{ $workspaces->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search-filter');
    const statusSelect = document.getElementById('status-filter');
    const clearBtn = document.getElementById('clear-filters');
    const rows = document.querySelectorAll('.workspace-row');
    const noResults = document.getElementById('no-results');

    function updateClearButton() {
        const hasFilters = searchInput.value.trim() !== '' || statusSelect.value !== '';
        if (hasFilters) {
            clearBtn.classList.remove('hidden');
        } else {
            clearBtn.classList.add('hidden');
        }
    }

    function filterTable() {
        const searchTerm = searchInput.value.toLowerCase().trim();
        const statusFilter = statusSelect.value;
        let visibleCount = 0;

        rows.forEach(row => {
            const name = row.dataset.name || '';
            const client = row.dataset.client || '';
            const owner = row.dataset.owner || '';
            const status = row.dataset.status || '';

            const matchesSearch = !searchTerm ||
                name.includes(searchTerm) ||
                client.includes(searchTerm) ||
                owner.includes(searchTerm);

            const matchesStatus = !statusFilter || status === statusFilter;

            if (matchesSearch && matchesStatus) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        // Show/hide no results message
        if (rows.length > 0) {
            if (visibleCount === 0) {
                noResults.classList.remove('hidden');
            } else {
                noResults.classList.add('hidden');
            }
        }

        updateClearButton();
    }

    function clearFilters() {
        searchInput.value = '';
        statusSelect.value = '';
        filterTable();
    }

    // Real-time filtering
    searchInput.addEventListener('input', filterTable);
    statusSelect.addEventListener('change', filterTable);
    clearBtn.addEventListener('click', clearFilters);
});
</script>
@endpush
@endsection
