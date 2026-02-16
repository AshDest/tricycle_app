<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-plus-circle me-2 text-info"></i>Nouvelle Moto
            </h4>
            <p class="text-muted mb-0">Enregistrer un nouveau moto-tricycle</p>
        </div>
        <a href="{{ route('supervisor.motos.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Retour à la liste
        </a>
    </div>

    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form wire:submit="save">
        <div class="row g-4">
            <!-- Informations principales -->
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-bicycle me-2 text-info"></i>Informations du Véhicule</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Plaque d'immatriculation <span class="text-danger">*</span></label>
                            <input type="text" wire:model="plaque_immatriculation" class="form-control @error('plaque_immatriculation') is-invalid @enderror" placeholder="ABC-123">
                            @error('plaque_immatriculation') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Numéro de châssis</label>
                            <input type="text" wire:model="numero_chassis" class="form-control @error('numero_chassis') is-invalid @enderror" placeholder="Numéro du châssis">
                            @error('numero_chassis') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Marque</label>
                                <input type="text" wire:model="marque" class="form-control" placeholder="Ex: Honda">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Modèle</label>
                                <input type="text" wire:model="modele" class="form-control" placeholder="Ex: CG125">
                            </div>
                        </div>

                        <div class="row g-3 mt-1">
                            <div class="col-md-6">
                                <label class="form-label">Année</label>
                                <input type="number" wire:model="annee" class="form-control" placeholder="2024" min="1990" max="{{ date('Y') + 1 }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Couleur</label>
                                <input type="text" wire:model="couleur" class="form-control" placeholder="Ex: Rouge">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Affectation -->
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-people me-2 text-primary"></i>Affectation</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Propriétaire <span class="text-danger">*</span></label>
                            <select wire:model="proprietaire_id" class="form-select @error('proprietaire_id') is-invalid @enderror">
                                <option value="">Sélectionner un propriétaire</option>
                                @foreach($proprietaires as $prop)
                                    <option value="{{ $prop->id }}">{{ $prop->user->name ?? 'N/A' }} {{ $prop->raison_sociale ? '('.$prop->raison_sociale.')' : '' }}</option>
                                @endforeach
                            </select>
                            @error('proprietaire_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Motard assigné</label>
                            <select wire:model="motard_id" class="form-select">
                                <option value="">Aucun (non assigné)</option>
                                @foreach($motards as $motard)
                                    <option value="{{ $motard->id }}">{{ $motard->user->name ?? 'N/A' }} ({{ $motard->numero_identifiant }})</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Seuls les motards sans moto assignée sont affichés</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Statut <span class="text-danger">*</span></label>
                            <select wire:model="statut" class="form-select @error('statut') is-invalid @enderror">
                                <option value="actif">Actif</option>
                                <option value="inactif">Inactif</option>
                                <option value="en_maintenance">En maintenance</option>
                            </select>
                            @error('statut') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="col-12">
                <div class="card">
                    <div class="card-body d-flex justify-content-end gap-2">
                        <a href="{{ route('supervisor.motos.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-lg me-1"></i>Annuler
                        </a>
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="save">
                                <i class="bi bi-check-lg me-1"></i>Enregistrer
                            </span>
                            <span wire:loading wire:target="save">
                                <span class="spinner-border spinner-border-sm me-1"></span>Enregistrement...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

