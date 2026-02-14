<?php

namespace App\Livewire\Admin\Accidents;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Accident;

#[Layout('components.dashlite-layout')]
class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $filterGravite = '';
    public $filterStatut = '';
    public $perPage = 15;

    protected $queryString = ['search', 'filterGravite', 'filterStatut'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterGravite()
    {
        $this->resetPage();
    }

    public function updatingFilterStatut()
    {
        $this->resetPage();
    }

    public function delete(Accident $accident)
    {
        $accident->forceDelete();
        session()->flash('success', 'Accident supprime avec succes.');
    }

    public function render()
    {
        $accidents = Accident::with(['moto', 'motard'])
            ->when($this->search, function ($q) {
                $q->where('lieu', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            })
            ->when($this->filterGravite, function ($q) {
                $q->where('gravite', $this->filterGravite);
            })
            ->when($this->filterStatut, function ($q) {
                $q->where('statut', $this->filterStatut);
            })
            ->orderBy('date_heure', 'desc')
            ->paginate($this->perPage);

        return view('livewire.admin.accidents.index', compact('accidents'));
    }
}
