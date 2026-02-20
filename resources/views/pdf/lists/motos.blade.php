@extends('pdf.lists.layout')

@section('content')
    @if(isset($filtres) && (($filtres['search'] ?? '') || ($filtres['statut'] ?? '')))
        <div class="filters-info">
            <strong>Filtres appliqués:</strong>
            @if($filtres['search'] ?? '') Recherche: "{{ $filtres['search'] }}" @endif
            @if($filtres['statut'] ?? '') | Statut: {{ ucfirst($filtres['statut']) }} @endif
        </div>
    @endif

    @if(isset($stats))
        <div class="stats-row">
            <div class="stat-item">
                <div class="value">{{ number_format($stats['total'] ?? 0) }}</div>
                <div class="label">Total Motos</div>
            </div>
            <div class="stat-item">
                <div class="value">{{ number_format($stats['actives'] ?? 0) }}</div>
                <div class="label">Actives</div>
            </div>
            <div class="stat-item">
                <div class="value">{{ number_format($stats['inactives'] ?? 0) }}</div>
                <div class="label">Inactives</div>
            </div>
            <div class="stat-item">
                <div class="value">{{ number_format($stats['en_maintenance'] ?? 0) }}</div>
                <div class="label">En Maintenance</div>
            </div>
        </div>
    @endif

    <table>
        <thead>
            <tr>
                <th style="width: 12%">Plaque</th>
                <th style="width: 12%">Matricule</th>
                <th style="width: 18%">Propriétaire</th>
                <th style="width: 15%">Motard</th>
                <th style="width: 10%">Statut</th>
                <th style="width: 12%">Contrat Début</th>
                <th style="width: 12%">Contrat Fin</th>
                <th style="width: 9%" class="text-right">Tarif/Jour</th>
            </tr>
        </thead>
        <tbody>
            @forelse($motos as $moto)
                <tr class="no-break">
                    <td>{{ $moto->plaque_immatriculation ?? '-' }}</td>
                    <td>{{ $moto->numero_matricule ?? '-' }}</td>
                    <td>{{ $moto->proprietaire?->user?->name ?? '-' }}</td>
                    <td>{{ $moto->motard?->user?->name ?? 'Non assigné' }}</td>
                    <td>
                        @php
                            $badgeClass = match($moto->statut) {
                                'actif' => 'badge-success',
                                'inactif' => 'badge-danger',
                                'en_maintenance' => 'badge-warning',
                                'en_attente' => 'badge-info',
                                default => 'badge-secondary'
                            };
                        @endphp
                        <span class="badge {{ $badgeClass }}">{{ ucfirst(str_replace('_', ' ', $moto->statut ?? '-')) }}</span>
                    </td>
                    <td>{{ $moto->contrat_debut ? \Carbon\Carbon::parse($moto->contrat_debut)->format('d/m/Y') : '-' }}</td>
                    <td>{{ $moto->contrat_fin ? \Carbon\Carbon::parse($moto->contrat_fin)->format('d/m/Y') : '-' }}</td>
                    <td class="amount">{{ number_format($moto->tarif_journalier ?? 0) }} FC</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center">Aucune moto trouvée</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection

