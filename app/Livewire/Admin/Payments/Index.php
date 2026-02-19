<?php

namespace App\Livewire\Admin\Payments;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Payment;
use App\Models\Proprietaire;
use Carbon\Carbon;

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

    public function approuver(int $id)
    {
        $payment = Payment::findOrFail($id);

        $payment->update([
            'statut' => 'paye',
            'date_paiement' => Carbon::now(),
            'valide_par' => auth()->id(),
            'valide_at' => Carbon::now(),
        ]);

        session()->flash('success', 'Paiement approuvé avec succès.');
    }

    public function rejeter(int $id)
    {
        $payment = Payment::findOrFail($id);

        $payment->update([
            'statut' => 'rejete',
            'valide_par' => auth()->id(),
            'valide_at' => Carbon::now(),
        ]);

        session()->flash('success', 'Paiement rejeté.');
    }

    public function delete(Payment $payment)
    {
        $payment->delete();
        session()->flash('success', 'Paiement supprimé avec succès.');
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

        $totalEnAttente = Payment::whereIn('statut', ['en_attente', 'demande'])->count();
        $totalPaye = Payment::where('statut', 'paye')->sum('total_paye');

        return view('livewire.admin.payments.index', compact('payments', 'totalEnAttente', 'totalPaye'));
    }
}
