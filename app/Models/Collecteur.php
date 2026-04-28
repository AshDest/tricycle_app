<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Collecteur = Agent terrain qui récupère l'argent chez les caissiers.
 * Il effectue des tournées quotidiennes et transmet l'argent à NTH (Admin).
 * Tout l'argent collecté va dans une caisse unique (solde_caisse).
 */
class Collecteur extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'numero_identifiant',
        'zone_affectation',
        'solde_caisse',
        'solde_part_okami',
        'solde_part_proprietaire',
        'telephone',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'solde_caisse' => 'decimal:2',
        'solde_part_okami' => 'decimal:2',
        'solde_part_proprietaire' => 'decimal:2',
    ];

    /**
     * L'utilisateur associé au collecteur
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Les tournées effectuées par ce collecteur
     */
    public function tournees(): HasMany
    {
        return $this->hasMany(Tournee::class);
    }

    /**
     * Obtenir la tournée du jour
     */
    public function tourneeAujourdhui()
    {
        return $this->tournees()->whereDate('date', today())->first();
    }

    /**
     * Ajouter un montant à la caisse unique
     * Tout l'argent des 5 jours de versement va dans la même caisse
     */
    public function ajouterMontantAvecRepartition(float $montant): array
    {
        $this->increment('solde_caisse', $montant);

        return [
            'montant_total' => $montant,
        ];
    }

    /**
     * Retirer un montant de la caisse (pour tout type de paiement)
     */
    public function retirerMontantProprietaire(float $montant): bool
    {
        if ($montant > $this->solde_caisse) {
            return false;
        }

        $this->decrement('solde_caisse', $montant);

        return true;
    }

    /**
     * Retirer un montant de la caisse (alias pour compatibilité)
     */
    public function retirerMontantOkami(float $montant): bool
    {
        if ($montant > $this->solde_caisse) {
            return false;
        }

        $this->decrement('solde_caisse', $montant);

        return true;
    }

    /**
     * Retirer un montant de la caisse
     */
    public function retirerMontant(float $montant): bool
    {
        if ($montant > $this->solde_caisse) {
            return false;
        }

        $this->decrement('solde_caisse', $montant);

        return true;
    }

    /**
     * Statistiques du collecteur
     */
    public function getStatistiques(): array
    {
        $tournees = $this->tournees;
        $tourneesTerminees = $tournees->where('statut', 'terminee');

        return [
            'total_tournees' => $tournees->count(),
            'tournees_terminees' => $tourneesTerminees->count(),
            'tournees_en_retard' => $tournees->where('statut', 'en_retard')->count(),
            'total_collecte' => $tourneesTerminees->sum('total_encaisse'),
            'ecart_cumule' => $tourneesTerminees->sum('ecart_total'),
        ];
    }

    /**
     * Scope pour les collecteurs actifs
     */
    public function scopeActif($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope par zone
     */
    public function scopeParZone($query, string $zone)
    {
        return $query->where('zone_affectation', $zone);
    }
}

