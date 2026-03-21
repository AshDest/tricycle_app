<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-gear-wide-connected me-2 text-warning"></i>Services KWADO
            </h4>
            <p class="text-muted mb-0">Tous les services de réparation de pneus</p>
        </div>
        <div class="d-flex gap-2">
            <button wire:click="exportPdf" class="btn btn-sm btn-danger" title="Exporter PDF">
                <i class="bi bi-file-pdf me-1"></i>PDF
            </button>
            <button wire:click="$refresh" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-arrow-clockwise"></i>
            </button>
        </div>
    </div>

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 bg-warning bg-opacity-10">
                <div class="card-body py-3 text-center">
                    <h4 class="fw-bold text-warning mb-1">{{ $stats['total_jour'] ?? 0 }}</h4>
                    <small class="text-muted">Services aujourd'hui</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 bg-success bg-opacity-10">
                <div class="card-body py-3 text-center">
                    <h4 class="fw-bold text-success mb-1">{{ number_format($stats['recettes_jour'] ?? 0) }} FC</h4>
                    <small class="text-muted">Recettes du jour</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 bg-info bg-opacity-10">
                <div class="card-body py-3 text-center">
                    <h4 class="fw-bold text-info mb-1">{{ $stats['total_mois'] ?? 0 }}</h4>
                    <small class="text-muted">Services ce mois</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 bg-primary bg-opacity-10">
                <div class="card-body py-3 text-center">
                    <h4 class="fw-bold text-primary mb-1">{{ number_format($stats['recettes_mois'] ?? 0) }} FC</h4>
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
                    <input type="text" wire:model.live="search" class="form-control" placeholder="N° service, plaque, opérateur...">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Opérateur</label>
                    <select wire:model.live="filterCleaner" class="form-select">
                        <option value="">Tous</option>
                        @foreach($cleaners as $cleaner)
                        <option value="{{ $cleaner->id }}">{{ $cleaner->user->name ?? 'N/A' }}</option>
                        @endforeach
                    </select>
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
                    <label class="form-label small fw-semibold">Du</label>
                    <input type="date" wire:model.live="dateDebut" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Au</label>
                    <input type="date" wire:model.live="dateFin" class="form-control">
                </div>
                <div class="col-md-1">
                    <button wire:click="resetFilters" class="btn btn-outline-secondary w-100" title="Réinitialiser">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">N° Service</th>
                            <th>Opérateur</th>
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
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar avatar-sm bg-warning bg-opacity-10 text-warning rounded-circle">
                                        {{ strtoupper(substr($service->cleaner->user->name ?? 'C', 0, 1)) }}
                                    </div>
                                    <span class="small">{{ $service->cleaner->user->name ?? 'N/A' }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">
                                    <i class="bi bi-bicycle me-1"></i>{{ $service->plaque }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-warning bg-opacity-10 text-warning small">
                                    {{ $service->type_service_label }}
                                </span>
                            </td>
                            <td class="text-muted small">{{ $service->position_pneu_label ?: '-' }}</td>
                            <td class="fw-bold text-success">{{ number_format($service->montant_encaisse) }} FC</td>
                            <td class="text-muted">
                                {{ ($service->cout_pieces ?? 0) > 0 ? number_format($service->cout_pieces) . ' FC' : '-' }}
                            </td>
                            <td>
                                <span class="badge bg-light text-dark small">
                                    <i class="bi bi-{{ $service->mode_paiement === 'cash' ? 'cash' : 'phone' }} me-1"></i>
                                    {{ $service->mode_paiement === 'cash' ? 'Cash' : 'Mobile' }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $sc = ['payé' => 'success', 'en_attente' => 'warning', 'annulé' => 'danger'];
                                @endphp
                                <span class="badge badge-soft-{{ $sc[$service->statut_paiement] ?? 'secondary' }}">
                                    {{ ucfirst($service->statut_paiement) }}
                                </span>
                            </td>
                            <td class="small text-muted">
                                {{ $service->date_service?->format('d/m/Y') }}
                                <br>{{ $service->date_service?->format('H:i') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-5 text-muted">
                                <i class="bi bi-gear-wide-connected fs-1 d-block mb-3 text-warning opacity-50"></i>
                                <p class="mb-0">Aucun service KWADO enregistré</p>
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

