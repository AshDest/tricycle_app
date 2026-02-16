<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-building me-2 text-warning"></i>Gestion des Propriétaires
            </h4>
            <p class="text-muted mb-0">Bailleurs de motos-tricycles</p>
        </div>
        <a href="{{ route('supervisor.proprietaires.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i>Nouveau Propriétaire
        </a>
    </div>

    <!-- Messages Flash -->
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Filtres -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label small fw-semibold">Rechercher</label>
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Nom, email, téléphone...">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Statut</label>
                    <select wire:model.live="filterStatus" class="form-select">
                        <option value="">Tous</option>
                        <option value="1">Actifs</option>
                        <option value="0">Inactifs</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button wire:click="resetFilters" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-x-lg me-1"></i>Réinitialiser
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des propriétaires -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Propriétaire</th>
                            <th>Contact</th>
                            <th>Motos</th>
                            <th>Comptes de paiement</th>
                            <th>Statut</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($proprietaires as $proprietaire)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar avatar-sm bg-warning bg-opacity-10 text-warning rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                        {{ strtoupper(substr($proprietaire->user->name ?? 'P', 0, 1)) }}
                                    </div>
                                    <div>
                                        <span class="fw-medium d-block">{{ $proprietaire->user->name ?? 'N/A' }}</span>
                                        @if($proprietaire->raison_sociale)
                                            <small class="text-muted">{{ $proprietaire->raison_sociale }}</small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <small class="text-muted d-block">{{ $proprietaire->user->email ?? 'N/A' }}</small>
                                    <small><i class="bi bi-telephone me-1"></i>{{ $proprietaire->telephone ?? 'N/A' }}</small>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">
                                    <i class="bi bi-bicycle me-1"></i>{{ $proprietaire->motos_count ?? 0 }} moto(s)
                                </span>
                            </td>
                            <td>
                                <div class="d-flex gap-1 flex-wrap">
                                    @if($proprietaire->numero_compte_mpesa)
                                        <span class="badge bg-success bg-opacity-10 text-success" title="M-Pesa">M-Pesa</span>
                                    @endif
                                    @if($proprietaire->numero_compte_airtel)
                                        <span class="badge bg-danger bg-opacity-10 text-danger" title="Airtel">Airtel</span>
                                    @endif
                                    @if($proprietaire->numero_compte_orange)
                                        <span class="badge bg-warning bg-opacity-10 text-warning" title="Orange">Orange</span>
                                    @endif
                                    @if($proprietaire->numero_compte_bancaire)
                                        <span class="badge bg-primary bg-opacity-10 text-primary" title="Banque">Banque</span>
                                    @endif
                                    @if(!$proprietaire->numero_compte_mpesa && !$proprietaire->numero_compte_airtel && !$proprietaire->numero_compte_orange && !$proprietaire->numero_compte_bancaire)
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary">Aucun</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-soft-{{ $proprietaire->is_active ? 'success' : 'danger' }}">
                                    {{ $proprietaire->is_active ? 'Actif' : 'Inactif' }}
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group">
                                    <a href="{{ route('supervisor.proprietaires.edit', $proprietaire) }}" class="btn btn-sm btn-outline-primary" title="Modifier">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button wire:click="toggleActive({{ $proprietaire->id }})"
                                            class="btn btn-sm btn-outline-{{ $proprietaire->is_active ? 'warning' : 'success' }}"
                                            title="{{ $proprietaire->is_active ? 'Désactiver' : 'Activer' }}">
                                        <i class="bi bi-{{ $proprietaire->is_active ? 'pause' : 'play' }}"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-building fs-1 d-block mb-3"></i>
                                <p class="mb-0">Aucun propriétaire trouvé</p>
                                <a href="{{ route('supervisor.proprietaires.create') }}" class="btn btn-sm btn-primary mt-3">
                                    <i class="bi bi-plus-lg me-1"></i>Ajouter un propriétaire
                                </a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($proprietaires->hasPages())
        <div class="card-footer bg-light">
            {{ $proprietaires->links() }}
        </div>
        @endif
    </div>
</div>

