<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_id', 
        'year_group_id', 
        'question_text', 
        'question_image_path',
        'formula', 
        'answer_text', 
        'explanation', 
        'explanation_image_path'
    ];

    /**
     * A question belongs to one subject.
     */
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * A question belongs to one year group.
     */
    public function yearGroup()
    {
        return $this->belongsTo(YearGroup::class);
    }

    /**
     * A question can have many choices.
     */
    public function choices()
    {
        return $this->hasMany(Choice::class);
    }

    /**
     * Get full URL for the question image.
     */
    public function getQuestionImageUrlAttribute()
    {
        return $this->question_image_path ? asset('storage/' . $this->question_image_path) : null;
    }

    /**
     * Get full URL for the explanation image.
     */
    public function getExplanationImageUrlAttribute()
    {
        return $this->explanation_image_path ? asset('storage/' . $this->explanation_image_path) : null;
    }
} 