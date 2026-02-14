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
                        <small class="text-muted">FCFA</small>
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
                        <p class="text-muted small text-uppercase fw-semibold mb-2">Prochain Paiement</p>
                        <h3 class="fw-bold text-info mb-1">{{ number_format($prochainPaiement ?? 0) }}</h3>
                        <small class="text-muted">FCFA estimé</small>
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
                        <p class="text-muted small text-uppercase fw-semibold mb-2">Maintenances</p>
                        <h3 class="fw-bold mb-1">{{ $maintenancesEnCours ?? 0 }}</h3>
                        <small class="text-muted">en cours</small>
                    </div>
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                        <i class="bi bi-tools"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
                        <a href="{{ route('owner.reports.index') }}" class="btn btn-outline-info">
                            <i class="bi bi-file-earmark-bar-graph me-2"></i>Mes rapports
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-clock-history me-2 text-primary"></i>Derniers Paiements</h6>
                    <a href="{{ route('owner.payments.index') }}" class="btn btn-sm btn-outline-primary">Voir tout</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">Date</th>
                                    <th>Montant</th>
                                    <th>Mode</th>
                                    <th class="pe-4">Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($derniersPaiements ?? [] as $paiement)
                                <tr>
                                    <td class="ps-4">{{ $paiement->date_paiement?->format('d/m/Y') ?? 'N/A' }}</td>
                                    <td class="fw-semibold text-success">{{ number_format($paiement->montant ?? 0) }} FCFA</td>
                                    <td>{{ ucfirst($paiement->mode_paiement ?? 'N/A') }}</td>
                                    <td class="pe-4">
                                        <span class="badge badge-soft-{{ $paiement->statut === 'payé' ? 'success' : 'warning' }}">
                                            {{ ucfirst($paiement->statut ?? 'En attente') }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">
                                        <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                        Aucun paiement récent
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
            <h6 class="mb-0 fw-bold"><i class="bi bi-bicycle me-2 text-info"></i>Mes Motos</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">Plaque</th>
                            <th>Motard Assigné</th>
                            <th>Versements ce mois</th>
                            <th class="pe-4">Statut</th>
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
                            <td>{{ $moto->motard->user->name ?? 'Non assigné' }}</td>
                            <td>{{ number_format($moto->versements_mois ?? 0) }} FCFA</td>
                            <td class="pe-4">
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
