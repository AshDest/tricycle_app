<div>
    @section('title', 'Modifier la Moto')

    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <h4>Modifier la Moto</h4>
            <p class="text-muted small mb-0">{{ $moto->plaque_immatriculation }}</p>
        </div>
        <a href="{{ route('admin.motos.index') }}" class="btn btn-light"><i class="bi bi-arrow-left me-1"></i> Retour</a>
    </div>

    <div class="card">
        <div class="card-body">
            <form wire:submit.prevent="save">
                <div class="row g-3">
                    <div class="col-12">
                        <h6 class="fw-semibold text-primary mb-0"><i class="bi bi-card-text me-1"></i> Identification</h6>
                        <hr class="mt-2">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Num&eacute;ro Matricule <span class="text-danger">*</span></label>
                        <input type="text" wire:model="numero_matricule" class="form-control @error('numero_matricule') is-invalid @enderror">
                        @error('numero_matricule') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Plaque d'Immatriculation <span class="text-danger">*</span></label>
                        <input type="text" wire:model="plaque_immatriculation" class="form-control @error('plaque_immatriculation') is-invalid @enderror">
                        @error('plaque_immatriculation') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Num&eacute;ro de Chassis</label>
                        <input type="text" wire:model="numero_chassis" class="form-control @error('numero_chassis') is-invalid @enderror">
                        @error('numero_chassis') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 mt-4">
                        <h6 class="fw-semibold text-primary mb-0"><i class="bi bi-people me-1"></i> Affectation</h6>
                        <hr class="mt-2">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Propri&eacute;taire <span class="text-danger">*</span></label>
                        <select wire:model="proprietaire_id" class="form-select @error('proprietaire_id') is-invalid @enderror">
                            <option value="">-- S&eacute;lectionner --</option>
                            @foreach($proprietaires as $prop)
                                <option value="{{ $prop->id }}">{{ $prop->user->name ?? 'N/A' }}</option>
                            @endforeach
                        </select>
                        @error('proprietaire_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Motard</label>
                        <select wire:model="motard_id" class="form-select @error('motard_id') is-invalid @enderror">
                            <option value="">-- Aucun --</option>
                            @foreach($motards as $motard)
                                <option value="{{ $motard->id }}">{{ $motard->user->name ?? 'N/A' }}</option>
                            @endforeach
                        </select>
                        @error('motard_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 mt-4">
                        <h6 class="fw-semibold text-primary mb-0"><i class="bi bi-cash-stack me-1"></i> Finance & Statut</h6>
                        <hr class="mt-2">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Montant Journalier Attendu <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" wire:model="montant_journalier_attendu" class="form-control @error('montant_journalier_attendu') is-invalid @enderror">
                            <span class="input-group-text">FCFA</span>
                        </div>
                        @error('montant_journalier_attendu') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Statut <span class="text-danger">*</span></label>
                        <select wire:model="statut" class="form-select @error('statut') is-invalid @enderror">
                            <option value="actif">Actif</option>
                            <option value="suspendu">Suspendu</option>
                            <option value="maintenance">En maintenance</option>
                        </select>
                        @error('statut') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 mt-4">
                        <hr>
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="save"><i class="bi bi-check-lg me-1"></i> Mettre &agrave; jour</span>
                            <span wire:loading wire:target="save"><span class="spinner-border spinner-border-sm me-1"></span> Mise &agrave; jour...</span>
                        </button>
                        <a href="{{ route('admin.motos.index') }}" class="btn btn-light ms-2">Annuler</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
