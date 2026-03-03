<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Lavage extends Model
{
    use SoftDeletes;

    // Pourcentages de répartition pour les motos du système
    const PART_OKAMI_PERCENT = 20;
    const PART_CLEANER_PERCENT = 80;

    protected $fillable = [
        'numero_lavage',
        'cleaner_id',
        'moto_id',
        'is_externe',
        'plaque_externe',
        'proprietaire_externe',
        'telephone_externe',
        'type_lavage',
        'prix_base',
        'prix_final',
        'remise',
        'part_cleaner',
        'part_okami',
        'mode_paiement',
        'statut_paiement',
        'date_lavage',
        'notes',
    ];

    protected $casts = [
        'prix_base' => 'decimal:2',
        'prix_final' => 'decimal:2',
        'remise' => 'decimal:2',
        'part_cleaner' => 'decimal:2',
        'part_okami' => 'decimal:2',
        'is_externe' => 'boolean',
        'date_lavage' => 'datetime',
    ];

    /**
     * Boot du modèle
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($lavage) {
            // Générer le numéro de lavage
            if (empty($lavage->numero_lavage)) {
                $lavage->numero_lavage = self::generateNumeroLavage();
            }

            // Calculer la répartition
            $lavage->calculerRepartition();
        });

        static::updating(function ($lavage) {
            if ($lavage->isDirty(['prix_final', 'is_externe'])) {
                $lavage->calculerRepartition();
            }
        });
    }

    /**
     * Générer un numéro de lavage unique
     */
    public static function generateNumeroLavage(): string
    {
        $date = now()->format('Ymd');
        $count = self::whereDate('created_at', today())->count() + 1;
        return 'LAV-' . $date . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Calculer la répartition entre le laveur et OKAMI
     */
    public function calculerRepartition(): void
    {
        $prixFinal = $this->prix_final ?? $this->prix_base ?? 0;

        if ($this->is_externe) {
            // Moto externe: 100% pour le laveur
            $this->part_cleaner = $prixFinal;
            $this->part_okami = 0;
        } else {
            // Moto du système: 80% laveur, 20% OKAMI
            $this->part_okami = round($prixFinal * (self::PART_OKAMI_PERCENT / 100), 2);
            $this->part_cleaner = $prixFinal - $this->part_okami;
        }
    }

    /**
     * Relation avec le laveur
     */
    public function cleaner(): BelongsTo
    {
        return $this->belongsTo(Cleaner::class);
    }

    /**
     * Relation avec la moto (si interne)
     */
    public function moto(): BelongsTo
    {
        return $this->belongsTo(Moto::class);
    }

    /**
     * Obtenir la plaque d'immatriculation (interne ou externe)
     */
    public function getPlaqueAttribute(): string
    {
        if ($this->is_externe) {
            return $this->plaque_externe ?? 'N/A';
        }
        return $this->moto?->plaque_immatriculation ?? 'N/A';
    }

    /**
     * Obtenir le nom du propriétaire (interne ou externe)
     */
    public function getProprietaireNomAttribute(): string
    {
        if ($this->is_externe) {
            return $this->proprietaire_externe ?? 'Externe';
        }
        return $this->moto?->proprietaire?->user?->name ?? 'N/A';
    }

    /**
     * Obtenir le libellé du type de lavage
     */
    public function getTypeLavageLabelAttribute(): string
    {
        return match ($this->type_lavage) {
            'simple' => 'Lavage Simple',
            'complet' => 'Lavage Complet',
            'premium' => 'Lavage Premium',
            default => 'Lavage',
        };
    }

    /**
     * Scope pour les lavages du jour
     */
    public function scopeAujourdhui($query)
    {
        return $query->whereDate('date_lavage', today());
    }

    /**
     * Scope pour les lavages internes (motos du système)
     */
    public function scopeInternes($query)
    {
        return $query->where('is_externe', false);
    }

    /**
     * Scope pour les lavages externes
     */
    public function scopeExternes($query)
    {
        return $query->where('is_externe', true);
    }

    /**
     * Scope pour les lavages payés
     */
    public function scopePayes($query)
    {
        return $query->where('statut_paiement', 'payé');
    }

    /**
     * Obtenir le prix d'un type de lavage depuis les paramètres
     */
    public static function getPrixLavage(string $type): float
    {
        return match ($type) {
            'simple' => (float) SystemSetting::get('prix_lavage_simple', 2000),
            'complet' => (float) SystemSetting::get('prix_lavage_complet', 3500),
            'premium' => (float) SystemSetting::get('prix_lavage_premium', 5000),
            default => 2000,
        };
    }
}

