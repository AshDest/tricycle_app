<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-clock-history me-2 text-info"></i>Historique de mes Versements
            </h4>
            <p class="text-muted mb-0">Consultez tous vos versements</p>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Période</label>
                    <select wire:model.live="filterPeriode" class="form-select">
                        <option value="">Tout</option>
                        <option value="today">Aujourd'hui</option>
                        <option value="week">Cette semaine</option>
                        <option value="month">Ce mois</option>
                        <option value="year">Cette année</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Statut</label>
                    <select wire:model.live="filterStatut" class="form-select">
                        <option value="">Tous</option>
                        <option value="payé">Payé</option>
                        <option value="partiellement_payé">Partiel</option>
                        <option value="en_retard">En retard</option>
                        <option value="non_effectué">Non effectué</option>
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
                <div class="col-md-2">
                    <button wire:click="resetFilters" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-x-lg me-1"></i>Réinitialiser
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats résumé -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card bg-success bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="fw-bold text-success mb-1">{{ number_format($totalVerse ?? 0) }} FC</h4>
                    <small class="text-muted">Total versé</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card bg-primary bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="fw-bold text-primary mb-1">{{ $nombreVersements ?? 0 }}</h4>
                    <small class="text-muted">Versements</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card bg-warning bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="fw-bold text-warning mb-1">{{ $versementsPartiels ?? 0 }}</h4>
                    <small class="text-muted">Partiels</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card bg-danger bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="fw-bold text-danger mb-1">{{ $versementsEnRetard ?? 0 }}</h4>
                    <small class="text-muted">En retard</small>
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
                            <th>Montant versé</th>
                            <th>Montant attendu</th>
                            <th>Mode</th>
                            <th>Statut</th>
                            <th class="pe-4">Validation</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($versements ?? [] as $versement)
                        <tr>
                            <td class="ps-4">
                                <div>
                                    <span class="fw-medium">{{ $versement->date_versement?->format('d/m/Y') }}</span>
                                    <small class="text-muted d-block">{{ $versement->created_at?->format('H:i') }}</small>
                                </div>
                            </td>
                            <td class="fw-semibold text-success">{{ number_format($versement->montant ?? 0) }} FC</td>
                            <td class="text-muted">{{ number_format($versement->montant_attendu ?? 0) }} FC</td>
                            <td>
                                <span class="badge bg-light text-dark">
                                    <i class="bi bi-{{ $versement->mode_paiement === 'cash' ? 'cash' : ($versement->mode_paiement === 'mobile_money' ? 'phone' : 'bank') }} me-1"></i>
                                    {{ ucfirst(str_replace('_', ' ', $versement->mode_paiement ?? 'N/A')) }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $statutColors = [
                                        'payé' => 'success',
                                        'partiellement_payé' => 'warning',
                                        'en_retard' => 'danger',
                                        'non_effectué' => 'secondary'
                                    ];
                                @endphp
                                <span class="badge badge-soft-{{ $statutColors[$versement->statut] ?? 'secondary' }}">
                                    {{ ucfirst(str_replace('_', ' ', $versement->statut ?? 'N/A')) }}
                                </span>
                            </td>
                            <td class="pe-4">
                                @if($versement->validated_at)
                                <span class="badge badge-soft-success">
                                    <i class="bi bi-check-circle me-1"></i>Validé
                                </span>
                                @else
                                <span class="badge badge-soft-secondary">
                                    <i class="bi bi-clock me-1"></i>En attente
                                </span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                <p class="mb-0">Aucun versement trouvé</p>
                                <small>Modifiez vos filtres pour voir plus de résultats</small>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if(($versements ?? collect())->hasPages())
        <div class="card-footer bg-light">
            {{ $versements->links() }}
        </div>
        @endif
    </div>
</div>
