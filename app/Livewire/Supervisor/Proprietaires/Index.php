<?php

namespace App\Livewire\Supervisor\Proprietaires;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Proprietaire;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

#[Layout('components.dashlite-layout')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterStatus = '';
    public string $sortBy = 'created_at';
    public string $sortDirection = 'desc';
    public int $perPage = 15;

    protected $queryString = [
        'search' => ['except' => ''],
        'filterStatus' => ['except' => ''],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function sortByColumn(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    public function toggleActive(int $id): void
    {
        $proprietaire = Proprietaire::findOrFail($id);
        $proprietaire->update(['is_active' => !$proprietaire->is_active]);

        session()->flash('success', 'Statut du propriétaire mis à jour.');
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'filterStatus']);
        $this->resetPage();
    }

    protected function getBaseQuery()
    {
        return Proprietaire::query()
            ->with(['user', 'motos'])
            ->withCount('motos')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('raison_sociale', 'like', '%' . $this->search . '%')
                      ->orWhere('telephone', 'like', '%' . $this->search . '%')
                      ->orWhere('adresse', 'like', '%' . $this->search . '%')
                      ->orWhereHas('user', function ($uq) {
                          $uq->where('name', 'like', '%' . $this->search . '%')
                            ->orWhere('email', 'like', '%' . $this->search . '%');
                      });
                });
            })
            ->when($this->filterStatus === '1', function ($query) {
                $query->where('is_active', true);
            })
            ->when($this->filterStatus === '0', function ($query) {
                $query->where('is_active', false);
            })
            ->orderBy($this->sortBy, $this->sortDirection);
    }

    public function exportPdf()
    {
        $proprietaires = $this->getBaseQuery()->get();

        $stats = [
            'total' => $proprietaires->count(),
            'total_motos' => $proprietaires->sum('motos_count'),
            'total_du' => 0,
            'total_paye' => 0,
        ];

        $pdf = Pdf::loadView('pdf.lists.proprietaires', [
            'proprietaires' => $proprietaires,
            'stats' => $stats,
            'title' => 'Liste des Propriétaires - OKAMI',
            'subtitle' => 'Exporté le ' . Carbon::now()->format('d/m/Y à H:i'),
            'filtres' => [
                'search' => $this->search,
            ],
        ]);

        $pdf->setPaper('A4', 'landscape');

        return response()->streamDownload(function() use ($pdf) {
            echo $pdf->output();
        }, 'proprietaires_okami_' . Carbon::now()->format('Y-m-d') . '.pdf');
    }

    public function render()
    {
        $proprietaires = $this->getBaseQuery()->paginate($this->perPage);

        return view('livewire.supervisor.proprietaires.index', [
            'proprietaires' => $proprietaires,
        ]);
    }
}

