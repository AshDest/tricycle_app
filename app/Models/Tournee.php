<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Tournée = Itinéraire journalier d'un collecteur pour ramasser l'argent chez les caissiers.
 */
class Tournee extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'collecteur_id',
        'date',
        'zone',
        'statut',
        'heure_debut_prevue',
        'heure_fin_prevue',
        'heure_debut_reelle',
        'heure_fin_reelle',
        'presence_confirmee',
        'presence_confirmee_at',
        'total_attendu',
        'total_encaisse',
        'ecart_total',
        'transmis_nth',
        'transmis_nth_at',
        'valide_par_nth_id',
        'valide_par_nth_at',
        'anomalies_notes',
        'latitude_debut',
        'longitude_debut',
        'latitude_fin',
        'longitude_fin',
    ];

    protected $casts = [
        'date' => 'date',
        'heure_debut_reelle' => 'datetime',
        'heure_fin_reelle' => 'datetime',
        'presence_confirmee' => 'boolean',
        'presence_confirmee_at' => 'datetime',
        'total_attendu' => 'decimal:2',
        'total_encaisse' => 'decimal:2',
        'ecart_total' => 'decimal:2',
        'transmis_nth' => 'boolean',
        'transmis_nth_at' => 'datetime',
        'valide_par_nth_at' => 'datetime',
        'latitude_debut' => 'decimal:8',
        'longitude_debut' => 'decimal:8',
        'latitude_fin' => 'decimal:8',
        'longitude_fin' => 'decimal:8',
    ];

    /**
     * Le collecteur qui effectue la tournée
     */
    public function collecteur(): BelongsTo
    {
        return $this->belongsTo(Collecteur::class);
    }

    /**
     * Les collectes effectuées pendant cette tournée
     */
    public function collectes(): HasMany
    {
        return $this->hasMany(Collecte::class);
    }

    /**
     * L'utilisateur NTH qui a validé la tournée
     */
    public function valideParNth(): BelongsTo
    {
        return $this->belongsTo(User::class, 'valide_par_nth_id');
    }

    /**
     * Calculer le total attendu basé sur les collectes
     */
    public function calculerTotalAttendu(): float
    {
        return $this->collectes()->sum('montant_attendu');
    }

    /**
     * Calculer le total encaissé
     */
    public function calculerTotalEncaisse(): float
    {
        return $this->collectes()->sum('montant_collecte') ?? 0;
    }

    /**
     * Calculer l'écart total
     */
    public function calculerEcartTotal(): float
    {
        return $this->calculerTotalEncaisse() - $this->calculerTotalAttendu();
    }

    /**
     * Vérifier si la tournée est terminée
     */
    public function getIsTermineeAttribute(): bool
    {
        return $this->statut === 'terminee';
    }

    /**
     * Vérifier si la tournée est en retard
     */
    public function getIsEnRetardAttribute(): bool
    {
        return $this->statut === 'en_retard';
    }

    /**
     * Nombre de caissiers visités
     */
    public function getNombreCaissiersVisitesAttribute(): int
    {
        return $this->collectes()->whereIn('statut', ['reussie', 'partielle'])->count();
    }

    /**
     * Confirmer la présence du collecteur au début de la tournée
     */
    public function confirmerPresence(): void
    {
        $this->update([
            'presence_confirmee' => true,
            'presence_confirmee_at' => now(),
            'statut' => 'en_cours',
            'heure_debut_reelle' => now(),
        ]);
    }

    /**
     * Terminer la tournée
     */
    public function terminer(): void
    {
        $this->update([
            'statut' => 'terminee',
            'heure_fin_reelle' => now(),
            'total_encaisse' => $this->calculerTotalEncaisse(),
            'ecart_total' => $this->calculerEcartTotal(),
        ]);
    }

    /**
     * Transmettre à NTH
     */
    public function transmettreNth(): void
    {
        $this->update([
            'transmis_nth' => true,
            'transmis_nth_at' => now(),
        ]);
    }

    /**
     * Scope pour les tournées du jour
     */
    public function scopeAujourdhui($query)
    {
        return $query->whereDate('date', today());
    }

    /**
     * Scope par statut
     */
    public function scopeParStatut($query, string $statut)
    {
        return $query->where('statut', $statut);
    }

    /**
     * Scope pour les tournées non transmises
     */
    public function scopeNonTransmises($query)
    {
        return $query->where('transmis_nth', false);
    }
}
