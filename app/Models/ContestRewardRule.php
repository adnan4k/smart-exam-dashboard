<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContestRewardRule extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function contest()
    {
        return $this->belongsTo(Contest::class);
    }

    public function covers(int $rank): bool
    {
        return $rank >= $this->rank_from
            && ($this->rank_to === null || $rank <= $this->rank_to);
    }

    /**
     * A contest's own bands win; otherwise fall back to the global defaults.
     */
    public static function forContest(Contest $contest)
    {
        $own = static::where('contest_id', $contest->id)->where('is_active', true)
            ->orderBy('rank_from')->get();

        if ($own->isNotEmpty()) {
            return $own;
        }

        return static::whereNull('contest_id')->where('is_active', true)
            ->orderBy('rank_from')->get();
    }
}
