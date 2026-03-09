<?php

namespace App\Services;

use App\Models\User;
use App\Models\Versement;
use App\Models\Accident;
use App\Models\Maintenance;
use App\Models\Tournee;
use App\Models\Payment;
use App\Models\Moto;
use App\Models\Motard;
use App\Models\SystemNotification;
use App\Models\Collecte;
use Illuminate\Support\Facades\DB;

/**
 * Service de gestion des notifications
 * Selon le cahier des charges - Notifications pour tous les acteurs
 */
class NotificationService
{
    /**
     * =====================================================
     * NOTIFICATIONS MOTARD
     * =====================================================
     */

    /**
     * Notifier le motard d'un retard de paiement
     */
    public static function notifierMotardRetardPaiement(Motard $motard, Versement $versement): void
    {
        if (!$motard->user) return;

        $dateVersement = $versement->date_versement ? $versement->date_versement->format('d/m/Y') : 'date inconnue';

        SystemNotification::create([
            'user_id' => $motard->user->id,
            'type' => 'retard_paiement',
            'titre' => 'Retard de versement',
            'message' => "Votre versement du {$dateVersement} est en retard. Montant dû: " . number_format($versement->arrieres ?? 0) . " FC",
            'icon' => 'exclamation-triangle',
            'couleur' => 'danger',
            'notifiable_type' => Versement::class,
            'notifiable_id' => $versement->id,
            'priorite' => 'haute',
        ]);
    }

    /**
     * Notifier le motard de la validation de son versement
     */
    public static function notifierMotardVersementValide(Motard $motard, Versement $versement): void
    {
        if (!$motard->user) return;

        $dateVersement = $versement->date_versement ? $versement->date_versement->format('d/m/Y') : 'date inconnue';

        SystemNotification::create([
            'user_id' => $motard->user->id,
            'type' => 'versement_valide',
            'titre' => 'Versement validé',
            'message' => "Votre versement de " . number_format($versement->montant ?? 0) . " FC du {$dateVersement} a été validé.",
            'icon' => 'check-circle',
            'couleur' => 'success',
            'notifiable_type' => Versement::class,
            'notifiable_id' => $versement->id,
            'priorite' => 'normale',
        ]);
    }

    /**
     * Notifier le motard d'arriérés critiques (accumulation dangereuse)
     */
    public static function notifierMotardArrieresCritiques(Motard $motard, float $totalArrieres): void
    {
        if (!$motard->user) return;

        // Vérifier si une notification similaire n'a pas déjà été envoyée récemment
        $existante = SystemNotification::where('user_id', $motard->user->id)
            ->where('type', 'arrieres_critiques')
            ->where('created_at', '>=', now()->subDays(3))
            ->exists();

        if ($existante) return;

        SystemNotification::create([
            'user_id' => $motard->user->id,
            'type' => 'arrieres_critiques',
            'titre' => '⚠️ Arriérés critiques',
            'message' => "Attention! Vos arriérés cumulés ont atteint " . number_format($totalArrieres) . " FC. Veuillez régulariser votre situation rapidement.",
            'icon' => 'exclamation-circle',
            'couleur' => 'danger',
            'priorite' => 'urgente',
        ]);
    }

    /**
     * Notifier le motard du jour de ramassage prévu dans sa zone
     */
    public static function notifierMotardRamassagePrevu(Motard $motard, Tournee $tournee): void
    {
        if (!$motard->user) return;

        $dateTournee = $tournee->date ? $tournee->date->format('d/m/Y') : 'date inconnue';
        $expireAt = $tournee->date ? $tournee->date->endOfDay() : now()->endOfDay();

        SystemNotification::create([
            'user_id' => $motard->user->id,
            'type' => 'ramassage_prevu',
            'titre' => 'Ramassage prévu',
            'message' => "Un ramassage est prévu dans votre zone le {$dateTournee}. Préparez votre versement.",
            'icon' => 'truck',
            'couleur' => 'info',
            'notifiable_type' => Tournee::class,
            'notifiable_id' => $tournee->id,
            'priorite' => 'normale',
            'expire_at' => $expireAt,
        ]);
    }

    /**
     * =====================================================
     * NOTIFICATIONS PROPRIETAIRE
     * =====================================================
     */

