<?php

namespace App\Observers;

use App\Models\Tournee;
use App\Services\NotificationService;

class TourneeObserver
{
    /**
     * Handle the Tournee "created" event.
     */
    public function created(Tournee $tournee): void
    {
        // Notifier le collecteur de sa nouvelle tournée
        NotificationService::notifierCollecteurTourneeJour($tournee);
    }

    /**
     * Handle the Tournee "updated" event.
     */
    public function updated(Tournee $tournee): void
    {
        // Si la tournée est confirmée (collecteur démarre)
        if ($tournee->isDirty('statut') && $tournee->statut === 'confirmee') {
            // Notifier les caissiers de la zone
            NotificationService::notifierCaissierTourneeConfirmee($tournee);
        }

        // Si la tournée est en cours
        if ($tournee->isDirty('statut') && $tournee->statut === 'en_cours') {
            NotificationService::notifierCaissierTourneeConfirmee($tournee);
        }

        // Si la tournée est terminée
        if ($tournee->isDirty('statut') && $tournee->statut === 'terminee') {
            $montantTotal = $tournee->montant_collecte ?? 0;
            NotificationService::notifierAdminFinRamassage($tournee, $montantTotal);
        }

        // Si la tournée a été modifiée (changement de collecteur, zone, etc.)
        if ($tournee->isDirty('collecteur_id') || $tournee->isDirty('zone') || $tournee->isDirty('date')) {
            NotificationService::notifierCollecteurModificationTournee($tournee);
        }
    }
}

