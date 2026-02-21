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

    <!-- Résumé mensuel -->
    <div class="section">
        <h3 class="section-title">Résumé du Mois</h3>
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
                <td>Versements partiels</td>
                <td class="text-right"><span class="badge badge-warning">{{ $stats['versementsPartiels'] ?? 0 }}</span></td>
            </tr>
            <tr>
                <td>Versements en retard</td>
                <td class="text-right"><span class="badge badge-danger">{{ $stats['versementsEnRetard'] ?? 0 }}</span></td>
            </tr>
            <tr>
                <td><strong>Moyenne journalière</strong></td>
                <td class="text-right"><strong>{{ number_format($stats['moyenneJournaliere'] ?? 0) }} FC</strong></td>
            </tr>
            <tr>
                <td>Motards actifs</td>
                <td class="text-right">{{ $stats['motardsActifs'] ?? 0 }}</td>
            </tr>
            <tr>
                <td>Motos actives</td>
                <td class="text-right">{{ $stats['motosActives'] ?? 0 }}</td>
            </tr>
        </table>
    </div>

    <!-- Versements par semaine -->
    @if(isset($stats['versementsParSemaine']) && count($stats['versementsParSemaine']) > 0)
    <div class="section">
        <h3 class="section-title">Versements par Semaine</h3>
        <table>
            <thead>
                <tr>
                    <th>Semaine</th>
                    <th class="text-right">Montant</th>
                    <th class="text-right">% Total</th>
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
                    <td class="text-center">{{ $m->nb_versements ?? $m->count ?? 0 }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Paiements & Maintenance -->
    <div class="section">
        <h3 class="section-title">Paiements & Maintenance</h3>
        <table>
            @if(isset($stats['paiementsProprietaires']))
            <tr>
                <td style="width: 70%;">Total versé aux propriétaires</td>
                <td class="amount">{{ number_format($stats['paiementsProprietaires']['totalPaye'] ?? 0) }} FC</td>
            </tr>
            <tr>
                <td>Paiements effectués</td>
                <td class="text-right">{{ $stats['paiementsProprietaires']['nombrePaiements'] ?? 0 }}</td>
            </tr>
            @endif
            @if(isset($stats['maintenance']))
            <tr>
                <td>Maintenances</td>
                <td class="text-right">{{ $stats['maintenance']['total'] ?? 0 }}</td>
            </tr>
            <tr>
                <td>Coût maintenance</td>
                <td class="amount">{{ number_format($stats['maintenance']['cout'] ?? 0) }} FC</td>
            </tr>
            @endif
            @if(isset($stats['accidents']))
            <tr>
                <td>Accidents</td>
                <td class="text-right">{{ $stats['accidents']['total'] ?? 0 }}</td>
            </tr>
            <tr>
                <td>Coût accidents</td>
                <td class="amount">{{ number_format($stats['accidents']['cout'] ?? 0) }} FC</td>
            </tr>
            @endif
        </table>
    </div>

    <!-- Bilan -->
    <div class="section">
        <h3 class="section-title">Bilan Financier</h3>
        <table>
            <tr style="background: #e8f5e9;">
                <td style="width: 70%;"><strong>Recettes (versements)</strong></td>
                <td class="amount" style="color: #2e7d32;"><strong>+ {{ number_format($stats['totalCollecte'] ?? 0) }} FC</strong></td>
            </tr>
            @php
                $depenses = ($stats['paiementsProprietaires']['totalPaye'] ?? 0) + ($stats['maintenance']['cout'] ?? 0) + ($stats['accidents']['cout'] ?? 0);
                $solde = ($stats['totalCollecte'] ?? 0) - $depenses;
            @endphp
            <tr style="background: #ffebee;">
                <td><strong>Dépenses</strong></td>
                <td class="amount" style="color: #c62828;"><strong>- {{ number_format($depenses) }} FC</strong></td>
            </tr>
            <tr class="total-row">
                <td><strong>SOLDE NET</strong></td>
                <td class="amount" style="color: {{ $solde >= 0 ? '#2e7d32' : '#c62828' }};"><strong>{{ $solde >= 0 ? '+' : '' }}{{ number_format($solde) }} FC</strong></td>
            </tr>
        </table>
    </div>

    <div class="footer-note">
        <strong>Note:</strong> Rapport généré automatiquement. Montants en Francs Congolais (FC).
    </div>
@endsection

