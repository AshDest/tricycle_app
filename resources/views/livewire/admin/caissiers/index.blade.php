<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-person-badge me-2 text-primary"></i>Gestion des Caissiers
            </h4>
            <p class="text-muted mb-0">Liste des caissiers et points de collecte</p>
        </div>
        <div class="d-flex gap-2">
            <button wire:click="exportPdf" class="btn btn-danger">
                <i class="bi bi-file-pdf me-1"></i>PDF
            </button>
            <a href="{{ route('admin.caissiers.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>Nouveau Caissier
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card bg-primary bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="fw-bold text-primary mb-1">{{ $caissiers->total() }}</h4>
                    <small class="text-muted">Total Caissiers</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card bg-success bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="fw-bold text-success mb-1">{{ $caissiers->where('is_active', true)->count() }}</h4>
                    <small class="text-muted">Actifs</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card bg-warning bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="fw-bold text-warning mb-1">{{ $caissiers->where('is_active', false)->count() }}</h4>
                    <small class="text-muted">Inactifs</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card bg-info bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="fw-bold text-info mb-1">{{ number_format($caissiers->sum('solde_actuel')) }} FC</h4>
                    <small class="text-muted">Total Solde en Caisse</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des caissiers -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Caissier</th>
                            <th>Point de Collecte</th>
                            <th>Zone</th>
                            <th>Téléphone</th>
                            <th>Solde</th>
                            <th>Statut</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($caissiers as $caissier)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar avatar-sm bg-primary bg-opacity-10 text-primary rounded-circle">
                                        {{ strtoupper(substr($caissier->user->name ?? 'C', 0, 1)) }}
                                    </div>
                                    <div>
                                        <span class="fw-medium d-block">{{ $caissier->user->name ?? 'N/A' }}</span>
                                        <small class="text-muted">{{ $caissier->numero_identifiant }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $caissier->nom_point_collecte ?? 'N/A' }}</td>
                            <td><span class="badge bg-light text-dark">{{ $caissier->zone ?? 'N/A' }}</span></td>
                            <td>{{ $caissier->telephone ?? 'N/A' }}</td>
                            <td class="fw-semibold text-success">{{ number_format($caissier->solde_actuel ?? 0) }} FC</td>
                            <td>
                                <span class="badge badge-soft-{{ $caissier->is_active ? 'success' : 'danger' }}"
                                      style="cursor: pointer;"
                                      wire:click="toggleActive({{ $caissier->id }})"
                                      title="Cliquer pour changer le statut">
                                    {{ $caissier->is_active ? 'Actif' : 'Inactif' }}
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group">
                                    <a href="{{ route('admin.caissiers.show', $caissier) }}" class="btn btn-sm btn-outline-primary" title="Voir">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.caissiers.edit', $caissier) }}" class="btn btn-sm btn-outline-warning" title="Modifier">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button wire:click="delete({{ $caissier->id }})"
                                            wire:confirm="Êtes-vous sûr de vouloir supprimer ce caissier ?"
                                            class="btn btn-sm btn-outline-danger" title="Supprimer">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-person-x fs-1 d-block mb-3"></i>
                                <p class="mb-0">Aucun caissier trouvé</p>
                                <a href="{{ route('admin.caissiers.create') }}" class="btn btn-sm btn-primary mt-3">
                                    <i class="bi bi-plus-lg me-1"></i>Ajouter un caissier
                                </a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($caissiers->hasPages())
        <div class="card-footer bg-light">
            {{ $caissiers->links() }}
        </div>
        @endif
    </div>
</div>
