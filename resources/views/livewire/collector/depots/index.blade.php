<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-box-arrow-in-down me-2 text-success"></i>Dépôts des Caissiers
            </h4>
            <p class="text-muted mb-0">Réception et validation des sommes déposées par les caissiers</p>
        </div>
        <div class="d-flex gap-2">
            <button wire:click="exporterPdf" class="btn btn-outline-danger" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="exporterPdf">
                    <i class="bi bi-file-pdf me-1"></i>Export PDF
                </span>
                <span wire:loading wire:target="exporterPdf">
                    <span class="spinner-border spinner-border-sm me-1"></span>...
                </span>
            </button>
            <button wire:click="$refresh" class="btn btn-outline-primary">
                <i class="bi bi-arrow-clockwise me-1"></i>Actualiser
            </button>
        </div>
    </div>

    <!-- Solde Caisse -->
    <div class="card border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, #212529 0%, #343a40 100%);">
        <div class="card-body py-4">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-white bg-opacity-10 rounded-circle p-3">
                    <i class="bi bi-safe fs-2" style="color: #fff;"></i>
                </div>
                <div>
                    <small class="d-block" style="color: rgba(255,255,255,0.7);">Solde Total en Caisse</small>
                    <h2 class="mb-0 fw-bold" style="color: #fff;">{{ number_format($soldeCaisse) }} FC</h2>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('warning'))
    <div class="alert alert-warning alert-dismissible fade show mb-4">
        <i class="bi bi-exclamation-triangle me-2"></i>{{ session('warning') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-4">
        <i class="bi bi-x-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-sm-4">
            <div class="card bg-warning bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="fw-bold text-warning mb-1">{{ $totalAValider }}</h4>
                    <small class="text-muted">À valider</small>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="card bg-success bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="fw-bold text-success mb-1">{{ $totalValide }}</h4>
                    <small class="text-muted">Validés</small>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="card bg-primary bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="fw-bold text-primary mb-1">{{ number_format($montantTotal) }} FC</h4>
                    <small class="text-muted">Montant total</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Date</label>
                    <input type="date" wire:model.live="filterDate" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Statut</label>
                    <select wire:model.live="filterStatut" class="form-select">
                        <option value="">Tous</option>
                        <option value="a_valider">À valider</option>
                        <option value="valide">Validés</option>
                        <option value="litige">En litige</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Rechercher</label>
                    <input type="text" wire:model.live="search" class="form-control" placeholder="Nom du caissier...">
                </div>
                <div class="col-md-2">
                    <button wire:click="$set('filterDate', '{{ now()->format('Y-m-d') }}')" class="btn btn-outline-secondary w-100">
                        Aujourd'hui
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des dépôts -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Date/Heure</th>
                            <th>Caissier</th>
                            <th>Montant reçu</th>
                            <th class="text-center">Part OKAMI</th>
                            <th class="text-center">Part Propriétaire</th>
                            <th>Statut</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($collectes as $collecte)
                        @php
                            // Calcul prévisualisation pour les non validés
                            $montant = $collecte->montant_collecte ?? 0;
                            $partOkami = $collecte->valide_par_collecteur ? $collecte->part_okami : round($montant / 6, 2);
                            $partProprietaire = $collecte->valide_par_collecteur ? $collecte->part_proprietaire : ($montant - $partOkami);
                        @endphp
                        <tr class="{{ !$collecte->valide_par_collecteur ? 'table-warning' : '' }}">
                            <td class="ps-4">
                                <span class="fw-medium">{{ $collecte->created_at?->format('d/m/Y') }}</span>
                                <small class="text-muted d-block">{{ $collecte->heure_arrivee?->format('H:i') ?? $collecte->created_at?->format('H:i') }}</small>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar avatar-sm bg-info bg-opacity-10 text-info rounded-circle">
                                        {{ strtoupper(substr($collecte->caissier->user->name ?? 'C', 0, 1)) }}
                                    </div>
                                    <div>
                                        <span class="fw-medium d-block">{{ $collecte->caissier->user->name ?? 'N/A' }}</span>
                                        <small class="text-muted">{{ $collecte->caissier->zone ?? '' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td class="fw-bold text-success">{{ number_format($montant) }} FC</td>
                            <td class="text-center">
                                <span class="badge bg-warning bg-opacity-25 text-warning">
                                    {{ number_format($partOkami) }} FC
                                </span>
                                @if(!$collecte->valide_par_collecteur)
                                <small class="d-block text-muted">(estimé)</small>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge bg-success bg-opacity-25 text-success">
                                    {{ number_format($partProprietaire) }} FC
                                </span>
                                @if(!$collecte->valide_par_collecteur)
                                <small class="d-block text-muted">(estimé)</small>
                                @endif
                            </td>
                            <td>
                                @if($collecte->statut === 'en_litige')
                                <span class="badge badge-soft-danger">En litige</span>
                                @elseif($collecte->valide_par_collecteur)
                                <span class="badge badge-soft-success">
                                    <i class="bi bi-check-circle me-1"></i>Validé
                                </span>
                                @else
                                <span class="badge badge-soft-warning">À valider</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                @if(!$collecte->valide_par_collecteur && $collecte->statut !== 'en_litige')
                                <div class="btn-group">
                                    <button wire:click="validerReception({{ $collecte->id }})"
                                            class="btn btn-sm btn-success"
                                            wire:confirm="Confirmer la réception de {{ number_format($collecte->montant_collecte) }} FC ?">
                                        <i class="bi bi-check-lg"></i> Valider
                                    </button>
                                    <button wire:click="signalerProbleme({{ $collecte->id }})"
                                            class="btn btn-sm btn-outline-danger"
                                            wire:confirm="Signaler un problème avec ce dépôt ?">
                                        <i class="bi bi-exclamation-triangle"></i>
                                    </button>
                                </div>
                                @else
                                <span class="text-muted small">
                                    @if($collecte->valide_collecteur_at)
                                    Validé le {{ $collecte->valide_collecteur_at->format('d/m H:i') }}
                                    @endif
                                </span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                <p class="mb-0">Aucun dépôt pour cette période</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($collectes->hasPages())
        <div class="card-footer bg-light">
            {{ $collectes->links() }}
        </div>
        @endif
    </div>

    <!-- Légende -->
    <div class="card mt-4 bg-light border-0">
        <div class="card-body py-3">
            <div class="row align-items-center">
                <div class="col-auto">
                    <i class="bi bi-info-circle text-info me-2"></i>
                    <strong>Tous les montants validés sont ajoutés à la caisse unique du collecteur.</strong>
                </div>
            </div>
        </div>
    </div>
</div>
