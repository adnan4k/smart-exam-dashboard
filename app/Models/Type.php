<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Type extends Model
{
    //

    protected $guarded = [];

    public function questions(){
        $this->hasMany(Question::class);
    }
}
