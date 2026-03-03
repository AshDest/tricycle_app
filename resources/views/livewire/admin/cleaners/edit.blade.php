<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-pencil me-2 text-primary"></i>Modifier le Laveur
            </h4>
            <p class="text-muted mb-0">{{ $cleaner->identifiant }} - {{ $cleaner->user->name ?? 'N/A' }}</p>
        </div>
        <a href="{{ route('admin.cleaners.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Retour
        </a>
    </div>

    <!-- Messages flash -->
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold">
                        <i class="bi bi-person me-2 text-info"></i>Informations du Laveur
                    </h6>
                </div>
                <div class="card-body">
                    <form wire:submit="save">
                        <!-- Informations personnelles -->
                        <h6 class="text-muted mb-3">Informations personnelles</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Nom complet <span class="text-danger">*</span></label>
                                <input type="text" wire:model="name" class="form-control @error('name') is-invalid @enderror">
                                @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" wire:model="email" class="form-control @error('email') is-invalid @enderror">
                                @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Contact et localisation -->
                        <h6 class="text-muted mb-3">Contact et localisation</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Téléphone</label>
                                <input type="text" wire:model="telephone" class="form-control @error('telephone') is-invalid @enderror">
                                @error('telephone')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Zone</label>
                                <select wire:model="zone" class="form-select @error('zone') is-invalid @enderror">
                                    <option value="">-- Sélectionner une zone --</option>
                                    @foreach($zones as $z)
                                    <option value="{{ $z->nom }}">{{ $z->nom }}</option>
                                    @endforeach
                                </select>
                                @error('zone')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label">Adresse</label>
                                <input type="text" wire:model="adresse" class="form-control @error('adresse') is-invalid @enderror">
                                @error('adresse')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Statut -->
                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" wire:model="is_active" id="isActive">
                                <label class="form-check-label" for="isActive">
                                    Laveur actif
                                </label>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="mb-4">
                            <label class="form-label">Notes</label>
                            <textarea wire:model="notes" class="form-control" rows="3"></textarea>
                        </div>

                        <!-- Bouton soumettre -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                                <span wire:loading.remove>
                                    <i class="bi bi-check-circle me-1"></i>Enregistrer
                                </span>
                                <span wire:loading>
                                    <span class="spinner-border spinner-border-sm me-1"></span>Enregistrement...
                                </span>
                            </button>
                            <a href="{{ route('admin.cleaners.index') }}" class="btn btn-outline-secondary">Annuler</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-info-circle me-2 text-info"></i>Identifiant</h6>
                </div>
                <div class="card-body text-center">
                    <h4 class="fw-bold text-info mb-2">{{ $cleaner->identifiant }}</h4>
                    <small class="text-muted">Créé le {{ $cleaner->created_at->format('d/m/Y') }}</small>
                </div>
            </div>
        </div>
    </div>
</div>

