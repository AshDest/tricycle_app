<?php

namespace App\Livewire\Collector\Collectes;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Collecte;
use App\Models\Tournee;
use Carbon\Carbon;

#[Layout('components.dashlite-layout')]
class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $filterStatut = '';
    public $filterPeriode = '';
    public $filterDate = '';
    public $perPage = 15;

    // Stats
    public $totalCollecte = 0;
    public $nombreCollectes = 0;
    public $collectesPartielles = 0;
    public $collectesEnLitige = 0;

    protected $queryString = ['search', 'filterStatut', 'filterPeriode', 'filterDate'];

    public function updatingSearch() { $this->resetPage(); }
    public function updatingFilterStatut() { $this->resetPage(); }
    public function updatingFilterPeriode() { $this->resetPage(); }
    public function updatingFilterDate() { $this->resetPage(); }

    public function resetFilters()
    {
        $this->reset(['search', 'filterStatut', 'filterPeriode', 'filterDate']);
        $this->resetPage();
    }

    public function voirDetails($collecteId)
    {
        // Peut ouvrir un modal ou rediriger vers une page de détail
    }

    public function render()
    {
        $collecteur = auth()->user()->collecteur;
        $collecteur_id = $collecteur?->id;

        // Récupérer les IDs des tournées du collecteur
        $tourneeIds = collect();
        if ($collecteur_id) {
            $tourneeIds = Tournee::where('collecteur_id', $collecteur_id)->pluck('id');
        }

        // Query de base
        $query = Collecte::with(['caissier.user', 'tournee'])
            ->whereIn('tournee_id', $tourneeIds)
            ->when($this->search, function ($q) {
                $q->whereHas('caissier', fn($q2) => $q2->where('nom_point_collecte', 'like', '%'.$this->search.'%'));
            })
            ->when($this->filterStatut, fn($q) => $q->where('statut', $this->filterStatut))
            ->when($this->filterDate, fn($q) => $q->whereDate('created_at', $this->filterDate))
            ->when($this->filterPeriode, function($q) {
                switch($this->filterPeriode) {
                    case 'today': $q->whereDate('created_at', Carbon::today()); break;
                    case 'week': $q->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]); break;
                    case 'month': $q->whereMonth('created_at', Carbon::now()->month)->whereYear('created_at', Carbon::now()->year); break;
                }
            });

        // Calculer les stats
        $statsQuery = clone $query;
        $this->totalCollecte = $statsQuery->sum('montant_collecte') ?? 0;
        $this->nombreCollectes = (clone $query)->count();
        $this->collectesPartielles = (clone $query)->where('statut', 'partielle')->count();
        $this->collectesEnLitige = (clone $query)->where('statut', 'en_litige')->count();

        $collectes = $query->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        return view('livewire.collector.collectes.index', compact('collectes'));
    }
}
