<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-wallet2 me-2 text-primary"></i>Demandes de Paiement à Traiter
            </h4>
            <p class="text-muted mb-0">Traiter les demandes de paiement soumises par OKAMI</p>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <button wire:click="exporterPdf" class="btn btn-outline-danger" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="exporterPdf">
                    <i class="bi bi-file-pdf me-1"></i>Export PDF
                </span>
                <span wire:loading wire:target="exporterPdf">
                    <span class="spinner-border spinner-border-sm me-1"></span>...
                </span>
            </button>
            <span class="badge bg-warning text-dark px-3 py-2 fs-6">
                <i class="bi bi-hourglass-split me-1"></i>{{ $demandesEnAttente }} en attente
            </span>
        </div>
    </div>

    <!-- Solde Caisse -->
    <div class="card border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, #212529 0%, #343a40 100%);">
        <div class="card-body py-3">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-safe fs-4" style="color: #fff;"></i>
                <div>
                    <small class="d-block" style="color: rgba(255,255,255,0.7);">Solde Total en Caisse</small>
                    <h5 class="mb-0 fw-bold" style="color: #fff;">{{ number_format($soldeCaisse) }} FC</h5>
                </div>
                <span class="ms-auto badge bg-warning text-dark">{{ $demandesEnAttente }} demande(s) en attente</span>
            </div>
        </div>
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
                    <input type="text" wire:model.live="search" class="form-control" placeholder="Nom du propriétaire ou bénéficiaire...">
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
                            <th class="ps-4">Date</th>
                            <th>Source</th>
                            <th>Bénéficiaire</th>
                            <th class="text-center">Solde dû</th>
                            <th class="text-center">Demandé</th>
                            <th>Mode</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $payment)
                        <tr class="{{ !$payment->peut_etre_paye ? 'table-danger bg-opacity-25' : '' }}">
                            <td class="ps-4">
                                <span class="fw-medium">{{ $payment->date_demande?->format('d/m/Y') }}</span>
                                <small class="text-muted d-block">{{ $payment->demande_at?->format('H:i') }}</small>
                            </td>
                            <td>
                                @if($payment->source_caisse === 'okami')
                                <span class="badge bg-warning text-dark">
                                    <i class="bi bi-building me-1"></i>OKAMI
                                </span>
                                @else
                                <span class="badge bg-success">
                                    <i class="bi bi-people me-1"></i>Propriétaire
                                </span>
                                @endif
                            </td>
                            <td>
                                @if($payment->source_caisse === 'okami')
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar avatar-sm bg-warning bg-opacity-10 text-warning rounded-circle">
                                        {{ strtoupper(substr($payment->beneficiaire_nom ?? 'B', 0, 1)) }}
                                    </div>
                                    <div>
                                        <span class="fw-medium d-block">{{ $payment->beneficiaire_nom ?? 'N/A' }}</span>
                                        @if($payment->beneficiaire_motif)
                                        <small class="text-muted" title="{{ $payment->beneficiaire_motif }}">
                                            {{ Str::limit($payment->beneficiaire_motif, 25) }}
                                        </small>
                                        @endif
                                    </div>
                                </div>
                                @else
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar avatar-sm bg-primary bg-opacity-10 text-primary rounded-circle">
                                        {{ strtoupper(substr($payment->proprietaire->user->name ?? 'P', 0, 1)) }}
                                    </div>
                                    <div>
                                        <span class="fw-medium d-block">{{ $payment->proprietaire->user->name ?? 'N/A' }}</span>
                                        <small class="text-muted">{{ $payment->proprietaire->telephone ?? '' }}</small>
                                    </div>
                                </div>
                                @endif
                            </td>
                            <td class="text-center">
                                <!-- Solde disponible du propriétaire -->
                                <div class="d-flex flex-column align-items-center">
                                    <span class="badge {{ $payment->solde_disponible >= $payment->total_du ? 'bg-success' : 'bg-danger' }} fs-6">
                                        {{ number_format($payment->solde_disponible) }} FC
                                    </span>
                                    @if($payment->peut_etre_paye)
                                    <small class="text-success mt-1">
                                        <i class="bi bi-check-circle me-1"></i>OK
                                    </small>
                                    @else
                                    <small class="text-danger mt-1">
                                        <i class="bi bi-x-circle me-1"></i>Insuffisant
                                    </small>
                                    @endif
                                </div>
                            </td>
                            <td class="text-center">
                                @php
                                    $tauxPmt = ($payment->taux_conversion && $payment->taux_conversion > 0) ? $payment->taux_conversion : \App\Models\SystemSetting::getTauxUsdCdf();
                                    $montantUsdCalc = ($payment->montant_usd && $payment->montant_usd > 0) ? $payment->montant_usd : ($tauxPmt > 0 ? round($payment->total_du / $tauxPmt, 2) : 0);
                                @endphp
                                <span class="fw-bold {{ $payment->peut_etre_paye ? 'text-success' : 'text-danger' }} fs-5">
                                    {{ number_format($montantUsdCalc, 2) }} $
                                </span>
                                <br><small class="text-muted">
                                    ≈ {{ number_format($payment->total_du) }} FC
                                    <span class="text-info">(×{{ number_format($tauxPmt) }})</span>
                                </small>
                                @if($payment->numero_compte)
                                <br><code class="small">{{ $payment->numero_compte }}</code>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">
                                    {{ \App\Models\Payment::getModesPaiement()[$payment->mode_paiement] ?? $payment->mode_paiement }}
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                @if($payment->peut_etre_paye)
                                <div class="btn-group">
                                    <button wire:click="ouvrirTraitement({{ $payment->id }})"
                                            class="btn btn-sm btn-success">
                                        <i class="bi bi-cash-coin me-1"></i>Traiter
                                    </button>
                                    <button wire:click="rejeterDemande({{ $payment->id }}, 'Rejeté par le collecteur')"
                                            class="btn btn-sm btn-outline-danger"
                                            wire:confirm="Rejeter cette demande ?">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                                @else
                                <div class="d-flex flex-column align-items-end gap-1">
                                    <span class="badge bg-danger">
                                        <i class="bi bi-exclamation-triangle me-1"></i>Fonds insuffisants
                                    </span>
                                    <button wire:click="rejeterDemande({{ $payment->id }}, 'Solde insuffisant du propriétaire')"
                                            class="btn btn-sm btn-outline-secondary"
                                            wire:confirm="Rejeter cette demande pour fonds insuffisants ?">
                                        <i class="bi bi-x-lg me-1"></i>Rejeter
                                    </button>
                                </div>
                                @endif
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

    <!-- Légende -->
    <div class="card mt-3 bg-light border-0">
        <div class="card-body py-2">
            <div class="row align-items-center">
                <div class="col-auto">
                    <small class="fw-bold text-muted"><i class="bi bi-info-circle me-1"></i>Légende:</small>
                </div>
                <div class="col">
                    <span class="badge bg-success me-2"><i class="bi bi-check-circle me-1"></i>OK</span>
                    <small class="text-muted me-3">Le propriétaire a assez de solde</small>

                    <span class="badge bg-danger me-2"><i class="bi bi-x-circle me-1"></i>Insuffisant</span>
                    <small class="text-muted">Le solde du propriétaire est insuffisant</small>
                </div>
            </div>
        </div>
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
                    <!-- Source de la caisse -->
                    @php
                        $isFromOkami = $paymentEnCours->source_caisse === 'okami';
                        $modeLabel = \App\Models\Payment::getModesPaiement()[$paymentEnCours->mode_paiement] ?? '';
                        $isCash = $paymentEnCours->mode_paiement === 'cash';
                    @endphp

                    <div class="alert {{ $isFromOkami ? 'alert-warning' : 'alert-info' }} mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                @if($isFromOkami)
                                <span class="badge bg-warning text-dark me-2"><i class="bi bi-building"></i> Caisse OKAMI</span>
                                @else
                                <span class="badge bg-success me-2"><i class="bi bi-people"></i> Caisse Propriétaire</span>
                                @endif
                                <span class="badge {{ $isCash ? 'bg-secondary' : 'bg-primary' }}">
                                    {{ $modeLabel }}
                                </span>
                            </div>
                            <div class="text-end">
                                @php
                                    $tauxModal = ($paymentEnCours->taux_conversion && $paymentEnCours->taux_conversion > 0) ? $paymentEnCours->taux_conversion : \App\Models\SystemSetting::getTauxUsdCdf();
                                    $montantUsdModal = ($paymentEnCours->montant_usd && $paymentEnCours->montant_usd > 0) ? $paymentEnCours->montant_usd : ($tauxModal > 0 ? round($paymentEnCours->total_du / $tauxModal, 2) : 0);
                                @endphp
                                <span class="badge bg-success fs-6">{{ number_format($montantUsdModal, 2) }} $</span>
                                <br><small class="text-muted mt-1 d-inline-block">
                                    ≈ {{ number_format($paymentEnCours->total_du) }} FC
                                    <br><span class="text-info">Taux: 1 USD = {{ number_format($tauxModal) }} FC</span>
                                </small>
                            </div>
                        </div>
                        <hr class="my-2">
                        <div class="d-flex justify-content-between">
                            <div>
                                <strong>Bénéficiaire:</strong><br>
                                @if($isFromOkami)
                                {{ $paymentEnCours->beneficiaire_nom ?? 'N/A' }}
                                @if($paymentEnCours->beneficiaire_telephone)
                                <br><small class="text-muted">{{ $paymentEnCours->beneficiaire_telephone }}</small>
                                @endif
                                @else
                                {{ $paymentEnCours->proprietaire->user->name ?? 'N/A' }}
                                @endif
                            </div>
                            @if($paymentEnCours->numero_compte)
                            <div class="text-end">
                                <strong>N° Compte:</strong><br>
                                <code>{{ $paymentEnCours->numero_compte }}</code>
                            </div>
                            @endif
                        </div>
                        @if($isFromOkami && $paymentEnCours->beneficiaire_motif)
                        <hr class="my-2">
                        <small><strong>Motif:</strong> {{ $paymentEnCours->beneficiaire_motif }}</small>
                        @endif
                    </div>

                    @if($isCash)
                    <div class="alert alert-secondary mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <i class="bi bi-cash-stack me-2"></i>
                                <strong>Paiement en Cash</strong>
                            </div>
                            <div class="text-end">
                                <small>Solde en Caisse:</small>
                                <strong class="ms-1 text-primary">{{ number_format($soldeCaisse) }} FC</strong>
                            </div>
                        </div>
                        <small class="d-block mt-1">Un reçu sera généré automatiquement après le traitement.</small>
                    </div>
                    @endif

                    <form wire:submit="traiterPaiement">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Montant payé <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" wire:model="montant_paye" class="form-control form-control-lg @error('montant_paye') is-invalid @enderror" required>
                                <span class="input-group-text">FC</span>
                            </div>
                            @error('montant_paye')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        @if(!$isCash)
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Numéro d'envoi <span class="text-danger">*</span></label>
                            <input type="text" wire:model="numero_envoi" class="form-control @error('numero_envoi') is-invalid @enderror"
                                   placeholder="Ex: TXN123456789">
                            @error('numero_envoi')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Numéro de transaction du transfert mobile/bancaire</small>
                        </div>
                        @endif

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
                        <span wire:loading.remove>
                            <i class="bi bi-check-lg me-1"></i>
                            {{ $isCash ? 'Confirmer & Imprimer reçu' : 'Confirmer le paiement' }}
                        </span>
                        <span wire:loading><span class="spinner-border spinner-border-sm me-1"></span>Traitement...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
