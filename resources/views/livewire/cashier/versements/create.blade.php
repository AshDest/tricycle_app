<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-plus-circle me-2 text-success"></i>Enregistrer un Versement
            </h4>
            <p class="text-muted mb-0">Réception du versement hebdomadaire ou remboursement d'arriérés</p>
        </div>
        <a href="{{ route('cashier.versements.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Retour
        </a>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
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
                    <h6 class="mb-0 fw-bold"><i class="bi bi-cash-stack me-2 text-success"></i>Détails du Versement</h6>
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
                                        <small class="text-muted d-block">Tarif hebdomadaire</small>
                                        <strong class="text-primary fs-5">{{ number_format($montantHebdomadaireAttendu) }} FC</strong>
                                        <small class="d-block text-muted">({{ number_format($montantJournalier) }} FC/jour × 6)</small>
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

                        <!-- Type de versement -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Type de versement <span class="text-danger">*</span></label>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-check card p-3 h-100 {{ $type_versement === 'semaine' ? 'border-primary bg-primary bg-opacity-10' : '' }}">
                                        <input class="form-check-input" type="radio" wire:model.live="type_versement" value="semaine" id="typeSemaine">
                                        <label class="form-check-label d-block" for="typeSemaine">
                                            <div class="d-flex align-items-center gap-2 mb-2">
                                                <i class="bi bi-calendar-week fs-4 text-primary"></i>
                                                <strong>Versement Hebdomadaire</strong>
                                            </div>
                                            <small class="text-muted">Payer une semaine de travail (6 jours)</small>
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
                                                @else
                                                Aucun arriéré
                                                @endif
                                            </small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if($type_versement === 'semaine')
                        <!-- Sélection de la semaine civile -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-calendar-week text-primary me-1"></i>
                                Semaine civile <span class="text-danger">*</span>
                            </label>
                            <select wire:model.live="semaine_selectionnee" class="form-select @error('semaine_selectionnee') is-invalid @enderror" required>
                                @foreach($semaines ?? [] as $semaine)
                                <option value="{{ $semaine['index'] }}">
                                    {{ $semaine['label'] }} (Sem. {{ $semaine['numero'] }})
                                </option>
                                @endforeach
                            </select>
                            @error('semaine_selectionnee')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                            <!-- Statut de la semaine sélectionnée -->
                            @if($semaineDejaVersee)
                            <div class="alert alert-info mt-3 mb-0">
                                <div class="d-flex align-items-start gap-3">
                                    <i class="bi bi-info-circle fs-4"></i>
                                    <div class="flex-grow-1">
                                        <strong>Semaine déjà versée partiellement</strong>
                                        <div class="row mt-2">
                                            <div class="col-4 text-center">
                                                <small class="text-muted d-block">Déjà versé</small>
                                                <span class="badge bg-success fs-6">{{ number_format($montantDejaVerse) }} FC</span>
                                            </div>
                                            <div class="col-4 text-center">
                                                <small class="text-muted d-block">Restant</small>
                                                <span class="badge bg-warning fs-6">{{ number_format($montantRestantSemaine) }} FC</span>
                                            </div>
                                            <div class="col-4 text-center">
                                                <small class="text-muted d-block">Attendu</small>
                                                <span class="badge bg-secondary">{{ number_format($montantHebdomadaireAttendu) }} FC</span>
                                            </div>
                                        </div>
                                        @if($montantRestantSemaine > 0)
                                        <div class="mt-2">
                                            <small class="text-muted">Tout versement sera ajouté comme complément.</small>
                                        </div>
                                        @else
                                        <div class="mt-2">
                                            <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Semaine complète!</span>
                                            @if($arrieresCumules > 0)
                                            <small class="d-block text-muted mt-1">Passez au remboursement d'arriérés si besoin.</small>
                                            @endif
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @else
                            <div class="alert alert-light mt-3 mb-0 border">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-calendar-check text-success"></i>
                                    <span><strong>Nouvelle semaine</strong> - Aucun versement enregistré</span>
                                </div>
                                <div class="mt-2">
                                    <small class="text-muted">Montant attendu: </small>
                                    <strong class="text-primary">{{ number_format($montantHebdomadaireAttendu) }} FC</strong>
                                </div>
                            </div>
                            @endif
                        </div>
                        @endif

                        @if($type_versement === 'arrieres' && count($arrieresDetails) > 0)
                        <!-- Détail des arriérés par semaine -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-list-ul text-danger me-1"></i>
                                Détail des arriérés
                            </label>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Semaine</th>
                                            <th class="text-end">Versé</th>
                                            <th class="text-end">Attendu</th>
                                            <th class="text-end text-danger">Arriéré</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($arrieresDetails as $detail)
                                        <tr>
                                            <td>
                                                <small>Sem. {{ $detail['semaine_numero'] }}</small>
                                                <br><small class="text-muted">{{ $detail['semaine_debut'] }} - {{ $detail['semaine_fin'] }}</small>
                                            </td>
                                            <td class="text-end">{{ number_format($detail['montant_verse']) }} FC</td>
                                            <td class="text-end">{{ number_format($detail['montant_attendu']) }} FC</td>
                                            <td class="text-end text-danger fw-bold">{{ number_format($detail['arrieres']) }} FC</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="table-warning">
                                        <tr>
                                            <th colspan="3" class="text-end">Total arriérés:</th>
                                            <th class="text-end text-danger">{{ number_format($arrieresCumules) }} FC</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        @endif

                        <!-- Montant reçu -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Montant reçu (FC) <span class="text-danger">*</span></label>
                            <div class="input-group input-group-lg">
                                <input type="number" wire:model.live="montant" class="form-control @error('montant') is-invalid @enderror"
                                       placeholder="0" min="1" required>
                                <span class="input-group-text">FC</span>
                            </div>
                            @error('montant')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror

                            <!-- Boutons de remplissage rapide -->
                            <div class="mt-2 d-flex flex-wrap gap-2">
                                @if($type_versement === 'semaine')
                                    @if($semaineDejaVersee && $montantRestantSemaine > 0)
                                    <button type="button" wire:click="remplirMontantSemaine" class="btn btn-sm btn-outline-warning">
                                        <i class="bi bi-lightning me-1"></i>Complément: {{ number_format($montantRestantSemaine) }} FC
                                    </button>
                                    @elseif(!$semaineDejaVersee)
                                    <button type="button" wire:click="remplirMontantSemaine" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-lightning me-1"></i>Semaine: {{ number_format($montantHebdomadaireAttendu) }} FC
                                    </button>
                                    @endif
                                    @if($arrieresCumules > 0 && !$semaineDejaVersee)
                                    <button type="button" wire:click="remplirTotalDu" class="btn btn-sm btn-outline-success">
                                        <i class="bi bi-check-all me-1"></i>Tout: {{ number_format($montantHebdomadaireAttendu + $arrieresCumules) }} FC
                                    </button>
                                    @endif
                                @else
                                    <button type="button" wire:click="remplirMontantArrieres" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-lightning me-1"></i>Arriérés: {{ number_format($arrieresCumules) }} FC
                                    </button>
                                @endif
                            </div>
                        </div>

                        <!-- Prévisualisation de la répartition -->
                        @if($montant > 0)
                        <div class="card bg-light mb-4">
                            <div class="card-header py-2">
                                <small class="fw-bold"><i class="bi bi-pie-chart me-2"></i>Répartition (5/6 Propriétaire + 1/6 OKAMI)</small>
                            </div>
                            <div class="card-body py-3">
                                <div class="row text-center">
                                    <div class="col-6">
                                        <div class="border-end">
                                            <small class="text-muted d-block">Part Propriétaire</small>
                                            <h5 class="fw-bold text-success mb-0">{{ number_format($partProprietairePreview) }} FC</h5>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted d-block">Part OKAMI</small>
                                        <h5 class="fw-bold text-warning mb-0">{{ number_format($partOkamiPreview) }} FC</h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Mode de paiement -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Mode de paiement <span class="text-danger">*</span></label>
                            <div class="row g-2">
                                <div class="col-4">
                                    <div class="form-check card p-2 text-center {{ $mode_paiement === 'cash' ? 'border-success bg-success bg-opacity-10' : '' }}">
                                        <input class="form-check-input d-none" type="radio" wire:model="mode_paiement" value="cash" id="modeCash">
                                        <label class="form-check-label" for="modeCash">
                                            <i class="bi bi-cash text-success fs-4 d-block"></i>
                                            <small>Cash</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="form-check card p-2 text-center {{ $mode_paiement === 'mobile_money' ? 'border-primary bg-primary bg-opacity-10' : '' }}">
                                        <input class="form-check-input d-none" type="radio" wire:model="mode_paiement" value="mobile_money" id="modeMobile">
                                        <label class="form-check-label" for="modeMobile">
                                            <i class="bi bi-phone text-primary fs-4 d-block"></i>
                                            <small>Mobile</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="form-check card p-2 text-center {{ $mode_paiement === 'depot' ? 'border-info bg-info bg-opacity-10' : '' }}">
                                        <input class="form-check-input d-none" type="radio" wire:model="mode_paiement" value="depot" id="modeDepot">
                                        <label class="form-check-label" for="modeDepot">
                                            <i class="bi bi-bank text-info fs-4 d-block"></i>
                                            <small>Dépôt</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Notes</label>
                            <textarea wire:model="notes" class="form-control" rows="2" placeholder="Remarques éventuelles..."></textarea>
                        </div>

                        <!-- Bouton soumettre -->
                        <div class="d-grid">
                            <button type="submit" class="btn btn-success btn-lg" wire:loading.attr="disabled" {{ !$motard_id ? 'disabled' : '' }}>
                                <span wire:loading.remove>
                                    <i class="bi bi-check-circle me-2"></i>
                                    @if($type_versement === 'arrieres')
                                    Enregistrer le Remboursement
                                    @elseif($semaineDejaVersee)
                                    Enregistrer le Complément
                                    @else
                                    Enregistrer le Versement
                                    @endif
                                </span>
                                <span wire:loading>
                                    <span class="spinner-border spinner-border-sm me-2"></span>Enregistrement...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
        </div>

        <!-- Sidebar infos -->
        <div class="col-lg-4">
            <!-- Résumé rapide -->
            @if($motardSelectionne)
            <div class="card mb-4 bg-dark text-white">
                <div class="card-body">
                    <h6 class="text-white-50 mb-3"><i class="bi bi-receipt me-2"></i>Résumé</h6>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-white-50">Motard:</span>
                        <strong>{{ $motardSelectionne->user->name ?? 'N/A' }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-white-50">Type:</span>
                        <span class="badge {{ $type_versement === 'semaine' ? 'bg-primary' : 'bg-danger' }}">
                            {{ $type_versement === 'semaine' ? 'Semaine' : 'Arriérés' }}
                        </span>
                    </div>
                    @if($type_versement === 'semaine' && isset($semaines[$semaine_selectionnee]))
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-white-50">Semaine:</span>
                        <span>{{ $semaines[$semaine_selectionnee]['numero'] }}/{{ $semaines[$semaine_selectionnee]['annee'] }}</span>
                    </div>
                    @endif
                    <hr class="border-secondary">
                    <div class="d-flex justify-content-between">
                        <span class="text-white-50">Montant:</span>
                        <strong class="fs-5 text-success">{{ number_format($montant ?: 0) }} FC</strong>
                    </div>
                </div>
            </div>
            @endif

            <!-- Info système -->
            <div class="card mb-4">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-info-circle me-2 text-info"></i>Système</h6>
                </div>
                <div class="card-body">
                    <ul class="mb-0 ps-3 small">
                        <li class="mb-2">Semaine = <strong>Lundi à Samedi</strong> (6 jours)</li>
                        <li class="mb-2">Répartition: 5/6 Propriétaire + 1/6 OKAMI</li>
                        <li class="mb-2">Si semaine déjà versée → Complément automatique</li>
                        <li>Les excédents remboursent les arriérés</li>
                    </ul>
                </div>
            </div>

            <!-- Solde -->
            <div class="card">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-wallet2 me-2 text-warning"></i>Ma Caisse</h6>
                </div>
                <div class="card-body text-center">
                    <h3 class="fw-bold text-warning mb-1">{{ number_format($soldeActuel ?? 0) }} FC</h3>
                    <small class="text-muted">Non collecté</small>
                </div>
            </div>
        </div>
    </div>
</div>

