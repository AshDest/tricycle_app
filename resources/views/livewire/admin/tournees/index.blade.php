<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-calendar-event me-2 text-primary"></i>Calendrier des Tournées
            </h4>
            <p class="text-muted mb-0">Planification du ramassage quotidien</p>
        </div>
        <div class="d-flex gap-2">
            <button wire:click="exportPdf" class="btn btn-danger">
                <i class="bi bi-file-pdf me-1"></i> PDF
            </button>
            <a href="{{ route('admin.tournees.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>Nouvelle Tournée
            </a>
        </div>
    </div>

    <!-- Stats du jour -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card bg-primary bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="fw-bold text-primary mb-1">{{ $tourneesAujourdhui ?? 0 }}</h4>
                    <small class="text-muted">Tournées aujourd'hui</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card bg-warning bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="fw-bold text-warning mb-1">{{ $tourneesEnCours ?? 0 }}</h4>
                    <small class="text-muted">En cours</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card bg-success bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="fw-bold text-success mb-1">{{ $tourneesTerminees ?? 0 }}</h4>
                    <small class="text-muted">Terminées</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card bg-info bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="fw-bold text-info mb-1">{{ number_format($totalCollecteJour ?? 0) }} FC</h4>
                    <small class="text-muted">Collecté aujourd'hui</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Collecteur</label>
                    <select wire:model.live="filterCollecteur" class="form-select">
                        <option value="">Tous</option>
                        @foreach($collecteurs ?? [] as $collecteur)
                        <option value="{{ $collecteur->id }}">{{ $collecteur->user->name ?? 'N/A' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Statut</label>
                    <select wire:model.live="filterStatut" class="form-select">
                        <option value="">Tous</option>
                        <option value="planifiee">Planifiée</option>
                        <option value="en_cours">En cours</option>
                        <option value="terminee">Terminée</option>
                        <option value="annulee">Annulée</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Zone</label>
                    <select wire:model.live="filterZone" class="form-select">
                        <option value="">Toutes</option>
                        @foreach($zones ?? [] as $zone)
                        <option value="{{ $zone }}">{{ $zone }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Date</label>
                    <input type="date" wire:model.live="filterDate" class="form-control">
                </div>
                <div class="col-md-2">
                    <button wire:click="resetFilters" class="btn btn-outline-secondary w-100">
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
                            <th>Collecteur</th>
                            <th>Zone</th>
                            <th>Caissiers</th>
                            <th>Montant collecté</th>
                            <th>Statut</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tournees ?? [] as $tournee)
                        <tr>
                            <td class="ps-4">
                                <span class="fw-medium">{{ $tournee->date?->format('d/m/Y') }}</span>
                                <small class="text-muted d-block">{{ $tournee->date?->translatedFormat('l') }}</small>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar avatar-sm bg-info bg-opacity-10 text-info rounded-circle">
                                        {{ strtoupper(substr($tournee->collecteur->user->name ?? 'C', 0, 1)) }}
                                    </div>
                                    <span>{{ $tournee->collecteur->user->name ?? 'N/A' }}</span>
                                </div>
                            </td>
                            <td><span class="badge bg-light text-dark">{{ $tournee->zone ?? 'N/A' }}</span></td>
                            <td>
                                <span class="fw-semibold">{{ $tournee->collectes_count ?? 0 }}</span>
                                <small class="text-muted">visités</small>
                            </td>
                            <td class="fw-semibold text-success">{{ number_format($tournee->collectes_sum_montant_collecte ?? 0) }} FC</td>
                            <td>
                                @php
                                    $statutConfig = [
                                        'planifiee' => ['color' => 'info', 'icon' => 'calendar'],
                                        'en_cours' => ['color' => 'warning', 'icon' => 'play-circle'],
                                        'terminee' => ['color' => 'success', 'icon' => 'check-circle'],
                                        'annulee' => ['color' => 'danger', 'icon' => 'x-circle'],
                                    ];
                                    $config = $statutConfig[$tournee->statut] ?? ['color' => 'secondary', 'icon' => 'question-circle'];
                                @endphp
                                <span class="badge badge-soft-{{ $config['color'] }}">
                                    <i class="bi bi-{{ $config['icon'] }} me-1"></i>
                                    {{ ucfirst(str_replace('_', ' ', $tournee->statut ?? 'N/A')) }}
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group">
                                    <a href="{{ route('admin.tournees.show', $tournee) }}" class="btn btn-sm btn-outline-primary" title="Voir">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if($tournee->statut === 'planifiee')
                                    <button wire:click="demarrer({{ $tournee->id }})" class="btn btn-sm btn-outline-success" title="Démarrer">
                                        <i class="bi bi-play"></i>
                                    </button>
                                    <button wire:click="annuler({{ $tournee->id }})" class="btn btn-sm btn-outline-danger" title="Annuler" wire:confirm="Annuler cette tournée ?">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                    @elseif($tournee->statut === 'en_cours')
                                    <button wire:click="terminer({{ $tournee->id }})" class="btn btn-sm btn-outline-success" title="Terminer">
                                        <i class="bi bi-check-lg"></i>
                                    </button>
                                    <button wire:click="annuler({{ $tournee->id }})" class="btn btn-sm btn-outline-danger" title="Annuler" wire:confirm="Annuler cette tournée ?">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-calendar-x fs-1 d-block mb-3"></i>
                                <p class="mb-0">Aucune tournée trouvée</p>
                                <a href="{{ route('admin.tournees.create') }}" class="btn btn-sm btn-primary mt-3">
                                    <i class="bi bi-plus-lg me-1"></i>Planifier une tournée
                                </a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if(($tournees ?? collect())->hasPages())
        <div class="card-footer bg-light">
            {{ $tournees->links() }}
        </div>
        @endif
    </div>
</div>
