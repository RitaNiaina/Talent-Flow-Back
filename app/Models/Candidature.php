<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Candidature extends Model
{
    protected $fillable = [
        'date_postule',
        'etat_candidature',
        'cv_candidat',
        'lettre_motivation',
        'candidat_id',
        'offre_id',
    ];

    // Relations
    public function candidat()
    {
        return $this->belongsTo(User::class, 'candidat_id');
    }

    public function offre()
    {
        return $this->belongsTo(Offre::class, 'offre_id');
    }
}