    /**
     * Notifier le propriétaire d'un versement effectué sur sa moto
     */
    public static function notifierProprietaireVersement(Versement $versement): void
    {
        $moto = $versement->moto;
        if (!$moto || !$moto->proprietaire || !$moto->proprietaire->user) return;

        $proprietaire = $moto->proprietaire;

        SystemNotification::create([
            'user_id' => $proprietaire->user->id,
            'type' => 'versement_moto',
            'titre' => 'Versement reçu',
            'message' => "Un versement de " . number_format($versement->montant) . " FC a été effectué pour votre moto {$moto->plaque_immatriculation}.",
            'icon' => 'cash',
            'couleur' => 'success',
            'notifiable_type' => Versement::class,
            'notifiable_id' => $versement->id,
            'priorite' => 'normale',
        ]);
    }

    /**
     * Notifier le propriétaire d'un paiement effectué
     */
    public static function notifierProprietairePaiement(Payment $payment): void
    {
        if (!$payment->proprietaire || !$payment->proprietaire->user) return;

        SystemNotification::create([
            'user_id' => $payment->proprietaire->user->id,
            'type' => 'paiement_recu',
            'titre' => 'Paiement reçu',
            'message' => "Vous avez reçu un paiement de " . number_format($payment->total_paye) . " FC via {$payment->mode_paiement}.",
            'icon' => 'wallet',
            'couleur' => 'success',
            'notifiable_type' => Payment::class,
            'notifiable_id' => $payment->id,
            'priorite' => 'normale',
        ]);
    }

    /**
     * Notifier le propriétaire d'un accident sur sa moto
     */
    public static function notifierProprietaireAccident(Accident $accident): void
    {
        $moto = $accident->moto;
        if (!$moto || !$moto->proprietaire || !$moto->proprietaire->user) return;

        $dateAccident = $accident->date_heure ? $accident->date_heure->format('d/m/Y à H:i') : 'date inconnue';

        SystemNotification::create([
            'user_id' => $moto->proprietaire->user->id,
            'type' => 'accident_moto',
            'titre' => '🚨 Accident déclaré',
            'message' => "Un accident impliquant votre moto {$moto->plaque_immatriculation} a été déclaré le {$dateAccident}.",
            'icon' => 'exclamation-triangle',
            'couleur' => 'danger',
            'notifiable_type' => Accident::class,
            'notifiable_id' => $accident->id,
            'priorite' => 'urgente',
        ]);
    }

    /**
     * Notifier le propriétaire d'une maintenance effectuée
     */
    public static function notifierProprietaireMaintenance(Maintenance $maintenance): void
    {
        $moto = $maintenance->moto;
        if (!$moto || !$moto->proprietaire || !$moto->proprietaire->user) return;

        $cout = ($maintenance->cout_pieces ?? 0) + ($maintenance->cout_main_oeuvre ?? 0);

        SystemNotification::create([
            'user_id' => $moto->proprietaire->user->id,
            'type' => 'maintenance_moto',
            'titre' => 'Maintenance effectuée',
            'message' => "Une maintenance ({$maintenance->type_maintenance}) a été effectuée sur votre moto {$moto->plaque_immatriculation}. Coût: " . number_format($cout) . " FC",
            'icon' => 'tools',
            'couleur' => 'warning',
            'notifiable_type' => Maintenance::class,
            'notifiable_id' => $maintenance->id,
            'priorite' => 'normale',
        ]);
    }

    /**
     * =====================================================
     * NOTIFICATIONS OKAMI (SUPERVISEUR)
     * =====================================================
     */

    /**
     * Notifier OKAMI des arriérés d'un motard
     */
    public static function notifierOkamiArrieres(Motard $motard, float $totalArrieres): void
    {
        $superviseurs = User::role('supervisor')->get();
        $nomMotard = $motard->user->name ?? $motard->numero_identifiant ?? 'Motard inconnu';

        foreach ($superviseurs as $user) {
            SystemNotification::create([
                'user_id' => $user->id,
                'type' => 'arrieres_motard',
                'titre' => 'Arriérés motard',
                'message' => "Le motard {$nomMotard} cumule " . number_format($totalArrieres) . " FC d'arriérés.",
                'icon' => 'exclamation-triangle',
                'couleur' => 'warning',
                'notifiable_type' => Motard::class,
                'notifiable_id' => $motard->id,
                'priorite' => $totalArrieres > 50000 ? 'urgente' : 'haute',
            ]);
        }
    }

