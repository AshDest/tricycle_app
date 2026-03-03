<?php

namespace App\Livewire\Cleaner;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Lavage;
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

