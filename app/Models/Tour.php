<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tour extends Model
{
    //
    public function schedules(){
        return $this->hasMany(Schedule::class);
    }
}
