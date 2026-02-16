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
 * Le caissier dépose son solde collecté au collecteur pendant sa tournée
 */
#[Layout('components.dashlite-layout')]
class Depot extends Component
{
    // Formulaire
    public $montant = '';
    public $collecteur_id = '';
    public $notes = '';

    // Données
    public $soldeActuel = 0;
    public $caissier = null;
    public $tourneeEnCours = null;
    public $collecteurDisponible = null;

    // Message
    public $message = '';
    public $messageType = 'info';

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

            // Chercher une tournée en cours/confirmée aujourd'hui pour la zone du caissier
            $zoneCaissier = $this->caissier->zone;

            $this->tourneeEnCours = Tournee::where('date', Carbon::today())
                ->whereIn('statut', ['confirmee', 'en_cours'])
                ->when($zoneCaissier, function($q) use ($zoneCaissier) {
                    // Correspondance exacte ou partielle de zone
                    $q->where(function($q2) use ($zoneCaissier) {
                        $q2->where('zone', $zoneCaissier)
                           ->orWhere('zone', 'like', '%' . $zoneCaissier . '%');
                    });
                })
                ->with('collecteur.user')
                ->first();

            // Si pas de tournée par zone, chercher une tournée globale
            if (!$this->tourneeEnCours) {
                $this->tourneeEnCours = Tournee::where('date', Carbon::today())
                    ->whereIn('statut', ['confirmee', 'en_cours'])
                    ->with('collecteur.user')
                    ->first();
            }

            if ($this->tourneeEnCours) {
                $this->collecteurDisponible = $this->tourneeEnCours->collecteur;
                $this->collecteur_id = $this->collecteurDisponible->id ?? null;
            }
        }
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

        // Vérifier que le montant ne dépasse pas le solde
        if ($this->montant > $this->soldeActuel) {
            $this->addError('montant', "Le montant dépasse votre solde actuel ({$this->soldeActuel} FC).");
            return;
        }

        // Vérifier qu'il y a une tournée en cours
        if (!$this->tourneeEnCours) {
            $this->message = 'Aucune tournée en cours pour aujourd\'hui. Veuillez attendre le passage du collecteur.';
            $this->messageType = 'warning';
            return;
        }

        try {
            // Créer ou mettre à jour la collecte
            $collecte = Collecte::updateOrCreate(
                [
                    'tournee_id' => $this->tourneeEnCours->id,
                    'caissier_id' => $this->caissier->id,
                ],
                [
                    'montant_attendu' => $this->soldeActuel,
                    'montant_collecte' => $this->montant,
                    'ecart' => $this->montant - $this->soldeActuel,
                    'statut' => 'reussie',
                    'heure_depart' => now(),
                    'commentaire_caissier' => $this->notes,
                ]
            );

            // Déduire du solde du caissier
            $this->caissier->decrement('solde_actuel', $this->montant);

            // Rafraîchir les données
            $this->soldeActuel = $this->caissier->fresh()->solde_actuel;

            // Réinitialiser le formulaire
            $this->reset(['montant', 'notes']);

            $this->message = "Dépôt de " . number_format($collecte->montant_collecte) . " FC effectué avec succès. En attente de validation par le collecteur.";
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
        // Historique des dépôts récents
        $depotsRecents = Collecte::where('caissier_id', $this->caissier?->id)
            ->with(['tournee.collecteur.user'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('livewire.cashier.depot', [
            'depotsRecents' => $depotsRecents,
        ]);
    }
}
