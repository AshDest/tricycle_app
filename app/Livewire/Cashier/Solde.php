<?php

namespace App\Livewire\Cashier;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.dashlite-layout')]
class Solde extends Component
{
    public $caissier;
    public $solde_actuel = 0;
    public $total_collecte = 0;
    public $total_entrants = 0;

    public function mount()
    {
        $this->caissier = auth()->user()->caissier;
        if ($this->caissier) {
            $this->solde_actuel = $this->caissier->solde_actuel ?? 0;
            $this->calculerTotaux();
        }
    }

    public function calculerTotaux()
    {
        if ($this->caissier) {
            // Calculate recent totals
            $this->total_entrants = $this->caissier->versements()
                ->whereNull('collecte_id')
                ->sum('montant');

            $this->total_collecte = $this->caissier->collectes()
                ->sum('montant_total');
        }
    }

    public function render()
    {
        return view('livewire.cashier.solde');
    }
}
