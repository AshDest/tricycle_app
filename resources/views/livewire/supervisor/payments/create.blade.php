<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-plus-circle me-2 text-success"></i>Nouvelle Demande de Paiement Hebdomadaire
            </h4>
            <p class="text-muted mb-0">Soumettre une demande de paiement pour une semaine de versements</p>
        </div>
        <a href="{{ route('supervisor.payments.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Retour
        </a>
    </div>

    <div class="row g-4">
        <!-- Formulaire -->
        <div class="col-lg-8">
            <form wire:submit="submit">
                <!-- Sélection de la semaine (pour les sources non-propriétaire) -->
                @if($source_caisse !== 'proprietaire')
                <div class="card mb-4 border-primary">
                    <div class="card-header py-3 bg-primary text-white">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-calendar-week me-2"></i>Période Hebdomadaire</h6>
                    </div>
                    <div class="card-body">
                        <label class="form-label fw-semibold">Sélectionner la semaine <span class="text-danger">*</span></label>
                        <select wire:model.live="semaine_selectionnee" class="form-select form-select-lg @error('semaine_selectionnee') is-invalid @enderror">
                            @foreach($semaines ?? [] as $semaine)
                            <option value="{{ $semaine['index'] }}">
                                {{ $semaine['label'] }} (Sem. {{ $semaine['numero'] }})
                            </option>
                            @endforeach
                        </select>
                        @error('semaine_selectionnee')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Les versements de cette semaine seront utilisés pour calculer le montant disponible.</small>
                    </div>
                </div>
                @endif

                <!-- Choix de la source de caisse -->
                <div class="card mb-4">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-wallet2 me-2 text-warning"></i>Source de la Caisse</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <div class="form-check card p-3 h-100 {{ $source_caisse === 'proprietaire' ? 'border-success bg-success bg-opacity-10' : '' }}">
                                    <input class="form-check-input" type="radio" wire:model.live="source_caisse" value="proprietaire" id="sourceProp">
                                    <label class="form-check-label d-flex align-items-start gap-3 w-100" for="sourceProp">
                                        <i class="bi bi-people fs-3 text-success"></i>
                                        <div>
                                            <strong class="d-block">Paiement Propriétaires</strong>
                                            <small class="text-muted">Versements collectés</small>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check card p-3 h-100 {{ $source_caisse === 'okami' ? 'border-warning bg-warning bg-opacity-10' : '' }}">
                                    <input class="form-check-input" type="radio" wire:model.live="source_caisse" value="okami" id="sourceOkami">
                                    <label class="form-check-label d-flex align-items-start gap-3 w-100" for="sourceOkami">
                                        <i class="bi bi-building fs-3 text-warning"></i>
                                        <div>
                                            <strong class="d-block">Caisse OKAMI</strong>
                                            <small class="text-muted">Dépenses OKAMI</small>
                                            <div class="mt-1">
                                                <span class="badge bg-warning text-dark">{{ $taux_usd_cdf > 0 ? number_format($soldeOkamiDisponible / $taux_usd_cdf, 2) : '0' }} $ dispo</span>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check card p-3 h-100 {{ $source_caisse === 'lavage' ? 'border-info bg-info bg-opacity-10' : '' }}">
                                    <input class="form-check-input" type="radio" wire:model.live="source_caisse" value="lavage" id="sourceLavage">
                                    <label class="form-check-label d-flex align-items-start gap-3 w-100" for="sourceLavage">
                                        <i class="bi bi-droplet fs-3 text-info"></i>
                                        <div>
                                            <strong class="d-block">Part Lavage</strong>
                                            <small class="text-muted">20% lavages</small>
                                            <div class="mt-1">
                                                <span class="badge bg-info">{{ $taux_usd_cdf > 0 ? number_format($soldeLavageOkamiDisponible / $taux_usd_cdf, 2) : '0' }} $</span>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check card p-3 h-100 {{ $source_caisse === 'commission' ? 'border-purple bg-purple bg-opacity-10' : '' }}" style="{{ $source_caisse === 'commission' ? 'border-color: #6f42c1 !important; background-color: rgba(111,66,193,0.1) !important;' : '' }}">
                                    <input class="form-check-input" type="radio" wire:model.live="source_caisse" value="commission" id="sourceCommission">
                                    <label class="form-check-label d-flex align-items-start gap-3 w-100" for="sourceCommission">
                                        <i class="bi bi-percent fs-3" style="color: #6f42c1;"></i>
                                        <div>
                                            <strong class="d-block">Part Commission</strong>
                                            <small class="text-muted">30% commissions</small>
                                            <div class="mt-1">
                                                <span class="badge" style="background-color: #6f42c1;">{{ $taux_usd_cdf > 0 ? number_format($soldeCommissionOkamiDisponible / $taux_usd_cdf, 2) : '0' }} $</span>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @if($source_caisse === 'proprietaire')
                <!-- Section Propriétaire -->
                <div class="card mb-4">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-person me-2 text-primary"></i>Propriétaire</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Sélectionner le propriétaire <span class="text-danger">*</span></label>
                            <select wire:model.live="proprietaire_id" class="form-select @error('proprietaire_id') is-invalid @enderror">
                                <option value="">-- Choisir un propriétaire --</option>
                                @foreach($proprietaires as $prop)
                                <option value="{{ $prop->id }}">
                                    {{ $prop->user->name ?? $prop->raison_sociale }}
                                    - {{ $prop->motos_actives ?? 0 }} moto(s)
                                    - Solde total: {{ $taux_usd_cdf > 0 ? number_format(($prop->solde_disponible ?? 0) / $taux_usd_cdf, 2) : '0' }} $
                                </option>
                                @endforeach
                            </select>
                            @error('proprietaire_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        @if($proprietaireSelectionne)
                        {{-- Info date de début de service --}}
                        @if($dateDebutService)
                        <div class="alert alert-light border mb-3">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-calendar-event text-primary fs-5"></i>
                                <div>
                                    <strong>Début de service:</strong>
                                    <span class="text-primary">{{ \Carbon\Carbon::parse($dateDebutService)->format('d/m/Y') }}</span>
                                    <br>
                                    <small class="text-muted">
                                        Les semaines disponibles sont calculées depuis cette date
                                        ({{ \Carbon\Carbon::parse($dateDebutService)->diffForHumans() }})
                                    </small>
                                </div>
                            </div>
                        </div>
                        @else
                        <div class="alert alert-warning mb-3">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <small>Aucune date de début de contrat définie pour les motos de ce propriétaire. Les semaines affichées sont basées sur les versements existants.</small>
                        </div>
                        @endif

                        {{-- Sélection de la semaine spécifique au propriétaire --}}
                        @if(count($semainesProprietaire) > 0)
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-calendar-week me-1 text-primary"></i>
                                Semaine de paiement <span class="text-danger">*</span>
                            </label>
                            <select wire:model.live="semaine_selectionnee" class="form-select @error('semaine_selectionnee') is-invalid @enderror">
                                @foreach($semainesProprietaire as $sem)
                                <option value="{{ $sem['index'] }}" {{ $sem['deja_paye'] ? 'class=text-muted' : '' }}>
                                    {{ $sem['label'] }}
                                    @if($sem['total_versements'] > 0) — {{ $taux_usd_cdf > 0 ? number_format($sem['total_versements'] / $taux_usd_cdf, 2) : '0' }} $ collectés @endif
                                    @if($sem['deja_paye']) ✓ Déjà demandé @endif
                                </option>
                                @endforeach
                            </select>
                            @error('semaine_selectionnee')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">{{ count($semainesProprietaire) }} semaine(s) depuis le début de service</small>
                        </div>
                        @endif

                        <div class="alert alert-info mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>{{ $proprietaireSelectionne->user->name ?? 'N/A' }}</strong>
                                    <br><small>{{ $proprietaireSelectionne->motos->count() }} moto(s) enregistrée(s)</small>
                                </div>
                                <div class="text-end">
                                    <div class="mb-1">
                                        <span class="badge bg-success fs-6">Solde total: {{ $taux_usd_cdf > 0 ? number_format($soldeDisponible / $taux_usd_cdf, 2) : '0' }} $</span>
                                    </div>
                                    <div>
                                        <span class="badge bg-primary">Semaine: {{ $taux_usd_cdf > 0 ? number_format($soldeHebdomadaire / $taux_usd_cdf, 2) : '0' }} $</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Info si la semaine a déjà été payée --}}
                        @if(isset($semainesProprietaire[$semaine_selectionnee]) && $semainesProprietaire[$semaine_selectionnee]['deja_paye'])
                        <div class="alert alert-warning mb-3">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>Attention:</strong> Une demande de paiement existe déjà pour cette semaine.
                        </div>
                        @endif

                        <!-- Détail des versements de la semaine -->
                        @if(count($versementsSemaine) > 0)
                        <div class="card border-primary">
                            <div class="card-header py-2 bg-light">
                                <h6 class="mb-0 small fw-bold">
                                    <i class="bi bi-list-check me-1"></i>
                                    Versements de la semaine ({{ count($versementsSemaine) }})
                                </h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive" style="max-height: 200px; overflow-y: auto;">
                                    <table class="table table-sm table-hover mb-0">
                                        <thead class="bg-light sticky-top">
                                            <tr>
                                                <th>Date</th>
                                                <th>Motard</th>
                                                <th>Moto</th>
                                                <th class="text-end">Montant</th>
                                                <th class="text-end">Part Prop.</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($versementsSemaine as $v)
                                            <tr>
                                                <td><small>{{ $v['date'] }}</small></td>
                                                <td><small>{{ $v['motard'] }}</small></td>
                                                <td><small>{{ $v['moto'] }}</small></td>
                                                <td class="text-end"><small>{{ $taux_usd_cdf > 0 ? number_format($v['montant'] / $taux_usd_cdf, 2) : '0' }} $</small></td>
                                                <td class="text-end"><small class="text-success fw-bold">{{ $taux_usd_cdf > 0 ? number_format($v['part_proprietaire'] / $taux_usd_cdf, 2) : '0' }} $</small></td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="bg-light">
                                            <tr class="fw-bold">
                                                <td colspan="3">Total</td>
                                                <td class="text-end">{{ $taux_usd_cdf > 0 ? number_format($totalVersementsSemaine / $taux_usd_cdf, 2) : '0' }} $</td>
                                                <td class="text-end text-success">{{ $taux_usd_cdf > 0 ? number_format($partProprietaireSemaine / $taux_usd_cdf, 2) : '0' }} $</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                        @else
                        <div class="alert alert-warning mb-0">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Aucun versement trouvé pour ce propriétaire durant cette semaine.
                        </div>
                        @endif
                        @endif
                    </div>
                </div>
                @elseif($source_caisse === 'okami')
                <!-- Section Bénéficiaire OKAMI -->
                <div class="card mb-4">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-person-badge me-2 text-warning"></i>Bénéficiaire (Caisse OKAMI)</h6>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning mb-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="bi bi-info-circle me-2"></i>
                                    <strong>Solde OKAMI</strong>
                                </div>
                                <div>
                                    <span class="badge bg-warning text-dark me-2">Total: {{ $taux_usd_cdf > 0 ? number_format($soldeOkamiDisponible / $taux_usd_cdf, 2) : '0' }} $</span>
                                    <span class="badge bg-secondary">Semaine: {{ $taux_usd_cdf > 0 ? number_format($soldeOkamiSemaine / $taux_usd_cdf, 2) : '0' }} $</span>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nom du bénéficiaire <span class="text-danger">*</span></label>
                                <input type="text" wire:model="beneficiaire_nom"
                                       class="form-control @error('beneficiaire_nom') is-invalid @enderror"
                                       placeholder="Nom complet du bénéficiaire">
                                @error('beneficiaire_nom')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Téléphone du bénéficiaire</label>
                                <input type="text" wire:model="beneficiaire_telephone"
                                       class="form-control @error('beneficiaire_telephone') is-invalid @enderror"
                                       placeholder="+243 XXX XXX XXX">
                                @error('beneficiaire_telephone')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Motif du paiement <span class="text-danger">*</span></label>
                                <textarea wire:model="beneficiaire_motif"
                                          class="form-control @error('beneficiaire_motif') is-invalid @enderror"
                                          rows="2" placeholder="Décrivez le motif de ce paiement..."></textarea>
                                @error('beneficiaire_motif')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                @elseif($source_caisse === 'lavage')
                <!-- Section Bénéficiaire Lavage -->
                <div class="card mb-4">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-droplet me-2 text-info"></i>Bénéficiaire (Part OKAMI Lavage)</h6>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info mb-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="bi bi-info-circle me-2"></i>
                                    <strong>Part OKAMI Lavage (20%)</strong>
                                </div>
                                <div>
                                    <span class="badge bg-info me-2">Total: {{ $taux_usd_cdf > 0 ? number_format($soldeLavageOkamiDisponible / $taux_usd_cdf, 2) : '0' }} $</span>
                                    <span class="badge bg-secondary">Semaine: {{ $taux_usd_cdf > 0 ? number_format($soldeLavageOkamiSemaine / $taux_usd_cdf, 2) : '0' }} $</span>
                                </div>
                            </div>
                        </div>

                        <!-- Détail des lavages de la semaine -->
                        @if(count($lavagesSemaine ?? []) > 0)
                        <div class="card border-info mb-4">
                            <div class="card-header py-2 bg-light">
                                <h6 class="mb-0 small fw-bold">
                                    <i class="bi bi-list-check me-1"></i>
                                    Lavages internes de la semaine ({{ count($lavagesSemaine) }})
                                </h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive" style="max-height: 200px; overflow-y: auto;">
                                    <table class="table table-sm table-hover mb-0">
                                        <thead class="bg-light sticky-top">
                                            <tr>
                                                <th>Date</th>
                                                <th>Moto</th>
                                                <th>Laveur</th>
                                                <th class="text-end">Part OKAMI (20%)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($lavagesSemaine as $l)
                                            <tr>
                                                <td><small>{{ $l['date'] }}</small></td>
                                                <td><small>{{ $l['moto'] }}</small></td>
                                                <td><small>{{ $l['laveur'] }}</small></td>
                                                <td class="text-end"><small class="text-info fw-bold">{{ $taux_usd_cdf > 0 ? number_format($l['part_okami'] / $taux_usd_cdf, 2) : '0' }} $</small></td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="bg-light">
                                            <tr class="fw-bold">
                                                <td colspan="3">Total Part OKAMI</td>
                                                <td class="text-end text-info">{{ $taux_usd_cdf > 0 ? number_format($partOkamiLavageSemaine / $taux_usd_cdf, 2) : '0' }} $</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                        @endif

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nom du bénéficiaire <span class="text-danger">*</span></label>
                                <input type="text" wire:model="beneficiaire_nom"
                                       class="form-control @error('beneficiaire_nom') is-invalid @enderror"
                                       placeholder="Nom complet du bénéficiaire">
                                @error('beneficiaire_nom')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Téléphone du bénéficiaire</label>
                                <input type="text" wire:model="beneficiaire_telephone"
                                       class="form-control @error('beneficiaire_telephone') is-invalid @enderror"
                                       placeholder="+243 XXX XXX XXX">
                                @error('beneficiaire_telephone')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Motif du paiement <span class="text-danger">*</span></label>
                                <textarea wire:model="beneficiaire_motif"
                                          class="form-control @error('beneficiaire_motif') is-invalid @enderror"
                                          rows="2" placeholder="Décrivez le motif de ce paiement (ex: Salaire laveur, Achat matériel...)"></textarea>
                                @error('beneficiaire_motif')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                @elseif($source_caisse === 'commission')
                <!-- Section Bénéficiaire Commission -->
                <div class="card mb-4">
                    <div class="card-header py-3" style="background-color: rgba(111,66,193,0.1);">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-percent me-2" style="color: #6f42c1;"></i>Bénéficiaire (Part OKAMI Commission)</h6>
                    </div>
                    <div class="card-body">
                        <div class="alert mb-4" style="background-color: rgba(111,66,193,0.1); border-color: #6f42c1;">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="bi bi-info-circle me-2" style="color: #6f42c1;"></i>
                                    <strong>Part OKAMI Commission (30%)</strong>
                                    <br><small class="text-muted">70% va à LATEM, 30% à OKAMI</small>
                                </div>
                                <div>
                                    <span class="badge" style="background-color: #6f42c1;">Disponible: {{ $taux_usd_cdf > 0 ? number_format($soldeCommissionOkamiDisponible / $taux_usd_cdf, 2) : '0' }} $</span>
                                </div>
                            </div>
                        </div>

                        <!-- Détail des commissions validées -->
                        @if(count($commissionsValidees ?? []) > 0)
                        <div class="card mb-4" style="border-color: #6f42c1;">
                            <div class="card-header py-2 bg-light">
                                <h6 class="mb-0 small fw-bold">
                                    <i class="bi bi-list-check me-1"></i>
                                    Commissions validées (derniers mois)
                                </h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive" style="max-height: 200px; overflow-y: auto;">
                                    <table class="table table-sm table-hover mb-0">
                                        <thead class="bg-light sticky-top">
                                            <tr>
                                                <th>Période</th>
                                                <th>Collecteur</th>
                                                <th class="text-end">Part LATEM (70%)</th>
                                                <th class="text-end">Part OKAMI (30%)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($commissionsValidees as $c)
                                            <tr>
                                                <td><small>{{ $c['periode'] }}</small></td>
                                                <td><small>{{ $c['collecteur'] }}</small></td>
                                                <td class="text-end"><small class="text-success">{{ $taux_usd_cdf > 0 ? number_format($c['part_nth'] / $taux_usd_cdf, 2) : '0' }} $</small></td>
                                                <td class="text-end"><small class="fw-bold" style="color: #6f42c1;">{{ $taux_usd_cdf > 0 ? number_format($c['part_okami'] / $taux_usd_cdf, 2) : '0' }} $</small></td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        @endif

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nom du bénéficiaire <span class="text-danger">*</span></label>
                                <input type="text" wire:model="beneficiaire_nom"
                                       class="form-control @error('beneficiaire_nom') is-invalid @enderror"
                                       placeholder="Nom complet du bénéficiaire">
                                @error('beneficiaire_nom')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Téléphone du bénéficiaire</label>
                                <input type="text" wire:model="beneficiaire_telephone"
                                       class="form-control @error('beneficiaire_telephone') is-invalid @enderror"
                                       placeholder="+243 XXX XXX XXX">
                                @error('beneficiaire_telephone')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Motif du paiement <span class="text-danger">*</span></label>
                                <textarea wire:model="beneficiaire_motif"
                                          class="form-control @error('beneficiaire_motif') is-invalid @enderror"
                                          rows="2" placeholder="Décrivez le motif de ce paiement (ex: Prélèvement part OKAMI...)"></textarea>
                                @error('beneficiaire_motif')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <div class="card mb-4">
                    <div class="card-header py-3">
                        <h6 class="mb-0 fw-bold"><i class="bi bi-cash-stack me-2 text-success"></i>Détails du Paiement</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            {{-- Saisie en USD avec conversion automatique en CDF pour toutes les sources --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-currency-dollar me-1 text-success"></i>
                                    Montant à payer (USD) <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-success text-white">$</span>
                                    <input type="number" wire:model.live="montant_usd"
                                           class="form-control form-control-lg @error('montant_usd') is-invalid @enderror"
                                           placeholder="0.00" min="0.01" step="0.01">
                                    <span class="input-group-text">USD</span>
                                </div>
                                @error('montant_usd')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror

                                {{-- Affichage du taux et de l'équivalent CDF --}}
                                <div class="mt-2">
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <small class="text-muted">
                                            <i class="bi bi-arrow-left-right me-1"></i>
                                            Taux: 1 USD = <strong>{{ number_format($tauxUsdCdf, 2) }}</strong> FC
                                        </small>
                                    </div>
                                    @if($montant_usd && is_numeric($montant_usd) && $montant_usd > 0)
                                    <div class="alert alert-success py-2 px-3 mb-0 d-flex align-items-center gap-2">
                                        <i class="bi bi-calculator"></i>
                                        <div>
                                            <small class="d-block text-muted">Équivalent en Franc Congolais:</small>
                                            <strong class="fs-5">{{ number_format($montant, 2) }} FC</strong>
                                        </div>
                                    </div>
                                    @endif
                                </div>

                                {{-- Montant CDF caché (calculé automatiquement) --}}
                                <input type="hidden" wire:model="montant">

                                <!-- Boutons de remplissage rapide -->
                                <div class="mt-2 d-flex gap-2 flex-wrap">
                                    @if($source_caisse === 'proprietaire')
                                        @if($soldeHebdomadaire > 0)
                                        <button type="button" wire:click="remplirMontantSemaine" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-calendar-week me-1"></i>Semaine (${{ $taux_usd_cdf > 0 ? number_format($soldeHebdomadaire / $taux_usd_cdf, 2) : '0' }})
                                        </button>
                                        @endif
                                        @if($soldeDisponible > 0)
                                        <button type="button" wire:click="remplirMontantTotal" class="btn btn-sm btn-outline-success">
                                            <i class="bi bi-wallet2 me-1"></i>Total (${{ $taux_usd_cdf > 0 ? number_format($soldeDisponible / $taux_usd_cdf, 2) : '0' }})
                                        </button>
                                        @endif
                                    @elseif($source_caisse === 'okami')
                                        @if($soldeOkamiSemaine > 0)
                                        <button type="button" wire:click="remplirMontantSemaine" class="btn btn-sm btn-outline-warning">
                                            <i class="bi bi-calendar-week me-1"></i>Semaine (${{ $taux_usd_cdf > 0 ? number_format($soldeOkamiSemaine / $taux_usd_cdf, 2) : '0' }})
                                        </button>
                                        @endif
                                        @if($soldeOkamiDisponible > 0)
                                        <button type="button" wire:click="remplirMontantTotal" class="btn btn-sm btn-outline-success">
                                            <i class="bi bi-wallet2 me-1"></i>Total (${{ $taux_usd_cdf > 0 ? number_format($soldeOkamiDisponible / $taux_usd_cdf, 2) : '0' }})
                                        </button>
                                        @endif
                                    @elseif($source_caisse === 'lavage')
                                        @if($soldeLavageOkamiSemaine > 0)
                                        <button type="button" wire:click="remplirMontantSemaine" class="btn btn-sm btn-outline-info">
                                            <i class="bi bi-calendar-week me-1"></i>Semaine (${{ $taux_usd_cdf > 0 ? number_format($soldeLavageOkamiSemaine / $taux_usd_cdf, 2) : '0' }})
                                        </button>
                                        @endif
                                        @if($soldeLavageOkamiDisponible > 0)
                                        <button type="button" wire:click="remplirMontantTotal" class="btn btn-sm btn-outline-success">
                                            <i class="bi bi-wallet2 me-1"></i>Total (${{ $taux_usd_cdf > 0 ? number_format($soldeLavageOkamiDisponible / $taux_usd_cdf, 2) : '0' }})
                                        </button>
                                        @endif
                                    @elseif($source_caisse === 'commission')
                                        @if($soldeCommissionOkamiDisponible > 0)
                                        <button type="button" wire:click="remplirMontantTotal" class="btn btn-sm" style="border-color: #6f42c1; color: #6f42c1;">
                                            <i class="bi bi-wallet me-1"></i>Total (${{ $taux_usd_cdf > 0 ? number_format($soldeCommissionOkamiDisponible / $taux_usd_cdf, 2) : '0' }})
                                        </button>
                                        @endif
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Mode de paiement <span class="text-danger">*</span></label>
                                <select wire:model.live="mode_paiement" class="form-select @error('mode_paiement') is-invalid @enderror">
                                    @foreach($modesPaiement as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('mode_paiement')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Numéro de compte / téléphone</label>
                                <input type="text" wire:model="numero_compte"
                                       class="form-control" placeholder="Ex: +243 XXX XXX XXX">
                                <small class="text-muted">Numéro de réception du paiement</small>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Notes / Commentaires</label>
                                <textarea wire:model="notes" class="form-control" rows="2"
                                          placeholder="Informations supplémentaires..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Boutons -->
                <div class="d-flex gap-3">
                    @php
                        $canSubmit = ($source_caisse === 'proprietaire' && $proprietaire_id && $soldeDisponible > 0)
                                  || ($source_caisse === 'okami' && $soldeOkamiDisponible > 0)
                                  || ($source_caisse === 'lavage' && $soldeLavageOkamiDisponible > 0)
                                  || ($source_caisse === 'commission' && $soldeCommissionOkamiDisponible > 0);
                    @endphp
                    <button type="submit" class="btn btn-success btn-lg flex-grow-1"
                            wire:loading.attr="disabled"
                            @if(!$canSubmit) disabled @endif>
                        <span wire:loading.remove>
                            <i class="bi bi-send me-2"></i>Soumettre la Demande
                        </span>
                        <span wire:loading>
                            <span class="spinner-border spinner-border-sm me-2"></span>Envoi en cours...
                        </span>
                    </button>
                    <a href="{{ route('supervisor.payments.index') }}" class="btn btn-outline-secondary btn-lg">
                        Annuler
                    </a>
                </div>
            </form>
        </div>

        <!-- Sidebar - Infos -->
        <div class="col-lg-4">
            <!-- Résumé de la semaine sélectionnée -->
            @php
                $semainesAffichees = ($source_caisse === 'proprietaire' && count($semainesProprietaire ?? []) > 0)
                    ? $semainesProprietaire
                    : ($semaines ?? []);
            @endphp
            @if(isset($semainesAffichees[$semaine_selectionnee]))
            @php $semaineActuelle = $semainesAffichees[$semaine_selectionnee]; @endphp
            <div class="card mb-4 border-primary">
                <div class="card-header py-3 bg-primary text-white">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-calendar-week me-2"></i>Semaine {{ $semaineActuelle['numero'] }}</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Période:</span>
                        <strong>{{ $semaineActuelle['debut_formatted'] }} - {{ $semaineActuelle['fin_formatted'] }}</strong>
                    </div>
                    @if($semaineActuelle['est_courante'])
                    <span class="badge bg-info">Semaine en cours</span>
                    @endif
                </div>
            </div>
            @endif

            @if($source_caisse === 'proprietaire')
            {{-- Info propriétaire et début de service --}}
            @if($proprietaireSelectionne && $dateDebutService)
            <div class="card mb-4 border-info">
                <div class="card-header py-3 bg-info text-white">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-calendar-check me-2"></i>Service du Propriétaire</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Début service:</span>
                        <strong class="text-primary">{{ \Carbon\Carbon::parse($dateDebutService)->format('d/m/Y') }}</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Semaines actives:</span>
                        <strong>{{ count($semainesProprietaire ?? []) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Motos en service:</span>
                        <strong>{{ $proprietaireSelectionne->motos->where('statut', 'actif')->count() }}</strong>
                    </div>
                    @php
                        $motosAvecContrat = $proprietaireSelectionne->motos->whereNotNull('contrat_debut');
                    @endphp
                    @if($motosAvecContrat->count() > 0)
                    <hr class="my-2">
                    <small class="text-muted fw-bold d-block mb-1">Motos & Contrats:</small>
                    @foreach($motosAvecContrat as $m)
                    <div class="d-flex justify-content-between small mb-1">
                        <span>{{ $m->plaque_immatriculation }}</span>
                        <span class="text-muted">{{ $m->contrat_debut?->format('d/m/Y') }} → {{ $m->contrat_fin?->format('d/m/Y') }}</span>
                    </div>
                    @endforeach
                    @endif
                </div>
            </div>
            @endif
            @endif

            <div class="card mb-4 border-success">
                <div class="card-header py-3 bg-success text-white">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-currency-exchange me-2"></i>Conversion USD → FC</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Taux actuel:</span>
                        <strong class="text-success">1 USD = {{ number_format($tauxUsdCdf, 2) }} FC</strong>
                    </div>
                    <small class="text-muted d-block">
                        <i class="bi bi-info-circle me-1"></i>
                        Le montant est saisi en USD et converti automatiquement en Franc Congolais.
                        Le taux est configuré par l'administrateur.
                    </small>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-info-circle me-2 text-info"></i>Informations</h6>
                </div>
                <div class="card-body">
                    @if($source_caisse === 'proprietaire')
                    <div class="alert alert-success border-0 mb-3">
                        <i class="bi bi-people me-2"></i>
                        <strong>Paiement Propriétaires:</strong><br>
                        <small>Les versements journaliers sont cumulés par semaine et payés aux propriétaires de motos.</small>
                    </div>
                    @elseif($source_caisse === 'okami')
                    <div class="alert alert-warning border-0 mb-3">
                        <i class="bi bi-building me-2"></i>
                        <strong>Caisse OKAMI:</strong><br>
                        <small>Paiement depuis la caisse pour les dépenses et frais de gestion OKAMI.</small>
                    </div>
                    @elseif($source_caisse === 'lavage')
                    <div class="alert alert-info border-0 mb-3">
                        <i class="bi bi-droplet me-2"></i>
                        <strong>Part OKAMI Lavage (20%):</strong><br>
                        <small>20% des recettes de lavage des motos internes revient à OKAMI.</small>
                    </div>
                    @endif

                    <ul class="list-unstyled mb-0">
                        <li class="d-flex align-items-start gap-2 mb-3">
                            <i class="bi bi-1-circle text-primary mt-1"></i>
                            <span>Sélectionnez la semaine concernée</span>
                        </li>
                        <li class="d-flex align-items-start gap-2 mb-3">
                            <i class="bi bi-2-circle text-primary mt-1"></i>
                            <span>Choisissez la source de caisse</span>
                        </li>
                        <li class="d-flex align-items-start gap-2 mb-3">
                            <i class="bi bi-3-circle text-primary mt-1"></i>
                            <span>
                                @if($source_caisse === 'proprietaire')
                                Sélectionnez le propriétaire bénéficiaire
                                @else
                                Renseignez le bénéficiaire et le motif
                                @endif
                            </span>
                        </li>
                        <li class="d-flex align-items-start gap-2 mb-3">
                            <i class="bi bi-4-circle text-primary mt-1"></i>
                            <span>
                                Entrez le montant en USD (converti automatiquement en FC)
                            </span>
                        </li>
                        <li class="d-flex align-items-start gap-2">
                            <i class="bi bi-5-circle text-primary mt-1"></i>
                            <span>La demande sera traitée par le collecteur</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="card">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-currency-exchange me-2 text-success"></i>Workflow</h6>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex align-items-center gap-2">
                            <span class="badge bg-warning">1</span>
                            <span>OKAMI soumet la demande hebdomadaire</span>
                        </li>
                        <li class="list-group-item d-flex align-items-center gap-2">
                            <span class="badge bg-info">2</span>
                            <span>Collecteur effectue le paiement</span>
                        </li>
                        <li class="list-group-item d-flex align-items-center gap-2">
                            <span class="badge bg-success">3</span>
                            <span>Paiement validé</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

