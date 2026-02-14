<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Motard = Conducteur de moto-tricycle.
 * Accès limité: consultation de son statut et historique uniquement.
 * NE PEUT PAS modifier ses données ou encoder des versements.
 */
class Motard extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'numero_identifiant',
        'licence_numero',
        'document_identite_url',
        'zone_affectation',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * L'utilisateur associé au motard
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * La moto actuellement assignée au motard
     */
    public function motoActuelle(): HasOne
    {
        return $this->hasOne(Moto::class)->where('statut', 'actif');
    }

    /**
     * Historique de toutes les motos conduites
     */
    public function motos(): HasMany
    {
        return $this->hasMany(Moto::class);
    }

    /**
     * Tous les versements du motard
     */
    public function versements(): HasMany
    {
        return $this->hasMany(Versement::class);
    }

    /**
     * Les maintenances déclarées par ce motard
     */
    public function maintenances(): HasMany
    {
        return $this->hasMany(Maintenance::class);
    }

    /**
     * Les accidents déclarés par ce motard
     */
    public function accidents(): HasMany
    {
        return $this->hasMany(Accident::class);
    }

    /**
     * Obtenir le statut du jour
     */
    public function getStatutDuJourAttribute(): string
    {
        $versementAujourdhui = $this->versements()
            ->whereDate('date_versement', today())
            ->first();

        if (!$versementAujourdhui) {
            return 'non_effectué';
        }

        return $versementAujourdhui->statut;
    }

    /**
     * Obtenir le récapitulatif de performance
     */
    public function getPerformanceRecap(): array
    {
        $versements = $this->versements;

        return [
            'total_jours_payes' => $versements->where('statut', 'payé')->count(),
            'total_jours_en_retard' => $versements->where('statut', 'en_retard')->count(),
            'total_jours_partiels' => $versements->where('statut', 'partiellement_payé')->count(),
            'montant_cumule_arrieres' => $this->getMontantArrieres(),
        ];
    }

    /**
     * Calculer le montant total des arriérés
     */
    public function getMontantArrieres(): float
    {
        return $this->versements()
            ->where('statut', '!=', 'payé')
            ->get()
            ->sum(function ($v) {
                return $v->montant_attendu - $v->montant;
            });
    }

    /**
     * Vérifier si le motard a des arriérés critiques
     */
    public function hasArrieresCritiques(float $seuil = 50000): bool
    {
        return $this->getMontantArrieres() >= $seuil;
    }

    /**
     * Scope pour les motards actifs
     */
    public function scopeActif($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope pour les motards par zone
     */
    public function scopeParZone($query, string $zone)
    {
        return $query->where('zone_affectation', $zone);
    }

    /**
     * Scope pour les motards en retard
     */
    public function scopeEnRetard($query)
    {
        return $query->whereHas('versements', function ($q) {
            $q->where('statut', 'en_retard');
        });
    }

    /**
     * Scope pour les motards avec arriérés critiques
     */
    public function scopeArrieresCritiques($query, float $seuil = 50000)
    {
        return $query->get()->filter(function ($motard) use ($seuil) {
            return $motard->getMontantArrieres() >= $seuil;
        });
    }
}
