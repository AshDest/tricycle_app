<?php

namespace App\Observers;

use App\Models\Maintenance;
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

