<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-droplet me-2 text-info"></i>Tous les Lavages
            </h4>
            <p class="text-muted mb-0">Vue globale des lavages effectués</p>
        </div>
        <a href="{{ route('admin.cleaners.index') }}" class="btn btn-outline-info">
            <i class="bi bi-people me-1"></i>Gérer les Laveurs
        </a>
    </div>

    <!-- Statistiques -->
    <div class="row g-3 mb-4">
        <div class="col-md-2 col-6">
            <div class="card bg-info bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="fw-bold mb-0">{{ $stats['total_jour'] }}</h4>
                    <small class="text-muted">Lavages (jour)</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card bg-success bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="fw-bold mb-0 text-success">{{ number_format($stats['ca_jour']) }} FC</h4>
                    <small class="text-muted">CA du jour</small>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-6">
            <div class="card bg-warning bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="fw-bold mb-0 text-warning">{{ number_format($stats['part_okami_jour']) }} FC</h4>
                    <small class="text-muted">OKAMI (jour)</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card bg-primary bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="fw-bold mb-0 text-primary">{{ number_format($stats['ca_mois']) }} FC</h4>
                    <small class="text-muted">CA du mois</small>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-6">
            <div class="card bg-secondary bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="fw-bold mb-0">{{ number_format($stats['part_okami_mois']) }} FC</h4>
                    <small class="text-muted">OKAMI (mois)</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-2">
                    <label class="form-label small">Recherche</label>
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="N° lavage, plaque...">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Laveur</label>
                    <select wire:model.live="filterCleaner" class="form-select">
                        <option value="">Tous</option>
                        @foreach($cleaners as $cleaner)
                        <option value="{{ $cleaner->id }}">{{ $cleaner->user->name ?? $cleaner->identifiant }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Type</label>
                    <select wire:model.live="filterType" class="form-select">
                        <option value="">Tous</option>
                        <option value="simple">Simple</option>
                        <option value="complet">Complet</option>
                        <option value="premium">Premium</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Source</label>
                    <select wire:model.live="filterSource" class="form-select">
                        <option value="">Toutes</option>
                        <option value="interne">Système</option>
                        <option value="externe">Externe</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Date début</label>
                    <input type="date" wire:model.live="dateDebut" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Date fin</label>
                    <input type="date" wire:model.live="dateFin" class="form-control">
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des lavages -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>N° Lavage</th>
                            <th>Laveur</th>
                            <th>Moto</th>
                            <th>Type</th>
                            <th>Prix</th>
                            <th>Part Laveur</th>
                            <th>Part OKAMI</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($lavages as $lavage)
                        <tr>
                            <td>
                                <span class="fw-semibold text-primary">{{ $lavage->numero_lavage }}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-info bg-opacity-10 rounded-circle p-1 me-2">
                                        <i class="bi bi-person text-info small"></i>
                                    </div>
                                    <div>
                                        <small class="fw-semibold">{{ $lavage->cleaner?->user?->name ?? 'N/A' }}</small>
                                        <small class="d-block text-muted">{{ $lavage->cleaner?->identifiant ?? '' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($lavage->is_externe)
                                <span class="badge bg-secondary">Externe</span><br>
                                <small>{{ $lavage->plaque_externe }}</small>
                                @else
                                <span class="badge bg-info">Système</span><br>
                                <small>{{ $lavage->moto?->plaque_immatriculation ?? 'N/A' }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $lavage->type_lavage === 'premium' ? 'warning' : ($lavage->type_lavage === 'complet' ? 'primary' : 'info') }}">
                                    {{ ucfirst($lavage->type_lavage) }}
                                </span>
                            </td>
                            <td>{{ number_format($lavage->prix_final) }} FC</td>
                            <td class="text-success fw-semibold">{{ number_format($lavage->part_cleaner) }} FC</td>
                            <td>
                                @if($lavage->part_okami > 0)
                                <span class="text-warning fw-semibold">{{ number_format($lavage->part_okami) }} FC</span>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <small>{{ $lavage->date_lavage->format('d/m/Y') }}</small><br>
                                <small class="text-muted">{{ $lavage->date_lavage->format('H:i') }}</small>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                Aucun lavage trouvé
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($lavages->hasPages())
        <div class="card-footer bg-white">
            {{ $lavages->links() }}
        </div>
        @endif
    </div>
</div>

