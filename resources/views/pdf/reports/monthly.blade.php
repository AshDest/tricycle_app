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

    <!-- Résumé mensuel -->
    <div class="section">
        <h3 class="section-title">Résumé du Mois</h3>
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
            <tr>
                <td><strong>Nombre de motards actifs</strong></td>
                <td class="text-right">{{ $stats['motardsActifs'] ?? 0 }}</td>
            </tr>
            <tr>
                <td><strong>Nombre de motos actives</strong></td>
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
                    <th class="text-right">Montant Collecté</th>
                    <th class="text-right">Nb Versements</th>
                </tr>
            </thead>
            <tbody>
                @foreach($stats['versementsParSemaine'] as $semaine)
                <tr>
                    <td>Semaine {{ $semaine->semaine }}</td>
                    <td class="amount">{{ number_format($semaine->total) }} FC</td>
                    <td class="text-center">{{ $semaine->count }}</td>
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

    <!-- Comparaison avec le mois précédent -->
    @if(isset($stats['comparaison']))
    <div class="section">
        <h3 class="section-title">Comparaison avec le Mois Précédent</h3>
        <table>
            <thead>
                <tr>
                    <th>Indicateur</th>
                    <th class="text-right">Mois Actuel</th>
                    <th class="text-right">Mois Précédent</th>
                    <th class="text-right">Évolution</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Total Collecté</td>
                    <td class="amount">{{ number_format($stats['totalCollecte']) }} FC</td>
                    <td class="amount">{{ number_format($stats['comparaison']['totalPrecedent'] ?? 0) }} FC</td>
                    <td class="text-right">
                        @php
                            $evolution = $stats['comparaison']['evolution'] ?? 0;
                        @endphp
                        <span class="badge {{ $evolution >= 0 ? 'badge-success' : 'badge-danger' }}">
                            {{ $evolution >= 0 ? '+' : '' }}{{ number_format($evolution, 1) }}%
                        </span>
                    </td>
                </tr>
                <tr>
                    <td>Nombre de Versements</td>
                    <td class="text-center">{{ $stats['nombreVersements'] }}</td>
                    <td class="text-center">{{ $stats['comparaison']['nbVersementsPrecedent'] ?? 0 }}</td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    </div>
    @endif

    <!-- Top motards du mois -->
    @if(isset($stats['topMotards']) && count($stats['topMotards']) > 0)
    <div class="section">
        <h3 class="section-title">Top 10 Motards du Mois</h3>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Motard</th>
                    <th class="text-right">Total Versé</th>
                    <th class="text-center">Nb Versements</th>
                    <th class="text-right">Moyenne/Jour</th>
                </tr>
            </thead>
            <tbody>
                @foreach($stats['topMotards'] as $index => $motard)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $motard->motard->user->name ?? 'N/A' }}</td>
                    <td class="amount">{{ number_format($motard->total) }} FC</td>
                    <td class="text-center">{{ $motard->count }}</td>
                    <td class="amount">{{ number_format($motard->count > 0 ? $motard->total / $motard->count : 0) }} FC</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Paiements propriétaires -->
    @if(isset($stats['paiementsProprietaires']))
    <div class="section">
        <h3 class="section-title">Paiements Propriétaires</h3>
        <table>
            <tr>
                <td><strong>Total versé aux propriétaires</strong></td>
                <td class="amount">{{ number_format($stats['paiementsProprietaires']['totalPaye'] ?? 0) }} FC</td>
            </tr>
            <tr>
                <td><strong>Nombre de paiements</strong></td>
                <td class="text-right">{{ $stats['paiementsProprietaires']['nombrePaiements'] ?? 0 }}</td>
            </tr>
            <tr>
                <td><strong>En attente</strong></td>
                <td class="text-right"><span class="badge badge-warning">{{ $stats['paiementsProprietaires']['enAttente'] ?? 0 }}</span></td>
            </tr>
        </table>
    </div>
    @endif

    <!-- Maintenance et Accidents -->
    @if(isset($stats['maintenance']) || isset($stats['accidents']))
    <div class="section">
        <h3 class="section-title">Maintenance & Accidents</h3>
        <table>
            @if(isset($stats['maintenance']))
            <tr>
                <td><strong>Maintenances effectuées</strong></td>
                <td class="text-right">{{ $stats['maintenance']['total'] ?? 0 }}</td>
            </tr>
            <tr>
                <td><strong>Coût total maintenance</strong></td>
                <td class="amount">{{ number_format($stats['maintenance']['cout'] ?? 0) }} FC</td>
            </tr>
            @endif
            @if(isset($stats['accidents']))
            <tr>
                <td><strong>Accidents déclarés</strong></td>
                <td class="text-right">{{ $stats['accidents']['total'] ?? 0 }}</td>
            </tr>
            <tr>
                <td><strong>Coût total accidents</strong></td>
                <td class="amount">{{ number_format($stats['accidents']['cout'] ?? 0) }} FC</td>
            </tr>
            @endif
        </table>
    </div>
    @endif
@endsection

