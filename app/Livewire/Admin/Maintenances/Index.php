<?php

namespace App\Livewire\Admin\Maintenances;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Maintenance;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

#[Layout('components.dashlite-layout')]
class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $filterType = '';
    public $filterStatut = '';
    public $perPage = 15;

    protected $queryString = ['search', 'filterType', 'filterStatut'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterType()
    {
        $this->resetPage();
    }

    public function updatingFilterStatut()
    {
        $this->resetPage();
    }

    public function delete(Maintenance $maintenance)
    {
        $maintenance->forceDelete();
        session()->flash('success', 'Maintenance supprimée avec succès.');
    }

    /**
     * Mettre la maintenance en attente
     */
    public function mettreEnAttente(int $id)
    {
        $maintenance = Maintenance::findOrFail($id);
        $maintenance->update(['statut' => 'en_attente']);
        session()->flash('success', 'Maintenance mise en attente.');
    }

    /**
     * Mettre la maintenance en cours
     */
    public function mettreEnCours(int $id)
    {
        $maintenance = Maintenance::findOrFail($id);
        $maintenance->update(['statut' => 'en_cours']);

        // Mettre la moto en maintenance
        if ($maintenance->moto) {
            $maintenance->moto->update(['statut' => 'maintenance']);
        }

        session()->flash('success', 'Maintenance en cours.');
    }

    /**
     * Terminer la maintenance
     */
    public function terminer(int $id)
    {
        $maintenance = Maintenance::findOrFail($id);
        $maintenance->update([
            'statut' => 'termine',
            'date_fin' => now(),
        ]);

        // Remettre la moto en service si elle était en maintenance
        if ($maintenance->moto && $maintenance->moto->statut === 'maintenance') {
            $maintenance->moto->update(['statut' => 'actif']);
        }

        session()->flash('success', 'Maintenance marquée comme terminée.');
    }

    protected function getBaseQuery()
    {
        return Maintenance::with(['moto', 'motard'])
            ->when($this->search, function ($q) {
                $q->where('description', 'like', '%' . $this->search . '%')
                  ->orWhere('technicien_garage_nom', 'like', '%' . $this->search . '%');
            })
            ->when($this->filterType, function ($q) {
                $q->where('type', $this->filterType);
            })
            ->when($this->filterStatut, function ($q) {
                $q->where('statut', $this->filterStatut);
            })
            ->orderBy('date_intervention', 'desc');
    }

    public function exportPdf()
    {
        $maintenances = $this->getBaseQuery()->get();

        $stats = [
            'total' => $maintenances->count(),
            'total_cout' => $maintenances->sum(function($m) {
                return ($m->cout_pieces ?? 0) + ($m->cout_main_oeuvre ?? 0);
            }),
            'terminees' => $maintenances->where('statut', 'termine')->count(),
            'en_cours' => $maintenances->where('statut', 'en_cours')->count(),
        ];

        $pdf = Pdf::loadView('pdf.lists.maintenances', [
            'maintenances' => $maintenances,
            'stats' => $stats,
            'title' => 'Liste des Maintenances',
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
        }, 'maintenances_' . Carbon::now()->format('Y-m-d') . '.pdf');
    }

    public function render()
    {
        $maintenances = $this->getBaseQuery()->paginate($this->perPage);

        // Statistiques
        $maintenancesEnCours = Maintenance::where('statut', 'en_cours')->count();
        $maintenancesPlanifiees = Maintenance::where('statut', 'en_attente')->count();
        $maintenancesTerminees = Maintenance::where('statut', 'termine')
            ->whereMonth('updated_at', now()->month)
            ->whereYear('updated_at', now()->year)
            ->count();
        $coutTotalMois = Maintenance::where('statut', 'termine')
            ->whereMonth('updated_at', now()->month)
            ->whereYear('updated_at', now()->year)
            ->selectRaw('COALESCE(SUM(COALESCE(cout_pieces, 0) + COALESCE(cout_main_oeuvre, 0)), 0) as total')
            ->value('total');

        return view('livewire.admin.maintenances.index', compact(
            'maintenances',
            'maintenancesEnCours',
            'maintenancesPlanifiees',
            'maintenancesTerminees',
            'coutTotalMois'
        ));
    }
}
