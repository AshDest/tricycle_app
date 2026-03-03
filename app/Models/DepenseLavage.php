<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class DepenseLavage extends Model
{
    use SoftDeletes;

    protected $table = 'depenses_lavage';

    protected $fillable = [
        'numero_depense',
        'cleaner_id',
        'categorie',
        'description',
        'montant',
        'mode_paiement',
        'reference_paiement',
        'fournisseur',
        'piece_justificative',
        'date_depense',
        'notes',
    ];

    protected $casts = [
        'montant' => 'decimal:2',
        'date_depense' => 'date',
    ];

    // Catégories disponibles
    const CATEGORIES = [
        'produits' => 'Produits de lavage',
        'equipement' => 'Équipement',
        'eau' => 'Eau',
        'electricite' => 'Électricité',
        'loyer' => 'Loyer',
        'salaire' => 'Salaire assistant',
        'transport' => 'Transport',
        'reparation' => 'Réparation',
        'autre' => 'Autre',
    ];

    /**
     * Boot du modèle
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($depense) {
            // Générer le numéro de dépense
            if (empty($depense->numero_depense)) {
                $depense->numero_depense = self::generateNumeroDepense();
            }
        });

        // Après création, déduire du solde du cleaner
        static::created(function ($depense) {
            $depense->cleaner->decrement('solde_actuel', $depense->montant);
        });

        // Si suppression, rembourser le solde
        static::deleted(function ($depense) {
            if (!$depense->isForceDeleting()) {
                $depense->cleaner->increment('solde_actuel', $depense->montant);
            }
        });
    }

    /**
     * Générer un numéro de dépense unique
     */
    public static function generateNumeroDepense(): string
    {
        $date = now()->format('Ymd');
        $count = self::whereDate('created_at', today())->count() + 1;
        return 'DEP-' . $date . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Relation avec le laveur
     */
    public function cleaner(): BelongsTo
    {
        return $this->belongsTo(Cleaner::class);
    }

    /**
     * Obtenir le libellé de la catégorie
     */
    public function getCategorieLabelAttribute(): string
    {
        return self::CATEGORIES[$this->categorie] ?? $this->categorie;
    }

    /**
     * Scope pour les dépenses du jour
     */
    public function scopeAujourdhui($query)
    {
        return $query->whereDate('date_depense', today());
    }

    /**
     * Scope pour les dépenses du mois
     */
    public function scopeDuMois($query, $mois = null, $annee = null)
    {
        $mois = $mois ?? now()->month;
        $annee = $annee ?? now()->year;

        return $query->whereMonth('date_depense', $mois)
                     ->whereYear('date_depense', $annee);
    }

    /**
     * Scope par catégorie
     */
    public function scopeCategorie($query, string $categorie)
    {
        return $query->where('categorie', $categorie);
    }
}

