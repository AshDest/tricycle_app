<?php

namespace App\Livewire\Driver;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Motard;

#[Layout('components.dashlite-layout')]
class Statut extends Component
{
    public $motard;

    public function mount()
    {
        $this->motard = auth()->user()->motard;
    }

    public function toggleStatut()
    {
        if ($this->motard) {
            // Logic to update driver status can be added here
        }
    }

    public function render()
    {
        return view('livewire.driver.statut');
    }
}
