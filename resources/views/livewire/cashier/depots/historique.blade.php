<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-clock-history me-2 text-info"></i>Historique des Dépôts
            </h4>
            <p class="text-muted mb-0">Tous vos dépôts effectués auprès des collecteurs</p>
        </div>
        <a href="{{ route('cashier.depot') }}" class="btn btn-primary">
            <i class="bi bi-box-arrow-up me-1"></i>Nouveau Dépôt
        </a>
    </div>

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-sm-4">
            <div class="card bg-primary bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="fw-bold text-primary mb-1">{{ number_format($totalDepose) }} FC</h4>
                    <small class="text-muted">Total déposé</small>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="card bg-success bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="fw-bold text-success mb-1">{{ number_format($totalValide) }} FC</h4>
                    <small class="text-muted">Validé</small>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="card bg-warning bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="fw-bold text-warning mb-1">{{ $enAttente }}</h4>
                    <small class="text-muted">En attente</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Date</label>
                    <input type="date" wire:model.live="filterDate" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Statut</label>
                    <select wire:model.live="filterStatut" class="form-select">
                        <option value="">Tous</option>
                        <option value="valide">Validé</option>
                        <option value="en_attente">En attente</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <button wire:click="$set('filterDate', '')" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-x-lg me-1"></i>Réinitialiser
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Date</th>
                            <th>Collecteur</th>
                            <th>Montant attendu</th>
                            <th>Montant déposé</th>
                            <th>Écart</th>
                            <th class="text-end pe-4">Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($depots as $depot)
                        <tr>
                            <td class="ps-4">
                                <span class="fw-medium">{{ $depot->created_at?->format('d/m/Y') }}</span>
                                <small class="text-muted d-block">{{ $depot->created_at?->format('H:i') }}</small>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar avatar-sm bg-info bg-opacity-10 text-info rounded-circle">
                                        {{ strtoupper(substr($depot->tournee?->collecteur?->user?->name ?? 'C', 0, 1)) }}
                                    </div>
                                    <span>{{ $depot->tournee?->collecteur?->user?->name ?? 'N/A' }}</span>
                                </div>
                            </td>
                            <td class="text-muted">{{ number_format($depot->montant_attendu ?? 0) }} FC</td>
                            <td class="fw-bold text-success">{{ number_format($depot->montant_collecte ?? 0) }} FC</td>
                            <td>
                                @php
                                    $ecart = ($depot->montant_collecte ?? 0) - ($depot->montant_attendu ?? 0);
                                @endphp
                                <span class="badge {{ $ecart >= 0 ? 'bg-success' : 'bg-danger' }}">
                                    {{ $ecart >= 0 ? '+' : '' }}{{ number_format($ecart) }} FC
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                @if($depot->valide_par_collecteur)
                                <span class="badge badge-soft-success">
                                    <i class="bi bi-check-circle me-1"></i>Validé
                                </span>
                                <small class="text-muted d-block">{{ $depot->valide_collecteur_at?->format('d/m H:i') }}</small>
                                @else
                                <span class="badge badge-soft-warning">En attente</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                <p class="mb-0">Aucun dépôt trouvé</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($depots->hasPages())
        <div class="card-footer bg-light">
            {{ $depots->links() }}
        </div>
        @endif
    </div>
</div>
