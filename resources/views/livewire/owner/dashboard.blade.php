<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-building me-2 text-warning"></i>Tableau de Bord Propriétaire
            </h4>
            <p class="text-muted mb-0">Suivi de vos revenus et de votre flotte</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-light text-dark px-3 py-2">
                <i class="bi bi-calendar3 me-2"></i>{{ now()->translatedFormat('l d F Y') }}
            </span>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card h-100 border-start border-success border-4">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small text-uppercase fw-semibold mb-2">Revenus ce Mois</p>
                        <h3 class="fw-bold text-success mb-1">{{ number_format($revenusMois ?? 0) }}</h3>
                        <small class="text-muted">FC</small>
                    </div>
                    <div class="stat-icon bg-success bg-opacity-10 text-success">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card h-100 border-start border-primary border-4">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small text-uppercase fw-semibold mb-2">Mes Motos</p>
                        <h3 class="fw-bold mb-1">{{ $totalMotos ?? 0 }}</h3>
                        <small class="text-success"><i class="bi bi-check-circle me-1"></i>{{ $motosActives ?? 0 }} actives</small>
                    </div>
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-bicycle"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card h-100 border-start border-info border-4">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small text-uppercase fw-semibold mb-2">Solde Disponible</p>
                        <h3 class="fw-bold text-info mb-1">{{ number_format($prochainPaiement ?? 0) }}</h3>
                        <small class="text-muted">FC à retirer</small>
                    </div>
                    <div class="stat-icon bg-info bg-opacity-10 text-info">
                        <i class="bi bi-wallet2"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card h-100 border-start border-warning border-4">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small text-uppercase fw-semibold mb-2">Arriérés Motards</p>
                        <h3 class="fw-bold text-{{ ($totalArrieres ?? 0) > 0 ? 'danger' : 'success' }} mb-1">{{ number_format($totalArrieres ?? 0) }}</h3>
                        <small class="text-muted">FC</small>
                    </div>
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Répartition Hebdomadaire -->
    @if($repartitionHebdo)
    <div class="card mb-4 border-info">
        <div class="card-header bg-info bg-opacity-10 py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold text-info">
                    <i class="bi bi-pie-chart me-2"></i>Répartition Hebdomadaire (Semaine en cours)
                </h6>
                <small class="text-muted">{{ $repartitionHebdo['periode']['debut'] ?? '' }} - {{ $repartitionHebdo['periode']['fin'] ?? '' }}</small>
            </div>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="text-center p-3 bg-light rounded">
                        <small class="text-muted d-block mb-1">Total Versé cette semaine</small>
                        <h4 class="fw-bold text-success mb-0">{{ number_format($repartitionHebdo['total_verse'] ?? 0) }} FC</h4>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="text-center p-3 bg-warning bg-opacity-10 rounded border border-warning">
                        <small class="text-muted d-block mb-1">Total Attendu cette semaine</small>
                        <h4 class="fw-bold text-warning mb-0">{{ number_format($repartitionHebdo['total_attendu'] ?? 0) }} FC</h4>
                    </div>
                </div>
            </div>
            <div class="alert alert-light mt-3 mb-0 small">
                <i class="bi bi-info-circle me-2"></i>
                <strong>Rappel:</strong> Les 6 jours de versement sont comptabilisés dans une caisse unique.
            </div>
        </div>
    </div>
    @endif

    <!-- Quick Actions & Recent Payments -->
    <div class="row g-3 mb-4">
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-lightning me-2 text-warning"></i>Actions Rapides</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('owner.versements.index') }}" class="btn btn-outline-success">
                            <i class="bi bi-cash-coin me-2"></i>Voir mes versements
                        </a>
                        <a href="{{ route('owner.payments.index') }}" class="btn btn-outline-primary">
                            <i class="bi bi-credit-card me-2"></i>Historique paiements
                        </a>
                    </div>

                    <!-- Résumé -->
                    <div class="mt-4 pt-3 border-top">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">Total revenus</span>
                            <span class="fw-bold">{{ number_format($revenusTotal ?? 0) }} FC</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">Maintenances en cours</span>
                            <span class="badge bg-warning">{{ $maintenancesEnCours ?? 0 }}</span>
                        </div>
                        @if(($paiementsEnAttente ?? 0) > 0)
                        <div class="d-flex justify-content-between">
                            <span class="text-muted small">Demandes en attente</span>
                            <span class="badge bg-info">{{ $paiementsEnAttente }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-clock-history me-2 text-primary"></i>Derniers Versements</h6>
                    <a href="{{ route('owner.versements.index') }}" class="btn btn-sm btn-outline-primary">Voir tout</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">Date</th>
                                    <th>Motard</th>
                                    <th>Moto</th>
                                    <th class="text-end">Montant</th>
                                    <th class="pe-4">Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($derniersVersements ?? [] as $versement)
                                <tr>
                                    <td class="ps-4">{{ $versement->date_versement?->format('d/m/Y') ?? 'N/A' }}</td>
                                    <td>{{ $versement->motard->user->name ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge bg-light text-dark">
                                            {{ $versement->moto->plaque_immatriculation ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="text-end fw-semibold text-success">{{ number_format($versement->montant ?? 0) }} FC</td>
                                    <td class="pe-4">
                                        @php
                                            $statutColors = [
                                                'payé' => 'success',
                                                'partiellement_payé' => 'warning',
                                                'en_retard' => 'danger',
                                                'non_effectué' => 'secondary'
                                            ];
                                        @endphp
                                        <span class="badge badge-soft-{{ $statutColors[$versement->statut] ?? 'secondary' }}">
                                            {{ ucfirst(str_replace('_', ' ', $versement->statut ?? 'N/A')) }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                        Aucun versement récent
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Motos List -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center py-3">
            <h6 class="mb-0 fw-bold"><i class="bi bi-bicycle me-2 text-info"></i>Mes Motos ({{ $totalMotos ?? 0 }})</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">Plaque</th>
                            <th>Matricule</th>
                            <th>Motard Assigné</th>
                            <th class="pe-4 text-center">Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($motos ?? [] as $moto)
                        <tr>
                            <td class="ps-4">
                                <span class="badge bg-light text-dark">
                                    <i class="bi bi-bicycle me-1"></i>{{ $moto->plaque_immatriculation ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="text-muted">{{ $moto->numero_matricule ?? 'N/A' }}</td>
                            <td>
                                @if($moto->motard)
                                <span class="fw-medium">{{ $moto->motard->user->name ?? 'N/A' }}</span>
                                @else
                                <span class="text-muted fst-italic">Non assigné</span>
                                @endif
                            </td>
                            <td class="pe-4 text-center">
                                <span class="badge badge-soft-{{ $moto->statut === 'actif' ? 'success' : 'secondary' }}">
                                    {{ ucfirst($moto->statut ?? 'Inactif') }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">
                                <i class="bi bi-bicycle fs-3 d-block mb-2"></i>
                                Aucune moto enregistrée
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
