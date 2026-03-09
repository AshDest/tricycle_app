<div>
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-percent me-2 text-success"></i>Commissions Mobile Money
            </h4>
            <p class="text-muted mb-0">Enregistrement mensuel des commissions (70% NTH / 30% OKAMI)</p>
        </div>
        <div class="d-flex gap-2">
            <button wire:click="exportPdf" class="btn btn-danger">
                <i class="bi bi-file-pdf me-1"></i>PDF
            </button>
            <button wire:click="ouvrirModal" class="btn btn-success">
                <i class="bi bi-plus-lg me-1"></i>Nouvelle Commission
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
        <div class="col-sm-6 col-lg-4">
            <div class="card bg-success bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <i class="bi bi-cash-stack fs-3 text-success"></i>
                    <h4 class="fw-bold text-success mb-1">{{ number_format($totalCommissions) }} FC</h4>
                    <small class="text-muted">Total Commissions {{ $filterAnnee }}</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-4">
            <div class="card bg-primary bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <i class="bi bi-building fs-3 text-primary"></i>
                    <h4 class="fw-bold text-primary mb-1">{{ number_format($totalPartNth) }} FC</h4>
                    <small class="text-muted">Part NTH (70%)</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-4">
            <div class="card bg-warning bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <i class="bi bi-star fs-3 text-warning"></i>
                    <h4 class="fw-bold text-warning mb-1">{{ number_format($totalPartOkami) }} FC</h4>
                    <small class="text-muted">Part OKAMI (30%)</small>
                </div>
            </div>
        </div>
    </div>
    <!-- Filtres -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Année</label>
                    <select wire:model.live="filterAnnee" class="form-select">
                        @foreach($annees as $a)
                        <option value="{{ $a }}">{{ $a }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Statut</label>
                    <select wire:model.live="filterStatut" class="form-select">
                        <option value="">Tous</option>
                        <option value="en_attente">En attente</option>
                        <option value="valide">Validé</option>
                        <option value="rejete">Rejeté</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    <!-- Liste -->
    <div class="card">
        <div class="card-header py-3">
            <h6 class="mb-0 fw-bold">Commissions mensuelles</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Référence</th>
                            <th>Période</th>
                            <th class="text-end">Montant Total</th>
                            <th class="text-end">Part NTH (70%)</th>
                            <th class="text-end">Part OKAMI (30%)</th>
                            <th>Preuve</th>
                            <th>Statut</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($commissions as $com)
                        <tr>
                            <td class="ps-4">
                                <span class="fw-medium">{{ $com->numero_reference }}</span>
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ $com->periode_label }}</span>
                            </td>
                            <td class="text-end fw-bold text-success">{{ number_format($com->montant_total) }} FC</td>
                            <td class="text-end text-primary">{{ number_format($com->part_nth) }} FC</td>
                            <td class="text-end text-warning">{{ number_format($com->part_okami) }} FC</td>
                            <td>
                                @if($com->preuve_paiement)
                                <a href="{{ Storage::url($com->preuve_paiement) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-file-earmark"></i>
                                </a>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $com->statut_badge }}">{{ $com->statut_label }}</span>
                            </td>
                            <td>
                                <small>{{ $com->created_at?->format('d/m/Y') }}</small>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-percent fs-1 d-block mb-3"></i>
                                <p class="mb-0">Aucune commission enregistrée</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($commissions->hasPages())
        <div class="card-footer bg-light">
            {{ $commissions->links() }}
        </div>
        @endif
    </div>
    <!-- Modal Création -->
    @if($showCreateModal)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-plus-circle me-2"></i>Nouvelle Commission Mensuelle
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="fermerModal"></button>
                </div>
                <form wire:submit="enregistrerCommission">
                    <div class="modal-body">
                        <div class="alert alert-info mb-4">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Répartition automatique:</strong> 70% pour NTH, 30% pour OKAMI
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Année <span class="text-danger">*</span></label>
                                <select wire:model="annee" class="form-select @error('annee') is-invalid @enderror">
                                    @foreach($annees as $a)
                                    <option value="{{ $a }}">{{ $a }}</option>
                                    @endforeach
                                </select>
                                @error('annee') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Mois <span class="text-danger">*</span></label>
                                <select wire:model="mois" class="form-select @error('mois') is-invalid @enderror">
                                    @foreach($moisList as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('mois') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Montant total de la commission <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" wire:model.live="montant_total" class="form-control form-control-lg @error('montant_total') is-invalid @enderror" placeholder="0" min="1">
                                    <span class="input-group-text">FC</span>
                                </div>
                                @error('montant_total') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                            <!-- Preview répartition -->
                            @if($montant_total && is_numeric($montant_total) && $montant_total > 0)
                            <div class="col-12">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <div class="alert alert-primary mb-0 py-2 text-center">
                                            <small class="d-block">Part NTH (70%)</small>
                                            <strong>{{ number_format($previewNth) }} FC</strong>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="alert alert-warning mb-0 py-2 text-center">
                                            <small class="d-block">Part OKAMI (30%)</small>
                                            <strong>{{ number_format($previewOkami) }} FC</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif
                            <div class="col-12">
                                <label class="form-label fw-semibold">Preuve de paiement <span class="text-danger">*</span></label>
                                <input type="file" wire:model="preuve_paiement" class="form-control @error('preuve_paiement') is-invalid @enderror" accept=".jpg,.jpeg,.png,.pdf">
                                @error('preuve_paiement') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <small class="text-muted">Formats acceptés: JPG, PNG, PDF (max 5 Mo)</small>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Commentaire</label>
                                <textarea wire:model="commentaire" class="form-control" rows="2" placeholder="Remarques éventuelles..."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" wire:click="fermerModal">Annuler</button>
                        <button type="submit" class="btn btn-success" wire:loading.attr="disabled">
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
