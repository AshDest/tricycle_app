<?php

namespace App\Livewire\Owner\Payments;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Payment;
use App\Models\Versement;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

#[Layout('components.dashlite-layout')]
class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $filterStatut = '';
    public $filterPeriode = '';
    public $dateFrom = '';
    public $dateTo = '';
    public $perPage = 15;

    // Stats
    public $totalRecu = 0;
    public $soldeDisponible = 0;
    public $enAttente = 0;
    public $arrieres = 0;

    // Message
    public $message = '';
    public $messageType = 'info';

    protected $queryString = ['search', 'filterStatut', 'filterPeriode', 'dateFrom', 'dateTo'];

    public function updatingSearch() { $this->resetPage(); }
    public function updatingFilterStatut() { $this->resetPage(); }
    public function updatingFilterPeriode() { $this->resetPage(); }
    public function updatingDateFrom() { $this->resetPage(); }
    public function updatingDateTo() { $this->resetPage(); }

    public function resetFilters()
    {
        $this->reset(['search', 'filterStatut', 'filterPeriode', 'dateFrom', 'dateTo']);
        $this->resetPage();
    }


    public function closeMessage()
    {
        $this->message = '';
    }

    public function telechargerRecu($paymentId)
    {
        $payment = Payment::with(['proprietaire.user'])->find($paymentId);

        if (!$payment) {
            $this->message = 'Paiement non trouvé.';
            $this->messageType = 'danger';
            return;
        }

        // Vérifier que le paiement appartient au propriétaire connecté
        $proprietaire = auth()->user()->proprietaire;
        if (!$proprietaire || $payment->proprietaire_id !== $proprietaire->id) {
            $this->message = 'Vous n\'êtes pas autorisé à télécharger ce reçu.';
            $this->messageType = 'danger';
            return;
        }

        // Vérifier que le paiement est payé
        if (!in_array($payment->statut, ['paye', 'payé'])) {
            $this->message = 'Le reçu n\'est disponible que pour les paiements effectués.';
            $this->messageType = 'warning';
            return;
        }

        $pdf = Pdf::loadView('pdf.recu-paiement', compact('payment'));

        // Dimensions d'un petit reçu (80mm x 200mm)
        $pdf->setPaper([0, 0, 226.77, 566.93], 'portrait');

        $filename = 'recu_paiement_' . $payment->id . '_' . now()->format('YmdHis') . '.pdf';

        return response()->streamDownload(function() use ($pdf) {
            echo $pdf->output();
        }, $filename);
    }

    protected function getBaseQuery()
    {
        $proprietaire = auth()->user()->proprietaire;
        $proprietaire_id = $proprietaire?->id;

        return Payment::where('proprietaire_id', $proprietaire_id)
            ->when($this->filterStatut, fn($q) => $q->where('statut', $this->filterStatut))
            ->when($this->dateFrom, fn($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->orderBy('created_at', 'desc');
    }

    public function exportPdf()
    {
        $payments = $this->getBaseQuery()->get();

        $stats = [
            'total' => $payments->count(),
            'total_montant' => $payments->sum('total_du'),
            'payes' => $payments->where('statut', 'paye')->count(),
            'en_attente' => $payments->where('statut', 'en_attente')->count(),
        ];

        $pdf = Pdf::loadView('pdf.lists.payments', [
            'payments' => $payments,
            'stats' => $stats,
            'title' => 'Mes Paiements - Propriétaire',
            'subtitle' => 'Exporté le ' . Carbon::now()->format('d/m/Y à H:i'),
            'filtres' => [
                'statut' => $this->filterStatut,
                'date_from' => $this->dateFrom,
                'date_to' => $this->dateTo,
            ],
        ]);

        $pdf->setPaper('A4', 'landscape');

        return response()->streamDownload(function() use ($pdf) {
            echo $pdf->output();
        }, 'mes_paiements_' . Carbon::now()->format('Y-m-d') . '.pdf');
    }

    public function render()
    {
        $proprietaire = auth()->user()->proprietaire;
        $proprietaire_id = $proprietaire?->id;

        // Calculer les statistiques
        if ($proprietaire) {
            $this->totalRecu = Payment::where('proprietaire_id', $proprietaire_id)
                ->where('statut', 'paye')
                ->sum('total_paye') ?? 0;

            $this->enAttente = Payment::where('proprietaire_id', $proprietaire_id)
                ->where('statut', 'en_attente')
                ->sum('total_du') ?? 0;

            // Solde disponible = versements des motos - paiements reçus
            $totalVersements = $proprietaire->versements()
                ->where('versements.statut', 'payé')
                ->sum('versements.montant') ?? 0;

            $this->soldeDisponible = max(0, $totalVersements - $this->totalRecu);

            // Arriérés = versements en retard des motards
            $this->arrieres = $proprietaire->versements()
                ->whereIn('versements.statut', ['en_retard', 'partiellement_payé'])
                ->selectRaw('COALESCE(SUM(versements.montant_attendu - COALESCE(versements.montant, 0)), 0) as total')
                ->value('total') ?? 0;
        }

        $payments = Payment::where('proprietaire_id', $proprietaire_id)
            ->when($this->filterStatut, fn($q) => $q->where('statut', $this->filterStatut))
            ->when($this->dateFrom, fn($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        return view('livewire.owner.payments.index', compact('payments'));
    }
}
