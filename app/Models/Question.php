<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = [
        'intitule_question','type_question','points_question','test_id'
    ];
    public function offre()
    {
        // Relation vers le modèle Test
        return $this->belongsTo(Test::class, 'test_id');
    }
}
