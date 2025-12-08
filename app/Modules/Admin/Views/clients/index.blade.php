@extends('admin::layouts.app')

@section('title', 'Clients')
@section('page-title', 'Clients')

@section('content')
<div class="space-y-6">
    <!-- Header with Filters -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-base-content">Clients</h1>
            <p class="text-base-content/60">Manage all client accounts</p>
        </div>
        <div class="flex items-center gap-3">
            <div class="relative">
                <span class="icon-[tabler--search] size-4 absolute left-3 top-1/2 -translate-y-1/2 text-base-content/40"></span>
                <input type="text" id="search-filter" placeholder="Search by name, email..." class="input input-bordered input-sm pl-9 w-64" />
            </div>
            <select id="status-filter" class="select select-bordered select-sm">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="paused">Paused</option>
            </select>
            <button type="button" id="clear-filters" class="btn btn-ghost btn-sm hidden">
                <span class="icon-[tabler--x] size-4"></span>
                Clear
            </button>
        </div>
    </div>

    <!-- Clients Table -->
    <div class="card bg-base-100 shadow">
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table" id="clients-table">
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>Owner Full Name</th>
                            <th>Email Address</th>
                            <th>Team Members</th>
                            <th>Plan</th>
                            <th>Workspace</th>
                            <th>Task</th>
                            <th>Discussion</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($companies as $company)
                            <tr class="client-row"
                                data-name="{{ strtolower($company->owner?->name ?? '') }}"
                                data-company="{{ strtolower($company->name) }}"
                                data-email="{{ strtolower($company->owner?->email ?? '') }}"
                                data-status="{{ ($company->paused_at ?? false) ? 'paused' : 'active' }}">
                                <td>
                                    @if($company->paused_at ?? false)
                                        <span class="badge badge-warning badge-sm">Paused</span>
                                    @else
                                        <span class="badge badge-success badge-sm">Active</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="avatar placeholder">
                                            <div class="bg-primary text-primary-content rounded-full w-10 h-10">
                                                <span>{{ strtoupper(substr($company->owner?->name ?? $company->name, 0, 2)) }}</span>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="font-medium">{{ $company->owner?->name ?? 'N/A' }}</div>
                                            <div class="text-xs text-base-content/60">{{ $company->name }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $company->owner?->email ?? 'N/A' }}</td>
                                <td>
                                    <span class="badge badge-ghost">{{ $company->users_count ?? 0 }}</span>
                                </td>
                                <td>
                                    <span class="badge badge-outline badge-sm">
                                        {{ $company->isOnTrial() ? 'Trial' : 'Active' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-ghost">{{ $company->workspaces_count ?? 0 }}</span>
                                </td>
                                <td>
                                    <span class="badge badge-ghost">{{ $company->tasks_count ?? 0 }}</span>
                                </td>
                                <td>
                                    <span class="badge badge-ghost">{{ $company->discussions_count ?? 0 }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('backoffice.clients.show', $company) }}" class="btn btn-ghost btn-sm">
                                        <span class="icon-[tabler--eye] size-4"></span>
                                        View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr id="no-clients-row">
                                <td colspan="9" class="text-center py-8">
                                    <span class="icon-[tabler--users-off] size-12 text-base-content/20 mb-2"></span>
                                    <p class="text-base-content/60">No clients found</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- No results message (hidden by default) -->
            <div id="no-results" class="hidden text-center py-8">
                <span class="icon-[tabler--search-off] size-12 text-base-content/20 block mx-auto mb-2"></span>
                <p class="text-base-content/60">No clients match your filters</p>
            </div>

            @if($companies->hasPages())
                <div class="p-4 border-t border-base-200">
                    {{ $companies->links() }}
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
    const rows = document.querySelectorAll('.client-row');
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
            const company = row.dataset.company || '';
            const email = row.dataset.email || '';
            const status = row.dataset.status || '';

            const matchesSearch = !searchTerm ||
                name.includes(searchTerm) ||
                company.includes(searchTerm) ||
                email.includes(searchTerm);

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
