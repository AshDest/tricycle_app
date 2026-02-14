<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Accident = Déclaration et suivi des accidents impliquant les motos-tricycles.
 * Le motard peut déclarer un accident mais NE PEUT PAS modifier les coûts.
 * Seul l'Admin NTH peut valider, modifier ou clôturer.
 */
class Accident extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'moto_id',
        'motard_id',
        'date_heure',
        'lieu',
        'description',
        'temoignage_motard',
        'temoignage_temoin',
        'temoin_nom',
        'temoin_telephone',
        'photo_dommage_url',
        'photos_supplementaires', // JSON array d'URLs
        'video_url',
        'estimation_cout',
        'cout_reel',
        'pieces_endommagees',
        'devis_url',
        'prise_en_charge', // motard / proprietaire / assurance / nth
        'statut',
        'gravite', // mineur / modere / grave
        'reparation_programmee_at',
        'reparation_terminee_at',
        'valide_par',
        'valide_at',
        'notes_admin',
    ];

    protected $casts = [
        'date_heure' => 'datetime',
        'estimation_cout' => 'decimal:2',
        'cout_reel' => 'decimal:2',
        'photos_supplementaires' => 'array',
        'reparation_programmee_at' => 'datetime',
        'reparation_terminee_at' => 'datetime',
        'valide_at' => 'datetime',
    ];

    /**
     * La moto impliquée
     */
    public function moto(): BelongsTo
    {
        return $this->belongsTo(Moto::class);
    }

    /**
     * Le motard impliqué
     */
    public function motard(): BelongsTo
    {
        return $this->belongsTo(Motard::class);
    }

    /**
     * L'admin qui a validé
     */
    public function validePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'valide_par');
    }

    /**
     * La maintenance liée à cet accident (si réparation)
     */
    public function maintenance(): HasOne
    {
        return $this->hasOne(Maintenance::class, 'accident_id');
    }

    /**
     * Calculer l'écart entre estimation et coût réel
     */
    public function getEcartCoutAttribute(): ?float
    {
        if (is_null($this->cout_reel)) {
            return null;
        }
        return $this->cout_reel - $this->estimation_cout;
    }

    /**
     * Vérifier si l'accident est grave
     */
    public function getIsGraveAttribute(): bool
    {
        return $this->gravite === 'grave';
    }

    /**
     * Vérifier si la réparation est en cours
     */
    public function getIsReparationEnCoursAttribute(): bool
    {
        return !is_null($this->reparation_programmee_at) && is_null($this->reparation_terminee_at);
    }

    /**
     * Programmer une réparation
     */
    public function programmerReparation(): void
    {
        $this->update([
            'statut' => 'reparation_programmee',
            'reparation_programmee_at' => now(),
        ]);
    }

    /**
     * Terminer la réparation
     */
    public function terminerReparation(float $coutReel = null): void
    {
        $this->update([
            'statut' => 'repare',
            'reparation_terminee_at' => now(),
            'cout_reel' => $coutReel ?? $this->estimation_cout,
        ]);
    }

    /**
     * Valider l'accident (Admin NTH)
     */
    public function valider(int $userId): void
    {
        $this->update([
            'valide_par' => $userId,
            'valide_at' => now(),
        ]);
    }

    /**
     * Scope par gravité
     */
    public function scopeParGravite($query, string $gravite)
    {
        return $query->where('gravite', $gravite);
    }

    /**
     * Scope pour les accidents graves
     */
    public function scopeGraves($query)
    {
        return $query->where('gravite', 'grave');
    }

    /**
     * Scope pour les accidents non réparés
     */
    public function scopeNonRepares($query)
    {
        return $query->whereNull('reparation_terminee_at');
    }

    /**
     * Les niveaux de gravité
     */
    public static function getNiveauxGravite(): array
    {
        return [
            'mineur' => 'Mineur',
            'modere' => 'Modéré',
            'grave' => 'Grave',
        ];
    }

    /**
     * Les options de prise en charge
     */
    public static function getPrisesEnCharge(): array
    {
        return [
            'motard' => 'Motard',
            'proprietaire' => 'Propriétaire',
            'assurance' => 'Assurance',
            'nth' => 'NTH Sarl',
        ];
    }
}
