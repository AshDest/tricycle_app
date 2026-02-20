@extends('pdf.lists.layout')

@section('content')
    @if(isset($filtres) && (($filtres['search'] ?? '') || ($filtres['zone'] ?? '')))
        <div class="filters-info">
            <strong>Filtres appliqués:</strong>
            @if($filtres['search'] ?? '') Recherche: "{{ $filtres['search'] }}" @endif
            @if($filtres['zone'] ?? '') | Zone: {{ $filtres['zone'] }} @endif
        </div>
    @endif

    @if(isset($stats))
        <div class="stats-row">
            <div class="stat-item">
                <div class="value">{{ number_format($stats['total'] ?? 0) }}</div>
                <div class="label">Total Caissiers</div>
            </div>
            <div class="stat-item">
                <div class="value">{{ number_format($stats['actifs'] ?? 0) }}</div>
                <div class="label">Actifs</div>
            </div>
            <div class="stat-item">
                <div class="value">{{ number_format($stats['zones'] ?? 0) }}</div>
                <div class="label">Zones Couvertes</div>
            </div>
        </div>
    @endif

    <table>
        <thead>
            <tr>
                <th style="width: 5%">#</th>
                <th style="width: 25%">Nom</th>
                <th style="width: 15%">Téléphone</th>
                <th style="width: 25%">Email</th>
                <th style="width: 15%">Zone</th>
                <th style="width: 15%">Statut</th>
            </tr>
        </thead>
        <tbody>
            @forelse($caissiers as $index => $caissier)
                <tr class="no-break">
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $caissier->user?->name ?? '-' }}</td>
                    <td>{{ $caissier->user?->telephone ?? $caissier->telephone ?? '-' }}</td>
                    <td style="font-size: 8px;">{{ $caissier->user?->email ?? '-' }}</td>
                    <td>{{ $caissier->zone ?? '-' }}</td>
                    <td>
                        <span class="badge {{ ($caissier->is_active ?? true) ? 'badge-success' : 'badge-danger' }}">
                            {{ ($caissier->is_active ?? true) ? 'Actif' : 'Inactif' }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">Aucun caissier trouvé</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection

