<?php

namespace App\Livewire\Admin\Maintenances;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Maintenance;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $filterType = '';
    public $filterStatut = '';
    public $perPage = 15;

    protected $queryString = ['search', 'filterType', 'filterStatut'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterType()
    {
        $this->resetPage();
    }

    public function updatingFilterStatut()
    {
        $this->resetPage();
    }

    public function delete(Maintenance $maintenance)
    {
        $maintenance->forceDelete();
        session()->flash('success', 'Maintenance supprimee avec succes.');
    }

    public function render()
    {
        $maintenances = Maintenance::with(['moto', 'motard'])
            ->when($this->search, function ($q) {
                $q->where('description', 'like', '%' . $this->search . '%')
                  ->orWhere('technicien_garage_nom', 'like', '%' . $this->search . '%');
            })
            ->when($this->filterType, function ($q) {
                $q->where('type', $this->filterType);
            })
            ->when($this->filterStatut, function ($q) {
                $q->where('statut', $this->filterStatut);
            })
            ->orderBy('date_intervention', 'desc')
            ->paginate($this->perPage);

        return view('livewire.admin.maintenances.index', compact('maintenances'));
    }
}
