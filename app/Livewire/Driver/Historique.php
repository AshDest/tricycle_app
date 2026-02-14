<?php

namespace App\Livewire\Driver;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Versement;

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
        $motard_id = auth()->user()->motard?->id;

        $versements = Versement::where('motard_id', $motard_id)
            ->when($this->filterStatut, function ($q) {
                $q->where('statut', $this->filterStatut);
            })
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        return view('livewire.driver.historique', compact('versements'));
    }
}
