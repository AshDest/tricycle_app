<?php

namespace App\Livewire\Supervisor\Payments;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Payment;
use App\Models\Proprietaire;
use App\Models\Collecteur;
use App\Services\PaymentService;

/**
 * Formulaire de création de demande de paiement par OKAMI
 * Peut être depuis la caisse Propriétaire ou la caisse OKAMI
 */
#[Layout('components.dashlite-layout')]
class Create extends Component
{
    // Source de la caisse
    public $source_caisse = 'proprietaire';

    // Pour caisse Propriétaire
    public $proprietaire_id = '';
    public $proprietaireSelectionne = null;
    public $soldeDisponible = 0;

    // Pour caisse OKAMI
    public $beneficiaire_nom = '';
    public $beneficiaire_telephone = '';
    public $beneficiaire_motif = '';
    public $soldeOkamiDisponible = 0;

    // Commun
    public $montant = '';
    public $mode_paiement = 'cash';
    public $numero_compte = '';
    public $notes = '';

    protected function rules()
    {
        $rules = [
            'source_caisse' => 'required|in:proprietaire,okami',
            'montant' => 'required|numeric|min:1',
            'mode_paiement' => 'required|in:cash,mpesa,airtel_money,orange_money,virement_bancaire',
            'numero_compte' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:500',
        ];

        if ($this->source_caisse === 'proprietaire') {
            $rules['proprietaire_id'] = 'required|exists:proprietaires,id';
        } else {
            $rules['beneficiaire_nom'] = 'required|string|max:100';
            $rules['beneficiaire_telephone'] = 'nullable|string|max:20';
            $rules['beneficiaire_motif'] = 'required|string|max:500';
        }

        return $rules;
    }

    protected $messages = [
        'proprietaire_id.required' => 'Veuillez sélectionner un propriétaire.',
        'beneficiaire_nom.required' => 'Le nom du bénéficiaire est obligatoire.',
        'beneficiaire_motif.required' => 'Le motif du paiement est obligatoire.',
        'montant.required' => 'Le montant est obligatoire.',
        'montant.min' => 'Le montant doit être supérieur à 0.',
        'mode_paiement.required' => 'Le mode de paiement est obligatoire.',
    ];

    public function mount()
    {
        $this->calculerSoldeOkami();
    }

    /**
     * Calculer le solde disponible dans la caisse OKAMI
     */
    private function calculerSoldeOkami()
    {
        // Somme des soldes OKAMI de tous les collecteurs
        $this->soldeOkamiDisponible = Collecteur::sum('solde_part_okami');
    }

    /**
     * Quand la source de caisse change
     */
    public function updatedSourceCaisse($value)
    {
        // Reset les champs
        $this->proprietaire_id = '';
        $this->proprietaireSelectionne = null;
        $this->soldeDisponible = 0;
        $this->beneficiaire_nom = '';
        $this->beneficiaire_telephone = '';
        $this->beneficiaire_motif = '';
        $this->montant = '';
        $this->numero_compte = '';

        if ($value === 'okami') {
            $this->calculerSoldeOkami();
        }
    }

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
        if ($this->source_caisse === 'proprietaire' && $this->proprietaireSelectionne) {
            $this->numero_compte = $this->proprietaireSelectionne->getNumeroCompte($this->mode_paiement) ?? '';
        } elseif ($this->source_caisse === 'okami' && $this->beneficiaire_telephone) {
            $this->numero_compte = $this->beneficiaire_telephone;
        }
    }

    /**
     * Soumettre la demande de paiement
     */
    public function submit()
    {
        $this->validate();

        // Vérifier le solde disponible selon la source
        $soldeMax = $this->source_caisse === 'proprietaire' ? $this->soldeDisponible : $this->soldeOkamiDisponible;

        if ($this->montant > $soldeMax) {
            $this->addError('montant', "Le montant demandé dépasse le solde disponible (" . number_format($soldeMax) . " FC).");
            return;
        }

        try {
            $paymentService = new PaymentService();

            if ($this->source_caisse === 'proprietaire') {
                // Paiement depuis caisse Propriétaire
                $paymentService->creerDemandePaiementOKAMI([
                    'proprietaire_id' => $this->proprietaire_id,
                    'source_caisse' => 'proprietaire',
                    'montant' => $this->montant,
                    'mode_paiement' => $this->mode_paiement,
                    'numero_compte' => $this->numero_compte,
                    'notes' => $this->notes,
                ], auth()->id());
            } else {
                // Paiement depuis caisse OKAMI
                $paymentService->creerDemandePaiementDepuisOKAMI([
                    'source_caisse' => 'okami',
                    'beneficiaire_nom' => $this->beneficiaire_nom,
                    'beneficiaire_telephone' => $this->beneficiaire_telephone,
                    'beneficiaire_motif' => $this->beneficiaire_motif,
                    'montant' => $this->montant,
                    'mode_paiement' => $this->mode_paiement,
                    'numero_compte' => $this->numero_compte ?: $this->beneficiaire_telephone,
                    'notes' => $this->notes,
                ], auth()->id());
            }

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
            'sourcesCaisse' => Payment::getSourcesCaisse(),
        ]);
    }
}
