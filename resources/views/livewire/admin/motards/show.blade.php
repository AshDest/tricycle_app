<div>

    <!-- Page Header -->
    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <h4>{{ $motard->user->name ?? 'Motard' }}</h4>
            <p class="text-muted small mb-0">Identifiant: {{ $motard->numero_identifiant }}</p>
        </div>
        <div class="d-flex gap-2">
            <button wire:click="toggleActive" class="btn btn-sm {{ $motard->is_active ? 'btn-outline-danger' : 'btn-outline-success' }}">
                <i class="bi bi-{{ $motard->is_active ? 'x-circle' : 'check-circle' }} me-1"></i>
                {{ $motard->is_active ? 'D&eacute;sactiver' : 'Activer' }}
            </button>
            <a href="{{ route('admin.motards.edit', $motard) }}" class="btn btn-sm btn-primary">
                <i class="bi bi-pencil me-1"></i> Modifier
            </a>
            <a href="{{ route('admin.motards.index') }}" class="btn btn-sm btn-light">
                <i class="bi bi-arrow-left me-1"></i> Retour
            </a>
        </div>
    </div>

    <div class="row g-3">
        <!-- Info Card -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body text-center">
                    <div class="user-avatar-sm bg-primary bg-gradient text-white mx-auto mb-3" style="width:64px;height:64px;font-size:1.5rem;">
                        {{ strtoupper(substr($motard->user->name ?? 'N', 0, 1)) }}
                    </div>
                    <h5 class="fw-semibold mb-1">{{ $motard->user->name ?? 'N/A' }}</h5>
                    <p class="text-muted small mb-2">{{ $motard->user->email ?? '' }}</p>
                    <span class="badge {{ $motard->is_active ? 'bg-success' : 'bg-danger' }}">
                        {{ $motard->is_active ? 'Actif' : 'Inactif' }}
                    </span>
                </div>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted small">T&eacute;l&eacute;phone</span>
                        <span class="fw-medium">{{ $motard->user->phone ?? '-' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted small">Identifiant</span>
                        <code>{{ $motard->numero_identifiant }}</code>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted small">Licence</span>
                        <span>{{ $motard->licence_numero ?? '-' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted small">Zone</span>
                        <span>{{ $motard->zone_affectation ?? '-' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted small">Moto actuelle</span>
                        <span>{{ $motard->motoActuelle->plaque_immatriculation ?? 'Non assign&eacute;e' }}</span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Stats & History -->
        <div class="col-lg-8">
            <!-- Stats -->
            <div class="row g-3 mb-3">
                <div class="col-sm-4">
                    <div class="card stat-card bg-white">
                        <p class="text-muted small mb-1">Total Vers&eacute;</p>
                        <h4 class="fw-bold mb-0">{{ number_format($stats['total_verse'] ?? 0) }} <small class="text-muted fw-normal fs-6">FCFA</small></h4>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="card stat-card bg-white">
                        <p class="text-muted small mb-1">Jours Actifs</p>
                        <h4 class="fw-bold mb-0">{{ $stats['jours_actifs'] ?? 0 }}</h4>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="card stat-card bg-white">
                        <p class="text-muted small mb-1">Taux Paiement</p>
                        <h4 class="fw-bold mb-0">{{ number_format($stats['taux_paiement'] ?? 0, 1) }}%</h4>
                    </div>
                </div>
            </div>

            <!-- Recent Versements -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0 fw-semibold">Derniers Versements</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Moto</th>
                                    <th>Montant</th>
                                    <th>Attendu</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($derniersVersements as $versement)
                                <tr>
                                    <td class="small">{{ $versement->date_versement?->format('d/m/Y') ?? '-' }}</td>
                                    <td>{{ $versement->moto->plaque_immatriculation ?? '-' }}</td>
                                    <td class="fw-semibold">{{ number_format($versement->montant) }} FCFA</td>
                                    <td class="text-muted">{{ number_format($versement->montant_attendu) }}</td>
                                    <td>
                                        @php
                                            $colors = ['paye' => 'success', 'payé' => 'success', 'partiel' => 'warning', 'en_retard' => 'danger'];
                                        @endphp
                                        <span class="badge bg-{{ $colors[$versement->statut] ?? 'secondary' }}">{{ ucfirst(str_replace('_', ' ', $versement->statut)) }}</span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-3">Aucun versement</td>
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
