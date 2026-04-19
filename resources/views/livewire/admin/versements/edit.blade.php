<div>
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-pencil-square me-2 text-warning"></i>Modifier le Versement
            </h4>
            <p class="text-muted mb-0">{{ $versement->motard?->user?->name ?? 'N/A' }} — {{ $versement->date_versement?->format('d/m/Y') }}</p>
        </div>
        <a href="{{ route('admin.versements.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Retour
        </a>
    </div>

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-4">
        <i class="bi bi-x-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="card">
        <div class="card-header py-3">
            <h6 class="mb-0 fw-bold"><i class="bi bi-cash-stack me-2 text-success"></i>Détails du Versement</h6>
        </div>
        <div class="card-body">
            <form wire:submit="save">
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Motard</label>
                        <input type="text" class="form-control" value="{{ $versement->motard?->user?->name ?? 'N/A' }}" disabled>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Moto</label>
                        <input type="text" class="form-control" value="{{ $versement->moto?->plaque_immatriculation ?? 'N/A' }}" disabled>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Date du versement <span class="text-danger">*</span></label>
                        <input type="date" wire:model="date_versement" class="form-control @error('date_versement') is-invalid @enderror">
                        @error('date_versement') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Mode de paiement <span class="text-danger">*</span></label>
                        <select wire:model="mode_paiement" class="form-select @error('mode_paiement') is-invalid @enderror">
                            <option value="cash">Cash</option>
                            <option value="mobile_money">Mobile Money</option>
                            <option value="depot">Dépôt</option>
                        </select>
                        @error('mode_paiement') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Montant attendu <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" wire:model="montant_attendu" class="form-control @error('montant_attendu') is-invalid @enderror" min="0" step="1" onkeydown="if(event.key==='.' || event.key===',') event.preventDefault();">
                            <span class="input-group-text">FC</span>
                        </div>
                        @error('montant_attendu') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Montant versé <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" wire:model="montant" class="form-control @error('montant') is-invalid @enderror" min="0" step="1" onkeydown="if(event.key==='.' || event.key===',') event.preventDefault();">
                            <span class="input-group-text">FC</span>
                        </div>
                        @error('montant') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Statut <span class="text-danger">*</span></label>
                        <select wire:model="statut" class="form-select @error('statut') is-invalid @enderror">
                            <option value="payé">Payé</option>
                            <option value="partiellement_payé">Partiellement payé</option>
                            <option value="en_retard">En retard</option>
                            <option value="non_effectué">Non effectué</option>
                        </select>
                        @error('statut') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Caissier</label>
                        <input type="text" class="form-control" value="{{ $versement->caissier?->user?->name ?? 'N/A' }}" disabled>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Notes</label>
                        <textarea wire:model="notes" class="form-control @error('notes') is-invalid @enderror" rows="3" placeholder="Observations..."></textarea>
                        @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('admin.versements.index') }}" class="btn btn-outline-secondary">Annuler</a>
                    <button type="submit" class="btn btn-warning" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="save"><i class="bi bi-check-circle me-1"></i>Enregistrer</span>
                        <span wire:loading wire:target="save"><span class="spinner-border spinner-border-sm me-1"></span>Enregistrement...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

