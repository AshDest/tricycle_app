<?php

namespace App\Livewire\Cashier\Versements;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Versement;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

#[Layout('components.dashlite-layout')]
class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $filterStatut = '';
    public $filterMode = '';
    public $filterDate = '';
    public $perPage = 15;

    // Stats
    public $totalAujourdhui = 0;
    public $nombreVersementsJour = 0;
    public $soldeEnCaisse = 0;
    public $motardsServisJour = 0;

    protected $queryString = ['search', 'filterStatut', 'filterMode', 'filterDate'];

    public function updatingSearch() { $this->resetPage(); }
    public function updatingFilterStatut() { $this->resetPage(); }
    public function updatingFilterMode() { $this->resetPage(); }
    public function updatingFilterDate() { $this->resetPage(); }

    public function resetFilters()
    {
        $this->reset(['search', 'filterStatut', 'filterMode', 'filterDate']);
        $this->resetPage();
    }

    public function voirDetails($versementId)
    {
        // Peut ouvrir un modal ou rediriger
    }

    /**
     * Télécharger le reçu d'un versement
     */
    public function telechargerRecu($versementId)
    {
        $versement = Versement::with(['motard.user', 'moto', 'caissier.user'])->findOrFail($versementId);

        $pdf = Pdf::loadView('pdf.recu-versement', compact('versement'));

        // Dimensions d'un petit reçu (80mm x 200mm environ)
        $pdf->setPaper([0, 0, 226.77, 566.93], 'portrait'); // 80mm x 200mm en points

        $filename = 'recu_versement_' . $versement->id . '_' . now()->format('YmdHis') . '.pdf';

        return response()->streamDownload(function() use ($pdf) {
            echo $pdf->output();
        }, $filename);
    }

    public function render()
    {
        $caissier = auth()->user()->caissier;
        $caissier_id = $caissier?->id;
        $today = Carbon::today();

        // Calculer les stats du jour
        if ($caissier_id) {
            $versementsJour = Versement::where('caissier_id', $caissier_id)
                ->whereDate('date_versement', $today);

            $this->totalAujourdhui = (clone $versementsJour)->sum('montant');
            $this->nombreVersementsJour = (clone $versementsJour)->count();
            $this->motardsServisJour = (clone $versementsJour)->distinct('motard_id')->count('motard_id');
            $this->soldeEnCaisse = $caissier->solde_actuel ?? 0;
        }

        // Query principale avec filtres
        $versements = Versement::with(['motard.user', 'moto'])
            ->where('caissier_id', $caissier_id)
            ->when($this->search, function ($q) {
                $q->whereHas('motard.user', fn($q2) => $q2->where('name', 'like', '%'.$this->search.'%'))
                  ->orWhereHas('moto', fn($q2) => $q2->where('plaque_immatriculation', 'like', '%'.$this->search.'%'));
            })
            ->when($this->filterStatut, fn($q) => $q->where('statut', $this->filterStatut))
            ->when($this->filterMode, fn($q) => $q->where('mode_paiement', $this->filterMode))
            ->when($this->filterDate, fn($q) => $q->whereDate('date_versement', $this->filterDate))
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        return view('livewire.cashier.versements.index', compact('versements'));
    }
}
