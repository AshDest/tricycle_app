<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-exclamation-triangle me-2 text-danger"></i>Gestion des Accidents
            </h4>
            <p class="text-muted mb-0">Suivi des accidents impliquant les motos-tricycles</p>
        </div>
    </div>

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card bg-danger bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="fw-bold text-danger mb-1">{{ $totalAccidents ?? 0 }}</h4>
                    <small class="text-muted">Total accidents</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card bg-warning bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="fw-bold text-warning mb-1">{{ $accidentsEnCours ?? 0 }}</h4>
                    <small class="text-muted">En cours de traitement</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card bg-success bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="fw-bold text-success mb-1">{{ $accidentsResolus ?? 0 }}</h4>
                    <small class="text-muted">Résolus</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card bg-info bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="fw-bold text-info mb-1">{{ number_format($coutTotal ?? 0) }} FC</h4>
                    <small class="text-muted">Coût total réparations</small>
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
                    <input type="text" wire:model.live="search" class="form-control" placeholder="Motard, moto, lieu...">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Statut</label>
                    <select wire:model.live="filterStatut" class="form-select">
                        <option value="">Tous</option>
                        <option value="declare">Déclaré</option>
                        <option value="en_cours">En cours</option>
                        <option value="repare">Réparé</option>
                        <option value="cloture">Clôturé</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Gravité</label>
                    <select wire:model.live="filterGravite" class="form-select">
                        <option value="">Tous</option>
                        <option value="leger">Léger</option>
                        <option value="moyen">Moyen</option>
                        <option value="grave">Grave</option>
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
                            <th>Coût estimé</th>
                            <th>Statut</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($accidents ?? [] as $accident)
                        <tr>
                            <td class="ps-4">
                                <span class="fw-medium">{{ $accident->date_accident?->format('d/m/Y') }}</span>
                                <small class="text-muted d-block">{{ $accident->date_accident?->format('H:i') }}</small>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">
                                    {{ $accident->moto->plaque_immatriculation ?? 'N/A' }}
                                </span>
                            </td>
                            <td>{{ $accident->motard->user->name ?? 'N/A' }}</td>
                            <td class="text-muted">{{ Str::limit($accident->lieu ?? 'N/A', 30) }}</td>
                            <td>
                                @php
                                    $graviteColors = ['leger' => 'info', 'moyen' => 'warning', 'grave' => 'danger'];
                                @endphp
                                <span class="badge badge-soft-{{ $graviteColors[$accident->gravite] ?? 'secondary' }}">
                                    {{ ucfirst($accident->gravite ?? 'N/A') }}
                                </span>
                            </td>
                            <td class="fw-semibold">{{ number_format($accident->cout_estime ?? 0) }} FC</td>
                            <td>
                                @php
                                    $statutColors = [
                                        'declare' => 'warning',
                                        'en_cours' => 'info',
                                        'repare' => 'primary',
                                        'cloture' => 'success'
                                    ];
                                @endphp
                                <span class="badge badge-soft-{{ $statutColors[$accident->statut] ?? 'secondary' }}">
                                    {{ ucfirst(str_replace('_', ' ', $accident->statut ?? 'N/A')) }}
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <a href="{{ route('admin.accidents.show', $accident) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-shield-check fs-1 text-success d-block mb-3"></i>
                                <p class="mb-0">Aucun accident enregistré</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if(($accidents ?? collect())->hasPages())
        <div class="card-footer bg-light">
            {{ $accidents->links() }}
        </div>
        @endif
    </div>
</div>
