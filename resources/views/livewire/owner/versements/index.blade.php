<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-cash-stack me-2 text-success"></i>Versements de mes Motos
            </h4>
            <p class="text-muted mb-0">Suivi des versements effectués par les motards sur vos motos</p>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label small fw-semibold">Rechercher</label>
                    <input type="text" wire:model.live="search" class="form-control" placeholder="Motard, moto...">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Statut</label>
                    <select wire:model.live="filterStatut" class="form-select">
                        <option value="">Tous</option>
                        <option value="payé">Payé</option>
                        <option value="partiellement_payé">Partiel</option>
                        <option value="en_retard">En retard</option>
                        <option value="non_effectué">Non effectué</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button wire:click="$refresh" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-arrow-clockwise me-1"></i>Actualiser
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
                            <th>Moto</th>
                            <th>Motard</th>
                            <th>Montant versé</th>
                            <th>Montant attendu</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($versements ?? [] as $versement)
                        <tr>
                            <td class="ps-4">
                                <span class="fw-medium">{{ $versement->date_versement?->format('d/m/Y') }}</span>
                                <small class="text-muted d-block">{{ $versement->created_at?->format('H:i') }}</small>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">
                                    <i class="bi bi-bicycle me-1"></i>{{ $versement->moto->plaque_immatriculation ?? 'N/A' }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar avatar-sm bg-success bg-opacity-10 text-success rounded-circle">
                                        {{ strtoupper(substr($versement->motard->user->name ?? 'M', 0, 1)) }}
                                    </div>
                                    <span>{{ $versement->motard->user->name ?? 'N/A' }}</span>
                                </div>
                            </td>
                            <td class="fw-bold text-success">{{ number_format($versement->montant ?? 0) }} FC</td>
                            <td class="text-muted">{{ number_format($versement->montant_attendu ?? 0) }} FC</td>
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
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                <p class="mb-0">Aucun versement trouvé</p>
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
