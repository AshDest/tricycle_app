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
        <nav class="d-flex justify-content-between align-items-center py-2">
            @if ($paginator->onFirstPage())
                <span class="btn btn-sm btn-outline-secondary disabled">&lsaquo; Précédent</span>
            @else
                <button type="button" class="btn btn-sm btn-outline-primary" wire:click="previousPage('{{ $paginator->getPageName() }}')" wire:loading.attr="disabled">&lsaquo; Précédent</button>
            @endif

            @if ($paginator->hasMorePages())
                <button type="button" class="btn btn-sm btn-outline-primary" wire:click="nextPage('{{ $paginator->getPageName() }}')" wire:loading.attr="disabled">Suivant &rsaquo;</button>
            @else
                <span class="btn btn-sm btn-outline-secondary disabled">Suivant &rsaquo;</span>
            @endif
        </nav>
    @endif
</div>
