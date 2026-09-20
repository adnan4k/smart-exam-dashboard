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

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function packages()
    {
        return $this->hasMany(Package::class);
    }

    public function subjects()
    {
        return $this->hasMany(Subject::class);
    }
}
