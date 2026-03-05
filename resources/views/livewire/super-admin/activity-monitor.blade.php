<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-activity me-2 text-success"></i>Moniteur d'Activité
            </h4>
            <p class="text-muted mb-0">Surveillance des utilisateurs connectés et activité</p>
        </div>
        <div class="d-flex gap-2">
            <div class="form-check form-switch d-flex align-items-center">
                <input class="form-check-input me-2" type="checkbox" wire:model.live="onlineOnly" id="onlineSwitch">
                <label class="form-check-label small" for="onlineSwitch">En ligne seulement</label>
            </div>
            <a href="{{ route('super-admin.dashboard') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Retour
            </a>
        </div>
    </div>

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
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm bg-primary bg-opacity-10">
                <div class="card-body py-3 text-center">
                    <h3 class="fw-bold text-primary mb-1">{{ $stats['total_users'] }}</h3>
                    <small class="text-muted">Total Utilisateurs</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm bg-success bg-opacity-10">
                <div class="card-body py-3 text-center">
                    <h3 class="fw-bold text-success mb-1">{{ $stats['active_users'] }}</h3>
                    <small class="text-muted">Utilisateurs Actifs</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm bg-info bg-opacity-10">
                <div class="card-body py-3 text-center">
                    <h3 class="fw-bold text-info mb-1">{{ $stats['online_now'] }}</h3>
                    <small class="text-muted">En Ligne Maintenant</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm bg-warning bg-opacity-10">
                <div class="card-body py-3 text-center">
                    <h3 class="fw-bold text-warning mb-1">{{ $stats['active_today'] }}</h3>
                    <small class="text-muted">Actifs Aujourd'hui</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Répartition par rôle -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header py-3 bg-light">
            <h6 class="mb-0 fw-bold">
                <i class="bi bi-pie-chart me-2"></i>Répartition par Rôle
            </h6>
        </div>
        <div class="card-body">
            <div class="row g-2">
                @foreach($roleStats as $role => $count)
                <div class="col-auto">
                    <span class="badge bg-{{
                        $role === 'super-admin' ? 'danger' :
                        ($role === 'admin' ? 'dark' :
                        ($role === 'supervisor' ? 'primary' :
                        ($role === 'owner' ? 'warning' :
                        ($role === 'driver' ? 'info' :
                        ($role === 'cashier' ? 'success' :
                        ($role === 'collector' ? 'secondary' :
                        ($role === 'cleaner' ? 'info' : 'secondary')))))))
                    }} px-3 py-2">
                        {{ ucfirst(str_replace('-', ' ', $role)) }}: {{ $count }}
                    </span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small">Recherche</label>
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control"
                           placeholder="Nom ou email...">
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Rôle</label>
                    <select wire:model.live="filterRole" class="form-select">
                        <option value="">Tous les rôles</option>
                        @foreach($roles as $role)
                        <option value="{{ $role }}">{{ ucfirst(str_replace('-', ' ', $role)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Statut</label>
                    <select wire:model.live="filterStatus" class="form-select">
                        <option value="">Tous</option>
                        <option value="active">Actifs</option>
                        <option value="inactive">Inactifs</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button wire:click="resetFilters" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-x-lg me-1"></i>Reset
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des utilisateurs -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Utilisateur</th>
                            <th>Rôle</th>
                            <th>Dernière Activité</th>
                            <th>Statut</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm me-2 position-relative">
                                        <div class="bg-{{ $user->is_active ? 'primary' : 'secondary' }} rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            <span class="text-white fw-bold">{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                                        </div>
                                        @if($user->last_activity && $user->last_activity >= now()->subMinutes(15))
                                        <span class="position-absolute bottom-0 end-0 bg-success border border-2 border-white rounded-circle" style="width: 12px; height: 12px;"></span>
                                        @endif
                                    </div>
                                    <div>
                                        <span class="fw-semibold">{{ $user->name }}</span>
                                        <br><small class="text-muted">{{ $user->email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @foreach($user->roles as $role)
                                <span class="badge bg-{{
                                    $role->name === 'super-admin' ? 'danger' :
                                    ($role->name === 'admin' ? 'dark' :
                                    ($role->name === 'supervisor' ? 'primary' :
                                    ($role->name === 'owner' ? 'warning' :
                                    ($role->name === 'driver' ? 'info' :
                                    ($role->name === 'cashier' ? 'success' :
                                    ($role->name === 'collector' ? 'secondary' :
                                    ($role->name === 'cleaner' ? 'info' : 'secondary')))))))
                                }}">
                                    {{ ucfirst(str_replace('-', ' ', $role->name)) }}
                                </span>
                                @endforeach
                            </td>
                            <td>
                                @if($user->last_activity)
                                    @if($user->last_activity >= now()->subMinutes(15))
                                    <span class="text-success"><i class="bi bi-circle-fill me-1 small"></i>En ligne</span>
                                    @elseif($user->last_activity >= now()->subHours(1))
                                    <span class="text-warning"><i class="bi bi-circle-fill me-1 small"></i>Il y a {{ $user->last_activity->diffForHumans() }}</span>
                                    @else
                                    <span class="text-muted">{{ $user->last_activity->format('d/m/Y H:i') }}</span>
                                    @endif
                                @else
                                <span class="text-muted">Jamais connecté</span>
                                @endif
                            </td>
                            <td>
                                @if($user->is_active)
                                <span class="badge bg-success">Actif</span>
                                @else
                                <span class="badge bg-danger">Inactif</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @if($user->id !== auth()->id())
                                <div class="btn-group btn-group-sm">
                                    <button wire:click="toggleUserStatus({{ $user->id }})"
                                            class="btn btn-outline-{{ $user->is_active ? 'warning' : 'success' }}"
                                            title="{{ $user->is_active ? 'Désactiver' : 'Activer' }}">
                                        <i class="bi bi-{{ $user->is_active ? 'pause' : 'play' }}"></i>
                                    </button>
                                    @if($user->last_activity && $user->last_activity >= now()->subMinutes(15))
                                    <button wire:click="forceLogout({{ $user->id }})"
                                            class="btn btn-outline-danger"
                                            title="Forcer la déconnexion"
                                            wire:confirm="Êtes-vous sûr de vouloir déconnecter cet utilisateur ?">
                                        <i class="bi bi-box-arrow-right"></i>
                                    </button>
                                    @endif
                                </div>
                                @else
                                <span class="badge bg-secondary">Vous</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                Aucun utilisateur trouvé
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($users->hasPages())
        <div class="card-footer bg-white">
            {{ $users->links() }}
        </div>
        @endif
    </div>
</div>

