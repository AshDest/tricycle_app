<?php

namespace App\Livewire\Owner\Motos;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Moto;
use App\Models\Versement;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

#[Layout('components.dashlite-layout')]
class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 15;

    // Modal détails
    public $showModal = false;
    public $motoSelectionnee = null;
    public $versementsMoto = [];
    public $statsMoto = [];

    protected $queryString = ['search'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    protected function getBaseQuery()
    {
        return Moto::where('proprietaire_id', auth()->user()->proprietaire?->id ?? null)
            ->with(['motard.user'])
            ->when($this->search, function ($q) {
                $q->where('plaque_immatriculation', 'like', '%' . $this->search . '%')
                  ->orWhere('numero_matricule', 'like', '%' . $this->search . '%');
            })
            ->orderBy('created_at', 'desc');
    }

    /**
     * Afficher les détails d'une moto
     */
    public function voirDetails($motoId)
    {
        $this->motoSelectionnee = Moto::with(['motard.user', 'proprietaire.user'])->find($motoId);

        if ($this->motoSelectionnee) {
            // Charger les derniers versements
            $this->versementsMoto = Versement::where('moto_id', $motoId)
                ->with(['motard.user'])
                ->orderBy('date_versement', 'desc')
                ->take(10)
                ->get();

            // Calculer les stats
            $this->statsMoto = [
                'totalVersements' => Versement::where('moto_id', $motoId)->sum('montant'),
                'versementsMois' => Versement::where('moto_id', $motoId)
                    ->whereMonth('date_versement', now()->month)
                    ->whereYear('date_versement', now()->year)
                    ->sum('montant'),
                'arrieres' => Versement::where('moto_id', $motoId)
                    ->whereRaw('montant < montant_attendu')
                    ->selectRaw('SUM(montant_attendu - montant) as total')
                    ->value('total') ?? 0,
                'nbVersements' => Versement::where('moto_id', $motoId)->count(),
            ];

            $this->showModal = true;
        }
    }

    /**
     * Fermer le modal
     */
    public function fermerModal()
    {
        $this->showModal = false;
        $this->motoSelectionnee = null;
        $this->versementsMoto = [];
        $this->statsMoto = [];
    }

    public function exportPdf()
    {
        $motos = $this->getBaseQuery()->get();

        $stats = [
            'total' => $motos->count(),
            'actives' => $motos->where('statut', 'actif')->count(),
            'inactives' => $motos->where('statut', 'inactif')->count(),
            'en_maintenance' => $motos->where('statut', 'en_maintenance')->count(),
        ];

        $pdf = Pdf::loadView('pdf.lists.motos', [
            'motos' => $motos,
            'stats' => $stats,
            'title' => 'Mes Motos - Propriétaire',
            'subtitle' => 'Exporté le ' . Carbon::now()->format('d/m/Y à H:i'),
            'filtres' => [
                'search' => $this->search,
            ],
        ]);

        $pdf->setPaper('A4', 'landscape');

        return response()->streamDownload(function() use ($pdf) {
            echo $pdf->output();
        }, 'mes_motos_' . Carbon::now()->format('Y-m-d') . '.pdf');
    }

    public function render()
    {
        $proprietaire = auth()->user()->proprietaire;
        $motos = $this->getBaseQuery()->paginate($this->perPage);

        // Stats globales
        $allMotos = Moto::where('proprietaire_id', $proprietaire?->id)->get();
        $totalMotos = $allMotos->count();
        $motosActives = $allMotos->where('statut', 'actif')->count();
        $motosEnMaintenance = $allMotos->whereIn('statut', ['maintenance', 'en_maintenance'])->count();

        // Revenus du mois
        $motoIds = $allMotos->pluck('id');
        $revenusTotal = Versement::whereIn('moto_id', $motoIds)
            ->whereMonth('date_versement', now()->month)
            ->whereYear('date_versement', now()->year)
            ->sum('montant');

        return view('livewire.owner.motos.index', compact(
            'motos',
            'totalMotos',
            'motosActives',
            'motosEnMaintenance',
            'revenusTotal'
        ));
    }
}
