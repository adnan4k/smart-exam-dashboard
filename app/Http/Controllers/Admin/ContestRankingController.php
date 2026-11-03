<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContestAttempt;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * One table ranking every student who has sat a contest, across all contests.
 *
 * Order: total stars, then total score, then total time taken (less is
 * better) - the same tiebreak a single contest uses. Genuine ties share a rank.
 *
 * Stars are read from the attempts, not the reward ledger, so live contests
 * count too: a finalized contest contributes what was actually paid (answer
 * stars plus rank bonus), an unfinalized one the stars its answers have earned
 * so far. Voided and still-running attempts are left out.
 */
class ContestRankingController extends Controller
{
    private const PER_PAGE = 25;

    /** Attempts that count as having taken part. */
    private const COUNTED = ['submitted', 'expired'];

    public function index(Request $request)
    {
        $typeId = $request->integer('type') ?: null;
        $search = trim((string) $request->input('q', ''));

        // Ranks are computed over the whole cohort first; the search box only
        // narrows what is shown, so a student keeps their real rank.
        $ranked = $this->rank($this->totals($typeId));

        $rows = $search === ''
            ? $ranked
            : $ranked->filter(fn ($r) => str_contains(mb_strtolower($r->name . ' ' . $r->email), mb_strtolower($search)))->values();

        $page = LengthAwarePaginator::resolveCurrentPage();
        $paginator = new LengthAwarePaginator(
            $rows->forPage($page, self::PER_PAGE)->values(),
            $rows->count(),
            self::PER_PAGE,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $history = $this->history($paginator->getCollection()->pluck('user_id'));

        $summary = [
            'participants' => $ranked->count(),
            'entries'      => (int) $ranked->sum('contests'),
            'contests'     => $this->baseAttempts($typeId)->distinct()->count('a.contest_id'),
            'stars'        => (int) $ranked->sum('stars'),
        ];

        return view('contests.rankings', [
            'rows'    => $paginator,
            'history' => $history,
            'summary' => $summary,
            'types'   => Type::orderBy('name')->get(['id', 'name']),
            'typeId'  => $typeId,
            'search'  => $search,
        ]);
    }

    private function baseAttempts(?int $typeId)
    {
        return DB::table('contest_attempts as a')
            ->join('contests as c', 'c.id', '=', 'a.contest_id')
            ->join('users as u', 'u.id', '=', 'a.user_id')
            ->whereIn('a.status', self::COUNTED)
            ->when($typeId, fn ($q) => $q->where('u.type_id', $typeId));
    }

    /**
     * One row per student with their totals across every contest.
     */
    private function totals(?int $typeId): Collection
    {
        return $this->baseAttempts($typeId)
            ->leftJoin('types as t', 't.id', '=', 'u.type_id')
            ->groupBy('a.user_id', 'u.name', 'u.email', 'u.institution_name', 't.name')
            ->selectRaw("
                a.user_id,
                u.name,
                u.email,
                u.institution_name as institution,
                t.name as type_name,
                COUNT(*) as contests,
                SUM(CASE WHEN c.status = 'finalized' THEN a.stars_awarded ELSE a.question_stars END) as stars,
                SUM(a.score) as score,
                SUM(a.correct_count) as correct,
                SUM(a.total_questions) as questions,
                SUM(a.time_taken_seconds) as time_taken,
                SUM(CASE WHEN c.status = 'finalized' AND a.`rank` = 1 THEN 1 ELSE 0 END) as wins,
                MIN(CASE WHEN c.status = 'finalized' THEN a.`rank` END) as best_rank
            ")
            ->get()
            ->map(function ($r) {
                foreach (['contests', 'stars', 'score', 'correct', 'questions', 'time_taken', 'wins'] as $col) {
                    $r->$col = (int) $r->$col;
                }
                $r->best_rank = $r->best_rank === null ? null : (int) $r->best_rank;
                $r->accuracy  = $r->questions ? round($r->correct / $r->questions * 100) : 0;

                return $r;
            });
    }

    private function rank(Collection $totals): Collection
    {
        $sorted = $totals->sort(fn ($a, $b) => [$b->stars, $b->score, $a->time_taken, $a->user_id]
            <=> [$a->stars, $a->score, $b->time_taken, $b->user_id])->values();

        $rank = 0;
        $lastKey = null;

        return $sorted->map(function ($r, $i) use (&$rank, &$lastKey) {
            $key = "{$r->stars}:{$r->score}:{$r->time_taken}";
            if ($key !== $lastKey) {
                $rank = $i + 1;
                $lastKey = $key;
            }
            $r->rank = $rank;

            return $r;
        });
    }

    /**
     * Per-contest results for the students on the current page only.
     */
    private function history(Collection $userIds): Collection
    {
        if ($userIds->isEmpty()) {
            return collect();
        }

        return ContestAttempt::query()
            ->whereIn('user_id', $userIds)
            ->whereIn('status', self::COUNTED)
            ->with('contest:id,title,status,starts_at')
            ->get()
            ->sortByDesc(fn ($a) => $a->contest?->starts_at)
            ->groupBy('user_id');
    }
}
