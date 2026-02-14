<?php

namespace App\Livewire\Admin\Caissiers;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Caissier;

#[Layout('components.dashlite-layout')]
class Show extends Component
{
    public $caissier;

    public function mount(Caissier $caissier)
    {
        $this->caissier = $caissier;
    }

    public function render()
    {
        return view('livewire.admin.caissiers.show');
    }
}
