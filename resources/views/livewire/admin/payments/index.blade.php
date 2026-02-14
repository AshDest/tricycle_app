<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-wallet2 me-2 text-success"></i>Paiements Propriétaires
            </h4>
            <p class="text-muted mb-0">Gestion des paiements aux propriétaires de motos</p>
        </div>
        <a href="{{ route('admin.payments.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i>Nouveau Paiement
        </a>
    </div>

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card bg-success bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="fw-bold text-success mb-1">{{ number_format($totalPaye ?? 0) }} FC</h4>
                    <small class="text-muted">Total payé ce mois</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card bg-warning bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="fw-bold text-warning mb-1">{{ number_format($totalEnAttente ?? 0) }} FC</h4>
                    <small class="text-muted">En attente</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card bg-danger bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="fw-bold text-danger mb-1">{{ number_format($totalArrieres ?? 0) }} FC</h4>
                    <small class="text-muted">Arriérés</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card bg-primary bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="fw-bold text-primary mb-1">{{ $nombrePaiements ?? 0 }}</h4>
                    <small class="text-muted">Paiements ce mois</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Propriétaire</label>
                    <select wire:model.live="filterProprietaire" class="form-select">
                        <option value="">Tous</option>
                        @foreach($proprietaires ?? [] as $prop)
                        <option value="{{ $prop->id }}">{{ $prop->user->name ?? 'N/A' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Statut</label>
                    <select wire:model.live="filterStatut" class="form-select">
                        <option value="">Tous</option>
                        <option value="en_attente">En attente</option>
                        <option value="approuve">Approuvé</option>
                        <option value="paye">Payé</option>
                        <option value="rejete">Rejeté</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Mode</label>
                    <select wire:model.live="filterMode" class="form-select">
                        <option value="">Tous</option>
                        <option value="mobile_money">Mobile Money</option>
                        <option value="virement">Virement bancaire</option>
                        <option value="cash">Cash</option>
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

    <!-- Liste des paiements -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Propriétaire</th>
                            <th>Montant</th>
                            <th>Mode</th>
                            <th>Référence</th>
                            <th>Statut</th>
                            <th>Date</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments ?? [] as $payment)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar avatar-sm bg-warning bg-opacity-10 text-warning rounded-circle">
                                        {{ strtoupper(substr($payment->proprietaire->user->name ?? 'P', 0, 1)) }}
                                    </div>
                                    <div>
                                        <span class="fw-medium d-block">{{ $payment->proprietaire->user->name ?? 'N/A' }}</span>
                                        <small class="text-muted">{{ $payment->proprietaire->user->email ?? '' }}</small>
                                    </div>
                                </div>
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
                                        'en_attente' => 'warning',
                                        'approuve' => 'info',
                                        'paye' => 'success',
                                        'rejete' => 'danger'
                                    ];
                                @endphp
                                <span class="badge badge-soft-{{ $statutColors[$payment->statut] ?? 'secondary' }}">
                                    {{ ucfirst(str_replace('_', ' ', $payment->statut ?? 'N/A')) }}
                                </span>
                            </td>
                            <td class="text-muted small">{{ $payment->created_at?->format('d/m/Y H:i') }}</td>
                            <td class="text-end pe-4">
                                <div class="btn-group">
                                    <a href="{{ route('admin.payments.show', $payment) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if($payment->statut === 'en_attente')
                                    <button wire:click="approuver({{ $payment->id }})" class="btn btn-sm btn-outline-success" title="Approuver">
                                        <i class="bi bi-check-lg"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-wallet fs-1 d-block mb-3"></i>
                                <p class="mb-0">Aucun paiement trouvé</p>
                                <a href="{{ route('admin.payments.create') }}" class="btn btn-sm btn-primary mt-3">
                                    <i class="bi bi-plus-lg me-1"></i>Nouveau paiement
                                </a>
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
