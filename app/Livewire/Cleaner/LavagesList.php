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

        // Si pas de profil cleaner, retourner une vue vide avec un paginator vide
        if (!$cleaner) {
            $emptyPaginator = new \Illuminate\Pagination\LengthAwarePaginator([], 0, $this->perPage);
            return view('livewire.cleaner.lavages-list', [
                'lavages' => $emptyPaginator,
                'stats' => [
                    'total' => 0,
                    'aujourdhui' => 0,
                    'ca_jour' => 0,
                ],
            ]);
        }

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

    public function telechargerRecu($lavageId)
    {
        $lavage = Lavage::with(['cleaner.user', 'moto.proprietaire.user'])->findOrFail($lavageId);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.recu-lavage', compact('lavage'));
        $pdf->setPaper([0, 0, 204, 400], 'portrait'); // 72mm = 204 points

        $filename = 'recu_lavage_' . $lavage->numero_lavage . '.pdf';

        return response()->streamDownload(function() use ($pdf) {
            echo $pdf->output();
        }, $filename);
    }

    /**
     * Exporter la liste des lavages en PDF
     */
    public function exporterPdf()
    {
        $cleaner = auth()->user()->cleaner;

        if (!$cleaner) {
            session()->flash('error', 'Profil laveur non trouvé.');
            return;
        }

        $lavages = Lavage::where('cleaner_id', $cleaner->id)
            ->with('moto.proprietaire.user')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('numero_lavage', 'like', '%' . $this->search . '%')
                      ->orWhere('plaque_externe', 'like', '%' . $this->search . '%')
                      ->orWhere('proprietaire_externe', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filterType, fn($q) => $q->where('type_lavage', $this->filterType))
            ->when($this->filterStatut, fn($q) => $q->where('statut_paiement', $this->filterStatut))
            ->when($this->filterSource === 'interne', fn($q) => $q->where('is_externe', false))
            ->when($this->filterSource === 'externe', fn($q) => $q->where('is_externe', true))
            ->when($this->dateDebut, fn($q) => $q->whereDate('date_lavage', '>=', $this->dateDebut))
            ->when($this->dateFin, fn($q) => $q->whereDate('date_lavage', '<=', $this->dateFin))
            ->orderBy('date_lavage', 'desc')
            ->get();

        $stats = [
            'total' => $lavages->count(),
            'total_montant' => $lavages->sum('montant'),
            'part_cleaner' => $lavages->sum('part_cleaner'),
            'part_okami' => $lavages->sum('part_okami'),
        ];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.liste-lavages', [
            'lavages' => $lavages,
            'stats' => $stats,
            'cleaner' => $cleaner,
            'dateDebut' => $this->dateDebut,
            'dateFin' => $this->dateFin,
        ]);
        $pdf->setPaper('a4', 'landscape');

        $filename = 'liste_lavages_' . now()->format('Y-m-d_His') . '.pdf';

        return response()->streamDownload(function() use ($pdf) {
            echo $pdf->output();
        }, $filename);
    }
}

