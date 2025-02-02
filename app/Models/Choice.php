<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Choice extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_id', 
        'choice_text', 
        'choice_image_path', 
        'formula'
    ];

    /**
     * A choice belongs to a question.
     */
    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * Get full URL for the choice image.
     */
    public function getChoiceImageUrlAttribute()
    {
        return $this->choice_image_path ? asset('storage/' . $this->choice_image_path) : null;
    }
} 