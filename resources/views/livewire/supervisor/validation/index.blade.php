<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-check2-square me-2 text-warning"></i>Validation des Versements
            </h4>
            <p class="text-muted mb-0">Versements douteux ou litigieux nécessitant une validation manuelle</p>
        </div>
        <div class="d-flex gap-2">
            <span class="badge bg-warning text-dark px-3 py-2">
                <i class="bi bi-exclamation-triangle me-1"></i>{{ $versementsEnAttente ?? 0 }} en attente
            </span>
            <button class="btn btn-sm btn-outline-primary" wire:click="$refresh">
                <i class="bi bi-arrow-clockwise"></i>
            </button>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Rechercher</label>
                    <input type="text" wire:model.live="search" class="form-control" placeholder="Motard, moto, caissier...">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Type de problème</label>
                    <select wire:model.live="filterType" class="form-select">
                        <option value="">Tous</option>
                        <option value="non_confirme">Non confirmé</option>
                        <option value="montant_incorrect">Montant incorrect</option>
                        <option value="double_saisie">Double saisie</option>
                        <option value="litigieux">Litigieux</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Statut</label>
                    <select wire:model.live="filterStatut" class="form-select">
                        <option value="">Tous</option>
                        <option value="en_attente">En attente</option>
                        <option value="valide">Validé</option>
                        <option value="rejete">Rejeté</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button wire:click="resetFilters" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des versements à valider -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Motard</th>
                            <th>Moto</th>
                            <th>Montant déclaré</th>
                            <th>Montant attendu</th>
                            <th>Problème</th>
                            <th>Date</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($versements ?? [] as $versement)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar avatar-sm bg-primary bg-opacity-10 text-primary rounded-circle">
                                        {{ strtoupper(substr($versement->motard->user->name ?? 'N', 0, 1)) }}
                                    </div>
                                    <div>
                                        <span class="fw-medium d-block">{{ $versement->motard->user->name ?? 'N/A' }}</span>
                                        <small class="text-muted">{{ $versement->motard->numero_identifiant ?? '' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">
                                    {{ $versement->moto->plaque_immatriculation ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="fw-semibold">{{ number_format($versement->montant ?? 0) }} FC</td>
                            <td class="text-muted">{{ number_format($versement->montant_attendu ?? 0) }} FC</td>
                            <td>
                                <span class="badge badge-soft-warning">
                                    <i class="bi bi-exclamation-triangle me-1"></i>
                                    {{ ucfirst(str_replace('_', ' ', $versement->type_probleme ?? 'À vérifier')) }}
                                </span>
                            </td>
                            <td class="text-muted small">{{ $versement->date_versement?->format('d/m/Y') }}</td>
                            <td class="text-end pe-4">
                                <div class="btn-group">
                                    <button wire:click="validerVersement({{ $versement->id }})"
                                            class="btn btn-sm btn-success"
                                            title="Valider"
                                            wire:confirm="Êtes-vous sûr de vouloir valider ce versement ?">
                                        <i class="bi bi-check-lg"></i>
                                    </button>
                                    <button wire:click="invaliderVersement({{ $versement->id }})"
                                            class="btn btn-sm btn-danger"
                                            title="Rejeter"
                                            wire:confirm="Êtes-vous sûr de vouloir rejeter ce versement ?">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-check-circle fs-1 text-success d-block mb-3"></i>
                                <p class="mb-0 fw-medium">Aucun versement en attente de validation</p>
                                <small>Tous les versements ont été traités</small>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($versements->hasPages())
        <div class="card-footer bg-light">
            {{ $versements->links() }}
        </div>
        @endif
    </div>

    <!-- Note importante -->
    <div class="alert alert-warning mt-4">
        <div class="d-flex align-items-start gap-3">
            <i class="bi bi-shield-exclamation fs-4"></i>
            <div>
                <strong>Rappel des règles OKAMI :</strong>
                <ul class="mb-0 mt-2">
                    <li>Vous pouvez <strong>valider ou invalider</strong> un versement après vérification</li>
                    <li>Vous ne pouvez <strong>pas modifier</strong> un montant</li>
                    <li>Vous ne pouvez <strong>pas enregistrer</strong> un nouveau versement</li>
                    <li>Vous ne pouvez <strong>pas effacer</strong> un versement</li>
                </ul>
            </div>
        </div>
    </div>
</div>
