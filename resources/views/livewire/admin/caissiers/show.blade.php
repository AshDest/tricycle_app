<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-person-workspace me-2 text-success"></i>Détails du Caissier
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Tableau de bord</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.caissiers.index') }}">Caissiers</a></li>
                    <li class="breadcrumb-item active">{{ $caissier->user->name ?? 'Détails' }}</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.caissiers.edit', $caissier) }}" class="btn btn-primary">
                <i class="bi bi-pencil me-1"></i>Modifier
            </a>
            <a href="{{ route('admin.caissiers.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Retour
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Informations principales -->
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <div class="avatar avatar-xl bg-success bg-opacity-10 text-success rounded-circle mx-auto mb-3" style="width: 80px; height: 80px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-person-workspace fs-1"></i>
                    </div>
                    <h5 class="fw-bold mb-1">{{ $caissier->user->name ?? 'N/A' }}</h5>
                    <p class="text-muted mb-2">{{ $caissier->numero_identifiant ?? 'N/A' }}</p>
                    <span class="badge badge-soft-{{ $caissier->is_active ? 'success' : 'danger' }} px-3 py-2">
                        <i class="bi bi-circle-fill me-1" style="font-size: 8px;"></i>
                        {{ $caissier->is_active ? 'Actif' : 'Inactif' }}
                    </span>

                    <hr class="my-4">

                    <div class="text-start">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted"><i class="bi bi-shop me-2"></i>Point de collecte</span>
                            <span class="fw-medium">{{ $caissier->nom_point_collecte ?? 'N/A' }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted"><i class="bi bi-envelope me-2"></i>Email</span>
                            <span class="fw-medium">{{ $caissier->user->email ?? 'N/A' }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted"><i class="bi bi-telephone me-2"></i>Téléphone</span>
                            <span class="fw-medium">{{ $caissier->telephone ?? $caissier->user->phone ?? 'N/A' }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted"><i class="bi bi-geo-alt me-2"></i>Zone</span>
                            <span class="fw-medium">{{ $caissier->zone ?? 'Non définie' }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted"><i class="bi bi-pin-map me-2"></i>Adresse</span>
                            <span class="fw-medium text-end" style="max-width: 60%;">{{ $caissier->adresse ?? 'N/A' }}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted"><i class="bi bi-calendar me-2"></i>Créé le</span>
                            <span class="fw-medium">{{ $caissier->created_at?->format('d/m/Y') ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistiques et Solde -->
        <div class="col-lg-8">
            <div class="row g-4">
                <!-- Solde Actuel -->
                <div class="col-12">
                    <div class="card bg-success bg-opacity-10 border-0">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted mb-1">Solde Actuel en Caisse</p>
                                    <h3 class="fw-bold text-success mb-0">{{ number_format($caissier->solde_actuel ?? 0) }} FC</h3>
                                </div>
                                <div class="avatar avatar-lg bg-success bg-opacity-25 text-success rounded">
                                    <i class="bi bi-cash-stack fs-3"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="col-sm-6 col-md-4">
                    <div class="card border-0 bg-primary bg-opacity-10">
                        <div class="card-body text-center py-3">
                            <h4 class="fw-bold text-primary mb-1">{{ $stats['total_versements'] ?? 0 }}</h4>
                            <small class="text-muted">Total Versements</small>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-4">
                    <div class="card border-0 bg-info bg-opacity-10">
                        <div class="card-body text-center py-3">
                            <h4 class="fw-bold text-info mb-1">{{ $stats['versements_aujourdhui'] ?? 0 }}</h4>
                            <small class="text-muted">Aujourd'hui</small>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-4">
                    <div class="card border-0 bg-success bg-opacity-10">
                        <div class="card-body text-center py-3">
                            <h4 class="fw-bold text-success mb-1">{{ number_format($stats['montant_aujourdhui'] ?? 0) }}</h4>
                            <small class="text-muted">Encaissé Aujourd'hui (FC)</small>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-6">
                    <div class="card border-0 bg-warning bg-opacity-10">
                        <div class="card-body text-center py-3">
                            <h4 class="fw-bold text-warning mb-1">{{ number_format($stats['montant_mois'] ?? 0) }}</h4>
                            <small class="text-muted">Encaissé ce Mois (FC)</small>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-6">
                    <div class="card border-0 bg-secondary bg-opacity-10">
                        <div class="card-body text-center py-3">
                            <h4 class="fw-bold text-secondary mb-1">{{ number_format($stats['montant_total'] ?? 0) }}</h4>
                            <small class="text-muted">Total Encaissé (FC)</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Derniers Versements -->
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-cash-coin me-2 text-success"></i>Derniers Versements Reçus</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Date</th>
                                    <th>Motard</th>
                                    <th>Moto</th>
                                    <th class="text-end">Montant</th>
                                    <th class="text-center">Mode</th>
                                    <th class="text-center pe-4">Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($derniersVersements as $versement)
                                <tr>
                                    <td class="ps-4">
                                        <span class="fw-medium">{{ $versement->date_versement?->format('d/m/Y') ?? 'N/A' }}</span>
                                        <small class="d-block text-muted">{{ $versement->created_at?->format('H:i') }}</small>
                                    </td>
                                    <td>{{ $versement->motard->user->name ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge bg-light text-dark">
                                            {{ $versement->moto->plaque_immatriculation ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="text-end fw-semibold text-success">{{ number_format($versement->montant ?? 0) }} FC</td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark">
                                            {{ ucfirst($versement->mode_paiement ?? 'N/A') }}
                                        </span>
                                    </td>
                                    <td class="text-center pe-4">
                                        @php
                                            $statutColors = [
                                                'payé' => 'success',
                                                'partiellement_payé' => 'warning',
                                                'en_retard' => 'danger',
                                                'non_effectué' => 'secondary',
                                            ];
                                        @endphp
                                        <span class="badge badge-soft-{{ $statutColors[$versement->statut] ?? 'secondary' }}">
                                            {{ ucfirst(str_replace('_', ' ', $versement->statut ?? 'N/A')) }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        <i class="bi bi-inbox fs-3 d-block mb-2"></i>
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
