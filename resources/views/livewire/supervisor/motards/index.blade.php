<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-people me-2 text-primary"></i>Gestion des Motards
            </h4>
            <p class="text-muted mb-0">Liste complète des conducteurs de tricycles</p>
        </div>
        <div class="d-flex gap-2">
            <button wire:click="exportPdf" class="btn btn-danger">
                <i class="bi bi-file-pdf me-1"></i>PDF
            </button>
            <div class="dropdown">
                <button class="btn btn-outline-success dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-download me-1"></i>Exporter
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" wire:click.prevent="export('csv')"><i class="bi bi-filetype-csv me-2"></i>CSV</a></li>
                    <li><a class="dropdown-item" href="#" wire:click.prevent="export('xlsx')"><i class="bi bi-file-earmark-excel me-2"></i>Excel</a></li>
                </ul>
            </div>
            <a href="{{ route('supervisor.motards.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>Nouveau Motard
            </a>
        </div>
    </div>

    <!-- Messages Flash -->
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Statistiques -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card bg-primary bg-opacity-10 border-0">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar bg-primary text-white rounded">
                                <i class="bi bi-people fs-5"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h4 class="mb-0 fw-bold">{{ $stats['total'] }}</h4>
                            <small class="text-muted">Total Motards</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card bg-success bg-opacity-10 border-0">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar bg-success text-white rounded">
                                <i class="bi bi-check-circle fs-5"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h4 class="mb-0 fw-bold">{{ $stats['actifs'] }}</h4>
                            <small class="text-muted">Actifs</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card bg-danger bg-opacity-10 border-0">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar bg-danger text-white rounded">
                                <i class="bi bi-x-circle fs-5"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h4 class="mb-0 fw-bold">{{ $stats['inactifs'] }}</h4>
                            <small class="text-muted">Inactifs</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card bg-info bg-opacity-10 border-0">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar bg-info text-white rounded">
                                <i class="bi bi-bicycle fs-5"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h4 class="mb-0 fw-bold">{{ $stats['avecMoto'] }}</h4>
                            <small class="text-muted">Avec Moto</small>
                        </div>
                    </div>
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
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Nom, email, téléphone...">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Zone</label>
                    <select wire:model.live="filterZone" class="form-select">
                        <option value="">Toutes les zones</option>
                        @foreach($zonesAffectation as $zone)
                            <option value="{{ $zone }}">{{ $zone }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Statut</label>
                    <select wire:model.live="filterStatut" class="form-select">
                        <option value="">Tous</option>
                        <option value="actif">Actifs</option>
                        <option value="inactif">Inactifs</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Date début</label>
                    <input type="date" wire:model.live="dateDebut" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Date fin</label>
                    <input type="date" wire:model.live="dateFin" class="form-control">
                </div>
                <div class="col-md-1">
                    <button wire:click="resetFilters" class="btn btn-outline-secondary w-100" title="Réinitialiser">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des motards -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Motard</th>
                            <th>Contact</th>
                            <th>Zone</th>
                            <th>Moto Assignée</th>
                            <th>Statut</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($motards as $motard)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar avatar-sm bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                        {{ strtoupper(substr($motard->user->name ?? 'M', 0, 1)) }}
                                    </div>
                                    <div>
                                        <span class="fw-medium d-block">{{ $motard->user->name ?? 'N/A' }}</span>
                                        <small class="text-muted">{{ $motard->numero_identifiant ?? '' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <small class="text-muted d-block">{{ $motard->user->email ?? 'N/A' }}</small>
                                    <small><i class="bi bi-telephone me-1"></i>{{ $motard->telephone ?? 'N/A' }}</small>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">
                                    <i class="bi bi-geo-alt me-1"></i>{{ $motard->zone_affectation ?? 'Non assigné' }}
                                </span>
                            </td>
                            <td>
                                @if($motard->motoActuelle)
                                    <span class="badge bg-info bg-opacity-10 text-info">
                                        <i class="bi bi-bicycle me-1"></i>{{ $motard->motoActuelle->plaque_immatriculation }}
                                    </span>
                                @else
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary">Aucune</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-soft-{{ $motard->is_active ? 'success' : 'danger' }}">
                                    {{ $motard->is_active ? 'Actif' : 'Inactif' }}
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                @if($confirmingDelete === $motard->id)
                                    <div class="btn-group">
                                        <button wire:click="delete({{ $motard->id }})" class="btn btn-sm btn-danger">
                                            <i class="bi bi-check"></i> Confirmer
                                        </button>
                                        <button wire:click="cancelDelete" class="btn btn-sm btn-secondary">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </div>
                                @else
                                    <div class="btn-group">
                                        <a href="{{ route('supervisor.motards.show', $motard) }}" class="btn btn-sm btn-outline-info" title="Voir">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('supervisor.motards.edit', $motard) }}" class="btn btn-sm btn-outline-primary" title="Modifier">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button wire:click="toggleActive({{ $motard->id }})"
                                                class="btn btn-sm btn-outline-{{ $motard->is_active ? 'warning' : 'success' }}"
                                                title="{{ $motard->is_active ? 'Désactiver' : 'Activer' }}">
                                            <i class="bi bi-{{ $motard->is_active ? 'pause' : 'play' }}"></i>
                                        </button>
                                        <button wire:click="confirmDelete({{ $motard->id }})" class="btn btn-sm btn-outline-danger" title="Supprimer">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-people fs-1 d-block mb-3"></i>
                                <p class="mb-0">Aucun motard trouvé</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($motards->hasPages())
        <div class="card-footer bg-light">
            {{ $motards->links() }}
        </div>
        @endif
    </div>
</div>
