<?php

namespace App\Livewire\Admin\Reports;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Versement;
use App\Models\Collecte;
use App\Models\Payment;
use App\Models\Motard;
use App\Models\Moto;
use App\Models\Maintenance;
use App\Models\Accident;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

#[Layout('components.dashlite-layout')]
class Monthly extends Component
{
    public $month;
    public $year;

    // Stats
    public $totalVersements = 0;
    public $totalAttendu = 0;
    public $totalCollecte = 0;
    public $totalPaiements = 0;
    public $totalMaintenances = 0;
    public $totalAccidents = 0;
    public $motardsActifs = 0;
    public $motosActives = 0;
    public $versementsParSemaine = [];
    public $topMotards = [];
    public $tauxRecouvrement = 0;

    public function mount()
    {
        $now = Carbon::now();
        $this->month = $now->format('m');
        $this->year = $now->format('Y');
        $this->loadStats();
    }

    public function updatedMonth() { $this->loadStats(); }
    public function updatedYear() { $this->loadStats(); }

    public function loadStats()
    {
        $startOfMonth = Carbon::create($this->year, $this->month, 1)->startOfMonth();
        $endOfMonth = Carbon::create($this->year, $this->month, 1)->endOfMonth();

        // Versements du mois
        $versements = Versement::whereBetween('date_versement', [$startOfMonth, $endOfMonth])->get();
        $this->totalVersements = $versements->sum('montant');
        $this->totalAttendu = $versements->sum('montant_attendu');

        // Taux de recouvrement
        $this->tauxRecouvrement = $this->totalAttendu > 0
            ? round(($this->totalVersements / $this->totalAttendu) * 100, 1)
            : 0;

        // Collectes
        $this->totalCollecte = Collecte::whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->sum('montant_collecte');

        // Paiements propriétaires
        $this->totalPaiements = Payment::whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->whereIn('statut', ['paye', 'valide'])
            ->sum('total_paye');

        // Maintenances
        $this->totalMaintenances = Maintenance::whereBetween('date_intervention', [$startOfMonth, $endOfMonth])
            ->sum(DB::raw('COALESCE(cout_pieces, 0) + COALESCE(cout_main_oeuvre, 0)'));

        // Accidents
        $this->totalAccidents = Accident::whereBetween('date_heure', [$startOfMonth, $endOfMonth])->count();

        // Motards et motos actifs
        $this->motardsActifs = Motard::where('is_active', true)->count();
        $this->motosActives = Moto::where('statut', 'actif')->count();

        // Versements par semaine
        $this->versementsParSemaine = [];
        $currentDate = $startOfMonth->copy();
        $weekNum = 1;
        while ($currentDate <= $endOfMonth) {
            $weekStart = $currentDate->copy();
            $weekEnd = $currentDate->copy()->endOfWeek()->min($endOfMonth);

            $weekVersements = Versement::whereBetween('date_versement', [$weekStart, $weekEnd])->sum('montant');
            $weekAttendu = Versement::whereBetween('date_versement', [$weekStart, $weekEnd])->sum('montant_attendu');

            $this->versementsParSemaine[] = [
                'semaine' => 'Sem. ' . $weekNum,
                'periode' => $weekStart->format('d/m') . ' - ' . $weekEnd->format('d/m'),
                'montant' => $weekVersements,
                'attendu' => $weekAttendu,
            ];

            $currentDate = $weekEnd->copy()->addDay();
            $weekNum++;
        }

        // Top 10 motards du mois
        $this->topMotards = Versement::whereBetween('date_versement', [$startOfMonth, $endOfMonth])
            ->with('motard.user')
            ->selectRaw('motard_id, SUM(montant) as total, COUNT(*) as nb_versements')
            ->groupBy('motard_id')
            ->orderByDesc('total')
            ->take(10)
            ->get();
    }

