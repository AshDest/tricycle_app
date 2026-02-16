<?php

namespace App\Livewire\Supervisor\Payments;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Payment;
use App\Models\Proprietaire;
use App\Services\PaymentService;

/**
 * Formulaire de création de demande de paiement par OKAMI
 */
#[Layout('components.dashlite-layout')]
class Create extends Component
{
    public $proprietaire_id = '';
    public $montant = '';
    public $mode_paiement = 'mpesa';
    public $numero_compte = '';
    public $notes = '';

    // Données calculées
    public $soldeDisponible = 0;
    public $proprietaireSelectionne = null;

    protected $rules = [
        'proprietaire_id' => 'required|exists:proprietaires,id',
        'montant' => 'required|numeric|min:1',
        'mode_paiement' => 'required|in:mpesa,airtel_money,orange_money,virement_bancaire',
        'numero_compte' => 'nullable|string|max:50',
        'notes' => 'nullable|string|max:500',
    ];

    protected $messages = [
        'proprietaire_id.required' => 'Veuillez sélectionner un propriétaire.',
        'montant.required' => 'Le montant est obligatoire.',
        'montant.min' => 'Le montant doit être supérieur à 0.',
        'mode_paiement.required' => 'Le mode de paiement est obligatoire.',
    ];

    /**
     * Quand le propriétaire change, recalculer le solde disponible
     */
    public function updatedProprietaireId($value)
    {
        if ($value) {
            $proprietaire = Proprietaire::with(['user', 'motos'])->find($value);
            $this->proprietaireSelectionne = $proprietaire;

            $paymentService = new PaymentService();
            $this->soldeDisponible = $paymentService->getSoldeDisponibleProprietaire($proprietaire);

            // Pré-remplir le numéro de compte selon le mode
            $this->updateNumeroCompte();
        } else {
            $this->soldeDisponible = 0;
            $this->proprietaireSelectionne = null;
        }
    }

    /**
     * Mettre à jour le numéro de compte selon le mode de paiement
     */
    public function updatedModePaiement($value)
    {
        $this->updateNumeroCompte();
    }

    private function updateNumeroCompte()
    {
        if ($this->proprietaireSelectionne) {
            $this->numero_compte = $this->proprietaireSelectionne->getNumeroCompte($this->mode_paiement) ?? '';
        }
    }

    /**
     * Soumettre la demande de paiement
     */
    public function submit()
    {
        $this->validate();

        // Vérifier que le montant ne dépasse pas le solde disponible
        if ($this->montant > $this->soldeDisponible) {
            $this->addError('montant', "Le montant demandé dépasse le solde disponible ({$this->soldeDisponible} FC).");
            return;
        }

        try {
            $paymentService = new PaymentService();
            $paymentService->creerDemandePaiementOKAMI([
                'proprietaire_id' => $this->proprietaire_id,
                'montant' => $this->montant,
                'mode_paiement' => $this->mode_paiement,
                'numero_compte' => $this->numero_compte,
                'notes' => $this->notes,
            ], auth()->id());

            session()->flash('success', 'Demande de paiement soumise avec succès.');
            return redirect()->route('supervisor.payments.index');
        } catch (\Exception $e) {
            $this->addError('montant', $e->getMessage());
        }
    }

    public function render()
    {
        $paymentService = new PaymentService();
        $proprietaires = $paymentService->getProprietairesAvecSolde();

        return view('livewire.supervisor.payments.create', [
            'proprietaires' => $proprietaires,
            'modesPaiement' => Payment::getModesPaiement(),
        ]);
    }
}
