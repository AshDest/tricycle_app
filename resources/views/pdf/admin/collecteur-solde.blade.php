@extends('pdf.lists.layout')

@section('content')
<div class="content">
    {{-- Soldes actuels --}}
    <div class="stats-row">
        <div class="stat-item">
            <div class="value">{{ number_format($soldeCaisse) }} FC</div>
            <div class="label">Solde Caisse Total</div>
        </div>
        <div class="stat-item">
            <div class="value">{{ number_format($soldePartProprietaire) }} FC</div>
            <div class="label">Part Propriétaire</div>
        </div>
        <div class="stat-item">
            <div class="value">{{ number_format($soldePartOkami) }} FC</div>
            <div class="label">Part OKAMI</div>
        </div>
    </div>

    {{-- Période --}}
    <div style="margin-bottom: 10px; font-size: 10px; color: #666;">
        <strong>Période:</strong> {{ \Carbon\Carbon::parse($dateDebut)->format('d/m/Y') }} au {{ \Carbon\Carbon::parse($dateFin)->format('d/m/Y') }}
        &nbsp;|&nbsp;
        <strong>Collectes:</strong> {{ number_format($totalCollectePeriode) }} FC
        &nbsp;|&nbsp;
        <strong>Paiements:</strong> {{ number_format($totalPaiementsPeriode) }} FC
    </div>

    {{-- Journal quotidien --}}
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th style="text-align: right;">Collectes</th>
                <th style="text-align: right;">Paiements</th>
                <th style="text-align: right;">Envois Mobile</th>
                <th style="text-align: right;">Retraits Mobile</th>
                <th style="text-align: right;">Total Entrées</th>
                <th style="text-align: right;">Total Sorties</th>
                <th style="text-align: right;">Solde Jour</th>
            </tr>
        </thead>
        <tbody>
            @forelse($journal as $jour)
            <tr>
                <td>{{ \Carbon\Carbon::parse($jour['date'])->format('d/m/Y') }}</td>
                <td style="text-align: right; color: #28a745;">{{ $jour['collectes'] > 0 ? number_format($jour['collectes']) : '-' }}</td>
                <td style="text-align: right; color: #dc3545;">{{ $jour['paiements'] > 0 ? number_format($jour['paiements']) : '-' }}</td>
                <td style="text-align: right; color: #ffc107;">{{ $jour['tx_envoi'] > 0 ? number_format($jour['tx_envoi']) : '-' }}</td>
                <td style="text-align: right; color: #17a2b8;">{{ $jour['tx_retrait'] > 0 ? number_format($jour['tx_retrait']) : '-' }}</td>
                <td style="text-align: right; font-weight: bold; color: #28a745;">{{ number_format($jour['total_entrees']) }}</td>
                <td style="text-align: right; font-weight: bold; color: #dc3545;">{{ number_format($jour['total_sorties']) }}</td>
                <td style="text-align: right; font-weight: bold; color: {{ $jour['solde_jour'] >= 0 ? '#28a745' : '#dc3545' }};">
                    {{ $jour['solde_jour'] >= 0 ? '+' : '' }}{{ number_format($jour['solde_jour']) }} FC
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align: center; padding: 20px; color: #999;">Aucun mouvement sur cette période</td>
            </tr>
            @endforelse
        </tbody>
        @if(count($journal) > 0)
        <tfoot>
            <tr style="background: #e9ecef; font-weight: bold;">
                <td>TOTAL</td>
                <td style="text-align: right; color: #28a745;">{{ number_format(collect($journal)->sum('collectes')) }}</td>
                <td style="text-align: right; color: #dc3545;">{{ number_format(collect($journal)->sum('paiements')) }}</td>
                <td style="text-align: right; color: #ffc107;">{{ number_format(collect($journal)->sum('tx_envoi')) }}</td>
                <td style="text-align: right; color: #17a2b8;">{{ number_format(collect($journal)->sum('tx_retrait')) }}</td>
                <td style="text-align: right; color: #28a745;">{{ number_format(collect($journal)->sum('total_entrees')) }}</td>
                <td style="text-align: right; color: #dc3545;">{{ number_format(collect($journal)->sum('total_sorties')) }}</td>
                <td style="text-align: right;">
                    @php $total = collect($journal)->sum('solde_jour'); @endphp
                    <span style="color: {{ $total >= 0 ? '#28a745' : '#dc3545' }};">
                        {{ $total >= 0 ? '+' : '' }}{{ number_format($total) }} FC
                    </span>
                </td>
            </tr>
        </tfoot>
        @endif
    </table>
</div>
@endsection

