@extends('layouts.app')

@section('content')
<div class="p-4 md:p-6">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center gap-2 text-sm text-base-content/60 mb-2">
                <a href="{{ route('dashboard') }}" class="hover:text-primary">Dashboard</a>
                <span class="icon-[tabler--chevron-right] size-4"></span>
                <a href="{{ route('documents.index') }}" class="hover:text-primary">Documents</a>
                <span class="icon-[tabler--chevron-right] size-4"></span>
                <a href="{{ route('documents.show', $document->uuid) }}" class="hover:text-primary">{{ Str::limit($document->title, 20) }}</a>
                <span class="icon-[tabler--chevron-right] size-4"></span>
                <a href="{{ route('documents.versions', $document->uuid) }}" class="hover:text-primary">Versions</a>
                <span class="icon-[tabler--chevron-right] size-4"></span>
                <span>v{{ $version->version_number }}</span>
            </div>
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-base-content flex items-center gap-2">
                        {{ $document->title }}
                        <span class="badge badge-ghost">Version {{ $version->version_number }}</span>
                    </h1>
                    <p class="text-base-content/60">
                        Saved by {{ $version->user->name }}
                        <span title="{{ $version->created_at->format('M d, Y g:i A') }}">
                            {{ $version->created_at->diffForHumans() }}
                        </span>
                        @if($version->change_summary)
                            &bull; "{{ $version->change_summary }}"
                        @endif
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    @if($version->version_number < $document->version_count && $document->canEdit(auth()->user()))
                        <form action="{{ route('documents.versions.restore', [$document->uuid, $version->id]) }}"
                              method="POST"
                              onsubmit="return confirm('Restore to this version? This will create a new version with this content.');">
                            @csrf
                            <button type="submit" class="btn btn-primary btn-sm">
                                <span class="icon-[tabler--restore] size-4"></span>
                                Restore This Version
                            </button>
                        </form>
                    @endif
                    <a href="{{ route('documents.versions', $document->uuid) }}" class="btn btn-ghost btn-sm">
                        <span class="icon-[tabler--arrow-left] size-4"></span>
                        Back to History
                    </a>
                </div>
            </div>
        </div>

        <!-- Version Info Banner -->
        <div class="alert alert-info mb-6">
            <span class="icon-[tabler--info-circle] size-5"></span>
            <div>
                <p class="font-medium">You're viewing a historical version</p>
                <p class="text-sm opacity-80">This is a read-only snapshot from {{ $version->created_at->format('M d, Y \a\t g:i A') }}</p>
            </div>
            <a href="{{ route('documents.show', $document->uuid) }}" class="btn btn-sm btn-ghost">
                View Current
            </a>
        </div>

        <!-- Document Pages -->
        @if($document->pages->count() > 0)
            <div class="mb-4">
                <h2 class="text-lg font-semibold mb-3 flex items-center gap-2">
                    <span class="icon-[tabler--files] size-5"></span>
                    Document Pages ({{ $document->pages->count() }})
                </h2>
                <div class="tabs tabs-boxed bg-base-200 mb-4">
                    @foreach($document->pages as $index => $page)
                        <button type="button"
                                class="tab page-tab {{ $index === 0 ? 'tab-active' : '' }}"
                                data-page-index="{{ $index }}">
                            {{ $page->title }}
                        </button>
                    @endforeach
                </div>
                @foreach($document->pages as $index => $page)
                    <div class="page-content card bg-base-100 shadow {{ $index === 0 ? '' : 'hidden' }}" data-page-index="{{ $index }}">
                        <div class="card-body">
                            <div class="flex items-center justify-between mb-4 pb-3 border-b border-base-200">
                                <h3 class="font-semibold text-lg">{{ $page->title }}</h3>
                                <span class="text-sm text-base-content/60">
                                    Page {{ $index + 1 }} of {{ $document->pages->count() }}
                                </span>
                            </div>
                            <div class="prose prose-sm max-w-none">
                                {!! $page->content !!}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <!-- Version Content (Legacy/Fallback) -->
        @if($version->content)
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    @if($document->pages->count() > 0)
                        <h3 class="font-semibold mb-3 text-base-content/60">Version Snapshot Content</h3>
                    @endif
                    <div class="prose prose-sm max-w-none">
                        {!! $version->content !!}
                    </div>
                </div>
            </div>
        @elseif($document->pages->count() === 0)
            <div class="card bg-base-100 shadow">
                <div class="card-body text-center py-12">
                    <span class="icon-[tabler--file-off] size-12 text-base-content/30 mx-auto mb-3"></span>
                    <p class="text-base-content/60">No content in this version</p>
                </div>
            </div>
        @endif

        <!-- Version Navigation -->
        <div class="flex items-center justify-between mt-6">
            @if($previousVersion)
                <a href="{{ route('documents.versions.view', [$document->uuid, $previousVersion->id]) }}"
                   class="btn btn-ghost btn-sm">
                    <span class="icon-[tabler--chevron-left] size-4"></span>
                    Version {{ $previousVersion->version_number }}
                </a>
            @else
                <div></div>
            @endif

            @if($nextVersion)
                <a href="{{ route('documents.versions.view', [$document->uuid, $nextVersion->id]) }}"
                   class="btn btn-ghost btn-sm">
                    Version {{ $nextVersion->version_number }}
                    <span class="icon-[tabler--chevron-right] size-4"></span>
                </a>
            @else
                <a href="{{ route('documents.show', $document->uuid) }}" class="btn btn-ghost btn-sm">
                    Current Version
                    <span class="icon-[tabler--chevron-right] size-4"></span>
                </a>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Page tab switching
    const pageTabs = document.querySelectorAll('.page-tab');
    const pageContents = document.querySelectorAll('.page-content');

    pageTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const pageIndex = this.dataset.pageIndex;

            // Update active tab
            pageTabs.forEach(t => t.classList.remove('tab-active'));
            this.classList.add('tab-active');

            // Show corresponding content
            pageContents.forEach(content => {
                if (content.dataset.pageIndex === pageIndex) {
                    content.classList.remove('hidden');
                } else {
                    content.classList.add('hidden');
                }
            });
        });
    });
});
</script>
@endpush
