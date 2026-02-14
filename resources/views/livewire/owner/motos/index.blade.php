<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-bicycle me-2 text-primary"></i>Mes Motos-Tricycles
            </h4>
            <p class="text-muted mb-0">Gestion de votre flotte</p>
        </div>
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
</div>
