<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-box-arrow-up me-2 text-primary"></i>Dépôt au Collecteur
            </h4>
            <p class="text-muted mb-0">Déposer votre solde auprès du collecteur lors de sa tournée</p>
        </div>
        <button wire:click="$refresh" class="btn btn-outline-primary">
            <i class="bi bi-arrow-clockwise me-1"></i>Actualiser
        </button>
    </div>

    <!-- Message Alert -->
    @if($message)
    <div class="alert alert-{{ $messageType }} alert-dismissible fade show mb-4" role="alert">
        <i class="bi bi-{{ $messageType === 'success' ? 'check-circle' : ($messageType === 'danger' ? 'exclamation-circle' : 'info-circle') }} me-2"></i>
        {{ $message }}
        <button type="button" class="btn-close" wire:click="closeMessage" aria-label="Close"></button>
    </div>
    @endif

    <!-- Solde actuel -->
    <div class="card bg-primary text-white mb-4">
        <div class="card-body py-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-white-50 mb-1">Votre Solde Actuel</h6>
                    <h2 class="fw-bold mb-0">{{ number_format($soldeActuel) }} FC</h2>
                </div>
                <div class="bg-white bg-opacity-25 rounded-circle p-3">
                    <i class="bi bi-wallet2 fs-1"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Tournées en attente de dépôt -->
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-clock me-2 text-warning"></i>Tournées en attente de dépôt</h6>
                </div>
                <div class="card-body p-0">
                    @if($collectesEnAttente->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($collectesEnAttente as $collecte)
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">
                                        <i class="bi bi-calendar-event me-2 text-primary"></i>
                                        {{ $collecte->tournee->date?->format('d/m/Y') }}
                                        <span class="badge bg-light text-dark ms-2">{{ $collecte->tournee->zone }}</span>
                                    </h6>
                                    <small class="text-muted">
                                        <i class="bi bi-person me-1"></i>Collecteur: <strong>{{ $collecte->tournee->collecteur->user->name ?? 'N/A' }}</strong>
                                    </small>
                                    <div class="mt-1">
                                        <span class="badge badge-soft-{{ $collecte->tournee->statut === 'confirmee' ? 'info' : 'primary' }}">
                                            {{ $collecte->tournee->statut === 'confirmee' ? 'Tournée confirmée' : 'Tournée en cours' }}
                                        </span>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <div class="mb-2">
                                        <small class="text-muted d-block">Montant attendu</small>
                                        <strong>{{ number_format($collecte->montant_attendu ?? 0) }} FC</strong>
                                    </div>
                                    <button wire:click="ouvrirDepot({{ $collecte->id }})"
                                            class="btn btn-success btn-sm"
                                            @if($soldeActuel <= 0) disabled title="Solde insuffisant" @endif>
                                        <i class="bi bi-cash me-1"></i>Déposer
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-calendar-x fs-1 d-block mb-3"></i>
                        <p class="mb-0">Aucune tournée en attente de dépôt</p>
                        <small>Les tournées confirmées par les collecteurs apparaîtront ici</small>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Historique récent -->
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-clock-history me-2 text-secondary"></i>Dépôts récents</h6>
                </div>
                <div class="card-body p-0">
                    @if($depotsRecents->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($depotsRecents as $depot)
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <span class="fw-medium">{{ number_format($depot->montant_collecte) }} FC</span>
                                    <small class="text-muted d-block">{{ $depot->created_at?->format('d/m/Y H:i') }}</small>
                                </div>
                                <div class="text-end">
                                    <span class="badge badge-soft-{{ $depot->valide_par_collecteur ? 'success' : 'warning' }}">
                                        {{ $depot->valide_par_collecteur ? 'Validé' : 'En attente' }}
                                    </span>
                                    <small class="text-muted d-block">{{ $depot->tournee->collecteur->user->name ?? 'N/A' }}</small>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                        <small>Aucun dépôt récent</small>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de dépôt -->
    @if($showModal && $collecteEnCours)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-cash-coin me-2 text-success"></i>Effectuer un dépôt
                    </h5>
                    <button type="button" class="btn-close" wire:click="fermerModal"></button>
                </div>
                <form wire:submit="effectuerDepot">
                    <div class="modal-body">
                        <!-- Info tournée -->
                        <div class="alert alert-light mb-3">
                            <div class="row">
                                <div class="col-6">
                                    <small class="text-muted">Collecteur</small>
                                    <div class="fw-semibold">{{ $collecteEnCours->tournee->collecteur->user->name ?? 'N/A' }}</div>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Date tournée</small>
                                    <div class="fw-semibold">{{ $collecteEnCours->tournee->date?->format('d/m/Y') }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Votre solde disponible</label>
                            <div class="h4 text-success">{{ number_format($soldeActuel) }} FC</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Montant à déposer <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" wire:model="montant"
                                       class="form-control form-control-lg @error('montant') is-invalid @enderror"
                                       placeholder="0" min="1" max="{{ $soldeActuel }}">
                                <span class="input-group-text">FC</span>
                                <button type="button" class="btn btn-outline-primary" wire:click="deposerTout">
                                    Tout
                                </button>
                            </div>
                            @error('montant') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Notes (optionnel)</label>
                            <textarea wire:model="notes" class="form-control" rows="2" placeholder="Remarques éventuelles..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" wire:click="fermerModal">Annuler</button>
                        <button type="submit" class="btn btn-success" wire:loading.attr="disabled">
                            <span wire:loading.remove>
                                <i class="bi bi-check-lg me-1"></i>Confirmer le dépôt
                            </span>
                            <span wire:loading>
                                <span class="spinner-border spinner-border-sm me-1"></span>Traitement...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
