<?php

namespace App\Livewire\Supervisor;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Motard;
use App\Models\Moto;
use App\Models\Versement;
use App\Models\Tournee;
use App\Models\Lavage;
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

    // Détails des arriérés
    public $arrieresJour = 0;
    public $arrieresSemaine = 0;
    public $arrieresMois = 0;
    public $motardsAvecArrieres = 0;
    public $topMotardsArrieres = [];

    // Solde total des versements
    public $soldeVersementsTotal = 0;
    public $soldeVersementsSemaine = 0;
    public $soldeVersementsMois = 0;
    public $repartitionHebdo = [];

    // Solde OKAMI des lavages (20% des lavages internes)
    public $soldeOkamiLavageTotal = 0;
    public $soldeOkamiLavageSemaine = 0;
    public $soldeOkamiLavageMois = 0;
    public $soldeOkamiLavageJour = 0;

    // Solde OKAMI des commissions (30% des commissions)
    public $soldeOkamiCommissionTotal = 0;
    public $commissionsParMois = [];

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
        $this->arrieresCumules = Versement::where('arrieres', '>', 0)->sum('arrieres') ?? 0;

        // Arriérés du jour
        $this->arrieresJour = Versement::whereDate('date_versement', $today)
            ->where('arrieres', '>', 0)
            ->sum('arrieres') ?? 0;

        // Arriérés de la semaine
        $this->arrieresSemaine = Versement::whereBetween('date_versement', [$startOfWeek, $today])
            ->where('arrieres', '>', 0)
            ->sum('arrieres') ?? 0;

        // Arriérés du mois
        $this->arrieresMois = Versement::whereBetween('date_versement', [$startOfMonth, $today])
            ->where('arrieres', '>', 0)
            ->sum('arrieres') ?? 0;

        // Nombre de motards avec arriérés
        $this->motardsAvecArrieres = Versement::where('arrieres', '>', 0)
            ->distinct('motard_id')
            ->count('motard_id');

        // Top 5 motards avec le plus d'arriérés
        $this->topMotardsArrieres = Versement::select('motard_id')
            ->selectRaw('SUM(arrieres) as total_arrieres')
            ->where('arrieres', '>', 0)
            ->groupBy('motard_id')
            ->orderByDesc('total_arrieres')
            ->limit(5)
            ->with('motard.user')
            ->get()
            ->map(function ($item) {
                return [
                    'motard_id' => $item->motard_id,
                    'nom' => $item->motard?->user?->name ?? 'N/A',
                    'total_arrieres' => $item->total_arrieres,
                ];
            })
            ->toArray();

        // Tournées du jour
        $this->tourneesAujourdhui = Tournee::whereDate('date', $today)
            ->with('collecteur.user')
            ->get();

        // Calcul du total des versements
        // Total historique
        $this->soldeVersementsTotal = Versement::sum('montant') ?? 0;

        // Total versements cette semaine
        $versementsSemaine = Versement::whereBetween('date_versement', [$startOfWeek, $today])->get();
        $this->soldeVersementsSemaine = $versementsSemaine->sum('montant') ?? 0;

        // Total versements ce mois
        $versementsMois = Versement::whereBetween('date_versement', [$startOfMonth, $today])->get();
        $this->soldeVersementsMois = $versementsMois->sum('montant') ?? 0;

        // ===== Calcul du solde OKAMI des lavages (20% des lavages internes) =====

        // Total historique des lavages OKAMI
        $this->soldeOkamiLavageTotal = Lavage::where('is_externe', false)
            ->where('statut_paiement', 'payé')
            ->sum('part_okami') ?? 0;

        // Solde OKAMI lavages cette semaine
        $this->soldeOkamiLavageSemaine = Lavage::where('is_externe', false)
            ->where('statut_paiement', 'payé')
            ->whereBetween('date_lavage', [$startOfWeek, $today->endOfDay()])
            ->sum('part_okami') ?? 0;

        // Solde OKAMI lavages ce mois
        $this->soldeOkamiLavageMois = Lavage::where('is_externe', false)
            ->where('statut_paiement', 'payé')
            ->whereBetween('date_lavage', [$startOfMonth, $today->endOfDay()])
            ->sum('part_okami') ?? 0;

        // Solde OKAMI lavages aujourd'hui
        $this->soldeOkamiLavageJour = Lavage::where('is_externe', false)
            ->where('statut_paiement', 'payé')
            ->whereDate('date_lavage', $today)
            ->sum('part_okami') ?? 0;

        // ===== Calcul du solde OKAMI des commissions (30%) =====
        $paymentService = new \App\Services\PaymentService();
        $this->soldeOkamiCommissionTotal = $paymentService->getSoldeCommissionOkami();

        // Commissions validées par mois (3 derniers mois)
        $this->commissionsParMois = \App\Models\CommissionMobileMensuelle::where('statut', 'valide')
            ->orderByDesc('annee')
            ->orderByDesc('mois')
            ->limit(3)
            ->get()
            ->map(function ($c) {
                return [
                    'periode' => $c->periode_label,
                    'total' => $c->montant_total,
                    'part_nth' => $c->part_nth,
                    'part_okami' => $c->part_okami,
                ];
            })
            ->toArray();

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

