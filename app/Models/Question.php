<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Scopes\ReleasedQuestionScope;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_id',
        'year_group_id',
        'chapter_id',
        'question_text',
        'question_image_path',
        'formula',
        'correct_choice_id',
        'explanation',
        'explanation_image_path',
        'type_id',
        'duration',
        'science_type',
        'region',
        'answer_id',
        'bank',
        'released_at',
        'difficulty',
    ];

    protected $casts = [
        'science_type' => 'string',
        'released_at'  => 'datetime',
    ];

    /**
     * Unreleased questions are invisible everywhere by default, so the existing
     * study API and admin screens need no changes to stay contest-safe.
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new ReleasedQuestionScope);
    }

    /**
     * Includes the questions the global scope hides. Only contest code should
     * use this - the paper builder and the live contest endpoints.
     */
    public function scopeWithUnreleased($query)
    {
        return $query->withoutGlobalScope(ReleasedQuestionScope::class);
    }

    /**
     * The pool contest papers are built from: authored for contests, not yet
     * released to the study bank.
     */
    public function scopeContestPool($query)
    {
        return $query->withoutGlobalScope(ReleasedQuestionScope::class)
            ->where('bank', 'contest')
            ->whereNull('released_at');
    }

    public function contests()
    {
        return $this->belongsToMany(Contest::class, 'contest_questions');
    }

    /**
     * The admin form keeps correct_choice_id and answer_id in step; read either.
     */
    public function correctChoiceId(): ?int
    {
        return $this->correct_choice_id ?: $this->answer_id;
    }

    /**
     * A question belongs to one subject.
     */
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
    // In app/Models/Question.php
public function correctChoice()
{
    return $this->belongsTo(Choice::class, 'correct_choice_id');
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

    public function type(){
        return $this->belongsTo(Type::class);
    }
    public function chapter(){
        return $this->belongsTo(Chapter::class);
    }

    /**
     * Get full URL for the explanation image.
     */
    public function getExplanationImageUrlAttribute()
    {
        return $this->explanation_image_path ? asset('storage/' . $this->explanation_image_path) : null;
    }


} 