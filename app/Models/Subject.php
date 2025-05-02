<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'type_id', 'default_duration'];

    /**
     * A subject can have many questions.
     */
    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function type()
    {
        return $this->belongsTo(\App\Models\Type::class);
    }
} 