<?php

namespace App\Livewire\Collector;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Tournee;
use App\Models\Collecte;
use Carbon\Carbon;

#[Layout('components.dashlite-layout')]
class Historique extends Component
{
    use WithPagination;

    public $filterStatut = '';
    public $filterPeriode = '';
    public $dateFrom = '';
    public $dateTo = '';
    public $perPage = 15;

    // Stats
    public $totalCollecte = 0;
    public $nombreTournees = 0;
    public $nombreCollectes = 0;
    public $moyenneParTournee = 0;

    protected $queryString = ['filterStatut', 'filterPeriode', 'dateFrom', 'dateTo'];

    public function updatingFilterStatut() { $this->resetPage(); }
    public function updatingFilterPeriode() { $this->resetPage(); }
    public function updatingDateFrom() { $this->resetPage(); }
    public function updatingDateTo() { $this->resetPage(); }

    public function resetFilters()
    {
        $this->reset(['filterStatut', 'filterPeriode', 'dateFrom', 'dateTo']);
        $this->resetPage();
    }

    public function voirDetails($tourneeId)
    {
        // Peut ouvrir un modal avec les détails de la tournée
    }

    public function render()
    {
        $collecteur = auth()->user()->collecteur;
        $collecteur_id = $collecteur?->id;

        $query = Tournee::withCount('collectes')
            ->withSum('collectes', 'montant_collecte')
            ->where('collecteur_id', $collecteur_id)
            ->when($this->filterStatut, fn($q) => $q->where('statut', $this->filterStatut))
            ->when($this->dateFrom, fn($q) => $q->whereDate('date', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->whereDate('date', '<=', $this->dateTo))
            ->when($this->filterPeriode, function($q) {
                switch($this->filterPeriode) {
                    case 'week': $q->whereBetween('date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]); break;
                    case 'month': $q->whereMonth('date', Carbon::now()->month)->whereYear('date', Carbon::now()->year); break;
                    case 'year': $q->whereYear('date', Carbon::now()->year); break;
                }
            });

        // Calculer les stats
        $this->nombreTournees = (clone $query)->count();
        $this->totalCollecte = (clone $query)->get()->sum('collectes_sum_montant_collecte') ?? 0;

        // Nombre total de collectes
        $tourneeIds = (clone $query)->pluck('id');
        $this->nombreCollectes = Collecte::whereIn('tournee_id', $tourneeIds)->count();

        // Moyenne par tournée
        $this->moyenneParTournee = $this->nombreTournees > 0 ? round($this->totalCollecte / $this->nombreTournees) : 0;

        $tournees = $query->orderBy('date', 'desc')
            ->paginate($this->perPage);

        // Ajouter le total_collecte pour chaque tournée
        $tournees->getCollection()->transform(function ($tournee) {
            $tournee->total_collecte = $tournee->collectes_sum_montant_collecte ?? 0;
            return $tournee;
        });

        return view('livewire.collector.historique', compact('tournees'));
    }
}
