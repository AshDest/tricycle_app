@extends('pdf.reports.layout')

@section('content')
    <!-- Liste des versements -->
    @if(isset($stats['derniersVersements']) && count($stats['derniersVersements']) > 0)
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Motard</th>
                <th>Moto</th>
                <th class="text-right">Versé</th>
                <th class="text-right">Attendu</th>
                <th class="text-center">Statut</th>
            </tr>
        </thead>
        <tbody>
            @php $total = 0; $totalAttendu = 0; @endphp
            @foreach($stats['derniersVersements'] as $i => $v)
            @php $total += $v->montant ?? 0; $totalAttendu += $v->montant_attendu ?? 0; @endphp
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $v->motard->user->name ?? 'N/A' }}</td>
                <td>{{ $v->moto->plaque_immatriculation ?? $v->moto->numero_matricule ?? 'N/A' }}</td>
                <td class="amount">{{ number_format($v->montant ?? 0) }} FC</td>
                <td class="amount">{{ number_format($v->montant_attendu ?? 0) }} FC</td>
                <td class="text-center">
                    @php
                        $badge = match($v->statut) {
                            'payé', 'paye' => 'badge-success',
                            'en_retard' => 'badge-danger',
                            default => 'badge-warning'
                        };
                    @endphp
                    <span class="badge {{ $badge }}">{{ ucfirst(str_replace('_', ' ', $v->statut ?? 'N/A')) }}</span>
                </td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="3"><strong>TOTAL</strong></td>
                <td class="amount"><strong>{{ number_format($total) }} FC</strong></td>
                <td class="amount"><strong>{{ number_format($totalAttendu) }} FC</strong></td>
                <td></td>
            </tr>
        </tbody>
    </table>
    @else
    <p style="text-align: center; padding: 20px; color: #666;">Aucun versement enregistré pour cette date.</p>
    @endif
@endsection
