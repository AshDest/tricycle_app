<?php

namespace App\Livewire\Admin\Caissiers;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Caissier;

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

    public function toggleActive(Caissier $caissier)
    {
        $caissier->update(['is_active' => !$caissier->is_active]);
    }

    public function delete(Caissier $caissier)
    {
        $caissier->delete();
        session()->flash('success', 'Caissier supprime avec succes.');
    }

    public function render()
    {
        $caissiers = Caissier::with(['user'])
            ->when($this->search, function ($q) {
                $q->whereHas('user', function ($q2) {
                    $q2->where('name', 'like', '%' . $this->search . '%')
                       ->orWhere('email', 'like', '%' . $this->search . '%');
                })->orWhere('numero_identifiant', 'like', '%' . $this->search . '%')
                  ->orWhere('nom_point_collecte', 'like', '%' . $this->search . '%');
            })
            ->when($this->filterZone, function ($q) {
                $q->where('zone', $this->filterZone);
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

        $zones = Caissier::distinct()->pluck('zone')->filter();

        return view('livewire.admin.caissiers.index', compact('caissiers', 'zones'));
    }
}
