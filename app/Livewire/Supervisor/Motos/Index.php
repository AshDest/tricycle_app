<?php

namespace App\Livewire\Supervisor\Motos;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Moto;
use App\Models\Proprietaire;

#[Layout('components.dashlite-layout')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterStatut = '';
    public string $filterProprietaire = '';
    public string $dateDebut = '';
    public string $dateFin = '';
    public int $perPage = 15;

    public ?int $confirmingDelete = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'filterStatut' => ['except' => ''],
        'filterProprietaire' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->reset(['search', 'filterStatut', 'filterProprietaire', 'dateDebut', 'dateFin']);
        $this->resetPage();
    }

    public function toggleStatut(int $id)
    {
        $moto = Moto::findOrFail($id);
        $newStatut = $moto->statut === 'actif' ? 'inactif' : 'actif';
        $moto->update(['statut' => $newStatut]);
        session()->flash('success', 'Statut de la moto mis à jour.');
    }

    public function confirmDelete(int $id)
    {
        $this->confirmingDelete = $id;
    }

    public function cancelDelete()
    {
        $this->confirmingDelete = null;
    }

    public function delete(int $id)
    {
        $moto = Moto::findOrFail($id);

        if ($moto->motardActuel) {
            session()->flash('error', 'Impossible de supprimer cette moto car elle est assignée à un motard.');
            $this->confirmingDelete = null;
            return;
        }

        $moto->delete();
        session()->flash('success', 'Moto supprimée avec succès.');
        $this->confirmingDelete = null;
    }

    public function export(string $format = 'csv')
    {
        $filename = 'motos_' . now()->format('Y-m-d_His');

        return response()->streamDownload(function () {
            $this->exportCsv($this->getFilteredQuery()->get());
        }, $filename . '.csv');
    }

    protected function exportCsv($motos)
    {
        $handle = fopen('php://output', 'w');

        fputcsv($handle, [
            'ID', 'Plaque', 'Marque', 'Modèle', 'Châssis',
            'Propriétaire', 'Motard Assigné', 'Statut', 'Date Création'
        ]);

        foreach ($motos as $moto) {
            fputcsv($handle, [
                $moto->id,
                $moto->plaque_immatriculation ?? 'N/A',
                $moto->marque ?? 'N/A',
                $moto->modele ?? 'N/A',
                $moto->numero_chassis ?? 'N/A',
                $moto->proprietaire->user->name ?? 'N/A',
                $moto->motardActuel->user->name ?? 'Aucun',
                $moto->statut ?? 'N/A',
                $moto->created_at->format('d/m/Y H:i'),
            ]);
        }

        fclose($handle);
    }

    protected function getFilteredQuery()
    {
        return Moto::with(['proprietaire.user', 'motardActuel.user'])
            ->when($this->search, function ($q) {
                $q->where(function ($query) {
                    $query->where('plaque_immatriculation', 'like', '%' . $this->search . '%')
                          ->orWhere('numero_chassis', 'like', '%' . $this->search . '%')
                          ->orWhere('marque', 'like', '%' . $this->search . '%')
                          ->orWhereHas('proprietaire.user', function ($q2) {
                              $q2->where('name', 'like', '%' . $this->search . '%');
                          });
                });
            })
            ->when($this->filterStatut, function ($q) {
                $q->where('statut', $this->filterStatut);
            })
            ->when($this->filterProprietaire, function ($q) {
                $q->where('proprietaire_id', $this->filterProprietaire);
            })
            ->when($this->dateDebut, function ($q) {
                $q->whereDate('created_at', '>=', $this->dateDebut);
            })
            ->when($this->dateFin, function ($q) {
                $q->whereDate('created_at', '<=', $this->dateFin);
            })
            ->orderBy('created_at', 'desc');
    }

    public function render()
    {
        $motos = $this->getFilteredQuery()->paginate($this->perPage);
        $proprietaires = Proprietaire::with('user')->get();

        $stats = [
            'total' => Moto::count(),
            'actives' => Moto::where('statut', 'actif')->count(),
            'inactives' => Moto::where('statut', 'inactif')->count(),
            'assignees' => Moto::whereHas('motardActuel')->count(),
        ];

        return view('livewire.supervisor.motos.index', compact('motos', 'proprietaires', 'stats'));
    }
}

