<?php

namespace App\Livewire\Collector\Payments;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Payment;
use App\Services\PaymentService;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * Traitement des demandes de paiement par le Collecteur
 * Le collecteur voit les demandes soumises par OKAMI et les traite
 */
#[Layout('components.dashlite-layout')]
class Index extends Component
{
    use WithPagination;

    public $filterStatut = '';
    public $search = '';
    public $perPage = 15;

    // Pour le formulaire de traitement
    public $paymentEnCours = null;
    public $montant_paye = '';
    public $numero_envoi = '';
    public $reference_paiement = '';
    public $notes = '';
    public $showModal = false;

    protected $queryString = ['filterStatut', 'search'];

    public function updatingSearch() { $this->resetPage(); }
    public function updatingFilterStatut() { $this->resetPage(); }

    /**
     * Ouvrir le modal pour traiter un paiement
     */
    public function ouvrirTraitement($paymentId)
    {
        $this->paymentEnCours = Payment::with('proprietaire.user')->findOrFail($paymentId);
        $this->montant_paye = $this->paymentEnCours->total_du;
        $this->numero_envoi = '';
        $this->reference_paiement = '';
        $this->notes = '';
        $this->showModal = true;
    }

    public function fermerModal()
    {
        $this->showModal = false;
        $this->paymentEnCours = null;
        $this->reset(['montant_paye', 'numero_envoi', 'reference_paiement', 'notes']);
    }

    /**
     * Traiter le paiement
     */
    public function traiterPaiement()
    {
        // Validation de base
        $rules = [
            'montant_paye' => 'required|numeric|min:1',
        ];
        $messages = [
            'montant_paye.required' => 'Le montant payé est obligatoire.',
        ];

        // Le numéro d'envoi est obligatoire seulement pour les paiements non-cash
        if ($this->paymentEnCours && $this->paymentEnCours->mode_paiement !== 'cash') {
            $rules['numero_envoi'] = 'required|string|max:100';
            $messages['numero_envoi.required'] = 'Le numéro d\'envoi est obligatoire pour ce mode de paiement.';
        }

        $this->validate($rules, $messages);

        if (!$this->paymentEnCours) {
            session()->flash('error', 'Paiement introuvable.');
            return;
        }

        // Vérifier que le montant ne dépasse pas le solde disponible du propriétaire
        $paymentService = new PaymentService();
        $soldeDisponible = $paymentService->getSoldeDisponibleProprietaire($this->paymentEnCours->proprietaire);

        if ($this->montant_paye > $soldeDisponible) {
            $this->addError('montant_paye', "Le montant dépasse le solde disponible du propriétaire ({$soldeDisponible} FC).");
            return;
        }

        // Générer un numéro de référence pour les paiements cash
        $numeroEnvoi = $this->numero_envoi;
        if ($this->paymentEnCours->mode_paiement === 'cash' && empty($numeroEnvoi)) {
            $numeroEnvoi = 'CASH-' . date('YmdHis') . '-' . $this->paymentEnCours->id;
        }

        $paymentService->traiterPaiement($this->paymentEnCours, [
            'montant_paye' => $this->montant_paye,
            'numero_envoi' => $numeroEnvoi,
            'reference_paiement' => $this->reference_paiement,
            'notes' => $this->notes,
        ], auth()->id());

        $paymentId = $this->paymentEnCours->id;
        $isCash = $this->paymentEnCours->mode_paiement === 'cash';

        $this->fermerModal();

        // Si c'est un paiement cash, télécharger le reçu
        if ($isCash) {
            return $this->telechargerRecuPaiement($paymentId);
        }

        session()->flash('success', 'Paiement effectué avec succès. En attente de validation OKAMI.');
    }

    /**
     * Télécharger le reçu d'un paiement
     */
    public function telechargerRecuPaiement($paymentId)
    {
        $payment = Payment::with(['proprietaire.user', 'traitePar'])->findOrFail($paymentId);

        $pdf = Pdf::loadView('pdf.recu-paiement', compact('payment'));

        // Dimensions d'un petit reçu (80mm x 200mm)
        $pdf->setPaper([0, 0, 226.77, 566.93], 'portrait');

        $filename = 'recu_paiement_' . $payment->id . '_' . now()->format('YmdHis') . '.pdf';

        return response()->streamDownload(function() use ($pdf) {
            echo $pdf->output();
        }, $filename);
    }

    /**
     * Rejeter une demande
     */
    public function rejeterDemande($paymentId, $motif = 'Rejeté par le collecteur')
    {
        $payment = Payment::findOrFail($paymentId);

        $paymentService = new PaymentService();
        $paymentService->rejeterPaiement($payment, auth()->id(), $motif);

        session()->flash('success', 'Demande rejetée.');
    }

    public function render()
    {
        // Demandes à traiter (statut = en_attente)
        $payments = Payment::with(['proprietaire.user', 'demandePar'])
            ->where('statut', 'en_attente')
            ->when($this->search, function($q) {
                $q->whereHas('proprietaire.user', fn($q2) => $q2->where('name', 'like', '%'.$this->search.'%'));
            })
            ->orderBy('created_at', 'asc')
            ->paginate($this->perPage);

        $demandesEnAttente = Payment::where('statut', 'en_attente')->count();

        return view('livewire.collector.payments.index', [
            'payments' => $payments,
            'demandesEnAttente' => $demandesEnAttente,
        ]);
    }
}


