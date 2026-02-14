<?php

namespace App\Livewire\Admin\Payments;

use Livewire\Component;
use App\Models\Payment;

class Show extends Component
{
    public Payment $payment;

    public function mount(Payment $payment)
    {
        $this->payment = $payment->load(['proprietaire.user', 'traitePar']);
    }

    public function marquerPaye()
    {
        $this->payment->update([
            'statut' => 'payé',
            'date_paiement' => now(),
            'traite_par' => auth()->id(),
        ]);
        $this->payment->refresh();
        session()->flash('success', 'Paiement marque comme paye.');
    }

    public function marquerRejete()
    {
        $this->payment->update([
            'statut' => 'rejeté',
            'traite_par' => auth()->id(),
        ]);
        $this->payment->refresh();
        session()->flash('success', 'Paiement rejete.');
    }

    public function render()
    {
        return view('livewire.admin.payments.show');
    }
}
