<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Relation avec le profil Motard
     */
    public function motard()
    {
        return $this->hasOne(Motard::class);
    }

    /**
     * Relation avec le profil Propriétaire
     */
    public function proprietaire()
    {
        return $this->hasOne(Proprietaire::class);
    }

    /**
     * Relation avec le profil Caissier
     */
    public function caissier()
    {
        return $this->hasOne(Caissier::class);
    }

    /**
     * Relation avec le profil Collecteur
     */
    public function collecteur()
    {
        return $this->hasOne(Collecteur::class);
    }

    /**
     * Vérifier si l'utilisateur est un motard
     */
    public function isMotard(): bool
    {
        return $this->hasRole('driver') && $this->motard !== null;
    }

    /**
     * Vérifier si l'utilisateur est un propriétaire
     */
    public function isProprietaire(): bool
    {
        return $this->hasRole('owner') && $this->proprietaire !== null;
    }

    /**
     * Vérifier si l'utilisateur est un caissier
     */
    public function isCaissier(): bool
    {
        return $this->hasRole('cashier') && $this->caissier !== null;
    }

    /**
     * Vérifier si l'utilisateur est un collecteur
     */
    public function isCollecteur(): bool
    {
        return $this->hasRole('collector') && $this->collecteur !== null;
    }

    /**
     * Vérifier si l'utilisateur est OKAMI (superviseur)
     */
    public function isOkami(): bool
    {
        return $this->hasRole('supervisor');
    }

    /**
     * Vérifier si l'utilisateur est Admin NTH
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
