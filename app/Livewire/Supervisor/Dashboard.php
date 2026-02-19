<?php

namespace App\Livewire\Supervisor;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Motard;
use App\Models\Moto;
use App\Models\Versement;
use App\Models\Tournee;
use Carbon\Carbon;

#[Layout('components.dashlite-layout')]
class Dashboard extends Component
{
    public $motardsEnRetard = [];
    public $versementsAujourdhui = 0;
    public $versementsAttenduAujourdhui = 0;
    public $arrieresCumules = 0;
    public $tourneesAujourdhui = [];

    public function mount()
    {
        $today = Carbon::today();

        // Motards en retard ou avec arriérés
        $this->motardsEnRetard = Motard::whereHas('versements', function($q) {
            $q->whereIn('statut', ['en_retard', 'non_effectue', 'partiel']);
        })->with('user')->take(10)->get();

        // Versements aujourd'hui
        $versementsToday = Versement::whereDate('date_versement', $today)->get();
        $this->versementsAujourdhui = $versementsToday->sum('montant');
        $this->versementsAttenduAujourdhui = $versementsToday->sum('montant_attendu');

        // Arriérés cumulés (total de tous les arriérés)
        $this->arrieresCumules = Versement::selectRaw('COALESCE(SUM(GREATEST(0, COALESCE(montant_attendu, 0) - COALESCE(montant, 0))), 0) as total')
            ->value('total') ?? 0;

        // Tournées du jour
        $this->tourneesAujourdhui = Tournee::whereDate('date', $today)
            ->with('collecteur.user')
            ->get();
    }

    public function render()
    {
        $totalMotards = Motard::count();
        $motardsActifs = Motard::where('is_active', true)->count();
        $totalMotos = Moto::count();
        $motosActives = Moto::where('statut', 'actif')->count();

        // Taux de collecte
        $tauxCollecte = $this->versementsAttenduAujourdhui > 0
            ? round(($this->versementsAujourdhui / $this->versementsAttenduAujourdhui) * 100, 1)
            : 0;

        // Dernières activités (versements récents)
        $dernieresActivites = Versement::with(['motard.user'])
            ->whereDate('created_at', Carbon::today())
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Alertes basées sur les arriérés
        $alertes = collect();
        if ($this->arrieresCumules > 0) {
            $alertes->push((object)[
                'message' => "Arriérés cumulés: " . number_format($this->arrieresCumules) . " FC",
                'color' => 'warning',
                'icon' => 'exclamation-triangle',
                'created_at' => now()
            ]);
        }

        $motardsEnRetardCount = $this->motardsEnRetard->count();
        if ($motardsEnRetardCount > 0) {
            $alertes->push((object)[
                'message' => "{$motardsEnRetardCount} motard(s) avec versements en retard",
                'color' => 'danger',
                'icon' => 'person-exclamation',
                'created_at' => now()
            ]);
        }

        return view('livewire.supervisor.dashboard', [
            'totalMotards' => $totalMotards,
            'motardsActifs' => $motardsActifs,
            'totalMotos' => $totalMotos,
            'motosActives' => $motosActives,
            'tauxCollecte' => $tauxCollecte,
            'dernieresActivites' => $dernieresActivites,
            'alertes' => $alertes,
        ]);
    }
}

