<?php

namespace App\Livewire\Admin\Versements;

use Livewire\Component;
use App\Models\Versement;

class Show extends Component
{
    public Versement $versement;

    public function mount(Versement $versement)
    {
        $this->versement = $versement->load([
            'motard.user',
            'moto',
            'caissier.user',
            'collecte',
            'validePar',
        ]);
    }

    public function render()
    {
        return view('livewire.admin.versements.show');
    }
}
