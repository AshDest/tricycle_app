<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-droplet me-2 text-info"></i>Gestion des Laveurs
            </h4>
            <p class="text-muted mb-0">Liste des laveurs du service de lavage</p>
        </div>
        <a href="{{ route('admin.cleaners.create') }}" class="btn btn-info">
            <i class="bi bi-plus-circle me-1"></i>Nouveau Laveur
        </a>
    </div>

    <!-- Statistiques -->
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="card bg-info bg-opacity-10 border-0">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted">Total Laveurs</small>
                            <h4 class="mb-0 fw-bold">{{ $stats['total'] }}</h4>
                        </div>
                        <i class="bi bi-people fs-1 text-info opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card bg-success bg-opacity-10 border-0">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted">Actifs</small>
                            <h4 class="mb-0 fw-bold">{{ $stats['actifs'] }}</h4>
                        </div>
                        <i class="bi bi-check-circle fs-1 text-success opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card bg-primary bg-opacity-10 border-0">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted">Lavages du jour</small>
                            <h4 class="mb-0 fw-bold">{{ $stats['lavages_jour'] }}</h4>
                        </div>
                        <i class="bi bi-droplet-half fs-1 text-primary opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card bg-warning bg-opacity-10 border-0">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted">Part OKAMI (jour)</small>
                            <h4 class="mb-0 fw-bold">{{ number_format($stats['part_okami_jour']) }} FC</h4>
                        </div>
                        <i class="bi bi-percent fs-1 text-warning opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Rechercher par nom, email, identifiant...">
                </div>
                <div class="col-md-4">
                    <select wire:model.live="filterStatut" class="form-select">
                        <option value="">Tous les statuts</option>
                        <option value="actif">Actifs</option>
                        <option value="inactif">Inactifs</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select wire:model.live="perPage" class="form-select">
                        <option value="15">15 / page</option>
                        <option value="30">30 / page</option>
                        <option value="50">50 / page</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Messages flash -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Liste des laveurs -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>ID</th>
                            <th>Laveur</th>
                            <th>Contact</th>
                            <th>Zone</th>
                            <th>Lavages</th>
                            <th>Recettes</th>
                            <th>Statut</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cleaners as $cleaner)
                        <tr>
                            <td>
                                <span class="badge bg-info">{{ $cleaner->identifiant }}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-info bg-opacity-10 rounded-circle p-2 me-2">
                                        <i class="bi bi-person text-info"></i>
                                    </div>
                                    <div>
                                        <strong>{{ $cleaner->user->name ?? 'N/A' }}</strong>
                                        <small class="d-block text-muted">{{ $cleaner->user->email ?? '' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <small>{{ $cleaner->telephone ?? '-' }}</small>
                            </td>
                            <td>{{ $cleaner->zone ?? '-' }}</td>
                            <td>
                                <span class="badge bg-light text-dark">{{ $cleaner->lavages_count ?? 0 }}</span>
                            </td>
                            <td>
                                <strong class="text-success">{{ number_format($cleaner->total_recettes ?? 0) }} FC</strong>
                            </td>
                            <td>
                                <span class="badge bg-{{ $cleaner->is_active ? 'success' : 'danger' }}">
                                    {{ $cleaner->is_active ? 'Actif' : 'Inactif' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.cleaners.show', $cleaner) }}" class="btn btn-outline-info" title="Voir">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.cleaners.edit', $cleaner) }}" class="btn btn-outline-primary" title="Modifier">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button wire:click="toggleStatut({{ $cleaner->id }})" class="btn btn-outline-{{ $cleaner->is_active ? 'warning' : 'success' }}" title="{{ $cleaner->is_active ? 'Désactiver' : 'Activer' }}">
                                        <i class="bi bi-{{ $cleaner->is_active ? 'pause' : 'play' }}"></i>
                                    </button>
                                    <button wire:click="supprimer({{ $cleaner->id }})" wire:confirm="Êtes-vous sûr de vouloir supprimer ce laveur?" class="btn btn-outline-danger" title="Supprimer">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                Aucun laveur trouvé
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($cleaners->hasPages())
        <div class="card-footer bg-white">
            {{ $cleaners->links() }}
        </div>
        @endif
    </div>
</div>

