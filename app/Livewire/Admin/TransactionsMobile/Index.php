<?php
namespace App\Livewire\Admin\TransactionsMobile;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\TransactionMobile;
use App\Models\Collecteur;
use App\Models\Collecte;
use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;

#[Layout('components.dashlite-layout')]
class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $filterType = '';
    public $filterStatut = '';
    public $filterOperateur = '';
    public $filterCollecteur = '';
    public $dateDebut = '';
    public $dateFin = '';
    public int $perPage = 20;

    // Stats Transactions Mobile
    public $totalEnvois = 0;
    public $totalRetraits = 0;

    // Stats Caisse Globale
    public $totalCollectes = 0;
    public $totalPaiements = 0;
    public $soldeCaisseGlobal = 0;

    protected $queryString = ['search', 'filterType', 'filterStatut', 'filterOperateur', 'filterCollecteur'];

    public function mount()
    {
        $this->dateDebut = now()->startOfMonth()->format('Y-m-d');
        $this->dateFin = now()->format('Y-m-d');
        $this->computeStats();
    }

    public function updatingSearch() { $this->resetPage(); }

    public function updated($property)
    {
        if (in_array($property, ['dateDebut', 'dateFin', 'filterType', 'filterStatut', 'filterOperateur', 'filterCollecteur'])) {
            $this->computeStats();
            $this->resetPage();
        }
    }

    private function computeStats()
    {
        // Stats des transactions mobile
        $queryTx = TransactionMobile::where('statut', 'complete')
            ->when($this->filterCollecteur, fn($q) => $q->where('collecteur_id', $this->filterCollecteur))
            ->when($this->dateDebut, fn($q) => $q->whereDate('date_transaction', '>=', $this->dateDebut))
            ->when($this->dateFin, fn($q) => $q->whereDate('date_transaction', '<=', $this->dateFin));

        $this->totalEnvois = (clone $queryTx)->where('type', 'envoi')->sum('montant');
        $this->totalRetraits = (clone $queryTx)->where('type', 'retrait')->sum('montant');

        // Solde caisse global des collecteurs (collectes - paiements)
        if ($this->filterCollecteur) {
            $collecteur = Collecteur::find($this->filterCollecteur);
            $this->totalCollectes = $collecteur ? ($collecteur->solde_caisse + $collecteur->solde_part_okami) : 0;
            $this->totalPaiements = Payment::where('traite_par', $collecteur?->user_id)
                ->where('statut', 'paye')
                ->sum('total_paye');
        } else {
            $this->totalCollectes = Collecteur::sum('solde_caisse') + Collecteur::sum('solde_part_okami');
            $this->totalPaiements = Payment::where('statut', 'paye')->sum('total_paye');
        }

        $this->soldeCaisseGlobal = $this->totalCollectes;
    }

    private function getBaseQuery()
    {
        return TransactionMobile::with('collecteur.user')
            ->when($this->search, fn($q) => $q->where(function($q2) {
                $q2->where('numero_transaction', 'like', "%{$this->search}%")
                   ->orWhere('numero_telephone', 'like', "%{$this->search}%")
                   ->orWhere('nom_beneficiaire', 'like', "%{$this->search}%");
            }))
            ->when($this->filterType, fn($q) => $q->where('type', $this->filterType))
            ->when($this->filterStatut, fn($q) => $q->where('statut', $this->filterStatut))
            ->when($this->filterOperateur, fn($q) => $q->where('operateur', $this->filterOperateur))
            ->when($this->filterCollecteur, fn($q) => $q->where('collecteur_id', $this->filterCollecteur))
            ->when($this->dateDebut, fn($q) => $q->whereDate('date_transaction', '>=', $this->dateDebut))
            ->when($this->dateFin, fn($q) => $q->whereDate('date_transaction', '<=', $this->dateFin))
            ->orderBy('date_transaction', 'desc');
    }

    public function resetFilters()
    {
        $this->reset(['search', 'filterType', 'filterStatut', 'filterOperateur', 'filterCollecteur']);
        $this->dateDebut = now()->startOfMonth()->format('Y-m-d');
        $this->dateFin = now()->format('Y-m-d');
        $this->computeStats();
    }

    public function exportPdf()
    {
        $transactions = $this->getBaseQuery()->get();
        $pdf = Pdf::loadView('pdf.lists.transactions-mobile', [
            'transactions' => $transactions,
            'collecteur' => $this->filterCollecteur ? Collecteur::with('user')->find($this->filterCollecteur) : null,
            'stats' => [
                'totalEnvois' => $this->totalEnvois,
                'totalRetraits' => $this->totalRetraits,
                'soldeCaisseGlobal' => $this->soldeCaisseGlobal,
            ],
            'periode' => $this->dateDebut . ' - ' . $this->dateFin,
            'title' => 'Transactions Mobile Money',
        ]);

        return response()->streamDownload(fn() => print($pdf->output()),
            'transactions_mobile_' . now()->format('Y-m-d') . '.pdf');
    }

    public function render()
    {
        return view('livewire.admin.transactions-mobile.index', [
            'transactions' => $this->getBaseQuery()->paginate($this->perPage),
            'types' => TransactionMobile::getTypes(),
            'statuts' => TransactionMobile::getStatuts(),
            'operateurs' => TransactionMobile::getOperateurs(),
            'collecteurs' => Collecteur::with('user')->get(),
            'nombreTransactions' => $this->getBaseQuery()->count(),
        ]);
    }
}
