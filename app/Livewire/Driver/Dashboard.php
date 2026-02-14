<?php

namespace App\Livewire\Driver;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Versement;
use Carbon\Carbon;

#[Layout('components.dashlite-layout')]
class Dashboard extends Component
{
    public $motard = null;
    public $moto = null;

    // Stats individuelles (pas de tableaux)
    public $versementAujourdhui = 0;
    public $montantAttendu = 0;
    public $totalMois = 0;
    public $joursPayes = 0;
    public $joursTravailles = 0;
    public $joursEnRetard = 0;
    public $arrieres = 0;
    public $statutDuJour = 'non_effectué';
    public $derniersVersements = [];

    public function mount()
    {
        $user = auth()->user();
        $this->motard = $user->motard;

        if ($this->motard) {
            $today = Carbon::today();
            $startOfMonth = Carbon::now()->startOfMonth();

            // Moto actuelle
            $this->moto = $this->motard->moto;
            $this->montantAttendu = $this->moto->montant_journalier_attendu ?? 5000;

            // Versement aujourd'hui
            $versementJour = Versement::where('motard_id', $this->motard->id)
                ->whereDate('date_versement', $today)
                ->first();

            if ($versementJour) {
                $this->versementAujourdhui = $versementJour->montant ?? 0;
                $this->statutDuJour = $versementJour->statut ?? 'non_effectué';
            }

            // Total du mois
            $this->totalMois = Versement::where('motard_id', $this->motard->id)
                ->whereDate('date_versement', '>=', $startOfMonth)
                ->sum('montant') ?? 0;

            // Jours payés ce mois
            $versementsMois = Versement::where('motard_id', $this->motard->id)
                ->whereDate('date_versement', '>=', $startOfMonth)
                ->get();

            $this->joursPayes = $versementsMois->where('statut', 'payé')->count();
            $this->joursEnRetard = $versementsMois->whereIn('statut', ['en_retard', 'non_effectué'])->count();
            $this->joursTravailles = $today->day; // Jours écoulés ce mois

            // Arriérés cumulés
            $this->arrieres = Versement::where('motard_id', $this->motard->id)
                ->whereIn('statut', ['en_retard', 'partiellement_payé'])
                ->selectRaw('COALESCE(SUM(montant_attendu - COALESCE(montant, 0)), 0) as total')
                ->value('total') ?? 0;

            // Derniers versements (historique)
            $this->derniersVersements = Versement::where('motard_id', $this->motard->id)
                ->with('moto')
                ->orderBy('date_versement', 'desc')
                ->take(10)
                ->get();
        }
    }

    public function render()
    {
        return view('livewire.driver.dashboard');
    }
}
