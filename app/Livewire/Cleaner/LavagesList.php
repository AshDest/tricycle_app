<?php

namespace App\Livewire\Cleaner;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Lavage;
use Carbon\Carbon;

#[Layout('components.dashlite-layout')]
class LavagesList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $filterType = '';
    public $filterStatut = '';
    public $filterSource = ''; // interne, externe
    public $dateDebut = '';
    public $dateFin = '';
    public $perPage = 15;

    protected $queryString = [
        'search' => ['except' => ''],
        'filterType' => ['except' => ''],
        'filterStatut' => ['except' => ''],
        'filterSource' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $cleaner = auth()->user()->cleaner;

        $lavages = Lavage::where('cleaner_id', $cleaner->id)
            ->with('moto.proprietaire.user')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('numero_lavage', 'like', '%' . $this->search . '%')
                      ->orWhere('plaque_externe', 'like', '%' . $this->search . '%')
                      ->orWhere('proprietaire_externe', 'like', '%' . $this->search . '%')
                      ->orWhereHas('moto', function ($q2) {
                          $q2->where('plaque_immatriculation', 'like', '%' . $this->search . '%');
                      });
                });
            })
            ->when($this->filterType, function ($query) {
                $query->where('type_lavage', $this->filterType);
            })
            ->when($this->filterStatut, function ($query) {
                $query->where('statut_paiement', $this->filterStatut);
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

        // Statistiques
        $stats = [
            'total' => Lavage::where('cleaner_id', $cleaner->id)->count(),
            'aujourdhui' => Lavage::where('cleaner_id', $cleaner->id)->whereDate('date_lavage', today())->count(),
            'ca_jour' => Lavage::where('cleaner_id', $cleaner->id)
                ->whereDate('date_lavage', today())
                ->where('statut_paiement', 'payé')
                ->sum('part_cleaner'),
        ];

        return view('livewire.cleaner.lavages-list', [
            'lavages' => $lavages,
            'stats' => $stats,
        ]);
    }

    public function resetFilters()
    {
        $this->reset(['search', 'filterType', 'filterStatut', 'filterSource', 'dateDebut', 'dateFin']);
        $this->resetPage();
    }
}

