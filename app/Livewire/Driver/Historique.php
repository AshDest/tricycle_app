<?php

namespace App\Livewire\Driver;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Versement;
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
    public $totalVerse = 0;
    public $nombreVersements = 0;
    public $versementsPartiels = 0;
    public $versementsEnRetard = 0;

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

    public function render()
    {
        $motard_id = auth()->user()->motard?->id;

        $query = Versement::where('motard_id', $motard_id)
            ->when($this->filterStatut, fn($q) => $q->where('statut', $this->filterStatut))
            ->when($this->filterPeriode, function($q) {
                switch($this->filterPeriode) {
                    case 'today': $q->whereDate('date_versement', Carbon::today()); break;
                    case 'week': $q->whereBetween('date_versement', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]); break;
                    case 'month': $q->whereMonth('date_versement', Carbon::now()->month)->whereYear('date_versement', Carbon::now()->year); break;
                    case 'year': $q->whereYear('date_versement', Carbon::now()->year); break;
                }
            })
            ->when($this->dateFrom, fn($q) => $q->whereDate('date_versement', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->whereDate('date_versement', '<=', $this->dateTo));

        // Calculer les stats sur les données filtrées
        $statsQuery = clone $query;
        $this->totalVerse = $statsQuery->sum('montant');
        $this->nombreVersements = $statsQuery->count();
        $this->versementsPartiels = (clone $query)->where('statut', 'partiellement_payé')->count();
        $this->versementsEnRetard = (clone $query)->where('statut', 'en_retard')->count();

        $versements = $query->orderBy('date_versement', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        return view('livewire.driver.historique', compact('versements'));
    }
}
