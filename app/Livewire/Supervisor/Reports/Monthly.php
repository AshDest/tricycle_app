<?php

namespace App\Livewire\Supervisor\Reports;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Versement;
use App\Models\Motard;
use App\Models\Moto;
use Carbon\Carbon;

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
        ];

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

        // Top motards
        $topMotards = Motard::with('user')
            ->withSum(['versements' => fn($q) => $q->whereBetween('date_versement', [$startOfMonth, $endOfMonth])], 'montant')
            ->orderByDesc('versements_sum_montant')
            ->limit(10)
            ->get();
        $this->stats['topMotards'] = $topMotards;
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

    public function render()
    {
        return view('livewire.supervisor.reports.monthly');
    }
}
