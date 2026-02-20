<?php

namespace App\Livewire\Admin\Motards;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Motard;
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

    public function toggleActive(Motard $motard)
    {
        $motard->update(['is_active' => !$motard->is_active]);
    }

    public function delete(Motard $motard)
    {
        $motard->delete();
        session()->flash('success', 'Motard supprime avec succes.');
    }

    protected function getBaseQuery()
    {
        return Motard::with(['user', 'motoActuelle'])
            ->when($this->search, function ($q) {
                $q->whereHas('user', function ($q2) {
                    $q2->where('name', 'like', '%' . $this->search . '%')
                       ->orWhere('email', 'like', '%' . $this->search . '%');
                })->orWhere('numero_identifiant', 'like', '%' . $this->search . '%');
            })
            ->when($this->filterZone, function ($q) {
                $q->where('zone_affectation', $this->filterZone);
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
        $motards = $this->getBaseQuery()->get();

        $stats = [
            'total' => $motards->count(),
            'actifs' => $motards->where('is_active', true)->count(),
            'inactifs' => $motards->where('is_active', false)->count(),
            'zones' => $motards->pluck('zone_affectation')->unique()->filter()->count(),
        ];

        $pdf = Pdf::loadView('pdf.lists.motards', [
            'motards' => $motards,
            'stats' => $stats,
            'title' => 'Liste des Motards',
            'subtitle' => 'Exporté le ' . Carbon::now()->format('d/m/Y à H:i'),
            'filtres' => [
                'search' => $this->search,
                'zone' => $this->filterZone,
                'statut' => $this->filterStatut,
            ],
        ]);

        $pdf->setPaper('A4', 'landscape');

        return response()->streamDownload(function() use ($pdf) {
            echo $pdf->output();
        }, 'motards_' . Carbon::now()->format('Y-m-d') . '.pdf');
    }

    public function render()
    {
        $motards = $this->getBaseQuery()->paginate($this->perPage);
        $zones = Motard::distinct()->pluck('zone_affectation')->filter();

        return view('livewire.admin.motards.index', compact('motards', 'zones'));
    }
}
