<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContestQuestion extends Model
{
    protected $guarded = [];

    public function contest()
    {
        return $this->belongsTo(Contest::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
