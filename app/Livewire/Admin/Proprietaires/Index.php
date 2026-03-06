<?php

namespace App\Livewire\Admin\Proprietaires;

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
    public string $filterStatut = '';
    public string $filterMotos = '';
    public string $sortBy = 'created_at';
    public string $sortDirection = 'desc';
    public int $perPage = 15;

    // Confirmation de suppression
    public ?int $confirmingDelete = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'filterStatut' => ['except' => ''],
        'filterMotos' => ['except' => ''],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatut(): void
    {
        $this->resetPage();
    }

    public function updatingFilterMotos(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->filterStatut = '';
        $this->filterMotos = '';
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

    /**
     * Activer/Désactiver un propriétaire
     */
    public function toggleActive(int $id): void
    {
        $proprietaire = Proprietaire::findOrFail($id);
        $proprietaire->update(['is_active' => !$proprietaire->is_active]);

        $status = $proprietaire->is_active ? 'activé' : 'désactivé';
        session()->flash('success', "Propriétaire {$status} avec succès.");
    }

    /**
     * Voir les détails d'un propriétaire
     */
    public function voir(int $id)
    {
        return redirect()->route('admin.proprietaires.show', $id);
    }

    public function confirmDelete(int $id): void
    {
        $this->confirmingDelete = $id;
    }

    public function cancelDelete(): void
    {
        $this->confirmingDelete = null;
    }

    public function delete(int $id): void
    {
        $proprietaire = Proprietaire::findOrFail($id);

        // Vérifier s'il a des motos actives
        if ($proprietaire->motos()->where('statut', 'actif')->exists()) {
            session()->flash('error', 'Impossible de supprimer ce propriétaire car il possède encore des motos actives.');
            $this->confirmingDelete = null;
            return;
        }

        // Supprimer le propriétaire (soft delete)
        $proprietaire->delete();

        // Désactiver le compte utilisateur associé
        if ($proprietaire->user) {
            $proprietaire->user->removeRole('owner');
        }

        session()->flash('success', 'Propriétaire supprimé avec succès.');
        $this->confirmingDelete = null;
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
            ->when($this->filterStatut !== '', function ($query) {
                $query->where('is_active', $this->filterStatut === '1');
            })
            ->when($this->filterMotos === 'avec', function ($query) {
                $query->has('motos');
            })
            ->when($this->filterMotos === 'sans', function ($query) {
                $query->doesntHave('motos');
            })
            ->orderBy($this->sortBy, $this->sortDirection);
    }

    public function exportPdf()
    {
        $proprietaires = $this->getBaseQuery()->get();

        $stats = [
            'total' => $proprietaires->count(),
            'total_motos' => $proprietaires->sum(fn($p) => $p->motos->count()),
            'total_du' => 0,
            'total_paye' => 0,
        ];

        $pdf = Pdf::loadView('pdf.lists.proprietaires', [
            'proprietaires' => $proprietaires,
            'stats' => $stats,
            'title' => 'Liste des Propriétaires',
            'subtitle' => 'Exporté le ' . Carbon::now()->format('d/m/Y à H:i'),
            'filtres' => [
                'search' => $this->search,
            ],
        ]);

        $pdf->setPaper('A4', 'landscape');

        return response()->streamDownload(function() use ($pdf) {
            echo $pdf->output();
        }, 'proprietaires_' . Carbon::now()->format('Y-m-d') . '.pdf');
    }

    public function render()
    {
        $proprietaires = $this->getBaseQuery()->paginate($this->perPage);

        return view('livewire.admin.proprietaires.index', [
            'proprietaires' => $proprietaires,
        ])->layout('layouts.dashlite', ['title' => 'Propriétaires']);
    }
}
