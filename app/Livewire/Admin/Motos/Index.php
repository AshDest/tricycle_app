<?php

namespace App\Livewire\Admin\Motos;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Moto;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $filterStatut = '';
    public $filterAssignation = '';
    public $perPage = 15;

    protected $queryString = ['search', 'filterStatut', 'filterAssignation'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function changeStatut(Moto $moto, string $statut)
    {
        $moto->update(['statut' => $statut]);
    }

    public function delete(Moto $moto)
    {
        $moto->delete();
        session()->flash('success', 'Moto supprimee avec succes.');
    }

    public function render()
    {
        $motos = Moto::with(['proprietaire.user', 'motard.user'])
            ->when($this->search, function ($q) {
                $q->where('plaque_immatriculation', 'like', '%' . $this->search . '%')
                  ->orWhere('numero_matricule', 'like', '%' . $this->search . '%')
                  ->orWhere('numero_chassis', 'like', '%' . $this->search . '%');
            })
            ->when($this->filterStatut, function ($q) {
                $q->where('statut', $this->filterStatut);
            })
            ->when($this->filterAssignation, function ($q) {
                if ($this->filterAssignation === 'assignee') {
                    $q->whereNotNull('motard_id');
                } elseif ($this->filterAssignation === 'non_assignee') {
                    $q->whereNull('motard_id');
                }
            })
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        return view('livewire.admin.motos.index', compact('motos'));
    }
}
