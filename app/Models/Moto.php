<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

/**
 * Moto = Moto-tricycle.
 * Appartient à un propriétaire et est assignée à un motard.
 * Le contrat détermine si la moto peut être active dans le système.
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
        'contrat_debut',
        'contrat_fin',
        'contrat_numero',
        'contrat_notes',
        'marque',
        'modele',
        'annee_fabrication',
        'couleur',
    ];

    protected $casts = [
        'montant_journalier_attendu' => 'decimal:2',
        'contrat_debut' => 'date',
        'contrat_fin' => 'date',
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

    // ==================== GESTION DU CONTRAT ====================

    /**
     * Vérifier si le contrat est actif (date actuelle entre début et fin)
     */
    public function getContratActifAttribute(): bool
    {
        if (!$this->contrat_debut || !$this->contrat_fin) {
            return false;
        }

        $today = Carbon::today();
        return $today->gte($this->contrat_debut) && $today->lte($this->contrat_fin);
    }

    /**
     * Vérifier si le contrat a expiré
     */
    public function getContratExpireAttribute(): bool
    {
        if (!$this->contrat_fin) {
            return false;
        }

        return Carbon::today()->gt($this->contrat_fin);
    }

    /**
     * Vérifier si le contrat n'a pas encore commencé
     */
    public function getContratPasCommenceAttribute(): bool
    {
        if (!$this->contrat_debut) {
            return true;
        }

        return Carbon::today()->lt($this->contrat_debut);
    }

    /**
     * Obtenir le nombre de jours restants du contrat
     */
    public function getJoursRestantsContratAttribute(): ?int
    {
        if (!$this->contrat_fin || $this->contrat_expire) {
            return null;
        }

        return Carbon::today()->diffInDays($this->contrat_fin, false);
    }

    /**
     * Obtenir le statut du contrat
     */
    public function getStatutContratAttribute(): string
    {
        if (!$this->contrat_debut || !$this->contrat_fin) {
            return 'non_defini';
        }

        if ($this->contrat_pas_commence) {
            return 'pas_commence';
        }

        if ($this->contrat_expire) {
            return 'expire';
        }

        $joursRestants = $this->jours_restants_contrat;
        if ($joursRestants !== null && $joursRestants <= 30) {
            return 'bientot_expire';
        }

        return 'actif';
    }

    /**
     * Vérifier si la moto peut être opérationnelle (contrat actif + statut actif)
     */
    public function getEstOperationnelleAttribute(): bool
    {
        return $this->statut === 'actif' && $this->contrat_actif;
    }

    /**
     * Renouveler le contrat
     */
    public function renouvelerContrat(Carbon $nouvelleDebut, Carbon $nouvelleFin, ?string $notes = null): void
    {
        $this->update([
            'contrat_debut' => $nouvelleDebut,
            'contrat_fin' => $nouvelleFin,
            'contrat_notes' => $notes ?? $this->contrat_notes,
        ]);
    }

    /**
     * Scope pour les motos avec contrat actif
     */
    public function scopeContratActif($query)
    {
        $today = Carbon::today();
        return $query->whereNotNull('contrat_debut')
                     ->whereNotNull('contrat_fin')
                     ->where('contrat_debut', '<=', $today)
                     ->where('contrat_fin', '>=', $today);
    }

    /**
     * Scope pour les motos avec contrat expiré
     */
    public function scopeContratExpire($query)
    {
        return $query->whereNotNull('contrat_fin')
                     ->where('contrat_fin', '<', Carbon::today());
    }

    /**
     * Scope pour les motos avec contrat bientôt expiré (30 jours)
     */
    public function scopeContratBientotExpire($query, int $jours = 30)
    {
        $today = Carbon::today();
        return $query->whereNotNull('contrat_fin')
                     ->where('contrat_fin', '>=', $today)
                     ->where('contrat_fin', '<=', $today->copy()->addDays($jours));
    }

    /**
     * Scope pour les motos sans contrat défini
     */
    public function scopeSansContrat($query)
    {
        return $query->whereNull('contrat_debut')
                     ->orWhereNull('contrat_fin');
    }

    // ==================== FIN GESTION DU CONTRAT ====================

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
