@extends('pdf.reports.layout')

@section('content')
    <!-- Statistiques principales -->
    <table class="stats-grid">
        <tr>
            <td class="stat-success">
                <div class="stat-box">
                    <div class="value">{{ number_format($stats['totalCollecte'] ?? 0) }} FC</div>
                    <div class="label">Total Collecté</div>
                </div>
            </td>
            <td class="stat-info">
                <div class="stat-box">
                    <div class="value">{{ number_format($stats['totalAttendu'] ?? 0) }} FC</div>
                    <div class="label">Total Attendu</div>
                </div>
            </td>
            <td class="stat-warning">
                <div class="stat-box">
                    <div class="value">{{ number_format($stats['arrieres'] ?? 0) }} FC</div>
                    <div class="label">Arriérés</div>
                </div>
            </td>
            <td>
                <div class="stat-box">
                    <div class="value">{{ $stats['tauxRecouvrement'] ?? 0 }}%</div>
                    <div class="label">Taux Recouvrement</div>
                </div>
            </td>
        </tr>
    </table>

    <!-- Résumé -->
    <div class="section">
        <h3 class="section-title">Résumé de la Semaine</h3>
        <table>
            <tr>
                <td style="width: 70%;"><strong>Nombre de jours travaillés</strong></td>
                <td class="text-right">{{ $stats['joursAvecVersements'] ?? 0 }} jour(s)</td>
            </tr>
            <tr>
                <td><strong>Total des versements</strong></td>
                <td class="text-right"><strong>{{ $stats['nombreVersements'] ?? 0 }}</strong></td>
            </tr>
            <tr>
                <td>Versements payés</td>
                <td class="text-right"><span class="badge badge-success">{{ $stats['versementsPayes'] ?? 0 }}</span></td>
            </tr>
            <tr>
                <td>Versements en retard</td>
                <td class="text-right"><span class="badge badge-danger">{{ $stats['versementsEnRetard'] ?? 0 }}</span></td>
            </tr>
            <tr>
                <td><strong>Moyenne journalière</strong></td>
                <td class="text-right"><strong>{{ number_format($stats['moyenneJournaliere'] ?? 0) }} FC</strong></td>
            </tr>
        </table>
    </div>

    <!-- Versements par jour -->
    @if(isset($stats['versementsParJour']) && count($stats['versementsParJour']) > 0)
    <div class="section">
        <h3 class="section-title">Détail par Jour</h3>
        <table>
            <thead>
                <tr>
                    <th>Jour</th>
                    <th class="text-right">Montant</th>
                    <th class="text-right">% Total</th>
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
                    <td><strong>TOTAL</strong></td>
                    <td class="amount"><strong>{{ number_format($stats['totalCollecte'] ?? 0) }} FC</strong></td>
                    <td class="text-right"><strong>100%</strong></td>
                </tr>
            </tbody>
        </table>
    </div>
    @endif

    <!-- Top motards -->
    @if(isset($stats['topMotards']) && count($stats['topMotards']) > 0)
    <div class="section">
        <h3 class="section-title">Top 10 Motards</h3>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Motard</th>
                    <th class="text-right">Total</th>
                    <th class="text-center">Nb</th>
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

    <div class="footer-note">
        <strong>Note:</strong> Rapport généré automatiquement. Montants en Francs Congolais (FC).
    </div>
@endsection

