<?php

namespace App\Livewire\Admin\Accidents;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Accident;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

#[Layout('components.dashlite-layout')]
class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $filterGravite = '';
    public $filterStatut = '';
    public $perPage = 15;

    protected $queryString = ['search', 'filterGravite', 'filterStatut'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterGravite()
    {
        $this->resetPage();
    }

    public function updatingFilterStatut()
    {
        $this->resetPage();
    }

    public function delete(Accident $accident)
    {
        $accident->forceDelete();
        session()->flash('success', 'Accident supprime avec succes.');
    }

    protected function getBaseQuery()
    {
        return Accident::with(['moto', 'motard'])
            ->when($this->search, function ($q) {
                $q->where('lieu', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            })
            ->when($this->filterGravite, function ($q) {
                $q->where('gravite', $this->filterGravite);
            })
            ->when($this->filterStatut, function ($q) {
                $q->where('statut', $this->filterStatut);
            })
            ->orderBy('date_heure', 'desc');
    }

    public function exportPdf()
    {
        $accidents = $this->getBaseQuery()->get();

        $stats = [
            'total' => $accidents->count(),
            'total_cout' => $accidents->sum('cout_estime'),
            'repares' => $accidents->where('statut', 'repare')->count(),
            'en_attente' => $accidents->whereIn('statut', ['declare', 'en_attente'])->count(),
        ];

        $pdf = Pdf::loadView('pdf.lists.accidents', [
            'accidents' => $accidents,
            'stats' => $stats,
            'title' => 'Liste des Accidents',
            'subtitle' => 'Exporté le ' . Carbon::now()->format('d/m/Y à H:i'),
            'filtres' => [
                'search' => $this->search,
                'statut' => $this->filterStatut,
            ],
        ]);

        $pdf->setPaper('A4', 'landscape');

        return response()->streamDownload(function() use ($pdf) {
            echo $pdf->output();
        }, 'accidents_' . Carbon::now()->format('Y-m-d') . '.pdf');
    }

    public function render()
    {
        $accidents = $this->getBaseQuery()->paginate($this->perPage);

        return view('livewire.admin.accidents.index', compact('accidents'));
    }
}
