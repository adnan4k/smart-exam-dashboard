<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContestAttempt extends Model
{
    protected $guarded = [];

    protected $casts = [
        'started_at'     => 'datetime',
        'expires_at'     => 'datetime',
        'submitted_at'   => 'datetime',
        'score'          => 'integer',
        'question_stars' => 'integer',
    ];

    public function contest()
    {
        return $this->belongsTo(Contest::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function answers()
    {
        return $this->hasMany(ContestAttemptAnswer::class);
    }

    public function isOpen(): bool
    {
        return $this->status === 'in_progress' && now()->lte($this->expires_at);
    }

    /**
     * Carbon 3 returns a signed float here, so floor it: the client counts down
     * in whole seconds and a fractional value would trip the int return type.
     */
    public function secondsRemaining(): int
    {
        return max(0, (int) floor(now()->diffInSeconds($this->expires_at, false)));
    }

    /**
     * Stars earned first, then score, then speed. For a contest that pays no
     * stars per question every question_stars is 0, so this falls back to the
     * old score-then-time order.
     */
    public function scopeRanked($query)
    {
        return $query->where('status', 'submitted')
            ->orderByDesc('question_stars')
            ->orderByDesc('score')
            ->orderBy('time_taken_seconds')
            ->orderBy('submitted_at');
    }
}
