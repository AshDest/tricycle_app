<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-bell-fill me-2 text-primary"></i>Historique des Notifications
            </h4>
            <p class="text-muted mb-0">Consultez toutes les notifications envoyées par le système</p>
        </div>
        <div class="d-flex gap-2">
            <button wire:click="supprimerAnciennes" class="btn btn-outline-danger" onclick="return confirm('Supprimer toutes les notifications lues de plus de 30 jours ?')">
                <i class="bi bi-trash me-1"></i>Nettoyer (+30j)
            </button>
            <a href="{{ route('super-admin.email-settings') }}" class="btn btn-outline-primary">
                <i class="bi bi-envelope-gear me-1"></i>Config Email
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Statistiques -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center py-3">
                    <h3 class="fw-bold mb-0 text-dark">{{ number_format($stats['total']) }}</h3>
                    <small class="text-muted">Total</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center py-3">
                    <h3 class="fw-bold mb-0 text-primary">{{ number_format($stats['aujourdhui']) }}</h3>
                    <small class="text-muted">Aujourd'hui</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center py-3">
                    <h3 class="fw-bold mb-0 text-info">{{ number_format($stats['semaine']) }}</h3>
                    <small class="text-muted">Cette semaine</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center py-3">
                    <h3 class="fw-bold mb-0 text-secondary">{{ number_format($stats['mois']) }}</h3>
                    <small class="text-muted">Ce mois</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center py-3">
                    <h3 class="fw-bold mb-0 text-warning">{{ number_format($stats['non_lues']) }}</h3>
                    <small class="text-muted">Non lues</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center py-3">
                    <h3 class="fw-bold mb-0 text-danger">{{ number_format($stats['urgentes']) }}</h3>
                    <small class="text-muted">Urgentes</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-dark">Recherche</label>
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Titre, message...">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold text-dark">Type</label>
                    <select wire:model.live="filterType" class="form-select">
                        <option value="">Tous</option>
                        @foreach($types as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold text-dark">Utilisateur</label>
                    <select wire:model.live="filterUser" class="form-select">
                        <option value="">Tous</option>
                        @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold text-dark">Priorité</label>
                    <select wire:model.live="filterPriorite" class="form-select">
                        <option value="">Toutes</option>
                        <option value="urgente">🔴 Urgente</option>
                        <option value="haute">🟠 Haute</option>
                        <option value="normale">🟢 Normale</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-dark">Période</label>
                    <div class="input-group">
                        <input type="date" wire:model.live="filterDateDebut" class="form-control">
                        <input type="date" wire:model.live="filterDateFin" class="form-control">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des notifications -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold text-dark">
                <i class="bi bi-list me-2"></i>Notifications ({{ $notifications->total() }})
            </h6>
            <select wire:model.live="perPage" class="form-select form-select-sm" style="width: auto;">
                <option value="20">20 par page</option>
                <option value="50">50 par page</option>
                <option value="100">100 par page</option>
            </select>
        </div>
        <div class="card-body p-0">
            @if($notifications->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="border-0" style="width: 50px;"></th>
                            <th class="border-0">Notification</th>
                            <th class="border-0">Destinataire</th>
                            <th class="border-0">Type</th>
                            <th class="border-0">Priorité</th>
                            <th class="border-0">Statut</th>
                            <th class="border-0">Date</th>
                            <th class="border-0 text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($notifications as $notification)
                        @php
                            $couleurs = [
                                'success' => 'success', 'danger' => 'danger', 'warning' => 'warning',
                                'info' => 'info', 'primary' => 'primary', 'green' => 'success',
                                'red' => 'danger', 'orange' => 'warning', 'blue' => 'info',
                            ];
                            $couleur = $couleurs[$notification->couleur] ?? 'secondary';
                        @endphp
                        <tr class="{{ !$notification->lu ? 'bg-light' : '' }}">
                            <td class="text-center">
                                <div class="rounded-circle bg-{{ $couleur }} bg-opacity-10 p-2 d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                    <i class="bi bi-{{ $notification->icon ?? 'bell' }} text-{{ $couleur }}"></i>
                                </div>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $notification->titre }}</div>
                                <small class="text-muted">{{ Str::limit($notification->message, 60) }}</small>
                            </td>
                            <td>
                                <span class="fw-medium text-dark">{{ $notification->user->name ?? 'N/A' }}</span>
                                <br><small class="text-muted">{{ $notification->user->email ?? '' }}</small>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    {{ $types[$notification->type] ?? $notification->type }}
                                </span>
                            </td>
                            <td>
                                @if($notification->priorite === 'urgente')
                                <span class="badge bg-danger">Urgente</span>
                                @elseif($notification->priorite === 'haute')
                                <span class="badge bg-warning text-dark">Haute</span>
                                @else
                                <span class="badge bg-success">Normale</span>
                                @endif
                            </td>
                            <td>
                                @if($notification->lu)
                                <span class="badge bg-light text-muted border">
                                    <i class="bi bi-check me-1"></i>Lu
                                </span>
                                @else
                                <span class="badge bg-primary">
                                    <i class="bi bi-envelope me-1"></i>Non lu
                                </span>
                                @endif
                            </td>
                            <td>
                                <small class="text-muted">{{ $notification->created_at->format('d/m/Y H:i') }}</small>
                            </td>
                            <td class="text-end">
                                <button wire:click="supprimer({{ $notification->id }})" class="btn btn-sm btn-outline-danger" title="Supprimer" onclick="return confirm('Supprimer cette notification ?')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-5">
                <i class="bi bi-bell-slash text-muted" style="font-size: 3rem;"></i>
                <p class="text-muted mt-3 mb-0">Aucune notification trouvée</p>
            </div>
            @endif
        </div>
        @if($notifications->hasPages())
        <div class="card-footer bg-light">
            {{ $notifications->links() }}
        </div>
        @endif
    </div>
</div>


