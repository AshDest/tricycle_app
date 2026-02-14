<?php

namespace App\Livewire\Admin\Collecteurs;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Collecteur;

#[Layout('components.dashlite-layout')]
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
