<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Subject extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'is_sample' => 'boolean',
    ];

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

    public function chapters()
    {
        return $this->hasMany(Chapter::class);
    }

    public function notes()
    {
        return $this->hasMany(Note::class);
    }
} 