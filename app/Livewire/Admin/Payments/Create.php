<?php

namespace App\Livewire\Admin\Payments;

use Livewire\Component;
use App\Models\Payment;
use App\Models\Proprietaire;
use App\Services\PaymentService;

class Create extends Component
{
    public $proprietaire_id = '';
    public $total_du = 0;
    public $total_paye = 0;
    public $mode_paiement = '';
    public $numero_compte = '';
    public $periode_debut = '';
    public $periode_fin = '';
    public $notes = '';

    protected $rules = [
        'proprietaire_id' => 'required|exists:proprietaires,id',
        'total_paye' => 'required|numeric|min:0',
        'mode_paiement' => 'required|string|in:mpesa,airtel_money,orange_money,virement_bancaire',
        'periode_debut' => 'nullable|date',
        'periode_fin' => 'nullable|date|after_or_equal:periode_debut',
        'notes' => 'nullable|string|max:1000',
    ];

    public function updatedProprietaireId($value)
    {
        if ($value) {
            $proprietaire = Proprietaire::find($value);
            if ($proprietaire) {
                $service = app(PaymentService::class);
                $this->total_du = $service->calculerTotalDuProprietaire(
                    $proprietaire,
                    $this->periode_debut ? \Carbon\Carbon::parse($this->periode_debut) : null,
                    $this->periode_fin ? \Carbon\Carbon::parse($this->periode_fin) : null
                );
                $this->numero_compte = $proprietaire->getNumeroCompte($this->mode_paiement) ?? '';
            }
        }
    }

    public function updatedModePaiement($value)
    {
        if ($this->proprietaire_id) {
            $proprietaire = Proprietaire::find($this->proprietaire_id);
            if ($proprietaire) {
                $this->numero_compte = $proprietaire->getNumeroCompte($value) ?? '';
            }
        }
    }

    public function save()
    {
        $this->validate();

        Payment::create([
            'proprietaire_id' => $this->proprietaire_id,
            'total_du' => $this->total_du,
            'total_paye' => $this->total_paye,
            'mode_paiement' => $this->mode_paiement,
            'numero_compte' => $this->numero_compte,
            'statut' => 'en_attente',
            'date_demande' => now(),
            'periode_debut' => $this->periode_debut ?: null,
            'periode_fin' => $this->periode_fin ?: null,
            'notes' => $this->notes ?: null,
            'traite_par' => auth()->id(),
        ]);

        session()->flash('success', 'Paiement cree avec succes.');
        return redirect()->route('admin.payments.index');
    }

    public function render()
    {
        $proprietaires = Proprietaire::with('user')->get();
        $modesPaiement = Payment::getModesPaiement();

        return view('livewire.admin.payments.create', compact('proprietaires', 'modesPaiement'));
    }
}
