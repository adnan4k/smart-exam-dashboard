<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Chapter extends Model
{
    //

    protected $guarded   = [];

    // Removed subject relationship

    use HasFactory;

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function notes()
    {
        return $this->hasMany(Note::class);
    }
}
