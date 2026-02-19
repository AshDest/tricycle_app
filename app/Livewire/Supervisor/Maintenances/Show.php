<?php

namespace App\Livewire\Supervisor\Maintenances;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Maintenance;

#[Layout('components.dashlite-layout')]
class Show extends Component
{
    public Maintenance $maintenance;

    public function mount(Maintenance $maintenance)
    {
        $this->maintenance = $maintenance->load(['moto.proprietaire.user', 'motard.user', 'validePar', 'accident']);
    }

    public function render()
    {
        return view('livewire.supervisor.maintenances.show');
    }
}

