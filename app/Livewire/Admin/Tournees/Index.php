<?php

namespace App\Livewire\Admin\Tournees;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Tournee;
use App\Models\Collecte;
use Carbon\Carbon;

#[Layout('components.dashlite-layout')]
class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $filterZone = '';
    public $filterStatut = '';
    public $filterDate = '';
    public $perPage = 15;

    // Stats
    public $tourneesAujourdhui = 0;
    public $tourneesEnCours = 0;
    public $tourneesTerminees = 0;
    public $totalCollecteJour = 0;

    protected $queryString = ['search', 'filterZone', 'filterStatut', 'filterDate'];

    public function updatingSearch() { $this->resetPage(); }
    public function updatingFilterZone() { $this->resetPage(); }
    public function updatingFilterStatut() { $this->resetPage(); }
    public function updatingFilterDate() { $this->resetPage(); }

    public function delete(Tournee $tournee)
    {
        $tournee->delete();
        session()->flash('success', 'Tournée supprimée avec succès.');
    }

    public function changerStatut(Tournee $tournee, string $nouveauStatut)
    {
        $tournee->update(['statut' => $nouveauStatut]);
        session()->flash('success', 'Statut mis à jour.');
    }

    public function render()
    {
        $aujourdhui = Carbon::today();

        // Calculer les stats du jour
        $this->tourneesAujourdhui = Tournee::whereDate('date', $aujourdhui)->count();
        $this->tourneesEnCours = Tournee::whereDate('date', $aujourdhui)->where('statut', 'en_cours')->count();
        $this->tourneesTerminees = Tournee::whereDate('date', $aujourdhui)->where('statut', 'terminee')->count();
        $this->totalCollecteJour = Collecte::whereHas('tournee', fn($q) => $q->whereDate('date', $aujourdhui))
            ->sum('montant_collecte');

        $tournees = Tournee::with(['collecteur.user'])
            ->when($this->search, function ($q) {
                $q->whereHas('collecteur.user', fn($q2) => $q2->where('name', 'like', '%'.$this->search.'%'))
                  ->orWhere('zone', 'like', '%'.$this->search.'%');
            })
            ->when($this->filterZone, fn($q) => $q->where('zone', $this->filterZone))
            ->when($this->filterStatut, fn($q) => $q->where('statut', $this->filterStatut))
            ->when($this->filterDate, fn($q) => $q->whereDate('date', $this->filterDate))
            ->orderBy('date', 'desc')
            ->paginate($this->perPage);

        $zones = Tournee::distinct()->pluck('zone')->filter();

        return view('livewire.admin.tournees.index', compact('tournees', 'zones'));
    }
}
