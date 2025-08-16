<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reponse extends Model
{
    protected $fillable = [
        'contenu_reponse','date_soumission','question_id'
    ];
    public function question()
    {
        // Relation vers le modèle question
        return $this->belongsTo(Question::class, 'question_id');
    }
}
