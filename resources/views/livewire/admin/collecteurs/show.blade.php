<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-person-badge me-2 text-info"></i>Détails du Collecteur
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Tableau de bord</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.collecteurs.index') }}">Collecteurs</a></li>
                    <li class="breadcrumb-item active">{{ $collecteur->user->name ?? 'Détails' }}</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.collecteurs.solde', $collecteur) }}" class="btn btn-info text-white">
                <i class="bi bi-wallet2 me-1"></i>Solde & Dépenses
            </a>
            <a href="{{ route('admin.collecteurs.edit', $collecteur) }}" class="btn btn-primary">
                <i class="bi bi-pencil me-1"></i>Modifier
            </a>
            <a href="{{ route('admin.collecteurs.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Retour
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Informations principales -->
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <div class="avatar avatar-xl bg-info bg-opacity-10 text-info rounded-circle mx-auto mb-3" style="width: 80px; height: 80px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-person-badge fs-1"></i>
                    </div>
                    <h5 class="fw-bold mb-1">{{ $collecteur->user->name ?? 'N/A' }}</h5>
                    <p class="text-muted mb-2">{{ $collecteur->numero_identifiant ?? 'N/A' }}</p>
                    <span class="badge badge-soft-{{ $collecteur->is_active ? 'success' : 'danger' }} px-3 py-2">
                        <i class="bi bi-circle-fill me-1" style="font-size: 8px;"></i>
                        {{ $collecteur->is_active ? 'Actif' : 'Inactif' }}
                    </span>

                    <hr class="my-4">

                    <div class="text-start">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted"><i class="bi bi-envelope me-2"></i>Email</span>
                            <span class="fw-medium">{{ $collecteur->user->email ?? 'N/A' }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted"><i class="bi bi-telephone me-2"></i>Téléphone</span>
                            <span class="fw-medium">{{ $collecteur->telephone ?? $collecteur->user->phone ?? 'N/A' }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted"><i class="bi bi-geo-alt me-2"></i>Zone</span>
                            <span class="fw-medium">{{ $collecteur->zone_affectation ?? 'Non définie' }}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted"><i class="bi bi-calendar me-2"></i>Créé le</span>
                            <span class="fw-medium">{{ $collecteur->created_at?->format('d/m/Y') ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistiques et Solde -->
        <div class="col-lg-8">
            <div class="row g-4">
                <!-- Solde Caisse -->
                <div class="col-12">
                    <div class="card bg-info bg-opacity-10 border-0">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted mb-1">Solde Caisse Actuel</p>
                                    <h3 class="fw-bold text-info mb-0">{{ number_format($collecteur->solde_caisse ?? 0) }} FC</h3>
                                </div>
                                <div class="avatar avatar-lg bg-info bg-opacity-25 text-info rounded">
                                    <i class="bi bi-wallet2 fs-3"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- Lien vers détails solde -->
                <div class="col-12">
                    <a href="{{ route('admin.collecteurs.solde', $collecteur) }}" class="btn btn-outline-info w-100">
                        <i class="bi bi-journal-text me-2"></i>Voir le journal quotidien & dépenses détaillées
                    </a>
                </div>

                <!-- Stats Cards -->
                @php
                    $stats = $collecteur->getStatistiques();
                @endphp
                <div class="col-sm-6 col-md-3">
                    <div class="card border-0 bg-primary bg-opacity-10">
                        <div class="card-body text-center py-3">
                            <h4 class="fw-bold text-primary mb-1">{{ $stats['total_tournees'] ?? 0 }}</h4>
                            <small class="text-muted">Total Tournées</small>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="card border-0 bg-success bg-opacity-10">
                        <div class="card-body text-center py-3">
                            <h4 class="fw-bold text-success mb-1">{{ $stats['tournees_terminees'] ?? 0 }}</h4>
                            <small class="text-muted">Terminées</small>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="card border-0 bg-warning bg-opacity-10">
                        <div class="card-body text-center py-3">
                            <h4 class="fw-bold text-warning mb-1">{{ $stats['tournees_en_retard'] ?? 0 }}</h4>
                            <small class="text-muted">En retard</small>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="card border-0 bg-success bg-opacity-10">
                        <div class="card-body text-center py-3">
                            <h4 class="fw-bold text-success mb-1">{{ number_format($stats['total_collecte'] ?? 0) }}</h4>
                            <small class="text-muted">Total Collecté (FC)</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dernières Tournées -->
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-calendar-check me-2 text-primary"></i>Dernières Tournées</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Date</th>
                                    <th>Zone</th>
                                    <th class="text-end">Montant Attendu</th>
                                    <th class="text-end">Montant Collecté</th>
                                    <th class="text-end">Écart</th>
                                    <th class="text-center pe-4">Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($collecteur->tournees()->orderBy('date', 'desc')->take(10)->get() as $tournee)
                                <tr>
                                    <td class="ps-4">
                                        <span class="fw-medium">{{ $tournee->date?->format('d/m/Y') ?? 'N/A' }}</span>
                                    </td>
                                    <td>{{ $tournee->zone ?? 'N/A' }}</td>
                                    <td class="text-end">{{ number_format($tournee->total_attendu ?? 0) }} FC</td>
                                    <td class="text-end fw-semibold text-success">{{ number_format($tournee->total_encaisse ?? 0) }} FC</td>
                                    <td class="text-end">
                                        @php
                                            $ecart = ($tournee->total_encaisse ?? 0) - ($tournee->total_attendu ?? 0);
                                        @endphp
                                        <span class="text-{{ $ecart >= 0 ? 'success' : 'danger' }}">
                                            {{ $ecart >= 0 ? '+' : '' }}{{ number_format($ecart) }} FC
                                        </span>
                                    </td>
                                    <td class="text-center pe-4">
                                        @php
                                            $statutColors = [
                                                'planifiee' => 'secondary',
                                                'confirmee' => 'info',
                                                'en_cours' => 'primary',
                                                'terminee' => 'success',
                                                'en_retard' => 'danger',
                                            ];
                                        @endphp
                                        <span class="badge badge-soft-{{ $statutColors[$tournee->statut] ?? 'secondary' }}">
                                            {{ ucfirst(str_replace('_', ' ', $tournee->statut ?? 'N/A')) }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        <i class="bi bi-calendar-x fs-3 d-block mb-2"></i>
                                        Aucune tournée enregistrée
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
