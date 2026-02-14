<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-calendar-day me-2 text-primary"></i>Rapport Quotidien
            </h4>
            <p class="text-muted mb-0">{{ $dateRapport?->translatedFormat('l d F Y') ?? now()->translatedFormat('l d F Y') }}</p>
        </div>
        <div class="d-flex gap-2">
            <input type="date" wire:model.live="selectedDate" class="form-control" style="width: auto;">
            <button class="btn btn-outline-primary" wire:click="exportPdf">
                <i class="bi bi-file-earmark-pdf me-1"></i>Exporter PDF
            </button>
        </div>
    </div>

    <!-- Stats Principales -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card h-100 border-start border-success border-4">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small text-uppercase fw-semibold mb-2">Total Collecté</p>
                        <h3 class="fw-bold text-success mb-1">{{ number_format($totalCollecte ?? 0) }} FC</h3>
                        <small class="text-muted">{{ $nombreVersements ?? 0 }} versements</small>
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
                        <h3 class="fw-bold text-primary mb-1">{{ number_format($totalAttendu ?? 0) }} FC</h3>
                        <small class="text-muted">{{ $motardsActifs ?? 0 }} motards actifs</small>
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
                        <h3 class="fw-bold text-warning mb-1">{{ $tauxCollecte ?? 0 }}%</h3>
                        <small class="text-{{ ($tauxCollecte ?? 0) >= 80 ? 'success' : 'danger' }}">
                            {{ ($tauxCollecte ?? 0) >= 80 ? 'Bon' : 'À améliorer' }}
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
                        <p class="text-muted small text-uppercase fw-semibold mb-2">Arriérés du Jour</p>
                        <h3 class="fw-bold text-danger mb-1">{{ number_format($arrieresDuJour ?? 0) }} FC</h3>
                        <small class="text-muted">{{ $motardsEnRetard ?? 0 }} motards</small>
                    </div>
                    <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphique et Détails -->
    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-bar-chart me-2 text-primary"></i>Versements par Zone</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Zone</th>
                                    <th>Motards</th>
                                    <th>Collecté</th>
                                    <th>Attendu</th>
                                    <th class="pe-4">Taux</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($parZone ?? [] as $zone)
                                <tr>
                                    <td class="ps-4 fw-medium">{{ $zone->zone ?? 'Non définie' }}</td>
                                    <td>{{ $zone->nombre_motards ?? 0 }}</td>
                                    <td class="text-success fw-semibold">{{ number_format($zone->total_collecte ?? 0) }} FC</td>
                                    <td class="text-muted">{{ number_format($zone->total_attendu ?? 0) }} FC</td>
                                    <td class="pe-4">
                                        @php $taux = $zone->total_attendu > 0 ? round(($zone->total_collecte / $zone->total_attendu) * 100) : 0; @endphp
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-grow-1" style="height: 6px;">
                                                <div class="progress-bar bg-{{ $taux >= 80 ? 'success' : ($taux >= 50 ? 'warning' : 'danger') }}" style="width: {{ $taux }}%"></div>
                                            </div>
                                            <span class="small fw-medium">{{ $taux }}%</span>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">Aucune donnée disponible</td>
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
                    <h6 class="mb-0 fw-bold"><i class="bi bi-pie-chart me-2 text-info"></i>Répartition</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center py-3 border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-success">&nbsp;</span>
                            <span>Payés complets</span>
                        </div>
                        <span class="fw-bold">{{ $versementsPayes ?? 0 }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-3 border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-warning">&nbsp;</span>
                            <span>Partiels</span>
                        </div>
                        <span class="fw-bold">{{ $versementsPartiels ?? 0 }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-3 border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-danger">&nbsp;</span>
                            <span>En retard</span>
                        </div>
                        <span class="fw-bold">{{ $versementsEnRetard ?? 0 }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-3">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-secondary">&nbsp;</span>
                            <span>Non effectués</span>
                        </div>
                        <span class="fw-bold">{{ $versementsNonEffectues ?? 0 }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des motards en retard -->
    <div class="card">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold"><i class="bi bi-exclamation-circle me-2 text-danger"></i>Motards en Retard</h6>
            <span class="badge bg-danger">{{ count($motardsRetardListe ?? []) }}</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Motard</th>
                            <th>Zone</th>
                            <th>Montant dû</th>
                            <th>Versé</th>
                            <th class="pe-4">Manquant</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($motardsRetardListe ?? [] as $motard)
                        <tr>
                            <td class="ps-4">
                                <span class="fw-medium">{{ $motard->user->name ?? 'N/A' }}</span>
                                <small class="text-muted d-block">{{ $motard->numero_identifiant ?? '' }}</small>
                            </td>
                            <td><span class="badge bg-light text-dark">{{ $motard->zone ?? 'N/A' }}</span></td>
                            <td>{{ number_format($motard->montant_attendu ?? 0) }} FC</td>
                            <td class="text-success">{{ number_format($motard->montant_verse ?? 0) }} FC</td>
                            <td class="pe-4 text-danger fw-bold">{{ number_format(($motard->montant_attendu ?? 0) - ($motard->montant_verse ?? 0)) }} FC</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                <i class="bi bi-check-circle text-success fs-3 d-block mb-2"></i>
                                Aucun motard en retard aujourd'hui
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
