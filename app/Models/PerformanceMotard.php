<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * PerformanceMotard = Historique des performances mensuelles des motards
 */
class PerformanceMotard extends Model
{
    protected $table = 'performance_motards';

    protected $fillable = [
        'motard_id',
        'mois',
        'annee',
        'jours_travailles',
        'versements_a_temps',
        'versements_en_retard',
        'total_verse',
        'total_attendu',
        'arrieres_cumules',
        'accidents_total',
        'accidents_mineurs',
        'accidents_moderes',
        'accidents_graves',
        'score_regularite',
        'score_securite',
        'score_versement',
        'score_total',
        'rang_mensuel',
        'badge',
    ];

    protected $casts = [
        'total_verse' => 'decimal:2',
        'total_attendu' => 'decimal:2',
        'arrieres_cumules' => 'decimal:2',
    ];

    /**
     * Le motard concerné
     */
    public function motard(): BelongsTo
    {
        return $this->belongsTo(Motard::class);
    }

    /**
     * Obtenir le nom du mois
     */
    public function getNomMoisAttribute(): string
    {
        $mois = [
            1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
            5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
            9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
        ];
        return $mois[$this->mois] ?? '';
    }

    /**
     * Obtenir la période formatée
     */
    public function getPeriodeAttribute(): string
    {
        return $this->nom_mois . ' ' . $this->annee;
    }

    /**
     * Obtenir la couleur du badge
     */
    public function getBadgeColorAttribute(): string
    {
        return match($this->badge) {
            'bronze' => '#CD7F32',
            'argent' => '#C0C0C0',
            'or' => '#FFD700',
            'diamant' => '#B9F2FF',
            default => '#6c757d',
        };
    }

    /**
     * Obtenir la classe CSS du badge
     */
    public function getBadgeClassAttribute(): string
    {
        return match($this->badge) {
            'bronze' => 'bg-warning text-dark',
            'argent' => 'bg-secondary text-white',
            'or' => 'bg-warning text-dark',
            'diamant' => 'bg-info text-white',
            default => 'bg-light text-muted',
        };
    }

    /**
     * Obtenir la classe CSS pour le score
     */
    public function getScoreClassAttribute(): string
    {
        if ($this->score_total >= 90) return 'text-success';
        if ($this->score_total >= 70) return 'text-primary';
        if ($this->score_total >= 50) return 'text-warning';
        return 'text-danger';
    }

    /**
     * Vérifier si le motard mérite une récompense
     */
    public function meriteRecompense(): bool
    {
        return $this->badge !== 'aucun';
    }

    /**
     * Déterminer le badge en fonction des scores
     */
    public static function determinerBadge(int $scoreTotal): string
    {
        if ($scoreTotal >= 95) return 'diamant';
        if ($scoreTotal >= 85) return 'or';
        if ($scoreTotal >= 70) return 'argent';
        if ($scoreTotal >= 50) return 'bronze';
        return 'aucun';
    }
}
