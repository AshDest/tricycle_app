<?php

namespace App\Observers;

use App\Models\Maintenance;
use App\Models\Moto;
use App\Models\SystemNotification;
use App\Services\NotificationService;

class MaintenanceObserver
{
    /**
     * Handle the Maintenance "created" event.
     */
    public function created(Maintenance $maintenance): void
    {
        // Si maintenance programmée, notifier les parties concernées
        if ($maintenance->statut === 'programmee' || $maintenance->statut === 'en_attente') {
            NotificationService::notifierMaintenanceProgrammee($maintenance);
        }

        // Si la maintenance est liée à un accident grave, s'assurer que la moto est en maintenance
        if ($maintenance->accident_id) {
            $this->verifierStatutMoto($maintenance);
        }
    }

    /**
     * Handle the Maintenance "updated" event.
     */
    public function updated(Maintenance $maintenance): void
    {
        // Si la maintenance est terminée
        if ($maintenance->isDirty('statut') && $maintenance->statut === 'termine') {
            NotificationService::notifierProprietaireMaintenance($maintenance);

            // Vérifier le coût total de maintenance de la moto
            $this->verifierCoutsMaintenance($maintenance);

            // Réactiver la moto si possible
            $this->reactiverMotoSiPossible($maintenance);
        }
    }

    /**
     * Vérifier et mettre à jour le statut de la moto en maintenance
     */
    protected function verifierStatutMoto(Maintenance $maintenance): void
    {
        $moto = $maintenance->moto;

        if ($moto && $moto->statut === 'actif') {
            // Si c'est une maintenance liée à un accident, mettre la moto en maintenance
            if ($maintenance->accident_id) {
                $accident = $maintenance->accident;
                if ($accident && in_array($accident->gravite, ['grave', 'très grave', 'tres_grave'])) {
                    $moto->update(['statut' => 'maintenance']);
                }
            }
        }
    }

    /**
     * Réactiver la moto si toutes les conditions sont remplies
     */
    protected function reactiverMotoSiPossible(Maintenance $maintenance): void
    {
        $moto = $maintenance->moto;

        if (!$moto || $moto->statut !== 'maintenance') {
            return;
        }

        // Vérifier s'il n'y a pas d'autres maintenances en cours
        $autresMaintenances = $moto->maintenances()
            ->where('id', '!=', $maintenance->id)
            ->whereIn('statut', ['en_attente', 'en_cours', 'programmee'])
            ->exists();

        // Vérifier s'il n'y a pas d'accidents graves non clôturés
        $accidentsGravesNonClotures = $moto->accidents()
            ->whereIn('gravite', ['grave', 'très grave', 'tres_grave'])
            ->where('statut', '!=', 'cloture')
            ->exists();

        // Si la maintenance terminée était liée à un accident, vérifier si l'accident peut être clôturé
        if ($maintenance->accident_id && $maintenance->accident) {
            $accident = $maintenance->accident;
            // Mettre à jour l'accident comme réparation terminée si ce n'est pas déjà fait
            if ($accident->statut !== 'cloture' && $accident->statut !== 'reparation_terminee') {
                $accident->update([
                    'statut' => 'reparation_terminee',
                    'reparation_terminee_at' => now(),
                ]);
            }
        }

        // Réactiver seulement s'il n'y a plus de blocage
        if (!$autresMaintenances && !$accidentsGravesNonClotures) {
            // Vérifier que le contrat est toujours actif
            if ($moto->contrat_actif) {
                $moto->update(['statut' => 'actif']);

                // Notifier le motard
                if ($moto->motard && $moto->motard->user) {
                    SystemNotification::create([
                        'user_id' => $moto->motard->user->id,
                        'type' => 'moto_reactivee',
                        'titre' => 'Moto réactivée',
                        'message' => "Votre moto {$moto->plaque_immatriculation} est à nouveau active après la maintenance.",
                        'icon' => 'check-circle',
                        'couleur' => 'success',
                        'notifiable_type' => Moto::class,
                        'notifiable_id' => $moto->id,
                        'priorite' => 'normale',
                    ]);
                }

                // Notifier le propriétaire
                if ($moto->proprietaire && $moto->proprietaire->user) {
                    SystemNotification::create([
                        'user_id' => $moto->proprietaire->user->id,
                        'type' => 'moto_reactivee',
                        'titre' => 'Moto réactivée',
                        'message' => "Votre moto {$moto->plaque_immatriculation} est à nouveau active après la maintenance.",
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

    /**
     * Vérifier si les coûts de maintenance dépassent un seuil
     */
    protected function verifierCoutsMaintenance(Maintenance $maintenance): void
    {
        if (!$maintenance->moto) return;

        $coutTotal = Maintenance::where('moto_id', $maintenance->moto_id)
            ->where('statut', 'termine')
            ->get()
            ->sum(function ($m) {
                return ($m->cout_pieces ?? 0) + ($m->cout_main_oeuvre ?? 0);
            });

        // Seuil d'alerte: 200 000 FC (paramétrable)
        $seuilAlerte = 200000;

        if ($coutTotal >= $seuilAlerte) {
            NotificationService::notifierAdminDepassementBudgetMaintenance($maintenance->moto, $coutTotal);
        }
    }
}

