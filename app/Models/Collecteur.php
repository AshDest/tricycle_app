<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Collecteur = Agent terrain qui récupère l'argent chez les caissiers.
 * Il effectue des tournées quotidiennes et transmet l'argent à NTH (Admin).
 */
class Collecteur extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'numero_identifiant',
        'zone_affectation',
        'telephone',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
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

