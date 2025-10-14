<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Reponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'contenu_reponse',
        'date_soumission',
        'question_id',
        'candidat_id',        // lien vers le candidat
        'reponse_correcte',   // nouveau champ Vrai/Faux
    ];

    /**
     * Relation vers Question
     */
    public function question()
    {
        return $this->belongsTo(Question::class, 'question_id');
    }

    /**
     * Relation vers User (candidat)
     */
    public function candidat()
    {
        return $this->belongsTo(User::class, 'candidat_id');
    }
}
