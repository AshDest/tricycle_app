<?php

namespace App\Livewire\Supervisor\Payments;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Payment;
use App\Models\Proprietaire;
use App\Models\SystemSetting;
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
    public $filterDateDebut = '';
    public $filterDateFin = '';
    public $search = '';
    public $perPage = 15;

    // Stats
    public $demandesEnAttente = 0;
    public $paiementsAValider = 0;
    public $totalPayeUsd = 0;
    public $tauxUsdCdf = 2800;

    // Modal d'édition
    public $showEditModal = false;
    public $editPayment = null;
    public $editMontant = '';
    public $editModePaiement = '';
    public $editNumeroCompte = '';
    public $editNotes = '';

    protected $queryString = ['filterStatut', 'filterProprietaire', 'filterDateDebut', 'filterDateFin', 'search'];

    public function updatingSearch() { $this->resetPage(); }
    public function updatingFilterStatut() { $this->resetPage(); }
    public function updatingFilterProprietaire() { $this->resetPage(); }
    public function updatingFilterDateDebut() { $this->resetPage(); }
    public function updatingFilterDateFin() { $this->resetPage(); }

    public function resetFilters()
    {
        $this->reset(['filterStatut', 'filterProprietaire', 'filterDateDebut', 'filterDateFin', 'search']);
        $this->resetPage();
    }

    protected function getBaseQuery()
    {
        return Payment::with(['proprietaire.user', 'demandePar', 'traitePar', 'validePar'])
            ->when($this->search, function($q) {
                $q->where(function($q2) {
                    $q2->whereHas('proprietaire.user', fn($q3) => $q3->where('name', 'like', '%'.$this->search.'%'))
                       ->orWhere('beneficiaire_nom', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->filterStatut, fn($q) => $q->where('statut', $this->filterStatut))
            ->when($this->filterProprietaire, fn($q) => $q->where('proprietaire_id', $this->filterProprietaire))
            ->when($this->filterDateDebut, fn($q) => $q->whereDate('date_demande', '>=', $this->filterDateDebut))
            ->when($this->filterDateFin, fn($q) => $q->whereDate('date_demande', '<=', $this->filterDateFin))
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
        // Afficher le montant en USD
        $taux = ($payment->taux_conversion && $payment->taux_conversion > 0) ? $payment->taux_conversion : $this->tauxUsdCdf;
        $this->editMontant = ($payment->montant_usd && $payment->montant_usd > 0)
            ? $payment->montant_usd
            : ($taux > 0 ? round($payment->total_du / $taux, 2) : $payment->total_du);
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
            'editMontant' => 'required|numeric|min:0.01',
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

        // Le montant est en USD, convertir en FC
        $montantUsd = $this->editMontant;
        $montantCdf = round($montantUsd * $this->tauxUsdCdf, 2);

        // Vérifier que le montant ne dépasse pas le solde disponible
        if ($this->editPayment->proprietaire_id) {
            $paymentService = new PaymentService();
            $soldeDisponible = $paymentService->getSoldeDisponibleProprietaire($this->editPayment->proprietaire);

            if ($montantCdf > $soldeDisponible) {
                $soldeUsd = $this->tauxUsdCdf > 0 ? round($soldeDisponible / $this->tauxUsdCdf, 2) : 0;
                $this->addError('editMontant', "Le montant dépasse le solde disponible ({$soldeUsd} USD ≈ {$soldeDisponible} FC).");
                return;
            }
        }

        $this->editPayment->update([
            'total_du' => $montantCdf,
            'montant_usd' => $montantUsd,
            'taux_conversion' => $this->tauxUsdCdf,
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
        $tauxActuel = $this->tauxUsdCdf;

        $totalUsd = $payments->sum(function ($p) use ($tauxActuel) {
            if ($p->montant_usd && $p->montant_usd > 0) {
                return $p->montant_usd;
            }
            $taux = ($p->taux_conversion && $p->taux_conversion > 0) ? $p->taux_conversion : $tauxActuel;
            return $taux > 0 ? round($p->total_du / $taux, 2) : 0;
        });

        $stats = [
            'total' => $payments->count(),
            'total_montant_usd' => $totalUsd,
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
        $this->tauxUsdCdf = SystemSetting::getTauxUsdCdf();

        // Calculer les stats
        $this->demandesEnAttente = Payment::where('statut', 'en_attente')->count();
        $this->paiementsAValider = Payment::where('statut', 'paye')->count();

        // Total payé en USD: utiliser montant_usd si disponible, sinon convertir total_paye
        $payementsApprouves = Payment::where('statut', 'approuve')->get();
        $this->totalPayeUsd = $payementsApprouves->sum(function ($p) {
            if ($p->montant_usd && $p->montant_usd > 0) {
                return $p->montant_usd;
            }
            // Convertir FC → USD avec le taux du paiement ou le taux actuel
            $taux = ($p->taux_conversion && $p->taux_conversion > 0) ? $p->taux_conversion : $this->tauxUsdCdf;
            return $taux > 0 ? round($p->total_paye / $taux, 2) : 0;
        });

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
            'tauxUsdCdf' => $this->tauxUsdCdf,
        ]);
    }
}
