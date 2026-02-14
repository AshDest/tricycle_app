<?php

namespace App\Livewire\Cashier\Versements;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Versement;
use App\Models\Motard;
use Carbon\Carbon;

#[Layout('components.dashlite-layout')]
class Create extends Component
{
    public $motard_id = '';
    public $montant = '';
    public $mode_paiement = 'cash';
    public $notes = '';

    public $motardSelectionne = null;
    public $montantAttendu = 0;
    public $soldeActuel = 0;

    protected $rules = [
        'motard_id' => 'required|exists:motards,id',
        'montant' => 'required|numeric|min:1',
        'mode_paiement' => 'required|in:cash,mobile_money,depot',
        'notes' => 'nullable|string',
    ];

    protected $messages = [
        'motard_id.required' => 'Veuillez sélectionner un motard.',
        'montant.required' => 'Le montant est obligatoire.',
        'montant.min' => 'Le montant doit être supérieur à 0.',
        'mode_paiement.required' => 'Veuillez choisir un mode de paiement.',
    ];

    public function mount()
    {
        $caissier = auth()->user()->caissier;
        $this->soldeActuel = $caissier->solde_actuel ?? 0;
    }

    public function updatedMotardId($value)
    {
        if ($value) {
            $this->motardSelectionne = Motard::with(['user', 'moto'])->find($value);
            $this->montantAttendu = $this->motardSelectionne?->moto?->montant_journalier_attendu ?? 5000;
        } else {
            $this->motardSelectionne = null;
            $this->montantAttendu = 0;
        }
    }

    public function enregistrer()
    {
        $this->validate();

        $caissier = auth()->user()->caissier;
        $motard = Motard::with('moto')->find($this->motard_id);
        $moto = $motard->moto;

        // Déterminer le statut
        $montantAttendu = $moto?->montant_journalier_attendu ?? 5000;
        $statut = 'non_effectué';
        if ($this->montant >= $montantAttendu) {
            $statut = 'payé';
        } elseif ($this->montant > 0) {
            $statut = 'partiellement_payé';
        }

        Versement::create([
            'motard_id' => $this->motard_id,
            'moto_id' => $moto?->id,
            'caissier_id' => $caissier->id,
            'montant' => $this->montant,
            'montant_attendu' => $montantAttendu,
            'mode_paiement' => $this->mode_paiement,
            'statut' => $statut,
            'date_versement' => Carbon::today(),
            'notes' => $this->notes,
        ]);

        // Mettre à jour le solde du caissier
        $caissier->increment('solde_actuel', $this->montant);

        session()->flash('success', 'Versement enregistré avec succès.');
        return redirect()->route('cashier.versements.index');
    }

    public function render()
    {
        $motards = Motard::with(['user', 'moto'])
            ->where('is_active', true)
            ->whereHas('moto')
            ->get();

        return view('livewire.cashier.versements.create', compact('motards'));
    }
}
