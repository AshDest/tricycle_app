<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-plus-circle me-2 text-primary"></i>Nouvelle Tournée
            </h4>
            <p class="text-muted mb-0">Planifier une nouvelle tournée de collecte</p>
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

    <div class="row g-4">
        <div class="col-lg-8">
            <form wire:submit="save">
                <div class="card mb-4">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-calendar-event me-2 text-primary"></i>Informations de la Tournée</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Collecteur <span class="text-danger">*</span></label>
                                <select wire:model="collecteur_id" class="form-select @error('collecteur_id') is-invalid @enderror">
                                    <option value="">-- Sélectionner un collecteur --</option>
                                    @foreach($collecteurs as $collecteur)
                                    <option value="{{ $collecteur->id }}">
                                        {{ $collecteur->user->name ?? 'N/A' }}
                                        @if($collecteur->zone_affectation)
                                        ({{ $collecteur->zone_affectation }})
                                        @endif
                                    </option>
                                    @endforeach
                                </select>
                                @error('collecteur_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Date de la tournée <span class="text-danger">*</span></label>
                                <input type="date" wire:model="date" class="form-control @error('date') is-invalid @enderror"
                                       min="{{ now()->format('Y-m-d') }}">
                                @error('date')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Zone de collecte <span class="text-danger">*</span></label>
                                <input type="text" wire:model="zone" class="form-control @error('zone') is-invalid @enderror"
                                       placeholder="Ex: Zone Nord, Gombe, Limete...">
                                @error('zone')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Heure de début prévue</label>
                                <input type="time" wire:model="heure_debut_prevue" class="form-control @error('heure_debut_prevue') is-invalid @enderror">
                                @error('heure_debut_prevue')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Heure de fin prévue</label>
                                <input type="time" wire:model="heure_fin_prevue" class="form-control @error('heure_fin_prevue') is-invalid @enderror">
                                @error('heure_fin_prevue')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Statut initial <span class="text-danger">*</span></label>
                                <select wire:model="statut" class="form-select @error('statut') is-invalid @enderror">
                                    <option value="">-- Choisir --</option>
                                    <option value="planifiee">Planifiée</option>
                                    <option value="confirmee">Confirmée</option>
                                </select>
                                @error('statut')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Boutons -->
                <div class="d-flex gap-3">
                    <button type="submit" class="btn btn-primary btn-lg flex-grow-1" wire:loading.attr="disabled">
                        <span wire:loading.remove>
                            <i class="bi bi-check-lg me-2"></i>Créer la Tournée
                        </span>
                        <span wire:loading>
                            <span class="spinner-border spinner-border-sm me-2"></span>Enregistrement...
                        </span>
                    </button>
                    <a href="{{ route('admin.tournees.index') }}" class="btn btn-outline-secondary btn-lg">
                        Annuler
                    </a>
                </div>
            </form>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-info-circle me-2 text-info"></i>Informations</h6>
                </div>
                <div class="card-body">
                    <p class="small text-muted mb-3">
                        Une tournée définit le parcours d'un collecteur pour récupérer les dépôts des caissiers dans une zone spécifique.
                    </p>
                    <ul class="list-unstyled small mb-0">
                        <li class="d-flex align-items-start gap-2 mb-2">
                            <i class="bi bi-check-circle text-success mt-1"></i>
                            <span>Choisissez un collecteur disponible</span>
                        </li>
                        <li class="d-flex align-items-start gap-2 mb-2">
                            <i class="bi bi-check-circle text-success mt-1"></i>
                            <span>Définissez la date et la zone</span>
                        </li>
                        <li class="d-flex align-items-start gap-2">
                            <i class="bi bi-check-circle text-success mt-1"></i>
                            <span>Les caissiers de la zone pourront déposer</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="card">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-list-check me-2 text-warning"></i>Statuts</h6>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush small">
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Planifiée</span>
                            <span class="badge bg-secondary">En préparation</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Confirmée</span>
                            <span class="badge bg-info">Prête à démarrer</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>En cours</span>
                            <span class="badge bg-warning">Collecte active</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Terminée</span>
                            <span class="badge bg-success">Collecte finie</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
