<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-droplet me-2 text-info"></i>Dashboard Lavage
            </h4>
            <p class="text-muted mb-0">Bienvenue, {{ auth()->user()->name }}</p>
        </div>
        <a href="{{ route('cleaner.lavages.create') }}" class="btn btn-info">
            <i class="bi bi-plus-circle me-1"></i>Nouveau Lavage
        </a>
    </div>

    <!-- Statistiques du jour -->
    <div class="row g-4 mb-4">
        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-info bg-opacity-10 rounded-circle p-3">
                                <i class="bi bi-droplet-half text-info fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0 fw-bold">{{ $lavagesAujourdhui }}</h3>
                            <span class="text-muted">Lavages aujourd'hui</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-opacity-10 rounded-circle p-3">
                                <i class="bi bi-cash-stack text-success fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0 fw-bold">{{ number_format($chiffreAffairesJour) }} FC</h3>
                            <span class="text-muted">Recettes du jour</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                                <i class="bi bi-calendar-month text-warning fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0 fw-bold">{{ number_format($chiffreAffairesMois) }} FC</h3>
                            <span class="text-muted">Recettes du mois</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                                <i class="bi bi-percent text-primary fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0 fw-bold">{{ number_format($partOkamiMois) }} FC</h3>
                            <span class="text-muted">Part OKAMI (mois)</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Répartition et Prix -->
    <div class="row g-4 mb-4">
        <!-- Répartition du jour -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold">
                        <i class="bi bi-pie-chart me-2 text-info"></i>Répartition du jour
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="border-end">
                                <span class="badge bg-info mb-2">Internes</span>
                                <h4 class="fw-bold mb-0">{{ $lavagesInternes }}</h4>
                                <small class="text-muted">Motos système</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border-end">
                                <span class="badge bg-secondary mb-2">Externes</span>
                                <h4 class="fw-bold mb-0">{{ $lavagesExternes }}</h4>
                                <small class="text-muted">Motos hors système</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <span class="badge bg-warning mb-2">Part OKAMI</span>
                            <h4 class="fw-bold mb-0">{{ number_format($partOkamiJour) }} FC</h4>
                            <small class="text-muted">20% des internes</small>
                        </div>
                    </div>

                    <hr class="my-3">

                    <div class="alert alert-light mb-0">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-info-circle text-info"></i>
                            <small>
                                <strong>Motos du système:</strong> 80% pour vous, 20% pour OKAMI<br>
                                <strong>Motos externes:</strong> 100% pour vous
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tarifs configurés -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold">
                        <i class="bi bi-tags me-2 text-success"></i>Tarifs de lavage
                    </h6>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <div>
                                <i class="bi bi-droplet text-info me-2"></i>
                                <strong>Lavage Simple</strong>
                                <small class="text-muted d-block">Nettoyage extérieur basique</small>
                            </div>
                            <span class="badge bg-info fs-6">{{ number_format($prixSimple) }} FC</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <div>
                                <i class="bi bi-droplet-fill text-primary me-2"></i>
                                <strong>Lavage Complet</strong>
                                <small class="text-muted d-block">Intérieur + Extérieur</small>
                            </div>
                            <span class="badge bg-primary fs-6">{{ number_format($prixComplet) }} FC</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <div>
                                <i class="bi bi-stars text-warning me-2"></i>
                                <strong>Lavage Premium</strong>
                                <small class="text-muted d-block">Complet + Cirage + Parfum</small>
                            </div>
                            <span class="badge bg-warning fs-6">{{ number_format($prixPremium) }} FC</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Derniers lavages -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold">
                <i class="bi bi-clock-history me-2 text-secondary"></i>Derniers lavages
            </h6>
            <a href="{{ route('cleaner.lavages.index') }}" class="btn btn-sm btn-outline-info">
                Voir tout <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
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
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($derniersLavages as $lavage)
                        <tr>
                            <td>
                                <span class="fw-semibold">{{ $lavage->numero_lavage }}</span>
                            </td>
                            <td>
                                @if($lavage->is_externe)
                                <span class="badge bg-secondary">Externe</span>
                                {{ $lavage->plaque_externe }}
                                @else
                                <span class="badge bg-info">Système</span>
                                {{ $lavage->moto?->plaque_immatriculation ?? 'N/A' }}
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $lavage->type_lavage === 'premium' ? 'warning' : ($lavage->type_lavage === 'complet' ? 'primary' : 'info') }}">
                                    {{ ucfirst($lavage->type_lavage) }}
                                </span>
                            </td>
                            <td>{{ number_format($lavage->prix_final) }} FC</td>
                            <td class="fw-bold text-success">{{ number_format($lavage->part_cleaner) }} FC</td>
                            <td>
                                <small>{{ $lavage->date_lavage->format('d/m/Y H:i') }}</small>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                Aucun lavage enregistré
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

