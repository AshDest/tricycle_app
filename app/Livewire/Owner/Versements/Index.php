<?php

namespace App\Livewire\Owner\Versements;

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
        $proprietaire_id = auth()->user()->proprietaire?->id;

        $versements = Versement::with(['motard', 'caissier'])
            ->whereHas('motard.proprietaire', function ($q) use ($proprietaire_id) {
                $q->where('id', $proprietaire_id);
            })
            ->when($this->filterStatut, function ($q) {
                $q->where('statut', $this->filterStatut);
            })
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        return view('livewire.owner.versements.index', compact('versements'));
    }
}
