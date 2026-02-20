<?php

namespace App\Livewire\Collector\Depots;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Collecte;
use App\Models\Caissier;
use Carbon\Carbon;

/**
 * Gestion des dépôts reçus des caissiers
 * Le caissier dépose une somme au collecteur pendant la tournée
 * Le collecteur valide la réception
 */
#[Layout('components.dashlite-layout')]
class Index extends Component
{
    use WithPagination;

    public $filterStatut = '';
    public $filterDate = '';
    public $search = '';
    public $perPage = 15;

    // Stats
    public $totalAValider = 0;
    public $totalValide = 0;
    public $montantTotal = 0;

    protected $queryString = ['filterStatut', 'filterDate', 'search'];

    public function updatingSearch() { $this->resetPage(); }
    public function updatingFilterStatut() { $this->resetPage(); }
    public function updatingFilterDate() { $this->resetPage(); }

    public function mount()
    {
        $this->filterDate = Carbon::today()->format('Y-m-d');
    }

    /**
     * Valider la réception d'un dépôt
     */
    public function validerReception($collecteId)
    {
        $collecte = Collecte::findOrFail($collecteId);
        $collecteur = auth()->user()->collecteur;

        // Vérifier que le dépôt n'est pas déjà validé
        if ($collecte->valide_par_collecteur) {
            session()->flash('error', 'Ce dépôt a déjà été validé.');
            return;
        }

        $collecte->update([
            'valide_par_collecteur' => true,
            'valide_collecteur_at' => now(),
            'statut' => 'reussie',
        ]);

        // Ajouter le montant à la caisse du collecteur
        if ($collecteur) {
            $collecteur->increment('solde_caisse', $collecte->montant_collecte);
        }

        session()->flash('success', 'Dépôt de ' . number_format($collecte->montant_collecte) . ' FC validé et ajouté à votre caisse.');
    }

    /**
     * Signaler un problème avec un dépôt
     */
    public function signalerProbleme($collecteId, $motif = 'Écart constaté')
    {
        $collecte = Collecte::findOrFail($collecteId);

        $collecte->update([
            'statut' => 'en_litige',
            'notes_collecteur' => $motif,
        ]);

        session()->flash('warning', 'Dépôt signalé comme problématique.');
    }

    public function render()
    {
        $collecteur = auth()->user()->collecteur;
        $collecteurId = $collecteur?->id;

        // Collectes (dépôts) à valider
        $query = Collecte::with(['caissier.user', 'tournee'])
            ->whereHas('tournee', function($q) use ($collecteurId) {
                $q->where('collecteur_id', $collecteurId);
            })
            ->when($this->filterDate, fn($q) => $q->whereDate('created_at', $this->filterDate))
            ->when($this->filterStatut === 'a_valider', fn($q) => $q->where('valide_par_collecteur', false))
            ->when($this->filterStatut === 'valide', fn($q) => $q->where('valide_par_collecteur', true))
            ->when($this->filterStatut === 'litige', fn($q) => $q->where('statut', 'en_litige'))
            ->when($this->search, function($q) {
                $q->whereHas('caissier.user', fn($q2) => $q2->where('name', 'like', '%'.$this->search.'%'));
            });

        // Stats
        $this->totalAValider = (clone $query)->where('valide_par_collecteur', false)->count();
        $this->totalValide = (clone $query)->where('valide_par_collecteur', true)->count();
        $this->montantTotal = (clone $query)->sum('montant_collecte');

        $soldeCaisse = $collecteur?->solde_caisse ?? 0;

        $collectes = $query->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        return view('livewire.collector.depots.index', [
            'collectes' => $collectes,
            'soldeCaisse' => $soldeCaisse,
        ]);
    }
}
