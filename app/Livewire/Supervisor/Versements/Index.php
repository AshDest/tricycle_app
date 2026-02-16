<?php

namespace App\Livewire\Supervisor\Versements;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Versement;
use App\Models\Motard;
use Carbon\Carbon;

#[Layout('components.dashlite-layout')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterStatut = '';
    public string $filterMode = '';
    public string $dateDebut = '';
    public string $dateFin = '';
    public int $perPage = 15;

    protected $queryString = [
        'search' => ['except' => ''],
        'filterStatut' => ['except' => ''],
        'filterMode' => ['except' => ''],
    ];

    public function mount()
    {
        // Par défaut, afficher les versements du mois en cours
        $this->dateDebut = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->dateFin = Carbon::now()->endOfMonth()->format('Y-m-d');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->reset(['search', 'filterStatut', 'filterMode']);
        $this->dateDebut = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->dateFin = Carbon::now()->endOfMonth()->format('Y-m-d');
        $this->resetPage();
    }

    public function export(string $format = 'csv')
    {
        $filename = 'versements_' . now()->format('Y-m-d_His');

        return response()->streamDownload(function () {
            $this->exportCsv($this->getFilteredQuery()->get());
        }, $filename . '.csv');
    }

    protected function exportCsv($versements)
    {
        $handle = fopen('php://output', 'w');

        fputcsv($handle, [
            'ID', 'Date', 'Motard', 'Identifiant', 'Moto',
            'Montant Attendu', 'Montant Versé', 'Mode', 'Statut', 'Caissier'
        ]);

        foreach ($versements as $versement) {
            fputcsv($handle, [
                $versement->id,
                $versement->date_versement?->format('d/m/Y'),
                $versement->motard->user->name ?? 'N/A',
                $versement->motard->numero_identifiant ?? 'N/A',
                $versement->moto->plaque_immatriculation ?? 'N/A',
                $versement->montant_attendu ?? 0,
                $versement->montant ?? 0,
                $versement->mode_paiement ?? 'N/A',
                $versement->statut ?? 'N/A',
                $versement->caissier->user->name ?? 'N/A',
            ]);
        }

        fclose($handle);
    }

    protected function getFilteredQuery()
    {
        return Versement::with(['motard.user', 'moto', 'caissier.user'])
            ->when($this->search, function ($q) {
                $q->where(function ($query) {
                    $query->whereHas('motard.user', function ($q2) {
                        $q2->where('name', 'like', '%' . $this->search . '%');
                    })
                    ->orWhereHas('motard', function ($q2) {
                        $q2->where('numero_identifiant', 'like', '%' . $this->search . '%');
                    })
                    ->orWhereHas('moto', function ($q2) {
                        $q2->where('plaque_immatriculation', 'like', '%' . $this->search . '%');
                    });
                });
            })
            ->when($this->filterStatut, function ($q) {
                $q->where('statut', $this->filterStatut);
            })
            ->when($this->filterMode, function ($q) {
                $q->where('mode_paiement', $this->filterMode);
            })
            ->when($this->dateDebut, function ($q) {
                $q->whereDate('date_versement', '>=', $this->dateDebut);
            })
            ->when($this->dateFin, function ($q) {
                $q->whereDate('date_versement', '<=', $this->dateFin);
            })
            ->orderBy('date_versement', 'desc')
            ->orderBy('created_at', 'desc');
    }

    public function render()
    {
        $versements = $this->getFilteredQuery()->paginate($this->perPage);

        // Statistiques sur la période filtrée
        $query = $this->getFilteredQuery();
        $stats = [
            'total' => (clone $query)->count(),
            'totalMontant' => (clone $query)->sum('montant'),
            'totalAttendu' => (clone $query)->sum('montant_attendu'),
            'payes' => (clone $query)->where('statut', 'paye')->count(),
            'enRetard' => (clone $query)->where('statut', 'en_retard')->count(),
            'enAttente' => (clone $query)->where('statut', 'en_attente')->count(),
        ];

        $stats['tauxRecouvrement'] = $stats['totalAttendu'] > 0
            ? round(($stats['totalMontant'] / $stats['totalAttendu']) * 100, 1)
            : 0;

        return view('livewire.supervisor.versements.index', compact('versements', 'stats'));
    }
}
