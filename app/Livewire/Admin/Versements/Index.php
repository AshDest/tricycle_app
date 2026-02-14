<?php

namespace App\Livewire\Admin\Versements;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Versement;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $filterStatut = '';
    public $filterMode = '';
    public $dateFrom = '';
    public $dateTo = '';
    public $perPage = 15;

    protected $queryString = ['search', 'filterStatut', 'filterMode', 'dateFrom', 'dateTo'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterStatut()
    {
        $this->resetPage();
    }

    public function updatingFilterMode()
    {
        $this->resetPage();
    }

    public function updatingDateFrom()
    {
        $this->resetPage();
    }

    public function updatingDateTo()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->reset(['search', 'filterStatut', 'filterMode', 'dateFrom', 'dateTo']);
        $this->resetPage();
    }

    public function render()
    {
        $versements = Versement::with(['motard.user', 'moto', 'caissier.user'])
            ->when($this->search, function ($q) {
                $q->whereHas('motard.user', function ($q2) {
                    $q2->where('name', 'like', '%' . $this->search . '%');
                })
                ->orWhereHas('moto', function ($q2) {
                    $q2->where('plaque_immatriculation', 'like', '%' . $this->search . '%');
                })
                ->orWhereHas('caissier.user', function ($q2) {
                    $q2->where('name', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filterStatut, function ($q) {
                $q->where('statut', $this->filterStatut);
            })
            ->when($this->filterMode, function ($q) {
                $q->where('mode_paiement', $this->filterMode);
            })
            ->when($this->dateFrom, function ($q) {
                $q->whereDate('date_versement', '>=', $this->dateFrom);
            })
            ->when($this->dateTo, function ($q) {
                $q->whereDate('date_versement', '<=', $this->dateTo);
            })
            ->orderBy('date_versement', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        return view('livewire.admin.versements.index', compact('versements'));
    }
}
