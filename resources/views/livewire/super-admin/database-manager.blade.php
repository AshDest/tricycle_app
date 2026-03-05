<div>
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-database me-2 text-danger"></i>Gestion Base de Données
            </h4>
            <p class="text-muted mb-0">Backup, restauration et suppression des données</p>
        </div>
        <a href="{{ route('super-admin.dashboard') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Retour
        </a>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row g-4">
        <!-- Section Backup -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header py-3 bg-success text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold">
                            <i class="bi bi-cloud-download me-2"></i>Backups
                        </h6>
                        <button wire:click="createBackup" class="btn btn-light btn-sm" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="createBackup">
                                <i class="bi bi-plus-circle me-1"></i>Nouveau Backup
                            </span>
                            <span wire:loading wire:target="createBackup">
                                <span class="spinner-border spinner-border-sm me-1"></span>Création...
                            </span>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    @if($lastBackup)
                    <div class="alert alert-info py-2 mb-3">
                        <small>
                            <i class="bi bi-info-circle me-1"></i>
                            Dernier backup: <strong>{{ $lastBackup['date'] }}</strong> ({{ $lastBackup['size'] }})
                        </small>
                    </div>
                    @endif

                    <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="bg-light sticky-top">
                                <tr>
                                    <th>Fichier</th>
                                    <th>Taille</th>
                                    <th>Date</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($backups as $backup)
                                <tr>
                                    <td>
                                        <i class="bi bi-file-earmark-code text-success me-1"></i>
                                        <small>{{ $backup['name'] }}</small>
                                    </td>
                                    <td><small class="text-muted">{{ $backup['size'] }}</small></td>
                                    <td><small>{{ $backup['date'] }}</small></td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <button wire:click="downloadBackup('{{ $backup['name'] }}')" class="btn btn-outline-primary" title="Télécharger">
                                                <i class="bi bi-download"></i>
                                            </button>
                                            <button wire:click="deleteBackup('{{ $backup['name'] }}')" class="btn btn-outline-danger" title="Supprimer"
                                                    wire:confirm="Supprimer ce backup ?">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">
                                        <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                        Aucun backup disponible
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section Tables -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header py-3 bg-danger text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold">
                            <i class="bi bi-table me-2"></i>Tables de Données
                        </h6>
                        <button wire:click="toggleSelectAll" class="btn btn-light btn-sm">
                            <i class="bi bi-check-all me-1"></i>Tout sélectionner
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="bg-light sticky-top">
                                <tr>
                                    <th style="width: 40px;"></th>
                                    <th>Table</th>
                                    <th class="text-end">Enregistrements</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($tables as $table => $info)
                                <tr>
                                    <td>
                                        <input type="checkbox" wire:model.live="selectedTables" value="{{ $table }}" class="form-check-input">
                                    </td>
                                    <td>
                                        <i class="bi {{ $info['icon'] }} text-{{ $info['color'] }} me-1"></i>
                                        {{ $info['label'] }}
                                    </td>
                                    <td class="text-end">
                                        <span class="badge bg-{{ $info['count'] > 0 ? $info['color'] : 'secondary' }}">
                                            {{ number_format($info['count']) }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if(count($selectedTables) > 0)
                    <div class="mt-3 p-3 bg-danger bg-opacity-10 rounded">
                        <p class="mb-2 text-danger fw-bold">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            {{ count($selectedTables) }} table(s) sélectionnée(s)
                        </p>
                        <button wire:click="$set('confirmDelete', true)" class="btn btn-danger btn-sm">
                            <i class="bi bi-trash me-1"></i>Vider les tables sélectionnées
                        </button>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Zone de Suppression Dangereuse -->
    <div class="card border-danger mt-4">
        <div class="card-header bg-danger text-white py-3">
            <h6 class="mb-0 fw-bold">
                <i class="bi bi-exclamation-octagon me-2"></i>Zone Dangereuse
            </h6>
        </div>
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h6 class="fw-bold text-danger">Supprimer toutes les données</h6>
                    <p class="text-muted mb-0">
                        Cette action supprimera <strong>TOUTES</strong> les données de l'application
                        (versements, paiements, motos, motards, etc.) sauf les utilisateurs et leurs rôles.
                        <br><strong>Cette action est irréversible!</strong>
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <button wire:click="$set('confirmDelete', 'all')" class="btn btn-outline-danger">
                        <i class="bi bi-radioactive me-1"></i>Tout Supprimer
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmation -->
    @if($confirmDelete)
    <div class="modal fade show d-block" style="background: rgba(0,0,0,0.6);" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-danger">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Confirmation de Suppression
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="$set('confirmDelete', false)"></button>
                </div>
                <div class="modal-body">
                    @if($confirmDelete === 'all')
                    <div class="alert alert-danger">
                        <strong>Attention!</strong> Vous allez supprimer TOUTES les données sauf les utilisateurs.
                    </div>
                    <p>Pour confirmer, tapez <strong>SUPPRIMER TOUT</strong> ci-dessous:</p>
                    @else
                    <div class="alert alert-warning">
                        <strong>Tables sélectionnées:</strong>
                        <ul class="mb-0 mt-2">
                            @foreach($selectedTables as $table)
                            <li>{{ $tables[$table]['label'] ?? $table }} ({{ $tables[$table]['count'] ?? 0 }} enregistrements)</li>
                            @endforeach
                        </ul>
                    </div>
                    <p>Pour confirmer, tapez <strong>SUPPRIMER</strong> ci-dessous:</p>
                    @endif

                    <input type="text" wire:model="deleteConfirmText" class="form-control form-control-lg text-center"
                           placeholder="{{ $confirmDelete === 'all' ? 'SUPPRIMER TOUT' : 'SUPPRIMER' }}"
                           autocomplete="off">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="$set('confirmDelete', false)">Annuler</button>
                    @if($confirmDelete === 'all')
                    <button type="button" class="btn btn-danger" wire:click="clearAllData"
                            {{ $deleteConfirmText !== 'SUPPRIMER TOUT' ? 'disabled' : '' }}>
                        <i class="bi bi-radioactive me-1"></i>Supprimer Tout
                    </button>
                    @else
                    <button type="button" class="btn btn-danger" wire:click="clearSelectedTables"
                            {{ $deleteConfirmText !== 'SUPPRIMER' ? 'disabled' : '' }}>
                        <i class="bi bi-trash me-1"></i>Supprimer
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

