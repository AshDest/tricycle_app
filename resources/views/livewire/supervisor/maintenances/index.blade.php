<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-tools me-2 text-info"></i>Suivi des Maintenances
            </h4>
            <p class="text-muted mb-0">Historique des interventions techniques sur les motos</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-success" wire:click="export">
                <i class="bi bi-download me-1"></i>Exporter CSV
            </button>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card bg-primary bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="mb-0 fw-bold text-primary">{{ $stats['total'] }}</h4>
                    <small class="text-muted">Total Maintenances</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card bg-warning bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="mb-0 fw-bold text-warning">{{ $stats['enCours'] }}</h4>
                    <small class="text-muted">En Cours</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card bg-success bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="mb-0 fw-bold text-success">{{ $stats['terminees'] }}</h4>
                    <small class="text-muted">Terminées</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card bg-danger bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="mb-0 fw-bold text-danger">{{ number_format($stats['coutTotal']) }} FC</h4>
                    <small class="text-muted">Coût Total</small>
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
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Moto, description...">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Statut</label>
                    <select wire:model.live="filterStatut" class="form-select">
                        <option value="">Tous</option>
                        <option value="en_attente">En attente</option>
                        <option value="en_cours">En cours</option>
                        <option value="termine">Terminé</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Type</label>
                    <select wire:model.live="filterType" class="form-select">
                        <option value="">Tous</option>
                        <option value="preventive">Préventive</option>
                        <option value="corrective">Corrective</option>
                        <option value="remplacement">Remplacement</option>
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

    <!-- Liste des maintenances -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Date</th>
                            <th>Moto</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th>Technicien</th>
                            <th class="text-end">Coût Total</th>
                            <th>Statut</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($maintenances as $maintenance)
                        <tr>
                            <td class="ps-4">
                                <span class="fw-medium">{{ $maintenance->date_intervention?->format('d/m/Y') }}</span>
                                <small class="text-muted d-block">{{ $maintenance->date_intervention?->format('H:i') }}</small>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">
                                    <i class="bi bi-bicycle me-1"></i>{{ $maintenance->moto->plaque_immatriculation ?? 'N/A' }}
                                </span>
                                @if($maintenance->moto->proprietaire)
                                <small class="text-muted d-block">{{ $maintenance->moto->proprietaire->user->name ?? '' }}</small>
                                @endif
                            </td>
                            <td>
                                @php
                                    $typeColors = [
                                        'preventive' => 'info',
                                        'corrective' => 'warning',
                                        'remplacement' => 'danger',
                                    ];
                                @endphp
                                <span class="badge badge-soft-{{ $typeColors[$maintenance->type] ?? 'secondary' }}">
                                    {{ ucfirst($maintenance->type ?? 'N/A') }}
                                </span>
                            </td>
                            <td>
                                <span class="text-truncate d-inline-block" style="max-width: 200px;" title="{{ $maintenance->description }}">
                                    {{ Str::limit($maintenance->description, 40) }}
                                </span>
                            </td>
                            <td>
                                <small>{{ $maintenance->technicien_garage_nom ?? 'N/A' }}</small>
                            </td>
                            <td class="text-end fw-semibold">
                                {{ number_format($maintenance->cout_total) }} FC
                            </td>
                            <td>
                                @php
                                    $statutColors = [
                                        'en_attente' => 'warning',
                                        'en_cours' => 'info',
                                        'termine' => 'success',
                                    ];
                                @endphp
                                <span class="badge badge-soft-{{ $statutColors[$maintenance->statut] ?? 'secondary' }}">
                                    {{ ucfirst(str_replace('_', ' ', $maintenance->statut ?? 'N/A')) }}
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <a href="{{ route('supervisor.maintenances.show', $maintenance) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-tools fs-1 d-block mb-3"></i>
                                <p class="mb-0">Aucune maintenance trouvée</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($maintenances->hasPages())
        <div class="card-footer bg-light">
            {{ $maintenances->links() }}
        </div>
        @endif
    </div>
</div>

