<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Contest extends Model
{
    protected $guarded = [];

    protected $casts = [
        'starts_at'    => 'datetime',
        'ends_at'      => 'datetime',
        'finalized_at' => 'datetime',
    ];

    public function type()
    {
        return $this->belongsTo(Type::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function questions()
    {
        return $this->belongsToMany(Question::class, 'contest_questions')
            ->withPivot(['position', 'points'])
            ->orderBy('contest_questions.position');
    }

    public function contestQuestions()
    {
        return $this->hasMany(ContestQuestion::class)->orderBy('position');
    }

    public function attempts()
    {
        return $this->hasMany(ContestAttempt::class);
    }

    public function rewardRules()
    {
        return $this->hasMany(ContestRewardRule::class)->orderBy('rank_from');
    }

    /**
     * The paper is open for new entries only during the join window.
     */
    public function joinClosesAt(): Carbon
    {
        return $this->starts_at->copy()->addMinutes($this->join_window_minutes);
    }

    public function hasStarted(): bool
    {
        return now()->gte($this->starts_at);
    }

    public function hasEnded(): bool
    {
        return now()->gt($this->ends_at);
    }

    /**
     * Live means students can be sitting it right now.
     */
    public function isLive(): bool
    {
        return $this->status === 'scheduled' && $this->hasStarted() && ! $this->hasEnded();
    }

    public function acceptsNewEntries(): bool
    {
        return $this->isLive() && now()->lte($this->joinClosesAt());
    }

    /**
     * A student who joins late still gets their full duration, but never past
     * the contest's own end time.
     */
    public function deadlineFor(Carbon $startedAt): Carbon
    {
        $personal = $startedAt->copy()->addMinutes($this->duration_minutes);

        return $personal->gt($this->ends_at) ? $this->ends_at->copy() : $personal;
    }

    public function scopeVisibleTo($query, ?User $user)
    {
        return $query->whereIn('status', ['scheduled', 'closed', 'finalized'])
            ->when($user?->type_id, fn ($q, $typeId) => $q->where(function ($q) use ($typeId) {
                $q->whereNull('type_id')->orWhere('type_id', $typeId);
            }));
    }
}
