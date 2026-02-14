<div>

    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h4 class="page-title mb-1">Tableau de Bord</h4>
            <p class="text-muted mb-0"><i class="bi bi-speedometer2 me-1"></i>Vue d'ensemble de votre flotte</p>
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

    <!-- Stats Row 1: Fleet -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card h-100">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small text-uppercase fw-semibold mb-2">Total Motards</p>
                        <h3 class="fw-bold mb-2">{{ number_format($totalMotards) }}</h3>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge badge-soft-success"><i class="bi bi-check-circle me-1"></i>{{ $motardsActifs }} actifs</span>
                        </div>
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
                        <p class="text-muted small text-uppercase fw-semibold mb-2">Total Motos</p>
                        <h3 class="fw-bold mb-2">{{ number_format($totalMotos) }}</h3>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge badge-soft-success"><i class="bi bi-check-circle me-1"></i>{{ $motosActives }} actives</span>
                        </div>
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
                        <p class="text-muted small text-uppercase fw-semibold mb-2">Propriétaires</p>
                        <h3 class="fw-bold mb-2">{{ number_format($totalProprietaires) }}</h3>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge badge-soft-warning"><i class="bi bi-building me-1"></i>enregistrés</span>
                        </div>
                    </div>
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                        <i class="bi bi-building"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card h-100">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small text-uppercase fw-semibold mb-2">Utilisateurs</p>
                        <h3 class="fw-bold mb-2">{{ number_format($totalUsers) }}</h3>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge badge-soft-secondary"><i class="bi bi-people me-1"></i>au total</span>
                        </div>
                    </div>
                    <div class="stat-icon bg-secondary bg-opacity-10 text-secondary">
                        <i class="bi bi-people-fill"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Row 2: Finances -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card h-100 border-start border-success border-4">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small text-uppercase fw-semibold mb-2">Versements Aujourd'hui</p>
                        <h3 class="fw-bold text-success mb-1">{{ number_format($versementsAujourdhui) }}</h3>
                        <small class="text-muted">FC</small>
                        <div class="mt-2">
                            <small class="text-muted">Attendu: <strong>{{ number_format($versementsAttenduAujourdhui) }}</strong> FC</small>
                        </div>
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
                        <p class="text-muted small text-uppercase fw-semibold mb-2">Versements ce Mois</p>
                        <h3 class="fw-bold text-primary mb-1">{{ number_format($versementsCeMois) }}</h3>
                        <small class="text-muted">FC</small>
                    </div>
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-graph-up-arrow"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card h-100 border-start border-danger border-4">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small text-uppercase fw-semibold mb-2">Arriérés Cumulés</p>
                        <h3 class="fw-bold text-danger mb-1">{{ number_format($arrieresCumules) }}</h3>
                        <small class="text-muted">FC</small>
                    </div>
                    <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card h-100 border-start border-info border-4">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small text-uppercase fw-semibold mb-2">Tournées Aujourd'hui</p>
                        <h3 class="fw-bold mb-2">{{ $tourneesAujourdhui }}</h3>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="badge badge-soft-success">{{ $tourneesTerminees }} terminées</span>
                            <span class="badge badge-soft-warning">{{ $tourneesEnCours }} en cours</span>
                        </div>
                    </div>
                    <div class="stat-icon bg-info bg-opacity-10 text-info">
                        <i class="bi bi-calendar-check-fill"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alerts Row -->
    @if($motardsEnRetard > 0 || $maintenancesEnAttente > 0 || $accidentsNonResolus > 0)
    <div class="row g-3 mb-4">
        @if($motardsEnRetard > 0)
        <div class="col-md-4">
            <div class="card border-0 bg-warning bg-opacity-10">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-warning bg-opacity-25 p-2">
                            <i class="bi bi-exclamation-triangle-fill text-warning fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 text-warning">{{ $motardsEnRetard }}</h5>
                            <small class="text-muted">motard(s) en retard de paiement</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
        @if($maintenancesEnAttente > 0)
        <div class="col-md-4">
            <div class="card border-0 bg-info bg-opacity-10">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-info bg-opacity-25 p-2">
                            <i class="bi bi-tools text-info fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 text-info">{{ $maintenancesEnAttente }}</h5>
                            <small class="text-muted">maintenance(s) en attente</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
        @if($accidentsNonResolus > 0)
        <div class="col-md-4">
            <div class="card border-0 bg-danger bg-opacity-10">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-danger bg-opacity-25 p-2">
                            <i class="bi bi-exclamation-circle-fill text-danger fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 text-danger">{{ $accidentsNonResolus }}</h5>
                            <small class="text-muted">accident(s) non résolu(s)</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
    @endif

    <!-- Recent Activity -->
    <div class="row g-3">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center py-3">
                    <div>
                        <h6 class="mb-0 fw-bold"><i class="bi bi-cash-coin me-2 text-primary"></i>Derniers Versements</h6>
                    </div>
                    <a href="{{ route('admin.versements.index') }}" class="btn btn-sm btn-primary">
                        <i class="bi bi-arrow-right me-1"></i>Voir tout
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">Motard</th>
                                    <th>Moto</th>
                                    <th>Montant</th>
                                    <th>Attendu</th>
                                    <th>Statut</th>
                                    <th class="pe-4">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($derniersVersements as $versement)
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="avatar avatar-sm" style="background: linear-gradient(135deg, var(--tc-primary, #4f46e5), #7c3aed); color: white;">
                                                {{ strtoupper(substr($versement->motard->user->name ?? 'N', 0, 1)) }}
                                            </div>
                                            <div>
                                                <span class="fw-semibold d-block">{{ $versement->motard->user->name ?? 'N/A' }}</span>
                                                <small class="text-muted">{{ $versement->motard->telephone ?? '' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark">
                                            <i class="bi bi-bicycle me-1"></i>{{ $versement->moto->plaque_immatriculation ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-success">{{ number_format($versement->montant) }}</span>
                                        <small class="text-muted">FC</small>
                                    </td>
                                    <td class="text-muted">{{ number_format($versement->montant_attendu) }} FC</td>
                                    <td>
                                        @php
                                            $statutConfig = [
                                                'paye' => ['color' => 'success', 'icon' => 'check-circle'],
                                                'payé' => ['color' => 'success', 'icon' => 'check-circle'],
                                                'partiel' => ['color' => 'warning', 'icon' => 'dash-circle'],
                                                'en_retard' => ['color' => 'danger', 'icon' => 'x-circle'],
                                                'non_paye' => ['color' => 'secondary', 'icon' => 'clock'],
                                                'non_payé' => ['color' => 'secondary', 'icon' => 'clock'],
                                            ];
                                            $config = $statutConfig[$versement->statut] ?? ['color' => 'secondary', 'icon' => 'question-circle'];
                                        @endphp
                                        <span class="badge badge-soft-{{ $config['color'] }}">
                                            <i class="bi bi-{{ $config['icon'] }} me-1"></i>{{ ucfirst(str_replace('_', ' ', $versement->statut)) }}
                                        </span>
                                    </td>
                                    <td class="pe-4">
                                        <small class="text-muted">
                                            <i class="bi bi-calendar3 me-1"></i>{{ $versement->date_versement?->format('d/m/Y') ?? $versement->created_at->format('d/m/Y') }}
                                        </small>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <div class="empty-state">
                                            <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                                            <p class="text-muted mt-3 mb-0">Aucun versement enregistré</p>
                                            <small class="text-muted">Les versements apparaîtront ici une fois enregistrés</small>
                                        </div>
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
</div>
