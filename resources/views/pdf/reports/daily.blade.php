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

    <!-- Résumé des versements -->
    <div class="section">
        <h3 class="section-title">Résumé des Versements</h3>
        <table>
            <tr>
                <td style="width: 70%;"><strong>Total des versements</strong></td>
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
        </table>
    </div>

    <!-- Détail des versements -->
    @if(isset($stats['derniersVersements']) && count($stats['derniersVersements']) > 0)
    <div class="section">
        <h3 class="section-title">Détail des Versements</h3>
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
    </div>
    @else
    <div class="section">
        <h3 class="section-title">Détail des Versements</h3>
        <p style="text-align: center; padding: 15px; color: #666;">Aucun versement enregistré pour cette date.</p>
    </div>
    @endif

    <!-- Motards en retard -->
    @if(isset($stats['motardsEnRetard']) && count($stats['motardsEnRetard']) > 0)
    <div class="section">
        <h3 class="section-title">Motards en Retard ({{ count($stats['motardsEnRetard']) }})</h3>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nom</th>
                    <th>Zone</th>
                    <th>Téléphone</th>
                </tr>
            </thead>
            <tbody>
                @foreach($stats['motardsEnRetard'] as $i => $m)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $m->user->name ?? 'N/A' }}</td>
                    <td>{{ $m->zone_affectation ?? 'N/A' }}</td>
                    <td>{{ $m->telephone ?? $m->user->phone ?? 'N/A' }}</td>
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

