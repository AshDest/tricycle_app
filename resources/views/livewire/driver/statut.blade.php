<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-person-badge me-2 text-primary"></i>Mon Statut du Jour
            </h4>
            <p class="text-muted mb-0">{{ now()->translatedFormat('l d F Y') }}</p>
        </div>
        <button class="btn btn-sm btn-outline-primary" wire:click="$refresh">
            <i class="bi bi-arrow-clockwise me-1"></i>Actualiser
        </button>
    </div>

    <!-- Statut du jour -->
    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-body text-center py-5">
                    @php
                        $statutConfig = [
                            'payé' => ['color' => 'success', 'icon' => 'check-circle-fill', 'text' => 'Versement Effectué'],
                            'partiellement_payé' => ['color' => 'warning', 'icon' => 'exclamation-circle-fill', 'text' => 'Versement Partiel'],
                            'en_retard' => ['color' => 'danger', 'icon' => 'x-circle-fill', 'text' => 'Versement en Retard'],
                            'non_effectué' => ['color' => 'secondary', 'icon' => 'clock-fill', 'text' => 'Versement Non Effectué'],
                        ];
                        $config = $statutConfig[$statutJour ?? 'non_effectué'] ?? $statutConfig['non_effectué'];
                    @endphp
                    <div class="rounded-circle bg-{{ $config['color'] }} bg-opacity-10 p-4 d-inline-flex mb-4" style="width: 120px; height: 120px;">
                        <i class="bi bi-{{ $config['icon'] }} text-{{ $config['color'] }}" style="font-size: 4rem; margin: auto;"></i>
                    </div>
                    <h3 class="fw-bold text-{{ $config['color'] }} mb-2">{{ $config['text'] }}</h3>
                    <p class="text-muted mb-0">Statut de votre versement aujourd'hui</p>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-cash-stack me-2 text-success"></i>Détails du Jour</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="d-flex justify-content-between py-3 border-bottom">
                            <span class="text-muted">Montant attendu</span>
                            <span class="fw-bold">{{ number_format($montantAttendu ?? 0) }} FC</span>
                        </li>
                        <li class="d-flex justify-content-between py-3 border-bottom">
                            <span class="text-muted">Montant versé</span>
                            <span class="fw-bold text-{{ ($montantVerse ?? 0) >= ($montantAttendu ?? 1) ? 'success' : 'warning' }}">
                                {{ number_format($montantVerse ?? 0) }} FC
                            </span>
                        </li>
                        <li class="d-flex justify-content-between py-3 border-bottom">
                            <span class="text-muted">Reste à payer</span>
                            <span class="fw-bold text-{{ ($resteAPayer ?? 0) > 0 ? 'danger' : 'success' }}">
                                {{ number_format($resteAPayer ?? 0) }} FC
                            </span>
                        </li>
                        <li class="d-flex justify-content-between py-3">
                            <span class="text-muted">Mode de paiement</span>
                            <span class="badge bg-light text-dark">
                                <i class="bi bi-{{ $modePaiement === 'cash' ? 'cash' : ($modePaiement === 'mobile_money' ? 'phone' : 'bank') }} me-1"></i>
                                {{ ucfirst(str_replace('_', ' ', $modePaiement ?? 'N/A')) }}
                            </span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Récapitulatif de Performance -->
    <div class="card mb-4">
        <div class="card-header py-3">
            <h6 class="mb-0 fw-bold"><i class="bi bi-bar-chart me-2 text-info"></i>Récapitulatif de Performance (Ce Mois)</h6>
        </div>
        <div class="card-body">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="text-center p-3 rounded bg-success bg-opacity-10">
                        <h2 class="fw-bold text-success mb-1">{{ $joursPayes ?? 0 }}</h2>
                        <p class="text-muted mb-0 small">Jours payés complets</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center p-3 rounded bg-danger bg-opacity-10">
                        <h2 class="fw-bold text-danger mb-1">{{ $joursEnRetard ?? 0 }}</h2>
                        <p class="text-muted mb-0 small">Jours en retard</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center p-3 rounded bg-warning bg-opacity-10">
                        <h2 class="fw-bold text-warning mb-1">{{ number_format($arrieresCumules ?? 0) }} FC</h2>
                        <p class="text-muted mb-0 small">Arriérés cumulés</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Moto assignée -->
    @if($moto ?? null)
    <div class="card">
        <div class="card-header py-3">
            <h6 class="mb-0 fw-bold"><i class="bi bi-bicycle me-2 text-primary"></i>Ma Moto Assignée</h6>
        </div>
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-2 text-center mb-3 mb-md-0">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 d-inline-flex">
                        <i class="bi bi-bicycle text-primary" style="font-size: 2rem;"></i>
                    </div>
                </div>
                <div class="col-md-10">
                    <div class="row g-3">
                        <div class="col-sm-6 col-md-3">
                            <label class="text-muted small">Plaque</label>
                            <p class="fw-semibold mb-0">{{ $moto->plaque_immatriculation ?? 'N/A' }}</p>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <label class="text-muted small">Matricule</label>
                            <p class="fw-semibold mb-0">{{ $moto->numero_matricule ?? 'N/A' }}</p>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <label class="text-muted small">Tarif journalier</label>
                            <p class="fw-semibold mb-0">{{ number_format($moto->montant_journalier_attendu ?? 0) }} FC</p>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <label class="text-muted small">Statut</label>
                            <p class="mb-0">
                                <span class="badge badge-soft-{{ $moto->statut === 'actif' ? 'success' : 'secondary' }}">
                                    {{ ucfirst($moto->statut ?? 'N/A') }}
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
