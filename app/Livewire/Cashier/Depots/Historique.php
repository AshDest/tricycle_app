<?php

namespace App\Livewire\Cashier\Depots;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Collecte;

/**
 * Historique des dépôts du caissier
 */
#[Layout('components.dashlite-layout')]
class Historique extends Component
{
    use WithPagination;

    public $filterStatut = '';
    public $filterDate = '';
    public $perPage = 15;

    protected $queryString = ['filterStatut', 'filterDate'];

    public function updatingFilterStatut() { $this->resetPage(); }
    public function updatingFilterDate() { $this->resetPage(); }

    public function render()
    {
        $caissier = auth()->user()->caissier;

        $depots = Collecte::where('caissier_id', $caissier?->id)
            ->with(['tournee.collecteur.user'])
            ->when($this->filterStatut === 'valide', fn($q) => $q->where('valide_par_collecteur', true))
            ->when($this->filterStatut === 'en_attente', fn($q) => $q->where('valide_par_collecteur', false))
            ->when($this->filterDate, fn($q) => $q->whereDate('created_at', $this->filterDate))
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        // Stats
        $totalDepose = Collecte::where('caissier_id', $caissier?->id)->sum('montant_collecte');
        $totalValide = Collecte::where('caissier_id', $caissier?->id)->where('valide_par_collecteur', true)->sum('montant_collecte');
        $enAttente = Collecte::where('caissier_id', $caissier?->id)->where('valide_par_collecteur', false)->count();

        return view('livewire.cashier.depots.historique', [
            'depots' => $depots,
            'totalDepose' => $totalDepose,
            'totalValide' => $totalValide,
            'enAttente' => $enAttente,
        ]);
    }
}
