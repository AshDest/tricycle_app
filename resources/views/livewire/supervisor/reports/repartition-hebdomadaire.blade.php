<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-pie-chart me-2 text-primary"></i>Répartition Hebdomadaire
            </h4>
            <p class="text-muted mb-0">Résumé des recettes hebdomadaires</p>
        </div>
        <button wire:click="exportPdf" class="btn btn-danger" wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="exportPdf">
                <i class="bi bi-file-pdf me-1"></i>Exporter PDF
            </span>
            <span wire:loading wire:target="exportPdf">
                <span class="spinner-border spinner-border-sm me-1"></span>Génération...
            </span>
        </button>
    </div>

    <!-- Info système -->
    <div class="alert alert-info mb-4">
        <div class="d-flex align-items-start gap-3">
            <i class="bi bi-info-circle fs-4"></i>
            <div>
                <h6 class="fw-bold mb-1">Système de recettes hebdomadaires</h6>
                <p class="mb-0 small">
                    <strong>Semaine = {{ $constantes['jours_semaine'] }} jours</strong> de travail |
                    Tous les versements vont dans une caisse unique
                </p>
            </div>
        </div>
    </div>

    <!-- Sélection de semaine -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row align-items-end">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Sélectionner une semaine</label>
                    <select wire:model.live="semaineSelectionnee" class="form-select">
                        @foreach($semaines as $semaine)
                        <option value="{{ $semaine['index'] }}">{{ $semaine['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <div class="alert alert-light mb-0 py-2">
                        <i class="bi bi-calendar-week me-2"></i>
                        Période: <strong>{{ $resume['periode']['debut'] }} - {{ $resume['periode']['fin'] }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Résumé Global -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card bg-primary bg-opacity-10 border-0 h-100">
                <div class="card-body text-center py-4">
                    <i class="bi bi-bicycle fs-2 text-primary mb-2"></i>
                    <h4 class="fw-bold text-primary mb-1">{{ $resume['nb_motos_actives'] }}</h4>
                    <small class="text-muted">Motos Actives</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info bg-opacity-10 border-0 h-100">
                <div class="card-body text-center py-4">
                    <i class="bi bi-bullseye fs-2 text-info mb-2"></i>
                    <h4 class="fw-bold text-info mb-1">{{ number_format($resume['total_attendu']) }} FC</h4>
                    <small class="text-muted">Total Attendu</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success bg-opacity-10 border-0 h-100">
                <div class="card-body text-center py-4">
                    <i class="bi bi-cash-stack fs-2 text-success mb-2"></i>
                    <h4 class="fw-bold text-success mb-1">{{ number_format($resume['total_verse']) }} FC</h4>
                    <small class="text-muted">Total Versé</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-{{ $resume['ecart'] >= 0 ? 'success' : 'danger' }} bg-opacity-10 border-0 h-100">
                <div class="card-body text-center py-4">
                    <i class="bi bi-graph-{{ $resume['ecart'] >= 0 ? 'up' : 'down' }}-arrow fs-2 text-{{ $resume['ecart'] >= 0 ? 'success' : 'danger' }} mb-2"></i>
                    <h4 class="fw-bold text-{{ $resume['ecart'] >= 0 ? 'success' : 'danger' }} mb-1">{{ $resume['taux_recouvrement'] }}%</h4>
                    <small class="text-muted">Taux Recouvrement</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Écart -->
    <div class="row g-4 mb-4">
        <div class="col-md-6 mx-auto">
            <div class="card border-{{ ($resume['ecart'] ?? 0) >= 0 ? 'success' : 'danger' }} h-100">
                <div class="card-header bg-{{ ($resume['ecart'] ?? 0) >= 0 ? 'success' : 'danger' }} bg-opacity-10 py-3">
                    <h6 class="mb-0 fw-bold text-{{ ($resume['ecart'] ?? 0) >= 0 ? 'success' : 'danger' }}">
                        <i class="bi bi-calculator me-2"></i>Écart (Versé - Attendu)
                    </h6>
                </div>
                <div class="card-body text-center">
                    <h3 class="fw-bold text-{{ ($resume['ecart'] ?? 0) >= 0 ? 'success' : 'danger' }}">
                        {{ ($resume['ecart'] ?? 0) >= 0 ? '+' : '' }}{{ number_format($resume['ecart'] ?? 0) }} FC
                    </h3>
                    <p class="text-muted small mb-0">Taux de recouvrement: {{ $resume['taux_recouvrement'] }}%</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Détails par Propriétaire -->
    <div class="card">
        <div class="card-header py-3">
            <h6 class="mb-0 fw-bold"><i class="bi bi-list-ul me-2 text-primary"></i>Détails par Propriétaire</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Propriétaire</th>
                            <th class="text-center">Motos</th>
                            <th class="text-end">Attendu</th>
                            <th class="text-end">Versé</th>
                            <th class="text-end">Part Propriétaire</th>
                            <th class="text-end">Part OKAMI</th>
                            <th class="text-center pe-4">Écart</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($detailsProprietaires as $detail)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar avatar-sm bg-warning bg-opacity-10 text-warning rounded">
                                        {{ strtoupper(substr($detail['proprietaire_nom'], 0, 1)) }}
                                    </div>
                                    <span class="fw-medium">{{ $detail['proprietaire_nom'] }}</span>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark">{{ $detail['nb_motos'] }}</span>
                            </td>
                            <td class="text-end">{{ number_format($detail['total_attendu']) }} FC</td>
                            <td class="text-end fw-semibold text-success">{{ number_format($detail['total_verse']) }} FC</td>
                            <td class="text-end text-warning fw-semibold">{{ number_format($detail['total_part_proprietaire']) }} FC</td>
                            <td class="text-end text-info fw-semibold">{{ number_format($detail['total_part_okami']) }} FC</td>
                            <td class="text-center pe-4">
                                @php
                                    $ecart = $detail['ecart'];
                                @endphp
                                <span class="badge badge-soft-{{ $ecart >= 0 ? 'success' : 'danger' }}">
                                    {{ $ecart >= 0 ? '+' : '' }}{{ number_format($ecart) }} FC
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                <p class="mb-0">Aucune donnée pour cette période</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if(count($detailsProprietaires) > 0)
                    <tfoot class="bg-light fw-bold">
                        <tr>
                            <td class="ps-4">TOTAL</td>
                            <td class="text-center">{{ $resume['nb_motos_actives'] }}</td>
                            <td class="text-end">{{ number_format($resume['total_attendu']) }} FC</td>
                            <td class="text-end text-success">{{ number_format($resume['total_verse']) }} FC</td>
                            <td class="text-end text-warning">{{ number_format($resume['repartition_verse']['part_proprietaires']) }} FC</td>
                            <td class="text-end text-info">{{ number_format($resume['repartition_verse']['part_okami']) }} FC</td>
                            <td class="text-center pe-4">
                                <span class="badge badge-soft-{{ $resume['ecart'] >= 0 ? 'success' : 'danger' }}">
                                    {{ $resume['ecart'] >= 0 ? '+' : '' }}{{ number_format($resume['ecart']) }} FC
                                </span>
                            </td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>

