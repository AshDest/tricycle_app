<?php

namespace App\Livewire\Supervisor\Proprietaires;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Proprietaire;

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

    public function render()
    {
        $proprietaires = Proprietaire::query()
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
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.supervisor.proprietaires.index', [
            'proprietaires' => $proprietaires,
        ]);
    }
}

