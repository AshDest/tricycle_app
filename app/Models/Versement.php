<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Versement = Paiement journalier du motard au caissier (point de collecte).
 * Flux: Motard → Caissier (validation) → Collecteur (ramassage) → NTH (Admin)
 */
class Versement extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'motard_id',
        'moto_id',
        'montant',
        'montant_attendu',
        'date_versement',
        'heure_versement',
        'mode_paiement',
        'statut',
        'caissier_id',
        'validated_by_caissier_at',
        'valide_par_okami',
        'validated_by_okami_id',
        'validated_by_okami_at',
        'okami_notes',
        'collecte_id',
        'notes',
    ];

    protected $casts = [
        'montant' => 'decimal:2',
        'montant_attendu' => 'decimal:2',
        'date_versement' => 'date',
        'validated_by_caissier_at' => 'datetime',
        'validated_by_okami_at' => 'datetime',
        'valide_par_okami' => 'boolean',
    ];

    /**
     * Le motard qui a effectué le versement
     */
    public function motard(): BelongsTo
    {
        return $this->belongsTo(Motard::class);
    }

    /**
     * La moto concernée par le versement
     */
    public function moto(): BelongsTo
    {
        return $this->belongsTo(Moto::class);
    }

    /**
     * Le caissier (point de collecte) qui a reçu le versement
     */
    public function caissier(): BelongsTo
    {
        return $this->belongsTo(Caissier::class);
    }

    /**
     * L'utilisateur OKAMI qui a validé (si applicable)
     */
    public function validePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by_okami_id');
    }

    /**
     * La collecte associée (quand le collecteur ramasse l'argent)
     */
    public function collecte(): BelongsTo
    {
        return $this->belongsTo(Collecte::class);
    }

    /**
     * Calculer l'écart entre le montant versé et le montant attendu
     */
    public function getEcartAttribute(): float
    {
        return $this->montant - $this->montant_attendu;
    }

    /**
     * Vérifier si le versement est en retard
     */
    public function getIsEnRetardAttribute(): bool
    {
        return $this->statut === 'en_retard' ||
               ($this->statut === 'non_effectué' && $this->date_versement->isPast());
    }

    /**
     * Vérifier si le versement est validé par le caissier
     */
    public function getIsValideByCaissierAttribute(): bool
    {
        return !is_null($this->validated_by_caissier_at);
    }

    /**
     * Scope pour les versements non collectés (encore chez le caissier)
     */
    public function scopeNonCollecte($query)
    {
        return $query->whereNull('collecte_id');
    }

    /**
     * Scope pour les versements du jour
     */
    public function scopeAujourdhui($query)
    {
        return $query->whereDate('date_versement', today());
    }

    /**
     * Scope pour les versements en retard
     */
    public function scopeEnRetard($query)
    {
        return $query->where('statut', 'en_retard');
    }
}
