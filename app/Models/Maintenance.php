<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Maintenance = Suivi technique complet des motos-tricycles.
 * Types: Préventive (révision, vidange), Corrective (réparation), Remplacement pièces.
 * Seul l'Admin NTH peut valider/modifier/clôturer une maintenance.
 */
class Maintenance extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'moto_id',
        'motard_id',
        'type',
        'description',
        'photo_avant_url',
        'photo_apres_url',
        'date_intervention',
        'technicien_garage_nom',
        'technicien_telephone',
        'garage_adresse',
        'prochain_entretien',
        'cout_pieces',
        'cout_main_oeuvre',
        'facture_url',
        'qui_a_paye',
        'statut',
        'valide_par',
        'valide_at',
        'notes',
    ];

    protected $casts = [
        'cout_pieces' => 'decimal:2',
        'cout_main_oeuvre' => 'decimal:2',
        'date_intervention' => 'datetime',
        'prochain_entretien' => 'date',
        'valide_at' => 'datetime',
    ];

    /**
     * La moto concernée
     */
    public function moto(): BelongsTo
    {
        return $this->belongsTo(Moto::class);
    }

    /**
     * Le motard concerné (si applicable)
     */
    public function motard(): BelongsTo
    {
        return $this->belongsTo(Motard::class);
    }

    /**
     * L'admin qui a validé
     */
    public function validePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'valide_par');
    }

    /**
     * Calculer le coût total de l'intervention
     */
    public function getCoutTotalAttribute(): float
    {
        return ($this->cout_pieces ?? 0) + ($this->cout_main_oeuvre ?? 0);
    }

    /**
     * Vérifier si la maintenance est terminée
     */
    public function getIsTermineeAttribute(): bool
    {
        return $this->statut === 'termine';
    }

    /**
     * Vérifier si un entretien est bientôt dû
     */
    public function getEntretienProchainAttribute(): bool
    {
        if (!$this->prochain_entretien) {
            return false;
        }
        return $this->prochain_entretien->diffInDays(now()) <= 7;
    }

    /**
     * Valider la maintenance (Admin NTH uniquement)
     */
    public function valider(int $userId): void
    {
        $this->update([
            'statut' => 'termine',
            'valide_par' => $userId,
            'valide_at' => now(),
        ]);
    }

    /**
     * Scope par type de maintenance
     */
    public function scopeParType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope pour les maintenances en attente
     */
    public function scopeEnAttente($query)
    {
        return $query->where('statut', 'en_attente');
    }

    /**
     * Scope pour les maintenances en cours
     */
    public function scopeEnCours($query)
    {
        return $query->where('statut', 'en_cours');
    }

    /**
     * Les types de maintenance disponibles
     */
    public static function getTypes(): array
    {
        return [
            'preventive' => 'Préventive (révision, vidange, réglage)',
            'corrective' => 'Corrective (réparation après panne)',
            'remplacement' => 'Remplacement de pièces',
        ];
    }

    /**
     * Les payeurs possibles
     */
    public static function getPayeurs(): array
    {
        return [
            'motard' => 'Motard',
            'proprietaire' => 'Propriétaire',
            'nth' => 'NTH Sarl',
            'okami' => 'OKAMI',
        ];
    }
}
