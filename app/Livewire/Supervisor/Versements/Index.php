<?php

namespace App\Livewire\Supervisor\Versements;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Versement;

#[Layout('components.dashlite-layout')]
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
        $versements = Versement::with(['motard', 'caissier'])
            ->when($this->search, function ($q) {
                $q->whereHas('motard', function ($q2) {
                    $q2->where('numero_identifiant', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filterStatut, function ($q) {
                $q->where('statut', $this->filterStatut);
            })
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        return view('livewire.supervisor.versements.index', compact('versements'));
    }
}
