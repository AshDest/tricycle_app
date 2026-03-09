<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
class AuditBeneficeCommission extends Model
{
    protected $table = 'audit_benefices_commissions';
    protected $fillable = [
        'auditable_type', 'auditable_id', 'user_id', 'action',
        'anciennes_valeurs', 'nouvelles_valeurs', 'ip_address',
    ];
    protected $casts = [
        'anciennes_valeurs' => 'array',
        'nouvelles_valeurs' => 'array',
    ];
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }
    public function getActionLabelAttribute(): string
    {
        return match($this->action) {
            'creation' => 'Création',
            'modification' => 'Modification',
            'validation' => 'Validation',
            'rejet' => 'Rejet',
            'suppression' => 'Suppression',
            default => ucfirst($this->action),
        };
    }
    public function getActionBadgeAttribute(): string
    {
        return match($this->action) {
            'creation' => 'success',
            'modification' => 'info',
            'validation' => 'primary',
            'rejet' => 'danger',
            'suppression' => 'dark',
            default => 'secondary',
        };
    }
    /**
     * Enregistrer une action d'audit
     */
    public static function enregistrer(
        string $type,
        int $id,
        string $action,
        ?array $anciennesValeurs = null,
        ?array $nouvellesValeurs = null
    ): self {
        return self::create([
            'auditable_type' => $type,
            'auditable_id' => $id,
            'user_id' => auth()->id(),
            'action' => $action,
            'anciennes_valeurs' => $anciennesValeurs,
            'nouvelles_valeurs' => $nouvellesValeurs,
            'ip_address' => request()->ip(),
        ]);
    }
}
