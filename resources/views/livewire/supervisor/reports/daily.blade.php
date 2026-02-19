<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-calendar-day me-2 text-primary"></i>Rapport Quotidien
            </h4>
            <p class="text-muted mb-0">{{ \Carbon\Carbon::parse($date)->translatedFormat('l d F Y') }}</p>
        </div>
        <div class="d-flex gap-2">
            <input type="date" wire:model.live="date" class="form-control" style="width: auto;">
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
                        <small class="text-muted">objectif du jour</small>
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

    <!-- Liste des versements du jour -->
    <div class="card">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold"><i class="bi bi-list-ul me-2 text-primary"></i>Versements du Jour</h6>
            <span class="badge bg-primary">{{ $stats['nombreVersements'] ?? 0 }} total</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Motard</th>
                            <th>Moto</th>
                            <th class="text-end">Versé</th>
                            <th class="text-end">Attendu</th>
                            <th>Statut</th>
                            <th>Caissier</th>
                            <th class="pe-4">Heure</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($stats['derniersVersements'] ?? [] as $versement)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar avatar-sm bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                        {{ strtoupper(substr($versement->motard->user->name ?? 'M', 0, 1)) }}
                                    </div>
                                    <div>
                                        <span class="fw-medium d-block small">{{ $versement->motard->user->name ?? 'N/A' }}</span>
                                        <small class="text-muted">{{ $versement->motard->numero_identifiant ?? '' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">{{ $versement->moto->plaque_immatriculation ?? 'N/A' }}</span>
                            </td>
                            <td class="text-end fw-semibold">{{ number_format($versement->montant ?? 0) }} FC</td>
                            <td class="text-end text-muted">{{ number_format($versement->montant_attendu ?? 0) }} FC</td>
                            <td>
                                @php
                                    $statutColors = [
                                        'paye' => 'success',
                                        'en_attente' => 'warning',
                                        'en_retard' => 'danger',
                                        'partiel' => 'info',
                                    ];
                                @endphp
                                <span class="badge badge-soft-{{ $statutColors[$versement->statut] ?? 'secondary' }}">
                                    {{ ucfirst(str_replace('_', ' ', $versement->statut ?? 'N/A')) }}
                                </span>
                            </td>
                            <td class="small text-muted">{{ $versement->caissier->user->name ?? 'N/A' }}</td>
                            <td class="pe-4 small text-muted">{{ $versement->created_at->format('H:i') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                <p class="mb-0">Aucun versement pour cette date</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Motards en retard -->
    @if(($stats['motardsEnRetard'] ?? collect())->count() > 0)
    <div class="card mt-4">
        <div class="card-header py-3">
            <h6 class="mb-0 fw-bold text-danger"><i class="bi bi-exclamation-circle me-2"></i>Motards en Retard</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Motard</th>
                            <th>Contact</th>
                            <th>Zone</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stats['motardsEnRetard'] as $motard)
                        <tr>
                            <td class="ps-4 fw-medium">{{ $motard->user->name ?? 'N/A' }}</td>
                            <td class="text-muted">{{ $motard->telephone ?? 'N/A' }}</td>
                            <td>
                                <span class="badge bg-light text-dark">{{ $motard->zone_affectation ?? 'N/A' }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>

