<div>

    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <h4>{{ $moto->plaque_immatriculation }}</h4>
            <p class="text-muted small mb-0">Matricule: {{ $moto->numero_matricule }}</p>
        </div>
        <div class="d-flex gap-2">
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">Changer statut</button>
                <ul class="dropdown-menu">
                    <li><button wire:click="changeStatut('actif')" class="dropdown-item">Actif</button></li>
                    <li><button wire:click="changeStatut('suspendu')" class="dropdown-item">Suspendu</button></li>
                    <li><button wire:click="changeStatut('maintenance')" class="dropdown-item">En maintenance</button></li>
                </ul>
            </div>
            <a href="{{ route('admin.motos.edit', $moto) }}" class="btn btn-sm btn-primary"><i class="bi bi-pencil me-1"></i> Modifier</a>
            <a href="{{ route('admin.motos.index') }}" class="btn btn-sm btn-light"><i class="bi bi-arrow-left me-1"></i> Retour</a>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body text-center">
                    <div class="stat-icon bg-info bg-opacity-10 text-info mx-auto mb-3" style="width:64px;height:64px;font-size:1.75rem;">
                        <i class="bi bi-bicycle"></i>
                    </div>
                    <h5 class="fw-semibold mb-1">{{ $moto->plaque_immatriculation }}</h5>
                    @php $statutColors = ['actif' => 'success', 'suspendu' => 'warning', 'maintenance' => 'info']; @endphp
                    <span class="badge bg-{{ $statutColors[$moto->statut] ?? 'secondary' }}">{{ ucfirst($moto->statut) }}</span>
                </div>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted small">Matricule</span>
                        <span class="fw-medium">{{ $moto->numero_matricule }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted small">Chassis</span>
                        <span>{{ $moto->numero_chassis ?? '-' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted small">Propri&eacute;taire</span>
                        <span class="fw-medium">{{ $moto->proprietaire->user->name ?? 'N/A' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted small">Motard</span>
                        <span>{{ $moto->motard->user->name ?? 'Non assign&eacute;' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted small">Montant/Jour</span>
                        <span class="fw-semibold text-success">{{ number_format($moto->montant_journalier_attendu) }} FC</span>
                    </li>
                </ul>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="row g-3 mb-3">
                <div class="col-sm-4">
                    <div class="card stat-card bg-white">
                        <p class="text-muted small mb-1">Total Vers&eacute;</p>
                        <h4 class="fw-bold mb-0">{{ number_format($stats['total_verse'] ?? 0) }} <small class="text-muted fw-normal fs-6">FC</small></h4>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="card stat-card bg-white">
                        <p class="text-muted small mb-1">Total Attendu</p>
                        <h4 class="fw-bold mb-0">{{ number_format($stats['total_attendu'] ?? 0) }} <small class="text-muted fw-normal fs-6">FC</small></h4>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="card stat-card bg-white">
                        <p class="text-muted small mb-1">Taux Recouvrement</p>
                        <h4 class="fw-bold mb-0">{{ number_format($stats['taux_recouvrement'] ?? 0, 1) }}%</h4>
                    </div>
                </div>
            </div>

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
                                    <th>Motard</th>
                                    <th>Montant</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($derniersVersements as $v)
                                <tr>
                                    <td class="small">{{ $v->date_versement?->format('d/m/Y') ?? '-' }}</td>
                                    <td>{{ $v->motard->user->name ?? 'N/A' }}</td>
                                    <td class="fw-semibold">{{ number_format($v->montant) }} FC</td>
                                    <td>
                                        @php $colors = ['paye' => 'success', 'payé' => 'success', 'partiel' => 'warning', 'en_retard' => 'danger']; @endphp
                                        <span class="badge bg-{{ $colors[$v->statut] ?? 'secondary' }}">{{ ucfirst(str_replace('_', ' ', $v->statut)) }}</span>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="text-center text-muted py-3">Aucun versement</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
