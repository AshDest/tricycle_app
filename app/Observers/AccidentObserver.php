<?php

namespace App\Observers;

use App\Models\Accident;
use App\Models\Moto;
use App\Models\SystemNotification;
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

        // Si accident grave, notifier OKAMI et Admin + désactiver la moto
        if (in_array($accident->gravite ?? '', ['grave', 'très grave', 'tres_grave'])) {
            NotificationService::notifierOkamiAccidentGrave($accident);
            NotificationService::notifierAdminAccidentGrave($accident);

            // Désactiver la moto automatiquement
            $this->desactiverMoto($accident);
        }
    }

    /**
     * Handle the Accident "updated" event.
     */
    public function updated(Accident $accident): void
    {
        // Si la gravité passe à grave lors d'une mise à jour
        if ($accident->isDirty('gravite') && in_array($accident->gravite, ['grave', 'très grave', 'tres_grave'])) {
            $this->desactiverMoto($accident);
        }

        // Si le statut passe à "en_reparation", notifier les parties concernées
        if ($accident->isDirty('statut') && $accident->statut === 'en_reparation') {
            $moto = $accident->moto;

            if ($moto && $moto->proprietaire && $moto->proprietaire->user) {
                SystemNotification::create([
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

        // Si l'accident est clôturé, réactiver la moto
        if ($accident->isDirty('statut') && $accident->statut === 'cloture') {
            $this->reactiverMoto($accident);

            $moto = $accident->moto;

            if ($moto && $moto->proprietaire && $moto->proprietaire->user) {
                SystemNotification::create([
                    'user_id' => $moto->proprietaire->user->id,
                    'type' => 'accident_cloture',
                    'titre' => 'Accident clôturé',
                    'message' => "Le dossier d'accident de votre moto {$moto->plaque_immatriculation} a été clôturé. La moto est à nouveau opérationnelle.",
                    'icon' => 'check-circle',
                    'couleur' => 'success',
                    'notifiable_type' => Accident::class,
                    'notifiable_id' => $accident->id,
                    'priorite' => 'normale',
                ]);
            }
        }
    }

    /**
     * Désactiver la moto suite à un accident grave
     */
    protected function desactiverMoto(Accident $accident): void
    {
        $moto = $accident->moto;

        if ($moto && $moto->statut === 'actif') {
            $moto->update(['statut' => 'maintenance']);

            // Notifier le motard
            if ($moto->motard && $moto->motard->user) {
                SystemNotification::create([
                    'user_id' => $moto->motard->user->id,
                    'type' => 'moto_desactivee_accident',
                    'titre' => 'Moto désactivée',
                    'message' => "Votre moto {$moto->plaque_immatriculation} a été désactivée suite à un accident grave. Elle sera réactivée après la maintenance.",
                    'icon' => 'exclamation-triangle',
                    'couleur' => 'danger',
                    'notifiable_type' => Moto::class,
                    'notifiable_id' => $moto->id,
                    'priorite' => 'haute',
                ]);
            }

            // Notifier le propriétaire
            if ($moto->proprietaire && $moto->proprietaire->user) {
                SystemNotification::create([
                    'user_id' => $moto->proprietaire->user->id,
                    'type' => 'moto_desactivee_accident',
                    'titre' => 'Moto désactivée',
                    'message' => "Votre moto {$moto->plaque_immatriculation} a été automatiquement désactivée suite à un accident grave. Elle sera réactivée après la maintenance.",
                    'icon' => 'exclamation-triangle',
                    'couleur' => 'danger',
                    'notifiable_type' => Moto::class,
                    'notifiable_id' => $moto->id,
                    'priorite' => 'haute',
                ]);
            }
        }
    }

    /**
     * Réactiver la moto après clôture de l'accident
     */
    protected function reactiverMoto(Accident $accident): void
    {
        $moto = $accident->moto;

        // Vérifier s'il n'y a pas d'autres accidents graves non clôturés
        if ($moto) {
            $autresAccidentsGraves = $moto->accidents()
                ->where('id', '!=', $accident->id)
                ->whereIn('gravite', ['grave', 'très grave', 'tres_grave'])
                ->where('statut', '!=', 'cloture')
                ->exists();

            // Vérifier aussi s'il n'y a pas de maintenance en cours
            $maintenanceEnCours = $moto->maintenances()
                ->whereIn('statut', ['en_attente', 'en_cours'])
                ->exists();

            // Réactiver seulement s'il n'y a pas d'autres blocages
            if (!$autresAccidentsGraves && !$maintenanceEnCours && $moto->statut === 'maintenance') {
                // Vérifier que le contrat est toujours actif
                if ($moto->contrat_actif) {
                    $moto->update(['statut' => 'actif']);

                    // Notifier le motard
                    if ($moto->motard && $moto->motard->user) {
                        SystemNotification::create([
                            'user_id' => $moto->motard->user->id,
                            'type' => 'moto_reactivee',
                            'titre' => 'Moto réactivée',
                            'message' => "Votre moto {$moto->plaque_immatriculation} est à nouveau active après la clôture de l'accident.",
                            'icon' => 'check-circle',
                            'couleur' => 'success',
                            'notifiable_type' => Moto::class,
                            'notifiable_id' => $moto->id,
                            'priorite' => 'normale',
                        ]);
                    }
                }
            }
        }
    }
}

