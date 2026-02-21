<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-signpost-2 me-2 text-primary"></i>Mes Tournées
            </h4>
            <p class="text-muted mb-0">Gérer et confirmer vos tournées de collecte</p>
        </div>
        <button wire:click="$refresh" class="btn btn-outline-primary">
            <i class="bi bi-arrow-clockwise me-1"></i>Actualiser
        </button>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-sm-4">
            <div class="card bg-warning bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="fw-bold text-warning mb-1">{{ $tourneesAConfirmer }}</h4>
                    <small class="text-muted">À confirmer</small>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="card bg-primary bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="fw-bold text-primary mb-1">{{ $tourneesEnCours }}</h4>
                    <small class="text-muted">En cours</small>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="card bg-success bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="fw-bold text-success mb-1">{{ $tourneesTerminees }}</h4>
                    <small class="text-muted">Terminées ce mois</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card mb-4">
        <div class="card-body py-3">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Statut</label>
                    <select wire:model.live="filterStatut" class="form-select">
                        <option value="">Tous les statuts</option>
                        <option value="planifiee">Planifiées</option>
                        <option value="confirmee">Confirmées</option>
                        <option value="en_cours">En cours</option>
                        <option value="terminee">Terminées</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Date</label>
                    <input type="date" wire:model.live="filterDate" class="form-control">
                </div>
                <div class="col-md-4">
                    <button wire:click="$set('filterStatut', ''); $set('filterDate', '')" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-x-lg me-1"></i>Réinitialiser
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des tournées -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Date</th>
                            <th>Zone</th>
                            <th>Caissiers</th>
                            <th>Montant attendu</th>
                            <th>Montant collecté</th>
                            <th>Statut</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tournees as $tournee)
                        <tr class="{{ $tournee->statut === 'planifiee' ? 'table-warning' : ($tournee->statut === 'en_cours' ? 'table-info' : '') }}">
                            <td class="ps-4">
                                <span class="fw-medium">{{ $tournee->date?->format('d/m/Y') }}</span>
                                <small class="text-muted d-block">{{ $tournee->date?->translatedFormat('l') }}</small>
                            </td>
                            <td><span class="badge bg-light text-dark">{{ $tournee->zone ?? 'N/A' }}</span></td>
                            <td>
                                <span class="fw-semibold">{{ $tournee->collectes_count ?? 0 }}</span>
                                <small class="text-muted">caissier(s)</small>
                            </td>
                            <td>{{ number_format($tournee->collectes_sum_montant_attendu ?? 0) }} FC</td>
                            <td class="fw-semibold text-success">{{ number_format($tournee->collectes_sum_montant_collecte ?? 0) }} FC</td>
                            <td>
                                @php
                                    $statutConfig = [
                                        'planifiee' => ['color' => 'warning', 'icon' => 'calendar', 'label' => 'Planifiée'],
                                        'confirmee' => ['color' => 'info', 'icon' => 'check-circle', 'label' => 'Confirmée'],
                                        'en_cours' => ['color' => 'primary', 'icon' => 'play-circle', 'label' => 'En cours'],
                                        'terminee' => ['color' => 'success', 'icon' => 'check-all', 'label' => 'Terminée'],
                                        'annulee' => ['color' => 'danger', 'icon' => 'x-circle', 'label' => 'Annulée'],
                                    ];
                                    $config = $statutConfig[$tournee->statut] ?? ['color' => 'secondary', 'icon' => 'question-circle', 'label' => $tournee->statut];
                                @endphp
                                <span class="badge badge-soft-{{ $config['color'] }}">
                                    <i class="bi bi-{{ $config['icon'] }} me-1"></i>{{ $config['label'] }}
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group">
                                    @if($tournee->statut === 'planifiee')
                                    <button wire:click="confirmerTournee({{ $tournee->id }})"
                                            class="btn btn-sm btn-success"
                                            title="Confirmer ma présence">
                                        <i class="bi bi-check-lg me-1"></i>Confirmer
                                    </button>
                                    @elseif($tournee->statut === 'confirmee')
                                    <button wire:click="demarrerTournee({{ $tournee->id }})"
                                            class="btn btn-sm btn-primary"
                                            title="Démarrer la tournée">
                                        <i class="bi bi-play me-1"></i>Démarrer
                                    </button>
                                    @elseif($tournee->statut === 'en_cours')
                                    <a href="{{ route('collector.tournee.show', $tournee) }}" class="btn btn-sm btn-info">
                                        <i class="bi bi-eye me-1"></i>Gérer
                                    </a>
                                    <button wire:click="terminerTournee({{ $tournee->id }})"
                                            class="btn btn-sm btn-success"
                                            wire:confirm="Êtes-vous sûr de vouloir terminer cette tournée ?"
                                            title="Terminer la tournée">
                                        <i class="bi bi-check-all me-1"></i>Terminer
                                    </button>
                                    @elseif($tournee->statut === 'terminee')
                                    <a href="{{ route('collector.tournee.show', $tournee) }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-eye me-1"></i>Détails
                                    </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-calendar-x fs-1 d-block mb-3"></i>
                                <p class="mb-0">Aucune tournée trouvée</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($tournees->hasPages())
        <div class="card-footer bg-light">
            {{ $tournees->links() }}
        </div>
        @endif
    </div>
</div>
