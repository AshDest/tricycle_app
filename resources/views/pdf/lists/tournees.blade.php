@extends('pdf.lists.layout')

@section('content')
    @if(isset($filtres) && (($filtres['search'] ?? '') || ($filtres['date'] ?? '') || ($filtres['statut'] ?? '')))
        <div class="filters-info">
            <strong>Filtres appliqués:</strong>
            @if($filtres['search'] ?? '') Recherche: "{{ $filtres['search'] }}" @endif
            @if($filtres['date'] ?? '') | Date: {{ $filtres['date'] }} @endif
            @if($filtres['statut'] ?? '') | Statut: {{ ucfirst($filtres['statut']) }} @endif
        </div>
    @endif

    @if(isset($stats))
        <div class="stats-row">
            <div class="stat-item">
                <div class="value">{{ number_format($stats['total'] ?? 0) }}</div>
                <div class="label">Total Tournées</div>
            </div>
            <div class="stat-item">
                <div class="value">{{ number_format($stats['terminees'] ?? 0) }}</div>
                <div class="label">Terminées</div>
            </div>
            <div class="stat-item">
                <div class="value">{{ number_format($stats['en_cours'] ?? 0) }}</div>
                <div class="label">En Cours</div>
            </div>
            <div class="stat-item">
                <div class="value">{{ number_format($stats['planifiees'] ?? 0) }}</div>
                <div class="label">Planifiées</div>
            </div>
        </div>
    @endif

    <table>
        <thead>
            <tr>
                <th style="width: 12%">Date</th>
                <th style="width: 18%">Collecteur</th>
                <th style="width: 12%">Zone</th>
                <th style="width: 10%">Statut</th>
                <th style="width: 12%" class="text-right">Montant Attendu</th>
                <th style="width: 12%" class="text-right">Montant Collecté</th>
                <th style="width: 12%">Début</th>
                <th style="width: 12%">Fin</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tournees as $tournee)
                <tr class="no-break">
                    <td>{{ $tournee->date ? \Carbon\Carbon::parse($tournee->date)->format('d/m/Y') : '-' }}</td>
                    <td>{{ $tournee->collecteur?->user?->name ?? '-' }}</td>
                    <td>{{ $tournee->zone ?? '-' }}</td>
                    <td>
                        @php
                            $badgeClass = match($tournee->statut) {
                                'terminee' => 'badge-success',
                                'en_cours' => 'badge-warning',
                                'confirmee', 'planifiee' => 'badge-info',
                                'annulee' => 'badge-danger',
                                default => 'badge-secondary'
                            };
                        @endphp
                        <span class="badge {{ $badgeClass }}">{{ ucfirst(str_replace('_', ' ', $tournee->statut ?? '-')) }}</span>
                    </td>
                    <td class="amount">{{ number_format($tournee->montant_attendu ?? 0) }} FC</td>
                    <td class="amount">{{ number_format($tournee->montant_collecte ?? 0) }} FC</td>
                    <td>{{ $tournee->heure_debut ? \Carbon\Carbon::parse($tournee->heure_debut)->format('H:i') : '-' }}</td>
                    <td>{{ $tournee->heure_fin ? \Carbon\Carbon::parse($tournee->heure_fin)->format('H:i') : '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center">Aucune tournée trouvée</td>
                </tr>
            @endforelse
        </tbody>
        @if(($tournees->count() ?? 0) > 0)
            <tfoot>
                <tr class="total-row">
                    <td colspan="4"><strong>TOTAL</strong></td>
                    <td class="amount"><strong>{{ number_format($tournees->sum('montant_attendu') ?? 0) }} FC</strong></td>
                    <td class="amount"><strong>{{ number_format($tournees->sum('montant_collecte') ?? 0) }} FC</strong></td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
        @endif
    </table>
@endsection