    /**
     * Notifier OKAMI d'un accident grave
     */
    public static function notifierOkamiAccidentGrave(Accident $accident): void
    {
        $superviseurs = User::role('supervisor')->get();
        $dateAccident = $accident->date_heure ? $accident->date_heure->format('d/m/Y à H:i') : 'date inconnue';

        foreach ($superviseurs as $user) {
            SystemNotification::create([
                'user_id' => $user->id,
                'type' => 'accident_grave',
                'titre' => '🚨 Accident grave',
                'message' => "Un accident grave a été déclaré à {$accident->lieu} le {$dateAccident}.",
                'icon' => 'exclamation-circle',
                'couleur' => 'danger',
                'notifiable_type' => Accident::class,
                'notifiable_id' => $accident->id,
                'priorite' => 'urgente',
            ]);
        }
    }

    /**
     * Notifier OKAMI d'une demande de paiement
     */
    public static function notifierOkamiDemandePaiement(Payment $payment): void
    {
        $superviseurs = User::role('supervisor')->get();

        foreach ($superviseurs as $user) {
            SystemNotification::create([
                'user_id' => $user->id,
                'type' => 'demande_paiement',
                'titre' => 'Nouvelle demande de paiement',
                'message' => "Une demande de paiement de " . number_format($payment->total_du) . " FC a été soumise.",
                'icon' => 'credit-card',
                'couleur' => 'info',
                'notifiable_type' => Payment::class,
                'notifiable_id' => $payment->id,
                'priorite' => 'normale',
            ]);
        }
    }

    /**
     * =====================================================
     * NOTIFICATIONS CAISSIER
     * =====================================================
     */

    /**
     * Notifier le caissier d'une tournée confirmée (collecteur en route)
     */
    public static function notifierCaissierTourneeConfirmee(Tournee $tournee): void
    {
        // Trouver les caissiers de la même zone
        $caissiers = \App\Models\Caissier::where('zone', $tournee->zone)
            ->where('is_active', true)
            ->with('user')
            ->get();

        $nomCollecteur = $tournee->collecteur->user->name ?? 'Collecteur';

        foreach ($caissiers as $caissier) {
            if (!$caissier->user) continue;

            SystemNotification::create([
                'user_id' => $caissier->user->id,
                'type' => 'tournee_confirmee',
                'titre' => 'Collecteur en route',
                'message' => "Le collecteur {$nomCollecteur} est en route pour collecter. Préparez votre caisse.",
                'icon' => 'truck',
                'couleur' => 'info',
                'notifiable_type' => Tournee::class,
                'notifiable_id' => $tournee->id,
                'priorite' => 'haute',
                'expire_at' => now()->endOfDay(),
            ]);
        }
    }

    /**
     * =====================================================
     * NOTIFICATIONS COLLECTEUR
     * =====================================================
     */

    /**
     * Notifier le collecteur de sa tournée du jour
     */
    public static function notifierCollecteurTourneeJour(Tournee $tournee): void
    {
        if (!$tournee->collecteur || !$tournee->collecteur->user) return;

        SystemNotification::create([
            'user_id' => $tournee->collecteur->user->id,
            'type' => 'tournee_jour',
            'titre' => 'Tournée du jour',
            'message' => "Vous avez une tournée prévue aujourd'hui dans la zone {$tournee->zone}.",
            'icon' => 'calendar-check',
            'couleur' => 'primary',
            'notifiable_type' => Tournee::class,
            'notifiable_id' => $tournee->id,
            'priorite' => 'haute',
            'expire_at' => now()->endOfDay(),
        ]);
    }

    /**
     * Notifier le collecteur d'une modification de tournée
     */
    public static function notifierCollecteurModificationTournee(Tournee $tournee): void
    {
        if (!$tournee->collecteur || !$tournee->collecteur->user) return;

        $dateTournee = $tournee->date ? $tournee->date->format('d/m/Y') : 'date inconnue';

        SystemNotification::create([
            'user_id' => $tournee->collecteur->user->id,
            'type' => 'modification_tournee',
            'titre' => 'Tournée modifiée',
            'message' => "Votre tournée du {$dateTournee} a été modifiée. Vérifiez les détails.",
            'icon' => 'pencil',
            'couleur' => 'warning',
            'notifiable_type' => Tournee::class,
            'notifiable_id' => $tournee->id,
            'priorite' => 'haute',
        ]);
    }

