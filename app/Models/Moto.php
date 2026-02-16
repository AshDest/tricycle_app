<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Moto = Moto-tricycle.
 * Appartient à un propriétaire et est assignée à un motard.
 */
class Moto extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'numero_matricule',
        'plaque_immatriculation',
        'numero_chassis',
        'proprietaire_id',
        'motard_id',
        'photo_url',
        'document_administratif_url',
        'statut',
        'montant_journalier_attendu',
    ];

    protected $casts = [
        'montant_journalier_attendu' => 'decimal:2',
    ];

    /**
     * Le propriétaire de la moto
     */
    public function proprietaire(): BelongsTo
    {
        return $this->belongsTo(Proprietaire::class);
    }

    /**
     * Le motard actuellement assigné
     */
    public function motard(): BelongsTo
    {
        return $this->belongsTo(Motard::class);
    }

    /**
     * Alias pour le motard actuellement assigné (pour compatibilité)
     */
    public function motardActuel(): BelongsTo
    {
        return $this->belongsTo(Motard::class, 'motard_id');
    }

    /**
     * Tous les versements liés à cette moto
     */
    public function versements(): HasMany
    {
        return $this->hasMany(Versement::class);
    }

    /**
     * Toutes les maintenances de cette moto
     */
    public function maintenances(): HasMany
    {
        return $this->hasMany(Maintenance::class);
    }

    /**
     * Tous les accidents impliquant cette moto
     */
    public function accidents(): HasMany
    {
        return $this->hasMany(Accident::class);
    }

    /**
     * Vérifier si la moto est assignée à un motard
     */
    public function getIsAssigneeAttribute(): bool
    {
        return !is_null($this->motard_id);
    }

    /**
     * Calculer le coût total de maintenance
     */
    public function getCoutTotalMaintenanceAttribute(): float
    {
        return $this->maintenances->sum('cout_total');
    }

    /**
     * Calculer le coût total des accidents
     */
    public function getCoutTotalAccidentsAttribute(): float
    {
        return $this->accidents->sum('estimation_cout');
    }

    /**
     * Obtenir les statistiques financières de la moto
     */
    public function getStatistiquesFinancieres(): array
    {
        $versements = $this->versements;

        return [
            'total_versements' => $versements->sum('montant'),
            'total_attendu' => $versements->sum('montant_attendu'),
            'arrieres' => $versements->sum('montant_attendu') - $versements->sum('montant'),
            'cout_maintenance' => $this->cout_total_maintenance,
            'cout_accidents' => $this->cout_total_accidents,
        ];
    }

    /**
     * Assigner un motard à cette moto
     */
    public function assignerMotard(Motard $motard): void
    {
        $this->update(['motard_id' => $motard->id]);
    }

    /**
     * Retirer l'assignation du motard
     */
    public function retirerMotard(): void
    {
        $this->update(['motard_id' => null]);
    }

    /**
     * Mettre en maintenance
     */
    public function mettreEnMaintenance(): void
    {
        $this->update(['statut' => 'maintenance']);
    }

    /**
     * Activer la moto
     */
    public function activer(): void
    {
        $this->update(['statut' => 'actif']);
    }

    /**
     * Suspendre la moto
     */
    public function suspendre(): void
    {
        $this->update(['statut' => 'suspendu']);
    }

    /**
     * Scope pour les motos actives
     */
    public function scopeActif($query)
    {
        return $query->where('statut', 'actif');
    }

    /**
     * Scope pour les motos non assignées
     */
    public function scopeNonAssignee($query)
    {
        return $query->whereNull('motard_id');
    }

    /**
     * Scope pour les motos d'un propriétaire
     */
    public function scopeDeProprietaire($query, int $proprietaireId)
    {
        return $query->where('proprietaire_id', $proprietaireId);
    }
}
