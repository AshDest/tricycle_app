<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-calendar-week me-2 text-info"></i>Rapport Hebdomadaire
            </h4>
            <p class="text-muted mb-0">
                Semaine du {{ $startOfWeek->format('d/m/Y') }} au {{ $endOfWeek->format('d/m/Y') }}
            </p>
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
                            @if($comparaisonSemainePrecedente != 0)
                            <small class="{{ $comparaisonSemainePrecedente >= 0 ? 'text-success' : 'text-danger' }}">
                                <i class="bi bi-{{ $comparaisonSemainePrecedente >= 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                                {{ abs($comparaisonSemainePrecedente) }}% vs sem. préc.
                            </small>
                            @endif
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
                            <h6 class="text-muted small mb-1">Paiements Propriétaires</h6>
                            <h4 class="fw-bold text-success mb-0">{{ number_format($totalPaiements) }} FC</h4>
                        </div>
                        <div class="bg-success bg-opacity-25 rounded-circle p-2">
                            <i class="bi bi-wallet2 text-success fs-4"></i>
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
        <!-- Graphique par jour -->
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-bar-chart me-2 text-primary"></i>Versements par Jour</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Jour</th>
                                    <th>Date</th>
                                    <th>Versé</th>
                                    <th>Attendu</th>
                                    <th>Écart</th>
                                    <th>%</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($versementsParJour as $jour)
                                <tr>
                                    <td class="fw-medium">{{ $jour['jour'] }}</td>
                                    <td class="text-muted">{{ $jour['date'] }}</td>
                                    <td class="fw-bold text-primary">{{ number_format($jour['montant']) }} FC</td>
                                    <td>{{ number_format($jour['attendu']) }} FC</td>
                                    <td>
                                        @php $ecartJour = $jour['montant'] - $jour['attendu']; @endphp
                                        <span class="badge {{ $ecartJour >= 0 ? 'bg-success' : 'bg-danger' }}">
                                            {{ $ecartJour >= 0 ? '+' : '' }}{{ number_format($ecartJour) }}
                                        </span>
                                    </td>
                                    <td>
                                        @php $pctJour = $jour['attendu'] > 0 ? round(($jour['montant'] / $jour['attendu']) * 100) : 0; @endphp
                                        <span class="badge {{ $pctJour >= 100 ? 'bg-success' : ($pctJour >= 80 ? 'bg-warning' : 'bg-danger') }}">
                                            {{ $pctJour }}%
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-light">
                                <tr class="fw-bold">
                                    <td colspan="2">TOTAL</td>
                                    <td class="text-primary">{{ number_format($totalVersements) }} FC</td>
                                    <td>{{ number_format($totalAttendu) }} FC</td>
                                    <td>
                                        @php $ecartTotal = $totalVersements - $totalAttendu; @endphp
                                        <span class="badge {{ $ecartTotal >= 0 ? 'bg-success' : 'bg-danger' }}">
                                            {{ $ecartTotal >= 0 ? '+' : '' }}{{ number_format($ecartTotal) }}
                                        </span>
                                    </td>
                                    <td>
                                        @php $pctTotal = $totalAttendu > 0 ? round(($totalVersements / $totalAttendu) * 100) : 0; @endphp
                                        <span class="badge {{ $pctTotal >= 100 ? 'bg-success' : ($pctTotal >= 80 ? 'bg-warning' : 'bg-danger') }}">
                                            {{ $pctTotal }}%
                                        </span>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Résumé -->
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-pie-chart me-2 text-success"></i>Taux de Recouvrement</h6>
                </div>
                <div class="card-body text-center">
                    @php
                        $tauxRecouvrement = $totalAttendu > 0 ? round(($totalVersements / $totalAttendu) * 100) : 0;
                    @endphp
                    <div class="display-4 fw-bold {{ $tauxRecouvrement >= 100 ? 'text-success' : ($tauxRecouvrement >= 80 ? 'text-warning' : 'text-danger') }}">
                        {{ $tauxRecouvrement }}%
                    </div>
                    <div class="progress my-3" style="height: 15px;">
                        <div class="progress-bar {{ $tauxRecouvrement >= 100 ? 'bg-success' : ($tauxRecouvrement >= 80 ? 'bg-warning' : 'bg-danger') }}"
                             style="width: {{ min($tauxRecouvrement, 100) }}%"></div>
                    </div>
                    <p class="text-muted small mb-0">Objectif: 100%</p>
                </div>
            </div>

            <div class="card">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-cash-coin me-2 text-warning"></i>Écart Semaine</h6>
                </div>
                <div class="card-body text-center">
                    @php $ecart = $totalVersements - $totalAttendu; @endphp
                    <div class="display-5 fw-bold {{ $ecart >= 0 ? 'text-success' : 'text-danger' }}">
                        {{ $ecart >= 0 ? '+' : '' }}{{ number_format($ecart) }} FC
                    </div>
                    <p class="text-muted small mb-0 mt-2">
                        @if($ecart >= 0)
                        Objectif atteint !
                        @else
                        Manque à gagner
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
