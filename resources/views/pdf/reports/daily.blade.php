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

    <!-- Résumé des versements -->
    <div class="section">
        <h3 class="section-title">Résumé des Versements</h3>
        <table>
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
                <td><strong>Versements partiels</strong></td>
                <td class="text-right"><span class="badge badge-warning">{{ $stats['versementsPartiels'] }}</span></td>
            </tr>
        </table>
    </div>

    <!-- Liste des versements -->
    @if(isset($stats['derniersVersements']) && count($stats['derniersVersements']) > 0)
    <div class="section">
        <h3 class="section-title">Détail des Versements</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Motard</th>
                    <th>Moto</th>
                    <th class="text-right">Montant Versé</th>
                    <th class="text-right">Montant Attendu</th>
                    <th class="text-center">Statut</th>
                </tr>
            </thead>
            <tbody>
                @foreach($stats['derniersVersements'] as $v)
                <tr>
                    <td>{{ $v->id }}</td>
                    <td>{{ $v->motard->user->name ?? 'N/A' }}</td>
                    <td>{{ $v->moto->plaque_immatriculation ?? 'N/A' }}</td>
                    <td class="amount">{{ number_format($v->montant ?? 0) }} FC</td>
                    <td class="amount">{{ number_format($v->montant_attendu ?? 0) }} FC</td>
                    <td class="text-center">
                        @php
                            $statutClass = match($v->statut) {
                                'paye' => 'badge-success',
                                'en_retard' => 'badge-danger',
                                'partiel' => 'badge-warning',
                                default => 'badge-info'
                            };
                        @endphp
                        <span class="badge {{ $statutClass }}">{{ ucfirst($v->statut ?? 'N/A') }}</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Motards en retard -->
    @if(isset($stats['motardsEnRetard']) && count($stats['motardsEnRetard']) > 0)
    <div class="section">
        <h3 class="section-title">Motards en Retard</h3>
        <table>
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Zone</th>
                </tr>
            </thead>
            <tbody>
                @foreach($stats['motardsEnRetard'] as $motard)
                <tr>
                    <td>{{ $motard->user->name ?? 'N/A' }}</td>
                    <td>{{ $motard->zone_affectation ?? 'N/A' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
@endsection

