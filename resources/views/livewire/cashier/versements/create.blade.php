<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-plus-circle me-2 text-success"></i>Enregistrer un Versement
            </h4>
            <p class="text-muted mb-0">Réception du versement journalier ou remboursement d'arriérés</p>
        </div>
        <a href="{{ route('cashier.versements.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Retour
        </a>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        @if(session('dernierVersementId'))
        <div class="mt-2">
            <button wire:click="telechargerRecu({{ session('dernierVersementId') }})" class="btn btn-sm btn-outline-success">
                <i class="bi bi-printer me-1"></i>Imprimer le reçu
            </button>
        </div>
        @endif
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-4">
        <i class="bi bi-x-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row g-4">
        <!-- Formulaire -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-cash-stack me-2 text-success"></i>Détails du Versement Journalier</h6>
                </div>
                <div class="card-body">
                    <form wire:submit="enregistrer">
                        <!-- Sélection du motard -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-person me-1 text-primary"></i>Motard <span class="text-danger">*</span>
                            </label>
                            <select wire:model.live="motard_id" class="form-select form-select-lg @error('motard_id') is-invalid @enderror" required>
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
                        <div class="card mb-4 border-{{ $arrieresCumules > 0 ? 'warning' : 'success' }}">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <h6 class="fw-bold mb-1">{{ $motardSelectionne->user->name ?? 'N/A' }}</h6>
                                        <span class="badge bg-light text-dark">
                                            <i class="bi bi-bicycle me-1"></i>{{ $motardSelectionne->moto->plaque_immatriculation ?? 'N/A' }}
                                        </span>
                                    </div>
                                    <div class="text-end">
                                        <small class="text-muted d-block">Tarif journalier</small>
                                        <strong class="text-primary fs-5">{{ number_format($montantJournalierAttendu) }} FC</strong>
                                    </div>
                                </div>

                                @if($arrieresCumules > 0)
                                <div class="alert alert-warning py-2 mb-0">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="bi bi-exclamation-triangle me-2"></i>
                                            <strong>Arriérés cumulés:</strong>
                                        </div>
                                        <span class="badge bg-danger fs-6">{{ number_format($arrieresCumules) }} FC</span>
                                    </div>
                                </div>
                                @else
                                <div class="alert alert-success py-2 mb-0">
                                    <i class="bi bi-check-circle me-2"></i>Aucun arriéré - Motard à jour
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Date du versement -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-calendar-date text-primary me-1"></i>
                                Date du versement <span class="text-danger">*</span>
                            </label>
                            <input type="date" wire:model.live="date_versement" class="form-control @error('date_versement') is-invalid @enderror" max="{{ now()->format('Y-m-d') }}" required>
                            @error('date_versement')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                            <!-- Statut du jour sélectionné -->
                            @if($versementExistantJour)
                            <div class="alert alert-info mt-3 mb-0">
                                <div class="d-flex align-items-start gap-3">
                                    <i class="bi bi-info-circle fs-4 text-info"></i>
                                    <div class="flex-grow-1">
                                        <strong class="d-block mb-1">Versement existant pour ce jour</strong>
                                        <div class="row g-2 small">
                                            <div class="col-md-4">
                                                <span class="text-muted">Déjà versé:</span>
                                                <strong class="text-success">{{ number_format($montantDejaVerseJour) }} FC</strong>
                                            </div>
                                            <div class="col-md-4">
                                                <span class="text-muted">Attendu:</span>
                                                <strong>{{ number_format($montantJournalierAttendu) }} FC</strong>
                                            </div>
                                            <div class="col-md-4">
                                                <span class="text-muted">Restant:</span>
                                                <strong class="text-{{ $montantRestantJour > 0 ? 'warning' : 'success' }}">
                                                    {{ number_format($montantRestantJour) }} FC
                                                </strong>
                                            </div>
                                        </div>
                                        @if($montantRestantJour > 0)
                                        <div class="mt-2">
                                            <small class="text-muted">Le nouveau montant sera ajouté au versement existant.</small>
                                        </div>
                                        @else
                                        <div class="mt-2">
                                            <span class="badge bg-success">Journée complète - Tout versement sera considéré comme arriérés</span>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>

                        <!-- Type de versement -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Type de versement <span class="text-danger">*</span></label>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-check card p-3 h-100 {{ $type_versement === 'journalier' ? 'border-primary bg-primary bg-opacity-10' : '' }}">
                                        <input class="form-check-input" type="radio" wire:model.live="type_versement" value="journalier" id="typeJournalier">
                                        <label class="form-check-label d-block" for="typeJournalier">
                                            <div class="d-flex align-items-center gap-2 mb-2">
                                                <i class="bi bi-calendar-day fs-4 text-primary"></i>
                                                <strong>Versement Journalier</strong>
                                            </div>
                                            <small class="text-muted">Payer la journée sélectionnée</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check card p-3 h-100 {{ $type_versement === 'arrieres' ? 'border-danger bg-danger bg-opacity-10' : '' }} {{ $arrieresCumules <= 0 ? 'opacity-50' : '' }}">
                                        <input class="form-check-input" type="radio" wire:model.live="type_versement" value="arrieres" id="typeArrieres" {{ $arrieresCumules <= 0 ? 'disabled' : '' }}>
                                        <label class="form-check-label d-block" for="typeArrieres">
                                            <div class="d-flex align-items-center gap-2 mb-2">
                                                <i class="bi bi-arrow-counterclockwise fs-4 text-danger"></i>
                                                <strong>Remboursement Arriérés</strong>
                                            </div>
                                            <small class="text-muted">
                                                @if($arrieresCumules > 0)
                                                Total: <strong class="text-danger">{{ number_format($arrieresCumules) }} FC</strong>
                                                @else
                                                Aucun arriéré
                                                @endif
                                            </small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Montant -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-cash me-1 text-success"></i>Montant versé <span class="text-danger">*</span>
                            </label>
                            <div class="input-group input-group-lg">
                                <input type="text" wire:model.live="montant" class="form-control text-end @error('montant') is-invalid @enderror"
                                       placeholder="0" inputmode="numeric" pattern="[0-9]*">
                                <span class="input-group-text">FC</span>
                            </div>
                            @error('montant')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror

                            <!-- Boutons de remplissage rapide -->
                            <div class="mt-2 d-flex gap-2 flex-wrap">
                                @if($type_versement === 'journalier')
                                <button type="button" wire:click="remplirMontantJournalier" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-calendar-day me-1"></i>Montant du jour ({{ number_format($montantRestantJour > 0 ? $montantRestantJour : $montantJournalierAttendu) }} FC)
                                </button>
                                @endif

                                @if($arrieresCumules > 0)
                                <button type="button" wire:click="remplirMontantArrieres" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-arrow-counterclockwise me-1"></i>Arriérés ({{ number_format($arrieresCumules) }} FC)
                                </button>
                                <button type="button" wire:click="remplirTotalDu" class="btn btn-sm btn-outline-warning">
                                    <i class="bi bi-calculator me-1"></i>Total dû ({{ number_format($montantRestantJour + $arrieresCumules) }} FC)
                                </button>
                                @endif
                            </div>
                        </div>

                        <!-- Mode de paiement -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Mode de paiement <span class="text-danger">*</span></label>
                            <div class="row g-2">
                                <div class="col-4">
                                    <div class="form-check card p-3 text-center h-100 {{ $mode_paiement === 'cash' ? 'border-success bg-success bg-opacity-10' : '' }}">
                                        <input class="form-check-input d-none" type="radio" wire:model="mode_paiement" value="cash" id="modeCash">
                                        <label class="form-check-label d-block" for="modeCash" style="cursor: pointer;">
                                            <i class="bi bi-cash fs-4 text-success d-block mb-1"></i>
                                            <small class="fw-semibold">Cash</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="form-check card p-3 text-center h-100 {{ $mode_paiement === 'mobile_money' ? 'border-primary bg-primary bg-opacity-10' : '' }}">
                                        <input class="form-check-input d-none" type="radio" wire:model="mode_paiement" value="mobile_money" id="modeMobile">
                                        <label class="form-check-label d-block" for="modeMobile" style="cursor: pointer;">
                                            <i class="bi bi-phone fs-4 text-primary d-block mb-1"></i>
                                            <small class="fw-semibold">Mobile</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="form-check card p-3 text-center h-100 {{ $mode_paiement === 'depot' ? 'border-info bg-info bg-opacity-10' : '' }}">
                                        <input class="form-check-input d-none" type="radio" wire:model="mode_paiement" value="depot" id="modeDepot">
                                        <label class="form-check-label d-block" for="modeDepot" style="cursor: pointer;">
                                            <i class="bi bi-bank fs-4 text-info d-block mb-1"></i>
                                            <small class="fw-semibold">Dépôt</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Notes (optionnel)</label>
                            <textarea wire:model="notes" class="form-control" rows="2" placeholder="Observations..."></textarea>
                        </div>

                        <!-- Bouton de soumission -->
                        <div class="d-grid">
                            <button type="submit" class="btn btn-success btn-lg" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="enregistrer">
                                    <i class="bi bi-check-circle me-2"></i>Enregistrer le Versement
                                </span>
                                <span wire:loading wire:target="enregistrer">
                                    <span class="spinner-border spinner-border-sm me-2"></span>Enregistrement...
                                </span>
                            </button>
                        </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>

        <!-- Panneau latéral -->
        <div class="col-lg-4">
            <!-- Solde Caisse -->
            <div class="card mb-4 border-0 bg-success bg-opacity-10">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-success rounded-circle p-3">
                            <i class="bi bi-wallet2 fs-4 text-white"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block">Solde en caisse</small>
                            <h4 class="mb-0 fw-bold text-success">{{ number_format($soldeActuel) }} FC</h4>
                        </div>
                    </div>
                </div>
            </div>

            @if($motardSelectionne && (float)$montant > 0)
            <!-- Aperçu de la répartition -->
            <div class="card mb-4">
                <div class="card-header py-3 bg-light">
                    <h6 class="mb-0 fw-bold">
                        <i class="bi bi-pie-chart me-2 text-primary"></i>Répartition prévisionnelle
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted d-block">Montant versé</small>
                        <h5 class="fw-bold mb-0">{{ number_format((float)$montant) }} FC</h5>
                    </div>
                    <hr>
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="bg-info bg-opacity-10 rounded p-3 text-center">
                                <small class="d-block text-muted">Part Propriétaire</small>
                                <strong class="text-info fs-5">{{ number_format($partProprietairePreview) }} FC</strong>
                                <small class="d-block text-muted">(5/6 ≈ 83.33%)</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-warning bg-opacity-10 rounded p-3 text-center">
                                <small class="d-block text-muted">Part OKAMI</small>
                                <strong class="text-warning fs-5">{{ number_format($partOkamiPreview) }} FC</strong>
                                <small class="d-block text-muted">(1/6 ≈ 16.67%)</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            @if($motardSelectionne && count($arrieresDetails) > 0)
            <!-- Historique des arriérés -->
            <div class="card">
                <div class="card-header py-3 bg-light">
                    <h6 class="mb-0 fw-bold">
                        <i class="bi bi-clock-history me-2 text-danger"></i>Détail des arriérés
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush" style="max-height: 300px; overflow-y: auto;">
                        @foreach($arrieresDetails as $arr)
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="text-muted">{{ $arr['date'] }}</small>
                                    <br>
                                    <small>Versé: {{ number_format($arr['montant_verse']) }}/{{ number_format($arr['montant_attendu']) }} FC</small>
                                </div>
                                <span class="badge bg-danger">{{ number_format($arr['arrieres']) }} FC</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <div class="p-3 bg-danger bg-opacity-10 border-top">
                        <div class="d-flex justify-content-between align-items-center">
                            <strong>Total Arriérés</strong>
                            <strong class="text-danger">{{ number_format($arrieresCumules) }} FC</strong>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

