<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-person me-2 text-info"></i>{{ $cleaner->user->name ?? 'Laveur' }}
            </h4>
            <p class="text-muted mb-0">
                <span class="badge bg-info">{{ $cleaner->identifiant }}</span>
                <span class="badge bg-{{ $cleaner->is_active ? 'success' : 'danger' }} ms-1">
                    {{ $cleaner->is_active ? 'Actif' : 'Inactif' }}
                </span>
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.cleaners.edit', $cleaner) }}" class="btn btn-outline-primary">
                <i class="bi bi-pencil me-1"></i>Modifier
            </a>
            <a href="{{ route('admin.cleaners.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Retour
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Informations -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-person-badge me-2 text-info"></i>Informations</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted">Nom</span>
                            <strong>{{ $cleaner->user->name ?? 'N/A' }}</strong>
                        </li>
                        <li class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted">Email</span>
                            <strong>{{ $cleaner->user->email ?? 'N/A' }}</strong>
                        </li>
                        <li class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted">Téléphone</span>
                            <strong>{{ $cleaner->telephone ?? '-' }}</strong>
                        </li>
                        <li class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted">Zone</span>
                            <strong>{{ $cleaner->zone ?? '-' }}</strong>
                        </li>
                        <li class="d-flex justify-content-between py-2">
                            <span class="text-muted">Membre depuis</span>
                            <strong>{{ $cleaner->created_at->format('d/m/Y') }}</strong>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Statistiques -->
        <div class="col-lg-8">
            <!-- Stats cards -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card bg-info bg-opacity-10 border-0">
                        <div class="card-body text-center py-3">
                            <h3 class="fw-bold mb-0">{{ $stats['lavages_jour'] }}</h3>
                            <small class="text-muted">Lavages aujourd'hui</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-success bg-opacity-10 border-0">
                        <div class="card-body text-center py-3">
                            <h3 class="fw-bold mb-0 text-success">{{ number_format($stats['ca_jour']) }} FC</h3>
                            <small class="text-muted">CA du jour</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-warning bg-opacity-10 border-0">
                        <div class="card-body text-center py-3">
                            <h3 class="fw-bold mb-0 text-warning">{{ number_format($stats['ca_mois']) }} FC</h3>
                            <small class="text-muted">CA du mois</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Derniers lavages -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-clock-history me-2 text-secondary"></i>Derniers lavages</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>N° Lavage</th>
                                    <th>Moto</th>
                                    <th>Type</th>
                                    <th>Prix</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($derniersLavages as $lavage)
                                <tr>
                                    <td><span class="fw-semibold">{{ $lavage->numero_lavage }}</span></td>
                                    <td>
                                        @if($lavage->is_externe)
                                        <span class="badge bg-secondary">Ext</span> {{ $lavage->plaque_externe }}
                                        @else
                                        <span class="badge bg-info">Sys</span> {{ $lavage->moto?->plaque_immatriculation ?? 'N/A' }}
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $lavage->type_lavage === 'premium' ? 'warning' : ($lavage->type_lavage === 'complet' ? 'primary' : 'info') }}">
                                            {{ ucfirst($lavage->type_lavage) }}
                                        </span>
                                    </td>
                                    <td>{{ number_format($lavage->prix_final) }} FC</td>
                                    <td>{{ $lavage->date_lavage->format('d/m/Y H:i') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">Aucun lavage</td>
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

