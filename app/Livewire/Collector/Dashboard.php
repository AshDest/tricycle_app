<?php

namespace App\Livewire\Collector;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Collecteur;
use App\Models\Tournee;
use App\Models\Collecte;
use Carbon\Carbon;
use Illuminate\Support\Collection;

#[Layout('components.dashlite-layout')]
class Dashboard extends Component
{
    public $collecteur = null;
    public $totalEncaisse = 0;
    public $collectesReussies = 0;
    public $tourneesTerminees = 0;
    public $totalTourneesJour = 0;

    public function mount()
    {
        $user = auth()->user();
        $this->collecteur = $user->collecteur;

        if ($this->collecteur) {
            $today = Carbon::today();

            // Tournées du jour
            $tourneesDuJour = Tournee::where('collecteur_id', $this->collecteur->id)
                ->whereDate('date', $today)
                ->get();

            $this->totalTourneesJour = $tourneesDuJour->count();
            $this->tourneesTerminees = $tourneesDuJour->where('statut', 'terminee')->count();

            // Collectes du jour via les tournées
            $tourneeIds = $tourneesDuJour->pluck('id');
            if ($tourneeIds->isNotEmpty()) {
                $collectesDuJour = Collecte::whereIn('tournee_id', $tourneeIds)->get();
                $this->totalEncaisse = $collectesDuJour->sum('montant_collecte') ?? 0;
                $this->collectesReussies = $collectesDuJour->where('statut', 'reussie')->count();
            }
        }
    }

    public function render()
    {
        $tourneesDuJour = collect();
        $collectesDuJour = collect();
        $historiqueRecent = collect();

        if ($this->collecteur) {
            $today = Carbon::today();

            // Tournées du jour
            $tourneesDuJour = Tournee::where('collecteur_id', $this->collecteur->id)
                ->whereDate('date', $today)
                ->with(['collectes.caissier'])
                ->get();

            // Collectes du jour
            $tourneeIds = $tourneesDuJour->pluck('id');
            if ($tourneeIds->isNotEmpty()) {
                $collectesDuJour = Collecte::whereIn('tournee_id', $tourneeIds)
                    ->with(['caissier'])
                    ->orderBy('created_at', 'desc')
                    ->get();
            }

            // Historique récent (10 dernières tournées)
            $historiqueRecent = Tournee::where('collecteur_id', $this->collecteur->id)
                ->orderBy('date', 'desc')
                ->take(10)
                ->get();
        }

        return view('livewire.collector.dashboard', [
            'tourneesDuJour' => $tourneesDuJour,
            'collectesDuJour' => $collectesDuJour,
            'historiqueRecent' => $historiqueRecent,
        ]);
    }
}
