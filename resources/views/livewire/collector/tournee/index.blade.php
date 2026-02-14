<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-signpost-2 me-2 text-primary"></i>Ma Tournée du Jour
            </h4>
            <p class="text-muted mb-0">{{ now()->translatedFormat('l d F Y') }}</p>
        </div>
        <div class="d-flex gap-2">
            @if($tourneeEnCours)
            <span class="badge bg-success px-3 py-2">
                <i class="bi bi-play-circle me-1"></i>Tournée en cours
            </span>
            @else
            <span class="badge bg-secondary px-3 py-2">
                <i class="bi bi-pause-circle me-1"></i>Aucune tournée active
            </span>
            @endif
        </div>
    </div>

    @if($tourneeEnCours)
    <!-- Progression -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0 fw-bold">Progression de la tournée</h6>
                <span class="badge bg-primary">{{ $collectesRealisees ?? 0 }} / {{ $totalCaissiers ?? 0 }}</span>
            </div>
            @php $progress = $totalCaissiers > 0 ? (($collectesRealisees ?? 0) / $totalCaissiers) * 100 : 0; @endphp
            <div class="progress mb-3" style="height: 12px;">
                <div class="progress-bar bg-success" style="width: {{ $progress }}%"></div>
            </div>
            <div class="row g-3 text-center">
                <div class="col-4">
                    <h5 class="fw-bold text-success mb-1">{{ number_format($totalEncaisse ?? 0) }} FC</h5>
                    <small class="text-muted">Encaissé</small>
                </div>
                <div class="col-4">
                    <h5 class="fw-bold text-primary mb-1">{{ number_format($totalAttendu ?? 0) }} FC</h5>
                    <small class="text-muted">Attendu</small>
                </div>
                <div class="col-4">
                    <h5 class="fw-bold text-warning mb-1">{{ $caissierRestants ?? 0 }}</h5>
                    <small class="text-muted">Restants</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des caissiers à visiter -->
    <div class="card">
        <div class="card-header py-3">
            <h6 class="mb-0 fw-bold"><i class="bi bi-list-check me-2 text-info"></i>Caissiers à Visiter</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Point de Collecte</th>
                            <th>Zone</th>
                            <th>Montant attendu</th>
                            <th>Statut</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($caissiers ?? [] as $caissier)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar avatar-sm bg-{{ $caissier->collecte_faite ? 'success' : 'warning' }} bg-opacity-10 text-{{ $caissier->collecte_faite ? 'success' : 'warning' }} rounded-circle">
                                        <i class="bi bi-{{ $caissier->collecte_faite ? 'check-lg' : 'shop' }}"></i>
                                    </div>
                                    <div>
                                        <span class="fw-medium d-block">{{ $caissier->nom_point_collecte ?? 'N/A' }}</span>
                                        <small class="text-muted">{{ $caissier->user->name ?? '' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge bg-light text-dark">{{ $caissier->zone ?? 'N/A' }}</span></td>
                            <td class="fw-semibold">{{ number_format($caissier->solde_actuel ?? 0) }} FC</td>
                            <td>
                                @if($caissier->collecte_faite)
                                <span class="badge badge-soft-success">
                                    <i class="bi bi-check-circle me-1"></i>Collecté
                                </span>
                                @else
                                <span class="badge badge-soft-warning">
                                    <i class="bi bi-clock me-1"></i>En attente
                                </span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                @if(!$caissier->collecte_faite)
                                <button wire:click="effectuerCollecte({{ $caissier->id }})" class="btn btn-sm btn-success">
                                    <i class="bi bi-cash-coin me-1"></i>Collecter
                                </button>
                                @else
                                <button class="btn btn-sm btn-outline-secondary" disabled>
                                    <i class="bi bi-check-lg me-1"></i>Fait
                                </button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="bi bi-check-circle fs-1 text-success d-block mb-3"></i>
                                <p class="mb-0">Tous les caissiers ont été visités</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Actions de fin de tournée -->
    <div class="card mt-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <button wire:click="terminerTournee"
                            wire:confirm="Êtes-vous sûr de vouloir terminer cette tournée ?"
                            class="btn btn-success w-100 py-3"
                            @if($caissierRestants > 0) disabled @endif>
                        <i class="bi bi-check-circle fs-4 d-block mb-2"></i>
                        Terminer la Tournée
                    </button>
                </div>
                <div class="col-md-6">
                    <button wire:click="signalerProbleme" class="btn btn-outline-danger w-100 py-3">
                        <i class="bi bi-exclamation-triangle fs-4 d-block mb-2"></i>
                        Signaler un Problème
                    </button>
                </div>
            </div>
        </div>
    </div>

    @else
    <!-- Aucune tournée -->
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bi bi-calendar-x text-muted" style="font-size: 4rem;"></i>
            <h4 class="mt-4 mb-2">Aucune tournée prévue aujourd'hui</h4>
            <p class="text-muted mb-4">Contactez l'administrateur si vous pensez qu'il y a une erreur.</p>
            <a href="{{ route('collector.historique') }}" class="btn btn-outline-primary">
                <i class="bi bi-clock-history me-2"></i>Voir l'historique
            </a>
        </div>
    </div>
    @endif
</div>
