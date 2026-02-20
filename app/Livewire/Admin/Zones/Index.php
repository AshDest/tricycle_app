<?php

namespace App\Livewire\Admin\Zones;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Zone;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

#[Layout('components.dashlite-layout')]
class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $filterStatut = '';
    public $perPage = 15;

    protected $queryString = ['search', 'filterStatut'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterStatut()
    {
        $this->resetPage();
    }

    public function toggleActive(Zone $zone)
    {
        $zone->update(['is_active' => !$zone->is_active]);
    }

    public function delete(Zone $zone)
    {
        $zone->delete();
        session()->flash('success', 'Zone supprimee avec succes.');
    }

    protected function getBaseQuery()
    {
        return Zone::when($this->search, function ($q) {
                $q->where('nom', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
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
        $zones = $this->getBaseQuery()->get();

        $stats = [
            'total' => $zones->count(),
            'actives' => $zones->where('is_active', true)->count(),
            'motards' => 0,
        ];

        $pdf = Pdf::loadView('pdf.lists.zones', [
            'zones' => $zones,
            'stats' => $stats,
            'title' => 'Liste des Zones',
            'subtitle' => 'Exporté le ' . Carbon::now()->format('d/m/Y à H:i'),
        ]);

        $pdf->setPaper('A4', 'portrait');

        return response()->streamDownload(function() use ($pdf) {
            echo $pdf->output();
        }, 'zones_' . Carbon::now()->format('Y-m-d') . '.pdf');
    }

    public function render()
    {
        $zones = $this->getBaseQuery()->paginate($this->perPage);

        return view('livewire.admin.zones.index', compact('zones'));
    }
}
