<div>
    <!-- Header -->
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">🏆 Classement des Performances</h4>
            <p class="text-muted mb-0">Performance des motards pour {{ $moisOptions[$mois] ?? '' }} {{ $annee }}</p>
        </div>
        <div class="d-flex gap-2">
            <button wire:click="calculerPerformances" class="btn btn-outline-primary">
                <i class="bi bi-calculator me-1"></i> Calculer
            </button>
            <button wire:click="attribuerRecompenses" class="btn btn-success">
                <i class="bi bi-award me-1"></i> Attribuer Récompenses
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Statistiques globales -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title mb-1">Score Moyen</h6>
                            <h3 class="mb-0">{{ $statistiques['moyenne_score'] }}/100</h3>
                        </div>
                        <div class="display-6 opacity-50"><i class="bi bi-graph-up"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title mb-1">Badges Diamant</h6>
                            <h3 class="mb-0">{{ $statistiques['badges']['diamant'] ?? 0 }}</h3>
                        </div>
                        <div class="display-6 opacity-50"><i class="bi bi-gem"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title mb-1">Badges Or</h6>
                            <h3 class="mb-0">{{ $statistiques['badges']['or'] ?? 0 }}</h3>
                        </div>
                        <div class="display-6 opacity-50"><i class="bi bi-award-fill"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title mb-1">Accidents</h6>
                            <h3 class="mb-0">{{ $statistiques['total_accidents'] ?? 0 }}</h3>
                        </div>
                        <div class="display-6 opacity-50"><i class="bi bi-exclamation-triangle"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top 5 Motards -->
    @if($topMotards->count() > 0)
    <div class="card mb-4">
        <div class="card-header bg-gradient bg-warning text-dark">
            <h6 class="mb-0"><i class="bi bi-trophy me-2"></i>Top 5 Motards du Mois</h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                @foreach($topMotards as $index => $top)
                <div class="col-md">
                    <div class="card h-100 border-{{ $index === 0 ? 'warning' : ($index === 1 ? 'secondary' : 'light') }}">
                        <div class="card-body text-center">
                            <div class="mb-2">
                                @if($index === 0)
                                    <span class="badge bg-warning text-dark fs-5">🥇 1er</span>
                                @elseif($index === 1)
                                    <span class="badge bg-secondary fs-6">🥈 2e</span>
                                @elseif($index === 2)
                                    <span class="badge bg-warning text-dark fs-6" style="background-color: #CD7F32 !important;">🥉 3e</span>
                                @else
                                    <span class="badge bg-light text-dark">{{ $index + 1 }}e</span>
                                @endif
                            </div>
                            <h6 class="mb-1">{{ $top->motard?->user?->name ?? 'N/A' }}</h6>
                            <div class="fs-4 fw-bold {{ $top->score_class }}">{{ $top->score_total }}/100</div>
                            <span class="badge" style="background-color: {{ $top->badge_color }}">
                                {{ ucfirst($top->badge) }}
                            </span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Filtres -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Mois</label>
                    <select wire:model.live="mois" class="form-select">
                        @foreach($moisOptions as $num => $nom)
                            <option value="{{ $num }}">{{ $nom }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Année</label>
                    <select wire:model.live="annee" class="form-select">
                        @foreach($anneeOptions as $an)
                            <option value="{{ $an }}">{{ $an }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Badge</label>
                    <select wire:model.live="badgeFilter" class="form-select">
                        <option value="">Tous les badges</option>
                        <option value="diamant">💎 Diamant</option>
                        <option value="or">🥇 Or</option>
                        <option value="argent">🥈 Argent</option>
                        <option value="bronze">🥉 Bronze</option>
                        <option value="aucun">❌ Aucun</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Rechercher</label>
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Nom du motard...">
                </div>
            </div>
        </div>
    </div>

    <!-- Tableau du classement -->
    <div class="card">
        <div class="card-header">
            <h6 class="mb-0">Classement Complet</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="60">Rang</th>
                        <th>Motard</th>
                        <th class="text-center">Régularité</th>
                        <th class="text-center">Sécurité</th>
                        <th class="text-center">Versement</th>
                        <th class="text-center">Score Total</th>
                        <th class="text-center">Badge</th>
                        <th class="text-end">Arriérés</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($performances as $perf)
                    <tr>
                        <td>
                            @if($perf->rang_mensuel <= 3)
                                <span class="badge bg-{{ $perf->rang_mensuel == 1 ? 'warning' : ($perf->rang_mensuel == 2 ? 'secondary' : 'light text-dark') }} fs-6">
                                    {{ $perf->rang_mensuel == 1 ? '🥇' : ($perf->rang_mensuel == 2 ? '🥈' : '🥉') }}
                                </span>
                            @else
                                <span class="text-muted">#{{ $perf->rang_mensuel }}</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 36px; height: 36px;">
                                    {{ substr($perf->motard?->user?->name ?? '?', 0, 1) }}
                                </div>
                                <div>
                                    <div class="fw-medium">{{ $perf->motard?->user?->name ?? 'N/A' }}</div>
                                    <small class="text-muted">{{ $perf->jours_travailles }} jours travaillés</small>
                                </div>
                            </div>
                        </td>
                        <td class="text-center">
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar bg-{{ $perf->score_regularite >= 70 ? 'success' : ($perf->score_regularite >= 50 ? 'warning' : 'danger') }}"
                                     style="width: {{ $perf->score_regularite }}%">
                                    {{ $perf->score_regularite }}%
                                </div>
                            </div>
                        </td>
                        <td class="text-center">
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar bg-{{ $perf->score_securite >= 70 ? 'success' : ($perf->score_securite >= 50 ? 'warning' : 'danger') }}"
                                     style="width: {{ $perf->score_securite }}%">
                                    {{ $perf->score_securite }}%
                                </div>
                            </div>
                            @if($perf->accidents_total > 0)
                                <small class="text-danger">{{ $perf->accidents_total }} accident(s)</small>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar bg-{{ $perf->score_versement >= 70 ? 'success' : ($perf->score_versement >= 50 ? 'warning' : 'danger') }}"
                                     style="width: {{ $perf->score_versement }}%">
                                    {{ $perf->score_versement }}%
                                </div>
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-{{ $perf->score_total >= 85 ? 'success' : ($perf->score_total >= 70 ? 'primary' : ($perf->score_total >= 50 ? 'warning' : 'danger')) }} fs-6">
                                {{ $perf->score_total }}/100
                            </span>
                        </td>
                        <td class="text-center">
                            @if($perf->badge !== 'aucun')
                                <span class="badge" style="background-color: {{ $perf->badge_color }}; color: {{ in_array($perf->badge, ['or', 'argent']) ? '#000' : '#fff' }}">
                                    @if($perf->badge === 'diamant') 💎
                                    @elseif($perf->badge === 'or') 🥇
                                    @elseif($perf->badge === 'argent') 🥈
                                    @else 🥉
                                    @endif
                                    {{ ucfirst($perf->badge) }}
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-end">
                            @if($perf->arrieres_cumules > 0)
                                <span class="text-danger">{{ number_format($perf->arrieres_cumules) }} FC</span>
                            @else
                                <span class="text-success">0 FC</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">
                            <i class="bi bi-inbox display-6 d-block mb-2"></i>
                            Aucune performance enregistrée pour cette période.
                            <br>
                            <button wire:click="calculerPerformances" class="btn btn-primary btn-sm mt-2">
                                Calculer les performances
                            </button>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($performances->hasPages())
        <div class="card-footer">
            {{ $performances->links() }}
        </div>
        @endif
    </div>
</div>

