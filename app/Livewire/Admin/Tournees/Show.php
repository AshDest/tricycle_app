<?php

namespace App\Livewire\Admin\Tournees;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Tournee;

#[Layout('components.dashlite-layout')]
class Show extends Component
{
    public $tournee;

    public function mount(Tournee $tournee)
    {
        $this->tournee = $tournee;
    }

    public function render()
    {
        return view('livewire.admin.tournees.show');
    }
}
