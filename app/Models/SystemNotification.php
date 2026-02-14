<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * SystemNotification = Notifications internes du système.
 * Gère les alertes pour tous les utilisateurs selon le cahier des charges.
 */
class SystemNotification extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'titre',
        'message',
        'icon',
        'couleur',
        'notifiable_type',
        'notifiable_id',
        'lu',
        'lu_at',
        'priorite',
        'expire_at',
    ];

    protected $casts = [
        'lu' => 'boolean',
        'lu_at' => 'datetime',
        'expire_at' => 'datetime',
    ];

    /**
     * L'utilisateur destinataire
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * L'entité concernée (polymorphique)
     */
    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Marquer comme lu
     */
    public function marquerCommeLu(): void
    {
        $this->update([
            'lu' => true,
            'lu_at' => now(),
        ]);
    }

    /**
     * Vérifier si la notification est expirée
     */
    public function getIsExpiredAttribute(): bool
    {
        if (!$this->expire_at) {
            return false;
        }
        return $this->expire_at->isPast();
    }

    /**
     * Scope pour les notifications non lues
     */
    public function scopeNonLues($query)
    {
        return $query->where('lu', false);
    }

    /**
     * Scope pour les notifications non expirées
     */
    public function scopeNonExpirees($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expire_at')
              ->orWhere('expire_at', '>', now());
        });
    }

    /**
     * Scope par priorité
     */
    public function scopeParPriorite($query, string $priorite)
    {
        return $query->where('priorite', $priorite);
    }

    /**
     * Scope pour les notifications urgentes
     */
    public function scopeUrgentes($query)
    {
        return $query->where('priorite', 'urgente');
    }

    /**
     * Créer une notification de retard de paiement
     */
    public static function notifierRetardPaiement(User $user, Versement $versement): self
    {
        return self::create([
            'user_id' => $user->id,
            'type' => 'retard_paiement',
            'titre' => 'Retard de paiement',
            'message' => "Votre versement du {$versement->date_versement->format('d/m/Y')} est en retard.",
            'icon' => 'exclamation-triangle',
            'couleur' => 'red',
            'notifiable_type' => Versement::class,
            'notifiable_id' => $versement->id,
            'priorite' => 'haute',
        ]);
    }

    /**
     * Créer une notification de validation de versement
     */
    public static function notifierValidationVersement(User $user, Versement $versement): self
    {
        return self::create([
            'user_id' => $user->id,
            'type' => 'validation_versement',
            'titre' => 'Versement validé',
            'message' => "Votre versement de {$versement->montant} FC a été validé.",
            'icon' => 'check-circle',
            'couleur' => 'green',
            'notifiable_type' => Versement::class,
            'notifiable_id' => $versement->id,
            'priorite' => 'normale',
        ]);
    }

    /**
     * Créer une notification d'arriérés critiques
     */
    public static function notifierArrieresCritiques(User $user, float $montant): self
    {
        return self::create([
            'user_id' => $user->id,
            'type' => 'arrieres_critiques',
            'titre' => 'Arriérés critiques',
            'message' => "Attention! Vos arriérés ont atteint {$montant} FC. Veuillez régulariser votre situation.",
            'icon' => 'alert',
            'couleur' => 'red',
            'priorite' => 'urgente',
        ]);
    }

    /**
     * Créer une notification d'accident
     */
    public static function notifierAccident(User $user, Accident $accident): self
    {
        return self::create([
            'user_id' => $user->id,
            'type' => 'accident',
            'titre' => 'Accident déclaré',
            'message' => "Un accident a été déclaré le {$accident->date_heure->format('d/m/Y à H:i')} à {$accident->lieu}.",
            'icon' => 'car-crash',
            'couleur' => 'orange',
            'notifiable_type' => Accident::class,
            'notifiable_id' => $accident->id,
            'priorite' => $accident->gravite === 'grave' ? 'urgente' : 'haute',
        ]);
    }

    /**
     * Créer une notification de ramassage prévu
     */
    public static function notifierRamassagePrevu(User $user, Tournee $tournee): self
    {
        return self::create([
            'user_id' => $user->id,
            'type' => 'ramassage_prevu',
            'titre' => 'Ramassage prévu',
            'message' => "Un ramassage est prévu dans votre zone le {$tournee->date->format('d/m/Y')}.",
            'icon' => 'truck',
            'couleur' => 'blue',
            'notifiable_type' => Tournee::class,
            'notifiable_id' => $tournee->id,
            'priorite' => 'normale',
            'expire_at' => $tournee->date->endOfDay(),
        ]);
    }
}

