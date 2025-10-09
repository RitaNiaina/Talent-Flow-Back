<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'nom_utilisateur',
        'email_utilisateur',
        'mot_passe',
        'role_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'mot_passe',
        'remember_token',
    ];

    /**
     * Mutator pour hasher automatiquement le mot de passe.
     */
    public function setMotPasseAttribute($value)
    {
        $this->attributes['mot_passe'] = Hash::make($value);
    }

    /**
     * Relations
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function offres()
    {
        return $this->hasMany(Offre::class, 'recruteur_id');
    }

    public function candidatures()
    {
        return $this->hasMany(Candidature::class, 'candidat_id');
    }

    public function candidaturesManager()
    {
        return $this->hasMany(Candidature::class, 'manager_id');
    }

    /**
     * Attributs castés
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
        ];
    }
}
