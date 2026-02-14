<?php

namespace App\Livewire\Owner\Payments;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Payment;

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

        $payments = Payment::where('proprietaire_id', $proprietaire_id)
            ->when($this->filterStatut, function ($q) {
                $q->where('statut', $this->filterStatut);
            })
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        return view('livewire.owner.payments.index', compact('payments'));
    }
}
