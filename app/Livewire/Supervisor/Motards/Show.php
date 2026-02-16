<?php

namespace App\Livewire\Supervisor\Motards;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Motard;
use App\Models\Versement;
use Carbon\Carbon;

#[Layout('components.dashlite-layout')]
class Show extends Component
{
    public Motard $motard;

    public function mount(Motard $motard): void
    {
        $this->motard = $motard->load(['user', 'motoActuelle', 'versements' => function ($q) {
            $q->latest()->limit(10);
        }]);
    }

    public function toggleActive()
    {
        $this->motard->update(['is_active' => !$this->motard->is_active]);
        session()->flash('success', 'Statut du motard mis à jour.');
    }

    public function render()
    {
        // Statistiques du motard
        $stats = [
            'totalVersements' => $this->motard->versements()->count(),
            'totalMontant' => $this->motard->versements()->sum('montant'),
            'versementsPayes' => $this->motard->versements()->where('statut', 'paye')->count(),
            'versementsEnRetard' => $this->motard->versements()->where('statut', 'en_retard')->count(),
            'dernierVersement' => $this->motard->versements()->latest()->first(),
        ];

        // Derniers versements
        $derniersVersements = $this->motard->versements()
            ->with('caissier.user')
            ->latest()
            ->limit(10)
            ->get();

        return view('livewire.supervisor.motards.show', compact('stats', 'derniersVersements'));
    }
}

