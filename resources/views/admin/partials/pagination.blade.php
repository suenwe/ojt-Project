{{-- Page switch buttons for admin tables: {{ $items->onEachSide(1)->links('admin.partials.pagination') }} --}}
@if ($paginator->hasPages())
    <nav class="pager" role="navigation" aria-label="Pages">
        <p class="pager-summary">Showing {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} of {{ number_format($paginator->total()) }}</p>

        <div class="pager-links">
            @if ($paginator->onFirstPage())
                <span class="pager-button is-disabled" aria-disabled="true">‹ Prev</span>
            @else
                <a class="pager-button" href="{{ $paginator->previousPageUrl() }}" rel="prev">‹ Prev</a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="pager-gap" aria-hidden="true">…</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="pager-button is-current" aria-current="page">{{ $page }}</span>
                        @else
                            <a class="pager-button" href="{{ $url }}" aria-label="Go to page {{ $page }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a class="pager-button" href="{{ $paginator->nextPageUrl() }}" rel="next">Next ›</a>
            @else
                <span class="pager-button is-disabled" aria-disabled="true">Next ›</span>
            @endif
        </div>
    </nav>
@endif
