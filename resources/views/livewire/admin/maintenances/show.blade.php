<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-tools me-2 text-warning"></i>Détails Maintenance #{{ $maintenance->id }}
            </h4>
            <p class="text-muted mb-0">{{ $maintenance->date_intervention?->format('d/m/Y') }}</p>
        </div>
        <a href="{{ route('admin.maintenances.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Retour
        </a>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <!-- Informations -->
            <div class="card mb-4">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-info-circle me-2 text-primary"></i>Informations</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Moto</label>
                            <p class="fw-medium mb-0">{{ $maintenance->moto?->plaque_immatriculation ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Type</label>
                            <p class="fw-medium mb-0">
                                <span class="badge bg-{{ $maintenance->type === 'preventive' ? 'info' : ($maintenance->type === 'corrective' ? 'warning' : 'secondary') }}">
                                    {{ ucfirst($maintenance->type ?? 'N/A') }}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Date d'intervention</label>
                            <p class="fw-medium mb-0">{{ $maintenance->date_intervention?->format('d/m/Y') ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Statut</label>
                            <p class="fw-medium mb-0">
                                <span class="badge bg-{{ $maintenance->statut === 'termine' ? 'success' : ($maintenance->statut === 'en_cours' ? 'warning' : 'secondary') }}">
                                    {{ ucfirst(str_replace('_', ' ', $maintenance->statut ?? 'N/A')) }}
                                </span>
                            </p>
                        </div>
                        <div class="col-12">
                            <label class="form-label small text-muted">Description</label>
                            <p class="mb-0">{{ $maintenance->description ?? 'Aucune description' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Technicien -->
            <div class="card mb-4">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-person-badge me-2 text-info"></i>Technicien / Garage</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Nom</label>
                            <p class="fw-medium mb-0">{{ $maintenance->technicien_garage_nom ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Téléphone</label>
                            <p class="fw-medium mb-0">{{ $maintenance->technicien_telephone ?? 'N/A' }}</p>
                        </div>
                        <div class="col-12">
                            <label class="form-label small text-muted">Adresse</label>
                            <p class="mb-0">{{ $maintenance->garage_adresse ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Coûts -->
            <div class="card mb-4">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-cash-coin me-2 text-success"></i>Coûts</h6>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Pièces</span>
                            <span class="fw-bold">{{ number_format($maintenance->cout_pieces ?? 0) }} FC</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Main d'œuvre</span>
                            <span class="fw-bold">{{ number_format($maintenance->cout_main_oeuvre ?? 0) }} FC</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between bg-light">
                            <span class="fw-bold">Total</span>
                            <span class="fw-bold text-success">{{ number_format(($maintenance->cout_pieces ?? 0) + ($maintenance->cout_main_oeuvre ?? 0)) }} FC</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Payé par</span>
                            <span class="badge bg-light text-dark">{{ ucfirst($maintenance->qui_a_paye ?? 'N/A') }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Prochain entretien -->
            @if($maintenance->prochain_entretien)
            <div class="card">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-calendar-event me-2 text-warning"></i>Prochain entretien</h6>
                </div>
                <div class="card-body text-center">
                    <h5 class="mb-0">{{ $maintenance->prochain_entretien?->format('d/m/Y') }}</h5>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
