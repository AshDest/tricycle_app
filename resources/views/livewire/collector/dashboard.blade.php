<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-geo-alt me-2 text-info"></i>Tableau de Bord Collecteur
            </h4>
            <p class="text-muted mb-0">Gestion des tournées et collectes</p>
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

    <!-- Stats Row -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card h-100 border-start border-success border-4">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small text-uppercase fw-semibold mb-2">Encaissé Aujourd'hui</p>
                        <h3 class="fw-bold text-success mb-1">{{ number_format($totalEncaisse ?? 0) }}</h3>
                        <small class="text-muted">FC</small>
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
                        <p class="text-muted small text-uppercase fw-semibold mb-2">Collectes</p>
                        <h3 class="fw-bold mb-1">{{ $collectesDuJour->count() ?? 0 }}</h3>
                        <small class="text-muted">aujourd'hui</small>
                    </div>
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-shop"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card h-100 border-start border-warning border-4">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small text-uppercase fw-semibold mb-2">Tournées du Jour</p>
                        <h3 class="fw-bold mb-1">{{ $tourneesDuJour->count() ?? 0 }}</h3>
                        <small class="text-muted">{{ $tourneesDuJour->where('statut', 'terminee')->count() }} terminée(s)</small>
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
                        <p class="text-muted small text-uppercase fw-semibold mb-2">Historique</p>
                        <h3 class="fw-bold mb-1">{{ $historiqueRecent->count() ?? 0 }}</h3>
                        <small class="text-muted">dernières tournées</small>
                    </div>
                    <div class="stat-icon bg-info bg-opacity-10 text-info">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-lightning me-2 text-warning"></i>Actions Rapides</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <a href="{{ route('collector.tournee.index') }}" class="btn btn-primary w-100 py-3">
                                <i class="bi bi-play-circle fs-4 d-block mb-2"></i>
                                Mes Tournées
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
        </div>
    </div>

    <!-- Today's Collections -->
    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-calendar-event me-2 text-warning"></i>Tournées du Jour</h6>
                </div>
                <div class="card-body p-0">
                    @if($tourneesDuJour->count() > 0)
                    <ul class="list-group list-group-flush">
                        @foreach($tourneesDuJour as $tournee)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <span class="fw-medium">Tournée #{{ $tournee->id }}</span>
                                <small class="text-muted d-block">{{ $tournee->zone ?? 'Zone non définie' }}</small>
                            </div>
                            <span class="badge badge-soft-{{ $tournee->statut === 'terminee' ? 'success' : ($tournee->statut === 'en_cours' ? 'warning' : 'secondary') }}">
                                {{ ucfirst(str_replace('_', ' ', $tournee->statut ?? 'N/A')) }}
                            </span>
                        </li>
                        @endforeach
                    </ul>
                    @else
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-calendar-x fs-1 d-block mb-2"></i>
                        <p class="mb-0">Aucune tournée aujourd'hui</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-clock-history me-2 text-info"></i>Collectes du Jour</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">Caissier</th>
                                    <th>Montant</th>
                                    <th class="pe-4">Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($collectesDuJour ?? [] as $collecte)
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="avatar avatar-sm bg-info bg-opacity-10 text-info rounded-circle">
                                                <i class="bi bi-shop"></i>
                                            </div>
                                            <span class="fw-medium">{{ $collecte->caissier->nom_point_collecte ?? 'N/A' }}</span>
                                        </div>
                                    </td>
                                    <td class="fw-semibold text-success">{{ number_format($collecte->montant_collecte ?? 0) }} FC</td>
                                    <td class="pe-4">
                                        <span class="badge badge-soft-{{ $collecte->statut === 'reussie' ? 'success' : 'warning' }}">
                                            {{ ucfirst($collecte->statut ?? 'N/A') }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted">
                                        <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                        <p class="mb-0">Aucune collecte aujourd'hui</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
