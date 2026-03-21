<?php

namespace App\Livewire\Cleaner;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Lavage;
use App\Models\DepenseLavage;
use App\Models\KwadoService;
use App\Models\SystemSetting;
use Carbon\Carbon;

#[Layout('components.dashlite-layout')]
class Dashboard extends Component
{
    public $lavagesAujourdhui = 0;
    public $chiffreAffairesJour = 0;
    public $chiffreAffairesMois = 0;
    public $partOkamiJour = 0;
    public $partOkamiMois = 0;
    public $lavagesInternes = 0;
    public $lavagesExternes = 0;
    public $derniersLavages = [];

    // Solde et dépenses
    public $soldeActuel = 0;
    public $depensesJour = 0;
    public $depensesMois = 0;
    public $beneficeNetMois = 0;

    // KWADO stats
    public $kwadoJour = 0;
    public $kwadoRecettesJour = 0;
    public $kwadoMois = 0;
    public $kwadoRecettesMois = 0;
    public $derniersKwado = [];

    // Prix configurés
    public $prixSimple = 0;
    public $prixComplet = 0;
    public $prixPremium = 0;

    public function mount()
    {
        $cleaner = auth()->user()->cleaner;

        if (!$cleaner) {
            session()->flash('error', 'Profil laveur non trouvé.');
            return;
        }

        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();

        // Solde actuel
        $this->soldeActuel = $cleaner->solde_actuel;

        // Lavages du jour
        $lavagesJour = Lavage::where('cleaner_id', $cleaner->id)
            ->whereDate('date_lavage', $today)
            ->where('statut_paiement', 'payé')
            ->get();

        $this->lavagesAujourdhui = $lavagesJour->count();
        $this->chiffreAffairesJour = $lavagesJour->sum('part_cleaner');
        $this->partOkamiJour = $lavagesJour->sum('part_okami');
        $this->lavagesInternes = $lavagesJour->where('is_externe', false)->count();
        $this->lavagesExternes = $lavagesJour->where('is_externe', true)->count();

        // Lavages du mois
        $lavagesMois = Lavage::where('cleaner_id', $cleaner->id)
            ->whereBetween('date_lavage', [$startOfMonth, now()])
            ->where('statut_paiement', 'payé')
            ->get();

        $this->chiffreAffairesMois = $lavagesMois->sum('part_cleaner');
        $this->partOkamiMois = $lavagesMois->sum('part_okami');

        // Dépenses du jour et du mois
        $this->depensesJour = DepenseLavage::where('cleaner_id', $cleaner->id)
            ->whereDate('date_depense', $today)
            ->sum('montant');

        $this->depensesMois = DepenseLavage::where('cleaner_id', $cleaner->id)
            ->whereBetween('date_depense', [$startOfMonth, now()])
            ->sum('montant');

        // Bénéfice net du mois (recettes - dépenses)
        $this->beneficeNetMois = $this->chiffreAffairesMois - $this->depensesMois;

        // KWADO stats
        $kwadoJour = KwadoService::where('cleaner_id', $cleaner->id)
            ->whereDate('date_service', $today)
            ->where('statut_paiement', 'payé')
            ->get();

        $this->kwadoJour = $kwadoJour->count();
        $this->kwadoRecettesJour = $kwadoJour->sum('montant_encaisse');

        $kwadoMois = KwadoService::where('cleaner_id', $cleaner->id)
            ->whereBetween('date_service', [$startOfMonth, now()])
            ->where('statut_paiement', 'payé')
            ->get();

        $this->kwadoMois = $kwadoMois->count();
        $this->kwadoRecettesMois = $kwadoMois->sum('montant_encaisse');

        // Inclure KWADO dans le bénéfice net
        $this->beneficeNetMois += $this->kwadoRecettesMois;

        // Derniers KWADO
        $this->derniersKwado = KwadoService::where('cleaner_id', $cleaner->id)
            ->with('moto')
            ->orderBy('date_service', 'desc')
            ->limit(5)
            ->get();

        // Derniers lavages
        $this->derniersLavages = Lavage::where('cleaner_id', $cleaner->id)
            ->with('moto')
            ->orderBy('date_lavage', 'desc')
            ->limit(10)
            ->get();

        // Prix configurés
        $this->prixSimple = Lavage::getPrixLavage('simple');
        $this->prixComplet = Lavage::getPrixLavage('complet');
        $this->prixPremium = Lavage::getPrixLavage('premium');
    }

    public function render()
    {
        return view('livewire.cleaner.dashboard');
    }
}

