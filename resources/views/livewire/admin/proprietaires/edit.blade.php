<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-building me-2 text-warning"></i>Modifier Propriétaire
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Tableau de bord</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.proprietaires.index') }}">Propriétaires</a></li>
                    <li class="breadcrumb-item active">{{ $proprietaire->user->name ?? 'Modifier' }}</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('admin.proprietaires.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Retour
        </a>
    </div>

    <!-- Formulaire -->
    <form wire:submit.prevent="save">
        <div class="row g-4">
            <!-- Informations du compte -->
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-person me-2 text-primary"></i>Informations du Compte</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nom complet <span class="text-danger">*</span></label>
                            <input type="text" wire:model="name" class="form-control @error('name') is-invalid @enderror" placeholder="Nom du propriétaire">
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                            <input type="email" wire:model="email" class="form-control @error('email') is-invalid @enderror" placeholder="email@exemple.com">
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Téléphone</label>
                            <input type="text" wire:model="phone" class="form-control @error('phone') is-invalid @enderror" placeholder="+243 ...">
                            @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="alert alert-info small">
                            <i class="bi bi-info-circle me-2"></i>
                            Laissez les champs mot de passe vides pour conserver l'actuel.
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Nouveau mot de passe</label>
                                <input type="password" wire:model="password" class="form-control @error('password') is-invalid @enderror" placeholder="Min. 8 caractères">
                                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Confirmer</label>
                                <input type="password" wire:model="password_confirmation" class="form-control" placeholder="Confirmer le mot de passe">
                            </div>
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
                            <label class="form-label fw-semibold">Raison sociale</label>
                            <input type="text" wire:model="raison_sociale" class="form-control @error('raison_sociale') is-invalid @enderror" placeholder="Nom de l'entreprise (optionnel)">
                            @error('raison_sociale') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Adresse</label>
                            <textarea wire:model="adresse" class="form-control @error('adresse') is-invalid @enderror" rows="2" placeholder="Adresse complète"></textarea>
                            @error('adresse') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Tél. contact</label>
                                <input type="text" wire:model="contact_phone" class="form-control @error('contact_phone') is-invalid @enderror" placeholder="+243 ...">
                                @error('contact_phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Email contact</label>
                                <input type="email" wire:model="contact_email" class="form-control @error('contact_email') is-invalid @enderror" placeholder="contact@exemple.com">
                                @error('contact_email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Comptes Mobile Money -->
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-phone me-2 text-success"></i>Comptes Mobile Money</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-circle-fill text-danger me-1" style="font-size: 8px;"></i>M-Pesa
                            </label>
                            <input type="text" wire:model="numero_compte_mpesa" class="form-control @error('numero_compte_mpesa') is-invalid @enderror" placeholder="Numéro M-Pesa">
                            @error('numero_compte_mpesa') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-circle-fill text-danger me-1" style="font-size: 8px;"></i>Airtel Money
                            </label>
                            <input type="text" wire:model="numero_compte_airtel" class="form-control @error('numero_compte_airtel') is-invalid @enderror" placeholder="Numéro Airtel Money">
                            @error('numero_compte_airtel') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-circle-fill text-warning me-1" style="font-size: 8px;"></i>Orange Money
                            </label>
                            <input type="text" wire:model="numero_compte_orange" class="form-control @error('numero_compte_orange') is-invalid @enderror" placeholder="Numéro Orange Money">
                            @error('numero_compte_orange') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Compte Bancaire -->
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-bank me-2 text-info"></i>Compte Bancaire</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nom de la banque</label>
                            <input type="text" wire:model="banque_nom" class="form-control @error('banque_nom') is-invalid @enderror" placeholder="Ex: Rawbank, Equity BCDC...">
                            @error('banque_nom') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Numéro de compte</label>
                            <input type="text" wire:model="numero_compte_bancaire" class="form-control @error('numero_compte_bancaire') is-invalid @enderror" placeholder="Numéro de compte bancaire">
                            @error('numero_compte_bancaire') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="alert alert-info small mb-0">
                            <i class="bi bi-info-circle me-2"></i>
                            Les informations bancaires seront utilisées pour les virements de paiement.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Boutons d'action -->
        <div class="card mt-4">
            <div class="card-body d-flex justify-content-between">
                <a href="{{ route('admin.proprietaires.show', $proprietaire) }}" class="btn btn-outline-info">
                    <i class="bi bi-eye me-1"></i>Voir le profil
                </a>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.proprietaires.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-lg me-1"></i>Annuler
                    </a>
                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="save">
                            <i class="bi bi-check-lg me-1"></i>Enregistrer
                        </span>
                        <span wire:loading wire:target="save">
                            <span class="spinner-border spinner-border-sm me-1"></span>Enregistrement...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
