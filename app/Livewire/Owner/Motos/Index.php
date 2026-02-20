<?php

namespace App\Livewire\Owner\Motos;

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
    public $perPage = 15;

    protected $queryString = ['search'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    protected function getBaseQuery()
    {
        return Moto::where('proprietaire_id', auth()->user()->proprietaire?->id ?? null)
            ->when($this->search, function ($q) {
                $q->where('plaque_immatriculation', 'like', '%' . $this->search . '%')
                  ->orWhere('numero_matricule', 'like', '%' . $this->search . '%');
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
            'title' => 'Mes Motos - Propriétaire',
            'subtitle' => 'Exporté le ' . Carbon::now()->format('d/m/Y à H:i'),
            'filtres' => [
                'search' => $this->search,
            ],
        ]);

        $pdf->setPaper('A4', 'landscape');

        return response()->streamDownload(function() use ($pdf) {
            echo $pdf->output();
        }, 'mes_motos_' . Carbon::now()->format('Y-m-d') . '.pdf');
    }

    public function render()
    {
        $motos = $this->getBaseQuery()->paginate($this->perPage);

        return view('livewire.owner.motos.index', compact('motos'));
    }
}
