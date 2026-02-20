<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-geo-alt me-2 text-info"></i>Tableau de Bord Collecteur
            </h4>
            <p class="text-muted mb-0">
                @if($collecteur)
                    Bienvenue, {{ auth()->user()->name }}
                    <span class="badge bg-light text-dark ms-2">{{ $collecteur->zone_affectation ?? 'Zone non définie' }}</span>
                @else
                    <span class="text-danger">Aucun profil collecteur associé</span>
                @endif
            </p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-light text-dark px-3 py-2">
                <i class="bi bi-calendar3 me-2"></i>{{ now()->translatedFormat('l d F Y') }}
            </span>
            <button class="btn btn-sm btn-outline-primary" wire:click="$refresh">
                <i class="bi bi-arrow-clockwise"></i>
            </button>
        </div>
    </div>

    @if($collecteur)
    <!-- Stats Row -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card h-100 border-start border-success border-4">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small text-uppercase fw-semibold mb-2">Encaissé Aujourd'hui</p>
                        <h3 class="fw-bold text-success mb-1">{{ number_format($totalEncaisse) }} FC</h3>
                        <small class="text-muted">Total collecté</small>
                    </div>
                    <div class="stat-icon bg-success bg-opacity-10 text-success">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card h-100 border-start border-primary border-4">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small text-uppercase fw-semibold mb-2">Collectes Réussies</p>
                        <h3 class="fw-bold text-primary mb-1">{{ $collectesReussies }}</h3>
                        <small class="text-muted">caissiers visités</small>
                    </div>
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-check2-circle"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card h-100 border-start border-warning border-4">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small text-uppercase fw-semibold mb-2">Tournées du Jour</p>
                        <h3 class="fw-bold text-warning mb-1">{{ $totalTourneesJour }}</h3>
                        <small class="text-{{ $tourneesTerminees > 0 ? 'success' : 'muted' }}">
                            {{ $tourneesTerminees }} terminée(s)
                        </small>
                    </div>
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                        <i class="bi bi-signpost-2"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card h-100 border-start border-info border-4">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small text-uppercase fw-semibold mb-2">Solde Caisse</p>
                        <h3 class="fw-bold text-info mb-1">{{ number_format($soldeCaisse) }} FC</h3>
                        <small class="text-muted">disponible pour paiements</small>
                    </div>
                    <div class="stat-icon bg-info bg-opacity-10 text-info">
                        <i class="bi bi-clock-history"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="card mb-4">
        <div class="card-header py-3">
            <h6 class="mb-0 fw-bold"><i class="bi bi-lightning me-2 text-warning"></i>Actions Rapides</h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <a href="{{ route('collector.tournee.index') }}" class="btn btn-primary w-100 py-3">
                        <i class="bi bi-play-circle fs-4 d-block mb-2"></i>
                        Ma Tournée du Jour
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="{{ route('collector.collectes.index') }}" class="btn btn-outline-success w-100 py-3">
                        <i class="bi bi-list-check fs-4 d-block mb-2"></i>
                        Mes Collectes
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="{{ route('collector.historique') }}" class="btn btn-outline-info w-100 py-3">
                        <i class="bi bi-clock-history fs-4 d-block mb-2"></i>
                        Historique
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Today's Data -->
    <div class="row g-4">
        <!-- Tournées du jour -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-calendar-event me-2 text-warning"></i>Tournées du Jour</h6>
                    <span class="badge bg-warning text-dark">{{ $tourneesDuJour->count() }}</span>
                </div>
                <div class="card-body p-0">
                    @if($tourneesDuJour->count() > 0)
                    <ul class="list-group list-group-flush">
                        @foreach($tourneesDuJour as $tournee)
                        <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                            <div>
                                <span class="fw-medium">Tournée #{{ $tournee->id }}</span>
                                <small class="text-muted d-block">
                                    <i class="bi bi-geo-alt me-1"></i>{{ $tournee->zone ?? 'Zone non définie' }}
                                </small>
                            </div>
                            <div class="text-end">
                                @php
                                    $statutColors = [
                                        'planifiee' => 'secondary',
                                        'en_cours' => 'warning',
                                        'terminee' => 'success',
                                        'annulee' => 'danger'
                                    ];
                                @endphp
                                <span class="badge badge-soft-{{ $statutColors[$tournee->statut] ?? 'secondary' }}">
                                    {{ ucfirst(str_replace('_', ' ', $tournee->statut ?? 'N/A')) }}
                                </span>
                                <small class="text-muted d-block mt-1">
                                    {{ $tournee->collectes->count() ?? 0 }} collecte(s)
                                </small>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                    @else
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-calendar-x fs-1 d-block mb-3"></i>
                        <p class="mb-2">Aucune tournée programmée aujourd'hui</p>
                        <small>Contactez l'administrateur si nécessaire</small>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Collectes du jour -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-shop me-2 text-success"></i>Collectes du Jour</h6>
                    <span class="badge bg-success">{{ $collectesDuJour->count() }}</span>
                </div>
                <div class="card-body p-0">
                    @if($collectesDuJour->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Point de Collecte</th>
                                    <th>Montant</th>
                                    <th class="pe-4">Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($collectesDuJour->take(5) as $collecte)
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="avatar avatar-sm bg-info bg-opacity-10 text-info rounded-circle">
                                                <i class="bi bi-shop"></i>
                                            </div>
                                            <div>
                                                <span class="fw-medium d-block">{{ $collecte->caissier->nom_point_collecte ?? 'N/A' }}</span>
                                                <small class="text-muted">{{ $collecte->caissier->zone ?? '' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="fw-semibold text-success">{{ number_format($collecte->montant_collecte ?? 0) }} FC</td>
                                    <td class="pe-4">
                                        @php
                                            $collecteStatuts = [
                                                'reussie' => 'success',
                                                'partielle' => 'warning',
                                                'non_realisee' => 'secondary',
                                                'en_litige' => 'danger'
                                            ];
                                        @endphp
                                        <span class="badge badge-soft-{{ $collecteStatuts[$collecte->statut] ?? 'secondary' }}">
                                            {{ ucfirst(str_replace('_', ' ', $collecte->statut ?? 'N/A')) }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($collectesDuJour->count() > 5)
                    <div class="card-footer text-center py-2">
                        <a href="{{ route('collector.collectes.index') }}" class="text-primary small">
                            Voir toutes les collectes <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                    @endif
                    @else
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                        <p class="mb-0">Aucune collecte effectuée aujourd'hui</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @else
    <!-- No Collector Profile -->
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bi bi-exclamation-triangle text-warning" style="font-size: 4rem;"></i>
            <h4 class="mt-4 mb-2">Profil Collecteur Non Configuré</h4>
            <p class="text-muted mb-4">
                Votre compte n'est pas encore associé à un profil collecteur.<br>
                Veuillez contacter l'administrateur pour configurer votre accès.
            </p>
            <a href="{{ route('dashboard') }}" class="btn btn-primary">
                <i class="bi bi-house me-2"></i>Retour à l'accueil
            </a>
        </div>
    </div>
    @endif
</div>
