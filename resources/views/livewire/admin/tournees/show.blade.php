<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-calendar-event me-2 text-primary"></i>Tournée #{{ $tournee->id }}
            </h4>
            <p class="text-muted mb-0">{{ $tournee->date?->format('d/m/Y') }} - {{ $tournee->zone }}</p>
        </div>
        <a href="{{ route('admin.tournees.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Retour
        </a>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <!-- Informations -->
            <div class="card mb-4">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-info-circle me-2 text-primary"></i>Informations de la tournée
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Collecteur</label>
                            <p class="fw-medium mb-0">{{ $tournee->collecteur?->user?->name ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Zone</label>
                            <p class="fw-medium mb-0">{{ $tournee->zone ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Date</label>
                            <p class="fw-medium mb-0">{{ $tournee->date?->format('d/m/Y') ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Horaires prévus</label>
                            <p class="fw-medium mb-0">
                                {{ $tournee->heure_debut_prevue ?? '?' }} - {{ $tournee->heure_fin_prevue ?? '?' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Collectes -->
            <div class="card">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-list-check me-2 text-success"></i>Collectes effectuées</h6>
                    <span class="badge bg-primary">{{ $tournee->collectes?->count() ?? 0 }} collecte(s)</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Caissier</th>
                                    <th>Montant attendu</th>
                                    <th>Montant collecté</th>
                                    <th>Écart</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($tournee->collectes ?? [] as $collecte)
                                <tr>
                                    <td class="ps-4">{{ $collecte->caissier?->user?->name ?? 'N/A' }}</td>
                                    <td>{{ number_format($collecte->montant_attendu ?? 0) }} FC</td>
                                    <td class="fw-bold text-success">{{ number_format($collecte->montant_collecte ?? 0) }} FC
                                    </td>
                                    <td>
                                        @php $ecart = ($collecte->montant_collecte ?? 0) - ($collecte->montant_attendu ?? 0); @endphp
                                        <span class="badge {{ $ecart >= 0 ? 'bg-success' : 'bg-danger' }}">
                                            {{ $ecart >= 0 ? '+' : '' }}{{ number_format($ecart) }} FC
                                        </span>
                                    </td>
                                    <td>
                                        @if($collecte->valide_par_collecteur)
                                        <span class="badge badge-soft-success">Validé</span>
                                        @else
                                        <span class="badge badge-soft-warning">En attente</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">Aucune collecte</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Statut -->
            <div class="card mb-4">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-flag me-2 text-secondary"></i>Statut</h6>
                </div>
                <div class="card-body text-center">
                    @php
                        $statutColors = [
                            'planifiee' => 'secondary',
                            'confirmee' => 'info',
                            'en_cours' => 'warning',
                            'terminee' => 'success',
                            'annulee' => 'danger',
                        ];
                    @endphp
                    <span class="badge bg-{{ $statutColors[$tournee->statut] ?? 'secondary' }} fs-6 px-3 py-2">
                        {{ ucfirst(str_replace('_', ' ', $tournee->statut ?? 'N/A')) }}
                    </span>
                </div>
            </div>

            <!-- Résumé financier -->
            <div class="card">
                <div class="card-header py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-cash-coin me-2 text-success"></i>Résumé</h6>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Total attendu</span>
                            <span class="fw-bold">{{ number_format($tournee->collectes?->sum('montant_attendu') ?? 0) }} FC</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between bg-success bg-opacity-10">
                            <span>Total collecté</span>
                            <span class="fw-bold text-success">{{ number_format($tournee->collectes?->sum('montant_collecte') ?? 0) }} FC</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Écart total</span>
                            @php
                                $ecartTotal = ($tournee->collectes?->sum('montant_collecte') ?? 0) - ($tournee->collectes?->sum('montant_attendu') ?? 0);
                            @endphp
                            <span class="fw-bold {{ $ecartTotal >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $ecartTotal >= 0 ? '+' : '' }}{{ number_format($ecartTotal) }} FC
                            </span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
