<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-gear-wide-connected me-2 text-warning"></i>Services KWADO
            </h4>
            <p class="text-muted mb-0">Réparation de pneus et services associés</p>
        </div>
        <div class="d-flex gap-2">
            <button wire:click="exportPdf" class="btn btn-sm btn-danger" title="Exporter PDF">
                <i class="bi bi-file-pdf me-1"></i>PDF
            </button>
            <a href="{{ route('cleaner.kwado.create') }}" class="btn btn-warning">
                <i class="bi bi-plus-circle me-1"></i>Nouveau Service
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        @if(session('dernierServiceId'))
        <div class="mt-2">
            <button wire:click="telechargerRecu({{ session('dernierServiceId') }})" class="btn btn-sm btn-outline-success">
                <i class="bi bi-printer me-1"></i>Imprimer le reçu
            </button>
        </div>
        @endif
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Stats du jour -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 bg-warning bg-opacity-10">
                <div class="card-body py-3 text-center">
                    <h4 class="fw-bold text-warning mb-1">{{ $totalJour }}</h4>
                    <small class="text-muted">Services aujourd'hui</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 bg-success bg-opacity-10">
                <div class="card-body py-3 text-center">
                    <h4 class="fw-bold text-success mb-1">{{ number_format($recettesJour) }} FC</h4>
                    <small class="text-muted">Recettes du jour</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 bg-info bg-opacity-10">
                <div class="card-body py-3 text-center">
                    <h4 class="fw-bold text-info mb-1">{{ $totalMois }}</h4>
                    <small class="text-muted">Services ce mois</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 bg-primary bg-opacity-10">
                <div class="card-body py-3 text-center">
                    <h4 class="fw-bold text-primary mb-1">{{ number_format($recettesMois) }} FC</h4>
                    <small class="text-muted">Recettes du mois</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Rechercher</label>
                    <input type="text" wire:model.live="search" class="form-control" placeholder="N° service, plaque...">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Type</label>
                    <select wire:model.live="filterType" class="form-select">
                        <option value="">Tous</option>
                        @foreach(\App\Models\KwadoService::TYPES_SERVICE as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Statut</label>
                    <select wire:model.live="filterStatut" class="form-select">
                        <option value="">Tous</option>
                        <option value="payé">Payé</option>
                        <option value="en_attente">En attente</option>
                        <option value="annulé">Annulé</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Date</label>
                    <input type="date" wire:model.live="filterDate" class="form-control">
                </div>
                <div class="col-md-2">
                    <button wire:click="resetFilters" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-x-lg me-1"></i>Réinitialiser
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des services -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">N° Service</th>
                            <th>Véhicule</th>
                            <th>Type</th>
                            <th>Position</th>
                            <th>Montant</th>
                            <th>Pièces</th>
                            <th>Mode</th>
                            <th>Statut</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($services as $service)
                        <tr>
                            <td class="ps-4">
                                <span class="fw-medium">{{ $service->numero_service }}</span>
                                @if($service->is_externe)
                                <span class="badge bg-secondary ms-1" style="font-size: 0.6rem;">EXT</span>
                                @endif
                            </td>
                            <td>
                                <div>
                                    <span class="badge bg-light text-dark">
                                        <i class="bi bi-bicycle me-1"></i>{{ $service->plaque }}
                                    </span>
                                    @if($service->proprietaire_nom)
                                    <small class="text-muted d-block">{{ $service->proprietaire_nom }}</small>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-warning bg-opacity-10 text-warning">
                                    {{ $service->type_service_label }}
                                </span>
                            </td>
                            <td class="text-muted small">
                                {{ $service->position_pneu_label ?: '-' }}
                            </td>
                            <td class="fw-bold text-success">{{ number_format($service->montant_encaisse) }} FC</td>
                            <td class="text-muted">
                                @if(($service->cout_pieces ?? 0) > 0)
                                {{ number_format($service->cout_pieces) }} FC
                                @else
                                -
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">
                                    <i class="bi bi-{{ $service->mode_paiement === 'cash' ? 'cash' : 'phone' }} me-1"></i>
                                    {{ $service->mode_paiement === 'cash' ? 'Cash' : 'Mobile' }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $statutColors = ['payé' => 'success', 'en_attente' => 'warning', 'annulé' => 'danger'];
                                @endphp
                                <span class="badge badge-soft-{{ $statutColors[$service->statut_paiement] ?? 'secondary' }}">
                                    {{ ucfirst($service->statut_paiement) }}
                                </span>
                            </td>
                            <td class="small text-muted">
                                {{ $service->date_service?->format('d/m/Y') }}
                                <br><i class="bi bi-clock me-1"></i>{{ $service->date_service?->format('H:i') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="bi bi-gear-wide-connected fs-1 d-block mb-3 text-warning opacity-50"></i>
                                <p class="mb-0">Aucun service KWADO enregistré</p>
                                <a href="{{ route('cleaner.kwado.create') }}" class="btn btn-sm btn-warning mt-3">
                                    <i class="bi bi-plus-lg me-1"></i>Enregistrer un service
                                </a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if(($services ?? collect())->hasPages())
        <div class="card-footer bg-light">
            {{ $services->links() }}
        </div>
        @endif
    </div>
</div>

