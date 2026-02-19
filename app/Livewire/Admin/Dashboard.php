<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\User;
use App\Models\Motard;
use App\Models\Proprietaire;
use App\Models\Moto;
use App\Models\Versement;
use App\Models\Tournee;
use App\Models\Maintenance;
use App\Models\Accident;
use App\Services\PaymentService;
use Carbon\Carbon;

#[Layout('components.dashlite-layout')]
class Dashboard extends Component
{
    public $totalUsers = 0;
    public $totalMotards = 0;
    public $motardsActifs = 0;
    public $totalProprietaires = 0;
    public $totalMotos = 0;
    public $motosActives = 0;

    // Finances
    public $versementsAujourdhui = 0;
    public $versementsAttenduAujourdhui = 0;
    public $versementsCeMois = 0;
    public $arrieresCumules = 0;

    // Tournées
    public $tourneesAujourdhui = 0;
    public $tourneesTerminees = 0;
    public $tourneesEnCours = 0;

    // Alertes
    public $motardsEnRetard = 0;
    public $maintenancesEnAttente = 0;
    public $accidentsNonResolus = 0;

    // Dernières activités
    public $derniersVersements = [];
    public $dernieresAlerts = [];

    public function mount()
    {
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();

        // Compteurs généraux
        $this->totalUsers = User::count();
        $this->totalMotards = Motard::count();
        $this->motardsActifs = Motard::where('is_active', true)->count();
        $this->totalProprietaires = Proprietaire::count();
        $this->totalMotos = Moto::count();
        $this->motosActives = Moto::where('statut', 'actif')->count();

        // Finances aujourd'hui
        $versementsToday = Versement::whereDate('date_versement', $today)->get();
        $this->versementsAujourdhui = $versementsToday->sum('montant');
        $this->versementsAttenduAujourdhui = $versementsToday->sum('montant_attendu');

        // Finances ce mois
        $this->versementsCeMois = Versement::whereDate('date_versement', '>=', $startOfMonth)->sum('montant');

        // Arriérés cumulés (total de tous les arriérés)
        $this->arrieresCumules = Versement::selectRaw('COALESCE(SUM(GREATEST(0, COALESCE(montant_attendu, 0) - COALESCE(montant, 0))), 0) as total')
            ->value('total') ?? 0;

        // Tournées
        $tourneesToday = Tournee::whereDate('date', $today)->get();
        $this->tourneesAujourdhui = $tourneesToday->count();
        $this->tourneesTerminees = $tourneesToday->where('statut', 'terminee')->count();
        $this->tourneesEnCours = $tourneesToday->where('statut', 'en_cours')->count();

        // Alertes
        $this->motardsEnRetard = Motard::whereHas('versements', function($q) {
            $q->whereIn('statut', ['en_retard', 'non_effectue']);
        })->count();
        $this->maintenancesEnAttente = Maintenance::where('statut', 'en_attente')->count();
        $this->accidentsNonResolus = Accident::whereNull('reparation_terminee_at')->count();

        // Derniers versements
        $this->derniersVersements = Versement::with(['motard.user', 'moto'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
    }

    public function render()
    {
        return view('livewire.admin.dashboard');
    }
}
