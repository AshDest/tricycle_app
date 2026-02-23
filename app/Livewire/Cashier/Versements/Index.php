<?php

namespace App\Livewire\Cashier\Versements;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Versement;
use App\Models\Motard;
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

    // Modal de complément
    public $showComplementModal = false;
    public $versementACompleter = null;
    public $montantComplement = '';
    public $montantManquant = 0;

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

    /**
     * Ouvrir le modal pour compléter un versement
     */
    public function ouvrirComplement($versementId)
    {
        $this->versementACompleter = Versement::with(['motard.user', 'moto'])->find($versementId);

        if ($this->versementACompleter) {
            $this->montantManquant = max(0, ($this->versementACompleter->montant_attendu ?? 0) - ($this->versementACompleter->montant ?? 0));
            $this->montantComplement = $this->montantManquant;
            $this->showComplementModal = true;
        }
    }

    /**
     * Fermer le modal
     */
    public function fermerComplement()
    {
        $this->showComplementModal = false;
        $this->versementACompleter = null;
        $this->montantComplement = '';
        $this->montantManquant = 0;
    }

    /**
     * Enregistrer le complément de versement
     */
    public function enregistrerComplement()
    {
        $this->validate([
            'montantComplement' => 'required|numeric|min:1',
        ], [
            'montantComplement.required' => 'Le montant est obligatoire.',
            'montantComplement.min' => 'Le montant doit être supérieur à 0.',
        ]);

        if (!$this->versementACompleter) {
            session()->flash('error', 'Versement non trouvé.');
            return;
        }

        $caissier = auth()->user()->caissier;
        $montantComplement = (float) $this->montantComplement;
        $versement = $this->versementACompleter;

        // Mettre à jour le versement existant
        $nouveauMontant = ($versement->montant ?? 0) + $montantComplement;
        $montantAttendu = $versement->montant_attendu ?? 0;

        // Calculer le nouveau statut
        if ($nouveauMontant >= $montantAttendu) {
            $nouveauStatut = 'payé';
        } else {
            $nouveauStatut = 'partiellement_payé';
        }

        // Calculer les arriérés restants pour ce versement
        $nouveauxArrieres = max(0, $montantAttendu - $nouveauMontant);

        $versement->update([
            'montant' => $nouveauMontant,
            'arrieres' => $nouveauxArrieres,
            'statut' => $nouveauStatut,
            'notes' => ($versement->notes ? $versement->notes . "\n" : '') .
                       "[Complément de " . number_format($montantComplement) . " FC le " . now()->format('d/m/Y H:i') . "]",
        ]);

        // Mettre à jour le solde du caissier
        $caissier->increment('solde_actuel', $montantComplement);

        // Fermer le modal
        $this->fermerComplement();

        session()->flash('success', 'Complément de ' . number_format($montantComplement) . ' FC enregistré avec succès.');
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
