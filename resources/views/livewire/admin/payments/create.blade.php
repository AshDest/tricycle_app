<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-plus-circle me-2 text-success"></i>Nouveau Paiement Propriétaire
            </h4>
            <p class="text-muted mb-0">Créer un paiement pour un propriétaire</p>
        </div>
        <a href="{{ route('admin.payments.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Retour
        </a>
    </div>

    <form wire:submit="save">
        <div class="row g-4">
            <div class="col-lg-8">
                <!-- Propriétaire -->
                <div class="card mb-4">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-person me-2 text-primary"></i>Propriétaire</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Sélectionner le propriétaire <span class="text-danger">*</span></label>
                                <select wire:model.live="proprietaire_id" class="form-select @error('proprietaire_id') is-invalid @enderror">
                                    <option value="">-- Choisir un propriétaire --</option>
                                    @foreach($proprietaires as $prop)
                                    <option value="{{ $prop->id }}">{{ $prop->user->name ?? $prop->raison_sociale }} - {{ $prop->motos->count() }} moto(s)</option>
                                    @endforeach
                                </select>
                                @error('proprietaire_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Période début</label>
                                <input type="date" wire:model.live="periode_debut" class="form-control @error('periode_debut') is-invalid @enderror">
                                @error('periode_debut')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Période fin</label>
                                <input type="date" wire:model.live="periode_fin" class="form-control @error('periode_fin') is-invalid @enderror">
                                @error('periode_fin')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mode de paiement -->
                <div class="card mb-4">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-credit-card me-2 text-info"></i>Mode de Paiement</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Mode de paiement <span class="text-danger">*</span></label>
                                <select wire:model.live="mode_paiement" class="form-select @error('mode_paiement') is-invalid @enderror">
                                    <option value="">-- Choisir --</option>
                                    <option value="mpesa">M-PESA</option>
                                    <option value="airtel_money">Airtel Money</option>
                                    <option value="orange_money">Orange Money</option>
                                    <option value="virement_bancaire">Virement Bancaire</option>
                                </select>
                                @error('mode_paiement')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Numéro de compte / téléphone</label>
                                <input type="text" wire:model="numero_compte" class="form-control" placeholder="+243 XXX XXX XXX">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Notes</label>
                                <textarea wire:model="notes" class="form-control" rows="3" placeholder="Commentaires..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar - Montants -->
            <div class="col-lg-4">
                <div class="card mb-4">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-cash-coin me-2 text-success"></i>Montants</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Total dû (calculé)</label>
                            <div class="input-group">
                                <input type="number" wire:model="total_du" class="form-control bg-light" readonly>
                                <span class="input-group-text">FC</span>
                            </div>
                            <small class="text-muted">Basé sur les versements de la période</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Montant à payer <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" wire:model="total_paye" class="form-control @error('total_paye') is-invalid @enderror" placeholder="0" min="0">
                                <span class="input-group-text">FC</span>
                            </div>
                            @error('total_paye')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                <!-- Boutons -->
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-success btn-lg" wire:loading.attr="disabled">
                        <span wire:loading.remove><i class="bi bi-check-lg me-2"></i>Créer le Paiement</span>
                        <span wire:loading><span class="spinner-border spinner-border-sm me-2"></span>Enregistrement...</span>
                    </button>
                    <a href="{{ route('admin.payments.index') }}" class="btn btn-outline-secondary">Annuler</a>
                </div>
            </div>
        </div>
    </form>
</div>
