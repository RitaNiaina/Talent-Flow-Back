<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Offre extends Model
{
    protected $fillable = [
        'titre_offre','description_offre','date_publication','date_expiration','statut_offre','recruteur_id'
    ];
    public function recruteur()
    {
        // Relation vers le modèle User
        return $this->belongsTo(User::class, 'recruteur_id');
    }
    public function candidatures()
    {
    return $this->hasMany(Candidature::class, 'offre_id');
    }

    public function tests()
    {
        return $this->hasMany(Test::class, 'offre_id');
    }
}
