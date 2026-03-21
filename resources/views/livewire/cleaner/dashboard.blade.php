<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-droplet me-2 text-info"></i>Dashboard Lavage
            </h4>
            <p class="text-muted mb-0">Bienvenue, {{ auth()->user()->name }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('cleaner.depenses.create') }}" class="btn btn-outline-danger">
                <i class="bi bi-dash-circle me-1"></i>Dépense
            </a>
            <a href="{{ route('cleaner.kwado.create') }}" class="btn btn-warning">
                <i class="bi bi-gear-wide-connected me-1"></i>KWADO
            </a>
            <a href="{{ route('cleaner.lavages.create') }}" class="btn btn-info">
                <i class="bi bi-plus-circle me-1"></i>Nouveau Lavage
            </a>
        </div>
    </div>

    <!-- Solde en caisse - Mis en évidence -->
    <div class="card border-0 shadow-sm mb-4 bg-gradient" style="background: linear-gradient(135deg, #198754 0%, #20c997 100%);">
        <div class="card-body py-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-white bg-opacity-25 rounded-circle p-3">
                            <i class="bi bi-wallet2 fs-2" style="color: #fff;"></i>
                        </div>
                        <div>
                            <small class="d-block" style="color: rgba(255,255,255,0.85);">Solde en caisse</small>
                            <h2 class="mb-0 fw-bold" style="color: #fff; text-shadow: 1px 1px 2px rgba(0,0,0,0.2);">{{ number_format($soldeActuel) }} FC</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <a href="{{ route('cleaner.depenses.index') }}" class="btn btn-light btn-sm">
                        <i class="bi bi-list me-1"></i>Voir dépenses
                    </a>
                </div>
            </div>
        </div>
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
                            <div class="bg-danger bg-opacity-10 rounded-circle p-3">
                                <i class="bi bi-dash-circle text-danger fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0 fw-bold">{{ number_format($depensesJour) }} FC</h3>
                            <span class="text-muted">Dépenses du jour</span>
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
                            <div class="bg-{{ $beneficeNetMois >= 0 ? 'primary' : 'warning' }} bg-opacity-10 rounded-circle p-3">
                                <i class="bi bi-graph-up-arrow text-{{ $beneficeNetMois >= 0 ? 'primary' : 'warning' }} fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0 fw-bold text-{{ $beneficeNetMois >= 0 ? 'primary' : 'warning' }}">{{ number_format($beneficeNetMois) }} FC</h3>
                            <span class="text-muted">Bénéfice net (mois)</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats mensuelles -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 border-start border-4 border-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted">Recettes du mois</small>
                            <h4 class="fw-bold mb-0 text-success">{{ number_format($chiffreAffairesMois) }} FC</h4>
                        </div>
                        <i class="bi bi-arrow-up-circle text-success fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 border-start border-4 border-danger">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted">Dépenses du mois</small>
                            <h4 class="fw-bold mb-0 text-danger">{{ number_format($depensesMois) }} FC</h4>
                        </div>
                        <i class="bi bi-arrow-down-circle text-danger fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 border-start border-4 border-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted">Part OKAMI (mois)</small>
                            <h4 class="fw-bold mb-0 text-warning">{{ number_format($partOkamiMois) }} FC</h4>
                        </div>
                        <i class="bi bi-percent text-warning fs-3"></i>
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

    <!-- Section KWADO -->
    <div class="card border-0 shadow-sm mb-4 border-start border-4 border-warning">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold">
                <i class="bi bi-gear-wide-connected me-2 text-warning"></i>Services KWADO (Pneus)
            </h6>
            <a href="{{ route('cleaner.kwado.create') }}" class="btn btn-sm btn-warning">
                <i class="bi bi-plus-circle me-1"></i>Nouveau
            </a>
        </div>
        <div class="card-body">
            <div class="row g-3 text-center">
                <div class="col-md-3">
                    <div class="p-3 bg-warning bg-opacity-10 rounded">
                        <h4 class="fw-bold text-warning mb-0">{{ $kwadoJour }}</h4>
                        <small class="text-muted">Services aujourd'hui</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-3 bg-success bg-opacity-10 rounded">
                        <h4 class="fw-bold text-success mb-0">{{ number_format($kwadoRecettesJour) }} FC</h4>
                        <small class="text-muted">Recettes KWADO jour</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-3 bg-info bg-opacity-10 rounded">
                        <h4 class="fw-bold text-info mb-0">{{ $kwadoMois }}</h4>
                        <small class="text-muted">Services ce mois</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-3 bg-primary bg-opacity-10 rounded">
                        <h4 class="fw-bold text-primary mb-0">{{ number_format($kwadoRecettesMois) }} FC</h4>
                        <small class="text-muted">Recettes KWADO mois</small>
                    </div>
                </div>
            </div>

            @if(count($derniersKwado) > 0)
            <hr class="my-3">
            <h6 class="fw-bold small text-muted mb-2"><i class="bi bi-clock-history me-1"></i>Derniers services KWADO</h6>
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>N°</th>
                            <th>Véhicule</th>
                            <th>Service</th>
                            <th>Montant</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($derniersKwado as $kwado)
                        <tr>
                            <td class="fw-semibold">{{ $kwado->numero_service }}</td>
                            <td>
                                @if($kwado->is_externe)
                                <span class="badge bg-secondary" style="font-size: 0.65rem;">EXT</span>
                                @endif
                                {{ $kwado->plaque }}
                            </td>
                            <td><span class="badge bg-warning bg-opacity-10 text-warning">{{ $kwado->type_service_label }}</span></td>
                            <td class="fw-bold text-success">{{ number_format($kwado->montant_encaisse) }} FC</td>
                            <td><small>{{ $kwado->date_service?->format('d/m H:i') }}</small></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="text-end mt-2">
                <a href="{{ route('cleaner.kwado.index') }}" class="btn btn-sm btn-outline-warning">
                    Voir tous les services <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
            @endif
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

