<?php

namespace App\Observers;

use App\Models\Moto;
use App\Services\NotificationService;
use Carbon\Carbon;

class MotoObserver
{
    /**
     * Handle the Moto "updated" event.
     */
    public function updated(Moto $moto): void
    {
        // Vérifier si le contrat vient d'expirer
        if ($moto->isDirty('contrat_fin') || $moto->isDirty('statut')) {
            $this->verifierContrat($moto);
        }
    }

    /**
     * Handle the Moto "saving" event.
     * Vérifie le contrat avant sauvegarde
     */
    public function saving(Moto $moto): void
    {
        // Vérifier si le contrat est expiré
        if ($moto->contrat_fin && Carbon::parse($moto->contrat_fin)->isPast()) {
            // Le contrat est expiré, notifier si ce n'est pas déjà fait
            if (!$moto->isDirty('statut') || $moto->statut !== 'inactif') {
                $this->notifierExpiration($moto);
            }
        }
    }

    /**
     * Vérifier le contrat de la moto
     */
    protected function verifierContrat(Moto $moto): void
    {
        if (!$moto->contrat_fin) return;

        $dateExpiration = Carbon::parse($moto->contrat_fin);
        $aujourdhui = Carbon::today();

        // Si le contrat expire dans 7 jours
        if ($dateExpiration->diffInDays($aujourdhui) <= 7 && $dateExpiration->isFuture()) {
            $this->notifierExpirationProchaine($moto, $dateExpiration->diffInDays($aujourdhui));
        }

        // Si le contrat est expiré
        if ($dateExpiration->isPast()) {
            $this->notifierExpiration($moto);
        }
    }

    /**
     * Notifier de l'expiration prochaine du contrat
     */
    protected function notifierExpirationProchaine(Moto $moto, int $joursRestants): void
    {
        // Vérifier si une notification similaire n'a pas déjà été envoyée
        $existante = \App\Models\SystemNotification::where('type', 'contrat_expire_bientot')
            ->where('notifiable_type', Moto::class)
            ->where('notifiable_id', $moto->id)
            ->where('created_at', '>=', now()->subDays(3))
            ->exists();

        if ($existante) return;

        // Notifier le propriétaire
        if ($moto->proprietaire && $moto->proprietaire->user) {
            \App\Models\SystemNotification::create([
                'user_id' => $moto->proprietaire->user->id,
                'type' => 'contrat_expire_bientot',
                'titre' => '⚠️ Contrat expire bientôt',
                'message' => "Le contrat de votre moto {$moto->plaque_immatriculation} expire dans {$joursRestants} jour(s). Pensez à le renouveler.",
                'icon' => 'calendar-event',
                'couleur' => 'warning',
                'notifiable_type' => Moto::class,
                'notifiable_id' => $moto->id,
                'priorite' => 'haute',
            ]);
        }
    }

    /**
     * Notifier de l'expiration du contrat
     */
    protected function notifierExpiration(Moto $moto): void
    {
        // Vérifier si une notification similaire n'a pas déjà été envoyée
        $existante = \App\Models\SystemNotification::where('type', 'contrat_expire')
            ->where('notifiable_type', Moto::class)
            ->where('notifiable_id', $moto->id)
            ->where('created_at', '>=', now()->subDays(7))
            ->exists();

        if ($existante) return;

        NotificationService::notifierContratExpire($moto);
    }
}

