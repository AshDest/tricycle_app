<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-pencil-square me-2 text-primary"></i>Modifier le Motard
            </h4>
            <p class="text-muted mb-0">{{ $motard->user->name ?? 'Motard' }} - {{ $motard->numero_identifiant }}</p>
        </div>
        <a href="{{ route('supervisor.motards.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Retour à la liste
        </a>
    </div>

    <!-- Messages Flash -->
    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form wire:submit="save">
        <div class="row g-4">
            <!-- Informations du compte -->
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-person-circle me-2 text-primary"></i>Compte Utilisateur</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Nom complet <span class="text-danger">*</span></label>
                            <input type="text" wire:model="name" class="form-control @error('name') is-invalid @enderror">
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" wire:model="email" class="form-control @error('email') is-invalid @enderror">
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Téléphone</label>
                            <input type="text" wire:model="phone" class="form-control @error('phone') is-invalid @enderror">
                            @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nouveau mot de passe <small class="text-muted">(laisser vide pour ne pas changer)</small></label>
                            <input type="password" wire:model="password" class="form-control @error('password') is-invalid @enderror">
                            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Confirmer le nouveau mot de passe</label>
                            <input type="password" wire:model="password_confirmation" class="form-control">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informations motard -->
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-person-badge me-2 text-success"></i>Informations Motard</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Numéro d'identifiant <span class="text-danger">*</span></label>
                            <input type="text" wire:model="numero_identifiant" class="form-control @error('numero_identifiant') is-invalid @enderror">
                            @error('numero_identifiant') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Numéro de licence</label>
                            <input type="text" wire:model="licence_numero" class="form-control @error('licence_numero') is-invalid @enderror">
                            @error('licence_numero') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Zone d'affectation <span class="text-danger">*</span></label>
                            <select wire:model="zone_affectation" class="form-select @error('zone_affectation') is-invalid @enderror">
                                <option value="">Sélectionner une zone</option>
                                @foreach($zones as $zone)
                                    <option value="{{ $zone->nom }}">{{ $zone->nom }}</option>
                                @endforeach
                            </select>
                            @error('zone_affectation') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-check form-switch mt-4">
                            <input type="checkbox" wire:model="is_active" class="form-check-input" id="is_active" role="switch">
                            <label class="form-check-label" for="is_active">Compte actif</label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="col-12">
                <div class="card">
                    <div class="card-body d-flex justify-content-end gap-2">
                        <a href="{{ route('supervisor.motards.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-lg me-1"></i>Annuler
                        </a>
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="save">
                                <i class="bi bi-check-lg me-1"></i>Mettre à jour
                            </span>
                            <span wire:loading wire:target="save">
                                <span class="spinner-border spinner-border-sm me-1"></span>Mise à jour...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

