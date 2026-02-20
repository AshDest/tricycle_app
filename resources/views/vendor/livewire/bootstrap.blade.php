@php
if (! isset($scrollTo)) {
    $scrollTo = 'body';
}

$scrollIntoViewJsSnippet = ($scrollTo !== false)
    ? <<<JS
       (\$el.closest('{$scrollTo}') || document.querySelector('{$scrollTo}')).scrollIntoView()
    JS
    : '';
@endphp

<div>
    @if ($paginator->hasPages())
        <nav class="d-flex align-items-center justify-content-between flex-wrap gap-2 py-2">
            {{-- Info résultats --}}
            <div class="text-muted small mb-0">
                Affichage de <span class="fw-semibold">{{ $paginator->firstItem() ?? 0 }}</span>
                à <span class="fw-semibold">{{ $paginator->lastItem() ?? 0 }}</span>
                sur <span class="fw-semibold">{{ $paginator->total() }}</span> résultats
            </div>

            {{-- Pagination --}}
            <ul class="pagination pagination-sm mb-0">
                {{-- Previous Page Link --}}
                @if ($paginator->onFirstPage())
                    <li class="page-item disabled">
                        <span class="page-link">&lsaquo;</span>
                    </li>
                @else
                    <li class="page-item">
                        <button type="button" class="page-link" wire:click="previousPage('{{ $paginator->getPageName() }}')" wire:loading.attr="disabled">&lsaquo;</button>
                    </li>
                @endif

                {{-- Pagination Elements --}}
                @foreach ($elements as $element)
                    @if (is_string($element))
                        <li class="page-item disabled"><span class="page-link">{{ $element }}</span></li>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <li class="page-item active" wire:key="paginator-{{ $paginator->getPageName() }}-page-{{ $page }}"><span class="page-link">{{ $page }}</span></li>
                            @else
                                <li class="page-item" wire:key="paginator-{{ $paginator->getPageName() }}-page-{{ $page }}"><button type="button" class="page-link" wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')">{{ $page }}</button></li>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                {{-- Next Page Link --}}
                @if ($paginator->hasMorePages())
                    <li class="page-item">
                        <button type="button" class="page-link" wire:click="nextPage('{{ $paginator->getPageName() }}')" wire:loading.attr="disabled">&rsaquo;</button>
                    </li>
                @else
                    <li class="page-item disabled">
                        <span class="page-link">&rsaquo;</span>
                    </li>
                @endif
            </ul>
        </nav>
    @endif
</div>
