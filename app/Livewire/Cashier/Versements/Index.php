<?php

namespace App\Livewire\Cashier\Versements;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Versement;

class Index extends Component
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
        $caissier_id = auth()->user()->caissier?->id;

        $versements = Versement::with(['motard'])
            ->where('caissier_id', $caissier_id)
            ->when($this->filterStatut, function ($q) {
                $q->where('statut', $this->filterStatut);
            })
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        return view('livewire.cashier.versements.index', compact('versements'));
    }
}
