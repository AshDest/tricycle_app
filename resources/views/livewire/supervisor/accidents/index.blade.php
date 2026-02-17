<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-exclamation-triangle me-2 text-danger"></i>Suivi des Accidents
            </h4>
            <p class="text-muted mb-0">Historique des accidents impliquant les motos-tricycles</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-success" wire:click="export">
                <i class="bi bi-download me-1"></i>Exporter CSV
            </button>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-2">
            <div class="card bg-primary bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="mb-0 fw-bold text-primary">{{ $stats['total'] }}</h4>
                    <small class="text-muted">Total</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-2">
            <div class="card bg-warning bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="mb-0 fw-bold text-warning">{{ $stats['declares'] }}</h4>
                    <small class="text-muted">Déclarés</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-2">
            <div class="card bg-info bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="mb-0 fw-bold text-info">{{ $stats['enReparation'] }}</h4>
                    <small class="text-muted">En réparation</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-2">
            <div class="card bg-success bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="mb-0 fw-bold text-success">{{ $stats['clotures'] }}</h4>
                    <small class="text-muted">Clôturés</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-2">
            <div class="card bg-danger bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="mb-0 fw-bold text-danger">{{ $stats['graves'] }}</h4>
                    <small class="text-muted">Graves</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-2">
            <div class="card bg-dark bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="mb-0 fw-bold">{{ number_format($stats['coutTotal']) }}</h4>
                    <small class="text-muted">Coût Total (FC)</small>
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
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Moto, motard, lieu...">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Statut</label>
                    <select wire:model.live="filterStatut" class="form-select">
                        <option value="">Tous</option>
                        <option value="declare">Déclaré</option>
                        <option value="en_evaluation">En évaluation</option>
                        <option value="en_reparation">En réparation</option>
                        <option value="cloture">Clôturé</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Gravité</label>
                    <select wire:model.live="filterGravite" class="form-select">
                        <option value="">Toutes</option>
                        <option value="mineur">Mineur</option>
                        <option value="modere">Modéré</option>
                        <option value="grave">Grave</option>
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

    <!-- Liste des accidents -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Date</th>
                            <th>Moto</th>
                            <th>Motard</th>
                            <th>Lieu</th>
                            <th>Gravité</th>
                            <th class="text-end">Coût</th>
                            <th>Statut</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($accidents as $accident)
                        <tr>
                            <td class="ps-4">
                                <span class="fw-medium">{{ $accident->date_heure?->format('d/m/Y') }}</span>
                                <small class="text-muted d-block">{{ $accident->date_heure?->format('H:i') }}</small>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">
                                    <i class="bi bi-bicycle me-1"></i>{{ $accident->moto->plaque_immatriculation ?? 'N/A' }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar avatar-sm bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                        {{ strtoupper(substr($accident->motard->user->name ?? 'M', 0, 1)) }}
                                    </div>
                                    <span class="small">{{ $accident->motard->user->name ?? 'N/A' }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="text-truncate d-inline-block" style="max-width: 150px;" title="{{ $accident->lieu }}">
                                    {{ Str::limit($accident->lieu, 25) }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $graviteColors = [
                                        'mineur' => 'success',
                                        'modere' => 'warning',
                                        'grave' => 'danger',
                                    ];
                                @endphp
                                <span class="badge badge-soft-{{ $graviteColors[$accident->gravite] ?? 'secondary' }}">
                                    {{ ucfirst($accident->gravite ?? 'N/A') }}
                                </span>
                            </td>
                            <td class="text-end fw-semibold">
                                {{ number_format($accident->cout_reel ?? $accident->estimation_cout ?? 0) }} FC
                            </td>
                            <td>
                                @php
                                    $statutColors = [
                                        'declare' => 'warning',
                                        'en_evaluation' => 'info',
                                        'en_reparation' => 'primary',
                                        'cloture' => 'success',
                                    ];
                                @endphp
                                <span class="badge badge-soft-{{ $statutColors[$accident->statut] ?? 'secondary' }}">
                                    {{ ucfirst(str_replace('_', ' ', $accident->statut ?? 'N/A')) }}
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <a href="{{ route('supervisor.accidents.show', $accident) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-exclamation-triangle fs-1 d-block mb-3"></i>
                                <p class="mb-0">Aucun accident trouvé</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($accidents->hasPages())
        <div class="card-footer bg-light">
            {{ $accidents->links() }}
        </div>
        @endif
    </div>
</div>