    /**
     * Notifier le collecteur de la validation/rejet du caissier
     */
    public static function notifierCollecteurValidationCaissier(Collecte $collecte, bool $valide): void
    {
        if (!$collecte->collecteur || !$collecte->collecteur->user) return;

        SystemNotification::create([
            'user_id' => $collecte->collecteur->user->id,
            'type' => $valide ? 'collecte_validee' : 'collecte_rejetee',
            'titre' => $valide ? 'Collecte validée' : 'Collecte rejetée',
            'message' => $valide
                ? "Votre collecte de " . number_format($collecte->montant_collecte ?? 0) . " FC a été validée."
                : "Votre collecte a été rejetée. Veuillez vérifier les détails.",
            'icon' => $valide ? 'check-circle' : 'x-circle',
            'couleur' => $valide ? 'success' : 'danger',
            'notifiable_type' => Collecte::class,
            'notifiable_id' => $collecte->id,
            'priorite' => $valide ? 'normale' : 'haute',
        ]);
    }

    /**
     * =====================================================
     * NOTIFICATIONS ADMIN (NTH SARL)
     * =====================================================
     */

    /**
     * Notifier l'admin d'un accident grave
     */
    public static function notifierAdminAccidentGrave(Accident $accident): void
    {
        $admins = User::role('admin')->get();
        $plaqueMoto = $accident->moto->plaque_immatriculation ?? 'N/A';

        foreach ($admins as $user) {
            SystemNotification::create([
                'user_id' => $user->id,
                'type' => 'accident_grave',
                'titre' => '🚨 Accident grave signalé',
                'message' => "Accident grave déclaré à {$accident->lieu}. Moto: {$plaqueMoto}",
                'icon' => 'exclamation-circle',
                'couleur' => 'danger',
                'notifiable_type' => Accident::class,
                'notifiable_id' => $accident->id,
                'priorite' => 'urgente',
            ]);
        }
    }

    /**
     * Notifier l'admin d'une immobilisation prolongée de moto
     */
    public static function notifierAdminImmobilisationProlongee(Moto $moto, int $jours): void
    {
        $admins = User::role('admin')->get();

        foreach ($admins as $user) {
            SystemNotification::create([
                'user_id' => $user->id,
                'type' => 'immobilisation_prolongee',
                'titre' => 'Immobilisation prolongée',
                'message' => "La moto {$moto->plaque_immatriculation} est immobilisée depuis {$jours} jours.",
                'icon' => 'clock',
                'couleur' => 'warning',
                'notifiable_type' => Moto::class,
                'notifiable_id' => $moto->id,
                'priorite' => 'haute',
            ]);
        }
    }

    /**
     * Notifier l'admin d'un dépassement de budget maintenance
     */
    public static function notifierAdminDepassementBudgetMaintenance(Moto $moto, float $cout): void
    {
        $admins = User::role('admin')->get();

        foreach ($admins as $user) {
            SystemNotification::create([
                'user_id' => $user->id,
                'type' => 'depassement_budget',
                'titre' => 'Budget maintenance dépassé',
                'message' => "Les coûts de maintenance de la moto {$moto->plaque_immatriculation} ont atteint " . number_format($cout) . " FC.",
                'icon' => 'currency-dollar',
                'couleur' => 'warning',
                'notifiable_type' => Moto::class,
                'notifiable_id' => $moto->id,
                'priorite' => 'haute',
            ]);
        }
    }

    /**
     * Notifier l'admin de la fin du ramassage d'un collecteur
     */
    public static function notifierAdminFinRamassage(Tournee $tournee, float $montantTotal): void
    {
        $admins = User::role('admin')->get();
        $dateTournee = $tournee->date ? $tournee->date->format('d/m/Y') : 'date inconnue';

        foreach ($admins as $user) {
            SystemNotification::create([
                'user_id' => $user->id,
                'type' => 'fin_ramassage',
                'titre' => 'Tournée terminée',
                'message' => "La tournée du {$dateTournee} est terminée. Total collecté: " . number_format($montantTotal) . " FC",
                'icon' => 'check-circle',
                'couleur' => 'success',
                'notifiable_type' => Tournee::class,
                'notifiable_id' => $tournee->id,
                'priorite' => 'normale',
            ]);
        }
    }

