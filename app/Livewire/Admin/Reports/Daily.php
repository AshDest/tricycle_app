<?php

namespace App\Livewire\Admin\Reports;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Versement;
use App\Models\Collecte;
use App\Models\Payment;
use App\Models\Motard;
use App\Models\Moto;
use App\Models\Tournee;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

#[Layout('components.dashlite-layout')]
class Daily extends Component
{
    public $date;

    // Stats
    public $totalVersements = 0;
    public $totalAttendu = 0;
    public $totalCollecte = 0;
    public $motardsEnRetard = 0;
    public $tourneesJour = 0;
    public $tourneesTerminees = 0;
    public $versementsParStatut = [];
    public $topMotards = [];

    public function mount()
    {
        $this->date = Carbon::today()->format('Y-m-d');
        $this->loadStats();
    }

    public function updatedDate()
    {
        $this->loadStats();
    }

    public function loadStats()
    {
        $dateCarbon = Carbon::parse($this->date);

        // Versements du jour
        $versements = Versement::whereDate('date_versement', $dateCarbon)->get();
        $this->totalVersements = $versements->sum('montant');
        $this->totalAttendu = $versements->sum('montant_attendu');

        // Versements par statut
        $this->versementsParStatut = [
            'paye' => Versement::whereDate('date_versement', $dateCarbon)->where('statut', 'payé')->count(),
            'partiel' => Versement::whereDate('date_versement', $dateCarbon)->where('statut', 'partiellement_payé')->count(),
            'retard' => Versement::whereDate('date_versement', $dateCarbon)->where('statut', 'en_retard')->count(),
            'non_effectue' => Versement::whereDate('date_versement', $dateCarbon)->where('statut', 'non_effectué')->count(),
        ];

        // Collectes du jour
        $this->totalCollecte = Collecte::whereDate('created_at', $dateCarbon)->sum('montant_collecte');

        // Motards en retard
        $this->motardsEnRetard = Motard::whereHas('versements', function($q) use ($dateCarbon) {
            $q->whereDate('date_versement', $dateCarbon)->where('statut', 'en_retard');
        })->count();

        // Tournées
        $this->tourneesJour = Tournee::whereDate('date', $dateCarbon)->count();
        $this->tourneesTerminees = Tournee::whereDate('date', $dateCarbon)->where('statut', 'terminee')->count();

        // Top motards (meilleurs payeurs du jour)
        $this->topMotards = Versement::whereDate('date_versement', $dateCarbon)
            ->where('statut', 'payé')
            ->with('motard.user')
            ->selectRaw('motard_id, SUM(montant) as total')
            ->groupBy('motard_id')
            ->orderByDesc('total')
            ->take(5)
            ->get();
    }

    public function export()
    {
        $filename = 'rapport_quotidien_admin_' . $this->date . '.csv';

        return response()->streamDownload(function() {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Rapport Quotidien Admin - ' . Carbon::parse($this->date)->format('d/m/Y')]);
            fputcsv($handle, []);
            fputcsv($handle, ['Total Versements', number_format($this->totalVersements) . ' FC']);
            fputcsv($handle, ['Total Attendu', number_format($this->totalAttendu) . ' FC']);
            fputcsv($handle, ['Total Collecte', number_format($this->totalCollecte) . ' FC']);
            fputcsv($handle, ['Motards en retard', $this->motardsEnRetard]);
            fputcsv($handle, ['Tournées du jour', $this->tourneesJour]);
            fputcsv($handle, ['Tournées terminées', $this->tourneesTerminees]);

            fclose($handle);
        }, $filename);
    }

    public function exportPdf()
    {
        $this->loadStats();

        $stats = [
            'totalCollecte' => $this->totalVersements,
            'totalAttendu' => $this->totalAttendu,
            'nombreVersements' => array_sum($this->versementsParStatut),
            'versementsPayes' => $this->versementsParStatut['paye'] ?? 0,
            'versementsEnRetard' => $this->versementsParStatut['retard'] ?? 0,
            'versementsPartiels' => $this->versementsParStatut['partiel'] ?? 0,
            'arrieres' => max(0, $this->totalAttendu - $this->totalVersements),
            'tauxRecouvrement' => $this->totalAttendu > 0
                ? round(($this->totalVersements / $this->totalAttendu) * 100, 1)
                : 0,
            'motardsEnRetard' => collect([]),
            'derniersVersements' => Versement::whereDate('date_versement', $this->date)
                ->with(['motard.user', 'moto'])
                ->orderBy('created_at', 'desc')
                ->limit(20)
                ->get(),
        ];

        $pdf = Pdf::loadView('pdf.reports.daily', [
            'title' => 'Rapport Quotidien - Administration',
            'subtitle' => 'Date: ' . Carbon::parse($this->date)->format('d/m/Y'),
            'stats' => $stats,
        ]);

        $pdf->setPaper('A4', 'portrait');

        return response()->streamDownload(function() use ($pdf) {
            echo $pdf->output();
        }, 'rapport_quotidien_admin_' . $this->date . '.pdf');
    }

    public function render()
    {
        return view('livewire.admin.reports.daily');
    }
}
