<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-file-earmark-bar-graph me-2 text-info"></i>Mes Relevés
            </h4>
            <p class="text-muted mb-0">Consultez vos relevés mensuels de paiements reçus</p>
        </div>
        @if($proprietaire)
        <button wire:click="exportPdf" class="btn btn-danger" wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="exportPdf">
                <i class="bi bi-file-pdf me-1"></i>Exporter PDF
            </span>
            <span wire:loading wire:target="exportPdf">
                <span class="spinner-border spinner-border-sm me-1"></span>Génération...
            </span>
        </button>
        @endif
    </div>

    @if(!$proprietaire)
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle me-2"></i>
        <strong>Attention:</strong> Votre compte n'est pas associé à un profil propriétaire. Veuillez contacter l'administration.
    </div>
    @else

    <!-- Filtres période -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Mois</label>
                    <select wire:model.live="mois" class="form-select">
                        @foreach($moisOptions as $num => $nom)
                        <option value="{{ $num }}">{{ $nom }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Année</label>
                    <select wire:model.live="annee" class="form-select">
                        @foreach($anneeOptions as $an)
                        <option value="{{ $an }}">{{ $an }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <div class="alert alert-info mb-0 py-2">
                        <i class="bi bi-calendar3 me-2"></i>
                        Période: <strong>{{ $moisOptions[$mois] ?? '' }} {{ $annee }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Résumé -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-4">
            <div class="card bg-success bg-opacity-10 border-0 h-100">
                <div class="card-body text-center py-4">
                    <i class="bi bi-cash-stack fs-2 text-success mb-2"></i>
                    <h4 class="fw-bold text-success mb-1">{{ number_format($recuMoisUsd ?? 0, 2) }} $</h4>
                    <small class="text-muted">Reçu ce mois (USD)</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-4">
            <div class="card bg-primary bg-opacity-10 border-0 h-100">
                <div class="card-body text-center py-4">
                    <i class="bi bi-wallet2 fs-2 text-primary mb-2"></i>
                    <h4 class="fw-bold text-primary mb-1">{{ number_format($totalRecuUsd ?? 0, 2) }} $</h4>
                    <small class="text-muted">Total reçu (USD)</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-4">
            <div class="card bg-info bg-opacity-10 border-0 h-100">
                <div class="card-body text-center py-4">
                    <i class="bi bi-receipt fs-2 text-info mb-2"></i>
                    <h4 class="fw-bold text-info mb-1">{{ $nbPaiements ?? 0 }}</h4>
                    <small class="text-muted">Paiements ce mois</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Détails des paiements -->
    <div class="card">
        <div class="card-header py-3">
            <h6 class="mb-0 fw-bold"><i class="bi bi-list-check me-2 text-primary"></i>Paiements Reçus - {{ $moisOptions[$mois] ?? '' }} {{ $annee }}</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Date</th>
                            <th>Mode</th>
                            <th>Référence</th>
                            <th class="text-end">Montant (USD)</th>
                            <th class="text-center pe-4">Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($paiements ?? [] as $payment)
                        <tr>
                            <td class="ps-4">
                                <span class="fw-medium">{{ $payment->date_paiement?->format('d/m/Y') ?? $payment->created_at?->format('d/m/Y') }}</span>
                                <small class="text-muted d-block">{{ $payment->created_at?->format('H:i') }}</small>
                            </td>
                            <td>
                                @php
                                    $modeIcons = ['mobile_money' => 'phone', 'mpesa' => 'phone', 'airtel_money' => 'phone', 'orange_money' => 'phone', 'virement_bancaire' => 'bank', 'cash' => 'cash'];
                                @endphp
                                <span class="badge bg-light text-dark">
                                    <i class="bi bi-{{ $modeIcons[$payment->mode_paiement] ?? 'credit-card' }} me-1"></i>
                                    {{ ucfirst(str_replace('_', ' ', $payment->mode_paiement ?? 'N/A')) }}
                                </span>
                            </td>
                            <td><code>{{ $payment->reference_paiement ?? 'N/A' }}</code></td>
                            <td class="text-end fw-semibold text-success">{{ number_format($payment->montant_usd ?? 0, 2) }} $</td>
                            <td class="text-center pe-4">
                                @php
                                    $statutColors = [
                                        'paye' => 'success',
                                        'payé' => 'success',
                                        'valide' => 'success',
                                        'en_attente' => 'warning',
                                        'demande' => 'info',
                                        'rejete' => 'danger',
                                    ];
                                @endphp
                                <span class="badge badge-soft-{{ $statutColors[$payment->statut] ?? 'secondary' }}">
                                    {{ ucfirst(str_replace('_', ' ', $payment->statut ?? 'N/A')) }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="bi bi-receipt fs-1 d-block mb-3"></i>
                                <p class="mb-0">Aucun paiement pour cette période</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if(count($paiements ?? []) > 0)
                    <tfoot class="bg-light">
                        <tr class="fw-bold">
                            <td class="ps-4" colspan="3">TOTAL</td>
                            <td class="text-end text-success">{{ number_format($recuMoisUsd ?? 0, 2) }} $</td>
                            <td></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>


    @endif {{-- End of @if($proprietaire) --}}
</div>
