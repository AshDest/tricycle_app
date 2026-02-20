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
                <div class="label">Total Accidents</div>
            </div>
            <div class="stat-item">
                <div class="value">{{ number_format($stats['total_cout'] ?? 0) }} FC</div>
                <div class="label">Coût Total</div>
            </div>
            <div class="stat-item">
                <div class="value">{{ number_format($stats['repares'] ?? 0) }}</div>
                <div class="label">Réparés</div>
            </div>
            <div class="stat-item">
                <div class="value">{{ number_format($stats['en_attente'] ?? 0) }}</div>
                <div class="label">En Attente</div>
            </div>
        </div>
    @endif

    <table>
        <thead>
            <tr>
                <th style="width: 10%">Date</th>
                <th style="width: 12%">Moto</th>
                <th style="width: 12%">Motard</th>
                <th style="width: 15%">Lieu</th>
                <th style="width: 18%">Description</th>
                <th style="width: 12%" class="text-right">Coût Estimé</th>
                <th style="width: 10%">Statut</th>
                <th style="width: 11%">Prise en Charge</th>
            </tr>
        </thead>
        <tbody>
            @forelse($accidents as $accident)
                <tr class="no-break">
                    <td>{{ $accident->date_heure ? \Carbon\Carbon::parse($accident->date_heure)->format('d/m/Y') : '-' }}</td>
                    <td>{{ $accident->moto?->plaque_immatriculation ?? '-' }}</td>
                    <td>{{ $accident->motard?->user?->name ?? '-' }}</td>
                    <td style="font-size: 8px;">{{ \Str::limit($accident->lieu ?? '', 25) }}</td>
                    <td style="font-size: 8px;">{{ \Str::limit($accident->description ?? '', 30) }}</td>
                    <td class="amount">{{ number_format($accident->cout_estime ?? 0) }} FC</td>
                    <td>
                        @php
                            $badgeClass = match($accident->statut) {
                                'repare' => 'badge-success',
                                'en_reparation' => 'badge-warning',
                                'en_attente', 'declare' => 'badge-info',
                                default => 'badge-secondary'
                            };
                        @endphp
                        <span class="badge {{ $badgeClass }}">{{ ucfirst(str_replace('_', ' ', $accident->statut ?? '-')) }}</span>
                    </td>
                    <td style="font-size: 8px;">{{ ucfirst($accident->prise_en_charge ?? '-') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center">Aucun accident trouvé</td>
                </tr>
            @endforelse
        </tbody>
        @if(($accidents->count() ?? 0) > 0)
            <tfoot>
                <tr class="total-row">
                    <td colspan="5"><strong>TOTAL</strong></td>
                    <td class="amount"><strong>{{ number_format($accidents->sum('cout_estime') ?? 0) }} FC</strong></td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
        @endif
    </table>
@endsection

