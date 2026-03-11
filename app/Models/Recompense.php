<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Recompense = Récompenses attribuées aux motards performants
 */
class Recompense extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'motard_id',
        'type',
        'categorie',
        'titre',
        'description',
        'montant_prime',
        'periode_debut',
        'periode_fin',
        'score_regularite',
        'score_securite',
        'score_versement',
        'score_total',
        'statut',
        'date_remise',
        'remis_par',
        'notes',
    ];

    protected $casts = [
        'montant_prime' => 'decimal:2',
        'periode_debut' => 'date',
        'periode_fin' => 'date',
        'date_remise' => 'date',
    ];

    /**
     * Le motard récompensé
     */
    public function motard(): BelongsTo
    {
        return $this->belongsTo(Motard::class);
    }

    /**
     * L'utilisateur qui a remis la récompense
     */
    public function remisPar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'remis_par');
    }

    /**
     * Types de récompenses disponibles
     */
    public static function getTypes(): array
    {
        return [
            'badge_bronze' => ['label' => 'Badge Bronze', 'icon' => 'award', 'color' => '#CD7F32'],
            'badge_argent' => ['label' => 'Badge Argent', 'icon' => 'award', 'color' => '#C0C0C0'],
            'badge_or' => ['label' => 'Badge Or', 'icon' => 'award', 'color' => '#FFD700'],
            'badge_diamant' => ['label' => 'Badge Diamant', 'icon' => 'gem', 'color' => '#B9F2FF'],
            'prime_mensuelle' => ['label' => 'Prime Mensuelle', 'icon' => 'dollar-sign', 'color' => '#28a745'],
            'prime_trimestrielle' => ['label' => 'Prime Trimestrielle', 'icon' => 'dollar-sign', 'color' => '#28a745'],
            'certificat' => ['label' => 'Certificat d\'Excellence', 'icon' => 'file-text', 'color' => '#6f42c1'],
            'bonus_special' => ['label' => 'Bonus Spécial', 'icon' => 'gift', 'color' => '#fd7e14'],
        ];
    }

    /**
     * Catégories de performance
     */
    public static function getCategories(): array
    {
        return [
            'regularite' => 'Régularité des Versements',
            'securite' => 'Sécurité (Sans Accident)',
            'versement_complet' => 'Versements Complets',
            'excellence' => 'Excellence Globale',
        ];
    }

    /**
     * Statuts possibles
     */
    public static function getStatuts(): array
    {
        return [
            'attribue' => 'Attribué',
            'remis' => 'Remis',
            'annule' => 'Annulé',
        ];
    }

    /**
     * Obtenir la couleur du badge
     */
    public function getBadgeColorAttribute(): string
    {
        return self::getTypes()[$this->type]['color'] ?? '#6c757d';
    }

    /**
     * Obtenir l'icône du badge
     */
    public function getBadgeIconAttribute(): string
    {
        return self::getTypes()[$this->type]['icon'] ?? 'award';
    }

    /**
     * Obtenir le label du type
     */
    public function getTypeLabelAttribute(): string
    {
        return self::getTypes()[$this->type]['label'] ?? $this->type;
    }

    /**
     * Vérifier si c'est une prime (avec montant)
     */
    public function getIsPrimeAttribute(): bool
    {
        return in_array($this->type, ['prime_mensuelle', 'prime_trimestrielle', 'bonus_special']);
    }
}
