<?php

namespace App\Livewire\Admin\Motos;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Moto;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

#[Layout('components.dashlite-layout')]
class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $filterStatut = '';
    public $filterAssignation = '';
    public $perPage = 15;

    protected $queryString = ['search', 'filterStatut', 'filterAssignation'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function changeStatut(Moto $moto, string $statut)
    {
        $moto->update(['statut' => $statut]);
    }

    public function delete(Moto $moto)
    {
        $moto->delete();
        session()->flash('success', 'Moto supprimee avec succes.');
    }

    protected function getBaseQuery()
    {
        return Moto::with(['proprietaire.user', 'motard.user'])
            ->when($this->search, function ($q) {
                $q->where('plaque_immatriculation', 'like', '%' . $this->search . '%')
                  ->orWhere('numero_matricule', 'like', '%' . $this->search . '%')
                  ->orWhere('numero_chassis', 'like', '%' . $this->search . '%');
            })
            ->when($this->filterStatut, function ($q) {
                $q->where('statut', $this->filterStatut);
            })
            ->when($this->filterAssignation, function ($q) {
                if ($this->filterAssignation === 'assignee') {
                    $q->whereNotNull('motard_id');
                } elseif ($this->filterAssignation === 'non_assignee') {
                    $q->whereNull('motard_id');
                }
            })
            ->orderBy('created_at', 'desc');
    }

    public function exportPdf()
    {
        $motos = $this->getBaseQuery()->get();

        $stats = [
            'total' => $motos->count(),
            'actives' => $motos->where('statut', 'actif')->count(),
            'inactives' => $motos->where('statut', 'inactif')->count(),
            'en_maintenance' => $motos->where('statut', 'en_maintenance')->count(),
        ];

        $pdf = Pdf::loadView('pdf.lists.motos', [
            'motos' => $motos,
            'stats' => $stats,
            'title' => 'Liste des Motos',
            'subtitle' => 'Exporté le ' . Carbon::now()->format('d/m/Y à H:i'),
            'filtres' => [
                'search' => $this->search,
                'statut' => $this->filterStatut,
            ],
        ]);

        $pdf->setPaper('A4', 'landscape');

        return response()->streamDownload(function() use ($pdf) {
            echo $pdf->output();
        }, 'motos_' . Carbon::now()->format('Y-m-d') . '.pdf');
    }

    public function render()
    {
        $motos = $this->getBaseQuery()->paginate($this->perPage);

        return view('livewire.admin.motos.index', compact('motos'));
    }
}
