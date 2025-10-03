<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Offre extends Model
{
    protected $fillable = [
        'titre_offre',
        'description_offre',
        'date_publication',
        'date_expiration',
        'statut_offre',
        'recruteur_id',
        'type_offre',   
        'lieu_offre'    
    ];

    public function recruteur()
    {
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
    public function competences()
{
    return $this->belongsToMany(Competence::class, 'competence_offre', 'offre_id', 'competence_id');
}

}
