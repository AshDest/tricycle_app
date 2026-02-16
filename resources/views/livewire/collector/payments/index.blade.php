<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-wallet2 me-2 text-primary"></i>Demandes de Paiement à Traiter
            </h4>
            <p class="text-muted mb-0">Traiter les demandes de paiement soumises par OKAMI</p>
        </div>
        <span class="badge bg-warning text-dark px-3 py-2 fs-6">
            <i class="bi bi-hourglass-split me-1"></i>{{ $demandesEnAttente }} en attente
        </span>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-4">
        <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Filtres -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-8">
                    <label class="form-label small fw-semibold">Rechercher</label>
                    <input type="text" wire:model.live="search" class="form-control" placeholder="Nom du propriétaire...">
                </div>
                <div class="col-md-4">
                    <button wire:click="$refresh" class="btn btn-outline-primary w-100">
                        <i class="bi bi-arrow-clockwise me-1"></i>Actualiser
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des demandes -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Date demande</th>
                            <th>Propriétaire</th>
                            <th>Montant demandé</th>
                            <th>Mode</th>
                            <th>N° Compte</th>
                            <th>Demandé par</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $payment)
                        <tr>
                            <td class="ps-4">
                                <span class="fw-medium">{{ $payment->date_demande?->format('d/m/Y') }}</span>
                                <small class="text-muted d-block">{{ $payment->demande_at?->format('H:i') }}</small>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar avatar-sm bg-primary bg-opacity-10 text-primary rounded-circle">
                                        {{ strtoupper(substr($payment->proprietaire->user->name ?? 'P', 0, 1)) }}
                                    </div>
                                    <div>
                                        <span class="fw-medium d-block">{{ $payment->proprietaire->user->name ?? 'N/A' }}</span>
                                        <small class="text-muted">{{ $payment->proprietaire->telephone ?? '' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td class="fw-bold text-success fs-5">{{ number_format($payment->total_du) }} FC</td>
                            <td>
                                <span class="badge bg-light text-dark">
                                    {{ \App\Models\Payment::getModesPaiement()[$payment->mode_paiement] ?? $payment->mode_paiement }}
                                </span>
                            </td>
                            <td>
                                <code>{{ $payment->numero_compte ?? '-' }}</code>
                            </td>
                            <td>
                                <small class="text-muted">{{ $payment->demandePar->name ?? 'OKAMI' }}</small>
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group">
                                    <button wire:click="ouvrirTraitement({{ $payment->id }})"
                                            class="btn btn-sm btn-success">
                                        <i class="bi bi-cash-coin me-1"></i>Traiter
                                    </button>
                                    <button wire:click="rejeterDemande({{ $payment->id }}, 'Fonds insuffisants')"
                                            class="btn btn-sm btn-outline-danger"
                                            wire:confirm="Rejeter cette demande ?">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-check-circle fs-1 d-block mb-3 text-success"></i>
                                <p class="mb-0">Aucune demande en attente</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($payments->hasPages())
        <div class="card-footer bg-light">
            {{ $payments->links() }}
        </div>
        @endif
    </div>

    <!-- Modal de traitement -->
    @if($showModal && $paymentEnCours)
    <div class="modal fade show d-block" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-cash-coin me-2 text-success"></i>Effectuer le Paiement
                    </h5>
                    <button type="button" class="btn-close" wire:click="fermerModal"></button>
                </div>
                <div class="modal-body">
                    <!-- Info propriétaire -->
                    <div class="alert alert-info mb-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>{{ $paymentEnCours->proprietaire->user->name ?? 'N/A' }}</strong>
                                <br><small>{{ \App\Models\Payment::getModesPaiement()[$paymentEnCours->mode_paiement] ?? '' }}</small>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-primary fs-6">{{ number_format($paymentEnCours->total_du) }} FC</span>
                            </div>
                        </div>
                        <hr class="my-2">
                        <small><strong>N° Compte:</strong> {{ $paymentEnCours->numero_compte ?? 'Non renseigné' }}</small>
                    </div>

                    <form wire:submit="traiterPaiement">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Montant payé <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" wire:model="montant_paye" class="form-control @error('montant_paye') is-invalid @enderror" required>
                                <span class="input-group-text">FC</span>
                            </div>
                            @error('montant_paye')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Numéro d'envoi <span class="text-danger">*</span></label>
                            <input type="text" wire:model="numero_envoi" class="form-control @error('numero_envoi') is-invalid @enderror"
                                   placeholder="Ex: TXN123456789" required>
                            @error('numero_envoi')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Numéro de transaction du transfert</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Référence (optionnel)</label>
                            <input type="text" wire:model="reference_paiement" class="form-control" placeholder="Référence interne">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Notes</label>
                            <textarea wire:model="notes" class="form-control" rows="2" placeholder="Commentaires..."></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="fermerModal">Annuler</button>
                    <button type="button" class="btn btn-success" wire:click="traiterPaiement" wire:loading.attr="disabled">
                        <span wire:loading.remove><i class="bi bi-check-lg me-1"></i>Confirmer le paiement</span>
                        <span wire:loading><span class="spinner-border spinner-border-sm me-1"></span>Traitement...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
