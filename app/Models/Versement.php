<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

/**
 * Versement = Paiement journalier du motard au caissier (point de collecte).
 * Flux: Motard → Caissier (validation) → Collecteur (ramassage) → NTH (Admin)
 */
class Versement extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'motard_id',
        'motard_secondaire_id',
        'moto_id',
        'montant',
        'montant_attendu',
        'date_versement',
        'heure_versement',
        'mode_paiement',
        'type', // journalier ou arrieres_only
        'statut',
        'caissier_id',
        'validated_by_caissier_at',
        'valide_par_okami',
        'validated_by_okami_id',
        'validated_by_okami_at',
        'okami_notes',
        'collecte_id',
        'notes',
        'arrieres',
        'semaine_debut',
        'semaine_fin',
        'numero_semaine',
        'part_proprietaire',
        'part_okami',
    ];

    protected $casts = [
        'montant' => 'decimal:2',
        'montant_attendu' => 'decimal:2',
        'arrieres' => 'decimal:2',
        'part_proprietaire' => 'decimal:2',
        'part_okami' => 'decimal:2',
        'date_versement' => 'date',
        'semaine_debut' => 'date',
        'semaine_fin' => 'date',
        'validated_by_caissier_at' => 'datetime',
        'validated_by_okami_at' => 'datetime',
        'valide_par_okami' => 'boolean',
    ];

    /**
     * Boot du modèle - Calcul automatique des arriérés et du statut
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($versement) {
            // Ne pas recalculer pour les versements de type arrieres_only
            if ($versement->type !== 'arrieres_only') {
                $versement->calculerArrieres();
                $versement->determinerStatut();
            }
        });

        static::updating(function ($versement) {
            // Ne pas recalculer pour les versements de type arrieres_only
            if ($versement->type !== 'arrieres_only' && ($versement->isDirty('montant') || $versement->isDirty('montant_attendu'))) {
                $versement->calculerArrieres();
                $versement->determinerStatut();
            }
        });
    }

    /**
     * Calculer les arriérés (différence entre attendu et versé)
     */
    public function calculerArrieres(): void
    {
        // Ne pas calculer pour les versements d'arriérés uniquement
        if ($this->type === 'arrieres_only') {
            return;
        }

        $montantAttendu = $this->montant_attendu ?? 0;
        $montantVerse = $this->montant ?? 0;

        // Les arriérés sont positifs si le motard doit de l'argent
        $this->arrieres = max(0, $montantAttendu - $montantVerse);
    }

    /**
     * Déterminer automatiquement le statut basé sur le montant versé
     */
    public function determinerStatut(): void
    {
        // Pour les versements d'arriérés uniquement, toujours payé
        if ($this->type === 'arrieres_only') {
            $this->statut = 'payé';
            return;
        }

        $montantAttendu = $this->montant_attendu ?? 0;
        $montantVerse = $this->montant ?? 0;

        if ($montantVerse <= 0) {
            $this->statut = 'non_effectué';
        } elseif ($montantVerse >= $montantAttendu) {
            $this->statut = 'payé';
        } elseif ($montantVerse > 0 && $montantVerse < $montantAttendu) {
            $this->statut = 'partiellement_payé';
        }
    }

    /**
     * Le motard qui a effectué le versement
     */
    public function motard(): BelongsTo
    {
        return $this->belongsTo(Motard::class);
    }

    /**
     * Le motard secondaire (remplaçant) qui a travaillé ce jour-là.
     * Null si c'est le motard titulaire qui a travaillé.
     */
    public function motardSecondaire(): BelongsTo
    {
        return $this->belongsTo(Motard::class, 'motard_secondaire_id');
    }

    /**
     * Vérifier si le versement a été fait par un motard remplaçant
     */
    public function getEstRemplacantAttribute(): bool
    {
        return !is_null($this->motard_secondaire_id);
    }

    /**
     * Obtenir le nom du conducteur effectif (remplaçant ou titulaire)
     */
    public function getConducteurEffectifAttribute(): ?string
    {
        if ($this->motard_secondaire_id) {
            return $this->motardSecondaire?->user?->name;
        }
        return $this->motard?->user?->name;
    }

    /**
     * La moto concernée par le versement
     */
    public function moto(): BelongsTo
    {
        return $this->belongsTo(Moto::class);
    }

    /**
     * Le caissier (point de collecte) qui a reçu le versement
     */
    public function caissier(): BelongsTo
    {
        return $this->belongsTo(Caissier::class);
    }

    /**
     * L'utilisateur OKAMI qui a validé (si applicable)
     */
    public function validePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by_okami_id');
    }

    /**
     * La collecte associée (quand le collecteur ramasse l'argent)
     */
    public function collecte(): BelongsTo
    {
        return $this->belongsTo(Collecte::class);
    }

    /**
     * Calculer l'écart entre le montant versé et le montant attendu
     */
    public function getEcartAttribute(): float
    {
        return ($this->montant ?? 0) - ($this->montant_attendu ?? 0);
    }

    /**
     * Vérifier si le versement a des arriérés
     */
    public function getHasArriereAttribute(): bool
    {
        return ($this->arrieres ?? 0) > 0 || $this->ecart < 0;
    }

    /**
     * Obtenir le montant des arriérés (toujours positif)
     */
    public function getMontantArriereAttribute(): float
    {
        return max(0, ($this->montant_attendu ?? 0) - ($this->montant ?? 0));
    }

    /**
     * Obtenir le pourcentage de paiement
     */
    public function getPourcentagePaiementAttribute(): float
    {
        if (($this->montant_attendu ?? 0) <= 0) {
            return 100;
        }
        return min(100, round(($this->montant / $this->montant_attendu) * 100, 1));
    }

    /**
     * Vérifier si le versement est en retard
     */
    public function getIsEnRetardAttribute(): bool
    {
        return $this->statut === 'en_retard' ||
               ($this->statut === 'non_effectue' && $this->date_versement && $this->date_versement->isPast());
    }

    /**
     * Vérifier si le versement est complet
     */
    public function getIsCompletAttribute(): bool
    {
        return ($this->montant ?? 0) >= ($this->montant_attendu ?? 0);
    }

    /**
     * Vérifier si le versement est validé par le caissier
     */
    public function getIsValideByCaissierAttribute(): bool
    {
        return !is_null($this->validated_by_caissier_at);
    }

    /**
     * Scope pour les versements non collectés (encore chez le caissier)
     */
    public function scopeNonCollecte($query)
    {
        return $query->whereNull('collecte_id');
    }

    /**
     * Scope pour les versements du jour
     */
    public function scopeAujourdhui($query)
    {
        return $query->whereDate('date_versement', today());
    }

    /**
     * Scope pour les versements en retard
     */
    public function scopeEnRetard($query)
    {
        return $query->where('statut', 'en_retard');
    }

    /**
     * Scope pour les versements avec arriérés
     */
    public function scopeAvecArrieres($query)
    {
        return $query->whereRaw('montant < montant_attendu');
    }

    /**
     * Scope pour les versements complets
     */
    public function scopeComplets($query)
    {
        return $query->whereRaw('montant >= montant_attendu');
    }

    /**
     * Scope pour les versements partiels
     */
    public function scopePartiels($query)
    {
        return $query->where('statut', 'partiellement_payé')
                     ->orWhereRaw('montant > 0 AND montant < montant_attendu');
    }
}
