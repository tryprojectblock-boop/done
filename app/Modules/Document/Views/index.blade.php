@extends('layouts.app')

@section('content')
<div class="p-4 md:p-6">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center gap-2 text-sm text-base-content/60 mb-2">
                <a href="{{ route('dashboard') }}" class="hover:text-primary">Dashboard</a>
                <span class="icon-[tabler--chevron-right] size-4"></span>
                <span>Documents</span>
            </div>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-base-content">Documents</h1>
                    <p class="text-base-content/60">Create and collaborate on documents with your team</p>
                </div>
                <a href="{{ route('documents.create') }}" class="btn btn-primary">
                    <span class="icon-[tabler--file-plus] size-5"></span>
                    New Document
                </a>
            </div>
        </div>

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

        <!-- Search & Sort -->
        <div class="flex flex-col md:flex-row gap-4 mb-6">
            <div class="flex-1">
                <div class="relative">
                    <span class="icon-[tabler--search] size-5 absolute left-3 top-1/2 -translate-y-1/2 text-base-content/50"></span>
                    <input type="text"
                           id="search-input"
                           value="{{ $filters['search'] ?? '' }}"
                           placeholder="Search documents..."
                           class="input input-bordered w-full pl-10"
                           autocomplete="off" />
                    <span id="search-loading" class="loading loading-spinner loading-sm absolute right-3 top-1/2 -translate-y-1/2 hidden"></span>
                </div>
            </div>
            <select id="sort-select" class="select select-bordered w-full md:w-48">
                <option value="last_edited_at-desc" {{ ($filters['sort'] ?? 'last_edited_at') === 'last_edited_at' && ($filters['direction'] ?? 'desc') === 'desc' ? 'selected' : '' }}>
                    Recently Edited
                </option>
                <option value="created_at-desc" {{ ($filters['sort'] ?? '') === 'created_at' ? 'selected' : '' }}>
                    Newest First
                </option>
                <option value="title-asc" {{ ($filters['sort'] ?? '') === 'title' ? 'selected' : '' }}>
                    Alphabetical
                </option>
            </select>
        </div>

        <!-- Documents Count -->
        <div id="documents-count" class="text-sm text-base-content/60 mb-4">
            {{ $documents->total() }} {{ Str::plural('document', $documents->total()) }}
        </div>

        <!-- Documents Grid -->
        <div id="documents-list">
            @if($documents->isEmpty())
                <div class="card bg-base-100 shadow">
                    <div class="card-body text-center py-12">
                        <div class="flex justify-center mb-4">
                            <span class="icon-[tabler--file-text] size-16 text-base-content/20"></span>
                        </div>
                        <h3 class="text-lg font-semibold text-base-content">No Documents Yet</h3>
                        <p class="text-base-content/60 mb-4">Create your first document to start collaborating with your team.</p>
                        <div>
                            <a href="{{ route('documents.create') }}" class="btn btn-primary">
                                <span class="icon-[tabler--plus] size-5"></span>
                                Create Document
                            </a>
                        </div>
                    </div>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($documents as $document)
                        @include('document::partials.document-card-new', ['document' => $document])
                    @endforeach
                </div>

                <!-- Pagination -->
                @if($documents->hasPages())
                    <div id="pagination" class="mt-6">
                        {{ $documents->withQueryString()->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search-input');
    const searchLoading = document.getElementById('search-loading');
    const documentsList = document.getElementById('documents-list');
    const documentsCount = document.getElementById('documents-count');
    const sortSelect = document.getElementById('sort-select');

    let searchTimeout = null;
    let currentSearch = searchInput.value;

    // Real-time search
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();

        searchTimeout = setTimeout(() => {
            if (query !== currentSearch) {
                currentSearch = query;
                performSearch();
            }
        }, 300);
    });

    // Sort change
    sortSelect.addEventListener('change', function() {
        performSearch();
    });

    async function performSearch() {
        const query = searchInput.value.trim();
        const [sort, direction] = sortSelect.value.split('-');

        searchLoading.classList.remove('hidden');

        try {
            const params = new URLSearchParams();
            if (query) params.set('search', query);
            params.set('sort', sort);
            params.set('direction', direction);
            params.set('ajax', '1');

            const response = await fetch(`{{ route('documents.index') }}?${params.toString()}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            // Update documents list
            documentsList.innerHTML = data.html;

            // Update count
            documentsCount.textContent = `${data.total} ${data.total === 1 ? 'document' : 'documents'}`;

            // Update URL without reload
            const url = new URL(window.location);
            if (query) {
                url.searchParams.set('search', query);
            } else {
                url.searchParams.delete('search');
            }
            url.searchParams.set('sort', sort);
            url.searchParams.set('direction', direction);
            window.history.replaceState({}, '', url);

        } catch (error) {
            console.error('Search error:', error);
        } finally {
            searchLoading.classList.add('hidden');
        }
    }
});
</script>
@endpush
@endsection
