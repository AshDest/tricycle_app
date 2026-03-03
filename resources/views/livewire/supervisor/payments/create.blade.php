<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-plus-circle me-2 text-success"></i>Nouvelle Demande de Paiement
            </h4>
            <p class="text-muted mb-0">Soumettre une demande de paiement</p>
        </div>
        <a href="{{ route('supervisor.payments.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Retour
        </a>
    </div>

    <div class="row g-4">
        <!-- Formulaire -->
        <div class="col-lg-8">
            <form wire:submit="submit">
                <!-- Choix de la source de caisse -->
                <div class="card mb-4">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-wallet2 me-2 text-warning"></i>Source de la Caisse</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-check card p-3 h-100 {{ $source_caisse === 'proprietaire' ? 'border-success bg-success bg-opacity-10' : '' }}">
                                    <input class="form-check-input" type="radio" wire:model.live="source_caisse" value="proprietaire" id="sourceProp">
                                    <label class="form-check-label d-flex align-items-start gap-3 w-100" for="sourceProp">
                                        <i class="bi bi-people fs-3 text-success"></i>
                                        <div>
                                            <strong class="d-block">Caisse Propriétaires (5/6)</strong>
                                            <small class="text-muted">Paiement vers un propriétaire de moto</small>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check card p-3 h-100 {{ $source_caisse === 'okami' ? 'border-warning bg-warning bg-opacity-10' : '' }}">
                                    <input class="form-check-input" type="radio" wire:model.live="source_caisse" value="okami" id="sourceOkami">
                                    <label class="form-check-label d-flex align-items-start gap-3 w-100" for="sourceOkami">
                                        <i class="bi bi-building fs-3 text-warning"></i>
                                        <div>
                                            <strong class="d-block">Caisse OKAMI (1/6)</strong>
                                            <small class="text-muted">Paiement vers un bénéficiaire externe</small>
                                            <div class="mt-1">
                                                <span class="badge bg-warning text-dark">Solde: {{ number_format($soldeOkamiDisponible) }} FC</span>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @if($source_caisse === 'proprietaire')
                <!-- Section Propriétaire -->
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
                @else
                <!-- Section Bénéficiaire OKAMI -->
                <div class="card mb-4">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-person-badge me-2 text-warning"></i>Bénéficiaire (Caisse OKAMI)</h6>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning mb-4">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Solde OKAMI disponible:</strong> {{ number_format($soldeOkamiDisponible) }} FC
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nom du bénéficiaire <span class="text-danger">*</span></label>
                                <input type="text" wire:model="beneficiaire_nom"
                                       class="form-control @error('beneficiaire_nom') is-invalid @enderror"
                                       placeholder="Nom complet du bénéficiaire">
                                @error('beneficiaire_nom')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Téléphone du bénéficiaire</label>
                                <input type="text" wire:model="beneficiaire_telephone"
                                       class="form-control @error('beneficiaire_telephone') is-invalid @enderror"
                                       placeholder="+243 XXX XXX XXX">
                                @error('beneficiaire_telephone')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Motif du paiement <span class="text-danger">*</span></label>
                                <textarea wire:model="beneficiaire_motif"
                                          class="form-control @error('beneficiaire_motif') is-invalid @enderror"
                                          rows="2" placeholder="Décrivez le motif de ce paiement..."></textarea>
                                @error('beneficiaire_motif')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                @endif

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
                                           placeholder="0" min="1" max="{{ $source_caisse === 'proprietaire' ? $soldeDisponible : $soldeOkamiDisponible }}">
                                    <span class="input-group-text">FC</span>
                                </div>
                                @error('montant')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                                @php
                                    $maxSolde = $source_caisse === 'proprietaire' ? $soldeDisponible : $soldeOkamiDisponible;
                                @endphp
                                @if($maxSolde > 0)
                                <small class="text-muted">Maximum: {{ number_format($maxSolde) }} FC</small>
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
                                <textarea wire:model="notes" class="form-control" rows="2"
                                          placeholder="Informations supplémentaires..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Boutons -->
                <div class="d-flex gap-3">
                    @php
                        $canSubmit = ($source_caisse === 'proprietaire' && $proprietaire_id && $soldeDisponible > 0)
                                  || ($source_caisse === 'okami' && $soldeOkamiDisponible > 0);
                    @endphp
                    <button type="submit" class="btn btn-success btn-lg flex-grow-1"
                            wire:loading.attr="disabled"
                            @if(!$canSubmit) disabled @endif>
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
                    @if($source_caisse === 'proprietaire')
                    <div class="alert alert-success border-0 mb-3">
                        <i class="bi bi-people me-2"></i>
                        <strong>Caisse Propriétaires:</strong><br>
                        <small>Les versements des motards sont répartis avec 5/6 pour les propriétaires.</small>
                    </div>
                    @else
                    <div class="alert alert-warning border-0 mb-3">
                        <i class="bi bi-building me-2"></i>
                        <strong>Caisse OKAMI:</strong><br>
                        <small>1/6 des versements est conservé pour les frais de gestion OKAMI.</small>
                    </div>
                    @endif

                    <ul class="list-unstyled mb-0">
                        <li class="d-flex align-items-start gap-2 mb-3">
                            <i class="bi bi-1-circle text-primary mt-1"></i>
                            <span>Choisissez la source de caisse</span>
                        </li>
                        <li class="d-flex align-items-start gap-2 mb-3">
                            <i class="bi bi-2-circle text-primary mt-1"></i>
                            <span>
                                @if($source_caisse === 'proprietaire')
                                Sélectionnez le propriétaire bénéficiaire
                                @else
                                Renseignez le bénéficiaire et le motif
                                @endif
                            </span>
                        </li>
                        <li class="d-flex align-items-start gap-2 mb-3">
                            <i class="bi bi-3-circle text-primary mt-1"></i>
                            <span>Entrez le montant et le mode de paiement</span>
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
                            <span class="badge bg-success">3</span>
                            <span>Paiement validé</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
