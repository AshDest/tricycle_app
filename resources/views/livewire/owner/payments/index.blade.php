<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-wallet2 me-2 text-success"></i>Mes Paiements
            </h4>
            <p class="text-muted mb-0">Historique de vos paiements reçus</p>
        </div>
        <button wire:click="demanderRetrait" class="btn btn-success">
            <i class="bi bi-cash-coin me-1"></i>Demander un Retrait
        </button>
    </div>

    <!-- Message Alert -->
    @if($message)
    <div class="alert alert-{{ $messageType }} alert-dismissible fade show mb-4" role="alert">
        <i class="bi bi-info-circle me-2"></i>{{ $message }}
        <button type="button" class="btn-close" wire:click="closeMessage" aria-label="Close"></button>
    </div>
    @endif

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card bg-success bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="fw-bold text-success mb-1">{{ number_format($totalRecu ?? 0) }} FC</h4>
                    <small class="text-muted">Total reçu</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card bg-primary bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="fw-bold text-primary mb-1">{{ number_format($soldeDisponible ?? 0) }} FC</h4>
                    <small class="text-muted">Solde disponible</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card bg-warning bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="fw-bold text-warning mb-1">{{ number_format($enAttente ?? 0) }} FC</h4>
                    <small class="text-muted">En attente</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card bg-danger bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="fw-bold text-danger mb-1">{{ number_format($arrieres ?? 0) }} FC</h4>
                    <small class="text-muted">Arriérés motards</small>
                </div>
            </div>
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
                        <option value="month">Ce mois</option>
                        <option value="quarter">Ce trimestre</option>
                        <option value="year">Cette année</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Statut</label>
                    <select wire:model.live="filterStatut" class="form-select">
                        <option value="">Tous</option>
                        <option value="paye">Payé</option>
                        <option value="en_attente">En attente</option>
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
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des paiements -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Date</th>
                            <th>Montant</th>
                            <th>Mode</th>
                            <th>Référence</th>
                            <th>Statut</th>
                            <th class="text-end pe-4">Reçu</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments ?? [] as $payment)
                        <tr>
                            <td class="ps-4">
                                <span class="fw-medium">{{ $payment->created_at?->format('d/m/Y') }}</span>
                                <small class="text-muted d-block">{{ $payment->created_at?->format('H:i') }}</small>
                            </td>
                            <td class="fw-bold text-success">{{ number_format($payment->montant ?? 0) }} FC</td>
                            <td>
                                @php
                                    $modeIcons = ['mobile_money' => 'phone', 'virement' => 'bank', 'cash' => 'cash'];
                                @endphp
                                <span class="badge bg-light text-dark">
                                    <i class="bi bi-{{ $modeIcons[$payment->mode_paiement] ?? 'credit-card' }} me-1"></i>
                                    {{ ucfirst(str_replace('_', ' ', $payment->mode_paiement ?? 'N/A')) }}
                                </span>
                            </td>
                            <td><code>{{ $payment->reference ?? 'N/A' }}</code></td>
                            <td>
                                @php
                                    $statutColors = [
                                        'paye' => 'success',
                                        'en_attente' => 'warning',
                                        'rejete' => 'danger'
                                    ];
                                @endphp
                                <span class="badge badge-soft-{{ $statutColors[$payment->statut] ?? 'secondary' }}">
                                    {{ ucfirst(str_replace('_', ' ', $payment->statut ?? 'N/A')) }}
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                @if($payment->statut === 'paye')
                                <button wire:click="telechargerRecu({{ $payment->id }})" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-download me-1"></i>PDF
                                </button>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-wallet fs-1 d-block mb-3"></i>
                                <p class="mb-0">Aucun paiement enregistré</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if(($payments ?? collect())->hasPages())
        <div class="card-footer bg-light">
            {{ $payments->links() }}
        </div>
        @endif
    </div>
</div>
