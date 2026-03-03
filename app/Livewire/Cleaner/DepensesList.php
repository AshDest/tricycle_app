<?php

namespace App\Livewire\Cleaner;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\DepenseLavage;
use Carbon\Carbon;

#[Layout('components.dashlite-layout')]
class DepensesList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $filterCategorie = '';
    public $dateDebut = '';
    public $dateFin = '';
    public $perPage = 15;

    protected $queryString = [
        'search' => ['except' => ''],
        'filterCategorie' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function supprimer($depenseId)
    {
        $cleaner = auth()->user()->cleaner;
        $depense = DepenseLavage::where('cleaner_id', $cleaner->id)->findOrFail($depenseId);

        $depense->delete();

        session()->flash('success', 'Dépense supprimée et solde remboursé.');
    }

    public function render()
    {
        $cleaner = auth()->user()->cleaner;

        $depenses = DepenseLavage::where('cleaner_id', $cleaner->id)
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('numero_depense', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%')
                      ->orWhere('fournisseur', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filterCategorie, function ($query) {
                $query->where('categorie', $this->filterCategorie);
            })
            ->when($this->dateDebut, function ($query) {
                $query->whereDate('date_depense', '>=', $this->dateDebut);
            })
            ->when($this->dateFin, function ($query) {
                $query->whereDate('date_depense', '<=', $this->dateFin);
            })
            ->orderBy('date_depense', 'desc')
            ->paginate($this->perPage);

        // Statistiques
        $stats = [
            'total_mois' => DepenseLavage::where('cleaner_id', $cleaner->id)->duMois()->sum('montant'),
            'total_jour' => DepenseLavage::where('cleaner_id', $cleaner->id)->aujourdhui()->sum('montant'),
            'nb_depenses_mois' => DepenseLavage::where('cleaner_id', $cleaner->id)->duMois()->count(),
            'solde_actuel' => $cleaner->solde_actuel,
        ];

        // Dépenses par catégorie ce mois
        $parCategorie = DepenseLavage::where('cleaner_id', $cleaner->id)
            ->duMois()
            ->selectRaw('categorie, SUM(montant) as total')
            ->groupBy('categorie')
            ->pluck('total', 'categorie')
            ->toArray();

        return view('livewire.cleaner.depenses-list', [
            'depenses' => $depenses,
            'stats' => $stats,
            'parCategorie' => $parCategorie,
            'categories' => DepenseLavage::CATEGORIES,
        ]);
    }

    public function resetFilters()
    {
        $this->reset(['search', 'filterCategorie', 'dateDebut', 'dateFin']);
        $this->resetPage();
    }
}

