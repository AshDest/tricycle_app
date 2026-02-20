@extends('pdf.lists.layout')

@section('content')
    @if(isset($stats))
        <div class="stats-row">
            <div class="stat-item">
                <div class="value">{{ number_format($stats['total'] ?? 0) }}</div>
                <div class="label">Total Zones</div>
            </div>
            <div class="stat-item">
                <div class="value">{{ number_format($stats['actives'] ?? 0) }}</div>
                <div class="label">Actives</div>
            </div>
            <div class="stat-item">
                <div class="value">{{ number_format($stats['motards'] ?? 0) }}</div>
                <div class="label">Motards Assignés</div>
            </div>
        </div>
    @endif

    <table>
        <thead>
            <tr>
                <th style="width: 5%">#</th>
                <th style="width: 25%">Nom</th>
                <th style="width: 35%">Description</th>
                <th style="width: 15%">Motards</th>
                <th style="width: 20%">Statut</th>
            </tr>
        </thead>
        <tbody>
            @forelse($zones as $index => $zone)
                <tr class="no-break">
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $zone->nom ?? '-' }}</td>
                    <td style="font-size: 8px;">{{ \Str::limit($zone->description ?? '', 50) }}</td>
                    <td class="text-center">{{ $zone->motards_count ?? 0 }}</td>
                    <td>
                        <span class="badge {{ ($zone->is_active ?? true) ? 'badge-success' : 'badge-danger' }}">
                            {{ ($zone->is_active ?? true) ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">Aucune zone trouvée</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection

