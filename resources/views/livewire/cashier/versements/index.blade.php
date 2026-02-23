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

    <!-- Messages Flash -->
    @if (session()->has('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            </div>
            @if(session()->has('dernierVersementId'))
            <button wire:click="telechargerRecu({{ session('dernierVersementId') }})" class="btn btn-sm btn-success ms-3">
                <i class="bi bi-download me-1"></i>Télécharger le reçu
            </button>
            @endif
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if (session()->has('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
        <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

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
                        <option value="paye">Payé (complet)</option>
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
                            <th>Versé</th>
                            <th>Attendu</th>
                            <th>Écart</th>
                            <th>Mode</th>
                            <th>Statut</th>
                            <th>Heure</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($versements ?? [] as $versement)
                        @php
                            $ecart = ($versement->montant ?? 0) - ($versement->montant_attendu ?? 0);
                            $peutCompleter = $ecart < 0 && $versement->date_versement?->isToday();
                        @endphp
                        <tr class="{{ $ecart < 0 ? 'table-warning' : '' }}">
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
                            <td class="fw-bold {{ $ecart >= 0 ? 'text-success' : 'text-warning' }}">{{ number_format($versement->montant ?? 0) }} FC</td>
                            <td class="text-muted">{{ number_format($versement->montant_attendu ?? 0) }} FC</td>
                            <td>
                                @if($ecart >= 0)
                                <span class="text-success"><i class="bi bi-check-circle"></i> OK</span>
                                @else
                                <span class="text-danger fw-bold">{{ number_format($ecart) }} FC</span>
                                @endif
                            </td>
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
                                        'paye' => 'success',
                                        'payé' => 'success',
                                        'partiel' => 'warning',
                                        'partiellement_payé' => 'warning',
                                        'en_retard' => 'danger',
                                        'non_effectue' => 'secondary',
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
                                <div class="d-flex gap-1 justify-content-end">
                                    @if($peutCompleter)
                                    <button wire:click="ouvrirComplement({{ $versement->id }})" class="btn btn-sm btn-warning" title="Compléter le versement">
                                        <i class="bi bi-plus-circle"></i>
                                    </button>
                                    @endif
                                    <button wire:click="telechargerRecu({{ $versement->id }})" class="btn btn-sm btn-outline-danger" title="Télécharger le reçu">
                                        <i class="bi bi-receipt"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
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

    <!-- Modal de complément -->
    @if($showComplementModal && $versementACompleter)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-warning bg-opacity-10">
                    <h5 class="modal-title">
                        <i class="bi bi-plus-circle me-2 text-warning"></i>Compléter le Versement
                    </h5>
                    <button type="button" class="btn-close" wire:click="fermerComplement"></button>
                </div>
                <div class="modal-body">
                    <!-- Info Motard -->
                    <div class="d-flex align-items-center gap-3 p-3 bg-light rounded mb-4">
                        <div class="avatar avatar-md bg-warning bg-opacity-10 text-warning rounded-circle">
                            {{ strtoupper(substr($versementACompleter->motard->user->name ?? 'M', 0, 1)) }}
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold">{{ $versementACompleter->motard->user->name ?? 'N/A' }}</h6>
                            <small class="text-muted">{{ $versementACompleter->moto->plaque_immatriculation ?? '' }}</small>
                        </div>
                    </div>

                    <!-- Détails du versement -->
                    <div class="row g-3 mb-4">
                        <div class="col-6">
                            <div class="text-center p-3 bg-light rounded">
                                <small class="text-muted d-block">Montant versé</small>
                                <span class="fw-bold text-primary">{{ number_format($versementACompleter->montant ?? 0) }} FC</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-3 bg-light rounded">
                                <small class="text-muted d-block">Montant attendu</small>
                                <span class="fw-bold">{{ number_format($versementACompleter->montant_attendu ?? 0) }} FC</span>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-warning d-flex align-items-center gap-2 mb-4">
                        <i class="bi bi-exclamation-triangle fs-5"></i>
                        <div>
                            <strong>Montant manquant:</strong> {{ number_format($montantManquant) }} FC
                        </div>
                    </div>

                    <!-- Formulaire -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Montant du complément <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" wire:model="montantComplement" class="form-control form-control-lg @error('montantComplement') is-invalid @enderror" placeholder="0" min="1">
                            <span class="input-group-text">FC</span>
                        </div>
                        @error('montantComplement')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <div class="d-flex gap-2 mt-2">
                            <button type="button" wire:click="$set('montantComplement', {{ $montantManquant }})" class="btn btn-sm btn-outline-warning">
                                Montant exact ({{ number_format($montantManquant) }} FC)
                            </button>
                        </div>
                    </div>

                    @if($montantComplement && $montantComplement > 0)
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Nouveau total après complément: <strong>{{ number_format(($versementACompleter->montant ?? 0) + (float)$montantComplement) }} FC</strong>
                        @if((float)$montantComplement >= $montantManquant)
                        <span class="badge bg-success ms-2">Complet</span>
                        @endif
                    </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" wire:click="fermerComplement">
                        <i class="bi bi-x-lg me-1"></i>Annuler
                    </button>
                    <button type="button" class="btn btn-warning" wire:click="enregistrerComplement" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="enregistrerComplement">
                            <i class="bi bi-check-lg me-1"></i>Enregistrer le Complément
                        </span>
                        <span wire:loading wire:target="enregistrerComplement">
                            <span class="spinner-border spinner-border-sm me-1"></span>Enregistrement...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
