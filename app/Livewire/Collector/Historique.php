<?php

namespace App\Livewire\Collector;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Tournee;

class Historique extends Component
{
    use WithPagination;

    public $search = '';
    public $filterStatut = '';
    public $perPage = 15;

    protected $queryString = ['search', 'filterStatut'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterStatut()
    {
        $this->resetPage();
    }

    public function render()
    {
        $collecteur_id = auth()->user()->collecteur?->id;

        $tournees = Tournee::where('collecteur_id', $collecteur_id)
            ->when($this->filterStatut, function ($q) {
                $q->where('statut', $this->filterStatut);
            })
            ->orderBy('date', 'desc')
            ->paginate($this->perPage);

        return view('livewire.collector.historique', compact('tournees'));
    }
}
