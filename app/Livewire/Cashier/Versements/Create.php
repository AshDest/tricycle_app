<?php

namespace App\Livewire\Cashier\Versements;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Versement;
use App\Models\Motard;

#[Layout('components.dashlite-layout')]
class Create extends Component
{
    public $motard_id = '';
    public $montant = '';
    public $method_paiement = '';
    public $reference_paiement = '';
    public $notes = '';

    protected $rules = [
        'motard_id' => 'required|exists:motards,id',
        'montant' => 'required|numeric|min:0',
        'method_paiement' => 'required|string',
        'reference_paiement' => 'nullable|string|max:255',
        'notes' => 'nullable|string',
    ];

    public function save()
    {
        $this->validate();

        $caissier = auth()->user()->caissier;

        Versement::create([
            'motard_id' => $this->motard_id,
            'caissier_id' => $caissier->id,
            'montant' => $this->montant,
            'method_paiement' => $this->method_paiement,
            'reference_paiement' => $this->reference_paiement,
            'notes' => $this->notes,
            'statut' => 'effectué',
        ]);

        session()->flash('success', 'Versement enregistre avec succes.');
        return redirect()->route('cashier.versements.index');
    }

    public function render()
    {
        $motards = Motard::all();
        return view('livewire.cashier.versements.create', compact('motards'));
    }
}
