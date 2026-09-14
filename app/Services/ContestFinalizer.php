<?php

namespace App\Services;

use App\Models\Contest;
use App\Models\ContestAttempt;
use App\Models\ContestRewardRule;
use App\Models\Question;
use App\Models\RewardTransaction;
use App\Models\Scopes\ReleasedQuestionScope;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ContestFinalizer
{
    public function __construct(private ContestService $contests)
    {
    }

    /**
     * Close a finished contest: score any stragglers, rank everyone, pay out
     * stars and coins, then release the paper into the study bank.
     *
     * Safe to run more than once - the ledger is keyed by attempt, so a repeat
     * run re-ranks without paying anyone twice.
     */
    public function finalize(Contest $contest): array
    {
        if (! $contest->hasEnded()) {
            return ['finalized' => false, 'reason' => 'contest_still_running'];
        }

        $this->contests->closeExpiredAttempts($contest);

        $rules = ContestRewardRule::forContest($contest);

        $ranked = ContestAttempt::where('contest_id', $contest->id)
            ->ranked()
            ->get();

        $awarded = 0;

        DB::transaction(function () use ($contest, $ranked, $rules, &$awarded) {
            $rank = 0;
            $lastKey = null;

            foreach ($ranked as $index => $attempt) {
                // Genuine ties share a rank; the next attempt skips ahead.
                $key = $attempt->score . ':' . $attempt->time_taken_seconds;
                if ($key !== $lastKey) {
                    $rank = $index + 1;
                    $lastKey = $key;
                }

                $rule = $rules->first(fn ($r) => $r->covers($rank));

                $stars = $rule?->stars ?? 0;
                $coins = $rule?->coins ?? 0;

                $attempt->update([
                    'rank'           => $rank,
                    'stars_awarded'  => $stars,
                    'coins_awarded'  => $coins,
                ]);

                if ($stars || $coins) {
                    $awarded += $this->credit($attempt, $stars, $coins, $rank);
                }
            }

            $this->releaseQuestions($contest);

            $contest->update([
                'status'       => 'finalized',
                'finalized_at' => now(),
            ]);
        });

        return [
            'finalized'    => true,
            'participants' => $ranked->count(),
            'paid'         => $awarded,
        ];
    }

    /**
     * Write the ledger entry and move the cached balance in the same breath.
     * The idempotency key means a second finalize run is a no-op.
     */
    private function credit(ContestAttempt $attempt, int $stars, int $coins, int $rank): int
    {
        $key = 'contest_attempt:' . $attempt->id;

        if (RewardTransaction::where('idempotency_key', $key)->exists()) {
            return 0;
        }

        RewardTransaction::create([
            'user_id'         => $attempt->user_id,
            'stars'           => $stars,
            'coins'           => $coins,
            'source_type'     => ContestAttempt::class,
            'source_id'       => $attempt->id,
            'reason'          => 'contest_rank',
            'meta'            => [
                'contest_id' => $attempt->contest_id,
                'rank'       => $rank,
                'score'      => $attempt->score,
            ],
            'idempotency_key' => $key,
        ]);

        User::whereKey($attempt->user_id)->update([
            'total_stars' => DB::raw('total_stars + ' . $stars),
            'total_coins' => DB::raw('total_coins + ' . $coins),
        ]);

        return 1;
    }

    /**
     * The contest is over, so its questions become study material - complete
     * with explanations, and with real difficulty data behind them.
     */
    private function releaseQuestions(Contest $contest): void
    {
        Question::withoutGlobalScope(ReleasedQuestionScope::class)
            ->whereIn('id', $contest->contestQuestions()->pluck('question_id'))
            ->whereNull('released_at')
            ->update(['released_at' => now()]);
    }
}
