<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-wallet2 me-2 text-primary"></i>Solde & Dépenses - {{ $collecteur->user->name ?? 'Collecteur' }}
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Tableau de bord</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.collecteurs.index') }}">Collecteurs</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.collecteurs.show', $collecteur) }}">{{ $collecteur->user->name ?? '' }}</a></li>
                    <li class="breadcrumb-item active">Solde & Dépenses</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <button wire:click="exportPdf" class="btn btn-danger">
                <i class="bi bi-file-pdf me-1"></i>Exporter PDF
            </button>
            <a href="{{ route('admin.collecteurs.show', $collecteur) }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Retour
            </a>
        </div>
    </div>

    <!-- Solde Card -->
    <div class="row g-3 mb-4">
        <div class="col-lg-6 mx-auto">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 opacity-75 small">Solde Caisse Total</p>
                            <h3 class="fw-bold mb-0">{{ number_format($soldeCaisse) }} FC</h3>
                        </div>
                        <div class="bg-white bg-opacity-25 rounded-3 p-3">
                            <i class="bi bi-wallet2 fs-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres Période -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Période rapide</label>
                    <select wire:model.live="periodeFilter" class="form-select">
                        <option value="jour">Aujourd'hui</option>
                        <option value="semaine">Cette semaine</option>
                        <option value="mois">Ce mois</option>
                        <option value="annee">Cette année</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Du</label>
                    <input type="date" wire:model.live="dateDebut" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Au</label>
                    <input type="date" wire:model.live="dateFin" class="form-control">
                </div>
                <div class="col-md-3">
                    <div class="d-flex gap-2">
                        <span class="badge bg-success bg-opacity-10 text-success p-2 flex-fill text-center">
                            <i class="bi bi-arrow-down-circle me-1"></i>Entrées: {{ number_format($totalCollectePeriode + $totalTransactionsRetrait) }} FC
                        </span>
                        <span class="badge bg-danger bg-opacity-10 text-danger p-2 flex-fill text-center">
                            <i class="bi bi-arrow-up-circle me-1"></i>Sorties: {{ number_format($totalPaiementsPeriode + $totalTransactionsEnvoi) }} FC
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats de la période -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 bg-primary bg-opacity-10">
                <div class="card-body text-center py-3">
                    <h4 class="fw-bold text-primary mb-0">{{ number_format($totalCollectePeriode) }} FC</h4>
                    <small class="text-muted">Collectes ({{ $nombreCollectes }})</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 bg-danger bg-opacity-10">
                <div class="card-body text-center py-3">
                    <h4 class="fw-bold text-danger mb-0">{{ number_format($totalPaiementsPeriode) }} FC</h4>
                    <small class="text-muted">Paiements ({{ $nombrePaiements }})</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 bg-warning bg-opacity-10">
                <div class="card-body text-center py-3">
                    <h4 class="fw-bold text-warning mb-0">{{ number_format($totalTransactionsEnvoi) }} FC</h4>
                    <small class="text-muted">Envois Mobile</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 bg-info bg-opacity-10">
                <div class="card-body text-center py-3">
                    <h4 class="fw-bold text-info mb-0">
                        @php $tauxUsdStatCard = \App\Models\SystemSetting::getTauxUsdCdf(); @endphp
                        {{ $tauxUsdStatCard > 0 ? number_format($totalCommissions / $tauxUsdStatCard, 2) : '0.00' }} $
                    </h4>
                    <small class="text-muted">Commissions totales</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation onglets -->
    <ul class="nav nav-tabs mb-0">
        <li class="nav-item">
            <button wire:click="setOnglet('resume')" class="nav-link {{ $onglet === 'resume' ? 'active' : '' }}">
                <i class="bi bi-journal-text me-1"></i>Journal Quotidien
            </button>
        </li>
        <li class="nav-item">
            <button wire:click="setOnglet('collectes')" class="nav-link {{ $onglet === 'collectes' ? 'active' : '' }}">
                <i class="bi bi-cash-stack me-1"></i>Collectes
            </button>
        </li>
        <li class="nav-item">
            <button wire:click="setOnglet('paiements')" class="nav-link {{ $onglet === 'paiements' ? 'active' : '' }}">
                <i class="bi bi-credit-card me-1"></i>Paiements
            </button>
        </li>
        <li class="nav-item">
            <button wire:click="setOnglet('transactions')" class="nav-link {{ $onglet === 'transactions' ? 'active' : '' }}">
                <i class="bi bi-phone me-1"></i>Transactions Mobile
            </button>
        </li>
        <li class="nav-item">
            <button wire:click="setOnglet('commissions')" class="nav-link {{ $onglet === 'commissions' ? 'active' : '' }}">
                <i class="bi bi-percent me-1"></i>Commissions
            </button>
        </li>
    </ul>

    <!-- Contenu onglets -->
    <div class="card border-top-0 rounded-top-0">
        <div class="card-body p-0">
            @if($onglet === 'resume')
                {{-- Journal quotidien --}}
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Date</th>
                                <th class="text-end text-success">Collectes (Entrées)</th>
                                <th class="text-end text-danger">Paiements (Sorties)</th>
                                <th class="text-end text-warning">Envois Mobile</th>
                                <th class="text-end text-info">Retraits Mobile</th>
                                <th class="text-end">Total Entrées</th>
                                <th class="text-end">Total Sorties</th>
                                <th class="text-end pe-4">Solde du Jour</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($journalQuotidien as $jour)
                            <tr>
                                <td class="ps-4">
                                    <span class="fw-medium">{{ \Carbon\Carbon::parse($jour['date'])->format('d/m/Y') }}</span>
                                    <br><small class="text-muted">{{ $jour['date_formatee'] }}</small>
                                </td>
                                <td class="text-end text-success fw-semibold">
                                    @if($jour['collectes'] > 0)
                                        +{{ number_format($jour['collectes']) }} FC
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-end text-danger fw-semibold">
                                    @if($jour['paiements'] > 0)
                                        -{{ number_format($jour['paiements']) }} FC
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-end text-warning">
                                    @if($jour['tx_envoi'] > 0)
                                        -{{ number_format($jour['tx_envoi']) }} FC
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-end text-info">
                                    @if($jour['tx_retrait'] > 0)
                                        +{{ number_format($jour['tx_retrait']) }} FC
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-end fw-semibold text-success">{{ number_format($jour['total_entrees']) }} FC</td>
                                <td class="text-end fw-semibold text-danger">{{ number_format($jour['total_sorties']) }} FC</td>
                                <td class="text-end pe-4">
                                    <span class="badge {{ $jour['solde_jour'] >= 0 ? 'bg-success' : 'bg-danger' }} bg-opacity-10 {{ $jour['solde_jour'] >= 0 ? 'text-success' : 'text-danger' }} px-3 py-2">
                                        {{ $jour['solde_jour'] >= 0 ? '+' : '' }}{{ number_format($jour['solde_jour']) }} FC
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-journal-x fs-1 d-block mb-3"></i>
                                    <p class="mb-0">Aucun mouvement sur cette période</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                        @if(count($journalQuotidien) > 0)
                        <tfoot class="bg-light fw-bold">
                            <tr>
                                <td class="ps-4">TOTAL</td>
                                <td class="text-end text-success">{{ number_format(collect($journalQuotidien)->sum('collectes')) }} FC</td>
                                <td class="text-end text-danger">{{ number_format(collect($journalQuotidien)->sum('paiements')) }} FC</td>
                                <td class="text-end text-warning">{{ number_format(collect($journalQuotidien)->sum('tx_envoi')) }} FC</td>
                                <td class="text-end text-info">{{ number_format(collect($journalQuotidien)->sum('tx_retrait')) }} FC</td>
                                <td class="text-end text-success">{{ number_format(collect($journalQuotidien)->sum('total_entrees')) }} FC</td>
                                <td class="text-end text-danger">{{ number_format(collect($journalQuotidien)->sum('total_sorties')) }} FC</td>
                                <td class="text-end pe-4">
                                    @php $soldePeriode = collect($journalQuotidien)->sum('solde_jour'); @endphp
                                    <span class="badge {{ $soldePeriode >= 0 ? 'bg-success' : 'bg-danger' }} px-3 py-2">
                                        {{ $soldePeriode >= 0 ? '+' : '' }}{{ number_format($soldePeriode) }} FC
                                    </span>
                                </td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>

            @elseif($onglet === 'collectes')
                {{-- Détail des collectes --}}
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Date</th>
                                <th>Caissier</th>
                                <th>Zone</th>
                                <th class="text-end">Montant Attendu</th>
                                <th class="text-end">Montant Collecté</th>
                                <th class="text-center pe-4">Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($collectes ?? [] as $collecte)
                            <tr>
                                <td class="ps-4">
                                    <span class="fw-medium">{{ $collecte->created_at?->format('d/m/Y H:i') }}</span>
                                </td>
                                <td>{{ $collecte->caissier?->user?->name ?? 'N/A' }}</td>
                                <td><span class="badge bg-light text-dark">{{ $collecte->tournee?->zone ?? 'N/A' }}</span></td>
                                <td class="text-end">{{ number_format($collecte->montant_attendu ?? 0) }} FC</td>
                                <td class="text-end fw-semibold text-success">{{ number_format($collecte->montant_collecte ?? 0) }} FC</td>
                                <td class="text-center pe-4">
                                    @php
                                        $colors = ['reussie' => 'success', 'partielle' => 'warning', 'echouee' => 'danger', 'en_litige' => 'info'];
                                    @endphp
                                    <span class="badge badge-soft-{{ $colors[$collecte->statut] ?? 'secondary' }}">
                                        {{ ucfirst(str_replace('_', ' ', $collecte->statut)) }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                    <p class="mb-0">Aucune collecte sur cette période</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if(($collectes ?? collect()) instanceof \Illuminate\Pagination\LengthAwarePaginator && $collectes->hasPages())
                <div class="card-footer bg-light">
                    {{ $collectes->links() }}
                </div>
                @endif

            @elseif($onglet === 'paiements')
                {{-- Détail des paiements effectués --}}
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Date</th>
                                <th>Bénéficiaire</th>
                                <th>Source</th>
                                <th>Mode</th>
                                <th class="text-end">Montant Dû</th>
                                <th class="text-end">Montant Payé</th>
                                <th class="text-center pe-4">Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($paiements ?? [] as $paiement)
                            <tr>
                                <td class="ps-4">
                                    <span class="fw-medium">{{ $paiement->date_paiement?->format('d/m/Y') }}</span>
                                </td>
                                <td>
                                    @if($paiement->source_caisse === 'proprietaire')
                                        {{ $paiement->proprietaire?->user?->name ?? 'N/A' }}
                                    @else
                                        {{ $paiement->beneficiaire_nom ?? 'N/A' }}
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $sourceColors = ['proprietaire' => 'success', 'okami' => 'warning', 'lavage' => 'info'];
                                    @endphp
                                    <span class="badge badge-soft-{{ $sourceColors[$paiement->source_caisse] ?? 'secondary' }}">
                                        {{ ucfirst($paiement->source_caisse ?? 'N/A') }}
                                    </span>
                                </td>
                                <td>{{ strtoupper(str_replace('_', ' ', $paiement->mode_paiement ?? 'N/A')) }}</td>
                                <td class="text-end">{{ number_format($paiement->total_du ?? 0) }} FC</td>
                                <td class="text-end fw-semibold text-danger">-{{ number_format($paiement->total_paye ?? 0) }} FC</td>
                                <td class="text-center pe-4">
                                    <span class="badge badge-soft-success">Payé</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                    <p class="mb-0">Aucun paiement sur cette période</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if(($paiements ?? collect()) instanceof \Illuminate\Pagination\LengthAwarePaginator && $paiements->hasPages())
                <div class="card-footer bg-light">
                    {{ $paiements->links() }}
                </div>
                @endif

            @elseif($onglet === 'transactions')
                {{-- Transactions Mobile Money --}}
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">N° Transaction</th>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Opérateur</th>
                                <th>Bénéficiaire</th>
                                <th class="text-end">Montant</th>
                                <th class="text-end">Frais</th>
                                <th class="text-center pe-4">Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions ?? [] as $tx)
                            <tr>
                                <td class="ps-4"><code>{{ $tx->numero_transaction }}</code></td>
                                <td>{{ $tx->date_transaction?->format('d/m/Y H:i') }}</td>
                                <td>
                                    <span class="badge badge-soft-{{ $tx->type === 'envoi' ? 'danger' : 'success' }}">
                                        <i class="bi bi-arrow-{{ $tx->type === 'envoi' ? 'up' : 'down' }}-circle me-1"></i>
                                        {{ ucfirst($tx->type) }}
                                    </span>
                                </td>
                                <td>{{ $tx->operateur_label }}</td>
                                <td>{{ $tx->nom_beneficiaire ?? 'N/A' }}</td>
                                <td class="text-end fw-semibold {{ $tx->type === 'envoi' ? 'text-danger' : 'text-success' }}">
                                    {{ $tx->type === 'envoi' ? '-' : '+' }}{{ number_format($tx->montant ?? 0) }} FC
                                </td>
                                <td class="text-end text-muted">{{ number_format($tx->frais ?? 0) }} FC</td>
                                <td class="text-center pe-4">
                                    @php
                                        $statutColors = ['en_attente' => 'warning', 'complete' => 'success', 'echoue' => 'danger', 'annule' => 'secondary'];
                                    @endphp
                                    <span class="badge badge-soft-{{ $statutColors[$tx->statut] ?? 'secondary' }}">
                                        {{ $tx->statut_label }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                    <p class="mb-0">Aucune transaction mobile sur cette période</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if(($transactions ?? collect()) instanceof \Illuminate\Pagination\LengthAwarePaginator && $transactions->hasPages())
                <div class="card-footer bg-light">
                    {{ $transactions->links() }}
                </div>
                @endif

            @elseif($onglet === 'commissions')
                {{-- Commissions Mobile Money --}}
                @php $tauxUsdCom = \App\Models\SystemSetting::getTauxUsdCdf(); @endphp
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Référence</th>
                                <th>Période</th>
                                <th class="text-end">Montant Total</th>
                                <th class="text-end">Part LATEM (70%)</th>
                                <th class="text-end">Part OKAMI (30%)</th>
                                <th class="text-center pe-4">Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($commissions ?? [] as $commission)
                            <tr>
                                <td class="ps-4"><code>{{ $commission->numero_reference }}</code></td>
                                <td>
                                    @php
                                        $moisNoms = ['', 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
                                    @endphp
                                    {{ $moisNoms[$commission->mois] ?? '' }} {{ $commission->annee }}
                                </td>
                                <td class="text-end fw-semibold">{{ $tauxUsdCom > 0 ? number_format(($commission->montant_total ?? 0) / $tauxUsdCom, 2) : '0.00' }} $</td>
                                <td class="text-end text-primary">{{ $tauxUsdCom > 0 ? number_format(($commission->part_nth ?? 0) / $tauxUsdCom, 2) : '0.00' }} $</td>
                                <td class="text-end text-warning">{{ $tauxUsdCom > 0 ? number_format(($commission->part_okami ?? 0) / $tauxUsdCom, 2) : '0.00' }} $</td>
                                <td class="text-center pe-4">
                                    @php
                                        $statutColors = ['en_attente' => 'warning', 'valide' => 'success', 'rejete' => 'danger'];
                                    @endphp
                                    <span class="badge badge-soft-{{ $statutColors[$commission->statut] ?? 'secondary' }}">
                                        {{ ucfirst(str_replace('_', ' ', $commission->statut)) }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                    <p class="mb-0">Aucune commission enregistrée</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if(($commissions ?? collect()) instanceof \Illuminate\Pagination\LengthAwarePaginator && $commissions->hasPages())
                <div class="card-footer bg-light">
                    {{ $commissions->links() }}
                </div>
                @endif
            @endif
        </div>
    </div>
</div>

