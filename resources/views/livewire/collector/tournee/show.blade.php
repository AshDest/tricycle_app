<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-signpost-2 me-2 text-primary"></i>Tournée du {{ $tournee->date?->format('d/m/Y') }}
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('collector.tournee.index') }}">Mes Tournées</a></li>
                    <li class="breadcrumb-item active">Détails</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            @php
                $statutConfig = [
                    'planifiee' => ['color' => 'warning', 'label' => 'Planifiée'],
                    'confirmee' => ['color' => 'info', 'label' => 'Confirmée'],
                    'en_cours' => ['color' => 'primary', 'label' => 'En cours'],
                    'terminee' => ['color' => 'success', 'label' => 'Terminée'],
                    'annulee' => ['color' => 'danger', 'label' => 'Annulée'],
                ];
                $config = $statutConfig[$tournee->statut] ?? ['color' => 'secondary', 'label' => $tournee->statut];
            @endphp
            <span class="badge bg-{{ $config['color'] }} px-3 py-2">{{ $config['label'] }}</span>
            <a href="{{ route('collector.tournee.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Retour
            </a>
        </div>
    </div>
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif
    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card bg-primary bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="fw-bold text-primary mb-1">{{ $totalCollectes }}</h4>
                    <small class="text-muted">Caissiers à visiter</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card bg-info bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="fw-bold text-info mb-1">{{ $collectesEffectuees }}</h4>
                    <small class="text-muted">Dépôts reçus</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card bg-success bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="fw-bold text-success mb-1">{{ $collectesValidees }}</h4>
                    <small class="text-muted">Validés</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card bg-warning bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="fw-bold text-warning mb-1">{{ number_format($totalEncaisse) }} FC</h4>
                    <small class="text-muted">Total encaissé</small>
                </div>
            </div>
        </div>
    </div>
    <!-- Liste des caissiers -->
    <div class="card">
        <div class="card-header py-3">
            <h6 class="mb-0 fw-bold"><i class="bi bi-people me-2"></i>Liste des caissiers</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Caissier</th>
                            <th>Point de collecte</th>
                            <th>Montant attendu</th>
                            <th>Montant déposé</th>
                            <th>Statut</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tournee->collectes as $collecte)
                        <tr class="{{ $collecte->valide_par_collecteur ? 'table-success' : ($collecte->statut === 'reussie' ? 'table-info' : '') }}">
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar avatar-sm bg-primary bg-opacity-10 text-primary rounded-circle">
                                        {{ strtoupper(substr($collecte->caissier->user->name ?? 'C', 0, 1)) }}
                                    </div>
                                    <div>
                                        <span class="fw-medium">{{ $collecte->caissier->user->name ?? 'N/A' }}</span>
                                        <small class="text-muted d-block">{{ $collecte->caissier->numero_identifiant ?? '' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $collecte->caissier->nom_point_collecte ?? 'N/A' }}</td>
                            <td>{{ number_format($collecte->montant_attendu ?? 0) }} FC</td>
                            <td class="fw-semibold {{ $collecte->montant_collecte > 0 ? 'text-success' : 'text-muted' }}">
                                {{ number_format($collecte->montant_collecte ?? 0) }} FC
                            </td>
                            <td>
                                @if($collecte->valide_par_collecteur)
                                <span class="badge badge-soft-success"><i class="bi bi-check-circle me-1"></i>Validé</span>
                                @elseif($collecte->statut === 'reussie')
                                <span class="badge badge-soft-info"><i class="bi bi-hourglass me-1"></i>Déposé</span>
                                @else
                                <span class="badge badge-soft-secondary"><i class="bi bi-clock me-1"></i>En attente</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                @if($collecte->statut === 'reussie' && !$collecte->valide_par_collecteur)
                                <button wire:click="validerCollecte({{ $collecte->id }})" class="btn btn-sm btn-success">
                                    <i class="bi bi-check-lg me-1"></i>Valider
                                </button>
                                @elseif($collecte->valide_par_collecteur)
                                <span class="text-success small"><i class="bi bi-check-circle me-1"></i>{{ $collecte->valide_collecteur_at?->format('H:i') }}</span>
                                @else
                                <span class="text-muted small">En attente du dépôt</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">Aucun caissier dans cette tournée</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
