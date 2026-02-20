<?php

namespace App\Livewire\Admin\Payments;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Payment;

#[Layout('components.dashlite-layout')]
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
            'statut' => 'paye',
            'date_paiement' => now(),
            'traite_par' => auth()->id(),
        ]);
        $this->payment->refresh();
        session()->flash('success', 'Paiement marqué comme payé.');
    }

    public function marquerRejete()
    {
        $this->payment->update([
            'statut' => 'rejete',
            'traite_par' => auth()->id(),
        ]);
        $this->payment->refresh();
        session()->flash('success', 'Paiement rejeté.');
    }

    public function render()
    {
        return view('livewire.admin.payments.show');
    }
}
