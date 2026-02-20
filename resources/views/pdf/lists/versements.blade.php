@extends('pdf.lists.layout')

@section('content')
    @if(isset($filtres) && (($filtres['search'] ?? '') || ($filtres['statut'] ?? '') || ($filtres['mode'] ?? '') || ($filtres['date_from'] ?? '') || ($filtres['date_to'] ?? '')))
        <div class="filters-info">
            <strong>Filtres appliqués:</strong>
            @if($filtres['search'] ?? '') Recherche: "{{ $filtres['search'] }}" @endif
            @if($filtres['statut'] ?? '') | Statut: {{ ucfirst($filtres['statut']) }} @endif
            @if($filtres['mode'] ?? '') | Mode: {{ ucfirst(str_replace('_', ' ', $filtres['mode'])) }} @endif
            @if($filtres['date_from'] ?? '') | Du: {{ $filtres['date_from'] }} @endif
            @if($filtres['date_to'] ?? '') | Au: {{ $filtres['date_to'] }} @endif
        </div>
    @endif

    @if(isset($stats))
        <div class="stats-row">
            <div class="stat-item">
                <div class="value">{{ number_format($stats['total'] ?? 0) }}</div>
                <div class="label">Total</div>
            </div>
            <div class="stat-item">
                <div class="value">{{ number_format($stats['total_montant'] ?? 0) }} FC</div>
                <div class="label">Montant Total</div>
            </div>
            <div class="stat-item">
                <div class="value">{{ number_format($stats['payes'] ?? 0) }}</div>
                <div class="label">Payés</div>
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
                <th style="width: 8%">Date</th>
                <th style="width: 15%">Motard</th>
                <th style="width: 12%">Moto</th>
                <th style="width: 12%" class="text-right">Montant</th>
                <th style="width: 10%">Mode</th>
                <th style="width: 10%">Statut</th>
                <th style="width: 15%">Caissier</th>
                <th style="width: 18%">Référence</th>
            </tr>
        </thead>
        <tbody>
            @forelse($versements as $versement)
                <tr class="no-break">
                    <td>{{ \Carbon\Carbon::parse($versement->date_versement)->format('d/m/Y') }}</td>
                    <td>{{ $versement->motard?->user?->name ?? '-' }}</td>
                    <td>{{ $versement->moto?->plaque_immatriculation ?? '-' }}</td>
                    <td class="amount">{{ number_format($versement->montant ?? 0) }} FC</td>
                    <td>
                        @php
                            $modes = [
                                'cash' => 'Cash',
                                'mobile_money' => 'Mobile Money',
                                'depot' => 'Dépôt',
                                'mpesa' => 'M-Pesa',
                                'airtel_money' => 'Airtel Money',
                                'orange_money' => 'Orange Money',
                            ];
                        @endphp
                        {{ $modes[$versement->mode_paiement] ?? ucfirst($versement->mode_paiement ?? '-') }}
                    </td>
                    <td>
                        @php
                            $badgeClass = match($versement->statut) {
                                'paye' => 'badge-success',
                                'en_retard' => 'badge-danger',
                                'partiel' => 'badge-warning',
                                default => 'badge-secondary'
                            };
                        @endphp
                        <span class="badge {{ $badgeClass }}">{{ ucfirst(str_replace('_', ' ', $versement->statut ?? '-')) }}</span>
                    </td>
                    <td>{{ $versement->caissier?->user?->name ?? '-' }}</td>
                    <td style="font-size: 8px;">{{ $versement->reference ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center">Aucun versement trouvé</td>
                </tr>
            @endforelse
        </tbody>
        @if($versements->count() > 0)
            <tfoot>
                <tr class="total-row">
                    <td colspan="3"><strong>TOTAL</strong></td>
                    <td class="amount"><strong>{{ number_format($versements->sum('montant') ?? 0) }} FC</strong></td>
                    <td colspan="4"></td>
                </tr>
            </tfoot>
        @endif
    </table>
@endsection

