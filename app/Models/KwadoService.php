<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

/**
 * KwadoService = Service de réparation de pneus (KWADO).
 * Les recettes s'ajoutent à la caisse du laveur (cleaner).
 */
class KwadoService extends Model
{
    use SoftDeletes;

    protected $table = 'kwado_services';

    // Types de services disponibles
    const TYPES_SERVICE = [
        'crevaison' => 'Réparation crevaison',
        'changement_pneu' => 'Changement de pneu',
        'changement_chambre' => 'Changement chambre à air',
        'equilibrage' => 'Équilibrage',
        'gonflage' => 'Gonflage',
        'rustine' => 'Pose de rustine',
        'autre' => 'Autre service',
    ];

    // Positions de pneu possibles
    const POSITIONS_PNEU = [
        'avant' => 'Roue avant',
        'arriere_gauche' => 'Roue arrière gauche',
        'arriere_droit' => 'Roue arrière droite',
    ];

    protected $fillable = [
        'numero_service',
        'cleaner_id',
        'moto_id',
        'is_externe',
        'plaque_externe',
        'proprietaire_externe',
        'telephone_externe',
        'type_service',
        'description_service',
        'position_pneu',
        'prix',
        'cout_pieces',
        'montant_encaisse',
        'mode_paiement',
        'statut_paiement',
        'date_service',
        'notes',
    ];

    protected $casts = [
        'prix' => 'decimal:2',
        'cout_pieces' => 'decimal:2',
        'montant_encaisse' => 'decimal:2',
        'is_externe' => 'boolean',
        'date_service' => 'datetime',
    ];

    /**
     * Boot du modèle
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($service) {
            if (empty($service->numero_service)) {
                $service->numero_service = self::generateNumeroService();
            }
        });

        // Après création, ajouter au solde du cleaner
        static::created(function ($service) {
            if ($service->statut_paiement === 'payé') {
                $service->cleaner->increment('solde_actuel', $service->montant_encaisse);
            }
        });

        // Si suppression, retirer du solde
        static::deleted(function ($service) {
            if (!$service->isForceDeleting() && $service->statut_paiement === 'payé') {
                $service->cleaner->decrement('solde_actuel', $service->montant_encaisse);
            }
        });
    }

    /**
     * Générer un numéro de service unique
     */
    public static function generateNumeroService(): string
    {
        $date = now()->format('Ymd');
        $count = self::whereDate('created_at', today())->count() + 1;
        return 'KWD-' . $date . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Relation avec le laveur/cleaner
     */
    public function cleaner(): BelongsTo
    {
        return $this->belongsTo(Cleaner::class);
    }

    /**
     * Relation avec la moto (si interne)
     */
    public function moto(): BelongsTo
    {
        return $this->belongsTo(Moto::class);
    }

    /**
     * Plaque d'immatriculation (interne ou externe)
     */
    public function getPlaqueAttribute(): string
    {
        if ($this->is_externe) {
            return $this->plaque_externe ?? 'N/A';
        }
        return $this->moto?->plaque_immatriculation ?? 'N/A';
    }

    /**
     * Nom du propriétaire (interne ou externe)
     */
    public function getProprietaireNomAttribute(): string
    {
        if ($this->is_externe) {
            return $this->proprietaire_externe ?? 'Externe';
        }
        return $this->moto?->proprietaire?->user?->name ?? 'N/A';
    }

    /**
     * Libellé du type de service
     */
    public function getTypeServiceLabelAttribute(): string
    {
        return self::TYPES_SERVICE[$this->type_service] ?? $this->type_service ?? 'N/A';
    }

    /**
     * Libellé de la position du pneu
     */
    public function getPositionPneuLabelAttribute(): string
    {
        return self::POSITIONS_PNEU[$this->position_pneu] ?? $this->position_pneu ?? '';
    }

    /**
     * Bénéfice net (montant encaissé - coût pièces)
     */
    public function getBeneficeNetAttribute(): float
    {
        return ($this->montant_encaisse ?? 0) - ($this->cout_pieces ?? 0);
    }

    /**
     * Scope pour les services du jour
     */
    public function scopeAujourdhui($query)
    {
        return $query->whereDate('date_service', today());
    }

    /**
     * Scope pour les services payés
     */
    public function scopePayes($query)
    {
        return $query->where('statut_paiement', 'payé');
    }

    /**
     * Scope pour les services d'un cleaner
     */
    public function scopeDuCleaner($query, int $cleanerId)
    {
        return $query->where('cleaner_id', $cleanerId);
    }
}

