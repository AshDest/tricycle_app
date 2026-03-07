<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Payment = Paiement vers le propriétaire ou bénéficiaire (reversement des recettes).
 * Workflow: OKAMI soumet une demande → Collecteur traite → OKAMI valide → Bénéficiaire visualise
 *
 * Sources de caisse:
 * - 'proprietaire': Paiement depuis la caisse des propriétaires (5/6)
 * - 'okami': Paiement depuis la caisse OKAMI (1/6)
 * - 'lavage': Paiement depuis la caisse Lavage (80% du service de lavage)
 *
 * Modes supportés: M-PESA, Airtel Money, Orange Money, Virement bancaire, Cash
 */
class Payment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'proprietaire_id',
        'source_caisse',         // 'proprietaire' ou 'okami'
        'beneficiaire_nom',      // Nom du bénéficiaire (si caisse OKAMI)
        'beneficiaire_telephone', // Téléphone du bénéficiaire
        'beneficiaire_motif',    // Motif du paiement (si caisse OKAMI)
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
            'en_attente' => 'En attente',
            'paye' => 'Payé (à valider)',
            'approuve' => 'Approuvé',
            'rejete' => 'Rejeté',
        ];
    }

    /**
     * Les modes de paiement disponibles
     */
    public static function getModesPaiement(): array
    {
        return [
            'cash' => 'Cash',
            'mpesa' => 'M-PESA',
            'airtel_money' => 'Airtel Money',
            'orange_money' => 'Orange Money',
            'virement_bancaire' => 'Virement Bancaire',
        ];
    }

    /**
     * Les sources de caisse disponibles
     */
    public static function getSourcesCaisse(): array
    {
        return [
            'proprietaire' => 'Part Propriétaires (5/6 versements)',
            'okami' => 'Part OKAMI Versements (1/6)',
            'lavage' => 'Part OKAMI Lavage (20%)',
        ];
    }

    /**
     * Vérifier si le paiement provient de la caisse OKAMI
     */
    public function getIsFromOkamiAttribute(): bool
    {
        return $this->source_caisse === 'okami';
    }

    /**
     * Vérifier si le paiement provient de la caisse Lavage
     */
    public function getIsFromLavageAttribute(): bool
    {
        return $this->source_caisse === 'lavage';
    }

    /**
     * Vérifier si le paiement provient de la caisse Propriétaire
     */
    public function getIsFromProprietaireAttribute(): bool
    {
        return $this->source_caisse === 'proprietaire' || empty($this->source_caisse);
    }

    /**
     * Obtenir le nom du bénéficiaire (propriétaire ou bénéficiaire OKAMI)
     */
    public function getBeneficiaireNomCompletAttribute(): string
    {
        if ($this->is_from_okami) {
            return $this->beneficiaire_nom ?? 'N/A';
        }
        return $this->proprietaire?->user?->name ?? $this->proprietaire?->raison_sociale ?? 'N/A';
    }

    /**
     * Scope pour les paiements depuis la caisse OKAMI
     */
    public function scopeFromOkami($query)
    {
        return $query->where('source_caisse', 'okami');
    }

    /**
     * Scope pour les paiements depuis la caisse Propriétaire
     */
    public function scopeFromProprietaire($query)
    {
        return $query->where(function($q) {
            $q->where('source_caisse', 'proprietaire')
              ->orWhereNull('source_caisse');
        });
    }
}
