<div>

    @if(session()->has('success'))
    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
        <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <h4>Liste des Motos</h4>
            <p class="text-muted small mb-0">G&eacute;rer les motos-tricycles de la flotte</p>
        </div>
        <div class="d-flex gap-2">
            <button wire:click="exportPdf" class="btn btn-danger">
                <i class="bi bi-file-pdf me-1"></i> PDF
            </button>
            <a href="{{ route('admin.motos.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Ajouter une moto
            </a>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body py-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Rechercher par plaque, matricule, chassis...">
                    </div>
                </div>
                <div class="col-md-2">
                    <select wire:model.live="filterStatut" class="form-select form-select-sm">
                        <option value="">Tous les statuts</option>
                        <option value="actif">Actif</option>
                        <option value="suspendu">Suspendu</option>
                        <option value="maintenance">En maintenance</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select wire:model.live="filterAssignation" class="form-select form-select-sm">
                        <option value="">Toutes</option>
                        <option value="assignee">Assign&eacute;es</option>
                        <option value="non_assignee">Non assign&eacute;es</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select wire:model.live="perPage" class="form-select form-select-sm">
                        <option value="10">10 par page</option>
                        <option value="15">15 par page</option>
                        <option value="25">25 par page</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Plaque</th>
                            <th>Matricule</th>
                            <th>Propri&eacute;taire</th>
                            <th>Motard</th>
                            <th>Montant/Jour</th>
                            <th>Statut</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($motos as $moto)
                        <tr>
                            <td><code class="fw-semibold">{{ $moto->plaque_immatriculation }}</code></td>
                            <td>{{ $moto->numero_matricule }}</td>
                            <td>{{ $moto->proprietaire->user->name ?? 'N/A' }}</td>
                            <td>
                                @if($moto->motard)
                                    <span class="fw-medium">{{ $moto->motard->user->name ?? 'N/A' }}</span>
                                @else
                                    <span class="text-muted small">Non assign&eacute;</span>
                                @endif
                            </td>
                            <td>{{ number_format($moto->montant_journalier_attendu) }} FC</td>
                            <td>
                                @php
                                    $statutColors = ['actif' => 'success', 'suspendu' => 'warning', 'maintenance' => 'info'];
                                @endphp
                                <span class="badge bg-{{ $statutColors[$moto->statut] ?? 'secondary' }}">{{ ucfirst($moto->statut) }}</span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.motos.show', $moto) }}" class="btn btn-outline-secondary" title="Voir"><i class="bi bi-eye"></i></a>
                                    <a href="{{ route('admin.motos.edit', $moto) }}" class="btn btn-outline-secondary" title="Modifier"><i class="bi bi-pencil"></i></a>
                                    <button wire:click="delete({{ $moto->id }})" wire:confirm="Supprimer cette moto ?" class="btn btn-outline-danger" title="Supprimer"><i class="bi bi-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="bi bi-bicycle fs-3 d-block mb-2"></i>
                                Aucune moto trouv&eacute;e
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($motos->hasPages())
        <div class="card-footer d-flex justify-content-between align-items-center">
            <small class="text-muted">{{ $motos->firstItem() }} &agrave; {{ $motos->lastItem() }} sur {{ $motos->total() }}</small>
            {{ $motos->links() }}
        </div>
        @endif
    </div>
</div>
