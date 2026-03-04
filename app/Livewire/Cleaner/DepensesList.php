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
        if (!$cleaner) {
            session()->flash('error', 'Profil laveur non trouvé.');
            return;
        }
        $depense = DepenseLavage::where('cleaner_id', $cleaner->id)->findOrFail($depenseId);

        $depense->delete();

        session()->flash('success', 'Dépense supprimée et solde remboursé.');
    }

    public function render()
    {
        $cleaner = auth()->user()->cleaner;

        // Si pas de profil cleaner, retourner une vue vide avec un paginator vide
        if (!$cleaner) {
            $emptyPaginator = new \Illuminate\Pagination\LengthAwarePaginator([], 0, $this->perPage);
            return view('livewire.cleaner.depenses-list', [
                'depenses' => $emptyPaginator,
                'stats' => [
                    'total_mois' => 0,
                    'total_jour' => 0,
                    'nb_depenses_mois' => 0,
                    'solde_actuel' => 0,
                ],
                'parCategorie' => [],
                'categories' => DepenseLavage::CATEGORIES,
            ]);
        }

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

    /**
     * Exporter la liste des dépenses en PDF
     */
    public function exporterPdf()
    {
        $cleaner = auth()->user()->cleaner;

        if (!$cleaner) {
            session()->flash('error', 'Profil laveur non trouvé.');
            return;
        }

        $depenses = DepenseLavage::where('cleaner_id', $cleaner->id)
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('numero_depense', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%')
                      ->orWhere('fournisseur', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filterCategorie, fn($q) => $q->where('categorie', $this->filterCategorie))
            ->when($this->dateDebut, fn($q) => $q->whereDate('date_depense', '>=', $this->dateDebut))
            ->when($this->dateFin, fn($q) => $q->whereDate('date_depense', '<=', $this->dateFin))
            ->orderBy('date_depense', 'desc')
            ->get();

        $stats = [
            'total' => $depenses->sum('montant'),
            'count' => $depenses->count(),
        ];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.liste-depenses-lavage', [
            'depenses' => $depenses,
            'stats' => $stats,
            'cleaner' => $cleaner,
            'dateDebut' => $this->dateDebut,
            'dateFin' => $this->dateFin,
            'categories' => DepenseLavage::CATEGORIES,
        ]);
        $pdf->setPaper('a4', 'portrait');

        $filename = 'liste_depenses_' . now()->format('Y-m-d_His') . '.pdf';

        return response()->streamDownload(function() use ($pdf) {
            echo $pdf->output();
        }, $filename);
    }
}

