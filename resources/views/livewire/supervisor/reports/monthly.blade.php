<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-calendar-month me-2 text-success"></i>Rapport Mensuel
            </h4>
            <p class="text-muted mb-0">{{ $stats['mois'] ?? 'N/A' }}</p>
        </div>
        <div class="d-flex gap-2">
            <select wire:model.live="month" class="form-select" style="width: auto;">
                @for($m = 1; $m <= 12; $m++)
                    <option value="{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}">{{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}</option>
                @endfor
            </select>
            <select wire:model.live="year" class="form-select" style="width: auto;">
                @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endfor
            </select>
            <div class="dropdown">
                <button class="btn btn-outline-success dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-download me-1"></i>Exporter
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item" href="#" wire:click.prevent="export">
                            <i class="bi bi-filetype-csv me-2 text-success"></i>Export CSV
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="#" wire:click.prevent="exportPdf">
                            <i class="bi bi-file-earmark-pdf me-2 text-danger"></i>Export PDF
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Stats Principales -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card h-100 border-start border-success border-4">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small text-uppercase fw-semibold mb-2">Total Collecté</p>
                        <h3 class="fw-bold text-success mb-1">{{ number_format($stats['totalCollecte'] ?? 0) }} FC</h3>
                        <small class="text-muted">{{ $stats['nombreVersements'] ?? 0 }} versements</small>
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
                        <p class="text-muted small text-uppercase fw-semibold mb-2">Total Attendu</p>
                        <h3 class="fw-bold text-primary mb-1">{{ number_format($stats['totalAttendu'] ?? 0) }} FC</h3>
                        <small class="text-muted">objectif du mois</small>
                    </div>
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-bullseye"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card h-100 border-start border-warning border-4">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small text-uppercase fw-semibold mb-2">Taux de Collecte</p>
                        <h3 class="fw-bold text-warning mb-1">{{ $stats['tauxRecouvrement'] ?? 0 }}%</h3>
                        <small class="text-{{ ($stats['tauxRecouvrement'] ?? 0) >= 80 ? 'success' : 'danger' }}">
                            {{ ($stats['tauxRecouvrement'] ?? 0) >= 80 ? 'Bon' : 'À améliorer' }}
                        </small>
                    </div>
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                        <i class="bi bi-percent"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card h-100 border-start border-danger border-4">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small text-uppercase fw-semibold mb-2">Arriérés Cumulés</p>
                        <h3 class="fw-bold text-danger mb-1">{{ number_format($stats['arrieres'] ?? 0) }} FC</h3>
                        <small class="text-muted">{{ $stats['versementsEnRetard'] ?? 0 }} en retard</small>
                    </div>
                    <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Répartition des statuts -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card bg-success bg-opacity-10 border-0 text-center py-3">
                <h4 class="mb-0 fw-bold text-success">{{ $stats['versementsPayes'] ?? 0 }}</h4>
                <small class="text-muted">Payés</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-warning bg-opacity-10 border-0 text-center py-3">
                <h4 class="mb-0 fw-bold text-warning">{{ $stats['versementsPartiels'] ?? 0 }}</h4>
                <small class="text-muted">Partiels</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-danger bg-opacity-10 border-0 text-center py-3">
                <h4 class="mb-0 fw-bold text-danger">{{ $stats['versementsEnRetard'] ?? 0 }}</h4>
                <small class="text-muted">En Retard</small>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Tableau par semaine -->
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-calendar3-week me-2 text-primary"></i>Détails par Semaine</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Semaine</th>
                                    <th>Période</th>
                                    <th class="text-end">Collecté</th>
                                    <th class="text-end">Attendu</th>
                                    <th class="pe-4 text-end">Taux</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($stats['parSemaine'] ?? [] as $semaine)
                                <tr>
                                    <td class="ps-4 fw-medium">{{ $semaine['semaine'] }}</td>
                                    <td class="text-muted">{{ $semaine['debut'] }} - {{ $semaine['fin'] }}</td>
                                    <td class="text-end fw-semibold text-success">{{ number_format($semaine['collecte']) }} FC</td>
                                    <td class="text-end text-muted">{{ number_format($semaine['attendu']) }} FC</td>
                                    <td class="pe-4 text-end">
                                        @php
                                            $taux = $semaine['attendu'] > 0 ? round(($semaine['collecte'] / $semaine['attendu']) * 100, 1) : 0;
                                        @endphp
                                        <span class="badge badge-soft-{{ $taux >= 80 ? 'success' : ($taux >= 50 ? 'warning' : 'danger') }}">
                                            {{ $taux }}%
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">Aucune donnée</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Motards -->
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-trophy me-2 text-warning"></i>Top 10 Motards</h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($stats['topMotards'] ?? [] as $index => $motard)
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-{{ $index < 3 ? 'warning' : 'secondary' }}">{{ $index + 1 }}</span>
                                <span class="small">{{ $motard->user->name ?? 'N/A' }}</span>
                            </div>
                            <span class="fw-semibold text-success small">{{ number_format($motard->versements_sum_montant ?? 0) }} FC</span>
                        </div>
                        @empty
                        <div class="list-group-item text-center text-muted py-4">Aucune donnée</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
