<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-wallet2 me-2 text-success"></i>Détails Paiement #{{ $payment->id }}
            </h4>
            <p class="text-muted mb-0">{{ $payment->date_demande?->format('d/m/Y') }}</p>
        </div>
        <a href="{{ route('admin.payments.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Retour
        </a>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <!-- Informations du paiement -->
            <div class="card mb-4">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-info-circle me-2 text-primary"></i>Informations du paiement</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Propriétaire</label>
                            <p class="fw-medium mb-0">{{ $payment->proprietaire?->user?->name ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Mode de paiement</label>
                            <p class="fw-medium mb-0">
                                <span class="badge bg-light text-dark">
                                    {{ \App\Models\Payment::getModesPaiement()[$payment->mode_paiement] ?? $payment->mode_paiement }}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Numéro de compte</label>
                            <p class="fw-medium mb-0">{{ $payment->numero_compte ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Numéro d'envoi</label>
                            <p class="fw-medium mb-0">{{ $payment->numero_envoi ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Période</label>
                            <p class="fw-medium mb-0">
                                @if($payment->periode_debut && $payment->periode_fin)
                                {{ $payment->periode_debut->format('d/m/Y') }} - {{ $payment->periode_fin->format('d/m/Y') }}
                                @else
                                N/A
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Référence</label>
                            <p class="fw-medium mb-0">{{ $payment->reference_paiement ?? 'N/A' }}</p>
                        </div>
                        @if($payment->notes)
                        <div class="col-12">
                            <label class="form-label small text-muted">Notes</label>
                            <p class="mb-0">{{ $payment->notes }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Historique -->
            <div class="card">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-clock-history me-2 text-info"></i>Historique</h6>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Demande soumise</span>
                            <span>{{ $payment->demande_at?->format('d/m/Y H:i') ?? $payment->created_at?->format('d/m/Y H:i') }}</span>
                        </li>
                        @if($payment->date_paiement)
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Paiement effectué</span>
                            <span>{{ $payment->date_paiement->format('d/m/Y') }}</span>
                        </li>
                        @endif
                        @if($payment->valide_at)
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Validé</span>
                            <span>{{ $payment->valide_at->format('d/m/Y H:i') }}</span>
                        </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Statut -->
            <div class="card mb-4">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-flag me-2 text-secondary"></i>Statut</h6>
                </div>
                <div class="card-body text-center">
                    @php
                        $statutColors = [
                            'demande' => 'warning',
                            'en_cours' => 'info',
                            'paye' => 'primary',
                            'valide' => 'success',
                            'rejete' => 'danger',
                        ];
                    @endphp
                    <span class="badge bg-{{ $statutColors[$payment->statut] ?? 'secondary' }} fs-6 px-3 py-2">
                        {{ \App\Models\Payment::getStatuts()[$payment->statut] ?? ucfirst($payment->statut) }}
                    </span>
                </div>
            </div>

            <!-- Montants -->
            <div class="card">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-cash-coin me-2 text-success"></i>Montants</h6>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Total dû</span>
                            <span class="fw-bold">{{ number_format($payment->total_du ?? 0) }} FC</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between bg-success bg-opacity-10">
                            <span>Total payé</span>
                            <span class="fw-bold text-success">{{ number_format($payment->total_paye ?? 0) }} FC</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Reste</span>
                            <span class="fw-bold {{ ($payment->total_du - $payment->total_paye) > 0 ? 'text-danger' : 'text-success' }}">
                                {{ number_format(($payment->total_du ?? 0) - ($payment->total_paye ?? 0)) }} FC
                            </span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
