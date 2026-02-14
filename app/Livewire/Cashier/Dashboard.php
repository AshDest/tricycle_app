<?php

namespace App\Livewire\Cashier;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Versement;
use App\Models\Caissier;
use Carbon\Carbon;

#[Layout('components.dashlite-layout')]
class Dashboard extends Component
{
    public $caissier;
    public $soldeActuel = 0;
    public $versementsAujourdhui = [];
    public $totalVersementsAujourdhui = 0;
    public $nombreVersements = 0;

    public function mount()
    {
        $user = auth()->user();
        $this->caissier = $user->caissier;

        if ($this->caissier) {
            // Solde actuel (argent en caisse non collecté)
            $this->soldeActuel = $this->caissier->solde_actuel;

            // Versements du jour
            $this->versementsAujourdhui = Versement::where('caissier_id', $this->caissier->id)
                ->whereDate('date_versement', Carbon::today())
                ->with(['motard.user', 'moto'])
                ->orderBy('created_at', 'desc')
                ->get();

            $this->totalVersementsAujourdhui = $this->versementsAujourdhui->sum('montant');
            $this->nombreVersements = $this->versementsAujourdhui->count();
        }
    }

    public function render()
    {
        return view('livewire.cashier.dashboard');
    }
}

