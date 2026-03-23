<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-people-fill me-2 text-danger"></i>Gestion des Utilisateurs
            </h4>
            <p class="text-muted mb-0">Créer et gérer les comptes utilisateurs du système</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('super-admin.dashboard') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Retour
            </a>
            <button wire:click="openCreate" class="btn btn-danger">
                <i class="bi bi-person-plus me-1"></i>Nouvel Utilisateur
            </button>
        </div>
    </div>

    <!-- Messages Flash -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Statistiques -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 bg-primary bg-opacity-10">
                <div class="card-body py-3 text-center">
                    <h3 class="fw-bold text-primary mb-0">{{ $totalUsers }}</h3>
                    <small class="text-muted">Total Utilisateurs</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-danger bg-opacity-10">
                <div class="card-body py-3 text-center">
                    <h3 class="fw-bold text-danger mb-0">{{ $totalAdmins }}</h3>
                    <small class="text-muted">Administrateurs</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card mb-4">
        <div class="card-body py-3">
            <div class="row g-3 align-items-center">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Rechercher par nom ou email...">
                    </div>
                </div>
                <div class="col-md-4">
                    <select wire:model.live="filterRole" class="form-select">
                        <option value="">Tous les rôles</option>
                        @foreach($roles as $r)
                        <option value="{{ $r->name }}">{{ $this->getRoleLabel($r->name) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Tableau des utilisateurs -->
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Utilisateur</th>
                        <th>Email</th>
                        <th>Rôle</th>
                        <th>Statut</th>
                        <th>Créé le</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="user-avatar-sm bg-{{ $this->getRoleBadge($user->roles->first()->name ?? '') }} bg-opacity-10 text-{{ $this->getRoleBadge($user->roles->first()->name ?? '') }}">
                                    {{ strtoupper(substr($user->name, 0, 2)) }}
                                </div>
                                <div>
                                    <strong>{{ $user->name }}</strong>
                                    @if($user->hasRole('super-admin'))
                                    <i class="bi bi-shield-fill-check text-danger ms-1" title="Super Admin"></i>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td><small class="text-muted">{{ $user->email }}</small></td>
                        <td>
                            @foreach($user->roles as $role)
                            <span class="badge bg-{{ $this->getRoleBadge($role->name) }}">
                                {{ $this->getRoleLabel($role->name) }}
                            </span>
                            @endforeach
                        </td>
                        <td>
                            @if($user->hasRole('super-admin'))
                            <span class="badge bg-danger"><i class="bi bi-shield-lock me-1"></i>Protégé</span>
                            @elseif($user->is_active ?? true)
                            <span class="badge bg-success">Actif</span>
                            @else
                            <span class="badge bg-secondary">Inactif</span>
                            @endif
                        </td>
                        <td><small class="text-muted">{{ $user->created_at->format('d/m/Y') }}</small></td>
                        <td class="text-end">
                            @if(!$user->hasRole('super-admin'))
                            <div class="btn-group btn-group-sm">
                                <button wire:click="openEdit({{ $user->id }})" class="btn btn-outline-primary" title="Modifier">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button wire:click="toggleActive({{ $user->id }})" class="btn btn-outline-{{ ($user->is_active ?? true) ? 'warning' : 'success' }}" title="{{ ($user->is_active ?? true) ? 'Désactiver' : 'Activer' }}">
                                    <i class="bi bi-{{ ($user->is_active ?? true) ? 'pause-circle' : 'play-circle' }}"></i>
                                </button>
                                <button wire:click="confirmDelete({{ $user->id }})" class="btn btn-outline-danger" title="Supprimer">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                            @else
                            <span class="text-muted small"><i class="bi bi-lock me-1"></i>Protégé</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-people fs-1 d-block mb-2"></i>
                            Aucun utilisateur trouvé
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
        <div class="card-footer bg-light">
            {{ $users->links() }}
        </div>
        @endif
    </div>

    <!-- Modal Création -->
    @if($showCreateModal)
    <div class="modal fade show d-block" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-danger bg-opacity-10">
                    <h5 class="modal-title"><i class="bi bi-person-plus me-2 text-danger"></i>Nouvel Utilisateur</h5>
                    <button type="button" class="btn-close" wire:click="$set('showCreateModal', false)"></button>
                </div>
                <form wire:submit="create">
                    <div class="modal-body">
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
                                <label class="form-label fw-semibold">Téléphone</label>
                                <input type="text" wire:model="phone" class="form-control" placeholder="+243 XXX XXX XXX">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Rôle <span class="text-danger">*</span></label>
                                <select wire:model="role" class="form-select @error('role') is-invalid @enderror">
                                    @foreach($roles as $r)
                                    <option value="{{ $r->name }}">{{ $this->getRoleLabel($r->name) }}</option>
                                    @endforeach
                                </select>
                                @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="$set('showCreateModal', false)">Annuler</button>
                        <button type="submit" class="btn btn-danger" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="create"><i class="bi bi-check-lg me-1"></i>Créer l'utilisateur</span>
                            <span wire:loading wire:target="create"><span class="spinner-border spinner-border-sm me-1"></span>Création...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <!-- Modal Édition -->
    @if($showEditModal)
    <div class="modal fade show d-block" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary bg-opacity-10">
                    <h5 class="modal-title"><i class="bi bi-pencil me-2 text-primary"></i>Modifier l'utilisateur</h5>
                    <button type="button" class="btn-close" wire:click="$set('showEditModal', false)"></button>
                </div>
                <form wire:submit="update">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nom complet <span class="text-danger">*</span></label>
                                <input type="text" wire:model="editName" class="form-control @error('editName') is-invalid @enderror">
                                @error('editName')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                                <input type="email" wire:model="editEmail" class="form-control @error('editEmail') is-invalid @enderror">
                                @error('editEmail')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Téléphone</label>
                                <input type="text" wire:model="editPhone" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Rôle <span class="text-danger">*</span></label>
                                <select wire:model="editRole" class="form-select @error('editRole') is-invalid @enderror">
                                    @foreach($roles as $r)
                                    <option value="{{ $r->name }}">{{ $this->getRoleLabel($r->name) }}</option>
                                    @endforeach
                                </select>
                                @error('editRole')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <hr>
                                <small class="text-muted"><i class="bi bi-info-circle me-1"></i>Laissez vide pour garder le mot de passe actuel</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nouveau mot de passe</label>
                                <input type="password" wire:model="editPassword" class="form-control @error('editPassword') is-invalid @enderror" placeholder="••••••••">
                                @error('editPassword')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Confirmer le mot de passe</label>
                                <input type="password" wire:model="editPassword_confirmation" class="form-control" placeholder="••••••••">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="$set('showEditModal', false)">Annuler</button>
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="update"><i class="bi bi-check-lg me-1"></i>Enregistrer</span>
                            <span wire:loading wire:target="update"><span class="spinner-border spinner-border-sm me-1"></span>Enregistrement...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <!-- Modal Suppression -->
    @if($showDeleteModal)
    <div class="modal fade show d-block" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger bg-opacity-10">
                    <h5 class="modal-title"><i class="bi bi-exclamation-triangle me-2 text-danger"></i>Confirmer la suppression</h5>
                    <button type="button" class="btn-close" wire:click="$set('showDeleteModal', false)"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <i class="bi bi-person-x fs-1 text-danger d-block mb-3"></i>
                    <p>Êtes-vous sûr de vouloir supprimer l'utilisateur :</p>
                    <h5 class="fw-bold">{{ $deleteUserName }}</h5>
                    <p class="text-danger small mt-2"><i class="bi bi-exclamation-triangle me-1"></i>Cette action est irréversible.</p>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary" wire:click="$set('showDeleteModal', false)">Annuler</button>
                    <button type="button" class="btn btn-danger" wire:click="delete" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="delete"><i class="bi bi-trash me-1"></i>Supprimer</span>
                        <span wire:loading wire:target="delete"><span class="spinner-border spinner-border-sm me-1"></span>Suppression...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

