<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReponseCandidat extends Model
{
    use HasFactory;

    protected $table = 'reponses_candidats';

    protected $fillable = [
        'candidat_id',
        'question_id',
        'reponse_id',
        'contenu_reponse',
        'reponse_correcte',
        'date_soumission',
    ];
    
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

    public function reponse() {
        return $this->belongsTo(Reponse::class, 'reponse_id');
    }
}
