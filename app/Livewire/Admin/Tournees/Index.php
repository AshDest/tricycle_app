<?php

namespace App\Livewire\Admin\Tournees;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Tournee;

#[Layout('components.dashlite-layout')]
class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $filterZone = '';
    public $filterStatut = '';
    public $perPage = 15;

    protected $queryString = ['search', 'filterZone', 'filterStatut'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterZone()
    {
        $this->resetPage();
    }

    public function updatingFilterStatut()
    {
        $this->resetPage();
    }

    public function delete(Tournee $tournee)
    {
        $tournee->forceDelete();
        session()->flash('success', 'Tournee supprimee avec succes.');
    }

    public function render()
    {
        $tournees = Tournee::with(['collecteur'])
            ->when($this->search, function ($q) {
                $q->whereHas('collecteur', function ($q2) {
                    $q2->where('numero_identifiant', 'like', '%' . $this->search . '%');
                })->orWhere('zone', 'like', '%' . $this->search . '%');
            })
            ->when($this->filterZone, function ($q) {
                $q->where('zone', $this->filterZone);
            })
            ->when($this->filterStatut, function ($q) {
                $q->where('statut', $this->filterStatut);
            })
            ->orderBy('date', 'desc')
            ->paginate($this->perPage);

        $zones = Tournee::distinct()->pluck('zone')->filter();

        return view('livewire.admin.tournees.index', compact('tournees', 'zones'));
    }
}
