<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-plus-circle me-2 text-primary"></i>Nouvelle Tournée
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.tournees.index') }}">Tournées</a></li>
                    <li class="breadcrumb-item active">Nouvelle</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('admin.tournees.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Retour
        </a>
    </div>

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-4">
        <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <form wire:submit="save">
        <div class="row">
            <!-- Colonne gauche - Infos tournée -->
            <div class="col-lg-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bi bi-calendar-event me-2"></i>Informations de la Tournée</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Date de la tournée <span class="text-danger">*</span></label>
                            <input type="date" wire:model="date" class="form-control @error('date') is-invalid @enderror">
                            @error('date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Collecteur <span class="text-danger">*</span></label>
                            <select wire:model="collecteur_id" class="form-select @error('collecteur_id') is-invalid @enderror">
                                <option value="">-- Sélectionner un collecteur --</option>
                                @foreach($collecteurs as $collecteur)
                                <option value="{{ $collecteur->id }}">
                                    {{ $collecteur->user->name }} ({{ $collecteur->numero_identifiant }})
                                </option>
                                @endforeach
                            </select>
                            @error('collecteur_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Zone <span class="text-danger">*</span></label>
                            <select wire:model.live="zone_id" class="form-select @error('zone_id') is-invalid @enderror">
                                <option value="">-- Sélectionner une zone --</option>
                                @foreach($zones as $zone)
                                <option value="{{ $zone->id }}">{{ $zone->nom }}</option>
                                @endforeach
                            </select>
                            @error('zone_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Heure début prévue</label>
                                    <input type="time" wire:model="heure_debut_prevue" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Heure fin prévue</label>
                                    <input type="time" wire:model="heure_fin_prevue" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Colonne droite - Sélection des caissiers -->
            <div class="col-lg-6">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="bi bi-people me-2"></i>Caissiers à visiter</h6>
                        @if($zone_id && $caissiers->count() > 0)
                        <div class="btn-group btn-group-sm">
                            <button type="button" wire:click="selectAllCaissiers" class="btn btn-outline-primary">
                                <i class="bi bi-check-all me-1"></i>Tout
                            </button>
                            <button type="button" wire:click="deselectAllCaissiers" class="btn btn-outline-secondary">
                                <i class="bi bi-x-lg me-1"></i>Aucun
                            </button>
                        </div>
                        @endif
                    </div>
                    <div class="card-body">
                        @if(!$zone_id)
                        <div class="alert alert-info mb-0">
                            <i class="bi bi-info-circle me-2"></i>
                            Sélectionnez d'abord une zone pour voir les caissiers disponibles.
                        </div>
                        @elseif($caissiers->count() == 0)
                        <div class="alert alert-warning mb-0">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Aucun caissier actif trouvé dans cette zone.
                        </div>
                        @else
                        <div class="mb-2">
                            <span class="badge bg-info">{{ count($caissiers_selectionnes) }} / {{ $caissiers->count() }} caissier(s) sélectionné(s)</span>
                        </div>
                        @error('caissiers_selectionnes')
                        <div class="alert alert-danger py-2 mb-3">{{ $message }}</div>
                        @enderror

                        <div class="list-group" style="max-height: 350px; overflow-y: auto;">
                            @foreach($caissiers as $caissier)
                            <label class="list-group-item list-group-item-action d-flex align-items-center gap-3" style="cursor: pointer;">
                                <input type="checkbox"
                                       wire:model="caissiers_selectionnes"
                                       value="{{ $caissier->id }}"
                                       class="form-check-input m-0">
                                <div class="flex-grow-1">
                                    <div class="fw-medium">{{ $caissier->user->name ?? 'N/A' }}</div>
                                    <small class="text-muted">
                                        {{ $caissier->nom_point_collecte }} •
                                        <span class="text-success fw-semibold">{{ number_format($caissier->solde_actuel ?? 0) }} FC</span>
                                    </small>
                                </div>
                                <span class="badge bg-light text-dark">{{ $caissier->numero_identifiant }}</span>
                            </label>
                            @endforeach
                        </div>

                        <div class="mt-3 p-3 bg-light rounded">
                            <div class="d-flex justify-content-between">
                                <span>Total attendu :</span>
                                <strong class="text-success">
                                    {{ number_format($caissiers->whereIn('id', $caissiers_selectionnes)->sum('solde_actuel')) }} FC
                                </strong>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Boutons d'action -->
        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('admin.tournees.index') }}" class="btn btn-light">
                <i class="bi bi-x-lg me-1"></i>Annuler
            </a>
            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                <span wire:loading.remove>
                    <i class="bi bi-check-lg me-1"></i>Créer la tournée
                </span>
                <span wire:loading>
                    <span class="spinner-border spinner-border-sm me-1"></span>Création...
                </span>
            </button>
        </div>
    </form>
</div>
