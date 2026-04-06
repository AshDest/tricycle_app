<?php

namespace App\Livewire\Collector\Tournee;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Tournee;
use App\Models\Collecte;
use App\Models\TransactionMobile;

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
     * Si le dépôt a été fait via mobile money, enregistre automatiquement une transaction entrante
     */
    public function validerCollecte($collecteId)
    {
        $collecte = Collecte::where('id', $collecteId)
            ->where('tournee_id', $this->tournee->id)
            ->with('caissier.user')
            ->firstOrFail();

        // Vérifier que le caissier a déjà déposé
        if ($collecte->statut !== 'reussie' || (float)$collecte->montant_collecte <= 0) {
            session()->flash('error', 'Le caissier n\'a pas encore effectué le dépôt.');
            return;
        }

        // Vérifier que ce n'est pas déjà validé
        if ($collecte->valide_par_collecteur) {
            session()->flash('error', 'Ce dépôt a déjà été validé.');
            return;
        }

        $collecte->update([
            'valide_par_collecteur' => true,
            'valide_collecteur_at' => now(),
        ]);

        // Ajouter le montant à la caisse du collecteur
        $collecteur = auth()->user()->collecteur;
        $montant = (float) $collecte->montant_collecte;
        if ($collecteur && $montant > 0) {
            $collecteur->ajouterMontantAvecRepartition($montant);

            // Si le dépôt a été fait via mobile money, créer automatiquement une transaction entrante
            if ($collecte->mode_paiement !== 'cash' && in_array($collecte->mode_paiement, ['mpesa', 'airtel_money', 'orange_money', 'afrimoney'])) {
                $this->enregistrerTransactionMobileEntrante($collecte, $collecteur);
            }
        }

        $this->tournee->refresh();

        $modeLabel = $collecte->mode_paiement === 'cash' ? '' : ' (via ' . strtoupper(str_replace('_', ' ', $collecte->mode_paiement)) . ')';
        session()->flash('success', 'Collecte validée. ' . number_format($montant) . " FC ajoutés à votre caisse{$modeLabel}.");
    }

    /**
     * Enregistrer une transaction Mobile Money entrante (retrait/réception)
     */
    protected function enregistrerTransactionMobileEntrante(Collecte $collecte, $collecteur): ?TransactionMobile
    {
        $caissierNom = $collecte->caissier?->user?->name ?? 'Caissier';

        return TransactionMobile::create([
            'collecteur_id' => $collecteur->id,
            'type' => 'retrait', // Retrait = argent reçu
            'montant' => $collecte->montant_collecte,
            'frais' => 0,
            'montant_net' => $collecte->montant_collecte,
            'operateur' => $collecte->mode_paiement,
            'numero_telephone' => $collecte->caissier?->user?->telephone ?? '',
            'nom_beneficiaire' => $caissierNom,
            'reference_operateur' => $collecte->numero_transaction_mobile,
            'statut' => 'complete',
            'motif' => 'Collecte #' . $collecte->id . ' - Dépôt du caissier ' . $caissierNom,
            'notes' => 'Transaction automatique - Collecte validée',
            'date_transaction' => now(),
        ]);
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

