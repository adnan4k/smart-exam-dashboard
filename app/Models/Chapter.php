<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chapter extends Model
{
    //

    protected $guarded   = [];

    public function subject()
    {
        return $this->belongsTo(\App\Models\Subject::class);
    }
}
