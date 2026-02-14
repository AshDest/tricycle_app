<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-cash-stack me-2 text-success"></i>Versements Reçus
            </h4>
            <p class="text-muted mb-0">Versements des motards à votre point de collecte</p>
        </div>
        <a href="{{ route('cashier.versements.create') }}" class="btn btn-success">
            <i class="bi bi-plus-lg me-1"></i>Enregistrer un Versement
        </a>
    </div>

    <!-- Stats du jour -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card bg-success bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="fw-bold text-success mb-1">{{ number_format($totalAujourdhui ?? 0) }} FC</h4>
                    <small class="text-muted">Reçu aujourd'hui</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card bg-primary bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="fw-bold text-primary mb-1">{{ $nombreVersementsJour ?? 0 }}</h4>
                    <small class="text-muted">Versements aujourd'hui</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card bg-warning bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="fw-bold text-warning mb-1">{{ number_format($soldeEnCaisse ?? 0) }} FC</h4>
                    <small class="text-muted">Solde en caisse</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card bg-info bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="fw-bold text-info mb-1">{{ $motardsServisJour ?? 0 }}</h4>
                    <small class="text-muted">Motards servis</small>
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
                    <input type="text" wire:model.live="search" class="form-control" placeholder="Nom du motard, plaque...">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Statut</label>
                    <select wire:model.live="filterStatut" class="form-select">
                        <option value="">Tous</option>
                        <option value="payé">Payé</option>
                        <option value="partiellement_payé">Partiel</option>
                        <option value="en_retard">En retard</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Mode</label>
                    <select wire:model.live="filterMode" class="form-select">
                        <option value="">Tous</option>
                        <option value="cash">Cash</option>
                        <option value="mobile_money">Mobile Money</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Date</label>
                    <input type="date" wire:model.live="filterDate" class="form-control">
                </div>
                <div class="col-md-2">
                    <button wire:click="resetFilters" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-x-lg me-1"></i>Réinitialiser
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
                            <th class="ps-4">Motard</th>
                            <th>Moto</th>
                            <th>Montant reçu</th>
                            <th>Montant attendu</th>
                            <th>Mode</th>
                            <th>Statut</th>
                            <th>Heure</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($versements ?? [] as $versement)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar avatar-sm bg-success bg-opacity-10 text-success rounded-circle">
                                        {{ strtoupper(substr($versement->motard->user->name ?? 'M', 0, 1)) }}
                                    </div>
                                    <div>
                                        <span class="fw-medium d-block">{{ $versement->motard->user->name ?? 'N/A' }}</span>
                                        <small class="text-muted">{{ $versement->motard->numero_identifiant ?? '' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">
                                    <i class="bi bi-bicycle me-1"></i>{{ $versement->moto->plaque_immatriculation ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="fw-bold text-success">{{ number_format($versement->montant ?? 0) }} FC</td>
                            <td class="text-muted">{{ number_format($versement->montant_attendu ?? 0) }} FC</td>
                            <td>
                                @php
                                    $modeIcons = ['cash' => 'cash', 'mobile_money' => 'phone', 'depot' => 'bank'];
                                @endphp
                                <span class="badge bg-light text-dark">
                                    <i class="bi bi-{{ $modeIcons[$versement->mode_paiement] ?? 'credit-card' }} me-1"></i>
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
                            <td class="text-muted small">
                                <i class="bi bi-clock me-1"></i>{{ $versement->created_at?->format('H:i') }}
                            </td>
                            <td class="text-end pe-4">
                                <button wire:click="voirDetails({{ $versement->id }})" class="btn btn-sm btn-outline-primary" title="Détails">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                <p class="mb-0">Aucun versement enregistré</p>
                                <a href="{{ route('cashier.versements.create') }}" class="btn btn-sm btn-success mt-3">
                                    <i class="bi bi-plus-lg me-1"></i>Enregistrer un versement
                                </a>
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
