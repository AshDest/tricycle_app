<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-building me-2 text-warning"></i>Solde OKAMI
            </h4>
            <p class="text-muted mb-0">Vue détaillée des fonds OKAMI (1/6 des versements + 20% lavages)</p>
        </div>
        <div class="d-flex gap-2">
            <select wire:model.live="periodeFilter" class="form-select" style="width: auto;">
                <option value="semaine">Cette semaine</option>
                <option value="mois">Ce mois</option>
                <option value="annee">Cette année</option>
            </select>
        </div>
    </div>

    <!-- Solde en Caisse Collecteur -->
    <div class="card border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
        <div class="card-body py-4">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-white bg-opacity-25 rounded-circle p-3">
                            <i class="bi bi-safe fs-2 text-white"></i>
                        </div>
                        <div>
                            <small class="d-block text-white text-opacity-75">Mon Solde OKAMI (en caisse)</small>
                            <h2 class="mb-0 fw-bold text-white">{{ number_format($soldeOkamiCollecteur) }} FC</h2>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 mt-3 mt-lg-0">
                    <div class="row text-center text-white">
                        <div class="col-6">
                            <div class="bg-white bg-opacity-10 rounded p-2">
                                <small class="d-block text-white-50">En attente paiement</small>
                                <strong class="fs-5">{{ number_format($paiementsEnAttenteOkami) }} FC</strong>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-white bg-opacity-10 rounded p-2">
                                <small class="d-block text-white-50">Disponible</small>
                                <strong class="fs-5">{{ number_format(max(0, $soldeOkamiCollecteur - $paiementsEnAttenteOkami)) }} FC</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques de la période -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card bg-success bg-opacity-10 border-0 h-100">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <small class="text-muted">Entrées (Versements)</small>
                            <h4 class="mb-0 fw-bold text-success">{{ number_format($totalPartOkamiVersements) }} FC</h4>
                            <small class="text-muted">1/6 des versements</small>
                        </div>
                        <i class="bi bi-arrow-down-circle fs-2 text-success opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info bg-opacity-10 border-0 h-100">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <small class="text-muted">Entrées (Lavages)</small>
                            <h4 class="mb-0 fw-bold text-info">{{ number_format($totalPartOkamiLavages) }} FC</h4>
                            <small class="text-muted">20% des lavages internes</small>
                        </div>
                        <i class="bi bi-droplet fs-2 text-info opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger bg-opacity-10 border-0 h-100">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <small class="text-muted">Sorties (Paiements)</small>
                            <h4 class="mb-0 fw-bold text-danger">{{ number_format($totalPaiementsOkami) }} FC</h4>
                            <small class="text-muted">Depuis caisse OKAMI</small>
                        </div>
                        <i class="bi bi-arrow-up-circle fs-2 text-danger opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning bg-opacity-10 border-0 h-100">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <small class="text-muted">Solde Net (Période)</small>
                            <h4 class="mb-0 fw-bold {{ $soldeNetPeriode >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $soldeNetPeriode >= 0 ? '+' : '' }}{{ number_format($soldeNetPeriode) }} FC
                            </h4>
                            <small class="text-muted">Entrées - Sorties</small>
                        </div>
                        <i class="bi bi-calculator fs-2 text-warning opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Statistiques par semaine -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold">
                        <i class="bi bi-bar-chart me-2 text-warning"></i>Part OKAMI par Semaine ({{ Carbon\Carbon::now()->format('F Y') }})
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>Semaine</th>
                                    <th class="text-end">Versements</th>
                                    <th class="text-end">Lavages</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($statsParSemaine as $stat)
                                <tr>
                                    <td>
                                        <span class="fw-medium">{{ $stat['semaine'] }}</span>
                                        <small class="text-muted d-block">{{ $stat['debut'] }} - {{ $stat['fin'] }}</small>
                                    </td>
                                    <td class="text-end text-success">{{ number_format($stat['versements']) }} FC</td>
                                    <td class="text-end text-info">{{ number_format($stat['lavages']) }} FC</td>
                                    <td class="text-end fw-bold text-warning">{{ number_format($stat['total']) }} FC</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">Aucune donnée</td>
                                </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="table-warning">
                                <tr>
                                    <th>Total</th>
                                    <th class="text-end">{{ number_format(collect($statsParSemaine)->sum('versements')) }} FC</th>
                                    <th class="text-end">{{ number_format(collect($statsParSemaine)->sum('lavages')) }} FC</th>
                                    <th class="text-end">{{ number_format(collect($statsParSemaine)->sum('total')) }} FC</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Derniers mouvements -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold">
                        <i class="bi bi-clock-history me-2 text-info"></i>Dernières Entrées OKAMI (Versements)
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="bg-light sticky-top">
                                <tr>
                                    <th>Date</th>
                                    <th>Motard</th>
                                    <th class="text-end">Part OKAMI</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($derniersVersements as $versement)
                                <tr>
                                    <td>
                                        <small>{{ $versement->date_versement->format('d/m/Y') }}</small>
                                    </td>
                                    <td>
                                        <span class="fw-medium">{{ $versement->motard->user->name ?? 'N/A' }}</span>
                                        <small class="text-muted d-block">{{ $versement->moto->plaque_immatriculation ?? '' }}</small>
                                    </td>
                                    <td class="text-end fw-bold text-warning">+{{ number_format($versement->part_okami) }} FC</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-3">Aucun versement</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Paiements depuis caisse OKAMI -->
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold">
                        <i class="bi bi-arrow-up-right-circle me-2 text-danger"></i>Paiements Effectués depuis Caisse OKAMI
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Bénéficiaire</th>
                                    <th>Motif</th>
                                    <th>Mode</th>
                                    <th class="text-end">Montant</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($derniersPaiements as $paiement)
                                <tr>
                                    <td>
                                        <span>{{ $paiement->date_paiement?->format('d/m/Y') }}</span>
                                        <small class="text-muted d-block">{{ $paiement->date_paiement?->format('H:i') }}</small>
                                    </td>
                                    <td>
                                        <span class="fw-medium">{{ $paiement->beneficiaire_nom ?? 'N/A' }}</span>
                                        @if($paiement->beneficiaire_telephone)
                                        <small class="text-muted d-block">{{ $paiement->beneficiaire_telephone }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ Str::limit($paiement->beneficiaire_motif, 40) ?? '-' }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark">
                                            {{ \App\Models\Payment::getModesPaiement()[$paiement->mode_paiement] ?? $paiement->mode_paiement }}
                                        </span>
                                    </td>
                                    <td class="text-end fw-bold text-danger">-{{ number_format($paiement->total_paye) }} FC</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                        Aucun paiement effectué depuis la caisse OKAMI
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Légende -->
    <div class="card mt-4 bg-light border-0">
        <div class="card-body py-2">
            <div class="row align-items-center">
                <div class="col-auto">
                    <small class="fw-bold text-muted"><i class="bi bi-info-circle me-1"></i>Sources des fonds OKAMI:</small>
                </div>
                <div class="col">
                    <span class="badge bg-success me-2">1/6 des versements</span>
                    <small class="text-muted me-3">Part OKAMI sur chaque versement hebdomadaire</small>

                    <span class="badge bg-info me-2">20% des lavages</span>
                    <small class="text-muted">Part OKAMI sur les lavages des motos du système</small>
                </div>
            </div>
        </div>
    </div>
</div>

