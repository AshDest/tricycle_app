<?php

namespace App\Livewire\Supervisor\Payments;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Payment;
use App\Models\Proprietaire;
use App\Services\PaymentService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

/**
 * Gestion des demandes de paiement par OKAMI
 * - Soumettre une demande au bénéfice d'un propriétaire
 * - Valider les paiements effectués par le collecteur
 * - Modifier/Supprimer les demandes en attente
 */
#[Layout('components.dashlite-layout')]
class Index extends Component
{
    use WithPagination;

    public $filterStatut = '';
    public $filterProprietaire = '';
    public $search = '';
    public $perPage = 15;

    // Stats
    public $demandesEnAttente = 0;
    public $paiementsAValider = 0;
    public $totalPaye = 0;

    // Modal d'édition
    public $showEditModal = false;
    public $editPayment = null;
    public $editMontant = '';
    public $editModePaiement = '';
    public $editNumeroCompte = '';
    public $editNotes = '';

    protected $queryString = ['filterStatut', 'filterProprietaire', 'search'];

    public function updatingSearch() { $this->resetPage(); }
    public function updatingFilterStatut() { $this->resetPage(); }
    public function updatingFilterProprietaire() { $this->resetPage(); }

    public function resetFilters()
    {
        $this->reset(['filterStatut', 'filterProprietaire', 'search']);
        $this->resetPage();
    }

    protected function getBaseQuery()
    {
        return Payment::with(['proprietaire.user', 'demandePar', 'traitePar', 'validePar'])
            ->when($this->search, function($q) {
                $q->whereHas('proprietaire.user', fn($q2) => $q2->where('name', 'like', '%'.$this->search.'%'));
            })
            ->when($this->filterStatut, fn($q) => $q->where('statut', $this->filterStatut))
            ->when($this->filterProprietaire, fn($q) => $q->where('proprietaire_id', $this->filterProprietaire))
            ->orderByRaw("FIELD(statut, 'paye', 'en_attente', 'approuve', 'rejete')")
            ->orderBy('created_at', 'desc');
    }

    /**
     * Ouvrir le modal d'édition
     */
    public function ouvrirEdition($paymentId)
    {
        $payment = Payment::with('proprietaire.user')->findOrFail($paymentId);

        if ($payment->statut !== 'en_attente') {
            session()->flash('error', 'Seules les demandes en attente peuvent être modifiées.');
            return;
        }

        $this->editPayment = $payment;
        $this->editMontant = $payment->total_du;
        $this->editModePaiement = $payment->mode_paiement;
        $this->editNumeroCompte = $payment->numero_compte;
        $this->editNotes = $payment->notes;
        $this->showEditModal = true;
    }

    /**
     * Fermer le modal d'édition
     */
    public function fermerEdition()
    {
        $this->showEditModal = false;
        $this->editPayment = null;
        $this->reset(['editMontant', 'editModePaiement', 'editNumeroCompte', 'editNotes']);
    }

    /**
     * Sauvegarder les modifications
     */
    public function sauvegarderModification()
    {
        $this->validate([
            'editMontant' => 'required|numeric|min:1',
            'editModePaiement' => 'required|in:cash,mpesa,airtel_money,orange_money,virement_bancaire',
        ], [
            'editMontant.required' => 'Le montant est obligatoire.',
            'editMontant.min' => 'Le montant doit être supérieur à 0.',
            'editModePaiement.required' => 'Le mode de paiement est obligatoire.',
        ]);

        if (!$this->editPayment || $this->editPayment->statut !== 'en_attente') {
            session()->flash('error', 'Cette demande ne peut plus être modifiée.');
            $this->fermerEdition();
            return;
        }

        // Vérifier que le montant ne dépasse pas le solde disponible
        $paymentService = new PaymentService();
        $soldeDisponible = $paymentService->getSoldeDisponibleProprietaire($this->editPayment->proprietaire);

        if ($this->editMontant > $soldeDisponible) {
            $this->addError('editMontant', "Le montant dépasse le solde disponible ({$soldeDisponible} FC).");
            return;
        }

        $this->editPayment->update([
            'total_du' => $this->editMontant,
            'mode_paiement' => $this->editModePaiement,
            'numero_compte' => $this->editNumeroCompte,
            'notes' => $this->editNotes,
        ]);

        session()->flash('success', 'Demande de paiement modifiée avec succès.');
        $this->fermerEdition();
    }

    /**
     * Supprimer une demande en attente
     */
    public function supprimerDemande($paymentId)
    {
        $payment = Payment::findOrFail($paymentId);

        if ($payment->statut !== 'en_attente') {
            session()->flash('error', 'Seules les demandes en attente peuvent être supprimées.');
            return;
        }

        $payment->delete();
        session()->flash('success', 'Demande de paiement supprimée.');
    }

    /**
     * Valider un paiement effectué
     */
    public function validerPaiement($paymentId)
    {
        $payment = Payment::findOrFail($paymentId);

        if ($payment->statut !== 'paye') {
            session()->flash('error', 'Ce paiement ne peut pas être validé.');
            return;
        }

        $paymentService = new PaymentService();
        $paymentService->validerPaiement($payment, auth()->id(), 'Validé par OKAMI');

        session()->flash('success', 'Paiement validé avec succès.');
    }

    /**
     * Rejeter un paiement
     */
    public function rejeterPaiement($paymentId, $motif = 'Rejeté par OKAMI')
    {
        $payment = Payment::findOrFail($paymentId);

        $paymentService = new PaymentService();
        $paymentService->rejeterPaiement($payment, auth()->id(), $motif);

        session()->flash('success', 'Paiement rejeté.');
    }

    public function exportPdf()
    {
        $payments = $this->getBaseQuery()->get();

        $stats = [
            'total' => $payments->count(),
            'total_montant' => $payments->sum('total_du'),
            'payes' => $payments->whereIn('statut', ['paye', 'approuve'])->count(),
            'en_attente' => $payments->where('statut', 'en_attente')->count(),
        ];

        $pdf = Pdf::loadView('pdf.lists.payments', [
            'payments' => $payments,
            'stats' => $stats,
            'title' => 'Liste des Paiements - OKAMI',
            'subtitle' => 'Exporté le ' . Carbon::now()->format('d/m/Y à H:i'),
            'filtres' => [
                'search' => $this->search,
                'statut' => $this->filterStatut,
            ],
        ]);

        $pdf->setPaper('A4', 'landscape');

        return response()->streamDownload(function() use ($pdf) {
            echo $pdf->output();
        }, 'payments_okami_' . Carbon::now()->format('Y-m-d') . '.pdf');
    }

    public function render()
    {
        $paymentService = new PaymentService();

        // Calculer les stats
        $this->demandesEnAttente = Payment::where('statut', 'en_attente')->count();
        $this->paiementsAValider = Payment::where('statut', 'paye')->count();
        $this->totalPaye = Payment::where('statut', 'approuve')->sum('total_paye');

        // Liste des paiements
        $payments = $this->getBaseQuery()->paginate($this->perPage);

        // Liste des propriétaires pour le filtre
        $proprietaires = Proprietaire::with('user')->get();

        // Modes de paiement pour le modal
        $modesPaiement = Payment::getModesPaiement();

        return view('livewire.supervisor.payments.index', [
            'payments' => $payments,
            'proprietaires' => $proprietaires,
            'modesPaiement' => $modesPaiement,
        ]);
    }
}
