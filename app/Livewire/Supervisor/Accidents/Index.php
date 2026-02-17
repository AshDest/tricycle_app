<?php

namespace App\Livewire\Supervisor\Accidents;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Accident;
use App\Models\Moto;
use Carbon\Carbon;

#[Layout('components.dashlite-layout')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterStatut = '';
    public string $filterGravite = '';
    public string $filterMoto = '';
    public string $dateDebut = '';
    public string $dateFin = '';
    public int $perPage = 15;

    protected $queryString = [
        'search' => ['except' => ''],
        'filterStatut' => ['except' => ''],
        'filterGravite' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->reset(['search', 'filterStatut', 'filterGravite', 'filterMoto', 'dateDebut', 'dateFin']);
        $this->resetPage();
    }

    public function export()
    {
        $filename = 'accidents_' . now()->format('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'ID', 'Date', 'Moto', 'Motard', 'Lieu',
                'Gravité', 'Estimation', 'Coût Réel', 'Statut'
            ]);

            $accidents = $this->getFilteredQuery()->get();
            foreach ($accidents as $a) {
                fputcsv($handle, [
                    $a->id,
                    $a->date_heure?->format('d/m/Y H:i'),
                    $a->moto->plaque_immatriculation ?? 'N/A',
                    $a->motard->user->name ?? 'N/A',
                    $a->lieu ?? 'N/A',
                    $a->gravite ?? 'N/A',
                    $a->estimation_cout ?? 0,
                    $a->cout_reel ?? 0,
                    $a->statut ?? 'N/A',
                ]);
            }

            fclose($handle);
        }, $filename);
    }

    protected function getFilteredQuery()
    {
        return Accident::with(['moto.proprietaire.user', 'motard.user'])
            ->when($this->search, function ($q) {
                $q->where(function ($query) {
                    $query->where('lieu', 'like', '%' . $this->search . '%')
                          ->orWhere('description', 'like', '%' . $this->search . '%')
                          ->orWhereHas('moto', function ($q2) {
                              $q2->where('plaque_immatriculation', 'like', '%' . $this->search . '%');
                          })
                          ->orWhereHas('motard.user', function ($q2) {
                              $q2->where('name', 'like', '%' . $this->search . '%');
                          });
                });
            })
            ->when($this->filterStatut, function ($q) {
                $q->where('statut', $this->filterStatut);
            })
            ->when($this->filterGravite, function ($q) {
                $q->where('gravite', $this->filterGravite);
            })
            ->when($this->filterMoto, function ($q) {
                $q->where('moto_id', $this->filterMoto);
            })
            ->when($this->dateDebut, function ($q) {
                $q->whereDate('date_heure', '>=', $this->dateDebut);
            })
            ->when($this->dateFin, function ($q) {
                $q->whereDate('date_heure', '<=', $this->dateFin);
            })
            ->orderBy('date_heure', 'desc');
    }

    public function render()
    {
        $accidents = $this->getFilteredQuery()->paginate($this->perPage);

        // Statistiques
        $stats = [
            'total' => Accident::count(),
            'declares' => Accident::where('statut', 'declare')->count(),
            'enReparation' => Accident::where('statut', 'en_reparation')->count(),
            'clotures' => Accident::where('statut', 'cloture')->count(),
            'coutTotal' => Accident::sum('cout_reel'),
            'graves' => Accident::where('gravite', 'grave')->count(),
        ];

        $motos = Moto::orderBy('plaque_immatriculation')->get();

        return view('livewire.supervisor.accidents.index', compact('accidents', 'stats', 'motos'));
    }
}

