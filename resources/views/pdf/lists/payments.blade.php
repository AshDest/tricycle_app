@extends('pdf.lists.layout')

@section('content')
    @if(isset($filtres) && (($filtres['search'] ?? '') || ($filtres['statut'] ?? '')))
        <div class="filters-info">
            <strong>Filtres appliqués:</strong>
            @if($filtres['search'] ?? '') Recherche: "{{ $filtres['search'] }}" @endif
            @if($filtres['statut'] ?? '') | Statut: {{ ucfirst($filtres['statut']) }} @endif
        </div>
    @endif

    @if(isset($stats))
        <div class="stats-row">
            <div class="stat-item">
                <div class="value">{{ number_format($stats['total'] ?? 0) }}</div>
                <div class="label">Total Paiements</div>
            </div>
            <div class="stat-item">
                <div class="value">{{ number_format($stats['total_montant_usd'] ?? 0, 2) }} $</div>
                <div class="label">Montant Total (USD)</div>
            </div>
            <div class="stat-item">
                <div class="value">{{ number_format($stats['payes'] ?? 0) }}</div>
                <div class="label">Payés</div>
            </div>
            <div class="stat-item">
                <div class="value">{{ number_format($stats['en_attente'] ?? 0) }}</div>
                <div class="label">En Attente</div>
            </div>
        </div>
    @endif

    <table>
        <thead>
            <tr>
                <th style="width: 12%">Date</th>
                <th style="width: 18%">Propriétaire</th>
                <th style="width: 12%" class="text-right">Montant (USD)</th>
                <th style="width: 12%">Mode</th>
                <th style="width: 10%">Statut</th>
                <th style="width: 18%">Référence</th>
                <th style="width: 18%">Notes</th>
            </tr>
        </thead>
        <tbody>
            @forelse($payments as $payment)
                @php
                    $tauxPdf = ($payment->taux_conversion && $payment->taux_conversion > 0) ? $payment->taux_conversion : \App\Models\SystemSetting::getTauxUsdCdf();
                    $montantUsdPdf = ($payment->montant_usd && $payment->montant_usd > 0) ? $payment->montant_usd : ($tauxPdf > 0 ? round($payment->total_du / $tauxPdf, 2) : 0);
                @endphp
                <tr class="no-break">
                    <td>{{ $payment->created_at?->format('d/m/Y') ?? '-' }}</td>
                    <td>{{ $payment->proprietaire?->user?->name ?? '-' }}</td>
                    <td class="amount">{{ number_format($montantUsdPdf, 2) }} $</td>
                    <td>
                        @php
                            $modes = [
                                'cash' => 'Cash',
                                'mpesa' => 'M-Pesa',
                                'airtel_money' => 'Airtel Money',
                                'orange_money' => 'Orange Money',
                                'virement' => 'Virement',
                            ];
                        @endphp
                        {{ $modes[$payment->mode_paiement] ?? ucfirst($payment->mode_paiement ?? '-') }}
                    </td>
                    <td>
                        @php
                            $badgeClass = match($payment->statut) {
                                'paye' => 'badge-success',
                                'en_attente', 'demande' => 'badge-warning',
                                'rejete' => 'badge-danger',
                                'approuve' => 'badge-info',
                                default => 'badge-secondary'
                            };
                        @endphp
                        <span class="badge {{ $badgeClass }}">{{ ucfirst(str_replace('_', ' ', $payment->statut ?? '-')) }}</span>
                    </td>
                    <td style="font-size: 8px;">{{ $payment->reference ?? '-' }}</td>
                    <td style="font-size: 8px;">{{ \Str::limit($payment->notes ?? '', 30) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">Aucun paiement trouvé</td>
                </tr>
            @endforelse
        </tbody>
        @if(($payments->count() ?? 0) > 0)
            <tfoot>
                <tr class="total-row">
                    <td colspan="2"><strong>TOTAL</strong></td>
                    <td class="amount">
                        @php
                            $totalUsdPdf = $payments->sum(function($p) {
                                $t = ($p->taux_conversion && $p->taux_conversion > 0) ? $p->taux_conversion : \App\Models\SystemSetting::getTauxUsdCdf();
                                return ($p->montant_usd && $p->montant_usd > 0) ? $p->montant_usd : ($t > 0 ? round($p->total_du / $t, 2) : 0);
                            });
                        @endphp
                        <strong>{{ number_format($totalUsdPdf, 2) }} $</strong>
                    </td>
                    <td colspan="4"></td>
                </tr>
            </tfoot>
        @endif
    </table>
@endsection

