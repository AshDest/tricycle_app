<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-list-check me-2 text-success"></i>Mes Collectes
            </h4>
            <p class="text-muted mb-0">Historique de toutes vos collectes effectuées</p>
        </div>
        <a href="{{ route('collector.tournee.index') }}" class="btn btn-primary">
            <i class="bi bi-play-circle me-1"></i>Tournée du Jour
        </a>
    </div>

    <!-- Stats -->
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
                    <h4 class="fw-bold text-primary mb-1">{{ $nombreCollectes ?? 0 }}</h4>
                    <small class="text-muted">Collectes totales</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card bg-warning bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="fw-bold text-warning mb-1">{{ $collectesPartielles ?? 0 }}</h4>
                    <small class="text-muted">Partielles</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card bg-danger bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="fw-bold text-danger mb-1">{{ $collectesEnLitige ?? 0 }}</h4>
                    <small class="text-muted">En litige</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Rechercher</label>
                    <input type="text" wire:model.live="search" class="form-control" placeholder="Point de collecte...">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Statut</label>
                    <select wire:model.live="filterStatut" class="form-select">
                        <option value="">Tous</option>
                        <option value="reussie">Réussie</option>
                        <option value="partielle">Partielle</option>
                        <option value="non_realisee">Non réalisée</option>
                        <option value="en_litige">En litige</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Période</label>
                    <select wire:model.live="filterPeriode" class="form-select">
                        <option value="">Tout</option>
                        <option value="today">Aujourd'hui</option>
                        <option value="week">Cette semaine</option>
                        <option value="month">Ce mois</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Date</label>
                    <input type="date" wire:model.live="filterDate" class="form-control">
                </div>
                <div class="col-md-2">
                    <button wire:click="resetFilters" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-x-lg me-1"></i>Réinitialiser
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des collectes -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Date</th>
                            <th>Point de Collecte</th>
                            <th>Zone</th>
                            <th>Montant attendu</th>
                            <th>Montant collecté</th>
                            <th>Écart</th>
                            <th>Statut</th>
                            <th class="text-end pe-4">Détails</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($collectes ?? [] as $collecte)
                        <tr>
                            <td class="ps-4">
                                <span class="fw-medium">{{ $collecte->created_at?->format('d/m/Y') }}</span>
                                <small class="text-muted d-block">{{ $collecte->created_at?->format('H:i') }}</small>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar avatar-sm bg-info bg-opacity-10 text-info rounded-circle">
                                        <i class="bi bi-shop"></i>
                                    </div>
                                    <div>
                                        <span class="fw-medium d-block">{{ $collecte->caissier->nom_point_collecte ?? 'N/A' }}</span>
                                        <small class="text-muted">{{ $collecte->caissier->user->name ?? '' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge bg-light text-dark">{{ $collecte->caissier->zone ?? 'N/A' }}</span></td>
                            <td class="text-muted">{{ number_format($collecte->montant_attendu ?? 0) }} FC</td>
                            <td class="fw-semibold text-success">{{ number_format($collecte->montant_collecte ?? 0) }} FC</td>
                            <td>
                                @php
                                    $ecart = ($collecte->montant_collecte ?? 0) - ($collecte->montant_attendu ?? 0);
                                @endphp
                                <span class="fw-semibold text-{{ $ecart >= 0 ? 'success' : 'danger' }}">
                                    {{ $ecart >= 0 ? '+' : '' }}{{ number_format($ecart) }} FC
                                </span>
                            </td>
                            <td>
                                @php
                                    $statutColors = [
                                        'reussie' => 'success',
                                        'partielle' => 'warning',
                                        'non_realisee' => 'secondary',
                                        'en_litige' => 'danger'
                                    ];
                                @endphp
                                <span class="badge badge-soft-{{ $statutColors[$collecte->statut] ?? 'secondary' }}">
                                    {{ ucfirst(str_replace('_', ' ', $collecte->statut ?? 'N/A')) }}
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <button wire:click="voirDetails({{ $collecte->id }})" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                <p class="mb-0">Aucune collecte trouvée</p>
                                <small>Modifiez vos filtres ou effectuez votre première collecte</small>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if(($collectes ?? collect())->hasPages())
        <div class="card-footer bg-light">
            {{ $collectes->links() }}
        </div>
        @endif
    </div>
</div>
