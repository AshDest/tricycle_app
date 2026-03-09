<?php

namespace App\Observers;

use App\Models\Payment;
use App\Services\NotificationService;

class PaymentObserver
{
    /**
     * Handle the Payment "created" event.
     */
    public function created(Payment $payment): void
    {
        // Si c'est une demande de paiement (statut en_attente)
        if ($payment->statut === 'en_attente') {
            // Notifier OKAMI d'une nouvelle demande
            NotificationService::notifierOkamiDemandePaiement($payment);
        }
    }

    /**
     * Handle the Payment "updated" event.
     */
    public function updated(Payment $payment): void
    {
        // Si le paiement est effectué (statut passe à payé/paye)
        if ($payment->isDirty('statut') && in_array($payment->statut, ['paye', 'payé'])) {
            NotificationService::notifierProprietairePaiement($payment);
        }

        // Si le paiement est rejeté
        if ($payment->isDirty('statut') && in_array($payment->statut, ['rejete', 'rejeté'])) {
            if ($payment->proprietaire && $payment->proprietaire->user) {
                \App\Models\SystemNotification::create([
                    'user_id' => $payment->proprietaire->user->id,
                    'type' => 'paiement_rejete',
                    'titre' => 'Demande de paiement rejetée',
                    'message' => "Votre demande de paiement de " . number_format($payment->total_du) . " FC a été rejetée.",
                    'icon' => 'x-circle',
                    'couleur' => 'danger',
                    'notifiable_type' => Payment::class,
                    'notifiable_id' => $payment->id,
                    'priorite' => 'haute',
                ]);
            }
        }

        // Si le paiement est approuvé (en attente de traitement)
        if ($payment->isDirty('statut') && $payment->statut === 'approuve') {
            if ($payment->proprietaire && $payment->proprietaire->user) {
                \App\Models\SystemNotification::create([
                    'user_id' => $payment->proprietaire->user->id,
                    'type' => 'paiement_approuve',
                    'titre' => 'Demande de paiement approuvée',
                    'message' => "Votre demande de paiement de " . number_format($payment->total_du) . " FC a été approuvée et sera traitée prochainement.",
                    'icon' => 'check-circle',
                    'couleur' => 'success',
                    'notifiable_type' => Payment::class,
                    'notifiable_id' => $payment->id,
                    'priorite' => 'normale',
                ]);
            }
        }
    }
}

