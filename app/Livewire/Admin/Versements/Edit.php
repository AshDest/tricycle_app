<?php

namespace App\Livewire\Admin\Versements;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Versement;

#[Layout('components.dashlite-layout')]
class Edit extends Component
{
    public Versement $versement;

    public $montant;
    public $montant_attendu;
    public $mode_paiement;
    public $statut;
    public $date_versement;
    public $notes;

    protected function rules()
    {
        return [
            'montant' => 'required|numeric|min:0',
            'montant_attendu' => 'required|numeric|min:0',
            'mode_paiement' => 'required|in:cash,mobile_money,depot',
            'statut' => 'required|in:payé,partiellement_payé,en_retard,non_effectué',
            'date_versement' => 'required|date',
            'notes' => 'nullable|string|max:500',
        ];
    }

    public function mount(Versement $versement)
    {
        $this->versement = $versement->load(['motard.user', 'moto', 'caissier.user']);
        $this->montant = $versement->montant;
        $this->montant_attendu = $versement->montant_attendu;
        $this->mode_paiement = $versement->mode_paiement;
        $this->statut = $versement->statut;
        $this->date_versement = $versement->date_versement?->format('Y-m-d');
        $this->notes = $versement->notes;
    }

    public function save()
    {
        $this->validate();

        try {
            $this->versement->update([
                'montant' => $this->montant,
                'montant_attendu' => $this->montant_attendu,
                'mode_paiement' => $this->mode_paiement,
                'statut' => $this->statut,
                'date_versement' => $this->date_versement,
                'notes' => $this->notes,
                'arrieres' => max(0, $this->montant_attendu - $this->montant),
            ]);

            session()->flash('success', 'Versement modifié avec succès.');
            return redirect()->route('admin.versements.index');
        } catch (\Exception $e) {
            session()->flash('error', 'Erreur lors de la modification: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.versements.edit');
    }
}

