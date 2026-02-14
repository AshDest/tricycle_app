@props([
    'title' => 'Page',
    'subtitle' => null,
    'icon' => 'bi-grid',
    'actions' => null
])

<div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h4 class="page-title mb-1">
            <i class="bi {{ $icon }} me-2 text-primary"></i>{{ $title }}
        </h4>
        @if($subtitle)
            <p class="text-muted mb-0">{{ $subtitle }}</p>
        @endif
    </div>
    @if($actions)
        <div class="d-flex align-items-center gap-2">
            {{ $actions }}
        </div>
    @endif
</div>
