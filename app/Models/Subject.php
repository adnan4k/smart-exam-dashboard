<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    /**
     * A subject can have many questions.
     */
    public function questions()
    {
        return $this->hasMany(Question::class);
    }
} 