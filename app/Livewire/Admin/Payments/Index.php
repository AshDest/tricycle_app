<?php

namespace App\Livewire\Admin\Payments;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Payment;
use App\Models\Proprietaire;

#[Layout('components.dashlite-layout')]
class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $filterStatut = '';
    public $filterMode = '';
    public $perPage = 15;

    protected $queryString = ['search', 'filterStatut', 'filterMode'];

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

    public function delete(Payment $payment)
    {
        $payment->delete();
        session()->flash('success', 'Paiement supprime avec succes.');
    }

    public function render()
    {
        $payments = Payment::with('proprietaire.user')
            ->when($this->search, function ($q) {
                $q->whereHas('proprietaire.user', function ($q2) {
                    $q2->where('name', 'like', '%' . $this->search . '%');
                })->orWhere('reference_paiement', 'like', '%' . $this->search . '%');
            })
            ->when($this->filterStatut, function ($q) {
                $q->where('statut', $this->filterStatut);
            })
            ->when($this->filterMode, function ($q) {
                $q->where('mode_paiement', $this->filterMode);
            })
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        $totalEnAttente = Payment::where('statut', 'en_attente')->count();
        $totalPaye = Payment::where('statut', 'payé')->sum('total_paye');

        return view('livewire.admin.payments.index', compact('payments', 'totalEnAttente', 'totalPaye'));
    }
}
