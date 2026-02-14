<div>
    @section('title', 'Tableau de Bord')

    <!-- Page Header -->
    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <h4>Tableau de Bord</h4>
            <p class="text-muted small mb-0">Vue d'ensemble de votre flotte</p>
        </div>
        <div>
            <span class="badge bg-light text-dark"><i class="bi bi-calendar3 me-1"></i>{{ now()->translatedFormat('l d F Y') }}</span>
        </div>
    </div>

    <!-- Stats Row 1: Fleet -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card bg-white">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small mb-1">Total Motards</p>
                        <h3 class="fw-bold mb-0">{{ number_format($totalMotards) }}</h3>
                        <small class="text-success"><i class="bi bi-person-check"></i> {{ $motardsActifs }} actifs</small>
                    </div>
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-people"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card bg-white">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small mb-1">Total Motos</p>
                        <h3 class="fw-bold mb-0">{{ number_format($totalMotos) }}</h3>
                        <small class="text-success"><i class="bi bi-check-circle"></i> {{ $motosActives }} actives</small>
                    </div>
                    <div class="stat-icon bg-info bg-opacity-10 text-info">
                        <i class="bi bi-bicycle"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card bg-white">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small mb-1">Propri&eacute;taires</p>
                        <h3 class="fw-bold mb-0">{{ number_format($totalProprietaires) }}</h3>
                        <small class="text-muted"><i class="bi bi-building"></i> enregistr&eacute;s</small>
                    </div>
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                        <i class="bi bi-building"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card bg-white">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small mb-1">Utilisateurs</p>
                        <h3 class="fw-bold mb-0">{{ number_format($totalUsers) }}</h3>
                        <small class="text-muted"><i class="bi bi-people-fill"></i> au total</small>
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
            <div class="card stat-card bg-white">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small mb-1">Versements Aujourd'hui</p>
                        <h3 class="fw-bold mb-0">{{ number_format($versementsAujourdhui) }} <small class="fw-normal text-muted fs-6">FCFA</small></h3>
                        <small class="text-muted">Attendu: {{ number_format($versementsAttenduAujourdhui) }}</small>
                    </div>
                    <div class="stat-icon bg-success bg-opacity-10 text-success">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card bg-white">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small mb-1">Versements ce Mois</p>
                        <h3 class="fw-bold mb-0">{{ number_format($versementsCeMois) }} <small class="fw-normal text-muted fs-6">FCFA</small></h3>
                    </div>
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-graph-up"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card bg-white">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small mb-1">Arri&eacute;r&eacute;s Cumul&eacute;s</p>
                        <h3 class="fw-bold mb-0 text-danger">{{ number_format($arrieresCumules) }} <small class="fw-normal fs-6">FCFA</small></h3>
                    </div>
                    <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card bg-white">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small mb-1">Tourn&eacute;es Aujourd'hui</p>
                        <h3 class="fw-bold mb-0">{{ $tourneesAujourdhui }}</h3>
                        <small>
                            <span class="text-success">{{ $tourneesTerminees }} termin&eacute;es</span>
                            <span class="text-muted mx-1">|</span>
                            <span class="text-warning">{{ $tourneesEnCours }} en cours</span>
                        </small>
                    </div>
                    <div class="stat-icon bg-info bg-opacity-10 text-info">
                        <i class="bi bi-calendar-check"></i>
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
            <div class="alert alert-warning d-flex align-items-center gap-2 mb-0" role="alert">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <div><strong>{{ $motardsEnRetard }}</strong> motard(s) en retard de paiement</div>
            </div>
        </div>
        @endif
        @if($maintenancesEnAttente > 0)
        <div class="col-md-4">
            <div class="alert alert-info d-flex align-items-center gap-2 mb-0" role="alert">
                <i class="bi bi-tools"></i>
                <div><strong>{{ $maintenancesEnAttente }}</strong> maintenance(s) en attente</div>
            </div>
        </div>
        @endif
        @if($accidentsNonResolus > 0)
        <div class="col-md-4">
            <div class="alert alert-danger d-flex align-items-center gap-2 mb-0" role="alert">
                <i class="bi bi-exclamation-circle-fill"></i>
                <div><strong>{{ $accidentsNonResolus }}</strong> accident(s) non r&eacute;solu(s)</div>
            </div>
        </div>
        @endif
    </div>
    @endif

    <!-- Recent Activity -->
    <div class="row g-3">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-semibold">Derniers Versements</h6>
                    <a href="{{ route('admin.versements.index') }}" class="btn btn-sm btn-outline-primary">Voir tout</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Motard</th>
                                    <th>Moto</th>
                                    <th>Montant</th>
                                    <th>Attendu</th>
                                    <th>Statut</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($derniersVersements as $versement)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="user-avatar-sm bg-primary bg-opacity-10 text-primary">
                                                {{ strtoupper(substr($versement->motard->user->name ?? 'N', 0, 1)) }}
                                            </div>
                                            <span class="fw-medium">{{ $versement->motard->user->name ?? 'N/A' }}</span>
                                        </div>
                                    </td>
                                    <td>{{ $versement->moto->plaque_immatriculation ?? 'N/A' }}</td>
                                    <td class="fw-semibold">{{ number_format($versement->montant) }} FCFA</td>
                                    <td class="text-muted">{{ number_format($versement->montant_attendu) }} FCFA</td>
                                    <td>
                                        @php
                                            $statutColors = [
                                                'paye' => 'success', 'payé' => 'success',
                                                'partiel' => 'warning',
                                                'en_retard' => 'danger',
                                                'non_paye' => 'secondary', 'non_payé' => 'secondary',
                                            ];
                                            $color = $statutColors[$versement->statut] ?? 'secondary';
                                        @endphp
                                        <span class="badge bg-{{ $color }}">{{ ucfirst(str_replace('_', ' ', $versement->statut)) }}</span>
                                    </td>
                                    <td class="text-muted small">{{ $versement->date_versement?->format('d/m/Y') ?? $versement->created_at->format('d/m/Y') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                        Aucun versement enregistr&eacute;
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
