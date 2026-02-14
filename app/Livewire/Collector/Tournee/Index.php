<?php

namespace App\Livewire\Collector\Tournee;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Tournee;
use App\Models\Caissier;
use App\Models\Collecte;
use Carbon\Carbon;

#[Layout('components.dashlite-layout')]
class Index extends Component
{
    public $tourneeEnCours = null;
    public $collectesRealisees = 0;
    public $totalCaissiers = 0;
    public $totalEncaisse = 0;
    public $totalAttendu = 0;
    public $caissierRestants = 0;

    public function mount()
    {
        $this->loadTourneeData();
    }

    public function loadTourneeData()
    {
        $collecteur = auth()->user()->collecteur;
        if (!$collecteur) return;

        // Chercher la tournée du jour (en cours ou planifiée)
        $this->tourneeEnCours = Tournee::where('collecteur_id', $collecteur->id)
            ->whereDate('date', Carbon::today())
            ->whereIn('statut', ['planifiee', 'en_cours'])
            ->with(['collectes.caissier'])
            ->first();

        if ($this->tourneeEnCours) {
            $this->totalCaissiers = $this->tourneeEnCours->collectes->count();
            $this->collectesRealisees = $this->tourneeEnCours->collectes
                ->whereIn('statut', ['reussie', 'partielle'])->count();
            $this->caissierRestants = $this->totalCaissiers - $this->collectesRealisees;
            $this->totalEncaisse = $this->tourneeEnCours->collectes->sum('montant_collecte') ?? 0;
            $this->totalAttendu = $this->tourneeEnCours->collectes->sum('montant_attendu') ?? 0;
        }
    }

    public function effectuerCollecte($caissierId)
    {
        // Logique pour effectuer une collecte - peut ouvrir un modal
        $this->dispatch('open-collecte-modal', caissierId: $caissierId);
    }

    public function terminerTournee()
    {
        if ($this->tourneeEnCours && $this->caissierRestants == 0) {
            $this->tourneeEnCours->update(['statut' => 'terminee']);
            session()->flash('success', 'Tournée terminée avec succès!');
            $this->loadTourneeData();
        }
    }

    public function signalerProbleme()
    {
        // Logique pour signaler un problème
        $this->dispatch('open-probleme-modal');
    }

    public function render()
    {
        $caissiers = collect();

        if ($this->tourneeEnCours) {
            // Récupérer les caissiers de la tournée avec leur statut de collecte
            $caissiers = $this->tourneeEnCours->collectes->map(function ($collecte) {
                $caissier = $collecte->caissier;
                if ($caissier) {
                    $caissier->collecte_faite = in_array($collecte->statut, ['reussie', 'partielle']);
                    $caissier->collecte_id = $collecte->id;
                    $caissier->montant_collecte = $collecte->montant_collecte;
                }
                return $caissier;
            })->filter();
        }

        return view('livewire.collector.tournee.index', [
            'caissiers' => $caissiers
        ]);
    }
}
