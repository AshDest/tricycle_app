<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;
class BeneficeChange extends Model
{
    use SoftDeletes;
    protected $table = 'benefices_change';
    protected $fillable = [
        'numero_reference', 'collecteur_id', 'date_operation', 'type_saisie',
        'montant_recu_caissier', 'solde_general_caisse', 'benefice',
        'commentaire', 'periode_debut', 'periode_fin',
        'statut', 'valide_par', 'valide_at', 'motif_rejet',
    ];
    protected $casts = [
        'date_operation' => 'date',
        'periode_debut' => 'date',
        'periode_fin' => 'date',
        'montant_recu_caissier' => 'decimal:2',
        'solde_general_caisse' => 'decimal:2',
        'benefice' => 'decimal:2',
        'valide_at' => 'datetime',
    ];
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->numero_reference)) {
                $prefix = match($model->type_saisie) {
                    'journalier' => 'BCJ',
                    'hebdomadaire' => 'BCH',
                    'mensuel' => 'BCM',
                    default => 'BC',
                };
                $model->numero_reference = $prefix . '-' . now()->format('ymd') . '-' . strtoupper(substr(uniqid(), -5));
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
    public static function getTypesSaisie(): array
    {
        return [
            'journalier' => 'Journalier',
            'hebdomadaire' => 'Hebdomadaire',
            'mensuel' => 'Mensuel',
        ];
    }
    public function getTypeSaisieLabelAttribute(): string
    {
        return self::getTypesSaisie()[$this->type_saisie] ?? ucfirst($this->type_saisie);
    }
    public function getTypeSaisieBadgeAttribute(): string
    {
        return match($this->type_saisie) {
            'journalier' => 'primary',
            'hebdomadaire' => 'info',
            'mensuel' => 'success',
            default => 'secondary',
        };
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
    public function getPeriodeLabelAttribute(): string
    {
        if ($this->type_saisie === 'journalier') {
            return $this->date_operation?->format('d/m/Y') ?? '';
        }
        if ($this->periode_debut && $this->periode_fin) {
            return $this->periode_debut->format('d/m') . ' - ' . $this->periode_fin->format('d/m/Y');
        }
        return $this->date_operation?->format('d/m/Y') ?? '';
    }
    public function scopeOfCollecteur($query, int $id)
    {
        return $query->where('collecteur_id', $id);
    }
    public function scopeJournalier($query)
    {
        return $query->where('type_saisie', 'journalier');
    }
    public function scopeHebdomadaire($query)
    {
        return $query->where('type_saisie', 'hebdomadaire');
    }
    public function scopeMensuel($query)
    {
        return $query->where('type_saisie', 'mensuel');
    }
    public function scopeValide($query)
    {
        return $query->where('statut', 'valide');
    }
    public function scopeEnAttente($query)
    {
        return $query->where('statut', 'en_attente');
    }
    public function scopePeriode($query, $debut, $fin)
    {
        return $query->whereBetween('date_operation', [$debut, $fin]);
    }
    /**
     * Calculer le bénéfice cumulé hebdomadaire automatiquement
     */
    public static function calculerBeneficeHebdomadaire(int $collecteurId, Carbon $dateDebut, Carbon $dateFin): float
    {
        return self::where('collecteur_id', $collecteurId)
            ->where('type_saisie', 'journalier')
            ->whereBetween('date_operation', [$dateDebut, $dateFin])
            ->where('statut', 'valide')
            ->sum('benefice');
    }
    /**
     * Calculer le bénéfice cumulé mensuel automatiquement
     */
    public static function calculerBeneficeMensuel(int $collecteurId, int $annee, int $mois): float
    {
        $debut = Carbon::create($annee, $mois, 1)->startOfMonth();
        $fin = Carbon::create($annee, $mois, 1)->endOfMonth();
        return self::where('collecteur_id', $collecteurId)
            ->whereIn('type_saisie', ['journalier', 'hebdomadaire'])
            ->whereBetween('date_operation', [$debut, $fin])
            ->where('statut', 'valide')
            ->sum('benefice');
    }
}
