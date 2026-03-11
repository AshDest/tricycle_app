<div>
    <!-- Header -->
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">🏆 Récompenses des Motards</h4>
            <p class="text-muted mb-0">Historique des récompenses attribuées</p>
        </div>
        <a href="{{ route('supervisor.recompenses.classement') }}" class="btn btn-primary">
            <i class="bi bi-bar-chart me-1"></i> Classement
        </a>
    </div>

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
                <div class="col-md-5">
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Rechercher un motard...">
                </div>
                <div class="col-md-4">
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
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
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
</div>

