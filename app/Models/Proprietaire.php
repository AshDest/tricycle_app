<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/**
 * Proprietaire = Bailleur qui possède des motos-tricycles.
 * Peut consulter ses versements, paiements, maintenances et accidents.
 * Peut envoyer des réclamations via messagerie interne.
 */
class Proprietaire extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'raison_sociale',
        'adresse',
        'telephone',
        'numero_compte_mpesa',
        'numero_compte_airtel',
        'numero_compte_orange',
        'numero_compte_bancaire',
        'banque_nom',
        'is_active',
    ];

    /**
     * L'utilisateur associé au propriétaire
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Toutes les motos du propriétaire
     */
    public function motos(): HasMany
    {
        return $this->hasMany(Moto::class);
    }

    /**
     * Tous les paiements reçus
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Les versements via les motos
     */
    public function versements(): HasManyThrough
    {
        return $this->hasManyThrough(Versement::class, Moto::class);
    }

    /**
     * Les maintenances de ses motos
     */
    public function maintenances(): HasManyThrough
    {
        return $this->hasManyThrough(Maintenance::class, Moto::class);
    }

    /**
     * Les accidents de ses motos
     */
    public function accidents(): HasManyThrough
    {
        return $this->hasManyThrough(Accident::class, Moto::class);
    }

    /**
     * Calculer le total dû au propriétaire
     */
    public function getTotalDuAttribute(): float
    {
        return $this->versements()
            ->where('versements.statut', 'payé')
            ->sum('versements.montant');
    }

    /**
     * Calculer le total déjà payé
     */
    public function getTotalPayeAttribute(): float
    {
        return $this->payments()
            ->where('statut', 'payé')
            ->sum('total_paye') ?? 0;
    }

    /**
     * Calculer les arriérés
     */
    public function getArrieresAttribute(): float
    {
        return $this->total_du - $this->total_paye;
    }

    /**
     * Obtenir les statistiques financières
     */
    public function getStatistiquesFinancieres(): array
    {
        return [
            'total_motos' => $this->motos()->count(),
            'motos_actives' => $this->motos()->where('motos.statut', 'actif')->count(),
            'total_du' => $this->total_du,
            'total_paye' => $this->total_paye,
            'arrieres' => $this->arrieres,
            'versements_ce_mois' => $this->versements()
                ->whereMonth('versements.date_versement', now()->month)
                ->whereYear('versements.date_versement', now()->year)
                ->sum('versements.montant'),
            'cout_maintenance' => $this->maintenances->sum('cout_total'),
            'cout_accidents' => $this->accidents->sum('estimation_cout'),
        ];
    }

    /**
     * Obtenir le numéro de compte selon le mode de paiement
     */
    public function getNumeroCompte(string $modePaiement): ?string
    {
        return match($modePaiement) {
            'mpesa' => $this->numero_compte_mpesa,
            'airtel_money' => $this->numero_compte_airtel,
            'orange_money' => $this->numero_compte_orange,
            'virement_bancaire' => $this->numero_compte_bancaire,
            default => null,
        };
    }

    /**
     * Scope pour les propriétaires avec arriérés
     */
    public function scopeAvecArrieres($query)
    {
        return $query->get()->filter(function ($proprietaire) {
            return $proprietaire->arrieres > 0;
        });
    }
}
