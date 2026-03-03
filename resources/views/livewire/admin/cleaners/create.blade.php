<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-plus-circle me-2 text-info"></i>Nouveau Laveur
            </h4>
            <p class="text-muted mb-0">Créer un nouveau compte laveur</p>
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
                        <i class="bi bi-person-plus me-2 text-info"></i>Informations du Laveur
                    </h6>
                </div>
                <div class="card-body">
                    <form wire:submit="save">
                        <!-- Informations personnelles -->
                        <h6 class="text-muted mb-3">Informations personnelles</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Nom complet <span class="text-danger">*</span></label>
                                <input type="text" wire:model="name" class="form-control @error('name') is-invalid @enderror" placeholder="Nom complet">
                                @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" wire:model="email" class="form-control @error('email') is-invalid @enderror" placeholder="email@exemple.com">
                                @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Mot de passe -->
                        <h6 class="text-muted mb-3">Mot de passe</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Mot de passe <span class="text-danger">*</span></label>
                                <input type="password" wire:model="password" class="form-control @error('password') is-invalid @enderror" placeholder="********">
                                @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Confirmer le mot de passe <span class="text-danger">*</span></label>
                                <input type="password" wire:model="password_confirmation" class="form-control" placeholder="********">
                            </div>
                        </div>

                        <!-- Contact et localisation -->
                        <h6 class="text-muted mb-3">Contact et localisation</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Téléphone</label>
                                <input type="text" wire:model="telephone" class="form-control @error('telephone') is-invalid @enderror" placeholder="+243...">
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
                                <input type="text" wire:model="adresse" class="form-control @error('adresse') is-invalid @enderror" placeholder="Adresse complète">
                                @error('adresse')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="mb-4">
                            <label class="form-label">Notes</label>
                            <textarea wire:model="notes" class="form-control" rows="3" placeholder="Notes additionnelles..."></textarea>
                        </div>

                        <!-- Bouton soumettre -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-info" wire:loading.attr="disabled">
                                <span wire:loading.remove>
                                    <i class="bi bi-check-circle me-1"></i>Créer le Laveur
                                </span>
                                <span wire:loading>
                                    <span class="spinner-border spinner-border-sm me-1"></span>Création...
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
                    <h6 class="mb-0 fw-bold"><i class="bi bi-info-circle me-2 text-primary"></i>Informations</h6>
                </div>
                <div class="card-body">
                    <p class="small text-muted mb-3">Le laveur pourra:</p>
                    <ul class="small text-muted ps-3 mb-0">
                        <li class="mb-2">Enregistrer des lavages de motos</li>
                        <li class="mb-2">Laver les motos du système (80% pour lui)</li>
                        <li class="mb-2">Laver les motos externes (100% pour lui)</li>
                        <li class="mb-2">Consulter son historique de lavages</li>
                        <li>Voir ses statistiques de recettes</li>
                    </ul>
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-percent me-2 text-warning"></i>Répartition</h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-light mb-0">
                        <small>
                            <strong>Motos du système:</strong><br>
                            • 80% → Laveur<br>
                            • 20% → OKAMI<br><br>
                            <strong>Motos externes:</strong><br>
                            • 100% → Laveur
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

