<?php

namespace App\Livewire\Supervisor;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Motard;
use App\Models\Moto;
use App\Models\Versement;
use App\Models\Tournee;
use App\Services\RepartitionService;
use Carbon\Carbon;

#[Layout('components.dashlite-layout')]
class Dashboard extends Component
{
    public $motardsEnRetard = [];
    public $versementsAujourdhui = 0;
    public $versementsAttenduAujourdhui = 0;
    public $arrieresCumules = 0;
    public $tourneesAujourdhui = [];

    // Solde OKAMI
    public $soldeOkamiTotal = 0;
    public $soldeOkamiSemaine = 0;
    public $soldeOkamiMois = 0;
    public $repartitionHebdo = [];

    public function mount()
    {
        $today = Carbon::today();
        $startOfWeek = Carbon::now()->startOfWeek();
        $startOfMonth = Carbon::now()->startOfMonth();

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

        // Calcul du solde OKAMI (part 1/6)
        // Total historique (part_okami stockée dans les versements)
        $this->soldeOkamiTotal = Versement::sum('part_okami') ?? 0;

        // Si part_okami n'est pas remplie, calculer à partir du montant
        if ($this->soldeOkamiTotal == 0) {
            $totalVersements = Versement::sum('montant') ?? 0;
            $this->soldeOkamiTotal = RepartitionService::getPartOkami($totalVersements);
        }

        // Solde OKAMI cette semaine
        $versementsSemaine = Versement::whereBetween('date_versement', [$startOfWeek, $today])->get();
        $this->soldeOkamiSemaine = $versementsSemaine->sum('part_okami') ?? 0;
        if ($this->soldeOkamiSemaine == 0) {
            $totalSemaine = $versementsSemaine->sum('montant') ?? 0;
            $this->soldeOkamiSemaine = RepartitionService::getPartOkami($totalSemaine);
        }

        // Solde OKAMI ce mois
        $versementsMois = Versement::whereBetween('date_versement', [$startOfMonth, $today])->get();
        $this->soldeOkamiMois = $versementsMois->sum('part_okami') ?? 0;
        if ($this->soldeOkamiMois == 0) {
            $totalMois = $versementsMois->sum('montant') ?? 0;
            $this->soldeOkamiMois = RepartitionService::getPartOkami($totalMois);
        }

        // Répartition hebdomadaire globale
        $this->repartitionHebdo = RepartitionService::getResumeHebdomadaireGlobal();
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

