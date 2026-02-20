<?php

namespace App\Livewire\Supervisor\Maintenances;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Maintenance;
use App\Models\Moto;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

#[Layout('components.dashlite-layout')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterStatut = '';
    public string $filterType = '';
    public string $filterMoto = '';
    public string $dateDebut = '';
    public string $dateFin = '';
    public int $perPage = 15;

    protected $queryString = [
        'search' => ['except' => ''],
        'filterStatut' => ['except' => ''],
        'filterType' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->reset(['search', 'filterStatut', 'filterType', 'filterMoto', 'dateDebut', 'dateFin']);
        $this->resetPage();
    }

    public function export()
    {
        $filename = 'maintenances_' . now()->format('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'ID', 'Date', 'Moto', 'Type', 'Description',
                'Coût Pièces', 'Coût Main d\'œuvre', 'Total', 'Statut'
            ]);

            $maintenances = $this->getFilteredQuery()->get();
            foreach ($maintenances as $m) {
                fputcsv($handle, [
                    $m->id,
                    $m->date_intervention?->format('d/m/Y'),
                    $m->moto->plaque_immatriculation ?? 'N/A',
                    $m->type ?? 'N/A',
                    $m->description ?? 'N/A',
                    $m->cout_pieces ?? 0,
                    $m->cout_main_oeuvre ?? 0,
                    $m->cout_total ?? 0,
                    $m->statut ?? 'N/A',
                ]);
            }

            fclose($handle);
        }, $filename);
    }

    public function exportPdf()
    {
        $maintenances = $this->getFilteredQuery()->get();

        $stats = [
            'total' => $maintenances->count(),
            'total_cout' => $maintenances->sum('cout_total'),
            'terminees' => $maintenances->where('statut', 'termine')->count(),
            'en_cours' => $maintenances->where('statut', 'en_cours')->count(),
        ];

        $pdf = Pdf::loadView('pdf.lists.maintenances', [
            'maintenances' => $maintenances,
            'stats' => $stats,
            'title' => 'Liste des Maintenances - OKAMI',
            'subtitle' => 'Exporté le ' . Carbon::now()->format('d/m/Y à H:i'),
            'filtres' => [
                'search' => $this->search,
                'type' => $this->filterType,
                'statut' => $this->filterStatut,
            ],
        ]);

        $pdf->setPaper('A4', 'landscape');

        return response()->streamDownload(function() use ($pdf) {
            echo $pdf->output();
        }, 'maintenances_okami_' . Carbon::now()->format('Y-m-d') . '.pdf');
    }

    protected function getFilteredQuery()
    {
        return Maintenance::with(['moto.proprietaire.user', 'motard.user', 'accident'])
            ->when($this->search, function ($q) {
                $q->where(function ($query) {
                    $query->where('description', 'like', '%' . $this->search . '%')
                          ->orWhere('technicien_garage_nom', 'like', '%' . $this->search . '%')
                          ->orWhereHas('moto', function ($q2) {
                              $q2->where('plaque_immatriculation', 'like', '%' . $this->search . '%');
                          });
                });
            })
            ->when($this->filterStatut, function ($q) {
                $q->where('statut', $this->filterStatut);
            })
            ->when($this->filterType, function ($q) {
                $q->where('type', $this->filterType);
            })
            ->when($this->filterMoto, function ($q) {
                $q->where('moto_id', $this->filterMoto);
            })
            ->when($this->dateDebut, function ($q) {
                $q->whereDate('date_intervention', '>=', $this->dateDebut);
            })
            ->when($this->dateFin, function ($q) {
                $q->whereDate('date_intervention', '<=', $this->dateFin);
            })
            ->orderBy('date_intervention', 'desc');
    }

    public function render()
    {
        $maintenances = $this->getFilteredQuery()->paginate($this->perPage);

        // Statistiques
        $stats = [
            'total' => Maintenance::count(),
            'enCours' => Maintenance::where('statut', 'en_cours')->count(),
            'terminees' => Maintenance::where('statut', 'termine')->count(),
            'coutTotal' => Maintenance::sum(\DB::raw('COALESCE(cout_pieces, 0) + COALESCE(cout_main_oeuvre, 0)')),
        ];

        $motos = Moto::orderBy('plaque_immatriculation')->get();

        return view('livewire.supervisor.maintenances.index', compact('maintenances', 'stats', 'motos'));
    }
}

