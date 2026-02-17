<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-exclamation-triangle me-2 text-danger"></i>Détails de l'Accident
            </h4>
            <p class="text-muted mb-0">{{ $accident->date_heure?->format('d/m/Y à H:i') }} - {{ $accident->lieu }}</p>
        </div>
        <a href="{{ route('supervisor.accidents.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Retour
        </a>
    </div>

    <div class="row g-4">
        <!-- Informations principales -->
        <div class="col-lg-8">
            <!-- Détails de l'accident -->
            <div class="card mb-4">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-info-circle me-2 text-primary"></i>Informations de l'accident</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Date et heure</label>
                            <p class="fw-medium mb-0">{{ $accident->date_heure?->format('d/m/Y à H:i') ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Lieu</label>
                            <p class="fw-medium mb-0">{{ $accident->lieu ?? 'N/A' }}</p>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-muted small">Description de l'accident</label>
                            <p class="mb-0">{{ $accident->description ?? 'Aucune description' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Témoignages -->
            <div class="card mb-4">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-chat-quote me-2 text-info"></i>Témoignages</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Déclaration du motard</label>
                            <p class="mb-0">{{ $accident->temoignage_motard ?? 'Aucune déclaration' }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Témoignage externe</label>
                            <p class="mb-0">{{ $accident->temoignage_temoin ?? 'Aucun témoin' }}</p>
                            @if($accident->temoin_nom)
                            <small class="text-muted">
                                <i class="bi bi-person me-1"></i>{{ $accident->temoin_nom }}
                                @if($accident->temoin_telephone)
                                - {{ $accident->temoin_telephone }}
                                @endif
                            </small>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Évaluation des dommages -->
            <div class="card mb-4">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-clipboard-data me-2 text-warning"></i>Évaluation des dommages</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label text-muted small">Estimation initiale</label>
                            <p class="fw-medium mb-0">{{ number_format($accident->estimation_cout ?? 0) }} FC</p>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small">Coût réel</label>
                            <p class="fw-bold text-danger fs-5 mb-0">{{ number_format($accident->cout_reel ?? 0) }} FC</p>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small">Écart</label>
                            @php
                                $ecart = ($accident->cout_reel ?? 0) - ($accident->estimation_cout ?? 0);
                            @endphp
                            <p class="fw-medium mb-0 text-{{ $ecart > 0 ? 'danger' : ($ecart < 0 ? 'success' : 'muted') }}">
                                {{ $ecart > 0 ? '+' : '' }}{{ number_format($ecart) }} FC
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Pièces endommagées</label>
                            <p class="mb-0">{{ $accident->pieces_endommagees ?? 'Non renseigné' }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Prise en charge</label>
                            <p class="fw-medium mb-0">
                                @php
                                    $priseEnChargeLabels = [
                                        'motard' => 'Motard',
                                        'proprietaire' => 'Propriétaire',
                                        'assurance' => 'Assurance',
                                        'nth' => 'NTH Sarl',
                                    ];
                                @endphp
                                {{ $priseEnChargeLabels[$accident->prise_en_charge] ?? ucfirst($accident->prise_en_charge ?? 'N/A') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Suivi des réparations -->
            <div class="card">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-tools me-2 text-success"></i>Suivi des réparations</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Réparation programmée</label>
                            <p class="fw-medium mb-0">{{ $accident->reparation_programmee_at?->format('d/m/Y') ?? 'Non planifiée' }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Réparation terminée</label>
                            <p class="fw-medium mb-0">{{ $accident->reparation_terminee_at?->format('d/m/Y') ?? 'En cours' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Statut et Gravité -->
            <div class="card mb-4">
                <div class="card-body text-center py-4">
                    @php
                        $graviteColors = [
                            'mineur' => 'success',
                            'modere' => 'warning',
                            'grave' => 'danger',
                        ];
                        $statutColors = [
                            'declare' => 'warning',
                            'en_evaluation' => 'info',
                            'en_reparation' => 'primary',
                            'cloture' => 'success',
                        ];
                    @endphp
                    <div class="mb-3">
                        <span class="badge badge-soft-{{ $graviteColors[$accident->gravite] ?? 'secondary' }} fs-6 px-4 py-2">
                            Gravité: {{ ucfirst($accident->gravite ?? 'N/A') }}
                        </span>
                    </div>
                    <span class="badge badge-soft-{{ $statutColors[$accident->statut] ?? 'secondary' }} fs-6 px-4 py-2">
                        {{ ucfirst(str_replace('_', ' ', $accident->statut ?? 'N/A')) }}
                    </span>
                    <hr class="my-3">
                    <small class="text-muted">Déclaré le {{ $accident->created_at?->format('d/m/Y à H:i') }}</small>
                </div>
            </div>

            <!-- Moto -->
            <div class="card mb-4">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-bicycle me-2 text-info"></i>Moto impliquée</h6>
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong>Plaque:</strong> {{ $accident->moto->plaque_immatriculation ?? 'N/A' }}</p>
                    <p class="mb-2"><strong>Châssis:</strong> {{ $accident->moto->numero_chassis ?? 'N/A' }}</p>
                    <p class="mb-0"><strong>Propriétaire:</strong> {{ $accident->moto->proprietaire->user->name ?? 'N/A' }}</p>
                </div>
            </div>

            <!-- Motard -->
            <div class="card mb-4">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-person me-2 text-primary"></i>Motard impliqué</h6>
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong>Nom:</strong> {{ $accident->motard->user->name ?? 'N/A' }}</p>
                    <p class="mb-0"><strong>Téléphone:</strong> {{ $accident->motard->telephone ?? 'N/A' }}</p>
                </div>
            </div>

            <!-- Notes admin -->
            @if($accident->notes_admin)
            <div class="card">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-sticky me-2 text-warning"></i>Notes administrateur</h6>
                </div>
                <div class="card-body">
                    <p class="mb-0">{{ $accident->notes_admin }}</p>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

