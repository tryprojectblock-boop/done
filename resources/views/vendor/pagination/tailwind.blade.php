@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex items-center justify-between">
        {{-- Mobile View --}}
        <div class="flex justify-between flex-1 sm:hidden">
            @if ($paginator->onFirstPage())
                <span class="btn btn-soft btn-sm btn-disabled">
                    {!! __('pagination.previous') !!}
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="btn btn-soft btn-sm">
                    {!! __('pagination.previous') !!}
                </a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="btn btn-soft btn-sm">
                    {!! __('pagination.next') !!}
                </a>
            @else
                <span class="btn btn-soft btn-sm btn-disabled">
                    {!! __('pagination.next') !!}
                </span>
            @endif
        </div>

        {{-- Desktop View --}}
        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-base-content/70">
                    {!! __('Showing') !!}
                    @if ($paginator->firstItem())
                        <span class="font-medium">{{ $paginator->firstItem() }}</span>
                        {!! __('to') !!}
                        <span class="font-medium">{{ $paginator->lastItem() }}</span>
                    @else
                        {{ $paginator->count() }}
                    @endif
                    {!! __('of') !!}
                    <span class="font-medium">{{ $paginator->total() }}</span>
                    {!! __('results') !!}
                </p>
            </div>

            <div>
                <nav class="flex items-center gap-x-1">
                    {{-- Previous Page Link --}}
                    @if ($paginator->onFirstPage())
                        <span class="btn btn-soft btn-sm btn-square btn-disabled" aria-disabled="true" aria-label="{{ __('pagination.previous') }}">
                            <span class="icon-[tabler--chevron-left] size-5 rtl:rotate-180"></span>
                        </span>
                    @else
                        <a href="{{ $paginator->previousPageUrl() }}" class="btn btn-soft btn-sm btn-square" rel="prev" aria-label="{{ __('pagination.previous') }}">
                            <span class="icon-[tabler--chevron-left] size-5 rtl:rotate-180"></span>
                        </a>
                    @endif

                    {{-- Pagination Elements --}}
                    <div class="flex items-center gap-x-1">
                        @foreach ($elements as $element)
                            {{-- "Three Dots" Separator --}}
                            @if (is_string($element))
                                <span class="btn btn-soft btn-sm btn-square btn-disabled" aria-disabled="true">
                                    {{ $element }}
                                </span>
                            @endif

                            {{-- Array Of Links --}}
                            @if (is_array($element))
                                @foreach ($element as $page => $url)
                                    @if ($page == $paginator->currentPage())
                                        <span class="btn btn-primary btn-sm btn-square" aria-current="page">
                                            {{ $page }}
                                        </span>
                                    @else
                                        <a href="{{ $url }}" class="btn btn-soft btn-sm btn-square hover:btn-primary hover:text-primary-content" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">
                                            {{ $page }}
                                        </a>
                                    @endif
                                @endforeach
                            @endif
                        @endforeach
                    </div>

                    {{-- Next Page Link --}}
                    @if ($paginator->hasMorePages())
                        <a href="{{ $paginator->nextPageUrl() }}" class="btn btn-soft btn-sm btn-square" rel="next" aria-label="{{ __('pagination.next') }}">
                            <span class="icon-[tabler--chevron-right] size-5 rtl:rotate-180"></span>
                        </a>
                    @else
                        <span class="btn btn-soft btn-sm btn-square btn-disabled" aria-disabled="true" aria-label="{{ __('pagination.next') }}">
                            <span class="icon-[tabler--chevron-right] size-5 rtl:rotate-180"></span>
                        </span>
                    @endif
                </nav>
            </div>
        </div>
    </nav>
@endif
