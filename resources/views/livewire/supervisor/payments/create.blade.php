<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-plus-circle me-2 text-success"></i>Nouvelle Demande de Paiement
            </h4>
            <p class="text-muted mb-0">Soumettre une demande de paiement au bénéfice d'un propriétaire</p>
        </div>
        <a href="{{ route('supervisor.payments.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Retour
        </a>
    </div>

    <div class="row g-4">
        <!-- Formulaire -->
        <div class="col-lg-8">
            <form wire:submit="submit">
                <div class="card mb-4">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-person me-2 text-primary"></i>Propriétaire</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Sélectionner le propriétaire <span class="text-danger">*</span></label>
                            <select wire:model.live="proprietaire_id" class="form-select @error('proprietaire_id') is-invalid @enderror">
                                <option value="">-- Choisir un propriétaire --</option>
                                @foreach($proprietaires as $prop)
                                <option value="{{ $prop->id }}">
                                    {{ $prop->user->name ?? $prop->raison_sociale }}
                                    - {{ $prop->motos_actives ?? 0 }} moto(s)
                                    - Solde: {{ number_format($prop->solde_disponible ?? 0) }} FC
                                </option>
                                @endforeach
                            </select>
                            @error('proprietaire_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        @if($proprietaireSelectionne)
                        <div class="alert alert-info mb-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>{{ $proprietaireSelectionne->user->name ?? 'N/A' }}</strong>
                                    <br><small>{{ $proprietaireSelectionne->motos->count() }} moto(s) enregistrée(s)</small>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-success fs-6">
                                        Solde disponible: {{ number_format($soldeDisponible) }} FC
                                    </span>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-cash-stack me-2 text-success"></i>Détails du Paiement</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Montant à payer <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" wire:model="montant"
                                           class="form-control @error('montant') is-invalid @enderror"
                                           placeholder="0" min="1" max="{{ $soldeDisponible }}">
                                    <span class="input-group-text">FC</span>
                                </div>
                                @error('montant')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                                @if($soldeDisponible > 0)
                                <small class="text-muted">Maximum: {{ number_format($soldeDisponible) }} FC</small>
                                @endif
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Mode de paiement <span class="text-danger">*</span></label>
                                <select wire:model.live="mode_paiement" class="form-select @error('mode_paiement') is-invalid @enderror">
                                    @foreach($modesPaiement as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('mode_paiement')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Numéro de compte / téléphone</label>
                                <input type="text" wire:model="numero_compte"
                                       class="form-control" placeholder="Ex: +243 XXX XXX XXX">
                                <small class="text-muted">Numéro de réception du paiement</small>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Notes / Commentaires</label>
                                <textarea wire:model="notes" class="form-control" rows="3"
                                          placeholder="Informations supplémentaires..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Boutons -->
                <div class="d-flex gap-3">
                    <button type="submit" class="btn btn-success btn-lg flex-grow-1"
                            wire:loading.attr="disabled"
                            @if(!$proprietaire_id || $soldeDisponible <= 0) disabled @endif>
                        <span wire:loading.remove>
                            <i class="bi bi-send me-2"></i>Soumettre la Demande
                        </span>
                        <span wire:loading>
                            <span class="spinner-border spinner-border-sm me-2"></span>Envoi en cours...
                        </span>
                    </button>
                    <a href="{{ route('supervisor.payments.index') }}" class="btn btn-outline-secondary btn-lg">
                        Annuler
                    </a>
                </div>
            </form>
        </div>

        <!-- Sidebar - Infos -->
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-info-circle me-2 text-info"></i>Informations</h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning border-0 mb-3">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Important:</strong> Le montant ne peut pas dépasser le solde disponible du propriétaire.
                    </div>
                    <ul class="list-unstyled mb-0">
                        <li class="d-flex align-items-start gap-2 mb-3">
                            <i class="bi bi-1-circle text-primary mt-1"></i>
                            <span>Sélectionnez le propriétaire bénéficiaire</span>
                        </li>
                        <li class="d-flex align-items-start gap-2 mb-3">
                            <i class="bi bi-2-circle text-primary mt-1"></i>
                            <span>Entrez le montant (≤ solde disponible)</span>
                        </li>
                        <li class="d-flex align-items-start gap-2 mb-3">
                            <i class="bi bi-3-circle text-primary mt-1"></i>
                            <span>Choisissez le mode de paiement</span>
                        </li>
                        <li class="d-flex align-items-start gap-2">
                            <i class="bi bi-4-circle text-primary mt-1"></i>
                            <span>La demande sera traitée par le collecteur</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="card">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-currency-exchange me-2 text-success"></i>Workflow</h6>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex align-items-center gap-2">
                            <span class="badge bg-warning">1</span>
                            <span>OKAMI soumet la demande</span>
                        </li>
                        <li class="list-group-item d-flex align-items-center gap-2">
                            <span class="badge bg-info">2</span>
                            <span>Collecteur effectue le paiement</span>
                        </li>
                        <li class="list-group-item d-flex align-items-center gap-2">
                            <span class="badge bg-primary">3</span>
                            <span>OKAMI valide le paiement</span>
                        </li>
                        <li class="list-group-item d-flex align-items-center gap-2">
                            <span class="badge bg-success">4</span>
                            <span>Propriétaire visualise</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
