<?php

namespace App\Livewire\Collector\Collectes;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Collecte;

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
        $collecteur_id = auth()->user()->collecteur?->id;

        $collectes = Collecte::with(['caissier', 'tournee'])
            ->whereHas('tournee', function ($q) use ($collecteur_id) {
                $q->where('collecteur_id', $collecteur_id);
            })
            ->when($this->filterStatut, function ($q) {
                $q->where('statut', $this->filterStatut);
            })
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        return view('livewire.collector.collectes.index', compact('collectes'));
    }
}
