<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContestAttemptAnswer extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_correct'  => 'boolean',
        'answered_at' => 'datetime',
    ];

    public function attempt()
    {
        return $this->belongsTo(ContestAttempt::class, 'contest_attempt_id');
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function choice()
    {
        return $this->belongsTo(Choice::class);
    }
}
