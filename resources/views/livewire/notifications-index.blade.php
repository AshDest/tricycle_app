<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-bell me-2 text-primary"></i>Mes Notifications
            </h4>
            <p class="text-muted mb-0">Consultez et gérez vos notifications</p>
        </div>
        <div class="d-flex gap-2">
            @if($stats['nonLues'] > 0)
            <button wire:click="marquerToutesCommeLues" class="btn btn-outline-primary">
                <i class="bi bi-check-all me-1"></i>Tout marquer lu
            </button>
            @endif
            <button wire:click="supprimerLues" class="btn btn-outline-danger" onclick="return confirm('Supprimer toutes les notifications lues ?')">
                <i class="bi bi-trash me-1"></i>Supprimer les lues
            </button>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-3 d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                        <i class="bi bi-bell text-primary fs-4"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-0 text-dark">{{ $stats['total'] }}</h4>
                        <small class="text-muted">Total notifications</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-3 d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-warning bg-opacity-10 p-3">
                        <i class="bi bi-envelope text-warning fs-4"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-0 text-dark">{{ $stats['nonLues'] }}</h4>
                        <small class="text-muted">Non lues</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-3 d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-danger bg-opacity-10 p-3">
                        <i class="bi bi-exclamation-triangle text-danger fs-4"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-0 text-dark">{{ $stats['urgentes'] }}</h4>
                        <small class="text-muted">Urgentes</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-dark">Type</label>
                    <select wire:model.live="filterType" class="form-select">
                        <option value="">Tous les types</option>
                        @foreach($types as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-dark">Statut</label>
                    <select wire:model.live="filterLu" class="form-select">
                        <option value="">Toutes</option>
                        <option value="0">Non lues</option>
                        <option value="1">Lues</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-dark">Priorité</label>
                    <select wire:model.live="filterPriorite" class="form-select">
                        <option value="">Toutes</option>
                        <option value="urgente">🔴 Urgente</option>
                        <option value="haute">🟠 Haute</option>
                        <option value="normale">🟢 Normale</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button wire:click="$set('filterType', '')" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-x-lg me-1"></i>Réinitialiser
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des notifications -->
    <div class="card">
        <div class="card-body p-0">
            @forelse($notifications as $notification)
            @php
                $couleurs = [
                    'success' => 'success',
                    'danger' => 'danger',
                    'warning' => 'warning',
                    'info' => 'info',
                    'primary' => 'primary',
                    'green' => 'success',
                    'red' => 'danger',
                    'orange' => 'warning',
                    'blue' => 'info',
                ];
                $couleur = $couleurs[$notification->couleur] ?? 'secondary';
                $prioriteClass = match($notification->priorite) {
                    'urgente' => 'border-start border-danger border-4',
                    'haute' => 'border-start border-warning border-4',
                    default => ''
                };
            @endphp
            <div class="px-4 py-3 border-bottom {{ !$notification->lu ? 'bg-light' : '' }} {{ $prioriteClass }}" style="transition: background 0.2s;">
                <div class="d-flex gap-3">
                    <!-- Icône -->
                    <div class="flex-shrink-0">
                        <div class="rounded-circle bg-{{ $couleur }} bg-opacity-10 p-2" style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-{{ $notification->icon ?? 'bell' }} text-{{ $couleur }} fs-5"></i>
                        </div>
                    </div>

                    <!-- Contenu -->
                    <div class="flex-grow-1 min-w-0">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <h6 class="fw-bold mb-0 text-dark {{ !$notification->lu ? '' : 'text-muted' }}">
                                {{ $notification->titre }}
                                @if(!$notification->lu)
                                <span class="badge bg-primary ms-2" style="font-size: 0.65rem;">Nouveau</span>
                                @endif
                                @if($notification->priorite === 'urgente')
                                <span class="badge bg-danger ms-1" style="font-size: 0.65rem;">Urgent</span>
                                @endif
                            </h6>
                            <small class="text-muted flex-shrink-0 ms-2">
                                <i class="bi bi-clock me-1"></i>{{ $notification->created_at->diffForHumans() }}
                            </small>
                        </div>
                        <p class="mb-2 text-muted small">{{ $notification->message }}</p>
                        <div class="d-flex gap-2 align-items-center">
                            @if(!$notification->lu)
                            <button wire:click="marquerCommeLu({{ $notification->id }})" class="btn btn-sm btn-outline-primary py-0 px-2">
                                <i class="bi bi-check me-1"></i>Marquer lu
                            </button>
                            @endif
                            <button wire:click="supprimer({{ $notification->id }})" class="btn btn-sm btn-outline-danger py-0 px-2" onclick="return confirm('Supprimer cette notification ?')">
                                <i class="bi bi-trash"></i>
                            </button>
                            <span class="badge bg-light text-dark">
                                {{ ucfirst(str_replace('_', ' ', $notification->type)) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center py-5">
                <div class="mb-3">
                    <i class="bi bi-bell-slash text-muted" style="font-size: 4rem;"></i>
                </div>
                <h5 class="text-muted">Aucune notification</h5>
                <p class="text-muted small">Vous n'avez aucune notification pour le moment.</p>
            </div>
            @endforelse
        </div>

        @if($notifications->hasPages())
        <div class="card-footer bg-light">
            {{ $notifications->links() }}
        </div>
        @endif
    </div>
</div>

