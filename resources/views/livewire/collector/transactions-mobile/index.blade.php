<div>
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-phone me-2 text-primary"></i>Transactions Mobile Money
            </h4>
            <p class="text-muted mb-0">Suivi des envois et retraits d'argent mobile</p>
        </div>
        <div class="d-flex gap-2">
            <button wire:click="exportPdf" class="btn btn-danger">
                <i class="bi bi-file-pdf me-1"></i>PDF
            </button>
            <a href="{{ route('collector.transactions-mobile.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>Nouvelle Transaction
            </a>
        </div>
    </div>
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif
    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card bg-danger bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <i class="bi bi-arrow-up-circle fs-3 text-danger"></i>
                    <h4 class="fw-bold text-danger mb-1">{{ number_format($totalEnvois) }} FC</h4>
                    <small class="text-muted">Total Envois</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card bg-success bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <i class="bi bi-arrow-down-circle fs-3 text-success"></i>
                    <h4 class="fw-bold text-success mb-1">{{ number_format($totalRetraits) }} FC</h4>
                    <small class="text-muted">Total Retraits</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card bg-{{ $soldeNet >= 0 ? 'info' : 'warning' }} bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <i class="bi bi-wallet2 fs-3 text-{{ $soldeNet >= 0 ? 'info' : 'warning' }}"></i>
                    <h4 class="fw-bold text-{{ $soldeNet >= 0 ? 'info' : 'warning' }} mb-1">{{ number_format($soldeNet) }} FC</h4>
                    <small class="text-muted">Solde Net</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card bg-primary bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <i class="bi bi-list-ol fs-3 text-primary"></i>
                    <h4 class="fw-bold text-primary mb-1">{{ $nombreTransactions }}</h4>
                    <small class="text-muted">Transactions</small>
                </div>
            </div>
        </div>
    </div>
    <!-- Filtres -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Rechercher</label>
                    <input type="text" wire:model.live="search" class="form-control" placeholder="N°, téléphone...">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Type</label>
                    <select wire:model.live="filterType" class="form-select">
                        <option value="">Tous</option>
                        @foreach($types as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Opérateur</label>
                    <select wire:model.live="filterOperateur" class="form-select">
                        <option value="">Tous</option>
                        @foreach($operateurs as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Du</label>
                    <input type="date" wire:model.live="dateDebut" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Au</label>
                    <input type="date" wire:model.live="dateFin" class="form-control">
                </div>
                <div class="col-md-2">
                    <button wire:click="resetFilters" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-x-lg me-1"></i>Reset
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- Liste -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Transaction</th>
                            <th>Type</th>
                            <th>Opérateur</th>
                            <th>Bénéficiaire</th>
                            <th class="text-end">Montant</th>
                            <th>Statut</th>
                            <th class="text-end pe-4">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $tx)
                        <tr>
                            <td class="ps-4">
                                <span class="fw-medium">{{ $tx->numero_transaction }}</span>
                                @if($tx->reference_operateur)
                                <small class="d-block text-muted">Ref: {{ $tx->reference_operateur }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $tx->type_badge_color }}">
                                    <i class="bi bi-arrow-{{ $tx->type === 'envoi' ? 'up' : 'down' }} me-1"></i>
                                    {{ $tx->type_label }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $tx->operateur_color }}">{{ $tx->operateur_label }}</span>
                            </td>
                            <td>
                                <span>{{ $tx->numero_telephone }}</span>
                                @if($tx->nom_beneficiaire)
                                <small class="d-block text-muted">{{ $tx->nom_beneficiaire }}</small>
                                @endif
                            </td>
                            <td class="text-end">
                                <span class="fw-bold text-{{ $tx->type === 'envoi' ? 'danger' : 'success' }}">
                                    {{ $tx->type === 'envoi' ? '-' : '+' }}{{ number_format($tx->montant) }} FC
                                </span>
                                @if($tx->frais > 0)
                                <small class="d-block text-muted">Frais: {{ number_format($tx->frais) }} FC</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $tx->statut_badge_color }}">{{ $tx->statut_label }}</span>
                            </td>
                            <td class="text-end pe-4">
                                <small>{{ $tx->date_transaction->format('d/m/Y H:i') }}</small>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-phone fs-1 d-block mb-3"></i>
                                <p class="mb-0">Aucune transaction trouvée</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($transactions->hasPages())
        <div class="card-footer bg-light">
            {{ $transactions->links() }}
        </div>
        @endif
    </div>
</div>
