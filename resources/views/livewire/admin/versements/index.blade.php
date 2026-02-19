<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-cash-stack me-2 text-success"></i>Versements
            </h4>
            <p class="text-muted mb-0">Suivi des versements journaliers des motards</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button wire:click="$refresh" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-arrow-clockwise"></i>
            </button>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Rechercher</label>
                    <input type="text" wire:model.live="search" class="form-control" placeholder="Motard, moto, caissier...">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Statut</label>
                    <select wire:model.live="filterStatut" class="form-select">
                        <option value="">Tous</option>
                        <option value="paye">Payé</option>
                        <option value="partiel">Partiel</option>
                        <option value="en_retard">En retard</option>
                        <option value="non_effectue">Non effectué</option>
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
                    <label class="form-label small fw-semibold">Du</label>
                    <input type="date" wire:model.live="dateFrom" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Au</label>
                    <input type="date" wire:model.live="dateTo" class="form-control">
                </div>
                <div class="col-md-1">
                    <button wire:click="resetFilters" class="btn btn-outline-secondary w-100" title="Réinitialiser">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Motard</th>
                            <th>Moto</th>
                            <th>Montant</th>
                            <th>Attendu</th>
                            <th>Écart</th>
                            <th>Mode</th>
                            <th>Statut</th>
                            <th>Date</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($versements as $versement)
                        @php
                            $ecart = ($versement->montant ?? 0) - ($versement->montant_attendu ?? 0);
                        @endphp
                        <tr class="{{ $ecart < 0 ? 'table-warning' : '' }}">
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar avatar-sm bg-primary bg-opacity-10 text-primary rounded-circle">
                                        {{ strtoupper(substr($versement->motard->user->name ?? 'N', 0, 1)) }}
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
                            <td class="fw-semibold {{ $ecart >= 0 ? 'text-success' : 'text-warning' }}">{{ number_format($versement->montant) }} FC</td>
                            <td class="text-muted">{{ number_format($versement->montant_attendu) }} FC</td>
                            <td>
                                @if($ecart >= 0)
                                <span class="text-success"><i class="bi bi-check-circle"></i> OK</span>
                                @else
                                <span class="text-danger fw-bold">{{ number_format($ecart) }} FC</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">
                                    <i class="bi bi-{{ $versement->mode_paiement === 'cash' ? 'cash' : ($versement->mode_paiement === 'mobile_money' ? 'phone' : 'bank') }} me-1"></i>
                                    {{ ucfirst(str_replace('_', ' ', $versement->mode_paiement ?? '-')) }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $colors = [
                                        'paye' => 'success',
                                        'payé' => 'success',
                                        'partiel' => 'warning',
                                        'partiellement_payé' => 'warning',
                                        'en_retard' => 'danger',
                                        'non_effectue' => 'secondary',
                                        'non_effectué' => 'secondary'
                                    ];
                                @endphp
                                <span class="badge badge-soft-{{ $colors[$versement->statut] ?? 'secondary' }}">
                                    {{ ucfirst(str_replace('_', ' ', $versement->statut)) }}
                                </span>
                            </td>
                            <td class="text-muted small">
                                <i class="bi bi-calendar3 me-1"></i>{{ $versement->date_versement?->format('d/m/Y') ?? '-' }}
                            </td>
                            <td class="text-end pe-4">
                                <a href="{{ route('admin.versements.show', $versement) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                <p class="mb-0">Aucun versement trouvé</p>
                                <small>Modifiez vos filtres ou revenez plus tard</small>
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
