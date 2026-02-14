<?php

namespace App\Livewire\Admin\Motards;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Motard;
use App\Models\Versement;
use Carbon\Carbon;

#[Layout('components.dashlite-layout')]
class Show extends Component
{
    public Motard $motard;
    public $stats = [];
    public $derniersVersements = [];

    public function mount(Motard $motard)
    {
        $this->motard = $motard->load(['user', 'motoActuelle', 'motos']);

        $this->stats = $motard->getPerformanceRecap();
        $this->stats['total_verse'] = $motard->versements()->sum('montant');

        $this->derniersVersements = $motard->versements()
            ->with('moto')
            ->orderBy('date_versement', 'desc')
            ->take(10)
            ->get();
    }

    public function toggleActive()
    {
        $this->motard->update(['is_active' => !$this->motard->is_active]);
        $this->motard->refresh();
    }

    public function render()
    {
        return view('livewire.admin.motards.show');
    }
}
