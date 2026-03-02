<?php

namespace App\Livewire\Owner;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Proprietaire;
use App\Models\Versement;
use App\Models\Payment;
use App\Models\Maintenance;
use App\Services\RepartitionService;
use Carbon\Carbon;

#[Layout('components.dashlite-layout')]
class Dashboard extends Component
{
    public $proprietaire;
    public $motos = [];

    // Statistiques
    public $totalMotos = 0;
    public $motosActives = 0;
    public $revenusMois = 0;
    public $revenusTotal = 0;
    public $prochainPaiement = 0;
    public $maintenancesEnCours = 0;
    public $totalArrieres = 0;
    public $paiementsEnAttente = 0;

    // Répartition hebdomadaire
    public $repartitionHebdo = null;
    public $partProprietaireHebdo = 0;
    public $partOkamiHebdo = 0;

    // Listes
    public $derniersVersements = [];
    public $derniersPaiements = [];

    public function mount()
    {
        $user = auth()->user();
        $this->proprietaire = $user->proprietaire;

        if (!$this->proprietaire) {
            return;
        }

        // Charger les motos
        $this->motos = $this->proprietaire->motos()->with('motard.user')->get();
        $motoIds = $this->motos->pluck('id');

        // Statistiques motos
        $this->totalMotos = $this->motos->count();
        $this->motosActives = $this->motos->where('statut', 'actif')->count();

        // Versements du mois en cours
        $this->revenusMois = Versement::whereIn('moto_id', $motoIds)
            ->whereMonth('date_versement', now()->month)
            ->whereYear('date_versement', now()->year)
            ->sum('montant');

        // Total des versements
        $this->revenusTotal = Versement::whereIn('moto_id', $motoIds)->sum('montant');

        // Arriérés = somme des écarts positifs (montant_attendu - montant)
        $this->totalArrieres = Versement::whereIn('moto_id', $motoIds)
            ->whereRaw('montant < montant_attendu')
            ->selectRaw('SUM(montant_attendu - montant) as total')
            ->value('total') ?? 0;

        // Répartition hebdomadaire
        $this->repartitionHebdo = RepartitionService::getRepartitionHebdomadaireProprietaire($this->proprietaire);
        $this->partProprietaireHebdo = $this->repartitionHebdo['total_part_proprietaire'] ?? 0;
        $this->partOkamiHebdo = $this->repartitionHebdo['total_part_okami'] ?? 0;

        // Prochain paiement estimé = part propriétaire non encore payée
        $totalPartProprietaire = RepartitionService::getPartProprietaire($this->revenusTotal);
        $totalPayeAuProprietaire = $this->proprietaire->payments()
            ->whereIn('statut', ['paye', 'payé', 'valide'])
            ->sum('total_paye');
        $this->prochainPaiement = max(0, $totalPartProprietaire - $totalPayeAuProprietaire);

        // Maintenances en cours
        $this->maintenancesEnCours = Maintenance::whereIn('moto_id', $motoIds)
            ->whereIn('statut', ['en_attente', 'en_cours'])
            ->count();

        // Paiements en attente
        $this->paiementsEnAttente = $this->proprietaire->payments()
            ->whereIn('statut', ['en_attente', 'demande'])
            ->count();

        // Derniers versements (5)
        $this->derniersVersements = Versement::whereIn('moto_id', $motoIds)
            ->with(['motard.user', 'moto'])
            ->orderBy('date_versement', 'desc')
            ->take(5)
            ->get();

        // Derniers paiements (5)
        $this->derniersPaiements = $this->proprietaire->payments()
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
    }

    public function render()
    {
        return view('livewire.owner.dashboard');
    }
}
