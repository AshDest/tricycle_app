<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-plus-circle me-2 text-success"></i>Enregistrer un Versement Hebdomadaire
            </h4>
            <p class="text-muted mb-0">Réception du versement hebdomadaire d'un motard (Lundi - Samedi)</p>
        </div>
        <a href="{{ route('cashier.versements.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Retour
        </a>
    </div>

    <!-- Info répartition -->
    <div class="alert alert-info mb-4">
        <div class="d-flex align-items-center gap-3">
            <i class="bi bi-info-circle fs-4"></i>
            <div>
                <strong>Système de répartition hebdomadaire (calendrier civil) :</strong><br>
                <small>Semaine de travail = <strong>Lundi à Samedi</strong> (6 jours)</small><br>
                Sur 6 jours de recettes → <span class="badge bg-warning">5 jours = Propriétaire (83.33%)</span>
                + <span class="badge bg-info">1 jour = OKAMI (16.67%)</span>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Formulaire -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-cash-stack me-2 text-success"></i>Détails du Versement</h6>
                </div>
                <div class="card-body">
                    <form wire:submit="enregistrer">
                        <!-- Sélection de la semaine civile -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-calendar-week text-primary me-1"></i>
                                Semaine civile concernée <span class="text-danger">*</span>
                            </label>
                            <select wire:model.live="semaine_selectionnee" class="form-select @error('semaine_selectionnee') is-invalid @enderror" required>
                                @foreach($semaines ?? [] as $semaine)
                                <option value="{{ $semaine['index'] }}">
                                    {{ $semaine['label'] }}
                                    @if($semaine['est_courante'])
                                    - Sem. {{ $semaine['numero'] }}/{{ $semaine['annee'] }}
                                    @else
                                    (Sem. {{ $semaine['numero'] }})
                                    @endif
                                </option>
                                @endforeach
                            </select>
                            @error('semaine_selectionnee')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                            @if(isset($semaines[$semaine_selectionnee]))
                            @php $semaineInfo = $semaines[$semaine_selectionnee]; @endphp
                            <div class="mt-2 p-3 bg-light rounded">
                                <div class="row text-center">
                                    <div class="col-4">
                                        <small class="text-muted d-block">Début (Lundi)</small>
                                        <strong>{{ $semaineInfo['debut_formatted'] ?? '' }}</strong>
                                    </div>
                                    <div class="col-4">
                                        <small class="text-muted d-block">Fin (Samedi)</small>
                                        <strong>{{ $semaineInfo['fin_formatted'] ?? '' }}</strong>
                                    </div>
                                    <div class="col-4">
                                        <small class="text-muted d-block">Jours de travail</small>
                                        <span class="badge {{ $semaineInfo['est_complete'] ? 'bg-success' : 'bg-warning' }}">
                                            {{ $semaineInfo['jours_ecoules'] }}/6 jours
                                        </span>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>

                        <!-- Sélection du motard -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Motard <span class="text-danger">*</span></label>
                            <select wire:model.live="motard_id" class="form-select @error('motard_id') is-invalid @enderror" required>
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
                        <div class="card mb-4 {{ $arrieresCumules > 0 ? 'border-warning' : 'border-success' }}">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <h6 class="fw-bold mb-1">{{ $motardSelectionne->user->name ?? 'N/A' }}</h6>
                                        <span class="badge bg-light text-dark">
                                            <i class="bi bi-bicycle me-1"></i>{{ $motardSelectionne->moto->plaque_immatriculation ?? 'N/A' }}
                                        </span>
                                    </div>
                                    <div class="text-end">
                                        <small class="text-muted d-block">Taux de paiement</small>
                                        <span class="badge badge-soft-{{ $tauxPaiement >= 90 ? 'success' : ($tauxPaiement >= 70 ? 'warning' : 'danger') }} fs-6">
                                            {{ $tauxPaiement }}%
                                        </span>
                                    </div>
                                </div>
                                <div class="row g-3">
                                    <div class="col-6 col-md-3">
                                        <div class="bg-secondary bg-opacity-10 rounded p-3 text-center">
                                            <small class="text-muted d-block">Tarif/jour</small>
                                            <strong class="text-secondary">{{ number_format($montantJournalier) }} FC</strong>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="bg-primary bg-opacity-10 rounded p-3 text-center">
                                            <small class="text-muted d-block">Attendu/semaine</small>
                                            <strong class="text-primary">{{ number_format($montantHebdomadaireAttendu) }} FC</strong>
                                            <small class="d-block text-muted">(6 jours)</small>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="bg-info bg-opacity-10 rounded p-3 text-center">
                                            <small class="text-muted d-block">Jours semaine</small>
                                            <strong class="text-info">{{ $joursEcoules }}/6</strong>
                                            <small class="d-block text-muted">Lun-Sam</small>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="bg-{{ $arrieresCumules > 0 ? 'danger' : 'success' }} bg-opacity-10 rounded p-3 text-center">
                                            <small class="text-muted d-block">Arriérés</small>
                                            <strong class="text-{{ $arrieresCumules > 0 ? 'danger' : 'success' }}">
                                                {{ number_format($arrieresCumules) }} FC
                                            </strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Montant reçu -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Montant reçu (FC) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" wire:model.live="montant" class="form-control form-control-lg @error('montant') is-invalid @enderror"
                                       placeholder="0" min="0" required>
                                <span class="input-group-text">FC</span>
                            </div>
                            @error('montant')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror

                            @if($motardSelectionne && $montant)
                            @php
                                $ecart = $montant - $montantHebdomadaireAttendu;
                            @endphp
                            <div class="mt-2">
                                @if($ecart >= 0)
                                <div class="alert alert-success py-2 mb-0">
                                    <i class="bi bi-check-circle me-2"></i>
                                    <strong>Versement complet</strong> - Semaine payée intégralement
                                    @if($ecart > 0 && $arrieresCumules > 0)
                                    <div class="mt-2 pt-2 border-top border-success">
                                        <i class="bi bi-arrow-repeat me-1"></i>
                                        Excédent de <strong>{{ number_format($ecart) }} FC</strong> utilisé pour les arriérés
                                    </div>
                                    @endif
                                </div>
                                @else
                                <div class="alert alert-warning py-2 mb-0">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    <strong>Versement partiel</strong> - Arriéré: <strong class="text-danger">{{ number_format(abs($ecart)) }} FC</strong>
                                </div>
                                @endif
                            </div>
                            @endif
                        </div>

                        <!-- Prévisualisation de la répartition -->
                        @if($montant > 0)
                        <div class="card bg-light mb-4">
                            <div class="card-header py-2">
                                <small class="fw-bold"><i class="bi bi-pie-chart me-2"></i>Répartition prévisionnelle</small>
                            </div>
                            <div class="card-body py-3">
                                <div class="row text-center">
                                    <div class="col-6">
                                        <div class="border-end">
                                            <small class="text-muted d-block">Part Propriétaire (5/6)</small>
                                            <h5 class="fw-bold text-warning mb-0">{{ number_format($partProprietairePreview) }} FC</h5>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted d-block">Part OKAMI (1/6)</small>
                                        <h5 class="fw-bold text-info mb-0">{{ number_format($partOkamiPreview) }} FC</h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Mode de paiement -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Mode de paiement <span class="text-danger">*</span></label>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="form-check card p-3 {{ $mode_paiement === 'cash' ? 'border-success bg-success bg-opacity-10' : '' }}">
                                        <input class="form-check-input" type="radio" wire:model="mode_paiement" value="cash" id="modeCash">
                                        <label class="form-check-label d-flex align-items-center gap-2" for="modeCash">
                                            <i class="bi bi-cash text-success fs-4"></i>
                                            <span>Cash</span>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check card p-3 {{ $mode_paiement === 'mobile_money' ? 'border-primary bg-primary bg-opacity-10' : '' }}">
                                        <input class="form-check-input" type="radio" wire:model="mode_paiement" value="mobile_money" id="modeMobile">
                                        <label class="form-check-label d-flex align-items-center gap-2" for="modeMobile">
                                            <i class="bi bi-phone text-primary fs-4"></i>
                                            <span>Mobile Money</span>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check card p-3 {{ $mode_paiement === 'depot' ? 'border-info bg-info bg-opacity-10' : '' }}">
                                        <input class="form-check-input" type="radio" wire:model="mode_paiement" value="depot" id="modeDepot">
                                        <label class="form-check-label d-flex align-items-center gap-2" for="modeDepot">
                                            <i class="bi bi-bank text-info fs-4"></i>
                                            <span>Dépôt</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            @error('mode_paiement')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Notes -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Notes / Observations</label>
                            <textarea wire:model="notes" class="form-control" rows="3" placeholder="Remarques éventuelles..."></textarea>
                        </div>

                        <!-- Bouton soumettre -->
                        <div class="d-grid">
                            <button type="submit" class="btn btn-success btn-lg" wire:loading.attr="disabled">
                                <span wire:loading.remove>
                                    <i class="bi bi-check-circle me-2"></i>Enregistrer le Versement
                                </span>
                                <span wire:loading>
                                    <span class="spinner-border spinner-border-sm me-2"></span>Enregistrement...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar infos -->
        <div class="col-lg-4">
            <!-- Calendrier de la semaine -->
            <div class="card mb-4">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-calendar3 me-2 text-primary"></i>Calendrier Civil</h6>
                </div>
                <div class="card-body">
                    <p class="small text-muted mb-3">Semaine de travail: <strong>Lundi à Samedi</strong></p>
                    <div class="d-flex justify-content-around text-center">
                        @php
                            $jourActuel = \Carbon\Carbon::now()->dayOfWeekIso; // 1=Lundi, 7=Dimanche
                        @endphp
                        @foreach(['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'] as $index => $jour)
                        @php
                            $estAujourdhui = ($jourActuel === ($index + 1));
                        @endphp
                        <div class="px-1">
                            <small class="{{ $estAujourdhui ? 'fw-bold text-primary' : 'text-muted' }}">{{ $jour }}</small>
                            @if($estAujourdhui)
                            <div class="mt-1"><span class="badge bg-primary rounded-circle" style="width:8px;height:8px;padding:0;"></span></div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                    <hr class="my-3">
                    <small class="text-muted">
                        <i class="bi bi-info-circle me-1"></i>
                        Le versement doit couvrir <strong>6 jours</strong> de recettes (Lundi-Samedi)
                    </small>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-info-circle me-2 text-info"></i>Instructions</h6>
                </div>
                <div class="card-body">
                    <ol class="mb-0 ps-3">
                        <li class="mb-2">Sélectionnez la semaine civile (Lun-Sam)</li>
                        <li class="mb-2">Sélectionnez le motard qui effectue le versement</li>
                        <li class="mb-2">Vérifiez le montant attendu pour 6 jours</li>
                        <li class="mb-2">Saisissez le montant réellement reçu</li>
                        <li>Validez l'enregistrement</li>
                    </ol>
                </div>
            </div>

            <div class="card">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-wallet2 me-2 text-warning"></i>Solde Actuel</h6>
                </div>
                <div class="card-body text-center">
                    <h3 class="fw-bold text-warning mb-2">{{ number_format($soldeActuel ?? 0) }} FC</h3>
                    <small class="text-muted">En caisse (non collecté)</small>
                </div>
            </div>
        </div>
    </div>
</div>
