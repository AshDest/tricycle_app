<?php

namespace App\Livewire\Cashier;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Collecte;
use App\Models\Tournee;
use App\Models\Collecteur;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Dépôt d'argent du caissier auprès du collecteur
 * Le caissier voit les tournées confirmées où il est inclus et peut déposer l'argent
 */
#[Layout('components.dashlite-layout')]
class Depot extends Component
{
    // Formulaire
    public $montant = '';
    public $collecte_id = '';
    public $notes = '';

    // Données
    public $soldeActuel = 0;
    public $caissier = null;
    public $collecteEnCours = null;

    // Message
    public $message = '';
    public $messageType = 'info';

    // Modal
    public $showModal = false;

    protected $rules = [
        'montant' => 'required|numeric|min:1',
        'notes' => 'nullable|string|max:500',
    ];

    protected $messages = [
        'montant.required' => 'Le montant est obligatoire.',
        'montant.min' => 'Le montant doit être supérieur à 0.',
    ];

    public function mount()
    {
        $this->caissier = auth()->user()->caissier;

        if ($this->caissier) {
            $this->soldeActuel = $this->caissier->solde_actuel ?? 0;
        }
    }

    /**
     * Ouvrir le modal pour faire un dépôt
     */
    public function ouvrirDepot($collecteId)
    {
        $this->collecteEnCours = Collecte::with(['tournee.collecteur.user'])
            ->where('id', $collecteId)
            ->where('caissier_id', $this->caissier->id)
            ->firstOrFail();

        $this->montant = $this->soldeActuel;
        $this->notes = '';
        $this->showModal = true;
    }

    public function fermerModal()
    {
        $this->showModal = false;
        $this->collecteEnCours = null;
        $this->reset(['montant', 'notes']);
    }

    /**
     * Effectuer le dépôt
     */
    public function effectuerDepot()
    {
        $this->validate();

        if (!$this->caissier) {
            $this->message = 'Erreur: Profil caissier non trouvé.';
            $this->messageType = 'danger';
            return;
        }

        if (!$this->collecteEnCours) {
            $this->message = 'Erreur: Collecte non trouvée.';
            $this->messageType = 'danger';
            return;
        }

        // Vérifier que le montant ne dépasse pas le solde
        $montant = (float) $this->montant;
        if ($montant > $this->soldeActuel) {
            $this->addError('montant', "Le montant dépasse votre solde actuel (" . number_format($this->soldeActuel) . " FC).");
            return;
        }

        try {
            // Mettre à jour la collecte
            $this->collecteEnCours->update([
                'montant_collecte' => $montant,
                'ecart' => $montant - ($this->collecteEnCours->montant_attendu ?? 0),
                'statut' => 'reussie',
                'heure_depart' => now(),
                'commentaire_caissier' => $this->notes,
            ]);

            // Déduire du solde du caissier
            $this->caissier->decrement('solde_actuel', $montant);

            // Rafraîchir les données
            $this->soldeActuel = $this->caissier->fresh()->solde_actuel;

            $this->fermerModal();

            $this->message = "Dépôt de " . number_format($montant) . " FC effectué avec succès. En attente de validation par le collecteur.";
            $this->messageType = 'success';

        } catch (\Exception $e) {
            $this->message = 'Erreur lors du dépôt: ' . $e->getMessage();
            $this->messageType = 'danger';
        }
    }

    public function deposerTout()
    {
        $this->montant = $this->soldeActuel;
    }

    public function closeMessage()
    {
        $this->message = '';
    }

    public function render()
    {
        // Collectes où ce caissier est inclus (tournées confirmées ou en cours)
        $collectesEnAttente = Collecte::with(['tournee.collecteur.user'])
            ->where('caissier_id', $this->caissier?->id)
            ->whereHas('tournee', function($q) {
                $q->whereIn('statut', ['confirmee', 'en_cours'])
                  ->whereDate('date', '>=', Carbon::today());
            })
            ->where('statut', 'en_attente')
            ->orderBy('created_at', 'desc')
            ->get();

        // Historique des dépôts récents
        $depotsRecents = Collecte::where('caissier_id', $this->caissier?->id)
            ->with(['tournee.collecteur.user'])
            ->whereIn('statut', ['reussie', 'partielle'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return view('livewire.cashier.depot', [
            'collectesEnAttente' => $collectesEnAttente,
            'depotsRecents' => $depotsRecents,
        ]);
    }
}
