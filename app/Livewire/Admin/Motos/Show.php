<?php

namespace App\Livewire\Admin\Motos;

use Livewire\Component;
use App\Models\Moto;

class Show extends Component
{
    public Moto $moto;
    public $stats = [];
    public $derniersVersements = [];

    public function mount(Moto $moto)
    {
        $this->moto = $moto->load(['proprietaire.user', 'motard.user', 'maintenances', 'accidents']);
        $this->stats = $moto->getStatistiquesFinancieres();
        $this->derniersVersements = $moto->versements()
            ->with('motard.user')
            ->orderBy('date_versement', 'desc')
            ->take(10)
            ->get();
    }

    public function changeStatut(string $statut)
    {
        $this->moto->update(['statut' => $statut]);
        $this->moto->refresh();
    }

    public function render()
    {
        return view('livewire.admin.motos.show');
    }
}
