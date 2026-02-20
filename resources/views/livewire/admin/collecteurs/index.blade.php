<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-geo-alt me-2 text-info"></i>Gestion des Collecteurs
            </h4>
            <p class="text-muted mb-0">Agents terrain de ramassage des fonds</p>
        </div>
        <div class="d-flex gap-2">
            <button wire:click="exportPdf" class="btn btn-danger">
                <i class="bi bi-file-pdf me-1"></i> PDF
            </button>
            <a href="{{ route('admin.collecteurs.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>Nouveau Collecteur
            </a>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Rechercher</label>
                    <input type="text" wire:model.live="search" class="form-control" placeholder="Nom, identifiant, téléphone...">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Zone</label>
                    <select wire:model.live="filterZone" class="form-select">
                        <option value="">Toutes les zones</option>
                        @foreach($zones ?? [] as $zone)
                        <option value="{{ $zone }}">{{ $zone }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Statut</label>
                    <select wire:model.live="filterStatut" class="form-select">
                        <option value="">Tous</option>
                        <option value="1">Actifs</option>
                        <option value="0">Inactifs</option>
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

    <!-- Liste -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Collecteur</th>
                            <th>Identifiant</th>
                            <th>Zone</th>
                            <th>Téléphone</th>
                            <th>Tournées ce mois</th>
                            <th>Statut</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($collecteurs ?? [] as $collecteur)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar avatar-sm bg-info bg-opacity-10 text-info rounded-circle">
                                        {{ strtoupper(substr($collecteur->user->name ?? 'C', 0, 1)) }}
                                    </div>
                                    <div>
                                        <span class="fw-medium d-block">{{ $collecteur->user->name ?? 'N/A' }}</span>
                                        <small class="text-muted">{{ $collecteur->user->email ?? '' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td><code>{{ $collecteur->numero_identifiant ?? 'N/A' }}</code></td>
                            <td><span class="badge bg-light text-dark">{{ $collecteur->zone_affectation ?? 'N/A' }}</span></td>
                            <td>{{ $collecteur->telephone ?? 'N/A' }}</td>
                            <td>
                                <span class="fw-semibold">{{ $collecteur->tournees_count ?? 0 }}</span>
                                <small class="text-muted">tournées</small>
                            </td>
                            <td>
                                <span class="badge badge-soft-{{ $collecteur->is_active ? 'success' : 'danger' }}">
                                    {{ $collecteur->is_active ? 'Actif' : 'Inactif' }}
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group">
                                    <a href="{{ route('admin.collecteurs.show', $collecteur) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.collecteurs.edit', $collecteur) }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button wire:click="toggleActive({{ $collecteur->id }})"
                                            class="btn btn-sm btn-outline-{{ $collecteur->is_active ? 'warning' : 'success' }}"
                                            title="{{ $collecteur->is_active ? 'Désactiver' : 'Activer' }}">
                                        <i class="bi bi-{{ $collecteur->is_active ? 'pause' : 'play' }}"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                <p class="mb-0">Aucun collecteur trouvé</p>
                                <a href="{{ route('admin.collecteurs.create') }}" class="btn btn-sm btn-primary mt-3">
                                    <i class="bi bi-plus-lg me-1"></i>Ajouter un collecteur
                                </a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if(($collecteurs ?? collect())->hasPages())
        <div class="card-footer bg-light">
            {{ $collecteurs->links() }}
        </div>
        @endif
    </div>
</div>
