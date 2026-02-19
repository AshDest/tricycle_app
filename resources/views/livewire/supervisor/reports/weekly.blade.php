<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-calendar-week me-2 text-info"></i>Rapport Hebdomadaire
            </h4>
            <p class="text-muted mb-0">Semaine du {{ $stats['debutSemaine'] ?? 'N/A' }} au {{ $stats['finSemaine'] ?? 'N/A' }}</p>
        </div>
        <div class="d-flex gap-2">
            <input type="date" wire:model.live="week_of" class="form-control" style="width: auto;">
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
                        <small class="text-muted">objectif de la semaine</small>
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
                        <p class="text-muted small text-uppercase fw-semibold mb-2">Arriérés</p>
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

    <!-- Tableau par jour -->
    <div class="card">
        <div class="card-header py-3">
            <h6 class="mb-0 fw-bold"><i class="bi bi-bar-chart me-2 text-primary"></i>Détails par Jour</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Jour</th>
                            <th>Date</th>
                            <th class="text-end">Collecté</th>
                            <th class="text-end">Attendu</th>
                            <th class="text-center">Versements</th>
                            <th class="pe-4 text-end">Taux</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($stats['parJour'] ?? [] as $jour)
                        <tr>
                            <td class="ps-4 fw-medium">{{ $jour['jour'] }}</td>
                            <td class="text-muted">{{ $jour['date'] }}</td>
                            <td class="text-end fw-semibold text-success">{{ number_format($jour['collecte']) }} FC</td>
                            <td class="text-end text-muted">{{ number_format($jour['attendu']) }} FC</td>
                            <td class="text-center">
                                <span class="badge bg-primary">{{ $jour['count'] }}</span>
                            </td>
                            <td class="pe-4 text-end">
                                @php
                                    $taux = $jour['attendu'] > 0 ? round(($jour['collecte'] / $jour['attendu']) * 100, 1) : 0;
                                @endphp
                                <span class="badge badge-soft-{{ $taux >= 80 ? 'success' : ($taux >= 50 ? 'warning' : 'danger') }}">
                                    {{ $taux }}%
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                <p class="mb-0">Aucune donnée pour cette semaine</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-light fw-bold">
                        <tr>
                            <td class="ps-4" colspan="2">Total</td>
                            <td class="text-end text-success">{{ number_format($stats['totalCollecte'] ?? 0) }} FC</td>
                            <td class="text-end">{{ number_format($stats['totalAttendu'] ?? 0) }} FC</td>
                            <td class="text-center">{{ $stats['nombreVersements'] ?? 0 }}</td>
                            <td class="pe-4 text-end">
                                <span class="badge badge-soft-{{ ($stats['tauxRecouvrement'] ?? 0) >= 80 ? 'success' : 'warning' }}">
                                    {{ $stats['tauxRecouvrement'] ?? 0 }}%
                                </span>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

