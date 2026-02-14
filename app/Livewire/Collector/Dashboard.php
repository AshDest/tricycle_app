<?php

namespace App\Livewire\Collector;

use Livewire\Component;
use App\Models\Collecteur;
use App\Models\Tournee;
use App\Models\Collecte;
use Carbon\Carbon;

class Dashboard extends Component
{
    public $collecteur;
    public $tourneesDuJour = [];
    public $collectesDuJour = [];
    public $totalEncaisse = 0;
    public $historiqueRecent = [];

    public function mount()
    {
        $user = auth()->user();
        $this->collecteur = $user->collecteur;

        if ($this->collecteur) {
            // Tournees du jour
            $this->tourneesDuJour = Tournee::where('collecteur_id', $this->collecteur->id)
                ->whereDate('date', Carbon::today())
                ->with('collectes.caissier.user')
                ->get();

            // Collectes du jour
            $tourneeIds = $this->tourneesDuJour->pluck('id');
            $this->collectesDuJour = Collecte::whereIn('tournee_id', $tourneeIds)
                ->with('caissier.user')
                ->get();

            // Total encaisse aujourd'hui
            $this->totalEncaisse = $this->collectesDuJour->sum('montant_collecte');

            // Historique recent (10 dernieres tournees)
            $this->historiqueRecent = Tournee::where('collecteur_id', $this->collecteur->id)
                ->orderBy('date', 'desc')
                ->take(10)
                ->get();
        }
    }

    public function render()
    {
        return view('livewire.collector.dashboard');
    }
}
