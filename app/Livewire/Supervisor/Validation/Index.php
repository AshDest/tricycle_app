<?php

namespace App\Livewire\Supervisor\Validation;

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

    public function validateTournee(Tournee $tournee)
    {
        if (auth()->id()) {
            $tournee->update([
                'valide_par_nth_id' => auth()->id(),
                'valide_par_nth_at' => now(),
                'statut' => 'validee',
            ]);
            session()->flash('success', 'Tournee validee avec succes.');
        }
    }

    public function render()
    {
        $tournees = Tournee::with(['collecteur'])
            ->where('statut', '!=', 'validee')
            ->when($this->search, function ($q) {
                $q->where('zone', 'like', '%' . $this->search . '%');
            })
            ->when($this->filterZone, function ($q) {
                $q->where('zone', $this->filterZone);
            })
            ->orderBy('date', 'desc')
            ->paginate($this->perPage);

        $zones = Tournee::distinct()->pluck('zone')->filter();

        return view('livewire.supervisor.validation.index', compact('tournees', 'zones'));
    }
}
