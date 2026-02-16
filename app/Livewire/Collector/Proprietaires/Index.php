<?php

namespace App\Livewire\Collector\Proprietaires;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Proprietaire;
use App\Services\PaymentService;

/**
 * Vue des propriétaires avec leur solde disponible
 * Permet au collecteur de voir le solde de chaque propriétaire
 * avant de traiter une demande de paiement
 */
#[Layout('components.dashlite-layout')]
class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $filterAvecSolde = false;
    public $perPage = 20;

    protected $queryString = ['search', 'filterAvecSolde'];

    public function updatingSearch() { $this->resetPage(); }
    public function updatingFilterAvecSolde() { $this->resetPage(); }

    public function render()
    {
        $paymentService = new PaymentService();

        $query = Proprietaire::with(['user', 'motos'])
            ->when($this->search, function($q) {
                $q->whereHas('user', fn($q2) => $q2->where('name', 'like', '%'.$this->search.'%'))
                  ->orWhere('raison_sociale', 'like', '%'.$this->search.'%')
                  ->orWhere('telephone', 'like', '%'.$this->search.'%');
            });

        $proprietaires = $query->get()->map(function ($proprietaire) use ($paymentService) {
            $stats = $paymentService->getStatsProprietaire($proprietaire);
            $proprietaire->solde_disponible = $stats['solde_disponible'];
            $proprietaire->total_versements = $stats['total_versements_payes'];
            $proprietaire->total_paiements = $stats['total_paiements_recus'];
            $proprietaire->motos_actives = $stats['motos_actives'];
            return $proprietaire;
        });

        // Filtrer par solde si demandé
        if ($this->filterAvecSolde) {
            $proprietaires = $proprietaires->filter(fn($p) => $p->solde_disponible > 0);
        }

        // Trier par solde décroissant
        $proprietaires = $proprietaires->sortByDesc('solde_disponible')->values();

        // Stats globales
        $totalSoldeDisponible = $proprietaires->sum('solde_disponible');
        $proprietairesAvecSolde = $proprietaires->filter(fn($p) => $p->solde_disponible > 0)->count();

        return view('livewire.collector.proprietaires.index', [
            'proprietaires' => $proprietaires,
            'totalSoldeDisponible' => $totalSoldeDisponible,
            'proprietairesAvecSolde' => $proprietairesAvecSolde,
        ]);
    }
}
