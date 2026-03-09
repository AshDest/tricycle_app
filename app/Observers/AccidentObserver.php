<?php

namespace App\Observers;

use App\Models\Accident;
use App\Services\NotificationService;

class AccidentObserver
{
    /**
     * Handle the Accident "created" event.
     */
    public function created(Accident $accident): void
    {
        // Notifier le propriétaire de la moto
        NotificationService::notifierProprietaireAccident($accident);

        // Si accident grave, notifier OKAMI et Admin
        if (in_array($accident->gravite ?? '', ['grave', 'très grave', 'tres_grave'])) {
            NotificationService::notifierOkamiAccidentGrave($accident);
            NotificationService::notifierAdminAccidentGrave($accident);
        }
    }

    /**
     * Handle the Accident "updated" event.
     */
    public function updated(Accident $accident): void
    {
        // Si le statut passe à "en_reparation", notifier les parties concernées
        if ($accident->isDirty('statut') && $accident->statut === 'en_reparation') {
            $moto = $accident->moto;

            if ($moto && $moto->proprietaire && $moto->proprietaire->user) {
                \App\Models\SystemNotification::create([
                    'user_id' => $moto->proprietaire->user->id,
                    'type' => 'reparation_accident',
                    'titre' => 'Réparation en cours',
                    'message' => "La réparation suite à l'accident de votre moto {$moto->plaque_immatriculation} a commencé.",
                    'icon' => 'tools',
                    'couleur' => 'info',
                    'notifiable_type' => Accident::class,
                    'notifiable_id' => $accident->id,
                    'priorite' => 'normale',
                ]);
            }
        }

        // Si l'accident est clôturé
        if ($accident->isDirty('statut') && $accident->statut === 'cloture') {
            $moto = $accident->moto;

            if ($moto && $moto->proprietaire && $moto->proprietaire->user) {
                \App\Models\SystemNotification::create([
                    'user_id' => $moto->proprietaire->user->id,
                    'type' => 'accident_cloture',
                    'titre' => 'Accident clôturé',
                    'message' => "Le dossier d'accident de votre moto {$moto->plaque_immatriculation} a été clôturé.",
                    'icon' => 'check-circle',
                    'couleur' => 'success',
                    'notifiable_type' => Accident::class,
                    'notifiable_id' => $accident->id,
                    'priorite' => 'normale',
                ]);
            }
        }
    }
}

