<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-cash-coin me-2 text-success"></i>Tableau de Bord Caissier
            </h4>
            <p class="text-muted mb-0">Gestion des versements - {{ $caissier->nom_point_collecte ?? 'Point de collecte' }}</p>
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
            <div class="card stat-card h-100 border-start border-success border-4">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small text-uppercase fw-semibold mb-2">Collecté Aujourd'hui</p>
                        <h3 class="fw-bold text-success mb-1">{{ number_format($totalVersementsAujourdhui ?? 0) }}</h3>
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
                        <p class="text-muted small text-uppercase fw-semibold mb-2">Motards Servis</p>
                        <h3 class="fw-bold mb-1">{{ $nombreVersements ?? 0 }}</h3>
                        <small class="text-muted">aujourd'hui</small>
                    </div>
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-people"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card h-100 border-start border-warning border-4">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small text-uppercase fw-semibold mb-2">Solde en Caisse</p>
                        <h3 class="fw-bold text-warning mb-1">{{ number_format($soldeActuel ?? 0) }}</h3>
                        <small class="text-muted">FCFA (non collecté)</small>
                    </div>
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                        <i class="bi bi-wallet2"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card h-100 border-start border-info border-4">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small text-uppercase fw-semibold mb-2">Zone</p>
                        <h3 class="fw-bold mb-1 fs-5">{{ $caissier->zone ?? 'N/A' }}</h3>
                        <small class="text-muted">affectation</small>
                    </div>
                    <div class="stat-icon bg-info bg-opacity-10 text-info">
                        <i class="bi bi-geo-alt"></i>
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
                        <div class="col-md-4">
                            <a href="{{ route('cashier.versements.create') }}" class="btn btn-success w-100 py-3">
                                <i class="bi bi-plus-circle fs-4 d-block mb-2"></i>
                                Nouveau Versement
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="{{ route('cashier.versements.index') }}" class="btn btn-outline-primary w-100 py-3">
                                <i class="bi bi-list-check fs-4 d-block mb-2"></i>
                                Liste Versements
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="{{ route('cashier.solde') }}" class="btn btn-outline-info w-100 py-3">
                                <i class="bi bi-wallet2 fs-4 d-block mb-2"></i>
                                Mon Solde
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Versements -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center py-3">
            <h6 class="mb-0 fw-bold"><i class="bi bi-clock-history me-2 text-primary"></i>Versements Récents</h6>
            <a href="{{ route('cashier.versements.index') }}" class="btn btn-sm btn-outline-primary">Voir tout</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">Heure</th>
                            <th>Motard</th>
                            <th>Moto</th>
                            <th>Montant</th>
                            <th class="pe-4">Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($versementsAujourdhui ?? [] as $versement)
                        <tr>
                            <td class="ps-4">
                                <i class="bi bi-clock me-1 text-muted"></i>
                                {{ $versement->heure_versement ?? $versement->created_at?->format('H:i') }}
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar avatar-sm bg-primary bg-opacity-10 text-primary rounded-circle">
                                        {{ strtoupper(substr($versement->motard->user->name ?? 'N', 0, 1)) }}
                                    </div>
                                    <span class="fw-medium">{{ $versement->motard->user->name ?? 'N/A' }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">
                                    {{ $versement->moto->plaque_immatriculation ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="fw-semibold text-success">{{ number_format($versement->montant ?? 0) }} FCFA</td>
                            <td class="pe-4">
                                @if($versement->statut === 'payé')
                                <span class="badge badge-soft-success">
                                    <i class="bi bi-check-circle me-1"></i>Payé
                                </span>
                                @elseif($versement->statut === 'partiellement_payé')
                                <span class="badge badge-soft-warning">
                                    <i class="bi bi-dash-circle me-1"></i>Partiel
                                </span>
                                @else
                                <span class="badge badge-soft-secondary">
                                    {{ ucfirst(str_replace('_', ' ', $versement->statut ?? 'N/A')) }}
                                </span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                <p class="mb-0">Aucun versement aujourd'hui</p>
                                <a href="{{ route('cashier.versements.create') }}" class="btn btn-sm btn-success mt-3">
                                    <i class="bi bi-plus-circle me-1"></i>Enregistrer un versement
                                </a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
