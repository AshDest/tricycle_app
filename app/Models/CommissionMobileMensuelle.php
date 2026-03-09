<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class CommissionMobileMensuelle extends Model
{
    use SoftDeletes;
    protected $table = 'commissions_mobile_mensuelles';
    protected $fillable = [
        'numero_reference', 'collecteur_id', 'annee', 'mois',
        'montant_total', 'part_nth', 'part_okami',
        'preuve_paiement', 'commentaire', 'statut',
        'valide_par', 'valide_at', 'motif_rejet',
    ];
    protected $casts = [
        'montant_total' => 'decimal:2',
        'part_nth' => 'decimal:2',
        'part_okami' => 'decimal:2',
        'valide_at' => 'datetime',
    ];
    // Constantes de répartition
    const PART_NTH_PERCENT = 70;
    const PART_OKAMI_PERCENT = 30;
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->numero_reference)) {
                $model->numero_reference = 'COM-' . $model->annee . str_pad($model->mois, 2, '0', STR_PAD_LEFT) . '-' . strtoupper(substr(uniqid(), -5));
            }
            // Calcul automatique de la répartition
            $model->part_nth = round($model->montant_total * self::PART_NTH_PERCENT / 100, 2);
            $model->part_okami = round($model->montant_total * self::PART_OKAMI_PERCENT / 100, 2);
        });
        static::updating(function ($model) {
            // Recalcul si le montant change
            if ($model->isDirty('montant_total')) {
                $model->part_nth = round($model->montant_total * self::PART_NTH_PERCENT / 100, 2);
                $model->part_okami = round($model->montant_total * self::PART_OKAMI_PERCENT / 100, 2);
            }
        });
    }
    public function collecteur(): BelongsTo
    {
        return $this->belongsTo(Collecteur::class);
    }
    public function validePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'valide_par');
    }
    public static function getMois(): array
    {
        return [
            1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
            5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
            9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre',
        ];
    }
    public function getMoisLabelAttribute(): string
    {
        return self::getMois()[$this->mois] ?? '';
    }
    public function getPeriodeLabelAttribute(): string
    {
        return $this->mois_label . ' ' . $this->annee;
    }
    public function getStatutLabelAttribute(): string
    {
        return match($this->statut) {
            'en_attente' => 'En attente',
            'valide' => 'Validé',
            'rejete' => 'Rejeté',
            default => ucfirst($this->statut),
        };
    }
    public function getStatutBadgeAttribute(): string
    {
        return match($this->statut) {
            'en_attente' => 'warning',
            'valide' => 'success',
            'rejete' => 'danger',
            default => 'secondary',
        };
    }
    public function scopeOfCollecteur($query, int $id)
    {
        return $query->where('collecteur_id', $id);
    }
    public function scopeValide($query)
    {
        return $query->where('statut', 'valide');
    }
    public function scopeEnAttente($query)
    {
        return $query->where('statut', 'en_attente');
    }
    public function scopeAnnee($query, int $annee)
    {
        return $query->where('annee', $annee);
    }
    /**
     * Vérifier si une commission existe déjà pour ce mois
     */
    public static function existePourMois(int $collecteurId, int $annee, int $mois): bool
    {
        return self::where('collecteur_id', $collecteurId)
            ->where('annee', $annee)
            ->where('mois', $mois)
            ->exists();
    }
}
