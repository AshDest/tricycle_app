<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-gear me-2 text-secondary"></i>Paramètres du Système
            </h4>
            <p class="text-muted mb-0">Configuration générale et tarifs des motos</p>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-4">
        <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Tabs Navigation -->
    <ul class="nav nav-tabs mb-4" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" data-bs-toggle="tab" href="#general" role="tab">
                <i class="bi bi-building me-1"></i>Général
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#versements" role="tab">
                <i class="bi bi-cash-stack me-1"></i>Versements
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#tarifs" role="tab">
                <i class="bi bi-bicycle me-1"></i>Tarifs Motos
            </a>
        </li>
    </ul>

    <!-- Tabs Content -->
    <div class="tab-content">
        <!-- Tab Général -->
        <div class="tab-pane fade show active" id="general" role="tabpanel">
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header py-3">
                            <h6 class="mb-0 fw-bold"><i class="bi bi-building me-2 text-primary"></i>Informations Générales</h6>
                        </div>
                        <div class="card-body">
                            <form wire:submit="saveGeneralSettings">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Nom de la société</label>
                                    <input type="text" wire:model="nom_societe" class="form-control @error('nom_societe') is-invalid @enderror">
                                    @error('nom_societe') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Devise</label>
                                    <input type="text" wire:model="devise" class="form-control @error('devise') is-invalid @enderror" placeholder="FC">
                                    @error('devise') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    <small class="text-muted">Symbole de la devise (ex: FC, USD, EUR)</small>
                                </div>
                                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                                    <span wire:loading.remove wire:target="saveGeneralSettings">
                                        <i class="bi bi-check-lg me-1"></i>Enregistrer
                                    </span>
                                    <span wire:loading wire:target="saveGeneralSettings">
                                        <span class="spinner-border spinner-border-sm me-1"></span>Enregistrement...
                                    </span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab Versements -->
        <div class="tab-pane fade" id="versements" role="tabpanel">
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header py-3">
                            <h6 class="mb-0 fw-bold"><i class="bi bi-cash-stack me-2 text-success"></i>Paramètres des Versements</h6>
                        </div>
                        <div class="card-body">
                            <form wire:submit="saveVersementSettings">
                                <div class="mb-4">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-currency-exchange me-1 text-primary"></i>
                                        Montant journalier par défaut
                                    </label>
                                    <div class="input-group">
                                        <input type="number" wire:model="montant_journalier_defaut" class="form-control form-control-lg @error('montant_journalier_defaut') is-invalid @enderror" min="0" step="100">
                                        <span class="input-group-text">FC</span>
                                    </div>
                                    @error('montant_journalier_defaut') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    <small class="text-muted">Tarif appliqué aux motos sans tarif personnalisé</small>
                                </div>

                                <hr class="my-4">
                                <h6 class="fw-bold mb-3"><i class="bi bi-exclamation-triangle me-1 text-warning"></i>Seuils des Arriérés</h6>

                                <div class="mb-3">
                                    <label class="form-label">Seuil arriéré faible</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-info text-white">Faible</span>
                                        <input type="number" wire:model="seuil_arriere_faible" class="form-control" min="0" step="1000">
                                        <span class="input-group-text">FC</span>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Seuil arriéré moyen</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-warning text-dark">Moyen</span>
                                        <input type="number" wire:model="seuil_arriere_moyen" class="form-control" min="0" step="1000">
                                        <span class="input-group-text">FC</span>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label">Seuil arriéré critique</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-danger text-white">Critique</span>
                                        <input type="number" wire:model="seuil_arriere_critique" class="form-control" min="0" step="1000">
                                        <span class="input-group-text">FC</span>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-success" wire:loading.attr="disabled">
                                    <span wire:loading.remove wire:target="saveVersementSettings">
                                        <i class="bi bi-check-lg me-1"></i>Enregistrer
                                    </span>
                                    <span wire:loading wire:target="saveVersementSettings">
                                        <span class="spinner-border spinner-border-sm me-1"></span>Enregistrement...
                                    </span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card bg-light border-0">
                        <div class="card-body">
                            <h6 class="fw-bold mb-3"><i class="bi bi-info-circle me-2 text-info"></i>Guide des Seuils</h6>
                            <p class="mb-3">Les seuils définissent comment les arriérés sont classifiés :</p>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2 d-flex align-items-center">
                                    <span class="badge bg-success me-2">OK</span>
                                    <span>0 FC - Aucun arriéré</span>
                                </li>
                                <li class="mb-2 d-flex align-items-center">
                                    <span class="badge bg-info me-2">Faible</span>
                                    <span>1 - {{ number_format($seuil_arriere_faible) }} FC</span>
                                </li>
                                <li class="mb-2 d-flex align-items-center">
                                    <span class="badge bg-warning me-2">Moyen</span>
                                    <span>{{ number_format($seuil_arriere_faible) }} - {{ number_format($seuil_arriere_moyen) }} FC</span>
                                </li>
                                <li class="mb-2 d-flex align-items-center">
                                    <span class="badge bg-danger me-2">Élevé</span>
                                    <span>{{ number_format($seuil_arriere_moyen) }} - {{ number_format($seuil_arriere_critique) }} FC</span>
                                </li>
                                <li class="d-flex align-items-center">
                                    <span class="badge bg-dark me-2">Critique</span>
                                    <span>> {{ number_format($seuil_arriere_critique) }} FC</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab Tarifs Motos -->
        <div class="tab-pane fade" id="tarifs" role="tabpanel">
            <!-- Stats des tarifs -->
            <div class="row g-3 mb-4">
                <div class="col-sm-6 col-lg-3">
                    <div class="card bg-primary bg-opacity-10 border-0">
                        <div class="card-body py-3 text-center">
                            <h4 class="fw-bold text-primary mb-1">{{ $statsMotos['total'] ?? 0 }}</h4>
                            <small class="text-muted">Total motos</small>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card bg-success bg-opacity-10 border-0">
                        <div class="card-body py-3 text-center">
                            <h4 class="fw-bold text-success mb-1">{{ $statsMotos['avecTarif'] ?? 0 }}</h4>
                            <small class="text-muted">Avec tarif personnalisé</small>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card bg-warning bg-opacity-10 border-0">
                        <div class="card-body py-3 text-center">
                            <h4 class="fw-bold text-warning mb-1">{{ $statsMotos['sansTarif'] ?? 0 }}</h4>
                            <small class="text-muted">Sans tarif (défaut)</small>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card bg-info bg-opacity-10 border-0">
                        <div class="card-body py-3 text-center">
                            <h4 class="fw-bold text-info mb-1">{{ number_format($statsMotos['tarifMoyen'] ?? 0) }} FC</h4>
                            <small class="text-muted">Tarif moyen</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions en masse -->
            <div class="card mb-4">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-gear me-2 text-secondary"></i>Modification en Masse</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Nouveau tarif pour les motos sélectionnées</label>
                            <div class="input-group">
                                <input type="number" wire:model="nouveauTarifMasse" class="form-control" min="0" step="100" placeholder="Ex: 5000">
                                <span class="input-group-text">FC</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <button wire:click="appliquerTarifMasse" class="btn btn-primary me-2" wire:loading.attr="disabled" {{ empty($selectedMotos) ? 'disabled' : '' }}>
                                <i class="bi bi-check-all me-1"></i>Appliquer ce tarif ({{ count($selectedMotos) }})
                            </button>
                        </div>
                        <div class="col-md-4">
                            <button wire:click="appliquerTarifDefautATous" class="btn btn-outline-secondary" wire:loading.attr="disabled" {{ empty($selectedMotos) ? 'disabled' : '' }}>
                                <i class="bi bi-arrow-repeat me-1"></i>Appliquer tarif par défaut ({{ number_format($montant_journalier_defaut) }} FC)
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Liste des motos -->
            <div class="card">
                <div class="card-header py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-bicycle me-2 text-info"></i>Tarifs par Moto</h6>
                        <div class="d-flex gap-2">
                            <input type="text" wire:model.live="searchMoto" class="form-control form-control-sm" placeholder="Rechercher..." style="width: 200px;">
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4" style="width: 40px;">
                                        <input type="checkbox" wire:model.live="selectAll" class="form-check-input">
                                    </th>
                                    <th>Moto</th>
                                    <th>Propriétaire</th>
                                    <th>Motard</th>
                                    <th>Statut</th>
                                    <th class="text-end">Tarif Journalier</th>
                                    <th class="text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($motos ?? [] as $moto)
                                <tr wire:key="moto-{{ $moto->id }}">
                                    <td class="ps-4">
                                        <input type="checkbox" wire:model.live="selectedMotos" value="{{ $moto->id }}" class="form-check-input">
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="avatar avatar-sm bg-info bg-opacity-10 text-info rounded">
                                                <i class="bi bi-bicycle"></i>
                                            </div>
                                            <div>
                                                <span class="fw-medium d-block">{{ $moto->plaque_immatriculation }}</span>
                                                <small class="text-muted">{{ $moto->numero_matricule ?? '' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $moto->proprietaire->user->name ?? 'N/A' }}</td>
                                    <td>{{ $moto->motard->user->name ?? 'Non assigné' }}</td>
                                    <td>
                                        <span class="badge badge-soft-{{ $moto->statut === 'actif' ? 'success' : 'secondary' }}">
                                            {{ ucfirst($moto->statut) }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        @if($editingMotoId === $moto->id)
                                        <div class="input-group input-group-sm" style="width: 150px; margin-left: auto;">
                                            <input type="number" wire:model="editingMotoTarif" class="form-control text-end" min="0" step="100">
                                            <span class="input-group-text">FC</span>
                                        </div>
                                        @else
                                        <span class="fw-bold {{ ($moto->montant_journalier_attendu ?? 0) > 0 ? 'text-success' : 'text-muted' }}">
                                            {{ number_format($moto->montant_journalier_attendu ?? $montant_journalier_defaut) }} FC
                                        </span>
                                        @if(!$moto->montant_journalier_attendu || $moto->montant_journalier_attendu == 0)
                                        <br><small class="text-muted">(défaut)</small>
                                        @endif
                                        @endif
                                    </td>
                                    <td class="text-end pe-4">
                                        @if($editingMotoId === $moto->id)
                                        <button wire:click="saveMotoTarif" class="btn btn-sm btn-success" title="Enregistrer">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                        <button wire:click="cancelEditMoto" class="btn btn-sm btn-outline-secondary" title="Annuler">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                        @else
                                        <button wire:click="editMotoTarif({{ $moto->id }})" class="btn btn-sm btn-outline-primary" title="Modifier le tarif">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        @if($moto->montant_journalier_attendu && $moto->montant_journalier_attendu != $montant_journalier_defaut)
                                        <button wire:click="appliquerTarifDefaut({{ $moto->id }})" class="btn btn-sm btn-outline-secondary" title="Remettre au tarif par défaut">
                                            <i class="bi bi-arrow-counterclockwise"></i>
                                        </button>
                                        @endif
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="bi bi-bicycle fs-1 d-block mb-2"></i>
                                        Aucune moto trouvée
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($motos->hasPages())
                <div class="card-footer bg-light">
                    {{ $motos->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

