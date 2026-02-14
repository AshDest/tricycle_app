<?php

namespace App\Services;

use App\Models\Tournee;
use App\Models\Collecte;
use App\Models\Collecteur;
use App\Models\Caissier;
use App\Models\Zone;
use App\Models\Versement;
use App\Models\SystemNotification;
use Carbon\Carbon;

/**
 * Service de gestion des tournées et collectes.
 * Flux: Collecteur → visite Caissiers → transmet à NTH
 */
class CollecteService
{
    /**
     * Planifier une tournée pour un collecteur
     */
    public function planifierTournee(Collecteur $collecteur, Carbon $date, string $zone): Tournee
    {
        // Vérifier qu'il n'y a pas déjà une tournée ce jour
        $existante = Tournee::where('collecteur_id', $collecteur->id)
            ->whereDate('date', $date)
            ->first();

        if ($existante) {
            throw new \Exception('Une tournée existe déjà pour ce collecteur à cette date.');
        }

        // Calculer le total attendu basé sur les caissiers de la zone
        $caissiers = Caissier::parZone($zone)->actif()->get();
        $totalAttendu = $caissiers->sum('solde_actuel');

        $tournee = Tournee::create([
            'collecteur_id' => $collecteur->id,
            'date' => $date,
            'zone' => $zone,
            'statut' => 'planifiee',
            'total_attendu' => $totalAttendu,
        ]);

        // Créer les collectes pour chaque caissier de la zone
        foreach ($caissiers as $caissier) {
            Collecte::create([
                'tournee_id' => $tournee->id,
                'caissier_id' => $caissier->id,
                'montant_attendu' => $caissier->solde_actuel,
                'statut' => 'non_realisee',
            ]);
        }

        // Notifier le collecteur
        SystemNotification::notifierRamassagePrevu($collecteur->user, $tournee);

        return $tournee;
    }

    /**
     * Confirmer la présence du collecteur au début de la tournée
     */
    public function confirmerPresence(Tournee $tournee, ?float $latitude = null, ?float $longitude = null): void
    {
        $tournee->update([
            'presence_confirmee' => true,
            'presence_confirmee_at' => now(),
            'statut' => 'en_cours',
            'heure_debut_reelle' => now(),
            'latitude_debut' => $latitude,
            'longitude_debut' => $longitude,
        ]);
    }

    /**
     * Enregistrer une collecte chez un caissier
     */
    public function enregistrerCollecte(Collecte $collecte, array $data): Collecte
    {
        $collecte->update([
            'montant_collecte' => $data['montant_collecte'],
            'ecart' => $data['montant_collecte'] - $collecte->montant_attendu,
            'statut' => $this->determinerStatutCollecte($data['montant_collecte'], $collecte->montant_attendu),
            'signature_base64' => $data['signature_base64'] ?? null,
            'photo_cash_url' => $data['photo_cash_url'] ?? null,
            'heure_arrivee' => $data['heure_arrivee'] ?? now(),
            'heure_depart' => now(),
            'notes_anomalies' => $data['notes_anomalies'] ?? null,
            'commentaire_caissier' => $data['commentaire_caissier'] ?? null,
        ]);

        // Associer les versements à cette collecte
        Versement::where('caissier_id', $collecte->caissier_id)
            ->whereNull('collecte_id')
            ->where('statut', '!=', 'non_effectué')
            ->update(['collecte_id' => $collecte->id]);

        // Mettre à jour le solde du caissier
        $collecte->caissier->update([
            'solde_actuel' => $collecte->caissier->solde_actuel - $collecte->montant_collecte,
        ]);

        return $collecte;
    }

    /**
     * Déterminer le statut d'une collecte
     */
    private function determinerStatutCollecte(float $montantCollecte, float $montantAttendu): string
    {
        if ($montantCollecte >= $montantAttendu) {
            return 'reussie';
        } elseif ($montantCollecte > 0) {
            return 'partielle';
        }
        return 'non_realisee';
    }

