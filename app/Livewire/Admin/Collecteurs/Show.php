<?php

namespace App\Livewire\Admin\Collecteurs;

use Livewire\Component;
use App\Models\Collecteur;

class Show extends Component
{
    public $collecteur;

    public function mount(Collecteur $collecteur)
    {
        $this->collecteur = $collecteur;
    }

    public function render()
    {
        return view('livewire.admin.collecteurs.show');
    }
}
