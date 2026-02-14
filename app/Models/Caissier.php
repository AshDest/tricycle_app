<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Caissier = Point de collecte terrain où les motards versent leur argent quotidien.
 * Le caissier réceptionne l'argent des motards et le conserve jusqu'au passage du collecteur.
 */
class Caissier extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'numero_identifiant',
        'nom_point_collecte',
        'zone',
        'adresse',
        'telephone',
        'solde_actuel',
        'is_active',
    ];

    protected $casts = [
        'solde_actuel' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * L'utilisateur associé au caissier
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Les versements reçus par ce caissier
     */
    public function versements(): HasMany
    {
        return $this->hasMany(Versement::class);
    }

    /**
     * Les collectes effectuées chez ce caissier
     */
    public function collectes(): HasMany
    {
        return $this->hasMany(Collecte::class);
    }

    /**
     * Calculer le solde actuel basé sur les versements non encore collectés
     */
    public function calculerSoldeActuel(): float
    {
        return $this->versements()
            ->whereNull('collecte_id')
            ->where('statut', '!=', 'non_effectué')
            ->sum('montant');
    }

    /**
     * Scope pour les caissiers actifs
     */
    public function scopeActif($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope par zone
     */
    public function scopeParZone($query, string $zone)
    {
        return $query->where('zone', $zone);
    }
}

