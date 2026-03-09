<div>
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-currency-exchange me-2 text-info"></i>Bénéfices de Change
            </h4>
            <p class="text-muted mb-0">Suivi des bénéfices sur les opérations de change de monnaie</p>
        </div>
        <div class="d-flex gap-2">
            <button wire:click="exportExcel" class="btn btn-success">
                <i class="bi bi-file-earmark-excel me-1"></i>Excel
            </button>
            <button wire:click="exportPdf" class="btn btn-danger">
                <i class="bi bi-file-pdf me-1"></i>PDF
            </button>
            <button wire:click="ouvrirModal" class="btn btn-info">
                <i class="bi bi-plus-lg me-1"></i>Nouveau Bénéfice
            </button>
        </div>
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
        <div class="col-sm-6 col-lg-3">
            <div class="card bg-primary bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <i class="bi bi-calendar-day fs-3 text-primary"></i>
                    <h4 class="fw-bold text-primary mb-1">{{ number_format($totalBeneficeJournalier) }} FC</h4>
                    <small class="text-muted">Journalier</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card bg-info bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <i class="bi bi-calendar-week fs-3 text-info"></i>
                    <h4 class="fw-bold text-info mb-1">{{ number_format($totalBeneficeHebdo) }} FC</h4>
                    <small class="text-muted">Hebdomadaire</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card bg-success bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <i class="bi bi-calendar-month fs-3 text-success"></i>
                    <h4 class="fw-bold text-success mb-1">{{ number_format($totalBeneficeMensuel) }} FC</h4>
                    <small class="text-muted">Mensuel</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card bg-warning bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <i class="bi bi-cash-coin fs-3 text-warning"></i>
                    <h4 class="fw-bold text-warning mb-1">{{ number_format($totalBeneficePeriode) }} FC</h4>
                    <small class="text-muted">Total Période</small>
                </div>
            </div>
        </div>
    </div>
    <!-- Filtres -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Type</label>
                    <select wire:model.live="filterType" class="form-select">
                        <option value="">Tous</option>
                        @foreach($typesSaisie as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Statut</label>
                    <select wire:model.live="filterStatut" class="form-select">
                        <option value="">Tous</option>
                        <option value="en_attente">En attente</option>
                        <option value="valide">Validé</option>
                        <option value="rejete">Rejeté</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Du</label>
                    <input type="date" wire:model.live="dateDebut" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Au</label>
                    <input type="date" wire:model.live="dateFin" class="form-control">
                </div>
            </div>
        </div>
    </div>
    <!-- Liste -->
    <div class="card">
        <div class="card-header py-3">
            <h6 class="mb-0 fw-bold">Liste des bénéfices</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Référence</th>
                            <th>Type</th>
                            <th>Période</th>
                            <th class="text-end">Montant Caissier</th>
                            <th class="text-end">Solde Caisse</th>
                            <th class="text-end">Bénéfice</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($benefices as $b)
                        <tr>
                            <td class="ps-4">
                                <span class="fw-medium">{{ $b->numero_reference }}</span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $b->type_saisie_badge }}">{{ $b->type_saisie_label }}</span>
                            </td>
                            <td>{{ $b->periode_label }}</td>
                            <td class="text-end">
                                @if($b->montant_recu_caissier)
                                {{ number_format($b->montant_recu_caissier) }} FC
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @if($b->solde_general_caisse)
                                {{ number_format($b->solde_general_caisse) }} FC
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-end fw-bold text-success">{{ number_format($b->benefice) }} FC</td>
                            <td>
                                <span class="badge bg-{{ $b->statut_badge }}">{{ $b->statut_label }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-currency-exchange fs-1 d-block mb-3"></i>
                                <p class="mb-0">Aucun bénéfice enregistré</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($benefices->hasPages())
        <div class="card-footer bg-light">
            {{ $benefices->links() }}
        </div>
        @endif
    </div>
    <!-- Modal Création -->
    @if($showCreateModal)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-plus-circle me-2"></i>Nouveau Bénéfice de Change
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="fermerModal"></button>
                </div>
                <form wire:submit="enregistrerBenefice">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Type de saisie <span class="text-danger">*</span></label>
                                <div class="btn-group w-100" role="group">
                                    <input type="radio" class="btn-check" wire:model.live="type_saisie" value="journalier" id="typeJ">
                                    <label class="btn btn-outline-primary" for="typeJ">Journalier</label>
                                    <input type="radio" class="btn-check" wire:model.live="type_saisie" value="hebdomadaire" id="typeH">
                                    <label class="btn btn-outline-info" for="typeH">Hebdomadaire</label>
                                    <input type="radio" class="btn-check" wire:model.live="type_saisie" value="mensuel" id="typeM">
                                    <label class="btn btn-outline-success" for="typeM">Mensuel</label>
                                </div>
                            </div>
                            @if($type_saisie === 'journalier')
                            <div class="col-12">
                                <label class="form-label fw-semibold">Date de l'opération <span class="text-danger">*</span></label>
                                <input type="date" wire:model="date_operation" class="form-control @error('date_operation') is-invalid @enderror">
                                @error('date_operation') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            @else
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Début de période <span class="text-danger">*</span></label>
                                <input type="date" wire:model="periode_debut" class="form-control @error('periode_debut') is-invalid @enderror">
                                @error('periode_debut') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Fin de période <span class="text-danger">*</span></label>
                                <input type="date" wire:model="periode_fin" class="form-control @error('periode_fin') is-invalid @enderror">
                                @error('periode_fin') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            @if($type_saisie !== 'journalier')
                            <div class="col-12">
                                <button type="button" wire:click="calculerBeneficeAuto" class="btn btn-outline-secondary btn-sm">
                                    <i class="bi bi-calculator me-1"></i>Calculer automatiquement
                                </button>
                                <small class="text-muted ms-2">Basé sur les saisies journalières validées</small>
                            </div>
                            @endif
                            @endif
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Montant reçu du caissier</label>
                                <div class="input-group">
                                    <input type="number" wire:model="montant_recu_caissier" class="form-control" placeholder="0" min="0">
                                    <span class="input-group-text">FC</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Solde général de la caisse</label>
                                <div class="input-group">
                                    <input type="number" wire:model="solde_general_caisse" class="form-control" placeholder="0" min="0">
                                    <span class="input-group-text">FC</span>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Bénéfice réalisé <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" wire:model="benefice" class="form-control form-control-lg @error('benefice') is-invalid @enderror" placeholder="0" min="0">
                                    <span class="input-group-text">FC</span>
                                </div>
                                @error('benefice') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Commentaire</label>
                                <textarea wire:model="commentaire" class="form-control" rows="2" placeholder="Remarques éventuelles..."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" wire:click="fermerModal">Annuler</button>
                        <button type="submit" class="btn btn-info" wire:loading.attr="disabled">
                            <span wire:loading.remove><i class="bi bi-check-lg me-1"></i>Enregistrer</span>
                            <span wire:loading><span class="spinner-border spinner-border-sm me-1"></span>...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
