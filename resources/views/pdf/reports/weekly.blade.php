@extends('pdf.reports.layout')

@section('content')
    <!-- Versements par jour -->
    @if(isset($stats['versementsParJour']) && count($stats['versementsParJour']) > 0)
    <table>
        <thead>
            <tr>
                <th>Jour</th>
                <th class="text-right">Montant Collecté</th>
                <th class="text-right">% du Total</th>
            </tr>
        </thead>
        <tbody>
            @php $totalSemaine = max(1, $stats['totalCollecte'] ?? 1); @endphp
            @foreach($stats['versementsParJour'] as $jour)
            @php $pct = round((($jour->total ?? 0) / $totalSemaine) * 100, 1); @endphp
            <tr>
                <td>{{ $jour->date ?? 'N/A' }}</td>
                <td class="amount">{{ number_format($jour->total ?? 0) }} FC</td>
                <td class="text-right">{{ $pct }}%</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td><strong>TOTAL SEMAINE</strong></td>
                <td class="amount"><strong>{{ number_format($stats['totalCollecte'] ?? 0) }} FC</strong></td>
                <td class="text-right"><strong>100%</strong></td>
            </tr>
        </tbody>
    </table>
    @endif

    <!-- Top motards -->
    @if(isset($stats['topMotards']) && count($stats['topMotards']) > 0)
    <div style="margin-top: 15px;">
        <h3 class="section-title">Top 10 Motards</h3>
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
                    <td class="text-center">{{ $m->count ?? 0 }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
@endsection
