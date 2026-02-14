<div>

    <!-- Page Header -->
    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <h4>Ajouter un Motard</h4>
            <p class="text-muted small mb-0">Cr&eacute;er un nouveau compte motard</p>
        </div>
        <a href="{{ route('admin.motards.index') }}" class="btn btn-light">
            <i class="bi bi-arrow-left me-1"></i> Retour
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form wire:submit.prevent="save">
                <div class="row g-3">
                    <!-- Section: Informations Personnelles -->
                    <div class="col-12">
                        <h6 class="fw-semibold text-primary mb-0"><i class="bi bi-person me-1"></i> Informations Personnelles</h6>
                        <hr class="mt-2">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Nom Complet <span class="text-danger">*</span></label>
                        <input type="text" wire:model="name" class="form-control @error('name') is-invalid @enderror" placeholder="Nom complet du motard">
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Adresse Email <span class="text-danger">*</span></label>
                        <input type="email" wire:model="email" class="form-control @error('email') is-invalid @enderror" placeholder="email@exemple.com">
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">T&eacute;l&eacute;phone</label>
                        <input type="text" wire:model="phone" class="form-control @error('phone') is-invalid @enderror" placeholder="+237 6XX XXX XXX">
                        @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Mot de passe <span class="text-danger">*</span></label>
                        <input type="password" wire:model="password" class="form-control @error('password') is-invalid @enderror" placeholder="Min. 6 caract&egrave;res">
                        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <!-- Section: Informations Professionnelles -->
                    <div class="col-12 mt-4">
                        <h6 class="fw-semibold text-primary mb-0"><i class="bi bi-card-text me-1"></i> Informations Professionnelles</h6>
                        <hr class="mt-2">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Num&eacute;ro d'Identifiant <span class="text-danger">*</span></label>
                        <input type="text" wire:model="numero_identifiant" class="form-control @error('numero_identifiant') is-invalid @enderror" placeholder="ID unique du motard">
                        @error('numero_identifiant') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Num&eacute;ro de Licence</label>
                        <input type="text" wire:model="licence_numero" class="form-control @error('licence_numero') is-invalid @enderror" placeholder="Num&eacute;ro de licence">
                        @error('licence_numero') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Zone d'Affectation <span class="text-danger">*</span></label>
                        <input type="text" wire:model="zone_affectation" class="form-control @error('zone_affectation') is-invalid @enderror" placeholder="Zone d'affectation">
                        @error('zone_affectation') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Statut</label>
                        <div class="form-check form-switch mt-2">
                            <input type="checkbox" wire:model="is_active" class="form-check-input" id="is_active">
                            <label class="form-check-label" for="is_active">Actif</label>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="col-12 mt-4">
                        <hr>
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="save"><i class="bi bi-check-lg me-1"></i> Enregistrer</span>
                            <span wire:loading wire:target="save"><span class="spinner-border spinner-border-sm me-1"></span> Enregistrement...</span>
                        </button>
                        <a href="{{ route('admin.motards.index') }}" class="btn btn-light ms-2">Annuler</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
