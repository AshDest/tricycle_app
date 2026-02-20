@extends('pdf.lists.layout')

@section('content')
    @if(isset($filtres) && (($filtres['search'] ?? '') || ($filtres['statut'] ?? '') || ($filtres['maintenance'] ?? '')))
        <div class="filters-info">
            <strong>Filtres appliqués:</strong>
            @if($filtres['search'] ?? '') Recherche: "{{ $filtres['search'] }}" @endif
            @if($filtres['statut'] ?? '') | Statut: {{ ucfirst($filtres['statut']) }} @endif
            @if($filtres['maintenance'] ?? '') | Maintenance: {{ ucfirst(str_replace('_', ' ', $filtres['maintenance'])) }} @endif
        </div>
    @endif

    @if(isset($stats))
        <div class="stats-row">
            <div class="stat-item">
                <div class="value">{{ number_format($stats['total'] ?? 0) }}</div>
                <div class="label">Total Motos</div>
            </div>
            <div class="stat-item">
                <div class="value">{{ number_format($stats['avec_maintenance_prevue'] ?? 0) }}</div>
                <div class="label">Maintenance Prévue</div>
            </div>
            <div class="stat-item">
                <div class="value">{{ number_format($stats['urgentes'] ?? 0) }}</div>
                <div class="label">Urgentes (7j)</div>
            </div>
            <div class="stat-item">
                <div class="value">{{ number_format($stats['en_retard'] ?? 0) }}</div>
                <div class="label">En Retard</div>
            </div>
        </div>
    @endif

    <table>
        <thead>
            <tr>
                <th style="width: 12%">Plaque</th>
                <th style="width: 15%">Propriétaire</th>
                <th style="width: 13%">Motard</th>
                <th style="width: 10%">Statut</th>
                <th style="width: 12%">Dernière Maint.</th>
                <th style="width: 12%">Type</th>
                <th style="width: 12%">Prochaine Maint.</th>
                <th style="width: 7%">Jours</th>
                <th style="width: 7%" class="text-right">Tarif/J</th>
            </tr>
        </thead>
        <tbody>
            @forelse($motos as $item)
                @php
                    $moto = $item['moto'];
                    $prochaine = $item['prochaine_maintenance'];
                    $derniere = $item['derniere_maintenance'];
                    $joursRestants = $item['jours_restants'];
                @endphp
                <tr class="no-break">
                    <td>{{ $moto->plaque_immatriculation ?? '-' }}</td>
                    <td>{{ $moto->proprietaire?->user?->name ?? '-' }}</td>
                    <td>{{ $moto->motard?->user?->name ?? 'Non assigné' }}</td>
                    <td>
                        @php
                            $badgeClass = match($moto->statut) {
                                'actif' => 'badge-success',
                                'inactif' => 'badge-danger',
                                'en_maintenance' => 'badge-warning',
                                default => 'badge-secondary'
                            };
                        @endphp
                        <span class="badge {{ $badgeClass }}">{{ ucfirst($moto->statut ?? '-') }}</span>
                    </td>
                    <td>{{ $derniere?->date_intervention ? \Carbon\Carbon::parse($derniere->date_intervention)->format('d/m/Y') : '-' }}</td>
                    <td style="font-size: 8px;">{{ $derniere?->type ?? '-' }}</td>
                    <td>
                        @if($prochaine?->prochain_entretien)
                            {{ \Carbon\Carbon::parse($prochaine->prochain_entretien)->format('d/m/Y') }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-center">
                        @if($joursRestants !== null)
                            @if($joursRestants < 0)
                                <span class="badge badge-danger">{{ abs($joursRestants) }}j retard</span>
                            @elseif($joursRestants <= 7)
                                <span class="badge badge-warning">{{ $joursRestants }}j</span>
                            @else
                                <span class="badge badge-success">{{ $joursRestants }}j</span>
                            @endif
                        @else
                            -
                        @endif
                    </td>
                    <td class="amount">{{ number_format($moto->tarif_journalier ?? 0) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center">Aucune moto trouvée</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection

