<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-building me-2 text-warning"></i>Détails du Propriétaire
            </h4>
            <p class="text-muted mb-0">{{ $proprietaire->user->name ?? 'N/A' }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.proprietaires.edit', $proprietaire) }}" class="btn btn-outline-primary">
                <i class="bi bi-pencil me-1"></i>Modifier
            </a>
            <a href="{{ route('admin.proprietaires.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Retour
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small text-uppercase fw-semibold mb-2">Total Motos</p>
                            <h3 class="fw-bold text-primary mb-0">{{ $totalMotos }}</h3>
                            <small class="text-muted">{{ $motosActives }} active(s)</small>
                        </div>
                        <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                            <i class="bi bi-bicycle text-primary fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small text-uppercase fw-semibold mb-2">Total Revenus</p>
                            <h3 class="fw-bold text-success mb-0">{{ number_format($totalRevenue) }}</h3>
                            <small class="text-muted">FC</small>
                        </div>
                        <div class="bg-success bg-opacity-10 rounded-circle p-3">
                            <i class="bi bi-cash-stack text-success fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small text-uppercase fw-semibold mb-2">Total Payé</p>
                            <h3 class="fw-bold text-info mb-0">{{ number_format($totalPaye) }}</h3>
                            <small class="text-muted">FC ({{ $totalPayments }} paiements)</small>
                        </div>
                        <div class="bg-info bg-opacity-10 rounded-circle p-3">
                            <i class="bi bi-wallet2 text-info fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small text-uppercase fw-semibold mb-2">Coût Maintenance</p>
                            <h3 class="fw-bold text-warning mb-0">{{ number_format($coutMaintenance) }}</h3>
                            <small class="text-muted">FC</small>
                        </div>
                        <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                            <i class="bi bi-tools text-warning fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Informations du propriétaire -->
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-person me-2 text-primary"></i>Informations</h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <div class="avatar avatar-xl bg-warning bg-opacity-10 text-warning rounded-circle mx-auto mb-3" style="width: 80px; height: 80px; font-size: 2rem; display: flex; align-items: center; justify-content: center;">
                            {{ strtoupper(substr($proprietaire->user->name ?? 'P', 0, 1)) }}
                        </div>
                        <h5 class="fw-bold mb-1">{{ $proprietaire->user->name ?? 'N/A' }}</h5>
                        <span class="badge bg-{{ $proprietaire->is_active ? 'success' : 'danger' }}">
                            {{ $proprietaire->is_active ? 'Actif' : 'Inactif' }}
                        </span>
                    </div>

                    <ul class="list-unstyled">
                        @if($proprietaire->raison_sociale)
                        <li class="d-flex align-items-start gap-3 mb-3">
                            <i class="bi bi-building text-muted"></i>
                            <div>
                                <small class="text-muted d-block">Raison Sociale</small>
                                <span>{{ $proprietaire->raison_sociale }}</span>
                            </div>
                        </li>
                        @endif

                        <li class="d-flex align-items-start gap-3 mb-3">
                            <i class="bi bi-envelope text-muted"></i>
                            <div>
                                <small class="text-muted d-block">Email</small>
                                <span>{{ $proprietaire->user->email ?? 'N/A' }}</span>
                            </div>
                        </li>

                        <li class="d-flex align-items-start gap-3 mb-3">
                            <i class="bi bi-telephone text-muted"></i>
                            <div>
                                <small class="text-muted d-block">Téléphone</small>
                                <span>{{ $proprietaire->telephone ?? 'N/A' }}</span>
                            </div>
                        </li>

                        <li class="d-flex align-items-start gap-3 mb-3">
                            <i class="bi bi-geo-alt text-muted"></i>
                            <div>
                                <small class="text-muted d-block">Adresse</small>
                                <span>{{ $proprietaire->adresse ?? 'N/A' }}</span>
                            </div>
                        </li>

                        <li class="d-flex align-items-start gap-3 mb-3">
                            <i class="bi bi-calendar text-muted"></i>
                            <div>
                                <small class="text-muted d-block">Inscrit le</small>
                                <span>{{ $proprietaire->created_at?->format('d/m/Y') ?? 'N/A' }}</span>
                            </div>
                        </li>
                    </ul>

                    <!-- Comptes de paiement -->
                    <hr>
                    <h6 class="fw-bold mb-3"><i class="bi bi-credit-card me-2"></i>Comptes de Paiement</h6>
                    <ul class="list-unstyled small">
                        @if($proprietaire->numero_compte_mpesa)
                        <li class="mb-2">
                            <span class="badge bg-success me-2">M-Pesa</span>
                            {{ $proprietaire->numero_compte_mpesa }}
                        </li>
                        @endif
                        @if($proprietaire->numero_compte_airtel)
                        <li class="mb-2">
                            <span class="badge bg-danger me-2">Airtel</span>
                            {{ $proprietaire->numero_compte_airtel }}
                        </li>
                        @endif
                        @if($proprietaire->numero_compte_orange)
                        <li class="mb-2">
                            <span class="badge bg-warning text-dark me-2">Orange</span>
                            {{ $proprietaire->numero_compte_orange }}
                        </li>
                        @endif
                        @if($proprietaire->numero_compte_bancaire)
                        <li class="mb-2">
                            <span class="badge bg-primary me-2">Banque</span>
                            {{ $proprietaire->numero_compte_bancaire }}
                            @if($proprietaire->banque_nom)
                            <small class="text-muted">({{ $proprietaire->banque_nom }})</small>
                            @endif
                        </li>
                        @endif
                        @if(!$proprietaire->numero_compte_mpesa && !$proprietaire->numero_compte_airtel && !$proprietaire->numero_compte_orange && !$proprietaire->numero_compte_bancaire)
                        <li class="text-muted">Aucun compte configuré</li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>

        <!-- Motos du propriétaire -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-bicycle me-2 text-info"></i>Motos ({{ $motos->count() }})</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Plaque</th>
                                    <th>Motard</th>
                                    <th>Statut</th>
                                    <th>Contrat</th>
                                    <th class="text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($motos as $moto)
                                <tr>
                                    <td class="ps-4">
                                        <span class="fw-medium">{{ $moto->plaque_immatriculation }}</span>
                                        @if($moto->numero_chassis)
                                        <small class="text-muted d-block">{{ $moto->numero_chassis }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($moto->motard)
                                        <span>{{ $moto->motard->user->name ?? 'N/A' }}</span>
                                        @else
                                        <span class="text-muted">Non assignée</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $moto->statut === 'actif' ? 'success' : ($moto->statut === 'en_maintenance' ? 'warning' : 'secondary') }}">
                                            {{ ucfirst(str_replace('_', ' ', $moto->statut)) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($moto->contrat_fin)
                                            @if($moto->contrat_fin->isPast())
                                            <span class="badge bg-danger">Expiré</span>
                                            @elseif($moto->contrat_fin->diffInDays(now()) <= 30)
                                            <span class="badge bg-warning text-dark">Expire bientôt</span>
                                            @else
                                            <span class="badge bg-success">Valide</span>
                                            @endif
                                            <small class="d-block text-muted">{{ $moto->contrat_fin->format('d/m/Y') }}</small>
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4">
                                        <a href="{{ route('admin.motos.show', $moto) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        <i class="bi bi-bicycle fs-1 d-block mb-2"></i>
                                        Aucune moto enregistrée
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Paiements récents -->
            <div class="card">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-cash-coin me-2 text-success"></i>Paiements Récents</h6>
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
                                @forelse($recentPayments as $payment)
                                <tr>
                                    <td class="ps-4">
                                        {{ $payment->created_at?->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="fw-semibold">{{ number_format($payment->total_paye ?? $payment->total_du ?? 0) }} FC</td>
                                    <td>
                                        @php
                                            $modeLabels = [
                                                'cash' => 'Cash',
                                                'mpesa' => 'M-Pesa',
                                                'airtel_money' => 'Airtel Money',
                                                'orange_money' => 'Orange Money',
                                                'virement_bancaire' => 'Virement',
                                            ];
                                        @endphp
                                        {{ $modeLabels[$payment->mode_paiement] ?? ucfirst($payment->mode_paiement ?? 'N/A') }}
                                    </td>
                                    <td>
                                        @php
                                            $statusColors = [
                                                'paye' => 'success',
                                                'en_attente' => 'warning',
                                                'annule' => 'danger',
                                                'approuve' => 'info',
                                            ];
                                        @endphp
                                        <span class="badge bg-{{ $statusColors[$payment->statut] ?? 'secondary' }}">
                                            {{ ucfirst(str_replace('_', ' ', $payment->statut ?? 'N/A')) }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">
                                        <i class="bi bi-cash-coin fs-1 d-block mb-2"></i>
                                        Aucun paiement enregistré
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

