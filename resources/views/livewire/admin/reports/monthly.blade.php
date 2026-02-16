<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-calendar-month me-2 text-success"></i>Rapport Mensuel
            </h4>
            <p class="text-muted mb-0">
                {{ \Carbon\Carbon::create($year, $month, 1)->translatedFormat('F Y') }}
            </p>
        </div>
        <div class="d-flex gap-2">
            <select wire:model.live="month" class="form-select" style="width: auto;">
                @for($m = 1; $m <= 12; $m++)
                <option value="{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}">
                    {{ \Carbon\Carbon::create(null, $m, 1)->translatedFormat('F') }}
                </option>
                @endfor
            </select>
            <select wire:model.live="year" class="form-select" style="width: auto;">
                @for($y = now()->year; $y >= now()->year - 5; $y--)
                <option value="{{ $y }}">{{ $y }}</option>
                @endfor
            </select>
            <button wire:click="$refresh" class="btn btn-outline-primary">
                <i class="bi bi-arrow-clockwise"></i>
            </button>
        </div>
    </div>

    <!-- Stats Cards Row 1 -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card bg-primary bg-opacity-10 border-0 h-100">
                <div class="card-body py-3">
                    <h6 class="text-muted small mb-1">Total Versements</h6>
                    <h4 class="fw-bold text-primary mb-0">{{ number_format($totalVersements) }} FC</h4>
                    <small class="text-muted">sur {{ number_format($totalAttendu) }} FC attendus</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card bg-success bg-opacity-10 border-0 h-100">
                <div class="card-body py-3">
                    <h6 class="text-muted small mb-1">Total Collecté</h6>
                    <h4 class="fw-bold text-success mb-0">{{ number_format($totalCollecte) }} FC</h4>
                    <small class="text-muted">des caissiers</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card bg-info bg-opacity-10 border-0 h-100">
                <div class="card-body py-3">
                    <h6 class="text-muted small mb-1">Paiements Propriétaires</h6>
                    <h4 class="fw-bold text-info mb-0">{{ number_format($totalPaiements) }} FC</h4>
                    <small class="text-muted">validés</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card {{ $tauxRecouvrement >= 100 ? 'bg-success' : ($tauxRecouvrement >= 80 ? 'bg-warning' : 'bg-danger') }} bg-opacity-10 border-0 h-100">
                <div class="card-body py-3">
                    <h6 class="text-muted small mb-1">Taux de Recouvrement</h6>
                    <h4 class="fw-bold {{ $tauxRecouvrement >= 100 ? 'text-success' : ($tauxRecouvrement >= 80 ? 'text-warning' : 'text-danger') }} mb-0">
                        {{ $tauxRecouvrement }}%
                    </h4>
                    <small class="text-muted">objectif: 100%</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards Row 2 -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 h-100">
                <div class="card-body py-3 d-flex align-items-center gap-3">
                    <div class="bg-primary bg-opacity-10 rounded-circle p-2">
                        <i class="bi bi-people text-primary fs-4"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0">{{ $motardsActifs }}</h5>
                        <small class="text-muted">Motards actifs</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 h-100">
                <div class="card-body py-3 d-flex align-items-center gap-3">
                    <div class="bg-info bg-opacity-10 rounded-circle p-2">
                        <i class="bi bi-bicycle text-info fs-4"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0">{{ $motosActives }}</h5>
                        <small class="text-muted">Motos actives</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 h-100">
                <div class="card-body py-3 d-flex align-items-center gap-3">
                    <div class="bg-warning bg-opacity-10 rounded-circle p-2">
                        <i class="bi bi-tools text-warning fs-4"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0">{{ number_format($totalMaintenances) }} FC</h5>
                        <small class="text-muted">Maintenances</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 h-100">
                <div class="card-body py-3 d-flex align-items-center gap-3">
                    <div class="bg-danger bg-opacity-10 rounded-circle p-2">
                        <i class="bi bi-exclamation-triangle text-danger fs-4"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0">{{ $totalAccidents }}</h5>
                        <small class="text-muted">Accidents</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Versements par semaine -->
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-bar-chart me-2 text-primary"></i>Versements par Semaine</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Semaine</th>
                                    <th>Période</th>
                                    <th>Versé</th>
                                    <th>Attendu</th>
                                    <th>Écart</th>
                                    <th>%</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($versementsParSemaine as $semaine)
                                <tr>
                                    <td class="fw-medium">{{ $semaine['semaine'] }}</td>
                                    <td class="text-muted small">{{ $semaine['periode'] }}</td>
                                    <td class="fw-bold text-primary">{{ number_format($semaine['montant']) }} FC</td>
                                    <td>{{ number_format($semaine['attendu']) }} FC</td>
                                    <td>
                                        @php $ecart = $semaine['montant'] - $semaine['attendu']; @endphp
                                        <span class="badge {{ $ecart >= 0 ? 'bg-success' : 'bg-danger' }}">
                                            {{ $ecart >= 0 ? '+' : '' }}{{ number_format($ecart) }}
                                        </span>
                                    </td>
                                    <td>
                                        @php $pct = $semaine['attendu'] > 0 ? round(($semaine['montant'] / $semaine['attendu']) * 100) : 0; @endphp
                                        <span class="badge {{ $pct >= 100 ? 'bg-success' : ($pct >= 80 ? 'bg-warning' : 'bg-danger') }}">
                                            {{ $pct }}%
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top 10 Motards -->
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-trophy me-2 text-warning"></i>Top 10 Motards</h6>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @forelse($topMotards as $index => $item)
                        <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge {{ $index < 3 ? 'bg-warning' : 'bg-light text-dark' }} rounded-pill">
                                    {{ $index + 1 }}
                                </span>
                                <div>
                                    <span class="d-block">{{ $item->motard?->user?->name ?? 'N/A' }}</span>
                                    <small class="text-muted">{{ $item->nb_versements }} versements</small>
                                </div>
                            </div>
                            <span class="fw-bold text-success">{{ number_format($item->total) }} FC</span>
                        </li>
                        @empty
                        <li class="list-group-item text-center text-muted py-4">
                            Aucune donnée pour ce mois
                        </li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        <!-- Résumé financier -->
        <div class="col-12">
            <div class="card">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-calculator me-2 text-info"></i>Résumé Financier du Mois</h6>
                </div>
                <div class="card-body">
                    <div class="row g-4 text-center">
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded">
                                <h6 class="text-muted small mb-1">Recettes Versements</h6>
                                <h4 class="fw-bold text-primary mb-0">{{ number_format($totalVersements) }} FC</h4>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded">
                                <h6 class="text-muted small mb-1">Paiements Propriétaires</h6>
                                <h4 class="fw-bold text-danger mb-0">- {{ number_format($totalPaiements) }} FC</h4>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded">
                                <h6 class="text-muted small mb-1">Coûts Maintenances</h6>
                                <h4 class="fw-bold text-warning mb-0">- {{ number_format($totalMaintenances) }} FC</h4>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 {{ ($totalVersements - $totalPaiements - $totalMaintenances) >= 0 ? 'bg-success' : 'bg-danger' }} bg-opacity-10 rounded">
                                <h6 class="text-muted small mb-1">Solde Net</h6>
                                <h4 class="fw-bold {{ ($totalVersements - $totalPaiements - $totalMaintenances) >= 0 ? 'text-success' : 'text-danger' }} mb-0">
                                    {{ number_format($totalVersements - $totalPaiements - $totalMaintenances) }} FC
                                </h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
