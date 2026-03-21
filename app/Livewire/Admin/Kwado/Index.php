<?php

namespace App\Livewire\Admin\Kwado;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\KwadoService;
use App\Models\Cleaner;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

#[Layout('components.dashlite-layout')]
class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $filterCleaner = '';
    public $filterType = '';
    public $dateDebut = '';
    public $dateFin = '';
    public $perPage = 20;

    protected $queryString = [
        'search' => ['except' => ''],
        'filterCleaner' => ['except' => ''],
        'filterType' => ['except' => ''],
    ];

    public function updatingSearch() { $this->resetPage(); }

    public function resetFilters()
    {
        $this->reset(['search', 'filterCleaner', 'filterType', 'dateDebut', 'dateFin']);
        $this->resetPage();
    }

    protected function getBaseQuery()
    {
        return KwadoService::with(['cleaner.user', 'moto.proprietaire.user'])
            ->when($this->search, function ($q) {
                $q->where(function ($q2) {
                    $q2->where('numero_service', 'like', '%'.$this->search.'%')
                       ->orWhere('plaque_externe', 'like', '%'.$this->search.'%')
                       ->orWhereHas('moto', fn($q3) => $q3->where('plaque_immatriculation', 'like', '%'.$this->search.'%'))
                       ->orWhereHas('cleaner.user', fn($q3) => $q3->where('name', 'like', '%'.$this->search.'%'));
                });
            })
            ->when($this->filterCleaner, fn($q) => $q->where('cleaner_id', $this->filterCleaner))
            ->when($this->filterType, fn($q) => $q->where('type_service', $this->filterType))
            ->when($this->dateDebut, fn($q) => $q->whereDate('date_service', '>=', $this->dateDebut))
            ->when($this->dateFin, fn($q) => $q->whereDate('date_service', '<=', $this->dateFin))
            ->orderBy('date_service', 'desc');
    }

    public function exportPdf()
    {
        $services = $this->getBaseQuery()->get();

        $pdf = Pdf::loadView('pdf.lists.kwado-services', [
            'services' => $services,
            'title' => 'Services KWADO - Administration',
            'subtitle' => 'Exporté le ' . Carbon::now()->format('d/m/Y à H:i'),
            'totalEncaisse' => $services->where('statut_paiement', 'payé')->sum('montant_encaisse'),
        ]);

        $pdf->setPaper('A4', 'landscape');

        return response()->streamDownload(function() use ($pdf) {
            echo $pdf->output();
        }, 'kwado_admin_' . Carbon::now()->format('Y-m-d') . '.pdf');
    }

    public function render()
    {
        $services = $this->getBaseQuery()->paginate($this->perPage);
        $cleaners = Cleaner::with('user')->where('is_active', true)->get();

        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();

        $stats = [
            'total_jour' => KwadoService::whereDate('date_service', $today)->count(),
            'recettes_jour' => KwadoService::whereDate('date_service', $today)->where('statut_paiement', 'payé')->sum('montant_encaisse'),
            'total_mois' => KwadoService::whereBetween('date_service', [$startOfMonth, now()])->count(),
            'recettes_mois' => KwadoService::whereBetween('date_service', [$startOfMonth, now()])->where('statut_paiement', 'payé')->sum('montant_encaisse'),
        ];

        return view('livewire.admin.kwado.index', compact('services', 'cleaners', 'stats'));
    }
}

