<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Payment = Paiement vers le propriétaire (reversement des recettes).
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
        'statut',
        'date_demande',
        'date_paiement',
        'reference_paiement',
        'numero_compte', // Numéro de téléphone ou compte bancaire
        'periode_debut', // Début de la période couverte
        'periode_fin',   // Fin de la période couverte
        'notes',
        'recu_url',      // URL du reçu PDF généré
        'traite_par',    // ID de l'admin qui a traité
    ];

    protected $casts = [
        'total_du' => 'decimal:2',
        'total_paye' => 'decimal:2',
        'date_demande' => 'date',
        'date_paiement' => 'date',
        'periode_debut' => 'date',
        'periode_fin' => 'date',
    ];

    /**
     * Le propriétaire qui reçoit le paiement
     */
    public function proprietaire(): BelongsTo
    {
        return $this->belongsTo(Proprietaire::class);
    }

    /**
     * L'admin qui a traité le paiement
     */
    public function traitePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'traite_par');
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
     * Vérifier si le paiement est en attente
     */
    public function getIsEnAttenteAttribute(): bool
    {
        return $this->statut === 'en_attente';
    }

    /**
     * Marquer comme payé
     */
    public function marquerCommePaye(string $reference = null): void
    {
        $this->update([
            'statut' => 'payé',
            'date_paiement' => now(),
            'reference_paiement' => $reference,
        ]);
    }

    /**
     * Scope pour les paiements en attente
     */
    public function scopeEnAttente($query)
    {
        return $query->where('statut', 'en_attente');
    }

    /**
     * Scope pour les paiements effectués
     */
    public function scopePayes($query)
    {
        return $query->where('statut', 'payé');
    }

    /**
     * Scope pour un propriétaire
     */
    public function scopeDeProprietaire($query, int $proprietaireId)
    {
        return $query->where('proprietaire_id', $proprietaireId);
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
