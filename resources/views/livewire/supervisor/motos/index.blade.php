<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-bicycle me-2 text-info"></i>Gestion des Motos
            </h4>
            <p class="text-muted mb-0">Liste complète des motos-tricycles et leurs contrats</p>
        </div>
        <div class="d-flex gap-2">
            <button wire:click="exportPdf" class="btn btn-danger">
                <i class="bi bi-file-pdf me-1"></i>PDF
            </button>
            <button class="btn btn-outline-success" wire:click="export('csv')">
                <i class="bi bi-download me-1"></i>CSV
            </button>
            <a href="{{ route('supervisor.motos.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>Nouvelle Moto
            </a>
        </div>
    </div>
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    <!-- Statistiques -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-2">
            <div class="card bg-info bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="mb-0 fw-bold text-info">{{ $stats['total'] }}</h4>
                    <small class="text-muted">Total Motos</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-2">
            <div class="card bg-success bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="mb-0 fw-bold text-success">{{ $stats['actives'] }}</h4>
                    <small class="text-muted">Statut Actif</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-2">
            <div class="card bg-primary bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="mb-0 fw-bold text-primary">{{ $stats['contratsActifs'] }}</h4>
                    <small class="text-muted">Contrats Actifs</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-2">
            <div class="card bg-warning bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="mb-0 fw-bold text-warning">{{ $stats['contratsBientotExpires'] }}</h4>
                    <small class="text-muted">Expirent bientôt</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-2">
            <div class="card bg-danger bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="mb-0 fw-bold text-danger">{{ $stats['contratsExpires'] }}</h4>
                    <small class="text-muted">Contrats Expirés</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-2">
            <div class="card bg-secondary bg-opacity-10 border-0">
                <div class="card-body py-3 text-center">
                    <h4 class="mb-0 fw-bold text-secondary">{{ $stats['sansContrat'] }}</h4>
                    <small class="text-muted">Sans Contrat</small>
                </div>
            </div>
        </div>
    </div>
    <!-- Filtres -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Rechercher</label>
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Plaque, châssis...">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Statut Moto</label>
                    <select wire:model.live="filterStatut" class="form-select">
                        <option value="">Tous</option>
                        <option value="actif">Actives</option>
                        <option value="suspendu">Suspendues</option>
                        <option value="maintenance">En maintenance</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Statut Contrat</label>
                    <select wire:model.live="filterContrat" class="form-select">
                        <option value="">Tous</option>
                        <option value="actif">Contrat Actif</option>
                        <option value="bientot_expire">Expire dans 30j</option>
                        <option value="expire">Contrat Expiré</option>
                        <option value="sans_contrat">Sans Contrat</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Propriétaire</label>
                    <select wire:model.live="filterProprietaire" class="form-select">
                        <option value="">Tous</option>
                        @foreach($proprietaires as $prop)
                            <option value="{{ $prop->id }}">{{ $prop->user->name ?? 'N/A' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button wire:click="resetFilters" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-x-lg me-1"></i>Réinitialiser
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal de renouvellement -->
    @if($renewingContrat)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-arrow-repeat me-2 text-success"></i>Renouveler le Contrat</h5>
                    <button type="button" class="btn-close" wire:click="cancelRenewContrat"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Date de début <span class="text-danger">*</span></label>
                        <input type="date" wire:model="newContratDebut" class="form-control @error('newContratDebut') is-invalid @enderror">
                        @error('newContratDebut')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Date de fin <span class="text-danger">*</span></label>
                        <input type="date" wire:model="newContratFin" class="form-control @error('newContratFin') is-invalid @enderror">
                        @error('newContratFin')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Notes (optionnel)</label>
                        <textarea wire:model="newContratNotes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="cancelRenewContrat">Annuler</button>
                    <button type="button" class="btn btn-success" wire:click="renewContrat">
                        <i class="bi bi-check-lg me-1"></i>Renouveler
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
    <!-- Liste -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Moto</th>
                            <th>Propriétaire</th>
                            <th>Motard</th>
                            <th>Contrat</th>
                            <th>Statut</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($motos as $moto)
                        @php
                            $statutContrat = $moto->statut_contrat;
                            $contratColors = ['actif'=>'success','bientot_expire'=>'warning','expire'=>'danger','pas_commence'=>'info','non_defini'=>'secondary'];
                            $contratLabels = ['actif'=>'Actif','bientot_expire'=>'Expire bientôt','expire'=>'Expiré','pas_commence'=>'Pas commencé','non_defini'=>'Non défini'];
                        @endphp
                        <tr class="{{ $statutContrat === 'expire' ? 'table-danger' : ($statutContrat === 'bientot_expire' ? 'table-warning' : '') }}">
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar avatar-sm bg-info bg-opacity-10 text-info rounded d-flex align-items-center justify-content-center" style="width:40px;height:40px;">
                                        <i class="bi bi-bicycle"></i>
                                    </div>
                                    <div>
                                        <span class="fw-medium d-block">{{ $moto->plaque_immatriculation }}</span>
                                        <small class="text-muted">{{ $moto->numero_chassis ?? 'N/A' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td><span class="fw-medium">{{ $moto->proprietaire->user->name ?? 'N/A' }}</span></td>
                            <td>
                                @if($moto->motardActuel)
                                    <span class="badge bg-primary bg-opacity-10 text-primary"><i class="bi bi-person me-1"></i>{{ $moto->motardActuel->user->name ?? 'N/A' }}</span>
                                @else
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary">Non assignée</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-soft-{{ $contratColors[$statutContrat] ?? 'secondary' }}">{{ $contratLabels[$statutContrat] ?? 'N/A' }}</span>
                                @if($moto->contrat_debut && $moto->contrat_fin)
                                <small class="text-muted d-block mt-1">{{ $moto->contrat_debut->format('d/m/Y') }} - {{ $moto->contrat_fin->format('d/m/Y') }}</small>
                                @if($moto->jours_restants_contrat !== null && $moto->jours_restants_contrat > 0)
                                <small class="text-{{ $moto->jours_restants_contrat <= 30 ? 'warning' : 'muted' }}">{{ $moto->jours_restants_contrat }}j restants</small>
                                @endif
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-soft-{{ $moto->statut === 'actif' ? 'success' : 'secondary' }}">{{ ucfirst($moto->statut ?? 'N/A') }}</span>
                                @if(!$moto->est_operationnelle && $moto->statut === 'actif')
                                <small class="text-danger d-block"><i class="bi bi-exclamation-triangle"></i> Contrat invalide</small>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group">
                                    <a href="{{ route('supervisor.motos.edit', $moto) }}" class="btn btn-sm btn-outline-primary" title="Modifier"><i class="bi bi-pencil"></i></a>
                                    @if(in_array($statutContrat, ['expire', 'bientot_expire', 'non_defini']))
                                    <button wire:click="openRenewContrat({{ $moto->id }})" class="btn btn-sm btn-outline-success" title="Renouveler"><i class="bi bi-arrow-repeat"></i></button>
                                    @endif
                                    <button wire:click="toggleStatut({{ $moto->id }})" class="btn btn-sm btn-outline-{{ $moto->statut === 'actif' ? 'warning' : 'success' }}" title="{{ $moto->statut === 'actif' ? 'Désactiver' : 'Activer' }}"><i class="bi bi-{{ $moto->statut === 'actif' ? 'pause' : 'play' }}"></i></button>
                                    @if($confirmingDelete === $moto->id)
                                    <button wire:click="delete({{ $moto->id }})" class="btn btn-sm btn-danger"><i class="bi bi-check"></i></button>
                                    <button wire:click="cancelDelete" class="btn btn-sm btn-secondary"><i class="bi bi-x"></i></button>
                                    @else
                                    <button wire:click="confirmDelete({{ $moto->id }})" class="btn btn-sm btn-outline-danger" title="Supprimer"><i class="bi bi-trash"></i></button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-bicycle fs-1 d-block mb-3"></i>
                                <p class="mb-0">Aucune moto trouvée</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($motos->hasPages())
        <div class="card-footer bg-light">{{ $motos->links() }}</div>
        @endif
    </div>
</div>
