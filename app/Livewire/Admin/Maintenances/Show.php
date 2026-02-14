<?php

namespace App\Livewire\Admin\Maintenances;

use Livewire\Component;
use App\Models\Maintenance;

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
