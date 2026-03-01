<?php

namespace App\Livewire\Admin\Caissiers;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Caissier;
use App\Models\Versement;
use Carbon\Carbon;

#[Layout('components.dashlite-layout')]
class Show extends Component
{
    public Caissier $caissier;

    public function mount(Caissier $caissier)
    {
        $this->caissier = $caissier->load('user');
    }

    public function getStats()
    {
        $versements = Versement::where('caissier_id', $this->caissier->id);

        return [
            'total_versements' => $versements->count(),
            'versements_aujourdhui' => (clone $versements)->whereDate('date_versement', today())->count(),
            'montant_total' => $versements->sum('montant'),
            'montant_aujourdhui' => (clone $versements)->whereDate('date_versement', today())->sum('montant'),
            'montant_mois' => (clone $versements)->whereMonth('date_versement', now()->month)
                ->whereYear('date_versement', now()->year)->sum('montant'),
        ];
    }

    public function render()
    {
        $stats = $this->getStats();

        $derniersVersements = Versement::where('caissier_id', $this->caissier->id)
            ->with(['motard.user', 'moto'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return view('livewire.admin.caissiers.show', compact('stats', 'derniersVersements'));
    }
}
