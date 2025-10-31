<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Entretien extends Model
{
    use HasFactory;

    protected $fillable = [
        'candidature_id',
        'manager_id',
        'type_entretien',
        'lieu',
        'lien_meet',
        'date_entretien',
        'commentaire',
    ];

    public function candidature()
    {
        return $this->belongsTo(Candidature::class);
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }
}
