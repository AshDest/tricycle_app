<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-wallet2 me-2 text-danger"></i>Mes Dépenses
            </h4>
            <p class="text-muted mb-0">Gestion des dépenses du service de lavage</p>
        </div>
        <a href="{{ route('cleaner.depenses.create') }}" class="btn btn-danger">
            <i class="bi bi-plus-circle me-1"></i>Nouvelle Dépense
        </a>
    </div>

    <!-- Statistiques -->
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="card bg-success bg-opacity-10 border-0">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted">Solde actuel</small>
                            <h4 class="mb-0 fw-bold text-success">{{ number_format($stats['solde_actuel']) }} FC</h4>
                        </div>
                        <i class="bi bi-wallet2 fs-1 text-success opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card bg-danger bg-opacity-10 border-0">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted">Dépenses du jour</small>
                            <h4 class="mb-0 fw-bold text-danger">{{ number_format($stats['total_jour']) }} FC</h4>
                        </div>
                        <i class="bi bi-calendar-day fs-1 text-danger opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card bg-warning bg-opacity-10 border-0">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted">Dépenses du mois</small>
                            <h4 class="mb-0 fw-bold text-warning">{{ number_format($stats['total_mois']) }} FC</h4>
                        </div>
                        <i class="bi bi-calendar-month fs-1 text-warning opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card bg-info bg-opacity-10 border-0">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted">Nb dépenses (mois)</small>
                            <h4 class="mb-0 fw-bold text-info">{{ $stats['nb_depenses_mois'] }}</h4>
                        </div>
                        <i class="bi bi-receipt fs-1 text-info opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Dépenses par catégorie -->
    @if(count($parCategorie) > 0)
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h6 class="mb-0 fw-bold"><i class="bi bi-pie-chart me-2 text-primary"></i>Répartition par catégorie (ce mois)</h6>
        </div>
        <div class="card-body">
            <div class="row g-2">
                @foreach($parCategorie as $cat => $total)
                <div class="col-md-4 col-6">
                    <div class="d-flex justify-content-between align-items-center p-2 bg-light rounded">
                        <span class="small">{{ $categories[$cat] ?? $cat }}</span>
                        <strong class="text-danger">{{ number_format($total) }} FC</strong>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Filtres -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small">Recherche</label>
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="N° dépense, description...">
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Catégorie</label>
                    <select wire:model.live="filterCategorie" class="form-select">
                        <option value="">Toutes</option>
                        @foreach($categories as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Date début</label>
                    <input type="date" wire:model.live="dateDebut" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Date fin</label>
                    <input type="date" wire:model.live="dateFin" class="form-control">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button wire:click="resetFilters" class="btn btn-outline-secondary w-100" title="Réinitialiser">
                        <i class="bi bi-x-lg me-1"></i>Réinit.
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Messages flash -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Liste des dépenses -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>N° Dépense</th>
                            <th>Catégorie</th>
                            <th>Description</th>
                            <th>Fournisseur</th>
                            <th>Montant</th>
                            <th>Paiement</th>
                            <th>Date</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($depenses as $depense)
                        <tr>
                            <td>
                                <span class="fw-semibold text-danger">{{ $depense->numero_depense }}</span>
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ $depense->categorie_label }}</span>
                            </td>
                            <td>
                                <span class="text-truncate d-inline-block" style="max-width: 200px;" title="{{ $depense->description }}">
                                    {{ $depense->description }}
                                </span>
                            </td>
                            <td>{{ $depense->fournisseur ?? '-' }}</td>
                            <td class="fw-bold text-danger">{{ number_format($depense->montant) }} FC</td>
                            <td>
                                <span class="badge bg-{{ $depense->mode_paiement === 'cash' ? 'success' : 'primary' }}">
                                    {{ $depense->mode_paiement === 'cash' ? 'Cash' : 'Mobile' }}
                                </span>
                            </td>
                            <td>
                                <small>{{ $depense->date_depense->format('d/m/Y') }}</small>
                            </td>
                            <td class="text-end">
                                <button wire:click="supprimer({{ $depense->id }})"
                                        wire:confirm="Êtes-vous sûr de vouloir supprimer cette dépense? Le montant sera remboursé à votre solde."
                                        class="btn btn-sm btn-outline-danger" title="Supprimer">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                Aucune dépense trouvée
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($depenses->hasPages())
        <div class="card-footer bg-white">
            {{ $depenses->links() }}
        </div>
        @endif
    </div>
</div>

