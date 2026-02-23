@extends('pdf.reports.layout')

@section('content')
    <!-- Versements par semaine -->
    @if(isset($stats['versementsParSemaine']) && count($stats['versementsParSemaine']) > 0)
    <table>
        <thead>
            <tr>
                <th>Semaine</th>
                <th class="text-right">Montant Collecté</th>
                <th class="text-right">% du Total</th>
            </tr>
        </thead>
        <tbody>
            @php $totalMois = max(1, $stats['totalCollecte'] ?? 1); @endphp
            @foreach($stats['versementsParSemaine'] as $semaine)
            @php $pct = round((($semaine->total ?? 0) / $totalMois) * 100, 1); @endphp
            <tr>
                <td>{{ $semaine->semaine ?? 'N/A' }}</td>
                <td class="amount">{{ number_format($semaine->total ?? 0) }} FC</td>
                <td class="text-right">{{ $pct }}%</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td><strong>TOTAL MOIS</strong></td>
                <td class="amount"><strong>{{ number_format($stats['totalCollecte'] ?? 0) }} FC</strong></td>
                <td class="text-right"><strong>100%</strong></td>
            </tr>
        </tbody>
    </table>
    @endif

    <!-- Top motards du mois -->
    @if(isset($stats['topMotards']) && count($stats['topMotards']) > 0)
    <div style="margin-top: 15px;">
        <h3 class="section-title">Top 10 Motards du Mois</h3>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Motard</th>
                    <th class="text-right">Total Versé</th>
                    <th class="text-center">Nb Versements</th>
                </tr>
            </thead>
            <tbody>
                @foreach($stats['topMotards'] as $i => $m)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $m->motard->user->name ?? 'N/A' }}</td>
                    <td class="amount">{{ number_format($m->total ?? 0) }} FC</td>
                    <td class="text-center">{{ $m->nb_versements ?? $m->count ?? 0 }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
@endsection
