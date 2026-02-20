<div>

    <!-- Flash Messages -->
    @if(session()->has('success'))
    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
        <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Page Header -->
    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <h4>Liste des Motards</h4>
            <p class="text-muted small mb-0">G&eacute;rer tous les motards de la flotte</p>
        </div>
        <div class="d-flex gap-2">
            <button wire:click="exportPdf" class="btn btn-danger">
                <i class="bi bi-file-pdf me-1"></i> PDF
            </button>
            <a href="{{ route('admin.motards.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Ajouter un motard
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-3">
        <div class="card-body py-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Rechercher par nom, email, identifiant...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select wire:model.live="filterZone" class="form-select form-select-sm">
                        <option value="">Toutes les zones</option>
                        @foreach($zones as $zone)
                            <option value="{{ $zone }}">{{ $zone }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select wire:model.live="filterStatut" class="form-select form-select-sm">
                        <option value="">Tous les statuts</option>
                        <option value="actif">Actifs</option>
                        <option value="inactif">Inactifs</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select wire:model.live="perPage" class="form-select form-select-sm">
                        <option value="10">10 par page</option>
                        <option value="15">15 par page</option>
                        <option value="25">25 par page</option>
                        <option value="50">50 par page</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Motard</th>
                            <th>Identifiant</th>
                            <th>Zone</th>
                            <th>Moto</th>
                            <th>Statut</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($motards as $motard)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="user-avatar-sm bg-primary bg-opacity-10 text-primary">
                                        {{ strtoupper(substr($motard->user->name ?? 'N', 0, 1)) }}
                                    </div>
                                    <div>
                                        <span class="fw-medium d-block">{{ $motard->user->name ?? 'N/A' }}</span>
                                        <small class="text-muted">{{ $motard->user->email ?? '' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td><code>{{ $motard->numero_identifiant }}</code></td>
                            <td>{{ $motard->zone_affectation ?? '-' }}</td>
                            <td>
                                @if($motard->motoActuelle)
                                    <span class="badge bg-info bg-opacity-10 text-info">{{ $motard->motoActuelle->plaque_immatriculation }}</span>
                                @else
                                    <span class="text-muted small">Non assign&eacute;</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $motard->is_active ? 'bg-success' : 'bg-danger' }}" style="cursor:pointer;" wire:click="toggleActive({{ $motard->id }})">
                                    {{ $motard->is_active ? 'Actif' : 'Inactif' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.motards.show', $motard) }}" class="btn btn-outline-secondary" title="Voir">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.motards.edit', $motard) }}" class="btn btn-outline-secondary" title="Modifier">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button wire:click="delete({{ $motard->id }})" wire:confirm="Supprimer ce motard ?" class="btn btn-outline-danger" title="Supprimer">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="bi bi-people fs-3 d-block mb-2"></i>
                                Aucun motard trouv&eacute;
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($motards->hasPages())
        <div class="card-footer d-flex justify-content-between align-items-center">
            <small class="text-muted">Affichage de {{ $motards->firstItem() }} &agrave; {{ $motards->lastItem() }} sur {{ $motards->total() }}</small>
            {{ $motards->links() }}
        </div>
        @endif
    </div>
</div>
