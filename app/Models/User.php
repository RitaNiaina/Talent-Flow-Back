<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nom_utilisateur',
        'email_utilisateur',
        'mot_passe',
        'role_id',
        'cv_candidat',
        'lettre_motivation',
        'date_inscription',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'mot_passe',
        'remember_token',
    ];
    // Hash automatique du mot de passe
    public function setMotPasseAttribute($value)
    {
        $this->attributes['mot_passe'] = Hash::make($value);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }
    
    // Relation : un recruteur peut avoir plusieurs offres
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
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'date_inscription' => 'date',
        ];
    }
}
