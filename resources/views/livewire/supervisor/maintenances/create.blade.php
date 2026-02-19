<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-tools me-2 text-info"></i>Nouvelle Maintenance
            </h4>
            <p class="text-muted mb-0">Enregistrer une intervention technique</p>
        </div>
        <a href="{{ route('supervisor.maintenances.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Retour
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
                                <select wire:model.live="moto_id" class="form-select @error('moto_id') is-invalid @enderror">
                                    <option value="">-- Sélectionner une moto --</option>
                                    @foreach($motos as $moto)
                                        <option value="{{ $moto->id }}">
                                            {{ $moto->plaque_immatriculation }}
                                            @if($moto->proprietaire) - {{ $moto->proprietaire->user->name ?? '' }} @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('moto_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Motard associé</label>
                                <select wire:model="motard_id" class="form-select @error('motard_id') is-invalid @enderror">
                                    <option value="">-- Aucun --</option>
                                    @foreach($motards as $motard)
                                        <option value="{{ $motard->id }}">{{ $motard->user->name ?? 'N/A' }}</option>
                                    @endforeach
                                </select>
                                @error('motard_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lien avec un accident -->
                @if(count($accidentsDisponibles) > 0)
                <div class="card mb-4 border-danger">
                    <div class="card-header py-3 bg-danger bg-opacity-10">
                        <h6 class="mb-0 fw-bold text-danger"><i class="bi bi-exclamation-triangle me-2"></i>Lier à un Accident (optionnel)</h6>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info small mb-3">
                            <i class="bi bi-info-circle me-2"></i>
                            Cette moto a des accidents déclarés non encore réparés. Sélectionnez un accident si cette maintenance est liée à une réparation suite à accident.
                        </div>
                        <select wire:model.live="accident_id" class="form-select">
                            <option value="">-- Pas lié à un accident --</option>
                            @foreach($accidentsDisponibles as $accident)
                                <option value="{{ $accident->id }}">
                                    {{ $accident->date_heure?->format('d/m/Y H:i') }} - {{ $accident->lieu }}
                                    ({{ ucfirst($accident->gravite) }}) - Est. {{ number_format($accident->estimation_cout ?? 0) }} FC
                                </option>
                            @endforeach
                        </select>
                        @if($accident_id)
                        <div class="mt-2 small text-success">
                            <i class="bi bi-check-circle me-1"></i>
                            Les informations de l'accident ont été pré-remplies ci-dessous.
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                <div class="card mb-4">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-wrench me-2 text-warning"></i>Détails de l'intervention</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Type de maintenance <span class="text-danger">*</span></label>
                                <select wire:model="type" class="form-select @error('type') is-invalid @enderror">
                                    @foreach($types as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Date d'intervention <span class="text-danger">*</span></label>
                                <input type="datetime-local" wire:model="date_intervention" class="form-control @error('date_intervention') is-invalid @enderror">
                                @error('date_intervention')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Description du problème <span class="text-danger">*</span></label>
                                <textarea wire:model="description" class="form-control @error('description') is-invalid @enderror" rows="3" placeholder="Décrivez le problème et l'intervention effectuée..."></textarea>
                                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-person-gear me-2 text-success"></i>Technicien / Garage</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Nom du technicien/garage</label>
                                <input type="text" wire:model="technicien_garage_nom" class="form-control" placeholder="Ex: Garage Central">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Téléphone</label>
                                <input type="text" wire:model="technicien_telephone" class="form-control" placeholder="+243 XXX XXX XXX">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Adresse</label>
                                <input type="text" wire:model="garage_adresse" class="form-control" placeholder="Quartier, commune">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-cash-stack me-2 text-success"></i>Coûts</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Coût des pièces (FC)</label>
                                <input type="number" wire:model="cout_pieces" class="form-control" min="0" step="100" placeholder="0">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Coût main d'œuvre (FC)</label>
                                <input type="number" wire:model="cout_main_oeuvre" class="form-control" min="0" step="100" placeholder="0">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Coût Total</label>
                                <div class="form-control bg-light fw-bold text-success">
                                    {{ number_format(($cout_pieces ?: 0) + ($cout_main_oeuvre ?: 0)) }} FC
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Qui a payé ? <span class="text-danger">*</span></label>
                                <select wire:model="qui_a_paye" class="form-select">
                                    @foreach($payeurs as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Prochain entretien prévu</label>
                                <input type="date" wire:model="prochain_entretien" class="form-control @error('prochain_entretien') is-invalid @enderror">
                                @error('prochain_entretien')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <div class="card mb-4">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-gear me-2 text-secondary"></i>Statut</h6>
                    </div>
                    <div class="card-body">
                        <label class="form-label fw-semibold">Statut de l'intervention <span class="text-danger">*</span></label>
                        <select wire:model="statut" class="form-select">
                            <option value="en_attente">En attente</option>
                            <option value="en_cours">En cours</option>
                            <option value="termine">Terminé</option>
                        </select>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-sticky me-2 text-warning"></i>Notes</h6>
                    </div>
                    <div class="card-body">
                        <textarea wire:model="notes" class="form-control" rows="4" placeholder="Notes additionnelles..."></textarea>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary w-100 mb-2" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="save">
                                <i class="bi bi-check-lg me-1"></i>Enregistrer la maintenance
                            </span>
                            <span wire:loading wire:target="save">
                                <span class="spinner-border spinner-border-sm me-1"></span>Enregistrement...
                            </span>
                        </button>
                        <a href="{{ route('supervisor.maintenances.index') }}" class="btn btn-outline-secondary w-100">
                            Annuler
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

