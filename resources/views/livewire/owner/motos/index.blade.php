<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-bicycle me-2 text-primary"></i>Mes Motos-Tricycles
            </h4>
            <p class="text-muted mb-0">Gestion de votre flotte</p>
        </div>
        <button wire:click="exportPdf" class="btn btn-danger">
            <i class="bi bi-file-pdf me-1"></i>Exporter PDF
        </button>
    </div>

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card bg-primary bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="fw-bold text-primary mb-1">{{ $totalMotos ?? 0 }}</h4>
                    <small class="text-muted">Total motos</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card bg-success bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="fw-bold text-success mb-1">{{ $motosActives ?? 0 }}</h4>
                    <small class="text-muted">En activité</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card bg-warning bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="fw-bold text-warning mb-1">{{ $motosEnMaintenance ?? 0 }}</h4>
                    <small class="text-muted">En maintenance</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card bg-info bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="fw-bold text-info mb-1">{{ number_format($revenusTotal ?? 0) }} FC</h4>
                    <small class="text-muted">Revenus ce mois</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des motos -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Moto</th>
                            <th>Motard assigné</th>
                            <th>Tarif journalier</th>
                            <th>Revenus ce mois</th>
                            <th>Statut</th>
                            <th class="text-end pe-4">Détails</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($motos ?? [] as $moto)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="avatar avatar-sm bg-primary bg-opacity-10 text-primary rounded">
                                        <i class="bi bi-bicycle"></i>
                                    </div>
                                    <div>
                                        <span class="fw-medium d-block">{{ $moto->plaque_immatriculation ?? 'N/A' }}</span>
                                        <small class="text-muted">{{ $moto->numero_matricule ?? '' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($moto->motard)
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar avatar-xs bg-success bg-opacity-10 text-success rounded-circle">
                                        {{ strtoupper(substr($moto->motard->user->name ?? 'M', 0, 1)) }}
                                    </div>
                                    <span>{{ $moto->motard->user->name ?? 'N/A' }}</span>
                                </div>
                                @else
                                <span class="text-muted">Non assignée</span>
                                @endif
                            </td>
                            <td class="fw-semibold">{{ number_format($moto->montant_journalier_attendu ?? 0) }} FC</td>
                            <td class="text-success fw-semibold">{{ number_format($moto->revenus_mois ?? 0) }} FC</td>
                            <td>
                                @php
                                    $statutColors = [
                                        'actif' => 'success',
                                        'suspendu' => 'danger',
                                        'maintenance' => 'warning',
                                    ];
                                @endphp
                                <span class="badge badge-soft-{{ $statutColors[$moto->statut] ?? 'secondary' }}">
                                    {{ ucfirst($moto->statut ?? 'N/A') }}
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <button wire:click="voirDetails({{ $moto->id }})" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-bicycle fs-1 d-block mb-3"></i>
                                <p class="mb-0">Aucune moto enregistrée</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if(($motos ?? collect())->hasPages())
        <div class="card-footer bg-light">
            {{ $motos->links() }}
        </div>
        @endif
    </div>

    <!-- Modal Détails Moto -->
    @if($showModal && $motoSelectionnee)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-bicycle me-2"></i>Détails de la Moto
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="fermerModal"></button>
                </div>
                <div class="modal-body">
                    <!-- Info Moto -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="card bg-light border-0">
                                <div class="card-body">
                                    <h6 class="fw-bold mb-3"><i class="bi bi-info-circle me-2"></i>Informations</h6>
                                    <table class="table table-sm table-borderless mb-0">
                                        <tr>
                                            <td class="text-muted">Plaque</td>
                                            <td class="fw-bold">{{ $motoSelectionnee->plaque_immatriculation ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Matricule</td>
                                            <td>{{ $motoSelectionnee->numero_matricule ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Tarif journalier</td>
                                            <td class="fw-bold text-primary">{{ number_format($motoSelectionnee->montant_journalier_attendu ?? 0) }} FC</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Statut</td>
                                            <td>
                                                <span class="badge badge-soft-{{ $motoSelectionnee->statut === 'actif' ? 'success' : 'secondary' }}">
                                                    {{ ucfirst($motoSelectionnee->statut ?? 'N/A') }}
                                                </span>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light border-0">
                                <div class="card-body">
                                    <h6 class="fw-bold mb-3"><i class="bi bi-person me-2"></i>Motard Assigné</h6>
                                    @if($motoSelectionnee->motard)
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="avatar avatar-md bg-success bg-opacity-10 text-success rounded-circle">
                                            {{ strtoupper(substr($motoSelectionnee->motard->user->name ?? 'M', 0, 1)) }}
                                        </div>
                                        <div>
                                            <span class="fw-bold d-block">{{ $motoSelectionnee->motard->user->name ?? 'N/A' }}</span>
                                            <small class="text-muted">{{ $motoSelectionnee->motard->numero_identifiant ?? '' }}</small>
                                        </div>
                                    </div>
                                    @else
                                    <p class="text-muted mb-0">Aucun motard assigné</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Stats -->
                    <div class="row g-3 mb-4">
                        <div class="col-6 col-md-3">
                            <div class="text-center p-3 bg-success bg-opacity-10 rounded">
                                <div class="fw-bold text-success">{{ number_format($statsMoto['versementsMois'] ?? 0) }} FC</div>
                                <small class="text-muted">Ce mois</small>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="text-center p-3 bg-primary bg-opacity-10 rounded">
                                <div class="fw-bold text-primary">{{ number_format($statsMoto['totalVersements'] ?? 0) }} FC</div>
                                <small class="text-muted">Total</small>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="text-center p-3 bg-info bg-opacity-10 rounded">
                                <div class="fw-bold text-info">{{ $statsMoto['nbVersements'] ?? 0 }}</div>
                                <small class="text-muted">Versements</small>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="text-center p-3 bg-{{ ($statsMoto['arrieres'] ?? 0) > 0 ? 'danger' : 'success' }} bg-opacity-10 rounded">
                                <div class="fw-bold text-{{ ($statsMoto['arrieres'] ?? 0) > 0 ? 'danger' : 'success' }}">{{ number_format($statsMoto['arrieres'] ?? 0) }} FC</div>
                                <small class="text-muted">Arriérés</small>
                            </div>
                        </div>
                    </div>

                    <!-- Derniers versements -->
                    <h6 class="fw-bold mb-3"><i class="bi bi-clock-history me-2"></i>Derniers Versements</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="bg-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Motard</th>
                                    <th class="text-end">Montant</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($versementsMoto ?? [] as $v)
                                <tr>
                                    <td>{{ $v->date_versement?->format('d/m/Y') ?? 'N/A' }}</td>
                                    <td>{{ $v->motard->user->name ?? 'N/A' }}</td>
                                    <td class="text-end fw-semibold">{{ number_format($v->montant ?? 0) }} FC</td>
                                    <td>
                                        @php
                                            $colors = ['payé' => 'success', 'partiellement_payé' => 'warning', 'en_retard' => 'danger'];
                                        @endphp
                                        <span class="badge badge-soft-{{ $colors[$v->statut] ?? 'secondary' }}">
                                            {{ ucfirst(str_replace('_', ' ', $v->statut ?? 'N/A')) }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">Aucun versement</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="fermerModal">
                        <i class="bi bi-x-lg me-1"></i>Fermer
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
