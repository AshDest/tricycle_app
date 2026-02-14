<?php

namespace App\Livewire\Driver;

use Livewire\Component;
use App\Services\PaymentService;
use App\Models\Versement;

class Dashboard extends Component
{
    public $motard;
    public $moto;
    public $stats = [];
    public $arrieres = [];
    public $statutDuJour;
    public $derniersVersements = [];
    public $notifications = [];

    public function mount()
    {
        $user = auth()->user();
        $this->motard = $user->motard;

        if ($this->motard) {
            $paymentService = new PaymentService();
            $this->stats = $paymentService->getDriverStats($this->motard);
            $this->arrieres = $paymentService->getArrearsForDriver($this->motard);

            // Moto actuelle
            $this->moto = $this->motard->motoActuelle;

            // Statut du jour
            $this->statutDuJour = $this->motard->statut_du_jour;

            // Derniers versements (historique)
            $this->derniersVersements = $this->motard->versements()
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
