<?php

namespace App\Livewire\Supervisor\Motards;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Motard;

#[Layout('components.dashlite-layout')]
class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $filterZone = '';
    public $perPage = 15;

    protected $queryString = ['search', 'filterZone'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterZone()
    {
        $this->resetPage();
    }

    public function render()
    {
        $motards = Motard::with(['user', 'motoActuelle'])
            ->when($this->search, function ($q) {
                $q->whereHas('user', function ($q2) {
                    $q2->where('name', 'like', '%' . $this->search . '%');
                })->orWhere('numero_identifiant', 'like', '%' . $this->search . '%');
            })
            ->when($this->filterZone, function ($q) {
                $q->where('zone_affectation', $this->filterZone);
            })
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        $zones = Motard::distinct()->pluck('zone_affectation')->filter();

        return view('livewire.supervisor.motards.index', compact('motards', 'zones'));
    }
}
