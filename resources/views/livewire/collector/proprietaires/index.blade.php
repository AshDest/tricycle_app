<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-people me-2 text-info"></i>Solde des Propriétaires
            </h4>
            <p class="text-muted mb-0">Visualiser le solde disponible de chaque propriétaire</p>
        </div>
        <button wire:click="$refresh" class="btn btn-outline-primary">
            <i class="bi bi-arrow-clockwise me-1"></i>Actualiser
        </button>
    </div>

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6">
            <div class="card bg-success bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="fw-bold text-success mb-1">{{ number_format($totalSoldeDisponible) }} FC</h4>
                    <small class="text-muted">Total solde disponible</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="card bg-primary bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="fw-bold text-primary mb-1">{{ $proprietairesAvecSolde }}</h4>
                    <small class="text-muted">Propriétaires avec solde</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Rechercher</label>
                    <input type="text" wire:model.live="search" class="form-control" placeholder="Nom, téléphone...">
                </div>
                <div class="col-md-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" wire:model.live="filterAvecSolde" id="filterSolde">
                        <label class="form-check-label" for="filterSolde">
                            Uniquement avec solde > 0
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alerte importante -->
    <div class="alert alert-warning mb-4">
        <i class="bi bi-exclamation-triangle me-2"></i>
        <strong>Rappel:</strong> Le montant d'un paiement ne peut jamais dépasser le solde disponible du propriétaire.
        Le solde = Versements payés des motos - Paiements déjà effectués.
    </div>

    <!-- Liste des propriétaires -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Propriétaire</th>
                            <th>Motos</th>
                            <th>Total Versements</th>
                            <th>Total Paiements</th>
                            <th class="text-end pe-4">Solde Disponible</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($proprietaires as $proprietaire)
                        <tr class="{{ $proprietaire->solde_disponible > 0 ? '' : 'table-light' }}">
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar avatar-sm bg-primary bg-opacity-10 text-primary rounded-circle">
                                        {{ strtoupper(substr($proprietaire->user->name ?? 'P', 0, 1)) }}
                                    </div>
                                    <div>
                                        <span class="fw-medium d-block">{{ $proprietaire->user->name ?? $proprietaire->raison_sociale }}</span>
                                        <small class="text-muted">{{ $proprietaire->telephone ?? '' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">
                                    <i class="bi bi-bicycle me-1"></i>{{ $proprietaire->motos_actives ?? 0 }} / {{ $proprietaire->motos->count() }}
                                </span>
                            </td>
                            <td class="text-success fw-semibold">{{ number_format($proprietaire->total_versements ?? 0) }} FC</td>
                            <td class="text-muted">{{ number_format($proprietaire->total_paiements ?? 0) }} FC</td>
                            <td class="text-end pe-4">
                                @if($proprietaire->solde_disponible > 0)
                                <span class="badge bg-success fs-6 px-3 py-2">
                                    {{ number_format($proprietaire->solde_disponible) }} FC
                                </span>
                                @else
                                <span class="badge bg-secondary">0 FC</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                <p class="mb-0">Aucun propriétaire trouvé</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
