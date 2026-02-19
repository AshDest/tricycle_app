<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-tools me-2 text-info"></i>Détails de la Maintenance
                @if($maintenance->accident_id)
                <span class="badge bg-danger ms-2">Suite à accident</span>
                @endif
            </h4>
            <p class="text-muted mb-0">Moto: {{ $maintenance->moto->plaque_immatriculation ?? 'N/A' }}</p>
        </div>
        <a href="{{ route('supervisor.maintenances.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Retour
        </a>
    </div>

    <div class="row g-4">
        <!-- Informations principales -->
        <div class="col-lg-8">
            <!-- Accident lié -->
            @if($maintenance->accident)
            <div class="card mb-4 border-danger">
                <div class="card-header py-3 bg-danger bg-opacity-10">
                    <h6 class="mb-0 fw-bold text-danger"><i class="bi bi-exclamation-triangle me-2"></i>Accident lié</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label text-muted small">Date de l'accident</label>
                            <p class="fw-medium mb-0">{{ $maintenance->accident->date_heure?->format('d/m/Y H:i') }}</p>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small">Lieu</label>
                            <p class="fw-medium mb-0">{{ $maintenance->accident->lieu }}</p>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small">Gravité</label>
                            @php
                                $graviteColors = ['mineur' => 'success', 'modere' => 'warning', 'grave' => 'danger'];
                            @endphp
                            <p class="mb-0">
                                <span class="badge badge-soft-{{ $graviteColors[$maintenance->accident->gravite] ?? 'secondary' }}">
                                    {{ ucfirst($maintenance->accident->gravite) }}
                                </span>
                            </p>
                        </div>
                        <div class="col-12">
                            <a href="{{ route('supervisor.accidents.show', $maintenance->accident) }}" class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-eye me-1"></i>Voir les détails de l'accident
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <div class="card mb-4">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-info-circle me-2 text-primary"></i>Informations de l'intervention</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Type de maintenance</label>
                            <p class="fw-medium mb-0">
                                @php
                                    $typeLabels = [
                                        'preventive' => 'Préventive (révision, vidange)',
                                        'corrective' => 'Corrective (réparation)',
                                        'remplacement' => 'Remplacement de pièces',
                                    ];
                                @endphp
                                {{ $typeLabels[$maintenance->type] ?? ucfirst($maintenance->type ?? 'N/A') }}
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Date d'intervention</label>
                            <p class="fw-medium mb-0">{{ $maintenance->date_intervention?->format('d/m/Y à H:i') ?? 'N/A' }}</p>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-muted small">Description du problème</label>
                            <p class="mb-0">{{ $maintenance->description ?? 'Aucune description' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Technicien / Garage -->
            <div class="card mb-4">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-person-gear me-2 text-warning"></i>Technicien / Garage</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label text-muted small">Nom du technicien/garage</label>
                            <p class="fw-medium mb-0">{{ $maintenance->technicien_garage_nom ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small">Téléphone</label>
                            <p class="fw-medium mb-0">{{ $maintenance->technicien_telephone ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small">Adresse</label>
                            <p class="fw-medium mb-0">{{ $maintenance->garage_adresse ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Coûts -->
            <div class="card">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-cash-stack me-2 text-success"></i>Coûts de l'intervention</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label text-muted small">Coût des pièces</label>
                            <p class="fw-medium mb-0">{{ number_format($maintenance->cout_pieces ?? 0) }} FC</p>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small">Coût main d'œuvre</label>
                            <p class="fw-medium mb-0">{{ number_format($maintenance->cout_main_oeuvre ?? 0) }} FC</p>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small">Coût Total</label>
                            <p class="fw-bold text-success fs-5 mb-0">{{ number_format($maintenance->cout_total) }} FC</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Qui a payé ?</label>
                            <p class="fw-medium mb-0">
                                @php
                                    $payeurLabels = [
                                        'motard' => 'Motard',
                                        'proprietaire' => 'Propriétaire',
                                        'nth' => 'NTH Sarl',
                                        'okami' => 'OKAMI',
                                    ];
                                @endphp
                                {{ $payeurLabels[$maintenance->qui_a_paye] ?? ucfirst($maintenance->qui_a_paye ?? 'N/A') }}
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Prochain entretien</label>
                            <p class="fw-medium mb-0">{{ $maintenance->prochain_entretien?->format('d/m/Y') ?? 'Non planifié' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Statut -->
            <div class="card mb-4">
                <div class="card-body text-center py-4">
                    @php
                        $statutColors = [
                            'en_attente' => 'warning',
                            'en_cours' => 'info',
                            'termine' => 'success',
                        ];
                    @endphp
                    <span class="badge badge-soft-{{ $statutColors[$maintenance->statut] ?? 'secondary' }} fs-6 px-4 py-2">
                        {{ ucfirst(str_replace('_', ' ', $maintenance->statut ?? 'N/A')) }}
                    </span>
                    <hr class="my-3">
                    <small class="text-muted">Créé le {{ $maintenance->created_at?->format('d/m/Y à H:i') }}</small>
                </div>
            </div>

            <!-- Moto -->
            <div class="card mb-4">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-bicycle me-2 text-info"></i>Moto concernée</h6>
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong>Plaque:</strong> {{ $maintenance->moto->plaque_immatriculation ?? 'N/A' }}</p>
                    <p class="mb-2"><strong>Châssis:</strong> {{ $maintenance->moto->numero_chassis ?? 'N/A' }}</p>
                    <p class="mb-0"><strong>Propriétaire:</strong> {{ $maintenance->moto->proprietaire->user->name ?? 'N/A' }}</p>
                </div>
            </div>

            <!-- Motard -->
            @if($maintenance->motard)
            <div class="card mb-4">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-person me-2 text-primary"></i>Motard</h6>
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong>Nom:</strong> {{ $maintenance->motard->user->name ?? 'N/A' }}</p>
                    <p class="mb-0"><strong>Téléphone:</strong> {{ $maintenance->motard->telephone ?? 'N/A' }}</p>
                </div>
            </div>
            @endif

            <!-- Notes -->
            @if($maintenance->notes)
            <div class="card">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-sticky me-2 text-warning"></i>Notes</h6>
                </div>
                <div class="card-body">
                    <p class="mb-0">{{ $maintenance->notes }}</p>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

