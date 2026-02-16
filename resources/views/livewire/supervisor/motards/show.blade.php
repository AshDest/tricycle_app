<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-person me-2 text-primary"></i>{{ $motard->user->name ?? 'Motard' }}
            </h4>
            <p class="text-muted mb-0">{{ $motard->numero_identifiant }} - {{ $motard->zone_affectation }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('supervisor.motards.edit', $motard) }}" class="btn btn-primary">
                <i class="bi bi-pencil me-1"></i>Modifier
            </a>
            <a href="{{ route('supervisor.motards.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Retour
            </a>
        </div>
    </div>

    <!-- Messages Flash -->
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        <!-- Informations principales -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body text-center py-4">
                    <div class="avatar bg-primary bg-opacity-10 text-primary rounded-circle mx-auto mb-3" style="width: 80px; height: 80px; font-size: 2rem; display: flex; align-items: center; justify-content: center;">
                        {{ strtoupper(substr($motard->user->name ?? 'M', 0, 1)) }}
                    </div>
                    <h5 class="mb-1">{{ $motard->user->name ?? 'N/A' }}</h5>
                    <p class="text-muted mb-3">{{ $motard->numero_identifiant }}</p>

                    <span class="badge badge-soft-{{ $motard->is_active ? 'success' : 'danger' }} fs-6 px-3 py-2">
                        {{ $motard->is_active ? 'Actif' : 'Inactif' }}
                    </span>

                    <hr class="my-4">

                    <div class="text-start">
                        <p class="mb-2"><i class="bi bi-envelope me-2 text-muted"></i>{{ $motard->user->email ?? 'N/A' }}</p>
                        <p class="mb-2"><i class="bi bi-telephone me-2 text-muted"></i>{{ $motard->telephone ?? 'N/A' }}</p>
                        <p class="mb-2"><i class="bi bi-geo-alt me-2 text-muted"></i>{{ $motard->zone_affectation ?? 'N/A' }}</p>
                        <p class="mb-0"><i class="bi bi-card-text me-2 text-muted"></i>Licence: {{ $motard->licence_numero ?? 'N/A' }}</p>
                    </div>

                    <hr class="my-4">

                    <button wire:click="toggleActive" class="btn btn-{{ $motard->is_active ? 'warning' : 'success' }} w-100">
                        <i class="bi bi-{{ $motard->is_active ? 'pause' : 'play' }} me-1"></i>
                        {{ $motard->is_active ? 'Désactiver' : 'Activer' }}
                    </button>
                </div>
            </div>

            <!-- Moto assignée -->
            <div class="card mt-4">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-bicycle me-2 text-info"></i>Moto Assignée</h6>
                </div>
                <div class="card-body">
                    @if($motard->motoActuelle)
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar bg-info bg-opacity-10 text-info rounded" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-bicycle fs-4"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">{{ $motard->motoActuelle->plaque_immatriculation }}</h6>
                                <small class="text-muted">{{ $motard->motoActuelle->marque }} {{ $motard->motoActuelle->modele }}</small>
                            </div>
                        </div>
                    @else
                        <p class="text-muted text-center mb-0">
                            <i class="bi bi-exclamation-circle me-1"></i>Aucune moto assignée
                        </p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Statistiques et historique -->
        <div class="col-lg-8">
            <!-- Statistiques -->
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3">
                    <div class="card bg-primary bg-opacity-10 border-0">
                        <div class="card-body py-3 text-center">
                            <h4 class="mb-0 fw-bold text-primary">{{ $stats['totalVersements'] }}</h4>
                            <small class="text-muted">Total Versements</small>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card bg-success bg-opacity-10 border-0">
                        <div class="card-body py-3 text-center">
                            <h4 class="mb-0 fw-bold text-success">{{ number_format($stats['totalMontant']) }}</h4>
                            <small class="text-muted">Total (FC)</small>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card bg-info bg-opacity-10 border-0">
                        <div class="card-body py-3 text-center">
                            <h4 class="mb-0 fw-bold text-info">{{ $stats['versementsPayes'] }}</h4>
                            <small class="text-muted">Payés</small>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card bg-danger bg-opacity-10 border-0">
                        <div class="card-body py-3 text-center">
                            <h4 class="mb-0 fw-bold text-danger">{{ $stats['versementsEnRetard'] }}</h4>
                            <small class="text-muted">En Retard</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Derniers versements -->
            <div class="card">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-clock-history me-2 text-primary"></i>Derniers Versements</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Date</th>
                                    <th>Montant</th>
                                    <th>Mode</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($derniersVersements as $versement)
                                <tr>
                                    <td class="ps-4">
                                        <span class="fw-medium">{{ $versement->date_versement?->format('d/m/Y') }}</span>
                                        <small class="text-muted d-block">{{ $versement->created_at->format('H:i') }}</small>
                                    </td>
                                    <td class="fw-semibold">{{ number_format($versement->montant) }} FC</td>
                                    <td>
                                        <span class="badge bg-light text-dark">{{ ucfirst($versement->mode_paiement ?? 'N/A') }}</span>
                                    </td>
                                    <td>
                                        @php
                                            $statutColors = [
                                                'paye' => 'success',
                                                'en_attente' => 'warning',
                                                'en_retard' => 'danger',
                                                'partiel' => 'info',
                                            ];
                                        @endphp
                                        <span class="badge badge-soft-{{ $statutColors[$versement->statut] ?? 'secondary' }}">
                                            {{ ucfirst(str_replace('_', ' ', $versement->statut ?? 'N/A')) }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">
                                        Aucun versement enregistré
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
</div>

