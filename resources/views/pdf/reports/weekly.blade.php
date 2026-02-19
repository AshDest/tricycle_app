@extends('pdf.reports.layout')

@section('content')
    <!-- Statistiques principales -->
    <div class="stats-grid">
        <div class="stat-box stat-success">
            <div class="value">{{ number_format($stats['totalCollecte']) }} FC</div>
            <div class="label">Total Collecté</div>
        </div>
        <div class="stat-box stat-info">
            <div class="value">{{ number_format($stats['totalAttendu']) }} FC</div>
            <div class="label">Total Attendu</div>
        </div>
        <div class="stat-box stat-warning">
            <div class="value">{{ number_format($stats['arrieres']) }} FC</div>
            <div class="label">Arriérés</div>
        </div>
        <div class="stat-box">
            <div class="value">{{ $stats['tauxRecouvrement'] }}%</div>
            <div class="label">Taux Recouvrement</div>
        </div>
    </div>

    <!-- Résumé -->
    <div class="section">
        <h3 class="section-title">Résumé de la Semaine</h3>
        <table>
            <tr>
                <td><strong>Nombre de jours travaillés</strong></td>
                <td class="text-right">{{ $stats['joursAvecVersements'] ?? 0 }}</td>
            </tr>
            <tr>
                <td><strong>Total des versements</strong></td>
                <td class="text-right">{{ $stats['nombreVersements'] }}</td>
            </tr>
            <tr>
                <td><strong>Versements payés</strong></td>
                <td class="text-right"><span class="badge badge-success">{{ $stats['versementsPayes'] }}</span></td>
            </tr>
            <tr>
                <td><strong>Versements en retard</strong></td>
                <td class="text-right"><span class="badge badge-danger">{{ $stats['versementsEnRetard'] }}</span></td>
            </tr>
            <tr>
                <td><strong>Moyenne journalière</strong></td>
                <td class="text-right">{{ number_format($stats['moyenneJournaliere'] ?? 0) }} FC</td>
            </tr>
        </table>
    </div>

    <!-- Versements par jour -->
    @if(isset($stats['versementsParJour']) && count($stats['versementsParJour']) > 0)
    <div class="section">
        <h3 class="section-title">Versements par Jour</h3>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th class="text-right">Montant Collecté</th>
                    <th class="text-right">Nb Versements</th>
                </tr>
            </thead>
            <tbody>
                @foreach($stats['versementsParJour'] as $jour)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($jour->date)->format('d/m/Y') }} ({{ \Carbon\Carbon::parse($jour->date)->locale('fr')->dayName }})</td>
                    <td class="amount">{{ number_format($jour->total) }} FC</td>
                    <td class="text-center">{{ $jour->count }}</td>
                </tr>
                @endforeach
                <tr class="total-row">
                    <td><strong>TOTAL</strong></td>
                    <td class="amount"><strong>{{ number_format($stats['totalCollecte']) }} FC</strong></td>
                    <td class="text-center"><strong>{{ $stats['nombreVersements'] }}</strong></td>
                </tr>
            </tbody>
        </table>
    </div>
    @endif

    <!-- Top motards -->
    @if(isset($stats['topMotards']) && count($stats['topMotards']) > 0)
    <div class="section">
        <h3 class="section-title">Top 10 Motards de la Semaine</h3>
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
                @foreach($stats['topMotards'] as $index => $motard)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $motard->motard->user->name ?? 'N/A' }}</td>
                    <td class="amount">{{ number_format($motard->total) }} FC</td>
                    <td class="text-center">{{ $motard->count }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
@endsection

