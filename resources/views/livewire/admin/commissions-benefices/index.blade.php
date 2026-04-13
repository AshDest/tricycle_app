<div>
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-clipboard-check me-2 text-primary"></i>Validation Commissions & Bénéfices
            </h4>
            <p class="text-muted mb-0">Validation et audit des commissions Mobile Money et bénéfices de change</p>
        </div>
        <button wire:click="exportPdf" class="btn btn-danger">
            <i class="bi bi-file-pdf me-1"></i>Export PDF
        </button>
    </div>
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif
    <!-- Stats -->
    @php $tauxUsd = \App\Models\SystemSetting::getTauxUsdCdf(); @endphp
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card border-success">
                <div class="card-header bg-success text-white py-2">
                    <h6 class="mb-0"><i class="bi bi-percent me-2"></i>Commissions Mobile Money</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center mb-3">
                        <div class="col-4">
                            <h4 class="text-warning mb-0">{{ $statsCommissions['en_attente'] }}</h4>
                            <small class="text-muted">En attente</small>
                        </div>
                        <div class="col-4">
                            <h4 class="text-success mb-0">{{ $statsCommissions['valide'] }}</h4>
                            <small class="text-muted">Validées</small>
                        </div>
                        <div class="col-4">
                            <h4 class="text-primary mb-0">{{ $tauxUsd > 0 ? number_format($statsCommissions['total'] / $tauxUsd, 2) : '0.00' }}</h4>
                            <small class="text-muted">Total $</small>
                        </div>
                    </div>
                    <!-- Répartition 70/30 -->
                    <div class="alert alert-light border mb-0">
                        <h6 class="mb-2"><i class="bi bi-pie-chart me-1"></i>Répartition des Commissions Validées</h6>
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="text-success fw-bold">{{ $tauxUsd > 0 ? number_format(($statsCommissions['part_nth'] ?? 0) / $tauxUsd, 2) : '0.00' }} $</div>
                                <small class="text-muted">Part LATEM (70%)</small>
                            </div>
                            <div class="col-4">
                                <div class="fw-bold" style="color: #6f42c1;">{{ $tauxUsd > 0 ? number_format(($statsCommissions['part_okami'] ?? 0) / $tauxUsd, 2) : '0.00' }} $</div>
                                <small class="text-muted">Part OKAMI (30%)</small>
                            </div>
                            <div class="col-4">
                                <div class="text-info fw-bold">{{ $tauxUsd > 0 ? number_format(($statsCommissions['solde_okami_disponible'] ?? 0) / $tauxUsd, 2) : '0.00' }} $</div>
                                <small class="text-muted">Disponible OKAMI</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-info">
                <div class="card-header bg-info text-white py-2">
                    <h6 class="mb-0"><i class="bi bi-currency-exchange me-2"></i>Bénéfices de Change</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-4">
                            <h4 class="text-warning mb-0">{{ $statsBenefices['en_attente'] }}</h4>
                            <small class="text-muted">En attente</small>
                        </div>
                        <div class="col-4">
                            <h4 class="text-success mb-0">{{ $statsBenefices['valide'] }}</h4>
                            <small class="text-muted">Validés</small>
                        </div>
                        <div class="col-4">
                            <h4 class="text-primary mb-0">{{ number_format($statsBenefices['total']) }}</h4>
                            <small class="text-muted">Total FC</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Tabs -->
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <button class="nav-link {{ $activeTab === 'commissions' ? 'active' : '' }}" wire:click="$set('activeTab', 'commissions')">
                <i class="bi bi-percent me-1"></i>Commissions
                @if($statsCommissions['en_attente'] > 0)
                <span class="badge bg-warning ms-1">{{ $statsCommissions['en_attente'] }}</span>
                @endif
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link {{ $activeTab === 'benefices' ? 'active' : '' }}" wire:click="$set('activeTab', 'benefices')">
                <i class="bi bi-currency-exchange me-1"></i>Bénéfices Change
                @if($statsBenefices['en_attente'] > 0)
                <span class="badge bg-warning ms-1">{{ $statsBenefices['en_attente'] }}</span>
                @endif
            </button>
        </li>
    </ul>
    <!-- Filtres -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Recherche collecteur</label>
                    <input type="text" wire:model.live="search" class="form-control" placeholder="Nom du collecteur...">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Statut</label>
                    <select wire:model.live="filterStatut" class="form-select">
                        <option value="">Tous</option>
                        <option value="en_attente">En attente</option>
                        <option value="valide">Validé</option>
                        <option value="rejete">Rejeté</option>
                    </select>
                </div>
                @if($activeTab === 'commissions')
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Année</label>
                    <select wire:model.live="filterAnnee" class="form-select">
                        @for($y = now()->year; $y >= 2020; $y--)
                        <option value="{{ $y }}">{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                @endif
            </div>
        </div>
    </div>
    <!-- Contenu onglet Commissions -->
    @if($activeTab === 'commissions')
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Collecteur</th>
                            <th>Période</th>
                            <th class="text-end">Montant Total</th>
                            <th class="text-end">LATEM (70%)</th>
                            <th class="text-end">OKAMI (30%)</th>
                            <th>Preuve</th>
                            <th>Statut</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($commissions as $com)
                        <tr>
                            <td class="ps-4">
                                <span class="fw-medium">{{ $com->collecteur->user->name ?? 'N/A' }}</span>
                                <small class="d-block text-muted">{{ $com->numero_reference }}</small>
                            </td>
                            <td><span class="badge bg-secondary">{{ $com->periode_label }}</span></td>
                            <td class="text-end fw-bold text-success">{{ $tauxUsd > 0 ? number_format($com->montant_total / $tauxUsd, 2) : '0.00' }} $</td>
                            <td class="text-end text-primary">{{ $tauxUsd > 0 ? number_format($com->part_nth / $tauxUsd, 2) : '0.00' }} $</td>
                            <td class="text-end text-warning">{{ $tauxUsd > 0 ? number_format($com->part_okami / $tauxUsd, 2) : '0.00' }} $</td>
                            <td>
                                @if($com->preuve_paiement)
                                <a href="{{ Storage::url($com->preuve_paiement) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-file-earmark"></i>
                                </a>
                                @endif
                            </td>
                            <td><span class="badge bg-{{ $com->statut_badge }}">{{ $com->statut_label }}</span></td>
                            <td class="text-end pe-4">
                                @if($com->statut === 'en_attente')
                                <button wire:click="ouvrirValidation('commission', {{ $com->id }}, 'valider')" class="btn btn-sm btn-success" title="Valider">
                                    <i class="bi bi-check-lg"></i>
                                </button>
                                <button wire:click="ouvrirValidation('commission', {{ $com->id }}, 'rejeter')" class="btn btn-sm btn-danger" title="Rejeter">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="8" class="text-center py-5 text-muted">Aucune commission</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if(method_exists($commissions, 'hasPages') && $commissions->hasPages())
        <div class="card-footer bg-light">{{ $commissions->links() }}</div>
        @endif
    </div>
    @endif
    <!-- Contenu onglet Bénéfices -->
    @if($activeTab === 'benefices')
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Collecteur</th>
                            <th>Type</th>
                            <th>Période</th>
                            <th class="text-end">Bénéfice</th>
                            <th>Statut</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($benefices as $b)
                        <tr>
                            <td class="ps-4">
                                <span class="fw-medium">{{ $b->collecteur->user->name ?? 'N/A' }}</span>
                                <small class="d-block text-muted">{{ $b->numero_reference }}</small>
                            </td>
                            <td><span class="badge bg-{{ $b->type_saisie_badge }}">{{ $b->type_saisie_label }}</span></td>
                            <td>{{ $b->periode_label }}</td>
                            <td class="text-end fw-bold text-success">{{ number_format($b->benefice) }} FC</td>
                            <td><span class="badge bg-{{ $b->statut_badge }}">{{ $b->statut_label }}</span></td>
                            <td class="text-end pe-4">
                                @if($b->statut === 'en_attente')
                                <button wire:click="ouvrirValidation('benefice', {{ $b->id }}, 'valider')" class="btn btn-sm btn-success">
                                    <i class="bi bi-check-lg"></i>
                                </button>
                                <button wire:click="ouvrirValidation('benefice', {{ $b->id }}, 'rejeter')" class="btn btn-sm btn-danger">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center py-5 text-muted">Aucun bénéfice</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if(method_exists($benefices, 'hasPages') && $benefices->hasPages())
        <div class="card-footer bg-light">{{ $benefices->links() }}</div>
        @endif
    </div>
    @endif
    <!-- Modal Validation -->
    @if($showValidationModal && $itemToValidate)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-{{ $actionType === 'valider' ? 'success' : 'danger' }} text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-{{ $actionType === 'valider' ? 'check-circle' : 'x-circle' }} me-2"></i>
                        {{ $actionType === 'valider' ? 'Confirmer la validation' : 'Confirmer le rejet' }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="fermerModal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-light">
                        <strong>{{ $itemType === 'commission' ? 'Commission' : 'Bénéfice' }}:</strong>
                        {{ $itemToValidate->numero_reference ?? 'N/A' }}<br>
                        <strong>Collecteur:</strong> {{ $itemToValidate->collecteur->user->name ?? 'N/A' }}<br>
                        <strong>Montant:</strong>
                        <span class="text-success fw-bold">
                            @if($itemType === 'commission')
                                {{ $tauxUsd > 0 ? number_format($itemToValidate->montant_total / $tauxUsd, 2) : '0.00' }} $
                            @else
                                {{ number_format($itemToValidate->benefice) }} FC
                            @endif
                        </span>
                    </div>
                    @if($actionType === 'rejeter')
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Motif du rejet <span class="text-danger">*</span></label>
                        <textarea wire:model="motifRejet" class="form-control @error('motifRejet') is-invalid @enderror" rows="3" placeholder="Expliquez la raison du rejet..."></textarea>
                        @error('motifRejet') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    @endif
                    <p class="text-muted small mb-0">
                        @if($actionType === 'valider')
                        Cette action marquera cet élément comme validé et sera enregistrée dans l'historique.
                        @else
                        Cette action marquera cet élément comme rejeté. Le collecteur sera informé du motif.
                        @endif
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" wire:click="fermerModal">Annuler</button>
                    <button wire:click="confirmerAction" class="btn btn-{{ $actionType === 'valider' ? 'success' : 'danger' }}">
                        <i class="bi bi-{{ $actionType === 'valider' ? 'check-lg' : 'x-lg' }} me-1"></i>
                        {{ $actionType === 'valider' ? 'Valider' : 'Rejeter' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
