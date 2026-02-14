<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-person-badge me-2 text-success"></i>Mon Espace Motard
            </h4>
            <p class="text-muted mb-0">Bienvenue, {{ auth()->user()->name }}</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-light text-dark px-3 py-2">
                <i class="bi bi-calendar3 me-2"></i>{{ now()->translatedFormat('l d F Y') }}
            </span>
        </div>
    </div>

    <!-- Alert if behind -->
    @if(($joursEnRetard ?? 0) > 0)
    <div class="alert alert-warning d-flex align-items-center gap-3 mb-4" role="alert">
        <i class="bi bi-exclamation-triangle-fill fs-4"></i>
        <div>
            <strong>Attention !</strong> Vous avez {{ $joursEnRetard }} jour(s) de retard de paiement.
            Veuillez régulariser votre situation.
        </div>
    </div>
    @endif

    <!-- Stats Row -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card h-100">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small text-uppercase fw-semibold mb-2">Versement Aujourd'hui</p>
                        <h3 class="fw-bold {{ ($versementAujourdhui ?? 0) > 0 ? 'text-success' : 'text-danger' }} mb-1">
                            {{ number_format($versementAujourdhui ?? 0) }}
                        </h3>
                        <small class="text-muted">FCFA sur {{ number_format($montantAttendu ?? 0) }} attendu</small>
                    </div>
                    <div class="stat-icon bg-{{ ($versementAujourdhui ?? 0) > 0 ? 'success' : 'danger' }} bg-opacity-10 text-{{ ($versementAujourdhui ?? 0) > 0 ? 'success' : 'danger' }}">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card h-100">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small text-uppercase fw-semibold mb-2">Total ce Mois</p>
                        <h3 class="fw-bold text-primary mb-1">{{ number_format($totalMois ?? 0) }}</h3>
                        <small class="text-muted">FCFA versés</small>
                    </div>
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card h-100">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small text-uppercase fw-semibold mb-2">Jours Payés</p>
                        <h3 class="fw-bold mb-1">{{ $joursPayes ?? 0 }}/{{ $joursTravailles ?? 0 }}</h3>
                        <small class="text-muted">ce mois</small>
                    </div>
                    <div class="stat-icon bg-info bg-opacity-10 text-info">
                        <i class="bi bi-check2-square"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card h-100">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small text-uppercase fw-semibold mb-2">Arriérés</p>
                        <h3 class="fw-bold {{ ($arrieres ?? 0) > 0 ? 'text-danger' : 'text-success' }} mb-1">
                            {{ number_format($arrieres ?? 0) }}
                        </h3>
                        <small class="text-muted">FCFA</small>
                    </div>
                    <div class="stat-icon bg-{{ ($arrieres ?? 0) > 0 ? 'danger' : 'success' }} bg-opacity-10 text-{{ ($arrieres ?? 0) > 0 ? 'danger' : 'success' }}">
                        <i class="bi bi-{{ ($arrieres ?? 0) > 0 ? 'exclamation-circle' : 'check-circle' }}"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Moto Info & Calendar -->
    <div class="row g-3 mb-4">
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-bicycle me-2 text-info"></i>Ma Moto</h6>
                </div>
                <div class="card-body">
                    @if($moto ?? null)
                    <div class="text-center mb-3">
                        <div class="rounded-circle bg-info bg-opacity-10 p-4 d-inline-flex mb-3">
                            <i class="bi bi-bicycle text-info" style="font-size: 2.5rem;"></i>
                        </div>
                        <h5 class="fw-bold mb-1">{{ $moto->plaque_immatriculation ?? 'N/A' }}</h5>
                        <p class="text-muted small mb-0">{{ $moto->marque ?? '' }} {{ $moto->modele ?? '' }}</p>
                    </div>
                    <ul class="list-unstyled mb-0">
                        <li class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted">Matricule</span>
                            <span class="fw-medium">{{ $moto->numero_matricule ?? 'N/A' }}</span>
                        </li>
                        <li class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted">Tarif journalier</span>
                            <span class="fw-medium">{{ number_format($moto->montant_journalier_attendu ?? 0) }} FCFA</span>
                        </li>
                        <li class="d-flex justify-content-between py-2">
                            <span class="text-muted">Statut</span>
                            <span class="badge badge-soft-{{ $moto->statut === 'actif' ? 'success' : 'secondary' }}">
                                {{ ucfirst($moto->statut ?? 'Inactif') }}
                            </span>
                        </li>
                    </ul>
                    @else
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-bicycle fs-1 d-block mb-2"></i>
                        <p class="mb-0">Aucune moto assignée</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-clock-history me-2 text-primary"></i>Mes Derniers Versements</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">Date</th>
                                    <th>Montant</th>
                                    <th>Attendu</th>
                                    <th class="pe-4">Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($derniersVersements ?? [] as $versement)
                                <tr>
                                    <td class="ps-4">
                                        <i class="bi bi-calendar3 me-1 text-muted"></i>
                                        {{ $versement->date_versement?->format('d/m/Y') ?? 'N/A' }}
                                    </td>
                                    <td class="fw-semibold {{ $versement->montant >= $versement->montant_attendu ? 'text-success' : 'text-warning' }}">
                                        {{ number_format($versement->montant ?? 0) }} FCFA
                                    </td>
                                    <td class="text-muted">{{ number_format($versement->montant_attendu ?? 0) }} FCFA</td>
                                    <td class="pe-4">
                                        @php
                                            $statutColors = [
                                                'payé' => 'success',
                                                'partiellement_payé' => 'warning',
                                                'non_effectué' => 'danger',
                                                'en_retard' => 'danger',
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

    <!-- Help Section -->
    <div class="card bg-light border-0">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h6 class="fw-bold mb-2"><i class="bi bi-question-circle me-2 text-primary"></i>Besoin d'aide ?</h6>
                    <p class="text-muted mb-0">Pour toute question concernant vos versements ou signaler un problème, contactez votre caissier ou utilisez la messagerie interne.</p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <a href="#" class="btn btn-primary">
                        <i class="bi bi-chat-dots me-2"></i>Contacter le support
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
