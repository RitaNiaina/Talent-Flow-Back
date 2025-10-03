<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Competence extends Model
{
    protected $fillable = [
        'nom_competence',
    ];

    public function offres()
    {
        return $this->belongsToMany(Offre::class, 'competence_offre', 'competence_id', 'offre_id');
    }
}
