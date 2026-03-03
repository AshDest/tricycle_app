<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Collecteur = Agent terrain qui récupère l'argent chez les caissiers.
 * Il effectue des tournées quotidiennes et transmet l'argent à NTH (Admin).
 * La caisse est répartie entre la part OKAMI et la part Propriétaire.
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
     * Ajouter un montant à la caisse avec répartition
     * Selon le cahier des charges: sur 6 jours, 5 jours pour propriétaire, 1 jour pour OKAMI
     * Ratio: 5/6 propriétaire, 1/6 OKAMI
     */
    public function ajouterMontantAvecRepartition(float $montant): array
    {
        // Calcul de la répartition
        $partOkami = round($montant / 6, 2); // 1/6 pour OKAMI
        $partProprietaire = $montant - $partOkami; // 5/6 pour propriétaire

        // Mise à jour des soldes
        $this->increment('solde_caisse', $montant);
        $this->increment('solde_part_okami', $partOkami);
        $this->increment('solde_part_proprietaire', $partProprietaire);

        return [
            'montant_total' => $montant,
            'part_okami' => $partOkami,
            'part_proprietaire' => $partProprietaire,
        ];
    }

    /**
     * Retirer un montant de la caisse (pour paiement propriétaire)
     */
    public function retirerMontantProprietaire(float $montant): bool
    {
        if ($montant > $this->solde_part_proprietaire) {
            return false;
        }

        $this->decrement('solde_caisse', $montant);
        $this->decrement('solde_part_proprietaire', $montant);

        return true;
    }

    /**
     * Retirer un montant pour OKAMI
     */
    public function retirerMontantOkami(float $montant): bool
    {
        if ($montant > $this->solde_part_okami) {
            return false;
        }

        $this->decrement('solde_caisse', $montant);
        $this->decrement('solde_part_okami', $montant);

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

