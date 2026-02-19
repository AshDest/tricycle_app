<?php

namespace App\Livewire\Admin\Reports;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Versement;
use App\Models\Collecte;
use App\Models\Payment;
use App\Models\Motard;
use App\Models\Tournee;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

#[Layout('components.dashlite-layout')]
class Weekly extends Component
{
    public $week_of;
    public $startOfWeek;
    public $endOfWeek;

    // Stats
    public $totalVersements = 0;
    public $totalAttendu = 0;
    public $totalCollecte = 0;
    public $totalPaiements = 0;
    public $motardsEnRetard = 0;
    public $versementsParJour = [];
    public $comparaisonSemainePrecedente = 0;

    public function mount()
    {
        $this->week_of = Carbon::today()->format('Y-m-d');
        $this->loadStats();
    }

    public function updatedWeekOf()
    {
        $this->loadStats();
    }

    public function loadStats()
    {
        $date = Carbon::parse($this->week_of);
        $this->startOfWeek = $date->copy()->startOfWeek();
        $this->endOfWeek = $date->copy()->endOfWeek();

        // Versements de la semaine
        $versements = Versement::whereBetween('date_versement', [$this->startOfWeek, $this->endOfWeek])->get();
        $this->totalVersements = $versements->sum('montant');
        $this->totalAttendu = $versements->sum('montant_attendu');

        // Collectes
        $this->totalCollecte = Collecte::whereBetween('created_at', [$this->startOfWeek, $this->endOfWeek])
            ->sum('montant_collecte');

        // Paiements propriétaires
        $this->totalPaiements = Payment::whereBetween('date_paiement', [$this->startOfWeek, $this->endOfWeek])
            ->whereIn('statut', ['paye', 'valide'])
            ->sum('total_paye');

        // Motards en retard
        $this->motardsEnRetard = Versement::whereBetween('date_versement', [$this->startOfWeek, $this->endOfWeek])
            ->where('statut', 'en_retard')
            ->distinct('motard_id')
            ->count('motard_id');

        // Versements par jour de la semaine
        $this->versementsParJour = [];
        for ($i = 0; $i < 7; $i++) {
            $jour = $this->startOfWeek->copy()->addDays($i);
            $this->versementsParJour[] = [
                'jour' => $jour->translatedFormat('D'),
                'date' => $jour->format('d/m'),
                'montant' => Versement::whereDate('date_versement', $jour)->sum('montant'),
                'attendu' => Versement::whereDate('date_versement', $jour)->sum('montant_attendu'),
            ];
        }

        // Comparaison avec semaine précédente
        $startPrev = $this->startOfWeek->copy()->subWeek();
        $endPrev = $this->endOfWeek->copy()->subWeek();
        $totalPrev = Versement::whereBetween('date_versement', [$startPrev, $endPrev])->sum('montant');

        if ($totalPrev > 0) {
            $this->comparaisonSemainePrecedente = round((($this->totalVersements - $totalPrev) / $totalPrev) * 100, 1);
        } else {
            $this->comparaisonSemainePrecedente = 0;
        }
    }

    public function export()
    {
        $filename = 'rapport_hebdomadaire_admin_' . $this->startOfWeek->format('Y-m-d') . '.csv';

        return response()->streamDownload(function() {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Rapport Hebdomadaire Admin - ' . $this->startOfWeek->format('d/m/Y') . ' au ' . $this->endOfWeek->format('d/m/Y')]);
            fputcsv($handle, []);
            fputcsv($handle, ['Total Versements', number_format($this->totalVersements) . ' FC']);
            fputcsv($handle, ['Total Attendu', number_format($this->totalAttendu) . ' FC']);
            fputcsv($handle, ['Total Collecte', number_format($this->totalCollecte) . ' FC']);
            fputcsv($handle, ['Motards en retard', $this->motardsEnRetard]);
            fputcsv($handle, []);
            fputcsv($handle, ['Jour', 'Date', 'Montant', 'Attendu']);
            foreach ($this->versementsParJour as $jour) {
                fputcsv($handle, [$jour['jour'], $jour['date'], $jour['montant'], $jour['attendu']]);
            }

            fclose($handle);
        }, $filename);
    }

    public function exportPdf()
    {
        $this->loadStats();

        $joursAvecVersements = collect($this->versementsParJour)->filter(fn($j) => $j['montant'] > 0)->count();

        $stats = [
            'debutSemaine' => $this->startOfWeek->format('d/m/Y'),
            'finSemaine' => $this->endOfWeek->format('d/m/Y'),
            'totalCollecte' => $this->totalVersements,
            'totalAttendu' => $this->totalAttendu,
            'nombreVersements' => Versement::whereBetween('date_versement', [$this->startOfWeek, $this->endOfWeek])->count(),
            'versementsPayes' => Versement::whereBetween('date_versement', [$this->startOfWeek, $this->endOfWeek])->where('statut', 'payé')->count(),
            'versementsEnRetard' => Versement::whereBetween('date_versement', [$this->startOfWeek, $this->endOfWeek])->where('statut', 'en_retard')->count(),
            'arrieres' => max(0, $this->totalAttendu - $this->totalVersements),
            'tauxRecouvrement' => $this->totalAttendu > 0 ? round(($this->totalVersements / $this->totalAttendu) * 100, 1) : 0,
            'joursAvecVersements' => $joursAvecVersements,
            'moyenneJournaliere' => $joursAvecVersements > 0 ? $this->totalVersements / $joursAvecVersements : 0,
            'versementsParJour' => collect($this->versementsParJour)->map(function($j) {
                return (object)['date' => $j['date'], 'total' => $j['montant'], 'count' => 0];
            }),
            'topMotards' => Versement::whereBetween('date_versement', [$this->startOfWeek, $this->endOfWeek])
                ->with('motard.user')
                ->selectRaw('motard_id, SUM(montant) as total, COUNT(*) as count')
                ->groupBy('motard_id')
                ->orderByDesc('total')
                ->limit(10)
                ->get(),
        ];

        $pdf = Pdf::loadView('pdf.reports.weekly', [
            'title' => 'Rapport Hebdomadaire - Administration',
            'subtitle' => 'Semaine du ' . $stats['debutSemaine'] . ' au ' . $stats['finSemaine'],
            'stats' => $stats,
        ]);

        $pdf->setPaper('A4', 'portrait');

        return response()->streamDownload(function() use ($pdf) {
            echo $pdf->output();
        }, 'rapport_hebdomadaire_admin_' . $this->startOfWeek->format('Y-m-d') . '.pdf');
    }

    public function render()
    {
        return view('livewire.admin.reports.weekly');
    }
}
