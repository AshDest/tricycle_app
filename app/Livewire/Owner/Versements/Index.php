<?php

namespace App\Livewire\Owner\Versements;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Versement;
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

    protected function getBaseQuery()
    {
        $proprietaire = auth()->user()->proprietaire;
        $proprietaire_id = $proprietaire?->id;

        return Versement::with(['motard.user', 'moto', 'caissier'])
            ->whereHas('moto', function ($q) use ($proprietaire_id) {
                $q->where('proprietaire_id', $proprietaire_id);
            })
            ->when($this->filterStatut, function ($q) {
                $q->where('statut', $this->filterStatut);
            })
            ->orderBy('created_at', 'desc');
    }

    public function exportPdf()
    {
        $versements = $this->getBaseQuery()->get();

        $stats = [
            'total' => $versements->count(),
            'total_montant' => $versements->sum('montant'),
            'payes' => $versements->where('statut', 'paye')->count(),
            'en_retard' => $versements->where('statut', 'en_retard')->count(),
        ];

        $pdf = Pdf::loadView('pdf.lists.versements', [
            'versements' => $versements,
            'stats' => $stats,
            'title' => 'Mes Versements - Propriétaire',
            'subtitle' => 'Exporté le ' . Carbon::now()->format('d/m/Y à H:i'),
            'filtres' => [
                'statut' => $this->filterStatut,
            ],
        ]);

        $pdf->setPaper('A4', 'landscape');

        return response()->streamDownload(function() use ($pdf) {
            echo $pdf->output();
        }, 'mes_versements_' . Carbon::now()->format('Y-m-d') . '.pdf');
    }

    public function render()
    {
        $versements = $this->getBaseQuery()->paginate($this->perPage);

        return view('livewire.owner.versements.index', compact('versements'));
    }
}
