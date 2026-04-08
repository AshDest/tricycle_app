<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-camera-reels me-2 text-primary"></i>Nos Réalisations
            </h4>
            <p class="text-muted mb-0">Galerie des événements, projets et activités OKAMI</p>
        </div>
        <a href="{{ route('supervisor.realisations.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i>Nouvelle Réalisation
        </a>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Statistiques -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-4">
            <div class="card bg-primary bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="mb-0 fw-bold text-primary">{{ $stats['total'] }}</h4>
                    <small class="text-muted">Total</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-4">
            <div class="card bg-success bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="mb-0 fw-bold text-success">{{ $stats['published'] }}</h4>
                    <small class="text-muted">Publiées</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-4">
            <div class="card bg-warning bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="mb-0 fw-bold text-warning">{{ $stats['draft'] }}</h4>
                    <small class="text-muted">Brouillons</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card mb-4">
        <div class="card-body py-3">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Rechercher</label>
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control form-control-sm" placeholder="Titre, description, lieu...">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Catégorie</label>
                    <select wire:model.live="filterCategorie" class="form-select form-select-sm">
                        <option value="">Toutes</option>
                        @foreach($categories as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Statut</label>
                    <select wire:model.live="filterPublished" class="form-select form-select-sm">
                        <option value="">Tous</option>
                        <option value="1">Publiées</option>
                        <option value="0">Brouillons</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Du</label>
                    <input type="date" wire:model.live="dateFrom" class="form-control form-control-sm">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Au</label>
                    <input type="date" wire:model.live="dateTo" class="form-control form-control-sm">
                </div>
                <div class="col-md-1">
                    <button wire:click="resetFilters" class="btn btn-sm btn-outline-secondary w-100" title="Réinitialiser">
                        <i class="bi bi-x-circle"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Grille de réalisations -->
    @if($realisations->count() > 0)
    <div class="row g-4">
        @foreach($realisations as $realisation)
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 shadow-sm border-0 overflow-hidden">
                <!-- Image de couverture -->
                <div class="position-relative" style="height: 200px; overflow: hidden;">
                    @php
                        $cover = $realisation->first_media;
                    @endphp
                    @if($cover && $cover['type'] === 'image')
                        <img src="{{ \App\Services\MediaService::getPublicUrl($cover['thumbnail'] ?? $cover['path']) }}"
                             alt="{{ $realisation->titre }}"
                             class="w-100 h-100" style="object-fit: cover;">
                    @elseif($cover && $cover['type'] === 'video')
                        <div class="w-100 h-100 bg-dark d-flex align-items-center justify-content-center">
                            <i class="bi bi-play-circle text-white" style="font-size: 3rem;"></i>
                        </div>
                    @else
                        <div class="w-100 h-100 bg-light d-flex align-items-center justify-content-center">
                            <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
                        </div>
                    @endif

                    <!-- Badge statut -->
                    <span class="position-absolute top-0 end-0 m-2 badge {{ $realisation->is_published ? 'bg-success' : 'bg-warning text-dark' }}">
                        {{ $realisation->is_published ? 'Publiée' : 'Brouillon' }}
                    </span>

                    <!-- Badge catégorie -->
                    <span class="position-absolute top-0 start-0 m-2 badge bg-primary">
                        {{ $realisation->getCategorieLabel() }}
                    </span>

                    <!-- Badge nombre de médias -->
                    @if($realisation->media_count > 0)
                    <span class="position-absolute bottom-0 end-0 m-2 badge bg-dark bg-opacity-75">
                        <i class="bi bi-images me-1"></i>{{ $realisation->media_count }}
                    </span>
                    @endif
                </div>

                <div class="card-body">
                    <h6 class="card-title fw-bold mb-1">
                        <a href="{{ route('supervisor.realisations.show', $realisation) }}" class="text-decoration-none text-dark">
                            {{ Str::limit($realisation->titre, 50) }}
                        </a>
                    </h6>
                    <p class="text-muted small mb-2">
                        <i class="bi bi-calendar3 me-1"></i>{{ $realisation->date_realisation?->format('d/m/Y') }}
                        @if($realisation->lieu)
                            <span class="ms-2"><i class="bi bi-geo-alt me-1"></i>{{ $realisation->lieu }}</span>
                        @endif
                    </p>
                    @if($realisation->description)
                        <p class="text-muted small mb-0">{{ Str::limit($realisation->description, 100) }}</p>
                    @endif
                </div>

                <div class="card-footer bg-transparent border-top-0 pt-0 d-flex justify-content-between align-items-center">
                    <div class="btn-group btn-group-sm">
                        <a href="{{ route('supervisor.realisations.show', $realisation) }}" class="btn btn-outline-primary" title="Voir">
                            <i class="bi bi-eye"></i>
                        </a>
                        <a href="{{ route('supervisor.realisations.edit', $realisation) }}" class="btn btn-outline-info" title="Modifier">
                            <i class="bi bi-pencil"></i>
                        </a>
                    </div>
                    <div class="d-flex gap-1">
                        <button wire:click="togglePublish({{ $realisation->id }})" class="btn btn-sm {{ $realisation->is_published ? 'btn-outline-warning' : 'btn-outline-success' }}" title="{{ $realisation->is_published ? 'Dépublier' : 'Publier' }}">
                            <i class="bi {{ $realisation->is_published ? 'bi-eye-slash' : 'bi-globe' }}"></i>
                        </button>
                        <button wire:click="delete({{ $realisation->id }})" wire:confirm="Supprimer cette réalisation et tous ses médias ?" class="btn btn-sm btn-outline-danger" title="Supprimer">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Pagination -->
    <div class="mt-4 d-flex justify-content-center">
        {{ $realisations->links() }}
    </div>
    @else
    <div class="card border-0">
        <div class="card-body text-center py-5">
            <i class="bi bi-camera-reels text-muted" style="font-size: 4rem;"></i>
            <h5 class="mt-3 text-muted">Aucune réalisation trouvée</h5>
            <p class="text-muted">Commencez par créer votre première réalisation.</p>
            <a href="{{ route('supervisor.realisations.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>Créer une réalisation
            </a>
        </div>
    </div>
    @endif
</div>

