@extends("pdf.lists.layout")

@section("content")
<div class="content">
    <div class="stats-row">
        <div class="stat-item">
            <div class="value">{{ number_format($totalSoldeCaisse) }} FC</div>
            <div class="label">Solde Caisse Total</div>
        </div>
        <div class="stat-item">
            <div class="value">{{ number_format($totalPaiements) }} FC</div>
            <div class="label">Paiements Période</div>
        </div>
    </div>

    <div style="margin-bottom: 10px; font-size: 10px; color: #666;">
        <strong>Période:</strong> {{ \Carbon\Carbon::parse($dateDebut)->format('d/m/Y') }} au {{ \Carbon\Carbon::parse($dateFin)->format('d/m/Y') }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Collecteur</th>
                <th>Zone</th>
                <th style="text-align: right;">Solde Caisse</th>
                <th style="text-align: right;">Collectes</th>
                <th style="text-align: right;">Dépenses</th>
            </tr>
        </thead>
        <tbody>
            @forelse($collecteurs as $c)
            <tr>
                <td>{{ $c['nom'] }}</td>
                <td>{{ $c['zone'] }}</td>
                <td style="text-align: right; font-weight: bold;">{{ number_format($c['solde_caisse']) }} FC</td>
                <td style="text-align: right; color: #28a745;">+{{ number_format($c['collectes_periode']) }} FC</td>
                <td style="text-align: right; color: #dc3545;">-{{ number_format($c['depenses_periode']) }} FC</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align: center;">Aucun collecteur</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
