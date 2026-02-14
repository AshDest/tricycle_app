<?php

namespace App\Livewire\Admin\Maintenances;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Maintenance;

#[Layout('components.dashlite-layout')]
class Show extends Component
{
    public $maintenance;

    public function mount(Maintenance $maintenance)
    {
        $this->maintenance = $maintenance;
    }

    public function render()
    {
        return view('livewire.admin.maintenances.show');
    }
}
