<div>
    <!-- Header -->
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">🏆 Gestion des Récompenses</h4>
            <p class="text-muted mb-0">Gérer les récompenses attribuées aux motards</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.recompenses.classement') }}" class="btn btn-outline-primary">
                <i class="bi bi-bar-chart me-1"></i> Classement
            </a>
            <a href="{{ route('admin.recompenses.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Nouvelle Récompense
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Statistiques -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h6 class="card-title mb-1">Total Récompenses</h6>
                    <h3 class="mb-0">{{ $stats['total'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <h6 class="card-title mb-1">À Remettre</h6>
                    <h3 class="mb-0">{{ $stats['attribuees'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6 class="card-title mb-1">Remises</h6>
                    <h3 class="mb-0">{{ $stats['remises'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h6 class="card-title mb-1">Primes Distribuées</h6>
                    <h3 class="mb-0">{{ number_format($stats['montant_primes']) }} FC</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Rechercher un motard...">
                </div>
                <div class="col-md-3">
                    <select wire:model.live="statutFilter" class="form-select">
                        <option value="">Tous les statuts</option>
                        @foreach($statuts as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select wire:model.live="typeFilter" class="form-select">
                        <option value="">Tous les types</option>
                        @foreach($types as $key => $type)
                            <option value="{{ $key }}">{{ $type['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select wire:model.live="categorieFilter" class="form-select">
                        <option value="">Catégorie</option>
                        @foreach($categories as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des récompenses -->
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Type</th>
                        <th>Motard</th>
                        <th>Titre</th>
                        <th class="text-center">Score</th>
                        <th>Période</th>
                        <th>Prime</th>
                        <th>Statut</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recompenses as $recompense)
                    <tr>
                        <td>
                            <span class="badge" style="background-color: {{ $recompense->badge_color }}">
                                <i class="bi bi-{{ $recompense->badge_icon }} me-1"></i>
                                {{ $recompense->type_label }}
                            </span>
                        </td>
                        <td>
                            <div class="fw-medium">{{ $recompense->motard?->user?->name ?? 'N/A' }}</div>
                            <small class="text-muted">{{ $recompense->motard?->identifiant ?? '' }}</small>
                        </td>
                        <td>
                            <div>{{ $recompense->titre }}</div>
                            @if($recompense->description)
                                <small class="text-muted">{{ Str::limit($recompense->description, 50) }}</small>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge bg-{{ $recompense->score_total >= 85 ? 'success' : ($recompense->score_total >= 70 ? 'primary' : 'warning') }}">
                                {{ $recompense->score_total }}/100
                            </span>
                        </td>
                        <td>
                            <small>
                                {{ $recompense->periode_debut?->format('d/m/Y') }}<br>
                                au {{ $recompense->periode_fin?->format('d/m/Y') }}
                            </small>
                        </td>
                        <td>
                            @if($recompense->montant_prime)
                                <span class="text-success fw-medium">{{ number_format($recompense->montant_prime) }} FC</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($recompense->statut === 'attribue')
                                <span class="badge bg-warning text-dark">À remettre</span>
                            @elseif($recompense->statut === 'remis')
                                <span class="badge bg-success">
                                    Remis
                                    @if($recompense->date_remise)
                                        <br><small>{{ $recompense->date_remise->format('d/m/Y') }}</small>
                                    @endif
                                </span>
                            @else
                                <span class="badge bg-danger">Annulé</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                @if($recompense->statut === 'attribue')
                                    <button wire:click="ouvrirModalRemise({{ $recompense->id }})" class="btn btn-success btn-sm" title="Marquer comme remis">
                                        <i class="bi bi-check-lg"></i>
                                    </button>
                                    <button wire:click="annuler({{ $recompense->id }})" class="btn btn-warning btn-sm" title="Annuler"
                                            onclick="return confirm('Annuler cette récompense ?')">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                @endif
                                <button wire:click="supprimer({{ $recompense->id }})" class="btn btn-danger btn-sm" title="Supprimer"
                                        onclick="return confirm('Supprimer définitivement cette récompense ?')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">
                            <i class="bi bi-trophy display-6 d-block mb-2"></i>
                            Aucune récompense enregistrée.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($recompenses->hasPages())
        <div class="card-footer">
            {{ $recompenses->links() }}
        </div>
        @endif
    </div>

    <!-- Modal de remise -->
    @if($showRemiseModal)
    <div class="modal fade show d-block" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmer la Remise</h5>
                    <button type="button" class="btn-close" wire:click="$set('showRemiseModal', false)"></button>
                </div>
                <div class="modal-body">
                    <p>Confirmer que cette récompense a été remise au motard ?</p>
                    <div class="mb-3">
                        <label class="form-label">Notes (optionnel)</label>
                        <textarea wire:model="notesRemise" class="form-control" rows="2" placeholder="Commentaires sur la remise..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="$set('showRemiseModal', false)">Annuler</button>
                    <button type="button" class="btn btn-success" wire:click="confirmerRemise">
                        <i class="bi bi-check-lg me-1"></i> Confirmer
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

