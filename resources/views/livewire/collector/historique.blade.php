<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-clock-history me-2 text-info"></i>Historique de mes Tournées
            </h4>
            <p class="text-muted mb-0">Consultez toutes vos tournées passées</p>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Période</label>
                    <select wire:model.live="filterPeriode" class="form-select">
                        <option value="">Tout</option>
                        <option value="week">Cette semaine</option>
                        <option value="month">Ce mois</option>
                        <option value="year">Cette année</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Statut</label>
                    <select wire:model.live="filterStatut" class="form-select">
                        <option value="">Tous</option>
                        <option value="terminee">Terminées</option>
                        <option value="annulee">Annulées</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Du</label>
                    <input type="date" wire:model.live="dateFrom" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Au</label>
                    <input type="date" wire:model.live="dateTo" class="form-control">
                </div>
                <div class="col-md-2">
                    <button wire:click="resetFilters" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats résumé -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card bg-success bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="fw-bold text-success mb-1">{{ number_format($totalCollecte ?? 0) }} FC</h4>
                    <small class="text-muted">Total collecté</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card bg-primary bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="fw-bold text-primary mb-1">{{ $nombreTournees ?? 0 }}</h4>
                    <small class="text-muted">Tournées effectuées</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card bg-info bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="fw-bold text-info mb-1">{{ $nombreCollectes ?? 0 }}</h4>
                    <small class="text-muted">Collectes réalisées</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card bg-warning bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="fw-bold text-warning mb-1">{{ number_format($moyenneParTournee ?? 0) }} FC</h4>
                    <small class="text-muted">Moyenne par tournée</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des tournées -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Date</th>
                            <th>Zone</th>
                            <th>Caissiers visités</th>
                            <th>Montant collecté</th>
                            <th>Statut</th>
                            <th class="text-end pe-4">Détails</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tournees ?? [] as $tournee)
                        <tr>
                            <td class="ps-4">
                                <span class="fw-medium">{{ $tournee->date?->format('d/m/Y') }}</span>
                                <small class="text-muted d-block">{{ $tournee->date?->translatedFormat('l') }}</small>
                            </td>
                            <td><span class="badge bg-light text-dark">{{ $tournee->zone ?? 'N/A' }}</span></td>
                            <td>
                                <span class="fw-semibold">{{ $tournee->collectes_count ?? 0 }}</span>
                                <small class="text-muted">caissiers</small>
                            </td>
                            <td class="fw-semibold text-success">{{ number_format($tournee->total_collecte ?? 0) }} FC</td>
                            <td>
                                @php
                                    $statutColors = [
                                        'terminee' => 'success',
                                        'annulee' => 'danger',
                                    ];
                                @endphp
                                <span class="badge badge-soft-{{ $statutColors[$tournee->statut] ?? 'secondary' }}">
                                    {{ ucfirst(str_replace('_', ' ', $tournee->statut ?? 'N/A')) }}
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <button wire:click="voirDetails({{ $tournee->id }})" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-calendar-x fs-1 d-block mb-3"></i>
                                <p class="mb-0">Aucune tournée dans l'historique</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if(($tournees ?? collect())->hasPages())
        <div class="card-footer bg-light">
            {{ $tournees->links() }}
        </div>
        @endif
    </div>
</div>
