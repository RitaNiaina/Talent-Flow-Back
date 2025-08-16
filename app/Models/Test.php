<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Test extends Model
{
    protected $fillable = [
        'nom_test','duree_test','description_test','offre_id'
    ];
    public function offre()
    {
        // Relation vers le modèle Offre
        return $this->belongsTo(Offre::class, 'offre_id');
    }
}
