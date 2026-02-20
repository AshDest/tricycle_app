<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-building me-2 text-warning"></i>Gestion des Propriétaires
            </h4>
            <p class="text-muted mb-0">Bailleurs de motos-tricycles</p>
        </div>
        <div class="d-flex gap-2">
            <button wire:click="exportPdf" class="btn btn-danger">
                <i class="bi bi-file-pdf me-1"></i> PDF
            </button>
            <a href="{{ route('admin.proprietaires.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>Nouveau Propriétaire
            </a>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label small fw-semibold">Rechercher</label>
                    <input type="text" wire:model.live="search" class="form-control" placeholder="Nom, email, téléphone...">
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
                    <label class="form-label small fw-semibold">Motos</label>
                    <select wire:model.live="filterMotos" class="form-select">
                        <option value="">Tous</option>
                        <option value="avec">Avec motos</option>
                        <option value="sans">Sans motos</option>
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

    <!-- Liste des propriétaires -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Propriétaire</th>
                            <th>Contact</th>
                            <th>Motos</th>
                            <th>Total dû</th>
                            <th>Total payé</th>
                            <th>Statut</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($proprietaires ?? [] as $proprietaire)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar avatar-sm bg-warning bg-opacity-10 text-warning rounded-circle">
                                        {{ strtoupper(substr($proprietaire->user->name ?? 'P', 0, 1)) }}
                                    </div>
                                    <div>
                                        <span class="fw-medium d-block">{{ $proprietaire->user->name ?? 'N/A' }}</span>
                                        <small class="text-muted">{{ $proprietaire->numero_identifiant ?? '' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <small class="text-muted d-block">{{ $proprietaire->user->email ?? 'N/A' }}</small>
                                    <small>{{ $proprietaire->telephone ?? 'N/A' }}</small>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">
                                    <i class="bi bi-bicycle me-1"></i>{{ $proprietaire->motos_count ?? 0 }} moto(s)
                                </span>
                            </td>
                            <td class="fw-semibold text-primary">{{ number_format($proprietaire->total_du ?? 0) }} FC</td>
                            <td class="text-success">{{ number_format($proprietaire->total_paye ?? 0) }} FC</td>
                            <td>
                                <span class="badge badge-soft-{{ $proprietaire->is_active ? 'success' : 'danger' }}">
                                    {{ $proprietaire->is_active ? 'Actif' : 'Inactif' }}
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group">
                                    <a href="{{ route('admin.proprietaires.show', $proprietaire) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.proprietaires.edit', $proprietaire) }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button wire:click="toggleActive({{ $proprietaire->id }})"
                                            class="btn btn-sm btn-outline-{{ $proprietaire->is_active ? 'warning' : 'success' }}"
                                            title="{{ $proprietaire->is_active ? 'Désactiver' : 'Activer' }}">
                                        <i class="bi bi-{{ $proprietaire->is_active ? 'pause' : 'play' }}"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-building fs-1 d-block mb-3"></i>
                                <p class="mb-0">Aucun propriétaire trouvé</p>
                                <a href="{{ route('admin.proprietaires.create') }}" class="btn btn-sm btn-primary mt-3">
                                    <i class="bi bi-plus-lg me-1"></i>Ajouter un propriétaire
                                </a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if(($proprietaires ?? collect())->hasPages())
        <div class="card-footer bg-light">
            {{ $proprietaires->links() }}
        </div>
        @endif
    </div>
</div>
