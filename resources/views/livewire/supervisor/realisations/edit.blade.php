<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-pencil-square me-2 text-info"></i>Modifier la Réalisation
            </h4>
            <p class="text-muted mb-0">{{ $realisation->titre }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('supervisor.realisations.show', $realisation) }}" class="btn btn-outline-primary">
                <i class="bi bi-eye me-1"></i>Voir
            </a>
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

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-4">
        <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <form wire:submit="save">
        <div class="row g-4">
            <!-- Informations principales -->
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-info-circle me-2 text-primary"></i>Informations</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Titre <span class="text-danger">*</span></label>
                                <input type="text" wire:model="titre" class="form-control @error('titre') is-invalid @enderror">
                                @error('titre')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                                <input type="date" wire:model="date_realisation" class="form-control @error('date_realisation') is-invalid @enderror">
                                @error('date_realisation')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Lieu</label>
                                <input type="text" wire:model="lieu" class="form-control @error('lieu') is-invalid @enderror">
                                @error('lieu')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Catégorie <span class="text-danger">*</span></label>
                                <select wire:model="categorie" class="form-select @error('categorie') is-invalid @enderror">
                                    @foreach($categories as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('categorie')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6 d-flex align-items-end">
                                <div class="form-check form-switch">
                                    <input type="checkbox" wire:model="is_published" class="form-check-input" id="isPublished">
                                    <label class="form-check-label fw-semibold" for="isPublished">
                                        Publier cette réalisation
                                    </label>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Description</label>
                                <textarea wire:model="description" class="form-control @error('description') is-invalid @enderror" rows="5"></textarea>
                                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                <small class="text-muted">Max 5000 caractères</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Médias existants -->
                @php $existingMedia = $realisation->media ?? []; @endphp
                @if(count($existingMedia) > 0)
                <div class="card mb-4">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-images me-2 text-info"></i>Médias existants ({{ count($existingMedia) }})</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            @foreach($existingMedia as $index => $item)
                            <div class="col-md-4 col-lg-3">
                                <div class="card border position-relative h-100">
                                    @if($item['type'] === 'image')
                                        <img src="{{ \App\Services\MediaService::getPublicUrl($item['thumbnail'] ?? $item['path']) }}"
                                             class="card-img-top" style="height: 120px; object-fit: cover;"
                                             alt="Média {{ $index + 1 }}">
                                    @elseif($item['type'] === 'video')
                                        <div class="card-img-top bg-dark d-flex align-items-center justify-content-center" style="height: 120px;">
                                            <i class="bi bi-camera-video text-white" style="font-size: 2rem;"></i>
                                        </div>
                                    @endif
                                    <div class="card-body p-2">
                                        <small class="text-muted d-block text-truncate" style="font-size: 10px;">{{ $item['original_name'] ?? 'Fichier' }}</small>
                                        <small class="text-muted" style="font-size: 10px;">{{ \App\Services\MediaService::formatSize($item['size'] ?? 0) }}</small>
                                    </div>
                                    <button type="button" wire:click="deleteMedia({{ $index }})" wire:confirm="Supprimer ce média ?" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 rounded-circle" style="width: 24px; height: 24px; padding: 0; font-size: 11px;">
                                        <i class="bi bi-x"></i>
                                    </button>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Nouveaux médias + actions -->
            <div class="col-lg-4">
                <div class="card mb-4">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-cloud-arrow-up me-2 text-success"></i>Ajouter des médias</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <input type="file" wire:model="nouveaux_fichiers" class="form-control @error('nouveaux_fichiers.*') is-invalid @enderror" multiple accept="image/*,video/*">
                            @error('nouveaux_fichiers.*')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <small class="text-muted d-block mt-1">
                                <i class="bi bi-info-circle me-1"></i>Formats: JPG, PNG, GIF, WebP, MP4, MOV, AVI, WebM. Max 50 MB/fichier.
                            </small>
                        </div>

                        <!-- Indicateur de chargement -->
                        <div wire:loading wire:target="nouveaux_fichiers" class="text-center py-3">
                            <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                            <span class="ms-2 text-muted small">Chargement...</span>
                        </div>

                        <!-- Aperçu des nouveaux fichiers -->
                        @if(count($nouveaux_fichiers) > 0)
                        <div class="mt-3">
                            <h6 class="small fw-bold text-muted mb-2">{{ count($nouveaux_fichiers) }} nouveau(x) fichier(s)</h6>
                            <div class="row g-2">
                                @foreach($nouveaux_fichiers as $index => $fichier)
                                <div class="col-6">
                                    <div class="card border position-relative">
                                        @if(str_starts_with($fichier->getMimeType(), 'image/'))
                                            <img src="{{ $fichier->temporaryUrl() }}" class="card-img-top" style="height: 70px; object-fit: cover;" alt="Aperçu">
                                        @else
                                            <div class="card-img-top bg-dark d-flex align-items-center justify-content-center" style="height: 70px;">
                                                <i class="bi bi-camera-video text-white"></i>
                                            </div>
                                        @endif
                                        <button type="button" wire:click="removeNewFile({{ $index }})" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 rounded-circle" style="width: 20px; height: 20px; padding: 0; font-size: 9px;">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Boutons d'action -->
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-lg" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="save">
                            <i class="bi bi-check-lg me-1"></i>Enregistrer les modifications
                        </span>
                        <span wire:loading wire:target="save">
                            <span class="spinner-border spinner-border-sm me-1"></span>Enregistrement...
                        </span>
                    </button>
                    <a href="{{ route('supervisor.realisations.show', $realisation) }}" class="btn btn-outline-secondary">
                        Annuler
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>

