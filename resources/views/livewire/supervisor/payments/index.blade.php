<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-wallet2 me-2 text-success"></i>Gestion des Paiements Propriétaires
            </h4>
            <p class="text-muted mb-0">Demandes de paiement et validations</p>
        </div>
        <a href="{{ route('supervisor.payments.create') }}" class="btn btn-success">
            <i class="bi bi-plus-lg me-1"></i>Nouvelle Demande
        </a>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-4">
        <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-4">
            <div class="card bg-warning bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="fw-bold text-warning mb-1">{{ $demandesEnAttente }}</h4>
                    <small class="text-muted">Demandes en attente</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-4">
            <div class="card bg-info bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="fw-bold text-info mb-1">{{ $paiementsAValider }}</h4>
                    <small class="text-muted">À valider</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-4">
            <div class="card bg-success bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="fw-bold text-success mb-1">{{ number_format($totalPaye) }} FC</h4>
                    <small class="text-muted">Total payé (validé)</small>
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
                    <input type="text" wire:model.live="search" class="form-control" placeholder="Propriétaire...">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Statut</label>
                    <select wire:model.live="filterStatut" class="form-select">
                        <option value="">Tous</option>
                        <option value="en_attente">En attente</option>
                        <option value="pay">Payé (à valider)</option>
                        <option value="approuve">Approuvé</option>
                        <option value="rejet">Rejeté</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Propriétaire</label>
                    <select wire:model.live="filterProprietaire" class="form-select">
                        <option value="">Tous</option>
                        @foreach($proprietaires as $prop)
                        <option value="{{ $prop->id }}">{{ $prop->user->name ?? $prop->raison_sociale }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button wire:click="resetFilters" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-x-lg me-1"></i>Réinitialiser
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
                            <th>Propriétaire</th>
                            <th>Montant demandé</th>
                            <th>Montant payé</th>
                            <th>Mode</th>
                            <th>N° Envoi</th>
                            <th>Statut</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $payment)
                        <tr class="{{ $payment->statut === 'pay' ? 'table-info' : '' }}">
                            <td class="ps-4">
                                <span class="fw-medium">{{ $payment->date_demande?->format('d/m/Y') }}</span>
                                <small class="text-muted d-block">{{ $payment->created_at?->format('H:i') }}</small>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar avatar-sm bg-primary bg-opacity-10 text-primary rounded-circle">
                                        {{ strtoupper(substr($payment->proprietaire->user->name ?? 'P', 0, 1)) }}
                                    </div>
                                    <div>
                                        <span class="fw-medium d-block">{{ $payment->proprietaire->user->name ?? 'N/A' }}</span>
                                        <small class="text-muted">{{ $payment->proprietaire->telephone ?? '' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td class="fw-semibold">{{ number_format($payment->total_du) }} FC</td>
                            <td class="fw-semibold text-success">{{ number_format($payment->total_paye) }} FC</td>
                            <td>
                                <span class="badge bg-light text-dark">
                                    {{ \App\Models\Payment::getModesPaiement()[$payment->mode_paiement] ?? $payment->mode_paiement }}
                                </span>
                            </td>
                            <td>{{ $payment->numero_envoi ?? '-' }}</td>
                            <td>
                                @php
                                    $statutColors = [
                                        'en_attente' => 'warning',
                                        'pay' => 'primary',
                                        'approuve' => 'success',
                                        'rejet' => 'danger',
                                    ];
                                    $statutLabels = [
                                        'en_attente' => 'En attente',
                                        'pay' => 'Payé',
                                        'approuve' => 'Approuvé',
                                        'rejet' => 'Rejeté',
                                    ];
                                @endphp
                                <span class="badge badge-soft-{{ $statutColors[$payment->statut] ?? 'secondary' }}">
                                    {{ $statutLabels[$payment->statut] ?? $payment->statut }}
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                @if($payment->statut === 'pay')
                                <div class="btn-group">
                                    <button wire:click="validerPaiement({{ $payment->id }})"
                                            class="btn btn-sm btn-success"
                                            wire:confirm="Confirmer la validation de ce paiement ?">
                                        <i class="bi bi-check-lg"></i> Valider
                                    </button>
                                    <button wire:click="rejeterPaiement({{ $payment->id }}, 'Information incorrecte')"
                                            class="btn btn-sm btn-outline-danger"
                                            wire:confirm="Rejeter ce paiement ?">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                                @else
                                <button class="btn btn-sm btn-outline-secondary" disabled>
                                    <i class="bi bi-eye"></i>
                                </button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                <p class="mb-0">Aucun paiement trouvé</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($payments->hasPages())
        <div class="card-footer bg-light">
            {{ $payments->links() }}
        </div>
        @endif
    </div>
</div>
