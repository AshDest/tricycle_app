<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-speedometer2 me-2 text-primary"></i>Tableau de Bord OKAMI
            </h4>
            <p class="text-muted mb-0">Supervision opérationnelle de la flotte</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-light text-dark px-3 py-2">
                <i class="bi bi-calendar3 me-2"></i>{{ now()->translatedFormat('l d F Y') }}
            </span>
            <button class="btn btn-sm btn-outline-primary" wire:click="$refresh">
                <i class="bi bi-arrow-clockwise"></i>
            </button>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card h-100">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small text-uppercase fw-semibold mb-2">Versements Aujourd'hui</p>
                        <h3 class="fw-bold text-success mb-1">{{ number_format($versementsAujourdhui ?? 0) }}</h3>
                        <small class="text-muted">FC collectés</small>
                    </div>
                    <div class="stat-icon bg-success bg-opacity-10 text-success">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card h-100">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small text-uppercase fw-semibold mb-2">Motards Actifs</p>
                        <h3 class="fw-bold mb-1">{{ $motardsActifs ?? 0 }}</h3>
                        <small class="text-muted">sur {{ $totalMotards ?? 0 }} total</small>
                    </div>
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-people-fill"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card h-100">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small text-uppercase fw-semibold mb-2">Motos Actives</p>
                        <h3 class="fw-bold text-info mb-1">{{ $motosActives ?? 0 }}</h3>
                        <small class="text-muted">sur {{ $totalMotos ?? 0 }} total</small>
                    </div>
                    <div class="stat-icon bg-info bg-opacity-10 text-info">
                        <i class="bi bi-bicycle"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card h-100">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small text-uppercase fw-semibold mb-2">Taux de Collecte</p>
                        <h3 class="fw-bold text-{{ ($tauxCollecte ?? 0) >= 80 ? 'success' : 'warning' }} mb-1">{{ $tauxCollecte ?? 0 }}%</h3>
                        <small class="text-muted">aujourd'hui</small>
                    </div>
                    <div class="stat-icon bg-{{ ($tauxCollecte ?? 0) >= 80 ? 'success' : 'warning' }} bg-opacity-10 text-{{ ($tauxCollecte ?? 0) >= 80 ? 'success' : 'warning' }}">
                        <i class="bi bi-graph-up"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section Arriérés / Manquants des Motards -->
    <div class="card mb-4 border-0 shadow-sm" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);">
        <div class="card-body text-white">
            <div class="row align-items-center">
                <div class="col-lg-3 mb-3 mb-lg-0">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-white bg-opacity-25 rounded-circle p-3">
                            <i class="bi bi-exclamation-triangle fs-2"></i>
                        </div>
                        <div>
                            <h5 class="mb-1 fw-bold">Arriérés Motards</h5>
                            <small class="opacity-75">{{ $motardsAvecArrieres ?? 0 }} motard(s) concerné(s)</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-9">
                    <div class="row g-3 text-center">
                        <div class="col-3">
                            <div class="bg-white bg-opacity-10 rounded p-3">
                                <small class="d-block opacity-75">Aujourd'hui</small>
                                <h4 class="fw-bold mb-0">{{ number_format($arrieresJour ?? 0) }} FC</h4>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="bg-white bg-opacity-10 rounded p-3">
                                <small class="d-block opacity-75">Cette semaine</small>
                                <h4 class="fw-bold mb-0">{{ number_format($arrieresSemaine ?? 0) }} FC</h4>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="bg-white bg-opacity-10 rounded p-3">
                                <small class="d-block opacity-75">Ce mois</small>
                                <h4 class="fw-bold mb-0">{{ number_format($arrieresMois ?? 0) }} FC</h4>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="bg-white bg-opacity-25 rounded p-3">
                                <small class="d-block opacity-75">Total cumulé</small>
                                <h4 class="fw-bold mb-0">{{ number_format($arrieresCumules ?? 0) }} FC</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if(count($topMotardsArrieres ?? []) > 0)
            <div class="mt-3 pt-3 border-top border-white border-opacity-25">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <small class="opacity-75 fw-bold">
                        <i class="bi bi-person-exclamation me-1"></i>
                        Top 5 Motards avec Arriérés
                    </small>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    @foreach($topMotardsArrieres as $motardArr)
                    <span class="badge bg-white bg-opacity-25 px-3 py-2">
                        {{ $motardArr['nom'] }}: <strong>{{ number_format($motardArr['total_arrieres']) }} FC</strong>
                    </span>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Total Versements -->
    <div class="card mb-4 border-0 shadow-sm" style="background: linear-gradient(135deg, #00838f 0%, #006064 100%);">
        <div class="card-body text-white">
            <div class="row align-items-center">
                <div class="col-lg-4 mb-3 mb-lg-0">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-white bg-opacity-25 rounded-circle p-3">
                            <i class="bi bi-wallet2 fs-2"></i>
                        </div>
                        <div>
                            <h5 class="mb-1 fw-bold">Total Versements</h5>
                            <small class="opacity-75">Montant total des versements collectés</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-8">
                    <div class="row g-3 text-center">
                        <div class="col-4">
                            <div class="bg-white bg-opacity-10 rounded p-3">
                                <small class="d-block opacity-75">Cette semaine</small>
                                <h4 class="fw-bold mb-0">{{ number_format($soldeVersementsSemaine ?? 0) }} FC</h4>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="bg-white bg-opacity-10 rounded p-3">
                                <small class="d-block opacity-75">Ce mois</small>
                                <h4 class="fw-bold mb-0">{{ number_format($soldeVersementsMois ?? 0) }} FC</h4>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="bg-white bg-opacity-25 rounded p-3">
                                <small class="d-block opacity-75">Total cumulé</small>
                                <h4 class="fw-bold mb-0">{{ number_format($soldeVersementsTotal ?? 0) }} FC</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-3 pt-3 border-top border-white border-opacity-25">
                <div class="d-flex justify-content-between align-items-center">
                    <small class="opacity-75">
                        <i class="bi bi-info-circle me-1"></i>
                        Les 5 jours de versement vont dans une caisse unique
                    </small>
                    <a href="{{ route('supervisor.reports.repartition') }}" class="btn btn-sm btn-light">
                        <i class="bi bi-bar-chart me-1"></i>Voir les rapports détaillés
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Solde OKAMI des Lavages (Part 20%) -->
    <div class="card mb-4 border-0 shadow-sm" style="background: linear-gradient(135deg, #0288d1 0%, #01579b 100%);">
        <div class="card-body text-white">
            <div class="row align-items-center">
                <div class="col-lg-4 mb-3 mb-lg-0">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-white bg-opacity-25 rounded-circle p-3">
                            <i class="bi bi-droplet-half fs-2"></i>
                        </div>
                        <div>
                            <h5 class="mb-1 fw-bold">Solde OKAMI - Lavages</h5>
                            <small class="opacity-75">Part de 20% sur chaque lavage interne</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-8">
                    <div class="row g-3 text-center">
                        <div class="col-3">
                            <div class="bg-white bg-opacity-10 rounded p-3">
                                <small class="d-block opacity-75">Aujourd'hui</small>
                                <h4 class="fw-bold mb-0">{{ number_format($soldeOkamiLavageJour ?? 0) }} FC</h4>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="bg-white bg-opacity-10 rounded p-3">
                                <small class="d-block opacity-75">Cette semaine</small>
                                <h4 class="fw-bold mb-0">{{ number_format($soldeOkamiLavageSemaine ?? 0) }} FC</h4>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="bg-white bg-opacity-10 rounded p-3">
                                <small class="d-block opacity-75">Ce mois</small>
                                <h4 class="fw-bold mb-0">{{ number_format($soldeOkamiLavageMois ?? 0) }} FC</h4>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="bg-white bg-opacity-25 rounded p-3">
                                <small class="d-block opacity-75">Total cumulé</small>
                                <h4 class="fw-bold mb-0">{{ number_format($soldeOkamiLavageTotal ?? 0) }} FC</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-3 pt-3 border-top border-white border-opacity-25">
                <div class="d-flex justify-content-between align-items-center">
                    <small class="opacity-75">
                        <i class="bi bi-info-circle me-1"></i>
                        20% du prix de chaque lavage des motos du système revient à OKAMI
                    </small>
                    <span class="badge bg-white text-primary px-3 py-2">
                        <i class="bi bi-calculator me-1"></i>
                        Total Lavage: {{ number_format($soldeOkamiLavageTotal ?? 0) }} FC
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Solde OKAMI Commission -->
    @php $tauxUsdCommission = \App\Models\SystemSetting::getTauxUsdCdf(); @endphp
    <div class="card mb-4 border-0 shadow-sm" style="background: linear-gradient(135deg, #6f42c1 0%, #9b6ed8 100%);">
        <div class="card-body text-white">
            <div class="row align-items-center">
                <div class="col-lg-5 mb-3 mb-lg-0">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-white bg-opacity-25 rounded-circle p-3">
                            <i class="bi bi-percent fs-2"></i>
                        </div>
                        <div>
                            <h5 class="mb-1 fw-bold">Solde OKAMI - Commissions</h5>
                            <small class="opacity-75">Part de 30% sur chaque commission (70% LATEM)</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 text-center">
                    <div class="bg-white bg-opacity-25 rounded p-3">
                        <small class="d-block opacity-75">Disponible</small>
                        <h3 class="fw-bold mb-0">{{ $tauxUsdCommission > 0 ? number_format(($soldeOkamiCommissionTotal ?? 0) / $tauxUsdCommission, 2) : '0.00' }} $</h3>
                    </div>
                </div>
                <div class="col-lg-4">
                    @if(count($commissionsParMois ?? []) > 0)
                    <div class="small">
                        <strong class="d-block mb-2">Dernières commissions validées:</strong>
                        @foreach($commissionsParMois as $comm)
                        <div class="d-flex justify-content-between py-1 border-bottom border-white border-opacity-25">
                            <span>{{ $comm['periode'] }}</span>
                            <span class="fw-bold">{{ $tauxUsdCommission > 0 ? number_format($comm['part_okami'] / $tauxUsdCommission, 2) : '0.00' }} $</span>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-center opacity-75">
                        <i class="bi bi-inbox d-block fs-3"></i>
                        <small>Aucune commission validée</small>
                    </div>
                    @endif
                </div>
            </div>
            <div class="mt-3 pt-3 border-top border-white border-opacity-25">
                <div class="d-flex justify-content-between align-items-center">
                    <small class="opacity-75">
                        <i class="bi bi-info-circle me-1"></i>
                        Les commissions sont réparties: 70% pour LATEM et 30% pour OKAMI
                    </small>
                    <a href="{{ route('supervisor.payments.create') }}" class="btn btn-sm btn-light">
                        <i class="bi bi-plus-circle me-1"></i>Demande de paiement
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Global Caisse -->
    <div class="alert alert-info mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <i class="bi bi-wallet2 me-2 fs-4"></i>
                <strong>Total Global en Caisse (Collecteurs):</strong>
            </div>
            <span class="badge bg-info text-white fs-5 px-3 py-2">
                {{ number_format(\App\Models\Collecteur::sum('solde_caisse')) }} FC
            </span>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-lightning me-2 text-warning"></i>Actions Rapides</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6 col-md-3 col-lg">
                            <a href="{{ route('supervisor.versements.index') }}" class="btn btn-outline-success w-100 py-3">
                                <i class="bi bi-cash-coin fs-4 d-block mb-2"></i>
                                Versements
                            </a>
                        </div>
                        <div class="col-6 col-md-3 col-lg">
                            <a href="{{ route('supervisor.motards.index') }}" class="btn btn-outline-primary w-100 py-3">
                                <i class="bi bi-people fs-4 d-block mb-2"></i>
                                Motards
                            </a>
                        </div>
                        <div class="col-6 col-md-3 col-lg">
                            <a href="{{ route('supervisor.motos.index') }}" class="btn btn-outline-info w-100 py-3">
                                <i class="bi bi-bicycle fs-4 d-block mb-2"></i>
                                Motos
                            </a>
                        </div>
                        <div class="col-6 col-md-3 col-lg">
                            <a href="{{ route('supervisor.maintenances.index') }}" class="btn btn-outline-secondary w-100 py-3">
                                <i class="bi bi-tools fs-4 d-block mb-2"></i>
                                Maintenances
                            </a>
                        </div>
                        <div class="col-6 col-md-3 col-lg">
                            <a href="{{ route('supervisor.accidents.index') }}" class="btn btn-outline-danger w-100 py-3">
                                <i class="bi bi-exclamation-triangle fs-4 d-block mb-2"></i>
                                Accidents
                            </a>
                        </div>
                        <div class="col-6 col-md-3 col-lg">
                            <a href="{{ route('supervisor.proprietaires.index') }}" class="btn btn-outline-warning w-100 py-3">
                                <i class="bi bi-building fs-4 d-block mb-2"></i>
                                Propriétaires
                            </a>
                        </div>
                        <div class="col-6 col-md-3 col-lg">
                            <a href="{{ route('supervisor.payments.index') }}" class="btn btn-outline-dark w-100 py-3">
                                <i class="bi bi-wallet2 fs-4 d-block mb-2"></i>
                                Paiements
                            </a>
                        </div>
                        <div class="col-6 col-md-3 col-lg">
                            <a href="{{ route('supervisor.reports.daily') }}" class="btn btn-outline-primary w-100 py-3">
                                <i class="bi bi-file-earmark-bar-graph fs-4 d-block mb-2"></i>
                                Rapports
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-clock-history me-2 text-primary"></i>Dernières Activités</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">Motard</th>
                                    <th>Action</th>
                                    <th>Montant</th>
                                    <th class="pe-4">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($dernieresActivites ?? [] as $activite)
                                <tr>
                                    <td class="ps-4">{{ $activite->motard->user->name ?? 'N/A' }}</td>
                                    <td>{{ $activite->type ?? 'Versement' }}</td>
                                    <td>{{ number_format($activite->montant ?? 0) }} FC</td>
                                    <td class="pe-4 text-muted small">{{ $activite->created_at?->diffForHumans() }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">
                                        <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                        Aucune activité récente
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-bell me-2 text-danger"></i>Alertes</h6>
                </div>
                <div class="card-body">
                    @if(($alertes ?? collect())->isEmpty())
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-check-circle fs-1 text-success d-block mb-2"></i>
                            <p class="mb-0">Aucune alerte en cours</p>
                        </div>
                    @else
                        @foreach($alertes ?? [] as $alerte)
                        <div class="d-flex align-items-center gap-3 mb-3 p-2 rounded bg-light">
                            <div class="rounded-circle bg-{{ $alerte->color ?? 'warning' }} bg-opacity-10 p-2">
                                <i class="bi bi-{{ $alerte->icon ?? 'exclamation-triangle' }} text-{{ $alerte->color ?? 'warning' }}"></i>
                            </div>
                            <div class="flex-grow-1">
                                <p class="mb-0 small fw-medium">{{ $alerte->message ?? '' }}</p>
                                <small class="text-muted">{{ $alerte->created_at?->diffForHumans() }}</small>
                            </div>
                        </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
