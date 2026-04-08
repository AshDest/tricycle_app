<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Realisation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'titre',
        'description',
        'date_realisation',
        'lieu',
        'categorie',
        'media',
        'is_published',
        'created_by',
    ];

    protected $casts = [
        'date_realisation' => 'date',
        'media' => 'array',
        'is_published' => 'boolean',
    ];

    /**
     * L'utilisateur qui a créé cette réalisation
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Les catégories disponibles
     */
    public static function getCategories(): array
    {
        return [
            'evenement' => 'Événement',
            'projet' => 'Projet',
            'activite' => 'Activité',
            'inauguration' => 'Inauguration',
            'formation' => 'Formation',
            'autre' => 'Autre',
        ];
    }

    /**
     * Obtenir le label de la catégorie
     */
    public function getCategorieLabel(): string
    {
        return self::getCategories()[$this->categorie] ?? ucfirst($this->categorie);
    }

    /**
     * Nombre de fichiers média
     */
    public function getMediaCountAttribute(): int
    {
        return count($this->media ?? []);
    }

    /**
     * Premier média (pour la vignette)
     */
    public function getFirstMediaAttribute(): ?array
    {
        $media = $this->media ?? [];
        return $media[0] ?? null;
    }

    /**
     * Scope publiés
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    /**
     * Scope brouillons
     */
    public function scopeDraft($query)
    {
        return $query->where('is_published', false);
    }

    /**
     * Scope par catégorie
     */
    public function scopeCategorie($query, string $categorie)
    {
        return $query->where('categorie', $categorie);
    }
}

