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
     * Alias pour motoActuelle - la moto assignée au motard
     */
    public function moto(): HasOne
    {
        return $this->hasOne(Moto::class);
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
            'total_jours_payes' => $versements->whereIn('statut', ['paye', 'payé'])->count(),
            'total_jours_en_retard' => $versements->whereIn('statut', ['en_retard', 'non_effectue'])->count(),
            'total_jours_partiels' => $versements->whereIn('statut', ['partiel', 'partiellement_payé'])->count(),
            'montant_cumule_arrieres' => $this->getTotalArrieres(),
            'total_verse' => $versements->sum('montant'),
            'total_attendu' => $versements->sum('montant_attendu'),
        ];
    }

    /**
     * Calculer le montant total des arriérés cumulés
     */
    public function getTotalArrieres(): float
    {
        return $this->versements()
            ->selectRaw('SUM(GREATEST(0, COALESCE(montant_attendu, 0) - COALESCE(montant, 0))) as total')
            ->value('total') ?? 0;
    }

    /**
     * Alias pour getTotalArrieres (compatibilité)
     */
    public function getMontantArrieres(): float
    {
        return $this->getTotalArrieres();
    }

    /**
     * Obtenir les arriérés du mois en cours
     */
    public function getArrieresMoisEnCours(): float
    {
        return $this->versements()
            ->whereMonth('date_versement', now()->month)
            ->whereYear('date_versement', now()->year)
            ->selectRaw('SUM(GREATEST(0, COALESCE(montant_attendu, 0) - COALESCE(montant, 0))) as total')
            ->value('total') ?? 0;
    }

    /**
     * Obtenir les arriérés pour une période donnée
     */
    public function getArrieresPeriode($dateDebut, $dateFin): float
    {
        return $this->versements()
            ->whereBetween('date_versement', [$dateDebut, $dateFin])
            ->selectRaw('SUM(GREATEST(0, COALESCE(montant_attendu, 0) - COALESCE(montant, 0))) as total')
            ->value('total') ?? 0;
    }

    /**
     * Obtenir le taux de paiement global (%)
     */
    public function getTauxPaiementAttribute(): float
    {
        $totalAttendu = $this->versements()->sum('montant_attendu');
        if ($totalAttendu <= 0) {
            return 100;
        }
        $totalVerse = $this->versements()->sum('montant');
        return round(($totalVerse / $totalAttendu) * 100, 1);
    }

    /**
     * Obtenir le statut d'arriéré
     */
    public function getStatutArriereAttribute(): string
    {
        $arrieres = $this->getTotalArrieres();

        if ($arrieres <= 0) {
            return 'ok';
        } elseif ($arrieres < 25000) {
            return 'faible';
        } elseif ($arrieres < 50000) {
            return 'moyen';
        } elseif ($arrieres < 100000) {
            return 'eleve';
        } else {
            return 'critique';
        }
    }

    /**
     * Vérifier si le motard a des arriérés
     */
    public function getHasArriereAttribute(): bool
    {
        return $this->getTotalArrieres() > 0;
    }

    /**
     * Attribut pour accéder facilement aux arriérés totaux
     */
    public function getTotalArrieresAttribute(): float
    {
        return $this->getTotalArrieres();
    }

    /**
     * Vérifier si le motard a des arriérés critiques
     */
    public function hasArrieresCritiques(float $seuil = 50000): bool
    {
        return $this->getTotalArrieres() >= $seuil;
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
            $q->whereIn('statut', ['en_retard', 'non_effectue']);
        });
    }

    /**
     * Scope pour les motards avec arriérés
     */
    public function scopeAvecArrieres($query)
    {
        return $query->whereHas('versements', function ($q) {
            $q->whereRaw('montant < montant_attendu');
        });
    }

    /**
     * Scope pour les motards avec arriérés critiques
     */
    public function scopeArrieresCritiques($query, float $seuil = 50000)
    {
        return $query->get()->filter(function ($motard) use ($seuil) {
            return $motard->getTotalArrieres() >= $seuil;
        });
    }
}
