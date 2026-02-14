<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Collecte = Ramassage de l'argent par le collecteur chez un CAISSIER (point de collecte).
 * Le collecteur visite plusieurs caissiers pendant sa tournée.
 */
class Collecte extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tournee_id',
        'caissier_id',
        'montant_attendu',
        'montant_collecte',
        'ecart',
        'statut',
        'signature_base64',
        'photo_cash_url',
        'recu_url',
        'heure_arrivee',
        'heure_depart',
        'notes_anomalies',
        'commentaire_caissier',
    ];

    protected $casts = [
        'montant_attendu' => 'decimal:2',
        'montant_collecte' => 'decimal:2',
        'ecart' => 'decimal:2',
        'heure_arrivee' => 'datetime',
        'heure_depart' => 'datetime',
    ];

    /**
     * La tournée à laquelle appartient cette collecte
     */
    public function tournee(): BelongsTo
    {
        return $this->belongsTo(Tournee::class);
    }

    /**
     * Le caissier (point de collecte) visité
     */
    public function caissier(): BelongsTo
    {
        return $this->belongsTo(Caissier::class);
    }

    /**
     * Les versements associés à cette collecte
     */
    public function versements(): HasMany
    {
        return $this->hasMany(Versement::class);
    }

    /**
     * Calculer l'écart automatiquement
     */
    public function calculerEcart(): float
    {
        if (is_null($this->montant_collecte)) {
            return 0;
        }
        return $this->montant_collecte - $this->montant_attendu;
    }

    /**
     * Vérifier si la collecte est réussie
     */
    public function getIsReussieAttribute(): bool
    {
        return $this->statut === 'reussie';
    }

    /**
     * Vérifier s'il y a un écart
     */
    public function getHasEcartAttribute(): bool
    {
        return $this->ecart != 0;
    }

    /**
     * Durée de la visite en minutes
     */
    public function getDureeVisiteAttribute(): ?int
    {
        if ($this->heure_arrivee && $this->heure_depart) {
            return $this->heure_arrivee->diffInMinutes($this->heure_depart);
        }
        return null;
    }

    /**
     * Scope pour les collectes réussies
     */
    public function scopeReussie($query)
    {
        return $query->where('statut', 'reussie');
    }

    /**
     * Scope pour les collectes en litige
     */
    public function scopeEnLitige($query)
    {
        return $query->where('statut', 'en_litige');
    }
}
