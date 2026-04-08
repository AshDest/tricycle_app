<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-camera-reels me-2 text-primary"></i>{{ $realisation->titre }}
            </h4>
            <p class="text-muted mb-0">
                <span class="badge {{ $realisation->is_published ? 'bg-success' : 'bg-warning text-dark' }} me-2">
                    {{ $realisation->is_published ? 'Publiée' : 'Brouillon' }}
                </span>
                <span class="badge bg-primary me-2">{{ $realisation->getCategorieLabel() }}</span>
                <i class="bi bi-calendar3 me-1"></i>{{ $realisation->date_realisation?->format('d/m/Y') }}
                @if($realisation->lieu)
                    <span class="ms-2"><i class="bi bi-geo-alt me-1"></i>{{ $realisation->lieu }}</span>
                @endif
            </p>
        </div>
        <div class="d-flex gap-2">
            <button wire:click="togglePublish" class="btn {{ $realisation->is_published ? 'btn-outline-warning' : 'btn-outline-success' }}">
                <i class="bi {{ $realisation->is_published ? 'bi-eye-slash' : 'bi-globe' }} me-1"></i>
                {{ $realisation->is_published ? 'Dépublier' : 'Publier' }}
            </button>
            <a href="{{ route('supervisor.realisations.edit', $realisation) }}" class="btn btn-outline-info">
                <i class="bi bi-pencil me-1"></i>Modifier
            </a>
            <button wire:click="delete" wire:confirm="Supprimer cette réalisation et tous ses médias ?" class="btn btn-outline-danger">
                <i class="bi bi-trash me-1"></i>Supprimer
            </button>
            <a href="{{ route('supervisor.realisations.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Retour
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row g-4">
        <!-- Détails -->
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-info-circle me-2 text-primary"></i>Informations</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless table-sm mb-0">
                        <tr>
                            <td class="text-muted fw-semibold" style="width: 40%;">Catégorie</td>
                            <td><span class="badge bg-primary">{{ $realisation->getCategorieLabel() }}</span></td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold">Date</td>
                            <td>{{ $realisation->date_realisation?->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold">Lieu</td>
                            <td>{{ $realisation->lieu ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold">Statut</td>
                            <td>
                                <span class="badge {{ $realisation->is_published ? 'bg-success' : 'bg-warning text-dark' }}">
                                    {{ $realisation->is_published ? 'Publiée' : 'Brouillon' }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold">Médias</td>
                            <td>{{ $realisation->media_count }} fichier(s)</td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold">Créé par</td>
                            <td>{{ $realisation->createdBy?->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-semibold">Créé le</td>
                            <td>{{ $realisation->created_at?->format('d/m/Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            @if($realisation->description)
            <div class="card mb-4">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-text-paragraph me-2 text-info"></i>Description</h6>
                </div>
                <div class="card-body">
                    <p class="mb-0" style="white-space: pre-line;">{{ $realisation->description }}</p>
                </div>
            </div>
            @endif
        </div>

        <!-- Galerie de médias -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-images me-2 text-info"></i>Galerie ({{ $realisation->media_count }})</h6>
                </div>
                <div class="card-body">
                    @php $media = $realisation->media ?? []; @endphp
                    @if(count($media) > 0)
                    <div class="row g-3">
                        @foreach($media as $index => $item)
                        <div class="col-md-6 col-lg-4">
                            <div class="card border position-relative h-100">
                                @if($item['type'] === 'image')
                                    <a href="{{ \App\Services\MediaService::getPublicUrl($item['path']) }}" target="_blank">
                                        <img src="{{ \App\Services\MediaService::getPublicUrl($item['thumbnail'] ?? $item['path']) }}"
                                             class="card-img-top" style="height: 180px; object-fit: cover;"
                                             alt="Média {{ $index + 1 }}">
                                    </a>
                                @elseif($item['type'] === 'video')
                                    <div class="position-relative">
                                        <video class="w-100" style="height: 180px; object-fit: cover;" preload="metadata">
                                            <source src="{{ \App\Services\MediaService::getPublicUrl($item['path']) }}" type="video/{{ pathinfo($item['path'], PATHINFO_EXTENSION) }}">
                                        </video>
                                        <a href="{{ \App\Services\MediaService::getPublicUrl($item['path']) }}" target="_blank" class="position-absolute top-50 start-50 translate-middle">
                                            <i class="bi bi-play-circle-fill text-white" style="font-size: 2.5rem; text-shadow: 0 0 10px rgba(0,0,0,0.5);"></i>
                                        </a>
                                    </div>
                                @endif

                                <div class="card-body p-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <small class="text-muted d-block text-truncate" style="max-width: 120px;" title="{{ $item['original_name'] ?? '' }}">
                                                {{ $item['original_name'] ?? 'Fichier' }}
                                            </small>
                                            <small class="text-muted">
                                                {{ \App\Services\MediaService::formatSize($item['size'] ?? 0) }}
                                                <span class="badge bg-{{ $item['type'] === 'image' ? 'info' : 'danger' }} ms-1" style="font-size: 9px;">
                                                    {{ strtoupper($item['type']) }}
                                                </span>
                                            </small>
                                        </div>
                                        <button wire:click="deleteMedia({{ $index }})" wire:confirm="Supprimer ce média ?" class="btn btn-sm btn-outline-danger" title="Supprimer">
                                            <i class="bi bi-trash" style="font-size: 12px;"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-center py-5">
                        <i class="bi bi-images text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-2">Aucun média pour cette réalisation.</p>
                        <a href="{{ route('supervisor.realisations.edit', $realisation) }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-plus me-1"></i>Ajouter des médias
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

