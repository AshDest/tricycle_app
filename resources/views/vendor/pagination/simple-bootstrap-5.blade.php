@if ($paginator->hasPages())
    <nav aria-label="Pagination" class="d-flex justify-content-between align-items-center">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <span class="btn btn-sm btn-outline-secondary disabled">
                &lsaquo; Précédent
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="btn btn-sm btn-outline-primary" rel="prev" wire:navigate>
                &lsaquo; Précédent
            </a>
        @endif

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="btn btn-sm btn-outline-primary" rel="next" wire:navigate>
                Suivant &rsaquo;
            </a>
        @else
            <span class="btn btn-sm btn-outline-secondary disabled">
                Suivant &rsaquo;
            </span>
        @endif
    </nav>
@endif
