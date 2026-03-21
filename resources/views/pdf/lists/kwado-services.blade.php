@extends('pdf.lists.layout')

@section('content')
    <table>
        <thead>
            <tr>
                <th style="width: 13%">N° Service</th>
                <th style="width: 10%">Date</th>
                <th style="width: 12%">Véhicule</th>
                <th style="width: 16%">Type Service</th>
                <th style="width: 10%">Position</th>
                <th style="width: 12%">Montant</th>
                <th style="width: 10%">Pièces</th>
                <th style="width: 9%">Mode</th>
                <th style="width: 8%">Statut</th>
            </tr>
        </thead>
        <tbody>
            @forelse($services as $service)
                <tr class="no-break">
                    <td>{{ $service->numero_service }}</td>
                    <td>{{ $service->date_service?->format('d/m/Y') }}</td>
                    <td>
                        {{ $service->plaque }}
                        @if($service->is_externe)
                        <small style="color: #6c757d;">(Ext)</small>
                        @endif
                    </td>
                    <td>{{ $service->type_service_label }}</td>
                    <td>{{ $service->position_pneu_label ?: '-' }}</td>
                    <td class="amount">{{ number_format($service->montant_encaisse ?? 0) }} FC</td>
                    <td>{{ ($service->cout_pieces ?? 0) > 0 ? number_format($service->cout_pieces) . ' FC' : '-' }}</td>
                    <td>{{ $service->mode_paiement === 'cash' ? 'Cash' : 'Mobile' }}</td>
                    <td>
                        <span style="color: {{ $service->statut_paiement === 'payé' ? '#198754' : '#dc3545' }}; font-weight: bold;">
                            {{ ucfirst($service->statut_paiement) }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align: center; padding: 20px;">Aucun service KWADO enregistré</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if(isset($totalEncaisse) && $totalEncaisse > 0)
    <div style="text-align: right; margin-top: 10px; font-size: 12px; font-weight: bold;">
        Total encaissé: {{ number_format($totalEncaisse) }} FC
    </div>
    @endif
@endsection
