<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-list-ul me-2 text-info"></i>Liste des Lavages
            </h4>
            <p class="text-muted mb-0">Historique de tous vos lavages</p>
        </div>
        <div class="d-flex gap-2">
            <button wire:click="exporterPdf" class="btn btn-outline-danger" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="exporterPdf">
                    <i class="bi bi-file-pdf me-1"></i>Export PDF
                </span>
                <span wire:loading wire:target="exporterPdf">
                    <span class="spinner-border spinner-border-sm me-1"></span>...
                </span>
            </button>
            <a href="{{ route('cleaner.lavages.create') }}" class="btn btn-info">
                <i class="bi bi-plus-circle me-1"></i>Nouveau Lavage
            </a>
        </div>
    </div>

    <!-- Statistiques rapides -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card bg-info bg-opacity-10 border-0">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted">Total lavages</small>
                            <h4 class="mb-0 fw-bold">{{ $stats['total'] }}</h4>
                        </div>
                        <i class="bi bi-droplet-half fs-1 text-info opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success bg-opacity-10 border-0">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted">Aujourd'hui</small>
                            <h4 class="mb-0 fw-bold">{{ $stats['aujourdhui'] }}</h4>
                        </div>
                        <i class="bi bi-calendar-check fs-1 text-success opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-warning bg-opacity-10 border-0">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted">CA du jour</small>
                            <h4 class="mb-0 fw-bold">{{ number_format($stats['ca_jour']) }} FC</h4>
                        </div>
                        <i class="bi bi-cash-stack fs-1 text-warning opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small">Recherche</label>
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="N° lavage, plaque...">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Type</label>
                    <select wire:model.live="filterType" class="form-select">
                        <option value="">Tous</option>
                        <option value="simple">Simple</option>
                        <option value="complet">Complet</option>
                        <option value="premium">Premium</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Source</label>
                    <select wire:model.live="filterSource" class="form-select">
                        <option value="">Toutes</option>
                        <option value="interne">Système</option>
                        <option value="externe">Externe</option>
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
                <div class="col-md-1 d-flex align-items-end">
                    <button wire:click="resetFilters" class="btn btn-outline-secondary w-100" title="Réinitialiser">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des lavages -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>N° Lavage</th>
                            <th>Moto</th>
                            <th>Type</th>
                            <th>Prix</th>
                            <th>Ma part</th>
                            <th>Part OKAMI</th>
                            <th>Date</th>
                            <th>Statut</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($lavages as $lavage)
                        <tr>
                            <td>
                                <span class="fw-semibold text-primary">{{ $lavage->numero_lavage }}</span>
                            </td>
                            <td>
                                @if($lavage->is_externe)
                                <span class="badge bg-secondary">Externe</span><br>
                                <small>{{ $lavage->plaque_externe }}</small>
                                @else
                                <span class="badge bg-info">Système</span><br>
                                <small>{{ $lavage->moto?->plaque_immatriculation ?? 'N/A' }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $lavage->type_lavage === 'premium' ? 'warning' : ($lavage->type_lavage === 'complet' ? 'primary' : 'info') }}">
                                    {{ ucfirst($lavage->type_lavage) }}
                                </span>
                            </td>
                            <td>
                                {{ number_format($lavage->prix_final) }} FC
                                @if($lavage->remise > 0)
                                <br><small class="text-success">-{{ number_format($lavage->remise) }} FC</small>
                                @endif
                            </td>
                            <td class="fw-bold text-success">{{ number_format($lavage->part_cleaner) }} FC</td>
                            <td>
                                @if($lavage->part_okami > 0)
                                <span class="text-warning">{{ number_format($lavage->part_okami) }} FC</span>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <small>{{ $lavage->date_lavage->format('d/m/Y') }}</small><br>
                                <small class="text-muted">{{ $lavage->date_lavage->format('H:i') }}</small>
                            </td>
                            <td>
                                <span class="badge bg-{{ $lavage->statut_paiement === 'payé' ? 'success' : ($lavage->statut_paiement === 'annulé' ? 'danger' : 'warning') }}">
                                    {{ ucfirst($lavage->statut_paiement) }}
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    @if($lavage->statut_paiement !== 'payé')
                                    <a href="{{ route('cleaner.lavages.edit', $lavage) }}" class="btn btn-outline-primary" title="Modifier">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @endif
                                    @if($lavage->statut_paiement === 'payé')
                                    <button wire:click="telechargerRecu({{ $lavage->id }})" class="btn btn-outline-info" title="Télécharger reçu">
                                        <i class="bi bi-printer"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                Aucun lavage trouvé
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($lavages->hasPages())
        <div class="card-footer bg-white">
            {{ $lavages->links() }}
        </div>
        @endif
    </div>
</div>

