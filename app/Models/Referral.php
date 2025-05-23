<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Referral extends Model
{
    //

    protected $guarded = [];

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }
    
    public function referred()
    {
        return $this->belongsTo(User::class, 'referred_id');
    }
    
}
