<?php
namespace App\Livewire\Collector\TransactionsMobile;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\TransactionMobile;
use App\Models\Collecteur;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

#[Layout('components.dashlite-layout')]
class Index extends Component
{
    use WithPagination;
    public $search = '';
    public $filterType = '';
    public $filterStatut = '';
    public $filterOperateur = '';
    public $dateDebut = '';
    public $dateFin = '';
    public int $perPage = 15;
    // Stats
    public $totalEnvois = 0;
    public $totalRetraits = 0;
    public $soldeNet = 0;
    public $nombreTransactions = 0;
    protected $queryString = ['search', 'filterType', 'filterStatut', 'filterOperateur'];
    public function mount()
    {
        $this->dateDebut = now()->startOfMonth()->format('Y-m-d');
        $this->dateFin = now()->format('Y-m-d');
        $this->computeStats();
    }
    public function updatingSearch() { $this->resetPage(); }
    public function updatingFilterType() { $this->resetPage(); }
    public function updatingFilterStatut() { $this->resetPage(); }
    public function updatingFilterOperateur() { $this->resetPage(); }
    public function updated($property)
    {
        if (in_array($property, ['dateDebut', 'dateFin', 'filterType', 'filterStatut', 'filterOperateur'])) {
            $this->computeStats();
        }
    }
    private function getCollecteur()
    {
        return auth()->user()->collecteur;
    }
    private function computeStats()
    {
        $collecteur = $this->getCollecteur();
        if (!$collecteur) return;
        $query = TransactionMobile::where('collecteur_id', $collecteur->id)
            ->where('statut', 'complete');
        if ($this->dateDebut) {
            $query->whereDate('date_transaction', '>=', $this->dateDebut);
        }
        if ($this->dateFin) {
            $query->whereDate('date_transaction', '<=', $this->dateFin);
        }
        $this->totalEnvois = (clone $query)->where('type', 'envoi')->sum('montant');
        $this->totalRetraits = (clone $query)->where('type', 'retrait')->sum('montant');
        $this->soldeNet = $this->totalRetraits - $this->totalEnvois;
        $this->nombreTransactions = (clone $query)->count();
    }
    private function getBaseQuery()
    {
        $collecteur = $this->getCollecteur();
        if (!$collecteur) {
            return TransactionMobile::whereRaw('1 = 0');
        }
        return TransactionMobile::where('collecteur_id', $collecteur->id)
            ->when($this->search, fn($q) => $q->where(function($q2) {
                $q2->where('numero_transaction', 'like', "%{$this->search}%")
                   ->orWhere('numero_telephone', 'like', "%{$this->search}%")
                   ->orWhere('nom_beneficiaire', 'like', "%{$this->search}%");
            }))
            ->when($this->filterType, fn($q) => $q->where('type', $this->filterType))
            ->when($this->filterStatut, fn($q) => $q->where('statut', $this->filterStatut))
            ->when($this->filterOperateur, fn($q) => $q->where('operateur', $this->filterOperateur))
            ->when($this->dateDebut, fn($q) => $q->whereDate('date_transaction', '>=', $this->dateDebut))
            ->when($this->dateFin, fn($q) => $q->whereDate('date_transaction', '<=', $this->dateFin))
            ->orderBy('date_transaction', 'desc');
    }
    public function resetFilters()
    {
        $this->reset(['search', 'filterType', 'filterStatut', 'filterOperateur']);
        $this->dateDebut = now()->startOfMonth()->format('Y-m-d');
        $this->dateFin = now()->format('Y-m-d');
        $this->computeStats();
    }
    public function exportPdf()
    {
        $transactions = $this->getBaseQuery()->get();
        $collecteur = $this->getCollecteur();
        $pdf = Pdf::loadView('pdf.lists.transactions-mobile', [
            'transactions' => $transactions,
            'collecteur' => $collecteur,
            'stats' => [
                'totalEnvois' => $this->totalEnvois,
                'totalRetraits' => $this->totalRetraits,
                'soldeNet' => $this->soldeNet,
            ],
            'periode' => $this->dateDebut . ' - ' . $this->dateFin,
            'title' => 'Transactions Mobile Money',
        ]);
        return response()->streamDownload(fn() => print($pdf->output()),
            'transactions_mobile_' . now()->format('Y-m-d') . '.pdf');
    }
    public function render()
    {
        return view('livewire.collector.transactions-mobile.index', [
            'transactions' => $this->getBaseQuery()->paginate($this->perPage),
            'types' => TransactionMobile::getTypes(),
            'statuts' => TransactionMobile::getStatuts(),
            'operateurs' => TransactionMobile::getOperateurs(),
        ]);
    }
}
