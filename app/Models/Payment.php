<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Payment = Paiement vers le propriétaire (reversement des recettes).
 * Workflow: OKAMI soumet une demande → Collecteur traite → OKAMI valide → Propriétaire visualise
 * Modes supportés: M-PESA, Airtel Money, Orange Money, Virement bancaire
 */
class Payment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'proprietaire_id',
        'total_du',
        'total_paye',
        'mode_paiement',
        'statut', // demande, en_cours, paye, valide, rejete
        'date_demande',
        'date_paiement',
        'reference_paiement',
        'numero_envoi',
        'numero_compte',
        'periode_debut',
        'periode_fin',
        'notes',
        'notes_validation',
        'recu_url',
        'demande_par',    // OKAMI qui a fait la demande
        'demande_at',
        'traite_par',     // Collecteur/Admin qui a traité
        'valide_par',     // OKAMI qui a validé après paiement
        'valide_at',
    ];

    protected $casts = [
        'total_du' => 'decimal:2',
        'total_paye' => 'decimal:2',
        'date_demande' => 'date',
        'date_paiement' => 'date',
        'periode_debut' => 'date',
        'periode_fin' => 'date',
        'demande_at' => 'datetime',
        'valide_at' => 'datetime',
    ];

    /**
     * Le propriétaire qui reçoit le paiement
     */
    public function proprietaire(): BelongsTo
    {
        return $this->belongsTo(Proprietaire::class);
    }

    /**
     * OKAMI qui a soumis la demande
     */
    public function demandePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'demande_par');
    }

    /**
     * Collecteur/Admin qui a traité le paiement
     */
    public function traitePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'traite_par');
    }

    /**
     * OKAMI qui a validé le paiement
     */
    public function validePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'valide_par');
    }

    /**
     * Calculer les arriérés (montant restant dû)
     */
    public function getArrieresAttribute(): float
    {
        return $this->total_du - $this->total_paye;
    }

    /**
     * Vérifier si le paiement est complet
     */
    public function getIsCompletAttribute(): bool
    {
        return $this->total_paye >= $this->total_du;
    }

    /**
     * Vérifier si le paiement est en attente de validation OKAMI
     */
    public function getIsEnAttenteValidationAttribute(): bool
    {
        return $this->statut === 'paye';
    }

    /**
     * Scope pour les demandes en attente
     */
    public function scopeEnAttente($query)
    {
        return $query->where('statut', 'demande');
    }

    /**
     * Scope pour les paiements effectués mais non validés
     */
    public function scopeEnAttenteValidation($query)
    {
        return $query->where('statut', 'paye');
    }

    /**
     * Scope pour les paiements validés
     */
    public function scopeValides($query)
    {
        return $query->where('statut', 'valide');
    }

    /**
     * Les statuts possibles
     */
    public static function getStatuts(): array
    {
        return [
            'demande' => 'Demande soumise',
            'en_cours' => 'En cours de traitement',
            'paye' => 'Payé (en attente validation)',
            'valide' => 'Validé',
            'rejete' => 'Rejeté',
        ];
    }

    /**
     * Les modes de paiement disponibles
     */
    public static function getModesPaiement(): array
    {
        return [
            'mpesa' => 'M-PESA',
            'airtel_money' => 'Airtel Money',
            'orange_money' => 'Orange Money',
            'virement_bancaire' => 'Virement Bancaire',
        ];
    }
}
