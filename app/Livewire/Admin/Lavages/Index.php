<?php

namespace App\Livewire\Admin\Lavages;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Lavage;
use App\Models\Cleaner;
use Carbon\Carbon;

#[Layout('components.dashlite-layout')]
class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $filterCleaner = '';
    public $filterType = '';
    public $filterSource = '';
    public $dateDebut = '';
    public $dateFin = '';
    public $perPage = 20;

    protected $queryString = [
        'search' => ['except' => ''],
        'filterCleaner' => ['except' => ''],
        'filterType' => ['except' => ''],
        'filterSource' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $lavages = Lavage::with(['cleaner.user', 'moto.proprietaire.user'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('numero_lavage', 'like', '%' . $this->search . '%')
                      ->orWhere('plaque_externe', 'like', '%' . $this->search . '%')
                      ->orWhereHas('moto', function ($q2) {
                          $q2->where('plaque_immatriculation', 'like', '%' . $this->search . '%');
                      });
                });
            })
            ->when($this->filterCleaner, function ($query) {
                $query->where('cleaner_id', $this->filterCleaner);
            })
            ->when($this->filterType, function ($query) {
                $query->where('type_lavage', $this->filterType);
            })
            ->when($this->filterSource === 'interne', function ($query) {
                $query->where('is_externe', false);
            })
            ->when($this->filterSource === 'externe', function ($query) {
                $query->where('is_externe', true);
            })
            ->when($this->dateDebut, function ($query) {
                $query->whereDate('date_lavage', '>=', $this->dateDebut);
            })
            ->when($this->dateFin, function ($query) {
                $query->whereDate('date_lavage', '<=', $this->dateFin);
            })
            ->orderBy('date_lavage', 'desc')
            ->paginate($this->perPage);

        $cleaners = Cleaner::with('user')->where('is_active', true)->get();

        // Statistiques
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();

        $stats = [
            'total_jour' => Lavage::whereDate('date_lavage', $today)->count(),
            'ca_jour' => Lavage::whereDate('date_lavage', $today)->where('statut_paiement', 'payé')->sum('prix_final'),
            'part_okami_jour' => Lavage::whereDate('date_lavage', $today)->where('statut_paiement', 'payé')->sum('part_okami'),
            'ca_mois' => Lavage::whereBetween('date_lavage', [$startOfMonth, now()])->where('statut_paiement', 'payé')->sum('prix_final'),
            'part_okami_mois' => Lavage::whereBetween('date_lavage', [$startOfMonth, now()])->where('statut_paiement', 'payé')->sum('part_okami'),
        ];

        return view('livewire.admin.lavages.index', [
            'lavages' => $lavages,
            'cleaners' => $cleaners,
            'stats' => $stats,
        ]);
    }

    public function resetFilters()
    {
        $this->reset(['search', 'filterCleaner', 'filterType', 'filterSource', 'dateDebut', 'dateFin']);
        $this->resetPage();
    }
}

