<?php

namespace App\Livewire\Supervisor\Reports;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Versement;
use App\Models\Motard;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

#[Layout('components.dashlite-layout')]
class Weekly extends Component
{
    public $week_of;
    public $stats = [];

    public function mount()
    {
        $this->week_of = Carbon::today()->format('Y-m-d');
        $this->loadStats();
    }

    public function updatedWeekOf()
    {
        $this->loadStats();
    }

    protected function loadStats()
    {
        $date = Carbon::parse($this->week_of);
        $startOfWeek = $date->copy()->startOfWeek();
        $endOfWeek = $date->copy()->endOfWeek();

        $versements = Versement::whereBetween('date_versement', [$startOfWeek, $endOfWeek])->get();

        $this->stats = [
            'debutSemaine' => $startOfWeek->format('d/m/Y'),
            'finSemaine' => $endOfWeek->format('d/m/Y'),
            'totalCollecte' => $versements->sum('montant'),
            'totalAttendu' => $versements->sum('montant_attendu'),
            'nombreVersements' => $versements->count(),
            'versementsPayes' => $versements->where('statut', 'paye')->count(),
            'versementsEnRetard' => $versements->where('statut', 'en_retard')->count(),
            'arrieres' => $versements->sum(fn($v) => max(0, ($v->montant_attendu ?? 0) - ($v->montant ?? 0))),
            'tauxRecouvrement' => $versements->sum('montant_attendu') > 0
                ? round(($versements->sum('montant') / $versements->sum('montant_attendu')) * 100, 1)
                : 0,
            'joursAvecVersements' => Versement::whereBetween('date_versement', [$startOfWeek, $endOfWeek])
                ->selectRaw('DATE(date_versement) as date')
                ->groupBy('date')
                ->get()
                ->count(),
            'moyenneJournaliere' => 0,
        ];

        // Calculer moyenne journalière
        if ($this->stats['joursAvecVersements'] > 0) {
            $this->stats['moyenneJournaliere'] = $this->stats['totalCollecte'] / $this->stats['joursAvecVersements'];
        }

        // Stats par jour de la semaine
        $parJour = [];
        for ($i = 0; $i < 7; $i++) {
            $jour = $startOfWeek->copy()->addDays($i);
            $versementsJour = $versements->filter(fn($v) => $v->date_versement && $v->date_versement->isSameDay($jour));
            $parJour[] = [
                'jour' => $jour->translatedFormat('l'),
                'date' => $jour->format('d/m'),
                'collecte' => $versementsJour->sum('montant'),
                'attendu' => $versementsJour->sum('montant_attendu'),
                'count' => $versementsJour->count(),
            ];
        }
        $this->stats['parJour'] = $parJour;

        // Versements par jour pour PDF
        $this->stats['versementsParJour'] = Versement::whereBetween('date_versement', [$startOfWeek, $endOfWeek])
            ->selectRaw('DATE(date_versement) as date, SUM(montant) as total, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Top motards
        $this->stats['topMotards'] = Versement::whereBetween('date_versement', [$startOfWeek, $endOfWeek])
            ->with('motard.user')
            ->selectRaw('motard_id, SUM(montant) as total, COUNT(*) as count')
            ->groupBy('motard_id')
            ->orderByDesc('total')
            ->limit(10)
            ->get();
    }

    public function export()
    {
        $filename = 'rapport_hebdomadaire_' . str_replace('/', '-', $this->stats['debutSemaine']) . '.csv';

        return response()->streamDownload(function() {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Rapport Hebdomadaire - Semaine du ' . $this->stats['debutSemaine'] . ' au ' . $this->stats['finSemaine']]);
            fputcsv($handle, []);
            fputcsv($handle, ['Total Collecté', number_format($this->stats['totalCollecte']) . ' FC']);
            fputcsv($handle, ['Total Attendu', number_format($this->stats['totalAttendu']) . ' FC']);
            fputcsv($handle, ['Taux Recouvrement', $this->stats['tauxRecouvrement'] . '%']);
            fputcsv($handle, []);
            fputcsv($handle, ['Jour', 'Date', 'Collecté', 'Attendu', 'Nombre']);

            foreach ($this->stats['parJour'] as $jour) {
                fputcsv($handle, [
                    $jour['jour'],
                    $jour['date'],
                    $jour['collecte'],
                    $jour['attendu'],
                    $jour['count']
                ]);
            }

            fclose($handle);
        }, $filename);
    }

    public function exportPdf()
    {
        $this->loadStats();

        $pdf = Pdf::loadView('pdf.reports.weekly', [
            'title' => 'Rapport Hebdomadaire',
            'subtitle' => 'Semaine du ' . $this->stats['debutSemaine'] . ' au ' . $this->stats['finSemaine'],
            'stats' => $this->stats,
        ]);

        $pdf->setPaper('A4', 'portrait');

        return response()->streamDownload(function() use ($pdf) {
            echo $pdf->output();
        }, 'rapport_hebdomadaire_' . str_replace('/', '-', $this->stats['debutSemaine']) . '.pdf');
    }

    public function render()
    {
        return view('livewire.supervisor.reports.weekly');
    }
}
