<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-cash-stack me-2 text-success"></i>Suivi des Versements
            </h4>
            <p class="text-muted mb-0">Visualisation des versements journaliers</p>
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
            <div class="card bg-success bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="mb-0 fw-bold text-success">{{ number_format($stats['totalMontant']) }}</h4>
                    <small class="text-muted">Versé (FC)</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-2">
            <div class="card bg-info bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="mb-0 fw-bold text-info">{{ number_format($stats['totalAttendu']) }}</h4>
                    <small class="text-muted">Attendu (FC)</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-2">
            <div class="card bg-warning bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="mb-0 fw-bold text-warning">{{ $stats['tauxRecouvrement'] }}%</h4>
                    <small class="text-muted">Recouvrement</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-2">
            <div class="card bg-success bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="mb-0 fw-bold text-success">{{ $stats['payes'] }}</h4>
                    <small class="text-muted">Payés</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-2">
            <div class="card bg-danger bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="mb-0 fw-bold text-danger">{{ $stats['enRetard'] }}</h4>
                    <small class="text-muted">En Retard</small>
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
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Motard, plaque...">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Statut</label>
                    <select wire:model.live="filterStatut" class="form-select">
                        <option value="">Tous</option>
                        <option value="paye">Payé</option>
                        <option value="en_attente">En attente</option>
                        <option value="en_retard">En retard</option>
                        <option value="partiel">Partiel</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Mode</label>
                    <select wire:model.live="filterMode" class="form-select">
                        <option value="">Tous</option>
                        <option value="cash">Cash</option>
                        <option value="mobile_money">Mobile Money</option>
                        <option value="depot">Dépôt</option>
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

    <!-- Liste des versements -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Date</th>
                            <th>Motard</th>
                            <th>Moto</th>
                            <th class="text-end">Attendu</th>
                            <th class="text-end">Versé</th>
                            <th>Mode</th>
                            <th>Statut</th>
                            <th>Caissier</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($versements as $versement)
                        <tr>
                            <td class="ps-4">
                                <span class="fw-medium">{{ $versement->date_versement?->format('d/m/Y') }}</span>
                                <small class="text-muted d-block">{{ $versement->created_at->format('H:i') }}</small>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar avatar-sm bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                        {{ strtoupper(substr($versement->motard->user->name ?? 'M', 0, 1)) }}
                                    </div>
                                    <div>
                                        <span class="fw-medium d-block small">{{ $versement->motard->user->name ?? 'N/A' }}</span>
                                        <small class="text-muted">{{ $versement->motard->numero_identifiant ?? '' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">
                                    {{ $versement->moto->plaque_immatriculation ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <span class="text-muted">{{ number_format($versement->montant_attendu ?? 0) }} FC</span>
                            </td>
                            <td class="text-end">
                                <span class="fw-semibold {{ $versement->montant >= ($versement->montant_attendu ?? 0) ? 'text-success' : 'text-danger' }}">
                                    {{ number_format($versement->montant ?? 0) }} FC
                                </span>
                            </td>
                            <td>
                                @php
                                    $modeIcons = [
                                        'cash' => 'bi-cash',
                                        'mobile_money' => 'bi-phone',
                                        'depot' => 'bi-bank',
                                    ];
                                @endphp
                                <span class="badge bg-light text-dark">
                                    <i class="{{ $modeIcons[$versement->mode_paiement] ?? 'bi-question' }} me-1"></i>
                                    {{ ucfirst(str_replace('_', ' ', $versement->mode_paiement ?? 'N/A')) }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $statutColors = [
                                        'paye' => 'success',
                                        'en_attente' => 'warning',
                                        'en_retard' => 'danger',
                                        'partiel' => 'info',
                                    ];
                                @endphp
                                <span class="badge badge-soft-{{ $statutColors[$versement->statut] ?? 'secondary' }}">
                                    {{ ucfirst(str_replace('_', ' ', $versement->statut ?? 'N/A')) }}
                                </span>
                            </td>
                            <td>
                                <small class="text-muted">{{ $versement->caissier->user->name ?? 'N/A' }}</small>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-cash-stack fs-1 d-block mb-3"></i>
                                <p class="mb-0">Aucun versement trouvé pour cette période</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($versements->hasPages())
        <div class="card-footer bg-light">
            {{ $versements->links() }}
        </div>
        @endif
    </div>
</div>
