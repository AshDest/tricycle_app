@extends('pdf.lists.layout')

@section('content')
    @if(isset($filtres) && (($filtres['search'] ?? '') || ($filtres['zone'] ?? '') || ($filtres['statut'] ?? '')))
        <div class="filters-info">
            <strong>Filtres appliqués:</strong>
            @if($filtres['search'] ?? '') Recherche: "{{ $filtres['search'] }}" @endif
            @if($filtres['zone'] ?? '') | Zone: {{ $filtres['zone'] }} @endif
            @if($filtres['statut'] ?? '') | Statut: {{ ucfirst($filtres['statut']) }} @endif
        </div>
    @endif

    @if(isset($stats))
        <div class="stats-row">
            <div class="stat-item">
                <div class="value">{{ number_format($stats['total'] ?? 0) }}</div>
                <div class="label">Total Motards</div>
            </div>
            <div class="stat-item">
                <div class="value">{{ number_format($stats['actifs'] ?? 0) }}</div>
                <div class="label">Actifs</div>
            </div>
            <div class="stat-item">
                <div class="value">{{ number_format($stats['inactifs'] ?? 0) }}</div>
                <div class="label">Inactifs</div>
            </div>
            <div class="stat-item">
                <div class="value">{{ number_format($stats['zones'] ?? 0) }}</div>
                <div class="label">Zones</div>
            </div>
        </div>
    @endif

    <table>
        <thead>
            <tr>
                <th style="width: 10%">ID</th>
                <th style="width: 20%">Nom</th>
                <th style="width: 15%">Téléphone</th>
                <th style="width: 15%">Zone</th>
                <th style="width: 15%">Moto Actuelle</th>
                <th style="width: 10%">Statut</th>
                <th style="width: 15%">Date Création</th>
            </tr>
        </thead>
        <tbody>
            @forelse($motards as $motard)
                <tr class="no-break">
                    <td>{{ $motard->numero_identifiant ?? '-' }}</td>
                    <td>{{ $motard->user?->name ?? '-' }}</td>
                    <td>{{ $motard->user?->telephone ?? $motard->telephone ?? '-' }}</td>
                    <td>{{ $motard->zone_affectation ?? '-' }}</td>
                    <td>{{ $motard->motoActuelle?->plaque_immatriculation ?? 'Non assigné' }}</td>
                    <td>
                        <span class="badge {{ $motard->is_active ? 'badge-success' : 'badge-danger' }}">
                            {{ $motard->is_active ? 'Actif' : 'Inactif' }}
                        </span>
                    </td>
                    <td>{{ $motard->created_at?->format('d/m/Y') ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">Aucun motard trouvé</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection

