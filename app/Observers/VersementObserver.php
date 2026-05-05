<?php

namespace App\Observers;

use App\Models\Versement;
use App\Models\Caissier;
use App\Services\NotificationService;

class VersementObserver
{
    /**
     * Recalcule et persiste le solde_actuel du caissier lié au versement.
     * C'est la source unique de vérité pour le solde.
     */
    protected function syncSoldeCaissier(Versement $versement): void
    {
        $caissierId = $versement->caissier_id;
        if (!$caissierId) return;

        $caissier = Caissier::find($caissierId);
        if (!$caissier) return;

        $caissier->update([
            'solde_actuel' => $caissier->calculerSoldeActuel(),
        ]);
    }

    /**
     * Handle the Versement "created" event.
     */
    public function created(Versement $versement): void
    {
        // Recalcul du solde en caisse (source unique de vérité)
        $this->syncSoldeCaissier($versement);

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
        // Recalcul du solde en caisse quand le montant, statut ou caissier change
        if ($versement->isDirty(['montant', 'statut', 'caissier_id', 'collecte_id'])) {
            $this->syncSoldeCaissier($versement);

            // Si le caissier a changé, recalculer aussi l'ancien
            if ($versement->isDirty('caissier_id') && $versement->getOriginal('caissier_id')) {
                $ancienCaissier = Caissier::find($versement->getOriginal('caissier_id'));
                if ($ancienCaissier) {
                    $ancienCaissier->update([
                        'solde_actuel' => $ancienCaissier->calculerSoldeActuel(),
                    ]);
                }
            }
        }

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
     * Handle the Versement "deleted" event (soft delete).
     */
    public function deleted(Versement $versement): void
    {
        // Recalculer le solde après suppression
        $this->syncSoldeCaissier($versement);
    }

    /**
     * Handle the Versement "restored" event.
     */
    public function restored(Versement $versement): void
    {
        // Recalculer le solde après restauration
        $this->syncSoldeCaissier($versement);
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

