<?php

namespace App\Livewire\Cleaner;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\KwadoService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

#[Layout('components.dashlite-layout')]
class KwadoList extends Component
{
    use WithPagination;

    public $search = '';
    public $filterType = '';
    public $filterStatut = '';
    public $filterDate = '';
    public $perPage = 15;

    // Stats
    public $totalJour = 0;
    public $recettesJour = 0;
    public $totalMois = 0;
    public $recettesMois = 0;

    protected $queryString = ['search', 'filterType', 'filterStatut', 'filterDate'];

    public function updatingSearch() { $this->resetPage(); }
    public function updatingFilterType() { $this->resetPage(); }
    public function updatingFilterStatut() { $this->resetPage(); }
    public function updatingFilterDate() { $this->resetPage(); }

    public function resetFilters()
    {
        $this->reset(['search', 'filterType', 'filterStatut', 'filterDate']);
        $this->resetPage();
    }

    protected function getBaseQuery()
    {
        $cleaner = auth()->user()->cleaner;
        if (!$cleaner) return KwadoService::whereRaw('1=0');

        return KwadoService::where('cleaner_id', $cleaner->id)
            ->when($this->search, function ($q) {
                $q->where(function ($q2) {
                    $q2->where('numero_service', 'like', '%'.$this->search.'%')
                       ->orWhere('plaque_externe', 'like', '%'.$this->search.'%')
                       ->orWhere('proprietaire_externe', 'like', '%'.$this->search.'%')
                       ->orWhereHas('moto', fn($q3) => $q3->where('plaque_immatriculation', 'like', '%'.$this->search.'%'));
                });
            })
            ->when($this->filterType, fn($q) => $q->where('type_service', $this->filterType))
            ->when($this->filterStatut, fn($q) => $q->where('statut_paiement', $this->filterStatut))
            ->when($this->filterDate, fn($q) => $q->whereDate('date_service', $this->filterDate))
            ->orderBy('date_service', 'desc')
            ->orderBy('created_at', 'desc');
    }

    public function exportPdf()
    {
        $services = $this->getBaseQuery()->get();

        $pdf = Pdf::loadView('pdf.lists.kwado-services', [
            'services' => $services,
            'title' => 'Services KWADO - Réparation Pneus',
            'subtitle' => 'Exporté le ' . Carbon::now()->format('d/m/Y à H:i'),
            'totalEncaisse' => $services->where('statut_paiement', 'payé')->sum('montant_encaisse'),
        ]);

        $pdf->setPaper('A4', 'landscape');

        return response()->streamDownload(function() use ($pdf) {
            echo $pdf->output();
        }, 'kwado_services_' . Carbon::now()->format('Y-m-d') . '.pdf');
    }

    public function telechargerRecu($serviceId)
    {
        $service = KwadoService::with(['cleaner.user', 'moto.proprietaire.user'])->findOrFail($serviceId);

        $pdf = Pdf::loadView('pdf.recu-kwado', compact('service'));
        $pdf->setPaper([0, 0, 204, 400], 'portrait');

        $filename = 'recu_kwado_' . $service->numero_service . '.pdf';

        return response()->streamDownload(function() use ($pdf) {
            echo $pdf->output();
        }, $filename);
    }

    public function render()
    {
        $cleaner = auth()->user()->cleaner;

        if ($cleaner) {
            $today = Carbon::today();
            $startOfMonth = Carbon::now()->startOfMonth();

            $this->totalJour = KwadoService::where('cleaner_id', $cleaner->id)
                ->whereDate('date_service', $today)->count();
            $this->recettesJour = KwadoService::where('cleaner_id', $cleaner->id)
                ->whereDate('date_service', $today)->where('statut_paiement', 'payé')->sum('montant_encaisse');
            $this->totalMois = KwadoService::where('cleaner_id', $cleaner->id)
                ->whereBetween('date_service', [$startOfMonth, now()])->count();
            $this->recettesMois = KwadoService::where('cleaner_id', $cleaner->id)
                ->whereBetween('date_service', [$startOfMonth, now()])->where('statut_paiement', 'payé')->sum('montant_encaisse');
        }

        $services = $this->getBaseQuery()->paginate($this->perPage);

        return view('livewire.cleaner.kwado-list', compact('services'));
    }
}

