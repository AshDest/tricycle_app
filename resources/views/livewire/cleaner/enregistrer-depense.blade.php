<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-plus-circle me-2 text-danger"></i>Enregistrer une Dépense
            </h4>
            <p class="text-muted mb-0">Ajouter une nouvelle dépense</p>
        </div>
        <a href="{{ route('cleaner.depenses.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Retour
        </a>
    </div>

    <!-- Alerte solde -->
    <div class="alert alert-{{ $soldeActuel > 0 ? 'success' : 'warning' }} mb-4">
        <div class="d-flex align-items-center gap-3">
            <i class="bi bi-wallet2 fs-4"></i>
            <div>
                <strong>Solde actuel disponible:</strong>
                <span class="fs-5 fw-bold ms-2">{{ number_format($soldeActuel) }} FC</span>
            </div>
        </div>
    </div>

    <!-- Messages flash -->
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row g-4">
        <!-- Formulaire -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold">
                        <i class="bi bi-receipt me-2 text-danger"></i>Détails de la Dépense
                    </h6>
                </div>
                <div class="card-body">
                    <form wire:submit="enregistrer">
                        <!-- Catégorie -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Catégorie <span class="text-danger">*</span></label>
                            <div class="row g-2">
                                @foreach($categories as $key => $label)
                                <div class="col-md-4 col-6">
                                    <div class="form-check card p-2 {{ $categorie === $key ? 'border-danger bg-danger bg-opacity-10' : '' }}">
                                        <input class="form-check-input" type="radio" wire:model.live="categorie" value="{{ $key }}" id="cat_{{ $key }}">
                                        <label class="form-check-label small" for="cat_{{ $key }}">
                                            {{ $label }}
                                        </label>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @error('categorie')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Description <span class="text-danger">*</span></label>
                            <input type="text" wire:model="description" class="form-control @error('description') is-invalid @enderror" placeholder="Ex: Achat de savon liquide">
                            @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Montant -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Montant (FC) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" wire:model.live="montant" class="form-control form-control-lg @error('montant') is-invalid @enderror" placeholder="0" min="1">
                                <span class="input-group-text">FC</span>
                            </div>
                            @error('montant')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror

                            @if($montant && (float)$montant > $soldeActuel)
                            <div class="alert alert-danger py-2 mt-2 mb-0">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <strong>Solde insuffisant!</strong> Vous ne pouvez dépenser que {{ number_format($soldeActuel) }} FC maximum.
                            </div>
                            @elseif($montant && (float)$montant > 0)
                            <div class="alert alert-info py-2 mt-2 mb-0">
                                <i class="bi bi-info-circle me-2"></i>
                                Nouveau solde après dépense: <strong>{{ number_format($soldeActuel - (float)$montant) }} FC</strong>
                            </div>
                            @endif
                        </div>

                        <div class="row g-3 mb-4">
                            <!-- Mode de paiement -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Mode de paiement <span class="text-danger">*</span></label>
                                <div class="d-flex gap-3">
                                    <div class="form-check card p-3 flex-fill {{ $mode_paiement === 'cash' ? 'border-success bg-success bg-opacity-10' : '' }}">
                                        <input class="form-check-input" type="radio" wire:model.live="mode_paiement" value="cash" id="modeCash">
                                        <label class="form-check-label d-flex align-items-center gap-2" for="modeCash">
                                            <i class="bi bi-cash text-success"></i>
                                            <span>Cash</span>
                                        </label>
                                    </div>
                                    <div class="form-check card p-3 flex-fill {{ $mode_paiement === 'mobile_money' ? 'border-primary bg-primary bg-opacity-10' : '' }}">
                                        <input class="form-check-input" type="radio" wire:model.live="mode_paiement" value="mobile_money" id="modeMobile">
                                        <label class="form-check-label d-flex align-items-center gap-2" for="modeMobile">
                                            <i class="bi bi-phone text-primary"></i>
                                            <span>Mobile</span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Date -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Date de la dépense <span class="text-danger">*</span></label>
                                <input type="date" wire:model="date_depense" class="form-control @error('date_depense') is-invalid @enderror" max="{{ now()->format('Y-m-d') }}">
                                @error('date_depense')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <!-- Fournisseur -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Fournisseur</label>
                                <input type="text" wire:model="fournisseur" class="form-control @error('fournisseur') is-invalid @enderror" placeholder="Nom du fournisseur (optionnel)">
                                @error('fournisseur')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Référence paiement -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Référence paiement</label>
                                <input type="text" wire:model="reference_paiement" class="form-control @error('reference_paiement') is-invalid @enderror" placeholder="N° transaction (optionnel)">
                                @error('reference_paiement')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Notes / Observations</label>
                            <textarea wire:model="notes" class="form-control" rows="2" placeholder="Remarques éventuelles..."></textarea>
                        </div>

                        <!-- Bouton soumettre -->
                        <div class="d-grid">
                            <button type="submit" class="btn btn-danger btn-lg" wire:loading.attr="disabled" @if($montant && (float)$montant > $soldeActuel) disabled @endif>
                                <span wire:loading.remove>
                                    <i class="bi bi-check-circle me-2"></i>Enregistrer la Dépense
                                </span>
                                <span wire:loading>
                                    <span class="spinner-border spinner-border-sm me-2"></span>Enregistrement...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Solde -->
            <div class="card border-0 shadow-sm mb-4 bg-success bg-opacity-10">
                <div class="card-body text-center py-4">
                    <i class="bi bi-wallet2 fs-1 text-success mb-2"></i>
                    <h6 class="text-muted mb-1">Solde disponible</h6>
                    <h2 class="fw-bold text-success mb-0">{{ number_format($soldeActuel) }} FC</h2>
                </div>
            </div>

            <!-- Catégories -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-info-circle me-2 text-info"></i>Catégories</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0 small">
                        <li class="mb-2"><strong>Produits:</strong> Savon, détergent, cire...</li>
                        <li class="mb-2"><strong>Équipement:</strong> Seaux, éponges, chiffons...</li>
                        <li class="mb-2"><strong>Eau:</strong> Facture d'eau</li>
                        <li class="mb-2"><strong>Électricité:</strong> Facture électricité</li>
                        <li class="mb-2"><strong>Loyer:</strong> Loyer du local</li>
                        <li class="mb-2"><strong>Salaire:</strong> Salaire assistant</li>
                        <li class="mb-2"><strong>Transport:</strong> Frais de déplacement</li>
                        <li class="mb-2"><strong>Réparation:</strong> Réparation matériel</li>
                        <li><strong>Autre:</strong> Autres dépenses</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

