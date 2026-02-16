<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-pencil-square me-2 text-warning"></i>Modifier le Propriétaire
            </h4>
            <p class="text-muted mb-0">{{ $proprietaire->user->name ?? 'Propriétaire' }}</p>
        </div>
        <a href="{{ route('supervisor.proprietaires.index') }}" class="btn btn-outline-secondary">
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
                            <input type="text" wire:model="name" class="form-control @error('name') is-invalid @enderror" placeholder="Entrez le nom complet">
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" wire:model="email" class="form-control @error('email') is-invalid @enderror" placeholder="exemple@email.com">
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nouveau mot de passe <small class="text-muted">(laisser vide pour ne pas changer)</small></label>
                            <input type="password" wire:model="password" class="form-control @error('password') is-invalid @enderror" placeholder="Minimum 8 caractères">
                            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Confirmer le nouveau mot de passe</label>
                            <input type="password" wire:model="password_confirmation" class="form-control" placeholder="Confirmez le mot de passe">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informations propriétaire -->
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-building me-2 text-warning"></i>Informations Propriétaire</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Raison sociale</label>
                            <input type="text" wire:model="raison_sociale" class="form-control @error('raison_sociale') is-invalid @enderror" placeholder="Nom de l'entreprise (optionnel)">
                            @error('raison_sociale') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Téléphone</label>
                            <input type="text" wire:model="contact_phone" class="form-control @error('contact_phone') is-invalid @enderror" placeholder="+243 XXX XXX XXX">
                            @error('contact_phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Adresse</label>
                            <textarea wire:model="adresse" class="form-control @error('adresse') is-invalid @enderror" rows="3" placeholder="Adresse complète"></textarea>
                            @error('adresse') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-check form-switch">
                            <input type="checkbox" wire:model="is_active" class="form-check-input" id="is_active" role="switch">
                            <label class="form-check-label" for="is_active">Compte actif</label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Comptes de paiement -->
            <div class="col-12">
                <div class="card">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-wallet2 me-2 text-success"></i>Comptes de Paiement</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6 col-lg-3">
                                <label class="form-label">
                                    <span class="badge bg-success me-1">M-Pesa</span> Numéro
                                </label>
                                <input type="text" wire:model="numero_compte_mpesa" class="form-control" placeholder="+243 XXX XXX XXX">
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label class="form-label">
                                    <span class="badge bg-danger me-1">Airtel</span> Numéro
                                </label>
                                <input type="text" wire:model="numero_compte_airtel" class="form-control" placeholder="+243 XXX XXX XXX">
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label class="form-label">
                                    <span class="badge bg-warning me-1">Orange</span> Numéro
                                </label>
                                <input type="text" wire:model="numero_compte_orange" class="form-control" placeholder="+243 XXX XXX XXX">
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label class="form-label">
                                    <span class="badge bg-primary me-1">Banque</span> Nom
                                </label>
                                <input type="text" wire:model="banque_nom" class="form-control" placeholder="Nom de la banque">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Numéro de compte bancaire</label>
                                <input type="text" wire:model="numero_compte_bancaire" class="form-control" placeholder="Numéro de compte">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="col-12">
                <div class="card">
                    <div class="card-body d-flex justify-content-end gap-2">
                        <a href="{{ route('supervisor.proprietaires.index') }}" class="btn btn-outline-secondary">
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

