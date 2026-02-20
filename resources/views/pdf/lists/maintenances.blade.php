@extends('pdf.lists.layout')

@section('content')
    @if(isset($filtres) && (($filtres['search'] ?? '') || ($filtres['type'] ?? '') || ($filtres['statut'] ?? '')))
        <div class="filters-info">
            <strong>Filtres appliqués:</strong>
            @if($filtres['search'] ?? '') Recherche: "{{ $filtres['search'] }}" @endif
            @if($filtres['type'] ?? '') | Type: {{ ucfirst($filtres['type']) }} @endif
            @if($filtres['statut'] ?? '') | Statut: {{ ucfirst($filtres['statut']) }} @endif
        </div>
    @endif

    @if(isset($stats))
        <div class="stats-row">
            <div class="stat-item">
                <div class="value">{{ number_format($stats['total'] ?? 0) }}</div>
                <div class="label">Total Maintenances</div>
            </div>
            <div class="stat-item">
                <div class="value">{{ number_format($stats['total_cout'] ?? 0) }} FC</div>
                <div class="label">Coût Total</div>
            </div>
            <div class="stat-item">
                <div class="value">{{ number_format($stats['terminees'] ?? 0) }}</div>
                <div class="label">Terminées</div>
            </div>
            <div class="stat-item">
                <div class="value">{{ number_format($stats['en_cours'] ?? 0) }}</div>
                <div class="label">En Cours</div>
            </div>
        </div>
    @endif

    <table>
        <thead>
            <tr>
                <th style="width: 10%">Date</th>
                <th style="width: 12%">Moto</th>
                <th style="width: 12%">Type</th>
                <th style="width: 18%">Description</th>
                <th style="width: 12%" class="text-right">Coût Total</th>
                <th style="width: 10%">Statut</th>
                <th style="width: 14%">Prochain Entretien</th>
                <th style="width: 12%">Technicien</th>
            </tr>
        </thead>
        <tbody>
            @forelse($maintenances as $maintenance)
                <tr class="no-break">
                    <td>{{ $maintenance->date_intervention ? \Carbon\Carbon::parse($maintenance->date_intervention)->format('d/m/Y') : '-' }}</td>
                    <td>{{ $maintenance->moto?->plaque_immatriculation ?? '-' }}</td>
                    <td>
                        @php
                            $types = [
                                'preventive' => 'Préventive',
                                'corrective' => 'Corrective',
                                'remplacement' => 'Remplacement',
                            ];
                        @endphp
                        {{ $types[$maintenance->type_maintenance] ?? ucfirst($maintenance->type_maintenance ?? '-') }}
                    </td>
                    <td style="font-size: 8px;">{{ \Str::limit($maintenance->description ?? '', 40) }}</td>
                    <td class="amount">{{ number_format($maintenance->cout_total ?? 0) }} FC</td>
                    <td>
                        @php
                            $badgeClass = match($maintenance->statut) {
                                'termine' => 'badge-success',
                                'en_cours' => 'badge-warning',
                                'en_attente' => 'badge-info',
                                default => 'badge-secondary'
                            };
                        @endphp
                        <span class="badge {{ $badgeClass }}">{{ ucfirst(str_replace('_', ' ', $maintenance->statut ?? '-')) }}</span>
                    </td>
                    <td>{{ $maintenance->prochain_entretien ? \Carbon\Carbon::parse($maintenance->prochain_entretien)->format('d/m/Y') : '-' }}</td>
                    <td>{{ $maintenance->technicien ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center">Aucune maintenance trouvée</td>
                </tr>
            @endforelse
        </tbody>
        @if(($maintenances->count() ?? 0) > 0)
            <tfoot>
                <tr class="total-row">
                    <td colspan="4"><strong>TOTAL</strong></td>
                    <td class="amount"><strong>{{ number_format($maintenances->sum('cout_total') ?? 0) }} FC</strong></td>
                    <td colspan="3"></td>
                </tr>
            </tfoot>
        @endif
    </table>
@endsection

