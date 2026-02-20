@extends('pdf.lists.layout')

@section('content')
    @if(isset($filtres) && (($filtres['search'] ?? '')))
        <div class="filters-info">
            <strong>Filtres appliqués:</strong>
            @if($filtres['search'] ?? '') Recherche: "{{ $filtres['search'] }}" @endif
        </div>
    @endif

    @if(isset($stats))
        <div class="stats-row">
            <div class="stat-item">
                <div class="value">{{ number_format($stats['total'] ?? 0) }}</div>
                <div class="label">Total Propriétaires</div>
            </div>
            <div class="stat-item">
                <div class="value">{{ number_format($stats['total_motos'] ?? 0) }}</div>
                <div class="label">Total Motos</div>
            </div>
            <div class="stat-item">
                <div class="value">{{ number_format($stats['total_du'] ?? 0) }} FC</div>
                <div class="label">Total Dû</div>
            </div>
            <div class="stat-item">
                <div class="value">{{ number_format($stats['total_paye'] ?? 0) }} FC</div>
                <div class="label">Total Payé</div>
            </div>
        </div>
    @endif

    <table>
        <thead>
            <tr>
                <th style="width: 20%">Nom</th>
                <th style="width: 15%">Téléphone</th>
                <th style="width: 20%">Email</th>
                <th style="width: 10%">Motos</th>
                <th style="width: 15%" class="text-right">Total Dû</th>
                <th style="width: 10%">Statut</th>
                <th style="width: 10%">Inscription</th>
            </tr>
        </thead>
        <tbody>
            @forelse($proprietaires as $proprietaire)
                <tr class="no-break">
                    <td>{{ $proprietaire->user?->name ?? '-' }}</td>
                    <td>{{ $proprietaire->user?->telephone ?? $proprietaire->telephone ?? '-' }}</td>
                    <td style="font-size: 8px;">{{ $proprietaire->user?->email ?? '-' }}</td>
                    <td class="text-center">{{ $proprietaire->motos_count ?? $proprietaire->motos->count() ?? 0 }}</td>
                    <td class="amount">{{ number_format($proprietaire->total_du ?? 0) }} FC</td>
                    <td>
                        <span class="badge {{ ($proprietaire->is_active ?? true) ? 'badge-success' : 'badge-danger' }}">
                            {{ ($proprietaire->is_active ?? true) ? 'Actif' : 'Inactif' }}
                        </span>
                    </td>
                    <td>{{ $proprietaire->created_at?->format('d/m/Y') ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">Aucun propriétaire trouvé</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection

