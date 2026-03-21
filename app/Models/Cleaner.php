<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cleaner extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'identifiant',
        'zone',
        'telephone',
        'adresse',
        'solde_actuel',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'solde_actuel' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Boot du modèle - Génération automatique de l'identifiant
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($cleaner) {
            if (empty($cleaner->identifiant)) {
                $cleaner->identifiant = self::generateIdentifiant();
            }
        });
    }

    /**
     * Générer un identifiant unique
     */
    public static function generateIdentifiant(): string
    {
        $lastCleaner = self::withTrashed()->orderBy('id', 'desc')->first();
        $nextId = $lastCleaner ? $lastCleaner->id + 1 : 1;
        return 'CLN-' . str_pad($nextId, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Relation avec l'utilisateur
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation avec les lavages
     */
    public function lavages(): HasMany
    {
        return $this->hasMany(Lavage::class);
    }

    /**
     * Relation avec les dépenses
     */
    public function depenses(): HasMany
    {
        return $this->hasMany(DepenseLavage::class);
    }

    /**
     * Relation avec les services KWADO (réparation pneus)
     */
    public function kwadoServices(): HasMany
    {
        return $this->hasMany(KwadoService::class);
    }

    /**
     * Nombre de services KWADO du jour
     */
    public function getKwadoAujourdhuiAttribute(): int
    {
        return $this->kwadoServices()->whereDate('date_service', today())->count();
    }

    /**
     * Recettes KWADO du jour
     */
    public function getKwadoRecettesJourAttribute(): float
    {
        return $this->kwadoServices()
            ->whereDate('date_service', today())
            ->where('statut_paiement', 'payé')
            ->sum('montant_encaisse');
    }

    /**
     * Recettes KWADO du mois
     */
    public function getKwadoRecettesMoisAttribute(): float
    {
        return $this->kwadoServices()
            ->whereMonth('date_service', now()->month)
            ->whereYear('date_service', now()->year)
            ->where('statut_paiement', 'payé')
            ->sum('montant_encaisse');
    }

    /**
     * Obtenir le nombre de lavages du jour
     */
    public function getLavagesAujourdhuiAttribute(): int
    {
        return $this->lavages()->whereDate('date_lavage', today())->count();
    }

    /**
     * Obtenir le chiffre d'affaires du jour
     */
    public function getChiffreAffairesJourAttribute(): float
    {
        return $this->lavages()
            ->whereDate('date_lavage', today())
            ->where('statut_paiement', 'payé')
            ->sum('part_cleaner');
    }

    /**
     * Obtenir le chiffre d'affaires du mois
     */
    public function getChiffreAffairesMoisAttribute(): float
    {
        return $this->lavages()
            ->whereMonth('date_lavage', now()->month)
            ->whereYear('date_lavage', now()->year)
            ->where('statut_paiement', 'payé')
            ->sum('part_cleaner');
    }

    /**
     * Obtenir les statistiques globales
     */
    public function getStatistiques(): array
    {
        $lavages = $this->lavages()->where('statut_paiement', 'payé');
        $kwado = $this->kwadoServices()->where('statut_paiement', 'payé');

        return [
            'total_lavages' => (clone $lavages)->count(),
            'lavages_internes' => (clone $lavages)->where('is_externe', false)->count(),
            'lavages_externes' => (clone $lavages)->where('is_externe', true)->count(),
            'chiffre_affaires_lavage' => (clone $lavages)->sum('part_cleaner'),
            'part_okami_total' => (clone $lavages)->sum('part_okami'),
            'total_kwado' => (clone $kwado)->count(),
            'chiffre_affaires_kwado' => (clone $kwado)->sum('montant_encaisse'),
        ];
    }
}

