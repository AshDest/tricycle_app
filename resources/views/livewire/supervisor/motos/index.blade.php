<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-bicycle me-2 text-info"></i>Gestion des Motos
            </h4>
            <p class="text-muted mb-0">Liste complète des motos-tricycles</p>
        </div>
        <div class="d-flex gap-2">
            <div class="dropdown">
                <button class="btn btn-outline-success dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-download me-1"></i>Exporter
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" wire:click.prevent="export('csv')"><i class="bi bi-filetype-csv me-2"></i>CSV</a></li>
                </ul>
            </div>
            <a href="{{ route('supervisor.motos.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>Nouvelle Moto
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
            <div class="card bg-info bg-opacity-10 border-0">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar bg-info text-white rounded">
                                <i class="bi bi-bicycle fs-5"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h4 class="mb-0 fw-bold">{{ $stats['total'] }}</h4>
                            <small class="text-muted">Total Motos</small>
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
                            <h4 class="mb-0 fw-bold">{{ $stats['actives'] }}</h4>
                            <small class="text-muted">Actives</small>
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
                            <h4 class="mb-0 fw-bold">{{ $stats['inactives'] }}</h4>
                            <small class="text-muted">Inactives</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card bg-primary bg-opacity-10 border-0">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar bg-primary text-white rounded">
                                <i class="bi bi-person-check fs-5"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h4 class="mb-0 fw-bold">{{ $stats['assignees'] }}</h4>
                            <small class="text-muted">Assignées</small>
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
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Plaque, châssis, propriétaire...">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Statut</label>
                    <select wire:model.live="filterStatut" class="form-select">
                        <option value="">Tous</option>
                        <option value="actif">Actives</option>
                        <option value="inactif">Inactives</option>
                        <option value="en_maintenance">En maintenance</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Propriétaire</label>
                    <select wire:model.live="filterProprietaire" class="form-select">
                        <option value="">Tous</option>
                        @foreach($proprietaires as $prop)
                            <option value="{{ $prop->id }}">{{ $prop->user->name ?? 'N/A' }}</option>
                        @endforeach
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

    <!-- Liste des motos -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Moto</th>
                            <th>Propriétaire</th>
                            <th>Motard Assigné</th>
                            <th>Détails</th>
                            <th>Statut</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($motos as $moto)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar avatar-sm bg-info bg-opacity-10 text-info rounded d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                        <i class="bi bi-bicycle"></i>
                                    </div>
                                    <div>
                                        <span class="fw-medium d-block">{{ $moto->plaque_immatriculation }}</span>
                                        <small class="text-muted">{{ $moto->numero_chassis ?? 'N/A' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="fw-medium">{{ $moto->proprietaire->user->name ?? 'N/A' }}</span>
                            </td>
                            <td>
                                @if($moto->motardActuel)
                                    <span class="badge bg-primary bg-opacity-10 text-primary">
                                        <i class="bi bi-person me-1"></i>{{ $moto->motardActuel->user->name ?? 'N/A' }}
                                    </span>
                                @else
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary">Non assignée</span>
                                @endif
                            </td>
                            <td>
                                <small class="text-muted">
                                    {{ $moto->marque ?? 'N/A' }} {{ $moto->modele ?? '' }}
                                    @if($moto->couleur)
                                        <br>{{ $moto->couleur }}
                                    @endif
                                </small>
                            </td>
                            <td>
                                @php
                                    $statutColors = [
                                        'actif' => 'success',
                                        'inactif' => 'danger',
                                        'en_maintenance' => 'warning',
                                    ];
                                @endphp
                                <span class="badge badge-soft-{{ $statutColors[$moto->statut] ?? 'secondary' }}">
                                    {{ ucfirst(str_replace('_', ' ', $moto->statut ?? 'N/A')) }}
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                @if($confirmingDelete === $moto->id)
                                    <div class="btn-group">
                                        <button wire:click="delete({{ $moto->id }})" class="btn btn-sm btn-danger">
                                            <i class="bi bi-check"></i> Confirmer
                                        </button>
                                        <button wire:click="cancelDelete" class="btn btn-sm btn-secondary">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </div>
                                @else
                                    <div class="btn-group">
                                        <a href="{{ route('supervisor.motos.edit', $moto) }}" class="btn btn-sm btn-outline-primary" title="Modifier">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button wire:click="toggleStatut({{ $moto->id }})"
                                                class="btn btn-sm btn-outline-{{ $moto->statut === 'actif' ? 'warning' : 'success' }}"
                                                title="{{ $moto->statut === 'actif' ? 'Désactiver' : 'Activer' }}">
                                            <i class="bi bi-{{ $moto->statut === 'actif' ? 'pause' : 'play' }}"></i>
                                        </button>
                                        <button wire:click="confirmDelete({{ $moto->id }})" class="btn btn-sm btn-outline-danger" title="Supprimer">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-bicycle fs-1 d-block mb-3"></i>
                                <p class="mb-0">Aucune moto trouvée</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($motos->hasPages())
        <div class="card-footer bg-light">
            {{ $motos->links() }}
        </div>
        @endif
    </div>
</div>

