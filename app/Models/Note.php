<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    protected $fillable = [
        'candidat_id',
        'test_id',
        'note_candidat',
    ];

    public function candidat()
    {
        return $this->belongsTo(User::class, 'candidat_id');
    }

    public function test()
    {
        return $this->belongsTo(Test::class, 'test_id');
    }
}
