<?php

namespace App\Livewire\Admin\Collecteurs;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Collecteur;

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

    public function toggleActive(Collecteur $collecteur)
    {
        $collecteur->update(['is_active' => !$collecteur->is_active]);
    }

    public function delete(Collecteur $collecteur)
    {
        $collecteur->forceDelete();
        session()->flash('success', 'Collecteur supprime avec succes.');
    }

    public function render()
    {
        $collecteurs = Collecteur::with(['user'])
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

        $zones = Collecteur::distinct()->pluck('zone_affectation')->filter();

        return view('livewire.admin.collecteurs.index', compact('collecteurs', 'zones'));
    }
}
