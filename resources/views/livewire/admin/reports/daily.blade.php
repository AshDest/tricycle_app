<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-calendar-day me-2 text-primary"></i>Rapport Journalier
            </h4>
            <p class="text-muted mb-0">Statistiques du {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</p>
        </div>
        <div class="d-flex gap-2">
            <input type="date" wire:model.live="date" class="form-control" style="width: auto;">
            <button wire:click="$refresh" class="btn btn-outline-primary">
                <i class="bi bi-arrow-clockwise"></i>
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card bg-primary bg-opacity-10 border-0 h-100">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted small mb-1">Total Versé</h6>
                            <h4 class="fw-bold text-primary mb-0">{{ number_format($totalVersements) }} FC</h4>
                        </div>
                        <div class="bg-primary bg-opacity-25 rounded-circle p-2">
                            <i class="bi bi-cash-stack text-primary fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card bg-info bg-opacity-10 border-0 h-100">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted small mb-1">Total Attendu</h6>
                            <h4 class="fw-bold text-info mb-0">{{ number_format($totalAttendu) }} FC</h4>
                        </div>
                        <div class="bg-info bg-opacity-25 rounded-circle p-2">
                            <i class="bi bi-graph-up text-info fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card bg-success bg-opacity-10 border-0 h-100">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted small mb-1">Total Collecté</h6>
                            <h4 class="fw-bold text-success mb-0">{{ number_format($totalCollecte) }} FC</h4>
                        </div>
                        <div class="bg-success bg-opacity-25 rounded-circle p-2">
                            <i class="bi bi-box-arrow-in-down text-success fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card bg-danger bg-opacity-10 border-0 h-100">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted small mb-1">Motards en Retard</h6>
                            <h4 class="fw-bold text-danger mb-0">{{ $motardsEnRetard }}</h4>
                        </div>
                        <div class="bg-danger bg-opacity-25 rounded-circle p-2">
                            <i class="bi bi-exclamation-triangle text-danger fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Versements par statut -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-pie-chart me-2 text-primary"></i>Versements par Statut</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="p-3 bg-success bg-opacity-10 rounded text-center">
                                <h3 class="fw-bold text-success mb-1">{{ $versementsParStatut['paye'] ?? 0 }}</h3>
                                <small class="text-muted">Payés</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-warning bg-opacity-10 rounded text-center">
                                <h3 class="fw-bold text-warning mb-1">{{ $versementsParStatut['partiel'] ?? 0 }}</h3>
                                <small class="text-muted">Partiels</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-danger bg-opacity-10 rounded text-center">
                                <h3 class="fw-bold text-danger mb-1">{{ $versementsParStatut['retard'] ?? 0 }}</h3>
                                <small class="text-muted">En retard</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-secondary bg-opacity-10 rounded text-center">
                                <h3 class="fw-bold text-secondary mb-1">{{ $versementsParStatut['non_effectue'] ?? 0 }}</h3>
                                <small class="text-muted">Non effectués</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tournées -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-calendar-event me-2 text-info"></i>Tournées du Jour</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-around text-center">
                        <div>
                            <div class="display-4 fw-bold text-primary">{{ $tourneesJour }}</div>
                            <small class="text-muted">Programmées</small>
                        </div>
                        <div class="border-start"></div>
                        <div>
                            <div class="display-4 fw-bold text-success">{{ $tourneesTerminees }}</div>
                            <small class="text-muted">Terminées</small>
                        </div>
                        <div class="border-start"></div>
                        <div>
                            <div class="display-4 fw-bold text-warning">{{ $tourneesJour - $tourneesTerminees }}</div>
                            <small class="text-muted">En cours</small>
                        </div>
                    </div>
                    @if($tourneesJour > 0)
                    <div class="progress mt-4" style="height: 10px;">
                        <div class="progress-bar bg-success" style="width: {{ ($tourneesTerminees / $tourneesJour) * 100 }}%"></div>
                    </div>
                    <small class="text-muted">{{ round(($tourneesTerminees / $tourneesJour) * 100) }}% complété</small>
                    @endif
                </div>
            </div>
        </div>

        <!-- Écart Journalier -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-graph-down me-2 text-warning"></i>Écart Journalier</h6>
                </div>
                <div class="card-body text-center">
                    @php
                        $ecart = $totalVersements - $totalAttendu;
                        $pourcentage = $totalAttendu > 0 ? round(($totalVersements / $totalAttendu) * 100) : 0;
                    @endphp
                    <div class="display-5 fw-bold {{ $ecart >= 0 ? 'text-success' : 'text-danger' }} mb-2">
                        {{ $ecart >= 0 ? '+' : '' }}{{ number_format($ecart) }} FC
                    </div>
                    <div class="progress mb-3" style="height: 20px;">
                        <div class="progress-bar {{ $pourcentage >= 100 ? 'bg-success' : ($pourcentage >= 80 ? 'bg-warning' : 'bg-danger') }}"
                             style="width: {{ min($pourcentage, 100) }}%">
                            {{ $pourcentage }}%
                        </div>
                    </div>
                    <p class="text-muted mb-0">Taux de recouvrement</p>
                </div>
            </div>
        </div>

        <!-- Top Motards -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-trophy me-2 text-warning"></i>Top Motards du Jour</h6>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @forelse($topMotards as $index => $item)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge {{ $index === 0 ? 'bg-warning' : 'bg-light text-dark' }} rounded-pill">{{ $index + 1 }}</span>
                                <span>{{ $item->motard?->user?->name ?? 'N/A' }}</span>
                            </div>
                            <span class="fw-bold text-success">{{ number_format($item->total) }} FC</span>
                        </li>
                        @empty
                        <li class="list-group-item text-center text-muted py-4">
                            Aucun versement pour cette date
                        </li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
