<?php

namespace App\Livewire\Collector\Tournee;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Tournee;
use App\Models\Collecte;

#[Layout('components.dashlite-layout')]
class Show extends Component
{
    public Tournee $tournee;

    public function mount(Tournee $tournee)
    {
        $collecteur = auth()->user()->collecteur;

        // Vérifier que c'est bien la tournée du collecteur connecté
        if ($tournee->collecteur_id !== $collecteur->id) {
            abort(403, 'Accès non autorisé à cette tournée.');
        }

        $this->tournee = $tournee->load(['collectes.caissier.user', 'collecteur.user']);
    }

    /**
     * Valider la réception d'un dépôt
     */
    public function validerCollecte($collecteId)
    {
        $collecte = Collecte::where('id', $collecteId)
            ->where('tournee_id', $this->tournee->id)
            ->firstOrFail();

        // Vérifier que le caissier a déjà déposé
        if ($collecte->statut !== 'reussie' || $collecte->montant_collecte <= 0) {
            session()->flash('error', 'Le caissier n\'a pas encore effectué le dépôt.');
            return;
        }

        $collecte->update([
            'valide_par_collecteur' => true,
            'valide_collecteur_at' => now(),
        ]);

        // Ajouter le montant à la caisse du collecteur
        $collecteur = auth()->user()->collecteur;
        if ($collecteur) {
            $collecteur->increment('solde_caisse', $collecte->montant_collecte);
        }

        $this->tournee->refresh();

        session()->flash('success', 'Collecte validée. ' . number_format($collecte->montant_collecte) . ' FC ajoutés à votre caisse.');
    }

    /**
     * Signaler un problème sur une collecte
     */
    public function signalerProbleme($collecteId, $motif = 'Problème signalé')
    {
        $collecte = Collecte::where('id', $collecteId)
            ->where('tournee_id', $this->tournee->id)
            ->firstOrFail();

        $collecte->update([
            'statut' => 'en_litige',
            'notes_collecteur' => $motif,
        ]);

        $this->tournee->refresh();

        session()->flash('warning', 'Problème signalé pour cette collecte.');
    }

    public function render()
    {
        // Stats de la tournée
        $totalCollectes = $this->tournee->collectes->count();
        $collectesEffectuees = $this->tournee->collectes->where('statut', 'reussie')->count();
        $collectesValidees = $this->tournee->collectes->where('valide_par_collecteur', true)->count();
        $totalEncaisse = $this->tournee->collectes->sum('montant_collecte');
        $totalAttendu = $this->tournee->collectes->sum('montant_attendu');

        return view('livewire.collector.tournee.show', compact(
            'totalCollectes',
            'collectesEffectuees',
            'collectesValidees',
            'totalEncaisse',
            'totalAttendu'
        ));
    }
}

