<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-plus-circle me-2 text-success"></i>Enregistrer un Versement
            </h4>
            <p class="text-muted mb-0">Réception du versement journalier ou remboursement d'arriérés</p>
        </div>
        <a href="{{ route('cashier.versements.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Retour
        </a>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        @if(session('dernierVersementId'))
        <div class="mt-2">
            <button wire:click="telechargerRecu({{ session('dernierVersementId') }})" class="btn btn-sm btn-outline-success">
                <i class="bi bi-printer me-1"></i>Imprimer le reçu
            </button>
        </div>
        @endif
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-4">
        <i class="bi bi-x-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row g-4">
        <!-- Formulaire -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-cash-stack me-2 text-success"></i>Détails du Versement Journalier</h6>
                </div>
                <div class="card-body">
                    <form wire:submit="enregistrer">
                        <!-- Sélection du motard -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-person me-1 text-primary"></i>Motard <span class="text-danger">*</span>
                            </label>
                            <select wire:model.live="motard_id" class="form-select form-select-lg @error('motard_id') is-invalid @enderror" required>
                                <option value="">-- Sélectionner un motard --</option>
                                @foreach($motards ?? [] as $motard)
                                <option value="{{ $motard->id }}">
                                    {{ $motard->user->name ?? 'N/A' }} - {{ $motard->moto->plaque_immatriculation ?? 'Sans moto' }}
                                </option>
                                @endforeach
                            </select>
                            @error('motard_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        @if($motardSelectionne)
                        <!-- Infos du motard sélectionné -->
                        <div class="card mb-4 border-{{ $arrieresCumules > 0 ? 'warning' : 'success' }}">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <h6 class="fw-bold mb-1">{{ $motardSelectionne->user->name ?? 'N/A' }}</h6>
                                        <span class="badge bg-light text-dark">
                                            <i class="bi bi-bicycle me-1"></i>{{ $motardSelectionne->moto->plaque_immatriculation ?? 'N/A' }}
                                        </span>
                                    </div>
                                    <div class="text-end">
                                        <small class="text-muted d-block">Tarif journalier</small>
                                        <strong class="text-primary fs-5">{{ number_format($montantJournalierAttendu) }} FC</strong>
                                    </div>
                                </div>

                                @if($arrieresCumules > 0)
                                <div class="alert alert-warning py-2 mb-0">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="bi bi-exclamation-triangle me-2"></i>
                                            <strong>Arriérés cumulés:</strong>
                                        </div>
                                        <span class="badge bg-danger fs-6">{{ number_format($arrieresCumules) }} FC</span>
                                    </div>
                                </div>
                                @else
                                <div class="alert alert-success py-2 mb-0">
                                    <i class="bi bi-check-circle me-2"></i>Aucun arriéré - Motard à jour
                                </div>
                                @endif
                            </div>
                        </div>

                        {{-- ===== CYCLE DE VERSEMENT (6 jours + 1 repos) ===== --}}
                        @if(!empty($cycleInfo))
                        <div class="card mb-4 border-{{ $estJourRepos ? 'success' : 'primary' }}">
                            <div class="card-header py-2 bg-{{ $estJourRepos ? 'success' : 'primary' }} bg-opacity-10">
                                <h6 class="mb-0 fw-bold text-{{ $estJourRepos ? 'success' : 'primary' }} small">
                                    <i class="bi bi-{{ $estJourRepos ? 'moon-stars' : 'calendar-check' }} me-1"></i>
                                    Cycle de versement
                                </h6>
                            </div>
                            <div class="card-body py-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-muted small">Cycle #{{ $cycleInfo['cycle_numero'] ?? 1 }}</span>
                                    <span class="badge bg-{{ $estJourRepos ? 'success' : 'primary' }}">
                                        {{ $cycleInfo['jours_travailles_cycle'] ?? 0 }}/6 jours
                                    </span>
                                </div>

                                {{-- Barre de progression du cycle --}}
                                <div class="progress mb-2" style="height: 10px;">
                                    @php
                                        $progressPct = (($cycleInfo['jours_travailles_cycle'] ?? 0) / 6) * 100;
                                    @endphp
                                    <div class="progress-bar bg-{{ $estJourRepos ? 'success' : 'primary' }}"
                                         role="progressbar"
                                         style="width: {{ $progressPct }}%"
                                         aria-valuenow="{{ $progressPct }}"
                                         aria-valuemin="0"
                                         aria-valuemax="100">
                                    </div>
                                </div>

                                <p class="mb-0 small">{{ $cycleInfo['message'] ?? '' }}</p>

                                @if($estJourRepos)
                                <div class="alert alert-success py-2 mt-2 mb-0 small">
                                    <i class="bi bi-moon-stars me-1"></i>
                                    <strong>Jour de repos mérité!</strong> Le motard a complété 6 jours de travail.
                                    @if($arrieresCumules > 0)
                                    <br>Seul le <strong>remboursement d'arriérés</strong> est autorisé.
                                    @endif
                                </div>
                                @else
                                <div class="d-flex gap-2 mt-2 flex-wrap">
                                    @if(!empty($cycleInfo['dates_cycle_actuel']))
                                    <small class="text-muted">
                                        <i class="bi bi-clock-history me-1"></i>Jours travaillés ce cycle:
                                        @foreach($cycleInfo['dates_cycle_actuel'] as $dateCycle)
                                            <span class="badge bg-light text-dark">{{ \Carbon\Carbon::parse($dateCycle)->format('d/m') }}</span>
                                        @endforeach
                                    </small>
                                    @endif
                                </div>
                                @endif

                                @if(($cycleInfo['jours_restants_cycle'] ?? 0) > 0 && !$estJourRepos)
                                <small class="text-muted mt-1 d-block">
                                    <i class="bi bi-arrow-right me-1"></i>Encore {{ $cycleInfo['jours_restants_cycle'] }} jour(s) avant le repos.
                                </small>
                                @endif
                            </div>
                        </div>
                        @endif

                        {{-- Cycle du motard secondaire --}}
                        @if(!empty($cycleInfoSecondaire) && $motard_secondaire_id)
                        <div class="card mb-4 border-info">
                            <div class="card-header py-2 bg-info bg-opacity-10">
                                <h6 class="mb-0 fw-bold text-info small">
                                    <i class="bi bi-person-badge me-1"></i>Cycle du remplaçant
                                </h6>
                            </div>
                            <div class="card-body py-2">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="text-muted small">Cycle #{{ $cycleInfoSecondaire['cycle_numero'] ?? 1 }}</span>
                                    <span class="badge bg-info">{{ $cycleInfoSecondaire['jours_travailles_cycle'] ?? 0 }}/6 jours</span>
                                </div>
                                <div class="progress mb-1" style="height: 6px;">
                                    @php $pctSec = (($cycleInfoSecondaire['jours_travailles_cycle'] ?? 0) / 6) * 100; @endphp
                                    <div class="progress-bar bg-info" style="width: {{ $pctSec }}%"></div>
                                </div>
                                <p class="mb-0 small">{{ $cycleInfoSecondaire['message'] ?? '' }}</p>
                            </div>
                        </div>
                        @endif

                        {{-- ===== JOURS MANQUANTS (SANS VERSEMENT) ===== --}}
                        @if($type_versement === 'journalier' && count($joursManquants) > 0)
                        <div class="card mb-4 border-danger">
                            <div class="card-header py-3 bg-danger bg-opacity-10">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0 fw-bold text-danger">
                                        <i class="bi bi-calendar-x me-2"></i>Jours sans versement ({{ count($joursManquants) }})
                                    </h6>
                                    <div class="d-flex gap-2">
                                        @if(count($joursSelectionnes) < count($joursManquants))
                                        <button type="button" wire:click="selectionnerTousLesJours" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-check-all me-1"></i>Tout sélectionner
                                        </button>
                                        @else
                                        <button type="button" wire:click="deselectionnerTousLesJours" class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-x-lg me-1"></i>Tout désélectionner
                                        </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="list-group list-group-flush" style="max-height: 280px; overflow-y: auto;">
                                    @foreach($joursManquants as $jour)
                                    @php
                                        $estSelectionne = in_array($jour['date'], $joursSelectionnes);
                                    @endphp
                                    <label class="list-group-item list-group-item-action d-flex align-items-center gap-3 {{ $estSelectionne ? 'bg-danger bg-opacity-10 border-start border-danger border-3' : '' }}"
                                           style="cursor: pointer;" wire:click.prevent="toggleJour('{{ $jour['date'] }}')">
                                        <input type="checkbox"
                                               class="form-check-input flex-shrink-0"
                                               value="{{ $jour['date'] }}"
                                               {{ $estSelectionne ? 'checked' : '' }}
                                               style="pointer-events: none;">
                                        <div class="flex-grow-1">
                                            <span class="fw-medium {{ $jour['est_aujourdhui'] ? 'text-primary' : '' }}">
                                                {{ $jour['date_formatted'] }}
                                                @if($jour['est_aujourdhui'])
                                                <span class="badge bg-primary ms-1">Aujourd'hui</span>
                                                @endif
                                            </span>
                                        </div>
                                        <span class="badge {{ $estSelectionne ? 'bg-danger' : 'bg-secondary bg-opacity-25 text-dark' }}">
                                            {{ number_format($montantJournalierAttendu) }} FC
                                        </span>
                                    </label>
                                    @endforeach
                                </div>

                                {{-- Résumé de la sélection --}}
                                @if(count($joursSelectionnes) > 0)
                                <div class="p-3 bg-danger bg-opacity-10 border-top">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>{{ count($joursSelectionnes) }} jour(s) sélectionné(s)</strong>
                                        </div>
                                        <div class="text-end">
                                            <strong class="text-danger fs-5">{{ number_format(count($joursSelectionnes) * $montantJournalierAttendu) }} FC</strong>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                        @elseif($type_versement === 'journalier' && count($joursManquants) === 0 && $motardSelectionne)
                        <div class="alert alert-success mb-4">
                            <i class="bi bi-check-circle-fill me-2"></i>
                            <strong>Tous les jours sont couverts!</strong> Aucun jour manquant dans les 30 derniers jours.
                        </div>
                        @endif

                        {{-- Date de versement (cachée si multi-jours, visible en mode arriérés ou date manuelle) --}}
                        <div class="mb-4 {{ ($type_versement === 'journalier' && count($joursManquants) > 0) ? 'd-none' : '' }}">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-calendar-date text-primary me-1"></i>
                                Date du versement <span class="text-danger">*</span>
                            </label>
                            <input type="date" wire:model.live="date_versement" class="form-control @error('date_versement') is-invalid @enderror" max="{{ now()->format('Y-m-d') }}" required>
                            @error('date_versement')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                            <!-- Motard remplaçant (secondaire) -->
                            @if(count($motardsSecondairesList ?? []) > 0)
                            <div class="mt-4 mb-0">
                                <div class="card border-info">
                                    <div class="card-header py-2 bg-info bg-opacity-10">
                                        <h6 class="mb-0 fw-bold text-info small">
                                            <i class="bi bi-person-badge me-1"></i>Motard remplaçant (optionnel)
                                        </h6>
                                    </div>
                                    <div class="card-body py-2">
                                        <small class="text-muted d-block mb-2">
                                            Si un autre motard (sans moto assignée) a travaillé ce jour à la place du titulaire, sélectionnez-le ci-dessous. Sinon, laissez vide.
                                        </small>
                                        <select wire:model="motard_secondaire_id" class="form-select form-select-sm @error('motard_secondaire_id') is-invalid @enderror">
                                            <option value="">-- Motard titulaire ({{ $motardSelectionne?->user?->name ?? 'N/A' }}) --</option>
                                            @foreach($motardsSecondairesList as $ms)
                                            <option value="{{ $ms->id }}">
                                                {{ $ms->user->name ?? 'N/A' }} ({{ $ms->numero_identifiant }})
                                            </option>
                                            @endforeach
                                        </select>
                                        @error('motard_secondaire_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror

                                        @if($motard_secondaire_id)
                                        <div class="alert alert-info py-1 mt-2 mb-0 small">
                                            <i class="bi bi-info-circle me-1"></i>
                                            Le versement sera enregistré pour la moto du titulaire, mais le conducteur effectif sera le remplaçant sélectionné.
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- Alerte Dimanche -->
                            @if($estDimanche)
                            <div class="alert alert-danger mt-3 mb-0 border-danger">
                                <div class="d-flex align-items-center gap-3">
                                    <i class="bi bi-calendar-x fs-3 text-danger"></i>
                                    <div>
                                        <strong class="d-block mb-1">Dimanche - Jour de repos</strong>
                                        <small>Les versements journaliers ne sont pas autorisés le dimanche.</small>
                                        @if($arrieresCumules > 0)
                                        <br><small class="text-muted">Seuls les <strong>remboursements d'arriérés</strong> sont acceptés.</small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- Statut du jour sélectionné -->
                            @if($versementExistantJour)
                            <div class="alert alert-info mt-3 mb-0">
                                <div class="d-flex align-items-start gap-3">
                                    <i class="bi bi-info-circle fs-4 text-info"></i>
                                    <div class="flex-grow-1">
                                        <strong class="d-block mb-1">Versement existant pour ce jour</strong>
                                        <div class="row g-2 small">
                                            <div class="col-md-4">
                                                <span class="text-muted">Déjà versé:</span>
                                                <strong class="text-success">{{ number_format($montantDejaVerseJour) }} FC</strong>
                                            </div>
                                            <div class="col-md-4">
                                                <span class="text-muted">Attendu:</span>
                                                <strong>{{ number_format($montantJournalierAttendu) }} FC</strong>
                                            </div>
                                            <div class="col-md-4">
                                                <span class="text-muted">Restant:</span>
                                                <strong class="text-{{ $montantRestantJour > 0 ? 'warning' : 'success' }}">
                                                    {{ number_format($montantRestantJour) }} FC
                                                </strong>
                                            </div>
                                        </div>
                                        @if($montantRestantJour > 0)
                                        <div class="mt-2">
                                            <small class="text-muted">Le nouveau montant sera ajouté au versement existant.</small>
                                        </div>
                                        @else
                                        <div class="mt-2">
                                            <span class="badge bg-success">Journée complète - Tout versement sera considéré comme arriérés</span>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endif

                        <!-- Type de versement -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Type de versement <span class="text-danger">*</span></label>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-check card p-3 h-100 {{ $type_versement === 'journalier' ? 'border-primary bg-primary bg-opacity-10' : '' }} {{ ($estDimanche || $estJourRepos) ? 'opacity-50' : '' }}">
                                        <input class="form-check-input" type="radio" wire:model.live="type_versement" value="journalier" id="typeJournalier" {{ ($estDimanche || $estJourRepos) ? 'disabled' : '' }}>
                                        <label class="form-check-label d-block" for="typeJournalier">
                                            <div class="d-flex align-items-center gap-2 mb-2">
                                                <i class="bi bi-calendar-day fs-4 text-primary"></i>
                                                <strong>Versement Journalier</strong>
                                            </div>
                                            <small class="text-muted">
                                                @if($estJourRepos)
                                                <span class="text-success"><i class="bi bi-moon-stars me-1"></i>Jour de repos (cycle complété)</span>
                                                @elseif($estDimanche)
                                                <span class="text-danger"><i class="bi bi-x-circle me-1"></i>Non disponible le dimanche</span>
                                                @else
                                                Payer la journée sélectionnée
                                                @endif
                                            </small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check card p-3 h-100 {{ $type_versement === 'arrieres' ? 'border-danger bg-danger bg-opacity-10' : '' }} {{ $arrieresCumules <= 0 ? 'opacity-50' : '' }}">
                                        <input class="form-check-input" type="radio" wire:model.live="type_versement" value="arrieres" id="typeArrieres" {{ $arrieresCumules <= 0 ? 'disabled' : '' }}>
                                        <label class="form-check-label d-block" for="typeArrieres">
                                            <div class="d-flex align-items-center gap-2 mb-2">
                                                <i class="bi bi-arrow-counterclockwise fs-4 text-danger"></i>
                                                <strong>Remboursement Arriérés</strong>
                                            </div>
                                            <small class="text-muted">
                                                @if($arrieresCumules > 0)
                                                Total: <strong class="text-danger">{{ number_format($arrieresCumules) }} FC</strong>
                                                @if($estDimanche)
                                                <span class="badge bg-success ms-1">Disponible</span>
                                                @endif
                                                @else
                                                Aucun arriéré
                                                @endif
                                            </small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Montant -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-cash me-1 text-success"></i>Montant versé <span class="text-danger">*</span>
                            </label>
                            <div class="input-group input-group-lg">
                                <input type="text" wire:model.live="montant" class="form-control text-end @error('montant') is-invalid @enderror"
                                       placeholder="0" inputmode="numeric" pattern="[0-9]*">
                                <span class="input-group-text">FC</span>
                            </div>
                            @error('montant')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror

                            <!-- Boutons de remplissage rapide -->
                            <div class="mt-2 d-flex gap-2 flex-wrap">
                                @if($type_versement === 'journalier')
                                <button type="button" wire:click="remplirMontantJournalier" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-calendar-day me-1"></i>Montant du jour ({{ number_format($montantRestantJour > 0 ? $montantRestantJour : $montantJournalierAttendu) }} FC)
                                </button>
                                @endif

                                @if($arrieresCumules > 0)
                                <button type="button" wire:click="remplirMontantArrieres" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-arrow-counterclockwise me-1"></i>Arriérés ({{ number_format($arrieresCumules) }} FC)
                                </button>
                                <button type="button" wire:click="remplirTotalDu" class="btn btn-sm btn-outline-warning">
                                    <i class="bi bi-calculator me-1"></i>Total dû ({{ number_format($montantRestantJour + $arrieresCumules) }} FC)
                                </button>
                                @endif
                            </div>
                        </div>

                        <!-- Mode de paiement -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Mode de paiement <span class="text-danger">*</span></label>
                            <div class="row g-2">
                                <div class="col-4">
                                    <div class="form-check card p-3 text-center h-100 {{ $mode_paiement === 'cash' ? 'border-success bg-success bg-opacity-10' : '' }}">
                                        <input class="form-check-input d-none" type="radio" wire:model.live="mode_paiement" value="cash" id="modeCash">
                                        <label class="form-check-label d-block" for="modeCash" style="cursor: pointer;">
                                            <i class="bi bi-cash fs-4 text-success d-block mb-1"></i>
                                            <small class="fw-semibold">Cash</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="form-check card p-3 text-center h-100 {{ $mode_paiement === 'mobile_money' ? 'border-primary bg-primary bg-opacity-10' : '' }}">
                                        <input class="form-check-input d-none" type="radio" wire:model.live="mode_paiement" value="mobile_money" id="modeMobile">
                                        <label class="form-check-label d-block" for="modeMobile" style="cursor: pointer;">
                                            <i class="bi bi-phone fs-4 text-primary d-block mb-1"></i>
                                            <small class="fw-semibold">Mobile</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="form-check card p-3 text-center h-100 {{ $mode_paiement === 'depot' ? 'border-info bg-info bg-opacity-10' : '' }}">
                                        <input class="form-check-input d-none" type="radio" wire:model.live="mode_paiement" value="depot" id="modeDepot">
                                        <label class="form-check-label d-block" for="modeDepot" style="cursor: pointer;">
                                            <i class="bi bi-bank fs-4 text-info d-block mb-1"></i>
                                            <small class="fw-semibold">Dépôt</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Notes (optionnel)</label>
                            <textarea wire:model="notes" class="form-control" rows="2" placeholder="Observations..."></textarea>
                        </div>

                        <!-- Bouton de soumission -->
                        <div class="d-grid">
                            @if($estJourRepos && $type_versement === 'journalier')
                            <div class="alert alert-success mb-0">
                                <i class="bi bi-moon-stars me-2"></i>
                                <strong>Jour de repos</strong> - Le motard a complété son cycle de 6 jours de travail.
                                @if($arrieresCumules > 0)
                                <br><small>Vous pouvez uniquement enregistrer un <strong>remboursement d'arriérés</strong>.</small>
                                @endif
                            </div>
                            @elseif($estDimanche && $type_versement === 'journalier')
                            <div class="alert alert-danger mb-0">
                                <i class="bi bi-calendar-x me-2"></i>
                                <strong>Enregistrement bloqué</strong> - Les versements journaliers ne sont pas autorisés le dimanche.
                                @if($arrieresCumules > 0)
                                <br><small>Vous pouvez uniquement enregistrer un <strong>remboursement d'arriérés</strong>.</small>
                                @endif
                            </div>
                            @elseif($versementExistantJour && $type_versement === 'journalier' && $montantRestantJour > 0)
                            <div class="alert alert-warning mb-0">
                                <i class="bi bi-lock me-2"></i>
                                <strong>Enregistrement bloqué</strong> - Un versement existe déjà pour ce jour.
                                <a href="{{ route('cashier.versements.index') }}" class="alert-link">Compléter via la liste</a>
                            </div>
                            @else
                            <button type="submit" class="btn btn-success btn-lg" wire:loading.attr="disabled" {{ (($versementExistantJour && $type_versement === 'journalier') || ($estDimanche && $type_versement === 'journalier') || ($estJourRepos && $type_versement === 'journalier')) ? 'disabled' : '' }}>
                                <span wire:loading.remove wire:target="enregistrer">
                                    <i class="bi bi-check-circle me-2"></i>Enregistrer le Versement
                                </span>
                                <span wire:loading wire:target="enregistrer">
                                    <span class="spinner-border spinner-border-sm me-2"></span>Enregistrement...
                                </span>
                            </button>
                            @endif
                        </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>

        <!-- Panneau latéral -->
        <div class="col-lg-4">
            <!-- Solde Caisse -->
            <div class="card mb-4 border-0 bg-success bg-opacity-10">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-success rounded-circle p-3">
                            <i class="bi bi-wallet2 fs-4 text-white"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block">Solde en caisse</small>
                            <h4 class="mb-0 fw-bold text-success">{{ number_format($soldeActuel) }} FC</h4>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Résumé du cycle --}}
            @if($motardSelectionne && !empty($cycleInfo))
            <div class="card mb-4 border-{{ $estJourRepos ? 'success' : 'primary' }}">
                <div class="card-header py-2">
                    <h6 class="mb-0 fw-bold small">
                        <i class="bi bi-recycle me-1 text-primary"></i>Résumé Cycle
                    </h6>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush small">
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Cycle actuel</span>
                            <strong>#{{ $cycleInfo['cycle_numero'] ?? 1 }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Jours travaillés</span>
                            <strong>{{ $cycleInfo['jours_travailles_cycle'] ?? 0 }} / 6</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Avant repos</span>
                            <strong class="text-{{ ($cycleInfo['jours_restants_cycle'] ?? 0) <= 1 ? 'warning' : 'primary' }}">
                                {{ $cycleInfo['jours_restants_cycle'] ?? 0 }} jour(s)
                            </strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Total jours</span>
                            <strong>{{ $cycleInfo['total_jours_travailles'] ?? 0 }}</strong>
                        </li>
                        @if($cycleInfo['dernier_versement'] ?? null)
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Dernier versement</span>
                            <strong>{{ $cycleInfo['dernier_versement'] }}</strong>
                        </li>
                        @endif
                        <li class="list-group-item">
                            <span class="badge bg-{{ $estJourRepos ? 'success' : ($peutFaireVersement ? 'primary' : 'warning') }} w-100 py-2">
                                @if($estJourRepos)
                                    <i class="bi bi-moon-stars me-1"></i>Jour de repos
                                @elseif($peutFaireVersement)
                                    <i class="bi bi-check-circle me-1"></i>Peut verser
                                @else
                                    <i class="bi bi-exclamation-triangle me-1"></i>{{ $raisonBlocage }}
                                @endif
                            </span>
                        </li>
                    </ul>
                </div>
            </div>
            @endif

            @if($motardSelectionne && (float)$montant > 0)
            <!-- Aperçu de la répartition -->
            <div class="card mb-4">
                <div class="card-header py-3 bg-light">
                    <h6 class="mb-0 fw-bold">
                        <i class="bi bi-pie-chart me-2 text-primary"></i>Répartition prévisionnelle
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted d-block">Montant versé</small>
                        <h5 class="fw-bold mb-0">{{ number_format((float)$montant) }} FC</h5>
                    </div>
                    <div class="alert alert-info mt-3 mb-0 small">
                        <i class="bi bi-info-circle me-1"></i>
                        Ce montant sera ajouté à la caisse unique du collecteur.
                    </div>
                </div>
            </div>
            @endif

            @if($motardSelectionne && count($arrieresDetails) > 0)
            <!-- Historique des arriérés -->
            <div class="card">
                <div class="card-header py-3 bg-light">
                    <h6 class="mb-0 fw-bold">
                        <i class="bi bi-clock-history me-2 text-danger"></i>Détail des arriérés
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush" style="max-height: 300px; overflow-y: auto;">
                        @foreach($arrieresDetails as $arr)
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="text-muted">{{ $arr['date'] }}</small>
                                    <br>
                                    <small>Versé: {{ number_format($arr['montant_verse']) }}/{{ number_format($arr['montant_attendu']) }} FC</small>
                                </div>
                                <span class="badge bg-danger">{{ number_format($arr['arrieres']) }} FC</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <div class="p-3 bg-danger bg-opacity-10 border-top">
                        <div class="d-flex justify-content-between align-items-center">
                            <strong>Total Arriérés</strong>
                            <strong class="text-danger">{{ number_format($arrieresCumules) }} FC</strong>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

