<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-geo-alt me-2 text-info"></i>Gestion des Zones
            </h4>
            <p class="text-muted mb-0">Zones géographiques de collecte</p>
        </div>
        <a href="{{ route('admin.zones.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i>Nouvelle Zone
        </a>
    </div>

    <!-- Filtres -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label small fw-semibold">Rechercher</label>
                    <input type="text" wire:model.live="search" class="form-control" placeholder="Nom de zone, communes...">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Statut</label>
                    <select wire:model.live="filterStatut" class="form-select">
                        <option value="">Tous</option>
                        <option value="1">Actives</option>
                        <option value="0">Inactives</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Par page</label>
                    <select wire:model.live="perPage" class="form-select">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button wire:click="resetFilters" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des zones -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Nom de la Zone</th>
                            <th>Description</th>
                            <th>Communes</th>
                            <th>Statut</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($zones ?? [] as $zone)
                        <tr>
                            <td class="ps-4">
                                <span class="fw-medium">{{ $zone->nom }}</span>
                            </td>
                            <td class="text-muted">{{ Str::limit($zone->description ?? '-', 50) }}</td>
                            <td>
                                <span class="badge bg-light text-dark">{{ $zone->communes ?? '-' }}</span>
                            </td>
                            <td>
                                <span class="badge badge-soft-{{ $zone->is_active ? 'success' : 'secondary' }}">
                                    {{ $zone->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group">
                                    <a href="{{ route('admin.zones.edit', $zone) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button wire:click="toggleActive({{ $zone->id }})"
                                            class="btn btn-sm btn-outline-{{ $zone->is_active ? 'warning' : 'success' }}"
                                            title="{{ $zone->is_active ? 'Désactiver' : 'Activer' }}">
                                        <i class="bi bi-{{ $zone->is_active ? 'pause' : 'play' }}"></i>
                                    </button>
                                    <button wire:click="delete({{ $zone->id }})"
                                            wire:confirm="Êtes-vous sûr de vouloir supprimer cette zone ?"
                                            class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="bi bi-geo-alt fs-1 d-block mb-3"></i>
                                <p class="mb-0">Aucune zone trouvée</p>
                                <a href="{{ route('admin.zones.create') }}" class="btn btn-sm btn-primary mt-3">
                                    <i class="bi bi-plus-lg me-1"></i>Ajouter une zone
                                </a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if(($zones ?? collect())->hasPages())
        <div class="card-footer bg-light">
            {{ $zones->links() }}
        </div>
        @endif
    </div>
</div>
