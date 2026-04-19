<?php

namespace App\Livewire\Cleaner;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Lavage;
use App\Models\DepenseLavage;
use App\Models\KwadoService;
use App\Models\Moto;
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

    // Conformité lavage hebdomadaire
    public $motosNonConformes = [];
    public $totalMotosSysteme = 0;
    public $motosConformes = 0;
    public $tauxConformite = 0;
    public $semaineLabel = '';

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

        // Conformité lavage hebdomadaire (3 lavages/semaine requis)
        $this->loadConformiteLavage();
    }

    /**
     * Charger les statistiques de conformité lavage hebdomadaire
     */
    protected function loadConformiteLavage()
    {
        $debutSemaine = Carbon::now()->startOfWeek(Carbon::MONDAY);
        $finSemaine = Carbon::now()->endOfWeek(Carbon::SUNDAY);
        $this->semaineLabel = $debutSemaine->format('d/m') . ' - ' . $finSemaine->format('d/m/Y');

        // Toutes les motos actives du système
        $motosActives = Moto::where('statut', 'actif')
            ->with(['proprietaire.user', 'motard.user'])
            ->get();

        $this->totalMotosSysteme = $motosActives->count();

        $this->motosNonConformes = [];
        $conformes = 0;

        foreach ($motosActives as $moto) {
            $nbLavages = Lavage::where('moto_id', $moto->id)
                ->where('is_externe', false)
                ->whereBetween('date_lavage', [$debutSemaine, $finSemaine])
                ->where('statut_paiement', 'payé')
                ->count();

            if ($nbLavages >= 3) {
                $conformes++;
            } else {
                $this->motosNonConformes[] = [
                    'id' => $moto->id,
                    'plaque' => $moto->plaque_immatriculation,
                    'proprietaire' => $moto->proprietaire?->user?->name ?? 'N/A',
                    'motard' => $moto->motard?->user?->name ?? 'Non assigné',
                    'nb_lavages' => $nbLavages,
                    'manquants' => 3 - $nbLavages,
                ];
            }
        }

        $this->motosConformes = $conformes;
        $this->tauxConformite = $this->totalMotosSysteme > 0
            ? round(($conformes / $this->totalMotosSysteme) * 100, 1)
            : 0;
    }

    public function render()
    {
        return view('livewire.cleaner.dashboard');
    }
}

