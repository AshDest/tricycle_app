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
                        <small class="text-muted">FCFA collectés</small>
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
                        <p class="text-muted small text-uppercase fw-semibold mb-2">Cas Litigieux</p>
                        <h3 class="fw-bold text-warning mb-1">{{ $casLitigieux ?? 0 }}</h3>
                        <small class="text-muted">en attente de validation</small>
                    </div>
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card h-100">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small text-uppercase fw-semibold mb-2">Taux de Collecte</p>
                        <h3 class="fw-bold text-info mb-1">{{ $tauxCollecte ?? 0 }}%</h3>
                        <small class="text-muted">aujourd'hui</small>
                    </div>
                    <div class="stat-icon bg-info bg-opacity-10 text-info">
                        <i class="bi bi-graph-up"></i>
                    </div>
                </div>
            </div>
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
                        <div class="col-md-3">
                            <a href="{{ route('supervisor.versements.index') }}" class="btn btn-outline-success w-100 py-3">
                                <i class="bi bi-cash-coin fs-4 d-block mb-2"></i>
                                Voir Versements
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('supervisor.validation.index') }}" class="btn btn-outline-warning w-100 py-3">
                                <i class="bi bi-check2-square fs-4 d-block mb-2"></i>
                                Validations OKAMI
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('supervisor.motards.index') }}" class="btn btn-outline-primary w-100 py-3">
                                <i class="bi bi-people fs-4 d-block mb-2"></i>
                                Liste Motards
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('supervisor.reports.daily') }}" class="btn btn-outline-info w-100 py-3">
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
                                    <td>{{ number_format($activite->montant ?? 0) }} FCFA</td>
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
