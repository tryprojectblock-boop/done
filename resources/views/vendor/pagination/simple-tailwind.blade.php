@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-between">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <span class="btn btn-soft btn-sm btn-disabled">
                <span class="icon-[tabler--chevron-left] size-4 rtl:rotate-180 me-1"></span>
                {!! __('pagination.previous') !!}
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="btn btn-soft btn-sm">
                <span class="icon-[tabler--chevron-left] size-4 rtl:rotate-180 me-1"></span>
                {!! __('pagination.previous') !!}
            </a>
        @endif

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="btn btn-soft btn-sm">
                {!! __('pagination.next') !!}
                <span class="icon-[tabler--chevron-right] size-4 rtl:rotate-180 ms-1"></span>
            </a>
        @else
            <span class="btn btn-soft btn-sm btn-disabled">
                {!! __('pagination.next') !!}
                <span class="icon-[tabler--chevron-right] size-4 rtl:rotate-180 ms-1"></span>
            </span>
        @endif
    </nav>
@endif
