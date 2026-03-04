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

        $collecteur = auth()->user()->collecteur;
        $isCash = $this->paymentEnCours->mode_paiement === 'cash';
        $isFromOkami = $this->paymentEnCours->source_caisse === 'okami';

        // Pour les paiements cash, vérifier que le collecteur a assez dans sa caisse
        if ($isCash && $collecteur) {
            // Vérifier selon la source de la caisse
            if ($isFromOkami) {
                $soldeDisponible = $collecteur->solde_part_okami ?? 0;
                if ($this->montant_paye > $soldeDisponible) {
                    $this->addError('montant_paye', "Le montant dépasse votre solde de caisse OKAMI ({$soldeDisponible} FC).");
                    return;
                }
            } else {
                $soldeDisponible = $collecteur->solde_part_proprietaire ?? 0;
                if ($this->montant_paye > $soldeDisponible) {
                    $this->addError('montant_paye', "Le montant dépasse votre solde de caisse Propriétaires ({$soldeDisponible} FC).");
                    return;
                }
            }
        }

        // Vérifier le solde disponible selon la source
        if (!$isFromOkami && $this->paymentEnCours->proprietaire) {
            $paymentService = new PaymentService();
            $soldeProprietaire = $paymentService->getSoldeDisponibleProprietaire($this->paymentEnCours->proprietaire);

            if ($this->montant_paye > $soldeProprietaire) {
                $this->addError('montant_paye', "Le montant dépasse le solde disponible du propriétaire ({$soldeProprietaire} FC).");
                return;
            }
        }

        // Générer un numéro de référence pour les paiements cash
        $numeroEnvoi = $this->numero_envoi;
        if ($isCash && empty($numeroEnvoi)) {
            $numeroEnvoi = 'CASH-' . date('YmdHis') . '-' . $this->paymentEnCours->id;
        }

        $paymentService = new PaymentService();
        $paymentService->traiterPaiement($this->paymentEnCours, [
            'montant_paye' => $this->montant_paye,
            'numero_envoi' => $numeroEnvoi,
            'reference_paiement' => $this->reference_paiement,
            'notes' => $this->notes,
        ], auth()->id());

        // Déduire le montant de la caisse du collecteur pour les paiements cash
        if ($isCash && $collecteur) {
            if ($isFromOkami) {
                // Déduire de la part OKAMI
                $collecteur->retirerMontantOkami($this->montant_paye);
            } else {
                // Déduire de la part Propriétaires
                $collecteur->retirerMontantProprietaire($this->montant_paye);
            }
        }

        $paymentId = $this->paymentEnCours->id;

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
     * Exporter la liste des demandes de paiement en PDF
     */
    public function exporterPdf()
    {
        $paymentService = new PaymentService();
        $collecteur = auth()->user()->collecteur;

        $payments = Payment::with(['proprietaire.user', 'demandePar'])
            ->where('statut', 'en_attente')
            ->orderBy('created_at', 'asc')
            ->get();

        // Calculer le solde pour chaque paiement
        foreach ($payments as $payment) {
            if ($payment->source_caisse === 'okami' || !$payment->proprietaire) {
                $payment->solde_disponible = $collecteur?->solde_part_okami ?? 0;
            } else {
                $payment->solde_disponible = $paymentService->getSoldeDisponibleProprietaire($payment->proprietaire);
            }
        }

        $stats = [
            'total_demandes' => $payments->count(),
            'total_montant' => $payments->sum('total_du'),
            'demandes_okami' => $payments->where('source_caisse', 'okami')->count(),
            'demandes_proprietaire' => $payments->where('source_caisse', '!=', 'okami')->count(),
        ];

        $pdf = Pdf::loadView('pdf.liste-demandes-paiement', [
            'payments' => $payments,
            'stats' => $stats,
            'collecteur' => $collecteur,
        ]);
        $pdf->setPaper('a4', 'landscape');

        $filename = 'demandes_paiement_' . now()->format('Y-m-d_His') . '.pdf';

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
        $collecteur = auth()->user()->collecteur;
        $soldeCaisse = $collecteur?->solde_caisse ?? 0;
        $soldePartOkami = $collecteur?->solde_part_okami ?? 0;
        $soldePartProprietaire = $collecteur?->solde_part_proprietaire ?? 0;

        $paymentService = new PaymentService();

        // Demandes à traiter (statut = en_attente)
        $payments = Payment::with(['proprietaire.user', 'demandePar'])
            ->where('statut', 'en_attente')
            ->when($this->search, function($q) {
                $q->where(function($q2) {
                    // Recherche dans le nom du propriétaire ou le nom du bénéficiaire OKAMI
                    $q2->whereHas('proprietaire.user', fn($q3) => $q3->where('name', 'like', '%'.$this->search.'%'))
                       ->orWhere('beneficiaire_nom', 'like', '%'.$this->search.'%');
                });
            })
            ->orderBy('created_at', 'asc')
            ->paginate($this->perPage);

        // Calculer le solde disponible pour chaque propriétaire
        foreach ($payments as $payment) {
            if ($payment->source_caisse === 'okami' || !$payment->proprietaire) {
                // Pour les paiements OKAMI, on utilise le solde OKAMI du collecteur
                $payment->solde_disponible = $soldePartOkami;
                $payment->peut_etre_paye = $payment->total_du <= $soldePartOkami;
            } else {
                // Pour les paiements propriétaire, calculer le solde réel du propriétaire
                $soldeProprietaire = $paymentService->getSoldeDisponibleProprietaire($payment->proprietaire);
                $payment->solde_disponible = $soldeProprietaire;
                $payment->peut_etre_paye = $payment->total_du <= $soldeProprietaire && $payment->total_du <= $soldePartProprietaire;
            }
        }

        $demandesEnAttente = Payment::where('statut', 'en_attente')->count();
        $demandesOkami = Payment::where('statut', 'en_attente')->where('source_caisse', 'okami')->count();
        $demandesProprietaire = Payment::where('statut', 'en_attente')->fromProprietaire()->count();

        return view('livewire.collector.payments.index', [
            'payments' => $payments,
            'demandesEnAttente' => $demandesEnAttente,
            'demandesOkami' => $demandesOkami,
            'demandesProprietaire' => $demandesProprietaire,
            'soldeCaisse' => $soldeCaisse,
            'soldePartOkami' => $soldePartOkami,
            'soldePartProprietaire' => $soldePartProprietaire,
        ]);
    }
}


