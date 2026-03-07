<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class TransactionMobile extends Model
{
    use SoftDeletes;
    protected $table = 'transactions_mobile';
    protected $fillable = [
        'numero_transaction', 'collecteur_id', 'type', 'montant', 'frais',
        'montant_net', 'operateur', 'numero_telephone', 'nom_beneficiaire',
        'reference_operateur', 'statut', 'motif', 'notes', 'date_transaction',
        'valide_par', 'valide_at',
    ];
    protected $casts = [
        'montant' => 'decimal:2',
        'frais' => 'decimal:2',
        'montant_net' => 'decimal:2',
        'date_transaction' => 'datetime',
        'valide_at' => 'datetime',
    ];
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->numero_transaction)) {
                $model->numero_transaction = 'TXM-' . now()->format('ymd') . '-' . strtoupper(substr(uniqid(), -5));
            }
            if (empty($model->montant_net)) {
                $model->montant_net = $model->type === 'envoi' 
                    ? $model->montant + ($model->frais ?? 0)
                    : $model->montant - ($model->frais ?? 0);
            }
        });
    }
    public function collecteur(): BelongsTo
    {
        return $this->belongsTo(Collecteur::class);
    }
    public function validateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'valide_par');
    }
    public static function getTypes(): array
    {
        return ['envoi' => 'Envoi d\'argent', 'retrait' => 'Retrait d\'argent'];
    }
    public static function getOperateurs(): array
    {
        return [
            'mpesa' => 'M-PESA', 'airtel_money' => 'Airtel Money',
            'orange_money' => 'Orange Money', 'afrimoney' => 'Afrimoney',
        ];
    }
    public static function getStatuts(): array
    {
        return [
            'en_attente' => 'En attente', 'complete' => 'Complétée',
            'echoue' => 'Échouée', 'annule' => 'Annulée',
        ];
    }
    public function getTypeLabelAttribute(): string
    {
        return self::getTypes()[$this->type] ?? ucfirst($this->type);
    }
    public function getOperateurLabelAttribute(): string
    {
        return self::getOperateurs()[$this->operateur] ?? ucfirst($this->operateur);
    }
    public function getStatutLabelAttribute(): string
    {
        return self::getStatuts()[$this->statut] ?? ucfirst($this->statut);
    }
    public function getTypeBadgeColorAttribute(): string
    {
        return $this->type === 'envoi' ? 'danger' : 'success';
    }
    public function getStatutBadgeColorAttribute(): string
    {
        return match($this->statut) {
            'complete' => 'success', 'en_attente' => 'warning',
            'echoue' => 'danger', default => 'secondary',
        };
    }
    public function getOperateurColorAttribute(): string
    {
        return match($this->operateur) {
            'mpesa' => 'success', 'airtel_money' => 'danger',
            'orange_money' => 'warning', default => 'info',
        };
    }
    public function scopeOfCollecteur($query, int $id) { return $query->where('collecteur_id', $id); }
    public function scopeEnvois($query) { return $query->where('type', 'envoi'); }
    public function scopeRetraits($query) { return $query->where('type', 'retrait'); }
    public function scopeCompletes($query) { return $query->where('statut', 'complete'); }
}
