<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-exclamation-triangle me-2 text-danger"></i>Détails de l'Accident #{{ $accident->id }}
            </h4>
            <p class="text-muted mb-0">{{ $accident->date_accident?->format('d/m/Y H:i') }}</p>
        </div>
        <a href="{{ route('admin.accidents.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Retour
        </a>
    </div>

    <div class="row g-4">
        <!-- Informations principales -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-info-circle me-2 text-primary"></i>Informations de l'accident</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Date et heure</label>
                            <p class="fw-medium mb-0">{{ $accident->date_heure?->format('d/m/Y à H:i') ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Lieu</label>
                            <p class="fw-medium mb-0">{{ $accident->lieu ?? 'N/A' }}</p>
                        </div>
                        <div class="col-12">
                            <label class="form-label small text-muted">Description</label>
                            <p class="mb-0">{{ $accident->description ?? 'Aucune description' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Moto et Motard -->
            <div class="card mb-4">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-bicycle me-2 text-info"></i>Véhicule et Conducteur</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Moto</label>
                            <p class="fw-medium mb-0">{{ $accident->moto?->plaque_immatriculation ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Motard</label>
                            <p class="fw-medium mb-0">{{ $accident->motard?->user?->name ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Déclarations -->
            <div class="card mb-4">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-chat-quote me-2 text-warning"></i>Déclarations</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label small text-muted">Déclaration du motard</label>
                        <p class="mb-0">{{ $accident->declaration_motard ?? 'Aucune déclaration' }}</p>
                    </div>
                    @if($accident->declaration_temoin)
                    <div>
                        <label class="form-label small text-muted">Déclaration du témoin</label>
                        <p class="mb-0">{{ $accident->declaration_temoin }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Statut -->
            <div class="card mb-4">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-flag me-2 text-secondary"></i>Statut</h6>
                </div>
                <div class="card-body text-center">
                    @php
                        $statutColors = [
                            'declare' => 'warning',
                            'en_cours' => 'info',
                            'repare' => 'success',
                            'clos' => 'secondary',
                        ];
                    @endphp
                    <span class="badge bg-{{ $statutColors[$accident->statut] ?? 'secondary' }} fs-6 px-3 py-2">
                        {{ ucfirst(str_replace('_', ' ', $accident->statut ?? 'N/A')) }}
                    </span>
                </div>
            </div>

            <!-- Coûts -->
            <div class="card mb-4">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-cash-coin me-2 text-success"></i>Coûts</h6>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Coût estimé</span>
                            <span class="fw-bold">{{ number_format($accident->cout_estime ?? 0) }} FC</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Coût réel</span>
                            <span class="fw-bold text-success">{{ number_format($accident->cout_reel ?? 0) }} FC</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Pris en charge par</span>
                            <span class="badge bg-light text-dark">{{ ucfirst($accident->prise_en_charge ?? 'N/A') }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Photos -->
            @if($accident->photos && count(json_decode($accident->photos, true) ?? []) > 0)
            <div class="card">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-images me-2 text-info"></i>Photos</h6>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        @foreach(json_decode($accident->photos, true) ?? [] as $photo)
                        <div class="col-6">
                            <img src="{{ asset('storage/' . $photo) }}" class="img-fluid rounded" alt="Photo accident">
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