    public function export()
    {
        $filename = 'rapport_mensuel_admin_' . $this->month . '_' . $this->year . '.csv';

        return response()->streamDownload(function() {
            $handle = fopen('php://output', 'w');

            $moisNom = Carbon::create($this->year, $this->month, 1)->translatedFormat('F Y');
            fputcsv($handle, ['Rapport Mensuel Admin - ' . $moisNom]);
            fputcsv($handle, []);
            fputcsv($handle, ['Total Versements', number_format($this->totalVersements) . ' FC']);
            fputcsv($handle, ['Total Attendu', number_format($this->totalAttendu) . ' FC']);
            fputcsv($handle, ['Taux Recouvrement', $this->tauxRecouvrement . '%']);
            fputcsv($handle, ['Total Paiements Propriétaires', number_format($this->totalPaiements) . ' FC']);
            fputcsv($handle, ['Total Maintenances', number_format($this->totalMaintenances) . ' FC']);
            fputcsv($handle, ['Nombre Accidents', $this->totalAccidents]);

            fclose($handle);
        }, $filename);
    }

    public function exportPdf()
    {
        $this->loadStats();

        $startOfMonth = Carbon::create($this->year, $this->month, 1)->startOfMonth();
        $endOfMonth = Carbon::create($this->year, $this->month, 1)->endOfMonth();

        $stats = [
            'mois' => Carbon::create($this->year, $this->month, 1)->translatedFormat('F Y'),
            'totalCollecte' => $this->totalVersements,
            'totalAttendu' => $this->totalAttendu,
            'nombreVersements' => Versement::whereBetween('date_versement', [$startOfMonth, $endOfMonth])->count(),
            'versementsPayes' => Versement::whereBetween('date_versement', [$startOfMonth, $endOfMonth])->where('statut', 'payé')->count(),
            'versementsEnRetard' => Versement::whereBetween('date_versement', [$startOfMonth, $endOfMonth])->where('statut', 'en_retard')->count(),
            'versementsPartiels' => Versement::whereBetween('date_versement', [$startOfMonth, $endOfMonth])->whereIn('statut', ['partiel', 'partiellement_payé'])->count(),
            'arrieres' => max(0, $this->totalAttendu - $this->totalVersements),
            'tauxRecouvrement' => $this->tauxRecouvrement,
            'motardsActifs' => $this->motardsActifs,
            'motosActives' => $this->motosActives,
            'joursAvecVersements' => Versement::whereBetween('date_versement', [$startOfMonth, $endOfMonth])
                ->selectRaw('DATE(date_versement) as date')
                ->groupBy('date')
                ->get()
                ->count(),
            'moyenneJournaliere' => 0,
            'versementsParSemaine' => collect($this->versementsParSemaine)->map(function($s) {
                return (object)['semaine' => $s['semaine'], 'total' => $s['montant'], 'count' => 0];
            }),
            'topMotards' => $this->topMotards,
            'paiementsProprietaires' => [
                'totalPaye' => $this->totalPaiements,
                'nombrePaiements' => Payment::whereBetween('created_at', [$startOfMonth, $endOfMonth])->whereIn('statut', ['paye', 'valide'])->count(),
                'enAttente' => Payment::whereBetween('created_at', [$startOfMonth, $endOfMonth])->whereIn('statut', ['en_attente', 'demande'])->count(),
            ],
            'maintenance' => [
                'total' => Maintenance::whereBetween('date_intervention', [$startOfMonth, $endOfMonth])->count(),
                'cout' => $this->totalMaintenances,
            ],
            'accidents' => [
                'total' => $this->totalAccidents,
                'cout' => Accident::whereBetween('date_heure', [$startOfMonth, $endOfMonth])->sum('cout_reel') ?? 0,
            ],
        ];

        if ($stats['joursAvecVersements'] > 0) {
            $stats['moyenneJournaliere'] = $stats['totalCollecte'] / $stats['joursAvecVersements'];
        }

        $pdf = Pdf::loadView('pdf.reports.monthly', [
            'title' => 'Rapport Mensuel - Administration',
            'subtitle' => $stats['mois'],
            'stats' => $stats,
        ]);

        $pdf->setPaper('A4', 'portrait');

        return response()->streamDownload(function() use ($pdf) {
            echo $pdf->output();
        }, 'rapport_mensuel_admin_' . $this->month . '_' . $this->year . '.pdf');
    }

    public function render()
    {
        return view('livewire.admin.reports.monthly');
    }
}
