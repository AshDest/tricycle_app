@if ($paginator->hasPages())
    <nav aria-label="Pagination" class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        {{-- Mobile version --}}
        <div class="d-flex justify-content-between flex-fill d-sm-none">
            <ul class="pagination pagination-sm mb-0">
                @if ($paginator->onFirstPage())
                    <li class="page-item disabled">
                        <span class="page-link border-0 bg-transparent px-2"><i class="bi bi-chevron-left"></i></span>
                    </li>
                @else
                    <li class="page-item">
                        <a class="page-link border-0 bg-transparent px-2" href="{{ $paginator->previousPageUrl() }}" rel="prev" wire:navigate><i class="bi bi-chevron-left"></i></a>
                    </li>
                @endif

                <li class="page-item disabled">
                    <span class="page-link border-0 bg-transparent text-muted small">
                        {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}
                    </span>
                </li>

                @if ($paginator->hasMorePages())
                    <li class="page-item">
                        <a class="page-link border-0 bg-transparent px-2" href="{{ $paginator->nextPageUrl() }}" rel="next" wire:navigate><i class="bi bi-chevron-right"></i></a>
                    </li>
                @else
                    <li class="page-item disabled">
                        <span class="page-link border-0 bg-transparent px-2"><i class="bi bi-chevron-right"></i></span>
                    </li>
                @endif
            </ul>
        </div>

        {{-- Desktop version --}}
        <div class="d-none d-sm-flex align-items-center justify-content-between flex-fill gap-3">
            <p class="small text-muted mb-0">
                Affichage de <span class="fw-semibold">{{ $paginator->firstItem() ?? 0 }}</span>
                à <span class="fw-semibold">{{ $paginator->lastItem() ?? 0 }}</span>
                sur <span class="fw-semibold">{{ $paginator->total() }}</span> résultats
            </p>

            <ul class="pagination pagination-sm mb-0">
                {{-- Previous Page Link --}}
                @if ($paginator->onFirstPage())
                    <li class="page-item disabled">
                        <span class="page-link rounded-start" aria-hidden="true"><i class="bi bi-chevron-left"></i></span>
                    </li>
                @else
                    <li class="page-item">
                        <a class="page-link rounded-start" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Précédent" wire:navigate><i class="bi bi-chevron-left"></i></a>
                    </li>
                @endif

                {{-- Pagination Elements --}}
                @foreach ($elements as $element)
                    {{-- "Three Dots" Separator --}}
                    @if (is_string($element))
                        <li class="page-item disabled"><span class="page-link">{{ $element }}</span></li>
                    @endif

                    {{-- Array Of Links --}}
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                            @else
                                <li class="page-item"><a class="page-link" href="{{ $url }}" wire:navigate>{{ $page }}</a></li>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                {{-- Next Page Link --}}
                @if ($paginator->hasMorePages())
                    <li class="page-item">
                        <a class="page-link rounded-end" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Suivant" wire:navigate><i class="bi bi-chevron-right"></i></a>
                    </li>
                @else
                    <li class="page-item disabled">
                        <span class="page-link rounded-end" aria-hidden="true"><i class="bi bi-chevron-right"></i></span>
                    </li>
                @endif
            </ul>
        </div>
    </nav>
@endif
