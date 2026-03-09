<?php

namespace App\Observers;

use App\Models\Versement;
use App\Services\NotificationService;

class VersementObserver
{
    /**
     * Handle the Versement "created" event.
     */
    public function created(Versement $versement): void
    {
        // Notifier le propriétaire du versement
        NotificationService::notifierProprietaireVersement($versement);

        // Notifier le motard de la validation
        if ($versement->motard) {
            NotificationService::notifierMotardVersementValide($versement->motard, $versement);
        }

        // Vérifier les arriérés du motard
        $this->verifierArrieres($versement);
    }

    /**
     * Handle the Versement "updated" event.
     */
    public function updated(Versement $versement): void
    {
        // Si le statut passe à "en_retard"
        if ($versement->isDirty('statut') && $versement->statut === 'en_retard') {
            if ($versement->motard) {
                NotificationService::notifierMotardRetardPaiement($versement->motard, $versement);
            }
        }

        // Vérifier les arriérés après mise à jour
        if ($versement->isDirty('arrieres') || $versement->isDirty('montant')) {
            $this->verifierArrieres($versement);
        }
    }

    /**
     * Vérifier si les arriérés cumulés sont critiques
     */
    protected function verifierArrieres(Versement $versement): void
    {
        if (!$versement->motard) return;

        $totalArrieres = Versement::where('motard_id', $versement->motard_id)
            ->where('arrieres', '>', 0)
            ->sum('arrieres');

        // Seuil critique: 30 000 FC (paramétrable)
        $seuilCritique = 30000;

        if ($totalArrieres >= $seuilCritique) {
            // Notifier le motard
            NotificationService::notifierMotardArrieresCritiques($versement->motard, $totalArrieres);

            // Notifier OKAMI si arriérés > 50 000 FC
            if ($totalArrieres >= 50000) {
                NotificationService::notifierOkamiArrieres($versement->motard, $totalArrieres);
            }
        }
    }
}

