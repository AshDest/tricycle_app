<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-person-plus me-2 text-primary"></i>Nouveau Caissier
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.caissiers.index') }}">Caissiers</a></li>
                    <li class="breadcrumb-item active">Nouveau</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('admin.caissiers.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Retour
        </a>
    </div>

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-4">
        <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <form wire:submit="save">
        <div class="row">
            <!-- Informations de l'utilisateur -->
            <div class="col-lg-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bi bi-person me-2"></i>Informations de l'utilisateur</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nom complet <span class="text-danger">*</span></label>
                            <input type="text" wire:model="name" class="form-control @error('name') is-invalid @enderror" placeholder="Nom du caissier">
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                            <input type="email" wire:model="email" class="form-control @error('email') is-invalid @enderror" placeholder="email@exemple.com">
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Téléphone</label>
                            <input type="text" wire:model="phone" class="form-control @error('phone') is-invalid @enderror" placeholder="+243...">
                            @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Mot de passe <span class="text-danger">*</span></label>
                            <input type="password" wire:model="password" class="form-control @error('password') is-invalid @enderror" placeholder="Minimum 6 caractères">
                            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informations du caissier -->
            <div class="col-lg-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bi bi-cash-coin me-2"></i>Informations du point de collecte</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Numéro identifiant <span class="text-danger">*</span></label>
                            <input type="text" wire:model="numero_identifiant" class="form-control @error('numero_identifiant') is-invalid @enderror" placeholder="CAI-001">
                            @error('numero_identifiant') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nom du point de collecte <span class="text-danger">*</span></label>
                            <input type="text" wire:model="nom_point_collecte" class="form-control @error('nom_point_collecte') is-invalid @enderror" placeholder="Point de collecte Gombe">
                            @error('nom_point_collecte') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Zone <span class="text-danger">*</span></label>
                                    <select wire:model="zone_id" class="form-select @error('zone_id') is-invalid @enderror">
                                        <option value="">-- Sélectionner une zone --</option>
                                        @foreach($zones as $zone)
                                        <option value="{{ $zone->id }}">{{ $zone->nom }}</option>
                                        @endforeach
                                    </select>
                                    @error('zone_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Téléphone du point <span class="text-danger">*</span></label>
                                    <input type="text" wire:model="telephone" class="form-control @error('telephone') is-invalid @enderror" placeholder="+243...">
                                    @error('telephone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Adresse <span class="text-danger">*</span></label>
                            <textarea wire:model="adresse" class="form-control @error('adresse') is-invalid @enderror" rows="2" placeholder="Adresse complète du point de collecte"></textarea>
                            @error('adresse') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-check form-switch">
                            <input type="checkbox" wire:model="is_active" class="form-check-input" id="is_active">
                            <label class="form-check-label fw-semibold" for="is_active">Actif</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Boutons d'action -->
        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('admin.caissiers.index') }}" class="btn btn-light">
                <i class="bi bi-x-lg me-1"></i>Annuler
            </a>
            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                <span wire:loading.remove>
                    <i class="bi bi-check-lg me-1"></i>Enregistrer
                </span>
                <span wire:loading>
                    <span class="spinner-border spinner-border-sm me-1"></span>Enregistrement...
                </span>
            </button>
        </div>
    </form>
</div>
