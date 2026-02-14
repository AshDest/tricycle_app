<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-wallet2 me-2 text-warning"></i>Mon Solde en Caisse
            </h4>
            <p class="text-muted mb-0">{{ $caissier->nom_point_collecte ?? 'Point de collecte' }}</p>
        </div>
        <button class="btn btn-sm btn-outline-primary" wire:click="$refresh">
            <i class="bi bi-arrow-clockwise me-1"></i>Actualiser
        </button>
    </div>

    <!-- Solde Principal -->
    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="card bg-gradient text-white h-100" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                <div class="card-body text-center py-5">
                    <i class="bi bi-wallet2 mb-3" style="font-size: 3rem;"></i>
                    <h2 class="fw-bold display-5 mb-2">{{ number_format($solde_actuel ?? 0) }} FC</h2>
                    <p class="mb-0 opacity-75">Solde actuel en caisse (non collecté)</p>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="row g-4 h-100">
                <div class="col-6">
                    <div class="card h-100 border-start border-success border-4">
                        <div class="card-body text-center py-4">
                            <i class="bi bi-arrow-down-circle text-success fs-1 mb-2"></i>
                            <h4 class="fw-bold text-success mb-1">{{ number_format($total_entrants ?? 0) }} FC</h4>
                            <small class="text-muted">Versements reçus (non collectés)</small>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card h-100 border-start border-info border-4">
                        <div class="card-body text-center py-4">
                            <i class="bi bi-arrow-up-circle text-info fs-1 mb-2"></i>
                            <h4 class="fw-bold text-info mb-1">{{ number_format($total_collecte ?? 0) }} FC</h4>
                            <small class="text-muted">Remis aux collecteurs</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Informations du Point de Collecte -->
    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-shop me-2 text-primary"></i>Informations du Point</h6>
                </div>
                <div class="card-body">
                    @if($caissier)
                    <ul class="list-unstyled mb-0">
                        <li class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted">Nom du point</span>
                            <span class="fw-semibold">{{ $caissier->nom_point_collecte ?? 'N/A' }}</span>
                        </li>
                        <li class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted">Identifiant</span>
                            <span class="fw-semibold">{{ $caissier->numero_identifiant ?? 'N/A' }}</span>
                        </li>
                        <li class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted">Zone</span>
                            <span class="badge bg-light text-dark">{{ $caissier->zone ?? 'N/A' }}</span>
                        </li>
                        <li class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted">Téléphone</span>
                            <span class="fw-semibold">{{ $caissier->telephone ?? 'N/A' }}</span>
                        </li>
                        <li class="d-flex justify-content-between py-2">
                            <span class="text-muted">Statut</span>
                            <span class="badge badge-soft-{{ $caissier->is_active ? 'success' : 'danger' }}">
                                {{ $caissier->is_active ? 'Actif' : 'Inactif' }}
                            </span>
                        </li>
                    </ul>
                    @else
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-exclamation-circle fs-1 d-block mb-2"></i>
                        <p class="mb-0">Aucun point de collecte assigné</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-info-circle me-2 text-info"></i>Instructions</h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-info border-0 mb-3">
                        <i class="bi bi-lightbulb me-2"></i>
                        <strong>Rappel :</strong> Le solde affiché représente l'argent en votre possession qui n'a pas encore été collecté par un collecteur.
                    </div>
                    <ul class="list-unstyled mb-0">
                        <li class="d-flex align-items-start gap-2 mb-3">
                            <i class="bi bi-check-circle text-success mt-1"></i>
                            <span>Recevez les versements des motards</span>
                        </li>
                        <li class="d-flex align-items-start gap-2 mb-3">
                            <i class="bi bi-check-circle text-success mt-1"></i>
                            <span>Vérifiez les montants et validez dans le système</span>
                        </li>
                        <li class="d-flex align-items-start gap-2 mb-3">
                            <i class="bi bi-check-circle text-success mt-1"></i>
                            <span>Conservez l'argent jusqu'au passage du collecteur</span>
                        </li>
                        <li class="d-flex align-items-start gap-2">
                            <i class="bi bi-check-circle text-success mt-1"></i>
                            <span>Signalez tout écart constaté</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions rapides -->
    <div class="card">
        <div class="card-header py-3">
            <h6 class="mb-0 fw-bold"><i class="bi bi-lightning me-2 text-warning"></i>Actions Rapides</h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <a href="{{ route('cashier.versements.create') }}" class="btn btn-success w-100 py-3">
                        <i class="bi bi-plus-circle fs-4 d-block mb-2"></i>
                        Enregistrer un versement
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="{{ route('cashier.versements.index') }}" class="btn btn-outline-primary w-100 py-3">
                        <i class="bi bi-list-check fs-4 d-block mb-2"></i>
                        Voir tous les versements
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary w-100 py-3">
                        <i class="bi bi-speedometer2 fs-4 d-block mb-2"></i>
                        Retour au tableau de bord
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
