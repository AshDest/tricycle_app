<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Zone = Zone géographique de collecte.
 * Utilisée pour organiser les tournées et les affectations.
 */
class Zone extends Model
{
    protected $fillable = [
        'nom',
        'description',
        'communes',
        'is_active',
    ];

    protected $casts = [
        'communes' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Les collecteurs assignés à cette zone
     */
    public function collecteurs(): BelongsToMany
    {
        return $this->belongsToMany(Collecteur::class, 'zone_collecteur')
            ->withPivot(['ordre_rotation', 'is_principal', 'is_active'])
            ->withTimestamps();
    }

    /**
     * Les caissiers de cette zone
     */
    public function caissiers(): BelongsToMany
    {
        return $this->belongsToMany(Caissier::class, 'zone_caissier')
            ->withTimestamps();
    }

    /**
     * Les motards dans cette zone
     */
    public function motards(): HasMany
    {
        return $this->hasMany(Motard::class, 'zone_affectation', 'nom');
    }

    /**
     * Les tournées dans cette zone
     */
    public function tournees(): HasMany
    {
        return $this->hasMany(Tournee::class, 'zone', 'nom');
    }

    /**
     * Obtenir le collecteur principal
     */
    public function getCollecteurPrincipal(): ?Collecteur
    {
        return $this->collecteurs()
            ->wherePivot('is_principal', true)
            ->wherePivot('is_active', true)
            ->first();
    }

    /**
     * Obtenir le prochain collecteur selon la rotation
     */
    public function getProchainCollecteur(): ?Collecteur
    {
        return $this->collecteurs()
            ->wherePivot('is_active', true)
            ->orderBy('pivot_ordre_rotation')
            ->first();
    }

    /**
     * Nombre de caissiers actifs dans la zone
     */
    public function getNombreCaissiersAttribute(): int
    {
        return $this->caissiers()->count();
    }

    /**
     * Nombre de motards actifs dans la zone
     */
    public function getNombreMotardsAttribute(): int
    {
        return $this->motards()->actif()->count();
    }

    /**
     * Scope pour les zones actives
     */
    public function scopeActif($query)
    {
        return $query->where('is_active', true);
    }
}

