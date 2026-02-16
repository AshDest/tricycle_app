<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-person-plus me-2 text-primary"></i>Nouvel Utilisateur
            </h4>
            <p class="text-muted mb-0">Créer un compte utilisateur</p>
        </div>
        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Retour
        </a>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <form wire:submit="save">
                <div class="card">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-person me-2 text-primary"></i>Informations de l'utilisateur</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nom complet <span class="text-danger">*</span></label>
                                <input type="text" wire:model="name" class="form-control @error('name') is-invalid @enderror" placeholder="Jean Dupont">
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                                <input type="email" wire:model="email" class="form-control @error('email') is-invalid @enderror" placeholder="email@exemple.com">
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Mot de passe <span class="text-danger">*</span></label>
                                <input type="password" wire:model="password" class="form-control @error('password') is-invalid @enderror" placeholder="••••••••">
                                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Confirmer le mot de passe <span class="text-danger">*</span></label>
                                <input type="password" wire:model="password_confirmation" class="form-control" placeholder="••••••••">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Rôle <span class="text-danger">*</span></label>
                                <select wire:model="role" class="form-select @error('role') is-invalid @enderror">
                                    <option value="">-- Sélectionner un rôle --</option>
                                    @foreach($roles as $r)
                                    <option value="{{ $r->name }}">{{ ucfirst($r->name) }}</option>
                                    @endforeach
                                </select>
                                @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-light">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                                <span wire:loading.remove><i class="bi bi-check-lg me-1"></i>Créer l'utilisateur</span>
                                <span wire:loading><span class="spinner-border spinner-border-sm me-1"></span>Création...</span>
                            </button>
                            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Annuler</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Sidebar Info -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-info-circle me-2 text-info"></i>Rôles disponibles</h6>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush small">
                        <li class="list-group-item d-flex justify-content-between">
                            <span><strong>admin</strong></span>
                            <span class="text-muted">Administrateur NTH</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span><strong>supervisor</strong></span>
                            <span class="text-muted">Superviseur OKAMI</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span><strong>owner</strong></span>
                            <span class="text-muted">Propriétaire</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span><strong>driver</strong></span>
                            <span class="text-muted">Motard</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span><strong>cashier</strong></span>
                            <span class="text-muted">Caissier</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span><strong>collector</strong></span>
                            <span class="text-muted">Collecteur</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
