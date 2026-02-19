<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-plus-circle me-2 text-success"></i>Enregistrer un Versement
            </h4>
            <p class="text-muted mb-0">Réception du versement d'un motard</p>
        </div>
        <a href="{{ route('cashier.versements.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Retour
        </a>
    </div>

    <div class="row g-4">
        <!-- Formulaire -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-cash-stack me-2 text-success"></i>Détails du Versement</h6>
                </div>
                <div class="card-body">
                    <form wire:submit="enregistrer">
                        <!-- Sélection du motard -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Motard <span class="text-danger">*</span></label>
                            <select wire:model.live="motard_id" class="form-select @error('motard_id') is-invalid @enderror" required>
                                <option value="">-- Sélectionner un motard --</option>
                                @foreach($motards ?? [] as $motard)
                                <option value="{{ $motard->id }}">
                                    {{ $motard->user->name ?? 'N/A' }} - {{ $motard->moto->plaque_immatriculation ?? 'Sans moto' }}
                                </option>
                                @endforeach
                            </select>
                            @error('motard_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        @if($motardSelectionne)
                        <!-- Infos du motard sélectionné -->
                        <div class="card mb-4 {{ $arrieresCumules > 0 ? 'border-warning' : 'border-success' }}">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <h6 class="fw-bold mb-1">{{ $motardSelectionne->user->name ?? 'N/A' }}</h6>
                                        <span class="badge bg-light text-dark">
                                            <i class="bi bi-bicycle me-1"></i>{{ $motardSelectionne->moto->plaque_immatriculation ?? 'N/A' }}
                                        </span>
                                    </div>
                                    <div class="text-end">
                                        <small class="text-muted d-block">Taux de paiement</small>
                                        <span class="badge badge-soft-{{ $tauxPaiement >= 90 ? 'success' : ($tauxPaiement >= 70 ? 'warning' : 'danger') }} fs-6">
                                            {{ $tauxPaiement }}%
                                        </span>
                                    </div>
                                </div>
                                <div class="row g-3">
                                    <div class="col-6">
                                        <div class="bg-primary bg-opacity-10 rounded p-3 text-center">
                                            <small class="text-muted d-block">Montant attendu</small>
                                            <strong class="text-primary fs-5">{{ number_format($montantAttendu) }} FC</strong>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="bg-{{ $arrieresCumules > 0 ? 'danger' : 'success' }} bg-opacity-10 rounded p-3 text-center">
                                            <small class="text-muted d-block">Arriérés cumulés</small>
                                            <strong class="text-{{ $arrieresCumules > 0 ? 'danger' : 'success' }} fs-5">
                                                {{ number_format($arrieresCumules) }} FC
                                            </strong>
                                        </div>
                                    </div>
                                </div>
                                @if($arrieresCumules > 0)
                                <div class="alert alert-warning mt-3 mb-0 py-2">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    <small>Ce motard a des arriérés en cours. Le versement du jour sera comparé au montant attendu de <strong>{{ number_format($montantAttendu) }} FC</strong>.</small>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endif

                        <!-- Montant reçu -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Montant reçu (FC) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" wire:model.live="montant" class="form-control form-control-lg @error('montant') is-invalid @enderror"
                                       placeholder="0" min="0" required>
                                <span class="input-group-text">FC</span>
                            </div>
                            @error('montant')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror

                            @if($motardSelectionne && $montant)
                            @php
                                $ecart = $montant - $montantAttendu;
                            @endphp
                            <div class="mt-2">
                                @if($ecart >= 0)
                                <div class="alert alert-success py-2 mb-0">
                                    <i class="bi bi-check-circle me-2"></i>
                                    <strong>Versement complet</strong> - Le montant couvre le tarif journalier
                                    @if($ecart > 0)
                                    <span class="badge bg-success ms-2">+{{ number_format($ecart) }} FC</span>
                                    @endif
                                </div>
                                @else
                                <div class="alert alert-warning py-2 mb-0">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    <strong>Versement partiel</strong> - Arriéré de ce jour: <strong class="text-danger">{{ number_format(abs($ecart)) }} FC</strong>
                                </div>
                                @endif
                            </div>
                            @endif
                        </div>

                        <!-- Mode de paiement -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Mode de paiement <span class="text-danger">*</span></label>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="form-check card p-3 {{ $mode_paiement === 'cash' ? 'border-success bg-success bg-opacity-10' : '' }}">
                                        <input class="form-check-input" type="radio" wire:model="mode_paiement" value="cash" id="modeCash">
                                        <label class="form-check-label d-flex align-items-center gap-2" for="modeCash">
                                            <i class="bi bi-cash text-success fs-4"></i>
                                            <span>Cash</span>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check card p-3 {{ $mode_paiement === 'mobile_money' ? 'border-primary bg-primary bg-opacity-10' : '' }}">
                                        <input class="form-check-input" type="radio" wire:model="mode_paiement" value="mobile_money" id="modeMobile">
                                        <label class="form-check-label d-flex align-items-center gap-2" for="modeMobile">
                                            <i class="bi bi-phone text-primary fs-4"></i>
                                            <span>Mobile Money</span>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check card p-3 {{ $mode_paiement === 'depot' ? 'border-info bg-info bg-opacity-10' : '' }}">
                                        <input class="form-check-input" type="radio" wire:model="mode_paiement" value="depot" id="modeDepot">
                                        <label class="form-check-label d-flex align-items-center gap-2" for="modeDepot">
                                            <i class="bi bi-bank text-info fs-4"></i>
                                            <span>Dépôt</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            @error('mode_paiement')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Notes -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Notes / Observations</label>
                            <textarea wire:model="notes" class="form-control" rows="3" placeholder="Remarques éventuelles..."></textarea>
                        </div>

                        <!-- Bouton soumettre -->
                        <div class="d-grid">
                            <button type="submit" class="btn btn-success btn-lg" wire:loading.attr="disabled">
                                <span wire:loading.remove>
                                    <i class="bi bi-check-circle me-2"></i>Enregistrer le Versement
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

        <!-- Sidebar infos -->
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-info-circle me-2 text-info"></i>Instructions</h6>
                </div>
                <div class="card-body">
                    <ol class="mb-0 ps-3">
                        <li class="mb-2">Sélectionnez le motard qui effectue le versement</li>
                        <li class="mb-2">Vérifiez le montant attendu affiché</li>
                        <li class="mb-2">Saisissez le montant réellement reçu</li>
                        <li class="mb-2">Choisissez le mode de paiement</li>
                        <li>Validez l'enregistrement</li>
                    </ol>
                </div>
            </div>

            <div class="card">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-wallet2 me-2 text-warning"></i>Solde Actuel</h6>
                </div>
                <div class="card-body text-center">
                    <h3 class="fw-bold text-warning mb-2">{{ number_format($soldeActuel ?? 0) }} FC</h3>
                    <small class="text-muted">En caisse (non collecté)</small>
                </div>
            </div>
        </div>
    </div>
</div>
