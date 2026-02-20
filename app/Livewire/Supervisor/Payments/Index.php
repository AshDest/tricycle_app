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
            ->orderByRaw("FIELD(statut, 'pay', 'en_attente', 'approuve', 'rejet')")
            ->orderBy('created_at', 'desc');
    }

    /**
     * Valider un paiement effectué
     */
    public function validerPaiement($paymentId)
    {
        $payment = Payment::findOrFail($paymentId);

        if ($payment->statut !== 'pay') {
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
        $this->paiementsAValider = Payment::where('statut', 'pay')->count();
        $this->totalPaye = Payment::where('statut', 'approuve')->sum('total_paye');

        // Liste des paiements
        $payments = Payment::with(['proprietaire.user', 'demandePar', 'traitePar', 'validePar'])
            ->when($this->search, function($q) {
                $q->whereHas('proprietaire.user', fn($q2) => $q2->where('name', 'like', '%'.$this->search.'%'));
            })
            ->when($this->filterStatut, fn($q) => $q->where('statut', $this->filterStatut))
            ->when($this->filterProprietaire, fn($q) => $q->where('proprietaire_id', $this->filterProprietaire))
            ->orderByRaw("FIELD(statut, 'pay', 'en_attente', 'approuve', 'rejet')")
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        // Liste des propriétaires pour le filtre
        $proprietaires = Proprietaire::with('user')->get();

        return view('livewire.supervisor.payments.index', [
            'payments' => $payments,
            'proprietaires' => $proprietaires,
        ]);
    }
}
