<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-plus-circle me-2 text-warning"></i>Nouvelle Maintenance
            </h4>
            <p class="text-muted mb-0">Enregistrer une intervention technique</p>
        </div>
        <a href="{{ route('admin.maintenances.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Retour
        </a>
    </div>

    <form wire:submit="save">
        <div class="row g-4">
            <!-- Informations de base -->
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-bicycle me-2 text-primary"></i>Véhicule concerné</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Moto <span class="text-danger">*</span></label>
                                <select wire:model="moto_id" class="form-select @error('moto_id') is-invalid @enderror">
                                    <option value="">-- Sélectionner une moto --</option>
                                    @foreach($motos as $moto)
                                    <option value="{{ $moto->id }}" {{ $moto_id == $moto->id ? 'selected' : '' }}>
                                        {{ $moto->plaque_immatriculation }} - {{ $moto->proprietaire->user->name ?? 'Sans propriétaire' }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('moto_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Type de maintenance <span class="text-danger">*</span></label>
                                <select wire:model="type" class="form-select @error('type') is-invalid @enderror">
                                    <option value="">-- Choisir --</option>
                                    <option value="preventive">Préventive (révision, vidange)</option>
                                    <option value="corrective">Corrective (réparation panne)</option>
                                    <option value="remplacement">Remplacement de pièces</option>
                                </select>
                                @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Description du problème / intervention <span class="text-danger">*</span></label>
                                <textarea wire:model="description" class="form-control @error('description') is-invalid @enderror" rows="4" placeholder="Décrivez le problème et l'intervention effectuée..."></textarea>
                                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Date de l'intervention <span class="text-danger">*</span></label>
                                <input type="date" wire:model="date_intervention" class="form-control @error('date_intervention') is-invalid @enderror">
                                @error('date_intervention')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Statut <span class="text-danger">*</span></label>
                                <select wire:model="statut" class="form-select @error('statut') is-invalid @enderror">
                                    <option value="en_attente">En attente</option>
                                    <option value="en_cours">En cours</option>
                                    <option value="termine">Terminé</option>
                                </select>
                                @error('statut')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-calendar-event me-1 text-info"></i>
                                    Prochain entretien recommandé
                                </label>
                                <input type="date" wire:model="prochain_entretien" class="form-control @error('prochain_entretien') is-invalid @enderror">
                                @error('prochain_entretien')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                <small class="text-muted">Date de la prochaine maintenance prévue pour cette moto</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-person-badge me-2 text-info"></i>Technicien / Garage</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nom du technicien / garage <span class="text-danger">*</span></label>
                                <input type="text" wire:model="technicien_garage_nom" class="form-control @error('technicien_garage_nom') is-invalid @enderror" placeholder="Garage Moto Pro">
                                @error('technicien_garage_nom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Téléphone <span class="text-danger">*</span></label>
                                <input type="text" wire:model="technicien_telephone" class="form-control @error('technicien_telephone') is-invalid @enderror" placeholder="+243 XXX XXX XXX">
                                @error('technicien_telephone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Adresse du garage <span class="text-danger">*</span></label>
                                <input type="text" wire:model="garage_adresse" class="form-control @error('garage_adresse') is-invalid @enderror" placeholder="Avenue X, Commune Y">
                                @error('garage_adresse')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar - Coûts -->
            <div class="col-lg-4">
                <div class="card mb-4">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-cash-coin me-2 text-success"></i>Coûts</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Coût des pièces</label>
                            <div class="input-group">
                                <input type="number" wire:model="cout_pieces" class="form-control @error('cout_pieces') is-invalid @enderror" placeholder="0" min="0">
                                <span class="input-group-text">FC</span>
                            </div>
                            @error('cout_pieces')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Coût main d'œuvre</label>
                            <div class="input-group">
                                <input type="number" wire:model="cout_main_oeuvre" class="form-control @error('cout_main_oeuvre') is-invalid @enderror" placeholder="0" min="0">
                                <span class="input-group-text">FC</span>
                            </div>
                            @error('cout_main_oeuvre')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between fw-bold">
                            <span>Total estimé:</span>
                            <span class="text-success">{{ number_format(($cout_pieces ?? 0) + ($cout_main_oeuvre ?? 0)) }} FC</span>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-wallet2 me-2 text-warning"></i>Prise en charge</h6>
                    </div>
                    <div class="card-body">
                        <label class="form-label fw-semibold">Qui a payé ? <span class="text-danger">*</span></label>
                        <select wire:model="qui_a_paye" class="form-select @error('qui_a_paye') is-invalid @enderror">
                            <option value="">-- Sélectionner --</option>
                            <option value="motard">Motard</option>
                            <option value="proprietaire">Propriétaire</option>
                            <option value="nth">NTH Sarl</option>
                            <option value="okami">OKAMI</option>
                        </select>
                        @error('qui_a_paye')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <!-- Boutons -->
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-lg" wire:loading.attr="disabled">
                        <span wire:loading.remove><i class="bi bi-check-lg me-2"></i>Enregistrer</span>
                        <span wire:loading><span class="spinner-border spinner-border-sm me-2"></span>Enregistrement...</span>
                    </button>
                    <a href="{{ route('admin.maintenances.index') }}" class="btn btn-outline-secondary">Annuler</a>
                </div>
            </div>
        </div>
    </form>
</div>