    /**
     * Terminer une tournée
     */
    public function terminerTournee(Tournee $tournee, ?float $latitude = null, ?float $longitude = null): void
    {
        $totalEncaisse = $tournee->collectes()->sum('montant_collecte');
        $ecartTotal = $totalEncaisse - $tournee->total_attendu;

        $tournee->update([
            'statut' => 'terminee',
            'heure_fin_reelle' => now(),
            'total_encaisse' => $totalEncaisse,
            'ecart_total' => $ecartTotal,
            'latitude_fin' => $latitude,
            'longitude_fin' => $longitude,
        ]);
    }

    /**
     * Transmettre une tournée à NTH (Admin)
     */
    public function transmettreNth(Tournee $tournee): void
    {
        if ($tournee->statut !== 'terminee') {
            throw new \Exception('La tournée doit être terminée avant la transmission.');
        }

        $tournee->update([
            'transmis_nth' => true,
            'transmis_nth_at' => now(),
        ]);

        // Notifier les admins
        // TODO: Envoyer notification aux admins
    }

    /**
     * Valider la réception par NTH (Admin)
     */
    public function validerReceptionNth(Tournee $tournee, int $userId): void
    {
        $tournee->update([
            'valide_par_nth_id' => $userId,
            'valide_par_nth_at' => now(),
        ]);
    }

    /**
     * Obtenir les tournées du jour pour un collecteur
     */
    public function getTourneesDuJour(Collecteur $collecteur): ?Tournee
    {
        return $tournee = Tournee::where('collecteur_id', $collecteur->id)
            ->whereDate('date', today())
            ->with('collectes.caissier')
            ->first();
    }

    /**
     * Obtenir la liste des caissiers à visiter pour une tournée
     */
    public function getCaissiersAVisiter(Tournee $tournee): \Illuminate\Database\Eloquent\Collection
    {
        return $tournee->collectes()
            ->with('caissier.user')
            ->orderBy('id')
            ->get();
    }

    /**
     * Vérifier les tournées en retard
     */
    public function verifierTourneesEnRetard(): void
    {
        // Marquer les tournées planifiées non commencées comme en retard
        Tournee::where('statut', 'planifiee')
            ->whereDate('date', '<', today())
            ->update(['statut' => 'en_retard']);

        // Marquer les tournées en cours non terminées de la veille
        Tournee::where('statut', 'en_cours')
            ->whereDate('date', '<', today())
            ->update(['statut' => 'en_retard']);
    }

    /**
     * Planifier automatiquement les tournées pour une date
     */
    public function planifierTourneesAutomatiques(Carbon $date): array
    {
        $zones = Zone::actif()->get();
        $tourneesCreees = [];

        foreach ($zones as $zone) {
            $collecteur = $zone->getProchainCollecteur();

            if ($collecteur && $collecteur->is_active) {
                try {
                    $tournee = $this->planifierTournee($collecteur, $date, $zone->nom);
                    $tourneesCreees[] = $tournee;
                } catch (\Exception $e) {
                    // Tournée déjà existante, on continue
                    continue;
                }
            }
        }

        return $tourneesCreees;
    }

    /**
     * Obtenir les statistiques des collectes pour une période
     */
    public function getStatistiquesCollectes(Carbon $debut, Carbon $fin): array
    {
        $tournees = Tournee::whereBetween('date', [$debut, $fin])
            ->with('collectes')
            ->get();

        return [
            'nombre_tournees' => $tournees->count(),
            'tournees_terminees' => $tournees->where('statut', 'terminee')->count(),
            'tournees_en_retard' => $tournees->where('statut', 'en_retard')->count(),
            'total_collecte' => $tournees->sum('total_encaisse'),
            'total_attendu' => $tournees->sum('total_attendu'),
            'ecart_total' => $tournees->sum('ecart_total'),
            'taux_reussite' => $tournees->count() > 0
                ? round(($tournees->where('statut', 'terminee')->count() / $tournees->count()) * 100, 2)
                : 0,
        ];
    }
}

