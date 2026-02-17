<?php
namespace App\Livewire\Supervisor\Accidents;
use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Accident;
#[Layout('components.dashlite-layout')]
class Show extends Component
{
    public Accident $accident;
    public function mount(Accident $accident)
    {
        $this->accident = $accident->load(['moto.proprietaire.user', 'motard.user', 'validePar']);
    }
    public function render()
    {
        return view('livewire.supervisor.accidents.show');
    }
}