    /**
     * =====================================================
     * NOTIFICATIONS GÉNÉRALES
     * =====================================================
     */

    /**
     * Notifier un contrat de moto expiré
     */
    public static function notifierContratExpire(Moto $moto): void
    {
        // Notifier le propriétaire
        if ($moto->proprietaire && $moto->proprietaire->user) {
            SystemNotification::create([
                'user_id' => $moto->proprietaire->user->id,
                'type' => 'contrat_expire',
                'titre' => 'Contrat expiré',
                'message' => "Le contrat de votre moto {$moto->plaque_immatriculation} a expiré. Veuillez le renouveler.",
                'icon' => 'calendar-x',
                'couleur' => 'danger',
                'notifiable_type' => Moto::class,
                'notifiable_id' => $moto->id,
                'priorite' => 'urgente',
            ]);
        }

        // Notifier les admins
        $admins = User::role('admin')->get();
        $nomProprietaire = $moto->proprietaire->user->name ?? 'N/A';

        foreach ($admins as $user) {
            SystemNotification::create([
                'user_id' => $user->id,
                'type' => 'contrat_expire',
                'titre' => 'Contrat moto expiré',
                'message' => "Le contrat de la moto {$moto->plaque_immatriculation} (Propriétaire: {$nomProprietaire}) a expiré.",
                'icon' => 'calendar-x',
                'couleur' => 'warning',
                'notifiable_type' => Moto::class,
                'notifiable_id' => $moto->id,
                'priorite' => 'haute',
            ]);
        }
    }

    /**
     * Notifier une prochaine maintenance programmée
     */
    public static function notifierMaintenanceProgrammee(Maintenance $maintenance): void
    {
        $moto = $maintenance->moto;
        if (!$moto) return;

        $dateMaintenance = $maintenance->date_intervention ? $maintenance->date_intervention->format('d/m/Y') : 'date à déterminer';

        // Notifier le motard
        if ($moto->motardActif && $moto->motardActif->user) {
            SystemNotification::create([
                'user_id' => $moto->motardActif->user->id,
                'type' => 'maintenance_programmee',
                'titre' => 'Maintenance programmée',
                'message' => "Une maintenance ({$maintenance->type_maintenance}) est programmée pour votre moto le {$dateMaintenance}.",
                'icon' => 'tools',
                'couleur' => 'info',
                'notifiable_type' => Maintenance::class,
                'notifiable_id' => $maintenance->id,
                'priorite' => 'normale',
            ]);
        }

        // Notifier le propriétaire
        if ($moto->proprietaire && $moto->proprietaire->user) {
            SystemNotification::create([
                'user_id' => $moto->proprietaire->user->id,
                'type' => 'maintenance_programmee',
                'titre' => 'Maintenance programmée',
                'message' => "Une maintenance ({$maintenance->type_maintenance}) est programmée pour votre moto {$moto->plaque_immatriculation}.",
                'icon' => 'tools',
                'couleur' => 'info',
                'notifiable_type' => Maintenance::class,
                'notifiable_id' => $maintenance->id,
                'priorite' => 'normale',
            ]);
        }
    }

    /**
     * Marquer toutes les notifications d'un utilisateur comme lues
     */
    public static function marquerToutesCommeLues(User $user): int
    {
        return SystemNotification::where('user_id', $user->id)
            ->where('lu', false)
            ->update([
                'lu' => true,
                'lu_at' => now(),
            ]);
    }

    /**
     * Supprimer les notifications expirées
     */
    public static function nettoyerNotificationsExpirees(): int
    {
        return SystemNotification::where('expire_at', '<', now())
            ->where('lu', true)
            ->delete();
    }

    /**
     * Obtenir le nombre de notifications non lues pour un utilisateur
     */
    public static function getNombreNonLues(User $user): int
    {
        return SystemNotification::where('user_id', $user->id)
            ->nonLues()
            ->nonExpirees()
            ->count();
    }
}

