<?php

namespace App\Livewire\Supervisor\Reports;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Versement;
use App\Models\Motard;
use App\Models\Moto;
use App\Models\Maintenance;
use App\Models\Accident;
use App\Models\Payment;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

#[Layout('components.dashlite-layout')]
class Monthly extends Component
{
    public $month;
    public $year;
    public $stats = [];

    public function mount()
    {
        $now = Carbon::now();
        $this->month = $now->format('m');
        $this->year = $now->format('Y');
        $this->loadStats();
    }

    public function updatedMonth()
    {
        $this->loadStats();
    }

    public function updatedYear()
    {
        $this->loadStats();
    }

    protected function loadStats()
    {
        $startOfMonth = Carbon::createFromDate($this->year, $this->month, 1)->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        $versements = Versement::whereBetween('date_versement', [$startOfMonth, $endOfMonth])->get();

        $this->stats = [
            'mois' => $startOfMonth->translatedFormat('F Y'),
            'totalCollecte' => $versements->sum('montant'),
            'totalAttendu' => $versements->sum('montant_attendu'),
            'nombreVersements' => $versements->count(),
            'versementsPayes' => $versements->where('statut', 'paye')->count(),
            'versementsEnRetard' => $versements->where('statut', 'en_retard')->count(),
            'versementsPartiels' => $versements->whereIn('statut', ['partiel', 'partiellement_payé'])->count(),
            'arrieres' => $versements->sum(fn($v) => max(0, ($v->montant_attendu ?? 0) - ($v->montant ?? 0))),
            'tauxRecouvrement' => $versements->sum('montant_attendu') > 0
                ? round(($versements->sum('montant') / $versements->sum('montant_attendu')) * 100, 1)
                : 0,
            'totalMotards' => Motard::count(),
            'totalMotos' => Moto::count(),
            'motardsActifs' => Motard::where('is_active', true)->count(),
            'motosActives' => Moto::where('statut', 'actif')->count(),
        ];

        // Jours avec versements et moyenne
        $joursAvecVersements = Versement::whereBetween('date_versement', [$startOfMonth, $endOfMonth])
            ->selectRaw('DATE(date_versement) as date')
            ->groupBy('date')
            ->get()
            ->count();

        $this->stats['joursAvecVersements'] = $joursAvecVersements;
        $this->stats['moyenneJournaliere'] = $joursAvecVersements > 0
            ? $this->stats['totalCollecte'] / $joursAvecVersements
            : 0;

        // Stats par semaine
        $parSemaine = [];
        $currentWeek = $startOfMonth->copy()->startOfWeek();
        while ($currentWeek <= $endOfMonth) {
            $endWeek = $currentWeek->copy()->endOfWeek();
            if ($endWeek > $endOfMonth) $endWeek = $endOfMonth->copy();

            $versementsSemaine = $versements->filter(function ($v) use ($currentWeek, $endWeek) {
                return $v->date_versement && $v->date_versement >= $currentWeek && $v->date_versement <= $endWeek;
            });

            $parSemaine[] = [
                'semaine' => 'Sem. ' . $currentWeek->weekOfMonth,
                'debut' => $currentWeek->format('d/m'),
                'fin' => $endWeek->format('d/m'),
                'collecte' => $versementsSemaine->sum('montant'),
                'attendu' => $versementsSemaine->sum('montant_attendu'),
                'count' => $versementsSemaine->count(),
            ];

            $currentWeek->addWeek();
        }
        $this->stats['parSemaine'] = $parSemaine;

        // Versements par semaine pour PDF
        $this->stats['versementsParSemaine'] = Versement::whereBetween('date_versement', [$startOfMonth, $endOfMonth])
            ->selectRaw('WEEK(date_versement, 1) as semaine, SUM(montant) as total, COUNT(*) as count')
            ->groupBy('semaine')
            ->orderBy('semaine')
            ->get();

        // Top motards
        $this->stats['topMotards'] = Versement::whereBetween('date_versement', [$startOfMonth, $endOfMonth])
            ->with('motard.user')
            ->selectRaw('motard_id, SUM(montant) as total, COUNT(*) as count')
            ->groupBy('motard_id')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        // Comparaison avec mois précédent
        $prevStartOfMonth = $startOfMonth->copy()->subMonth();
        $prevEndOfMonth = $prevStartOfMonth->copy()->endOfMonth();
        $versementsPrecedent = Versement::whereBetween('date_versement', [$prevStartOfMonth, $prevEndOfMonth])->get();

        $totalPrecedent = $versementsPrecedent->sum('montant');
        $evolution = $totalPrecedent > 0
            ? (($this->stats['totalCollecte'] - $totalPrecedent) / $totalPrecedent) * 100
            : 0;

        $this->stats['comparaison'] = [
            'totalPrecedent' => $totalPrecedent,
            'nbVersementsPrecedent' => $versementsPrecedent->count(),
            'evolution' => $evolution,
        ];

        // Paiements propriétaires
        $this->stats['paiementsProprietaires'] = [
            'totalPaye' => Payment::whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->where('statut', 'paye')
                ->sum('montant'),
            'nombrePaiements' => Payment::whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->where('statut', 'paye')
                ->count(),
            'enAttente' => Payment::whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->whereIn('statut', ['en_attente', 'demande'])
                ->count(),
        ];

        // Maintenance
        $this->stats['maintenance'] = [
            'total' => Maintenance::whereBetween('date_intervention', [$startOfMonth, $endOfMonth])->count(),
            'cout' => Maintenance::whereBetween('date_intervention', [$startOfMonth, $endOfMonth])
                ->selectRaw('SUM(COALESCE(cout_pieces, 0) + COALESCE(cout_main_oeuvre, 0)) as total')
                ->value('total') ?? 0,
        ];

        // Accidents
        $this->stats['accidents'] = [
            'total' => Accident::whereBetween('date_heure', [$startOfMonth, $endOfMonth])->count(),
            'cout' => Accident::whereBetween('date_heure', [$startOfMonth, $endOfMonth])
                ->sum('cout_reel') ?? 0,
        ];
    }

    public function export()
    {
        $filename = 'rapport_mensuel_' . $this->month . '_' . $this->year . '.csv';

        return response()->streamDownload(function() {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Rapport Mensuel - ' . $this->stats['mois']]);
            fputcsv($handle, []);
            fputcsv($handle, ['Total Collecté', number_format($this->stats['totalCollecte']) . ' FC']);
            fputcsv($handle, ['Total Attendu', number_format($this->stats['totalAttendu']) . ' FC']);
            fputcsv($handle, ['Arriérés', number_format($this->stats['arrieres']) . ' FC']);
            fputcsv($handle, ['Taux Recouvrement', $this->stats['tauxRecouvrement'] . '%']);

            fclose($handle);
        }, $filename);
    }

    public function exportPdf()
    {
        $this->loadStats();

        $pdf = Pdf::loadView('pdf.reports.monthly', [
            'title' => 'Rapport Mensuel',
            'subtitle' => $this->stats['mois'],
            'stats' => $this->stats,
        ]);

        $pdf->setPaper('A4', 'portrait');

        return response()->streamDownload(function() use ($pdf) {
            echo $pdf->output();
        }, 'rapport_mensuel_' . $this->month . '_' . $this->year . '.pdf');
    }

    public function render()
    {
        return view('livewire.supervisor.reports.monthly');
    }
}
