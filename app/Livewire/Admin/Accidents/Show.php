<?php

namespace App\Livewire\Admin\Accidents;

use Livewire\Component;
use App\Models\Accident;

class Show extends Component
{
    public $accident;

    public function mount(Accident $accident)
    {
        $this->accident = $accident;
    }

    public function render()
    {
        return view('livewire.admin.accidents.show');
    }
}
