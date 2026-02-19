<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-tools me-2 text-info"></i>Motos & Prochaines Maintenances
            </h4>
            <p class="text-muted mb-0">Liste des motos avec leurs dates de maintenance prévues</p>
        </div>
        <div class="d-flex gap-2">
            <div class="dropdown">
                <button class="btn btn-success dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-download me-1"></i>Exporter
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item" href="#" wire:click.prevent="exportPdf">
                            <i class="bi bi-file-earmark-pdf me-2 text-danger"></i>Export PDF
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="#" wire:click.prevent="exportCsv">
                            <i class="bi bi-filetype-csv me-2 text-success"></i>Export CSV
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card bg-primary bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="fw-bold text-primary mb-1">{{ $stats['total'] }}</h4>
                    <small class="text-muted">Total Motos</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card bg-success bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="fw-bold text-success mb-1">{{ $stats['avec_maintenance'] }}</h4>
                    <small class="text-muted">Maintenance Planifiée</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card bg-warning bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="fw-bold text-warning mb-1">{{ $stats['urgentes'] }}</h4>
                    <small class="text-muted">Urgentes (< 7 jours)</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card bg-danger bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="fw-bold text-danger mb-1">{{ $stats['en_retard'] }}</h4>
                    <small class="text-muted">En Retard</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Rechercher</label>
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Plaque, matricule, propriétaire, motard...">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Statut Moto</label>
                    <select wire:model.live="filterStatut" class="form-select">
                        <option value="">Tous</option>
                        <option value="actif">Actif</option>
                        <option value="inactif">Inactif</option>
                        <option value="en_maintenance">En maintenance</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Maintenance</label>
                    <select wire:model.live="filterMaintenance" class="form-select">
                        <option value="">Toutes</option>
                        <option value="with_maintenance">Avec maintenance prévue</option>
                        <option value="without_maintenance">Sans maintenance prévue</option>
                        <option value="urgent">Urgentes (< 7 jours)</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Par page</label>
                    <select wire:model.live="perPage" class="form-select">
                        <option value="15">15</option>
                        <option value="30">30</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
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
                            <th>Motard</th>
                            <th>Statut</th>
                            <th>Dernière Maintenance</th>
                            <th>Prochaine Maintenance</th>
                            <th class="text-center">Jours Restants</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($motos as $moto)
                        @php
                            $prochaineMaintenance = $moto->maintenances
                                ->whereNotNull('prochain_entretien')
                                ->sortBy('prochain_entretien')
                                ->first();

                            $derniereMaintenance = $moto->maintenances
                                ->whereNotNull('date_intervention')
                                ->sortByDesc('date_intervention')
                                ->first();

                            $joursRestants = $prochaineMaintenance && $prochaineMaintenance->prochain_entretien
                                ? \Carbon\Carbon::today()->diffInDays(\Carbon\Carbon::parse($prochaineMaintenance->prochain_entretien), false)
                                : null;

                            $urgenceClass = '';
                            if ($joursRestants !== null) {
                                if ($joursRestants < 0) {
                                    $urgenceClass = 'table-danger';
                                } elseif ($joursRestants <= 7) {
                                    $urgenceClass = 'table-warning';
                                }
                            }
                        @endphp
                        <tr class="{{ $urgenceClass }}">
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar avatar-sm bg-info bg-opacity-10 text-info rounded">
                                        <i class="bi bi-bicycle"></i>
                                    </div>
                                    <div>
                                        <span class="fw-medium d-block">{{ $moto->plaque_immatriculation }}</span>
                                        <small class="text-muted">{{ $moto->numero_matricule ?? 'N/A' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $moto->proprietaire->user->name ?? 'N/A' }}</td>
                            <td>{{ $moto->motard->user->name ?? 'Non assigné' }}</td>
                            <td>
                                <span class="badge badge-soft-{{ $moto->statut === 'actif' ? 'success' : ($moto->statut === 'en_maintenance' ? 'warning' : 'secondary') }}">
                                    {{ ucfirst(str_replace('_', ' ', $moto->statut)) }}
                                </span>
                            </td>
                            <td>
                                @if($derniereMaintenance)
                                <div>
                                    <span class="small">{{ $derniereMaintenance->date_intervention->format('d/m/Y') }}</span>
                                    <span class="badge bg-light text-dark ms-1">{{ ucfirst($derniereMaintenance->type) }}</span>
                                </div>
                                @else
                                <span class="text-muted small">Aucune</span>
                                @endif
                            </td>
                            <td>
                                @if($prochaineMaintenance && $prochaineMaintenance->prochain_entretien)
                                <div>
                                    <span class="fw-medium {{ $joursRestants < 0 ? 'text-danger' : ($joursRestants <= 7 ? 'text-warning' : 'text-success') }}">
                                        {{ \Carbon\Carbon::parse($prochaineMaintenance->prochain_entretien)->format('d/m/Y') }}
                                    </span>
                                    @if($prochaineMaintenance->type)
                                    <span class="badge bg-light text-dark ms-1 small">{{ ucfirst($prochaineMaintenance->type) }}</span>
                                    @endif
                                </div>
                                @else
                                <span class="text-muted small">Non planifiée</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($joursRestants !== null)
                                    @if($joursRestants < 0)
                                    <span class="badge bg-danger">{{ abs($joursRestants) }} jours de retard</span>
                                    @elseif($joursRestants == 0)
                                    <span class="badge bg-danger">Aujourd'hui!</span>
                                    @elseif($joursRestants <= 7)
                                    <span class="badge bg-warning text-dark">{{ $joursRestants }} jours</span>
                                    @else
                                    <span class="badge bg-success">{{ $joursRestants }} jours</span>
                                    @endif
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group">
                                    <a href="{{ route('admin.motos.show', $moto) }}" class="btn btn-sm btn-outline-primary" title="Voir détails">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.maintenances.create', ['moto_id' => $moto->id]) }}" class="btn btn-sm btn-outline-success" title="Nouvelle maintenance">
                                        <i class="bi bi-tools"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
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

