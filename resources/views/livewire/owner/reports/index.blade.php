<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-file-earmark-bar-graph me-2 text-info"></i>Mes Relevés
            </h4>
            <p class="text-muted mb-0">Consultez vos relevés mensuels de versements</p>
        </div>
        @if($proprietaire)
        <button wire:click="exportPdf" class="btn btn-danger" wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="exportPdf">
                <i class="bi bi-file-pdf me-1"></i>Exporter PDF
            </span>
            <span wire:loading wire:target="exportPdf">
                <span class="spinner-border spinner-border-sm me-1"></span>Génération...
            </span>
        </button>
        @endif
    </div>

    @if(!$proprietaire)
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle me-2"></i>
        <strong>Attention:</strong> Votre compte n'est pas associé à un profil propriétaire. Veuillez contacter l'administration.
    </div>
    @else


    <!-- Filtres période -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Mois</label>
                    <select wire:model.live="mois" class="form-select">
                        @foreach($moisOptions as $num => $nom)
                        <option value="{{ $num }}">{{ $nom }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Année</label>
                    <select wire:model.live="annee" class="form-select">
                        @foreach($anneeOptions as $an)
                        <option value="{{ $an }}">{{ $an }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <div class="alert alert-info mb-0 py-2">
                        <i class="bi bi-calendar3 me-2"></i>
                        Période: <strong>{{ $moisOptions[$mois] ?? '' }} {{ $annee }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Résumé -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card bg-success bg-opacity-10 border-0 h-100">
                <div class="card-body text-center py-4">
                    <i class="bi bi-cash-stack fs-2 text-success mb-2"></i>
                    <h4 class="fw-bold text-success mb-1">{{ number_format($totalVersements) }} FC</h4>
                    <small class="text-muted">Total Versé</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card bg-primary bg-opacity-10 border-0 h-100">
                <div class="card-body text-center py-4">
                    <i class="bi bi-bullseye fs-2 text-primary mb-2"></i>
                    <h4 class="fw-bold text-primary mb-1">{{ number_format($totalAttendu) }} FC</h4>
                    <small class="text-muted">Total Attendu</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card bg-{{ $totalArrieres > 0 ? 'danger' : 'success' }} bg-opacity-10 border-0 h-100">
                <div class="card-body text-center py-4">
                    <i class="bi bi-exclamation-triangle fs-2 text-{{ $totalArrieres > 0 ? 'danger' : 'success' }} mb-2"></i>
                    <h4 class="fw-bold text-{{ $totalArrieres > 0 ? 'danger' : 'success' }} mb-1">{{ number_format($totalArrieres) }} FC</h4>
                    <small class="text-muted">Arriérés</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card bg-info bg-opacity-10 border-0 h-100">
                <div class="card-body text-center py-4">
                    <i class="bi bi-wallet2 fs-2 text-info mb-2"></i>
                    <h4 class="fw-bold text-info mb-1">{{ number_format($soldeDisponible) }} FC</h4>
                    <small class="text-muted">Solde Disponible</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Détails par moto -->
    <div class="card">
        <div class="card-header py-3">
            <h6 class="mb-0 fw-bold"><i class="bi bi-bicycle me-2 text-primary"></i>Détails par Moto ({{ count($versementsParMoto) }} motos)</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Moto</th>
                            <th>Motard</th>
                            <th class="text-center">Nb Versements</th>
                            <th class="text-end">Versé</th>
                            <th class="text-end">Attendu</th>
                            <th class="text-end">Arriérés</th>
                            <th class="text-center pe-4">Taux</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($versementsParMoto as $item)
                        @php
                            $taux = ($item['attendu'] ?? 0) > 0 ? round((($item['total'] ?? 0) / $item['attendu']) * 100, 1) : 0;
                            $tauxColor = $taux >= 90 ? 'success' : ($taux >= 70 ? 'warning' : 'danger');
                        @endphp
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar avatar-sm bg-primary bg-opacity-10 text-primary rounded">
                                        <i class="bi bi-bicycle"></i>
                                    </div>
                                    <div>
                                        <span class="fw-medium d-block">{{ $item['moto']->plaque_immatriculation ?? 'N/A' }}</span>
                                        <small class="text-muted">{{ $item['moto']->numero_matricule ?? '' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($item['moto']->motard)
                                {{ $item['moto']->motard->user->name ?? 'N/A' }}
                                @else
                                <span class="text-muted fst-italic">Non assigné</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark">{{ $item['nb_versements'] ?? 0 }}</span>
                            </td>
                            <td class="text-end fw-semibold text-success">{{ number_format($item['total'] ?? 0) }} FC</td>
                            <td class="text-end text-muted">{{ number_format($item['attendu'] ?? 0) }} FC</td>
                            <td class="text-end">
                                @if(($item['arrieres'] ?? 0) > 0)
                                <span class="text-danger fw-semibold">{{ number_format($item['arrieres']) }} FC</span>
                                @else
                                <span class="text-success"><i class="bi bi-check-circle"></i></span>
                                @endif
                            </td>
                            <td class="text-center pe-4">
                                <span class="badge badge-soft-{{ $tauxColor }}">{{ $taux }}%</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-bicycle fs-1 d-block mb-3"></i>
                                <p class="mb-0">Vous n'avez aucune moto enregistrée</p>
                                <small>Les motos doivent être associées à votre compte propriétaire</small>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if(count($versementsParMoto) > 0)
                    <tfoot class="bg-light">
                        <tr class="fw-bold">
                            <td class="ps-4" colspan="3">TOTAL</td>
                            <td class="text-end text-success">{{ number_format($totalVersements) }} FC</td>
                            <td class="text-end">{{ number_format($totalAttendu) }} FC</td>
                            <td class="text-end text-danger">{{ number_format($totalArrieres) }} FC</td>
                            <td class="text-center pe-4">
                                @php
                                    $tauxGlobal = $totalAttendu > 0 ? round(($totalVersements / $totalAttendu) * 100, 1) : 100;
                                @endphp
                                <span class="badge badge-soft-{{ $tauxGlobal >= 90 ? 'success' : ($tauxGlobal >= 70 ? 'warning' : 'danger') }}">
                                    {{ $tauxGlobal }}%
                                </span>
                            </td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

    <!-- Info paiements -->
    @if($paiementsRecus > 0)
    <div class="alert alert-success mt-4">
        <i class="bi bi-check-circle me-2"></i>
        <strong>Paiements reçus ce mois:</strong> {{ number_format($paiementsRecus) }} FC
    </div>
    @endif

    @endif {{-- End of @if($proprietaire) --}}
</div>
