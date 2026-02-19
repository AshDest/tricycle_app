<?php

namespace App\Livewire\Supervisor\Reports;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Versement;
use App\Models\Motard;
use App\Models\Collecte;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

#[Layout('components.dashlite-layout')]
class Daily extends Component
{
    public $date;
    public $stats = [];

    public function mount()
    {
        $this->date = Carbon::today()->format('Y-m-d');
        $this->loadStats();
    }

    public function updatedDate()
    {
        $this->loadStats();
    }

    protected function loadStats()
    {
        $selectedDate = Carbon::parse($this->date);

        // Versements du jour
        $versements = Versement::whereDate('date_versement', $selectedDate)->get();

        $this->stats = [
            'totalCollecte' => $versements->sum('montant'),
            'totalAttendu' => $versements->sum('montant_attendu'),
            'nombreVersements' => $versements->count(),
            'versementsPayes' => $versements->where('statut', 'paye')->count(),
            'versementsEnRetard' => $versements->where('statut', 'en_retard')->count(),
            'versementsPartiels' => $versements->where('statut', 'partiel')->count(),
            'arrieres' => $versements->sum(fn($v) => max(0, ($v->montant_attendu ?? 0) - ($v->montant ?? 0))),
            'tauxRecouvrement' => $versements->sum('montant_attendu') > 0
                ? round(($versements->sum('montant') / $versements->sum('montant_attendu')) * 100, 1)
                : 0,
        ];

        // Motards en retard ce jour
        $this->stats['motardsEnRetard'] = Motard::whereHas('versements', function($q) use ($selectedDate) {
            $q->whereDate('date_versement', $selectedDate)
              ->where('statut', 'en_retard');
        })->with('user')->get();

        // Derniers versements du jour
        $this->stats['derniersVersements'] = Versement::whereDate('date_versement', $selectedDate)
            ->with(['motard.user', 'moto', 'caissier.user'])
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();
    }

    public function export()
    {
        $filename = 'rapport_quotidien_' . $this->date . '.csv';

        return response()->streamDownload(function() {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Rapport Quotidien - ' . Carbon::parse($this->date)->format('d/m/Y')]);
            fputcsv($handle, []);
            fputcsv($handle, ['Total Collecté', number_format($this->stats['totalCollecte']) . ' FC']);
            fputcsv($handle, ['Total Attendu', number_format($this->stats['totalAttendu']) . ' FC']);
            fputcsv($handle, ['Taux Recouvrement', $this->stats['tauxRecouvrement'] . '%']);
            fputcsv($handle, []);
            fputcsv($handle, ['ID', 'Motard', 'Moto', 'Montant Versé', 'Montant Attendu', 'Statut']);

            foreach ($this->stats['derniersVersements'] as $v) {
                fputcsv($handle, [
                    $v->id,
                    $v->motard->user->name ?? 'N/A',
                    $v->moto->plaque_immatriculation ?? 'N/A',
                    $v->montant ?? 0,
                    $v->montant_attendu ?? 0,
                    $v->statut ?? 'N/A'
                ]);
            }

            fclose($handle);
        }, $filename);
    }

    public function exportPdf()
    {
        $this->loadStats();

        $pdf = Pdf::loadView('pdf.reports.daily', [
            'title' => 'Rapport Quotidien',
            'subtitle' => 'Date: ' . Carbon::parse($this->date)->format('d/m/Y'),
            'stats' => $this->stats,
        ]);

        $pdf->setPaper('A4', 'portrait');

        return response()->streamDownload(function() use ($pdf) {
            echo $pdf->output();
        }, 'rapport_quotidien_' . $this->date . '.pdf');
    }

    public function render()
    {
        return view('livewire.supervisor.reports.daily');
    }
}
