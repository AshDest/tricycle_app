<?php

namespace App\Livewire\Admin\Motards;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Motard;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $filterZone = '';
    public $filterStatut = '';
    public $perPage = 15;

    protected $queryString = ['search', 'filterZone', 'filterStatut'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterZone()
    {
        $this->resetPage();
    }

    public function updatingFilterStatut()
    {
        $this->resetPage();
    }

    public function toggleActive(Motard $motard)
    {
        $motard->update(['is_active' => !$motard->is_active]);
    }

    public function delete(Motard $motard)
    {
        $motard->delete();
        session()->flash('success', 'Motard supprime avec succes.');
    }

    public function render()
    {
        $motards = Motard::with(['user', 'motoActuelle'])
            ->when($this->search, function ($q) {
                $q->whereHas('user', function ($q2) {
                    $q2->where('name', 'like', '%' . $this->search . '%')
                       ->orWhere('email', 'like', '%' . $this->search . '%');
                })->orWhere('numero_identifiant', 'like', '%' . $this->search . '%');
            })
            ->when($this->filterZone, function ($q) {
                $q->where('zone_affectation', $this->filterZone);
            })
            ->when($this->filterStatut !== '', function ($q) {
                if ($this->filterStatut === 'actif') {
                    $q->where('is_active', true);
                } elseif ($this->filterStatut === 'inactif') {
                    $q->where('is_active', false);
                }
            })
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        $zones = Motard::distinct()->pluck('zone_affectation')->filter();

        return view('livewire.admin.motards.index', compact('motards', 'zones'));
    }
}
