<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Scholarship extends Model
{

    protected $casts = [
        'date' => 'date',

    ];
    //
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
