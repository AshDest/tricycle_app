<?php

namespace App\Livewire\Admin\Caissiers;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\Caissier;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

#[Layout('components.dashlite-layout')]
class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $filterZone = '';
    public $filterStatut = '';
    public $perPage = 15;

    protected $queryString = ['search', 'filterZone', 'filterStatut'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterZone()
    {
        $this->resetPage();
    }

    public function updatingFilterStatut()
    {
        $this->resetPage();
    }

    public function toggleActive(Caissier $caissier)
    {
        $caissier->update(['is_active' => !$caissier->is_active]);
    }

    public function delete(Caissier $caissier)
    {
        $caissier->delete();
        session()->flash('success', 'Caissier supprime avec succes.');
    }

    protected function getBaseQuery()
    {
        return Caissier::with(['user'])
            ->when($this->search, function ($q) {
                $q->whereHas('user', function ($q2) {
                    $q2->where('name', 'like', '%' . $this->search . '%')
                       ->orWhere('email', 'like', '%' . $this->search . '%');
                })->orWhere('numero_identifiant', 'like', '%' . $this->search . '%')
                  ->orWhere('nom_point_collecte', 'like', '%' . $this->search . '%');
            })
            ->when($this->filterZone, function ($q) {
                $q->where('zone', $this->filterZone);
            })
            ->when($this->filterStatut !== '', function ($q) {
                if ($this->filterStatut === 'actif') {
                    $q->where('is_active', true);
                } elseif ($this->filterStatut === 'inactif') {
                    $q->where('is_active', false);
                }
            })
            ->orderBy('created_at', 'desc');
    }

    public function exportPdf()
    {
        $caissiers = $this->getBaseQuery()->get();

        $stats = [
            'total' => $caissiers->count(),
            'actifs' => $caissiers->where('is_active', true)->count(),
            'zones' => $caissiers->pluck('zone')->unique()->filter()->count(),
        ];

        $pdf = Pdf::loadView('pdf.lists.caissiers', [
            'caissiers' => $caissiers,
            'stats' => $stats,
            'title' => 'Liste des Caissiers',
            'subtitle' => 'Exporté le ' . Carbon::now()->format('d/m/Y à H:i'),
            'filtres' => [
                'search' => $this->search,
                'zone' => $this->filterZone,
            ],
        ]);

        $pdf->setPaper('A4', 'landscape');

        return response()->streamDownload(function() use ($pdf) {
            echo $pdf->output();
        }, 'caissiers_' . Carbon::now()->format('Y-m-d') . '.pdf');
    }

    public function render()
    {
        $caissiers = $this->getBaseQuery()->paginate($this->perPage);
        $zones = Caissier::distinct()->pluck('zone')->filter();

        return view('livewire.admin.caissiers.index', compact('caissiers', 'zones'));
    }
}
