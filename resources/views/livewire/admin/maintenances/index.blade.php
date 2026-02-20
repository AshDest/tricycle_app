<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-tools me-2 text-warning"></i>Gestion des Maintenances
            </h4>
            <p class="text-muted mb-0">Suivi technique des motos-tricycles</p>
        </div>
        <div class="d-flex gap-2">
            <button wire:click="exportPdf" class="btn btn-danger">
                <i class="bi bi-file-pdf me-1"></i> PDF
            </button>
            <a href="{{ route('admin.maintenances.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>Nouvelle Maintenance
            </a>
        </div>
    </div>

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card bg-warning bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="fw-bold text-warning mb-1">{{ $maintenancesEnCours ?? 0 }}</h4>
                    <small class="text-muted">En cours</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card bg-info bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="fw-bold text-info mb-1">{{ $maintenancesPlanifiees ?? 0 }}</h4>
                    <small class="text-muted">Planifiées</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card bg-success bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="fw-bold text-success mb-1">{{ $maintenancesTerminees ?? 0 }}</h4>
                    <small class="text-muted">Terminées ce mois</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card bg-danger bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="fw-bold text-danger mb-1">{{ number_format($coutTotalMois ?? 0) }} FC</h4>
                    <small class="text-muted">Coût ce mois</small>
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
                    <input type="text" wire:model.live="search" class="form-control" placeholder="Moto, description...">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Type</label>
                    <select wire:model.live="filterType" class="form-select">
                        <option value="">Tous</option>
                        <option value="preventive">Préventive</option>
                        <option value="corrective">Corrective</option>
                        <option value="remplacement">Remplacement pièces</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Statut</label>
                    <select wire:model.live="filterStatut" class="form-select">
                        <option value="">Tous</option>
                        <option value="planifiee">Planifiée</option>
                        <option value="en_cours">En cours</option>
                        <option value="terminee">Terminée</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Du</label>
                    <input type="date" wire:model.live="dateFrom" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Au</label>
                    <input type="date" wire:model.live="dateTo" class="form-control">
                </div>
                <div class="col-md-1">
                    <button wire:click="resetFilters" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des maintenances -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Moto</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th>Technicien/Garage</th>
                            <th>Coût total</th>
                            <th>Statut</th>
                            <th>Date</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($maintenances ?? [] as $maintenance)
                        <tr>
                            <td class="ps-4">
                                <span class="badge bg-light text-dark">
                                    <i class="bi bi-bicycle me-1"></i>{{ $maintenance->moto->plaque_immatriculation ?? 'N/A' }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $typeColors = ['preventive' => 'info', 'corrective' => 'warning', 'remplacement' => 'primary'];
                                    $typeIcons = ['preventive' => 'calendar-check', 'corrective' => 'wrench', 'remplacement' => 'gear'];
                                @endphp
                                <span class="badge badge-soft-{{ $typeColors[$maintenance->type] ?? 'secondary' }}">
                                    <i class="bi bi-{{ $typeIcons[$maintenance->type] ?? 'tools' }} me-1"></i>
                                    {{ ucfirst($maintenance->type ?? 'N/A') }}
                                </span>
                            </td>
                            <td class="text-muted">{{ Str::limit($maintenance->description ?? 'N/A', 40) }}</td>
                            <td>{{ $maintenance->technicien ?? 'N/A' }}</td>
                            <td class="fw-semibold">{{ number_format($maintenance->cout_total ?? 0) }} FC</td>
                            <td>
                                @php
                                    $statutColors = ['planifiee' => 'info', 'en_cours' => 'warning', 'terminee' => 'success'];
                                @endphp
                                <span class="badge badge-soft-{{ $statutColors[$maintenance->statut] ?? 'secondary' }}">
                                    {{ ucfirst(str_replace('_', ' ', $maintenance->statut ?? 'N/A')) }}
                                </span>
                            </td>
                            <td class="text-muted small">{{ $maintenance->date_maintenance?->format('d/m/Y') ?? 'N/A' }}</td>
                            <td class="text-end pe-4">
                                <div class="btn-group">
                                    <a href="{{ route('admin.maintenances.show', $maintenance) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if($maintenance->statut !== 'terminee')
                                    <button wire:click="terminer({{ $maintenance->id }})" class="btn btn-sm btn-outline-success" title="Marquer terminée">
                                        <i class="bi bi-check-lg"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-tools fs-1 d-block mb-3"></i>
                                <p class="mb-0">Aucune maintenance enregistrée</p>
                                <a href="{{ route('admin.maintenances.create') }}" class="btn btn-sm btn-primary mt-3">
                                    <i class="bi bi-plus-lg me-1"></i>Nouvelle maintenance
                                </a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if(($maintenances ?? collect())->hasPages())
        <div class="card-footer bg-light">
            {{ $maintenances->links() }}
        </div>
        @endif
    </div>
</div>
