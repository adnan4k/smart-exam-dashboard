<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\ContestException;
use App\Http\Controllers\Controller;
use App\Models\Contest;
use App\Models\ContestAttempt;
use App\Models\ContestRewardRule;
use App\Models\Question;
use App\Models\RewardTransaction;
use App\Services\ContestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * The contest surface. Deliberately separate from QuestionController: the study
 * endpoints ship the correct answer and explanation with every question, which
 * is right for studying and fatal for a contest.
 *
 * Every route here requires a Sanctum token. A contest pays out real rewards, so
 * the caller has to prove who they are rather than naming a user_id.
 */
class ContestController extends Controller
{
    public function __construct(private ContestService $contests)
    {
    }

    /**
     * Contests this student can see, with where they stand in each.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $contests = Contest::visibleTo($user)
            ->with('type:id,name')
            ->where('ends_at', '>=', now()->subDays(30))
            ->orderBy('starts_at')
            ->get();

        $attempts = ContestAttempt::where('user_id', $user->id)
            ->whereIn('contest_id', $contests->pluck('id'))
            ->get()
            ->keyBy('contest_id');

        // Contests stay browsable without a subscription: the prize bands and
        // the countdown are what sell the package. Entry is refused in
        // ContestService::start(); this flag is only so the app can render the
        // card locked instead of letting the student tap through to a 403.
        $locked = ! $user->hasPaidPackage();

        return response()->json([
            'status'      => 'success',
            'server_time' => now()->toIso8601String(),
            'contests'    => $contests->map(function ($contest) use ($attempts, $locked) {
                $attempt = $attempts->get($contest->id);

                return [
                    'id'                 => $contest->id,
                    'title'              => $contest->title,
                    'description'        => $contest->description,
                    'exam_type'          => $contest->type?->name,
                    'question_count'     => $contest->question_count,
                    'duration_minutes'   => $contest->duration_minutes,
                    'starts_at'          => $contest->starts_at->toIso8601String(),
                    'ends_at'            => $contest->ends_at->toIso8601String(),
                    'join_closes_at'     => $contest->joinClosesAt()->toIso8601String(),
                    'state'              => $this->stateOf($contest),
                    'has_attempted'      => $attempt !== null,
                    'attempt_status'     => $attempt?->status,
                    'my_score'           => $attempt?->status === 'submitted' ? $attempt->score : null,
                    'my_rank'            => $attempt?->rank,
                    // An attempt already in progress can always be finished,
                    // so it is never shown locked - see ContestService::start().
                    'locked'             => $locked && $attempt?->status !== 'in_progress',
                    'lock_reason'        => $locked && $attempt?->status !== 'in_progress'
                        ? 'subscription_required'
                        : null,
                ];
            }),
        ]);
    }

    /**
     * One contest in full - the app's contest-details screen. The list stays a
     * summary; this is where the prize bands and the caller's own entry live.
     */
    public function show(Request $request, Contest $contest): JsonResponse
    {
        $user = $request->user();

        // Drafts and other-cohort contests must not even be discoverable.
        $contest = Contest::visibleTo($user)
            ->with(['type:id,name', 'subject:id,name'])
            ->find($contest->id);

        if (! $contest) {
            return response()->json(['status' => 'error', 'error_code' => 'not_found',
                'message' => 'Contest not found.'], 404);
        }

        $attempt = ContestAttempt::where('contest_id', $contest->id)
            ->where('user_id', $user->id)
            ->first();

        // An attempt already under way can be finished regardless, so it is
        // never presented as locked. See ContestService::start().
        $locked = ! $user->hasPaidPackage() && $attempt?->status !== 'in_progress';

        return response()->json([
            'status'      => 'success',
            'server_time' => now()->toIso8601String(),
            'contest'     => [
                'id'               => $contest->id,
                'title'            => $contest->title,
                'description'      => $contest->description,
                'exam_type'        => $contest->type?->name,
                'subject'          => $contest->subject?->name,
                'question_count'   => $contest->question_count,
                'duration_minutes' => $contest->duration_minutes,
                'starts_at'        => $contest->starts_at->toIso8601String(),
                'ends_at'          => $contest->ends_at->toIso8601String(),
                'join_closes_at'   => $contest->joinClosesAt()->toIso8601String(),
                'state'            => $this->stateOf($contest),
                // Prize bands the contest pays out; the contest's own rules win,
                // otherwise the global defaults are shown.
                'rewards'          => ContestRewardRule::forContest($contest)->map(fn ($rule) => [
                    'rank_from' => $rule->rank_from,
                    'rank_to'   => $rule->rank_to,
                    'stars'     => $rule->stars,
                ])->values(),
                'locked'           => $locked,
                'lock_reason'      => $locked ? 'subscription_required' : null,
                // What the student has to buy to unlock it. Their own exam
                // type's package, not the contest's - a contest with no type
                // of its own is still only reachable through that package.
                'package'          => $locked ? [
                    'type_id' => $user->type_id,
                    'name'    => $user->type?->name,
                    'price'   => $user->type?->price,
                ] : null,
            ],
            'my_attempt' => $attempt ? [
                'status'            => $attempt->status,
                'started_at'        => $attempt->started_at?->toIso8601String(),
                'expires_at'        => $attempt->expires_at?->toIso8601String(),
                'seconds_remaining' => $attempt->status === 'in_progress' ? $attempt->secondsRemaining() : 0,
                'submitted_at'      => $attempt->submitted_at?->toIso8601String(),
                'score'             => $attempt->score,
                'rank'              => $attempt->rank,
                'stars_awarded'     => $attempt->stars_awarded,
            ] : null,
        ]);
    }

    /**
     * Enter a contest, or resume an entry already in progress.
     *
     * Returns the whole paper in one payload: the student can then answer
     * offline if the network drops, and the countdown runs against a deadline
     * the server set.
     */
    public function start(Request $request, Contest $contest): JsonResponse
    {
        try {
            $attempt = $this->contests->start(
                $contest,
                $request->user(),
                $request->input('device_id') ?: $request->user()->device_id,
                $request->ip()
            );
        } catch (ContestException $e) {
            return $this->failed($e);
        }

        $answered = $attempt->answers()
            ->whereNotNull('choice_id')
            ->pluck('choice_id', 'question_id');

        return response()->json([
            'status'  => 'success',
            'attempt' => [
                'id'                => $attempt->id,
                'started_at'        => $attempt->started_at->toIso8601String(),
                'expires_at'        => $attempt->expires_at->toIso8601String(),
                'seconds_remaining' => $attempt->secondsRemaining(),
                // Saved answers come back so a resumed attempt is not blank.
                'saved_answers'     => $answered,
            ],
            'server_time' => now()->toIso8601String(),
            'contest'     => [
                'id'               => $contest->id,
                'title'            => $contest->title,
                'duration_minutes' => $contest->duration_minutes,
                'ends_at'          => $contest->ends_at->toIso8601String(),
            ],
            'questions' => $this->contests->paperFor($contest),
        ]);
    }

    /**
     * Mid-contest autosave. The app calls this whenever it has connectivity.
     */
    public function saveAnswers(Request $request, Contest $contest): JsonResponse
    {
        $request->validate([
            'answers'               => 'required|array',
            'answers.*.question_id' => 'required|integer',
            'answers.*.choice_id'   => 'nullable|integer',
        ]);

        $attempt = $this->attemptFor($request, $contest);

        if (! $attempt) {
            return response()->json(['status' => 'error', 'error_code' => 'no_attempt',
                'message' => 'You have not started this contest.'], 404);
        }

        try {
            $saved = $this->contests->syncAnswers($attempt, $request->input('answers'));
        } catch (ContestException $e) {
            return $this->failed($e);
        }

        return response()->json([
            'status'            => 'success',
            'saved'             => $saved,
            'seconds_remaining' => $attempt->secondsRemaining(),
            'server_time'       => now()->toIso8601String(),
        ]);
    }

    /**
     * Final submission. Grading happens here, on the server, from the question
     * bank - the app never reports a score.
     */
    public function submit(Request $request, Contest $contest): JsonResponse
    {
        $request->validate([
            'answers'               => 'nullable|array',
            'answers.*.question_id' => 'required_with:answers|integer',
            'answers.*.choice_id'   => 'nullable|integer',
        ]);

        $attempt = $this->attemptFor($request, $contest);

        if (! $attempt) {
            return response()->json(['status' => 'error', 'error_code' => 'no_attempt',
                'message' => 'You have not started this contest.'], 404);
        }

        try {
            $attempt = $this->contests->submit($attempt, $request->input('answers', []));
        } catch (ContestException $e) {
            return $this->failed($e);
        }

        return response()->json([
            'status' => 'success',
            'result' => [
                'score'              => $attempt->score,
                'correct'            => $attempt->correct_count,
                'wrong'              => $attempt->wrong_count,
                'unanswered'         => $attempt->unanswered_count,
                'total_questions'    => $attempt->total_questions,
                'time_taken_seconds' => $attempt->time_taken_seconds,
                // Final standings are only settled when the contest closes.
                'provisional_rank'   => $this->contests->provisionalRank($attempt),
                'rewards_pending'    => ! $contest->hasEnded(),
            ],
        ]);
    }

    /**
     * The student's own paper back, and - once the contest is over for
     * everybody - the answer key with it.
     *
     * Finishing early must not buy an answer key: the first student to submit
     * could otherwise pull the key here and hand it to everyone still sitting
     * the paper. So while the contest is still running this returns only what
     * the student themselves chose. The correct choice stays server-side, used
     * for scoring and nothing else, until ends_at has passed.
     */
    public function review(Request $request, Contest $contest): JsonResponse
    {
        // Belt and braces. An attempt is the real gate here and one can only be
        // created by ContestService::start(), which already refuses an
        // unsubscribed student - but this endpoint hands back the answer key
        // for questions that have not reached the study bank yet, so it states
        // the rule itself rather than inheriting it.
        if (! $request->user()->hasPaidPackage()) {
            return $this->failed(ContestException::subscriptionRequired());
        }

        $attempt = $this->attemptFor($request, $contest);

        if (! $attempt || $attempt->status === 'in_progress') {
            return response()->json(['status' => 'error', 'error_code' => 'not_finished',
                'message' => 'Finish the contest to see the answers.'], 403);
        }

        $keyReleased = $contest->hasEnded();

        $answers = $attempt->answers()->get()->keyBy('question_id');

        $questions = Question::withUnreleased()
            ->with('choices')
            ->whereIn('id', $contest->contestQuestions()->pluck('question_id'))
            ->get();

        return response()->json([
            'status'        => 'success',
            'score'         => $attempt->score,
            'rank'          => $attempt->rank,
            'stars_awarded' => $attempt->stars_awarded,
            // Standings are only settled once the contest is finalized.
            'is_final'      => $contest->status === 'finalized',
            // Tells the app whether to render an answer key or just the
            // student's own selections with a "comes back when the contest
            // closes" note.
            'answers_released'    => $keyReleased,
            'answers_released_at' => $contest->ends_at->toIso8601String(),
            'questions' => $questions->map(function ($question) use ($answers, $keyReleased) {
                $answer = $answers->get($question->id);

                return [
                    'question_id'       => $question->id,
                    'question_text'     => $question->question_text,
                    // Null until the contest closes - never "the answer, but
                    // flagged": an app that ignores the flag still cannot leak it.
                    'correct_choice_id' => $keyReleased ? $question->correctChoiceId() : null,
                    'my_choice_id'      => $answer?->choice_id,
                    'is_correct'        => $keyReleased ? (bool) $answer?->is_correct : null,
                    'explanation'       => $keyReleased ? $question->explanation : null,
                    'explanation_image' => $keyReleased && $question->explanation_image_path
                        ? asset('storage/' . $question->explanation_image_path)
                        : null,
                    'choices' => $question->choices->map(fn ($c) => [
                        'id'   => $c->id,
                        'text' => $c->choice_text,
                    ]),
                ];
            }),
        ]);
    }

    /**
     * Standings for one contest, always including the caller's own row even
     * when they finished well outside the top of the table.
     */
    public function leaderboard(Request $request, Contest $contest): JsonResponse
    {
        $limit = min((int) $request->input('limit', 50), 200);

        $top = ContestAttempt::where('contest_id', $contest->id)
            ->ranked()
            ->with('user:id,name,institution_name')
            ->limit($limit)
            ->get();

        $rows = $top->values()->map(fn ($attempt, $i) => [
            'rank'               => $attempt->rank ?? $i + 1,
            'user_id'            => $attempt->user_id,
            'name'               => $attempt->user?->name,
            'institution'        => $attempt->user?->institution_name,
            'score'              => $attempt->score,
            'time_taken_seconds' => $attempt->time_taken_seconds,
            'stars_awarded'      => $attempt->stars_awarded,
        ]);

        $mine = ContestAttempt::where('contest_id', $contest->id)
            ->where('user_id', $request->user()->id)
            ->first();

        return response()->json([
            'status'      => 'success',
            'contest'     => ['id' => $contest->id, 'title' => $contest->title,
                              'state' => $this->stateOf($contest)],
            'is_final'    => $contest->status === 'finalized',
            'leaderboard' => $rows,
            'me' => $mine && $mine->status === 'submitted' ? [
                'rank'          => $mine->rank ?? $this->contests->provisionalRank($mine),
                'score'         => $mine->score,
                'stars_awarded' => $mine->stars_awarded,
            ] : null,
        ]);
    }

    /**
     * Season standings: stars earned across every contest.
     */
    public function globalLeaderboard(Request $request): JsonResponse
    {
        $user   = $request->user();
        $period = $request->input('period', 'all');
        $limit  = min((int) $request->input('limit', 50), 200);

        $since = match ($period) {
            'weekly'  => now()->startOfWeek(),
            'monthly' => now()->startOfMonth(),
            default   => null,
        };

        $totals = RewardTransaction::selectRaw('user_id, SUM(stars) as stars')
            ->where('stars', '>', 0)
            ->when($since, fn ($q) => $q->where('created_at', '>=', $since))
            ->when($user->type_id, fn ($q) => $q->whereIn(
                'user_id',
                \App\Models\User::where('type_id', $user->type_id)->select('id')
            ))
            ->groupBy('user_id')
            ->orderByDesc('stars')
            ->limit($limit)
            ->get();

        $names = \App\Models\User::whereIn('id', $totals->pluck('user_id'))
            ->get(['id', 'name', 'institution_name'])
            ->keyBy('id');

        return response()->json([
            'status' => 'success',
            'period' => $period,
            'leaderboard' => $totals->values()->map(fn ($row, $i) => [
                'rank'        => $i + 1,
                'user_id'     => $row->user_id,
                'name'        => $names[$row->user_id]->name ?? null,
                'institution' => $names[$row->user_id]->institution_name ?? null,
                'stars'       => (int) $row->stars,
            ]),
            'me' => [
                'total_stars' => $user->total_stars,
                'total_coins' => $user->total_coins,
            ],
        ]);
    }

    /**
     * The caller's own contest record, for the app's "My contests" screen.
     * Every entry with its result, newest first; an attempt still in progress
     * comes back with its seconds_remaining so a resume is one tap away.
     */
    public function myAttempts(Request $request): JsonResponse
    {
        $attempts = ContestAttempt::with('contest:id,title')
            ->where('user_id', $request->user()->id)
            ->latest('id')
            ->limit(100)
            ->get();

        return response()->json([
            'status'   => 'success',
            'attempts' => $attempts->map(fn ($attempt) => [
                'contest_id'         => $attempt->contest_id,
                'contest_title'      => $attempt->contest?->title,
                'status'             => $attempt->status,
                'started_at'         => $attempt->started_at?->toIso8601String(),
                'expires_at'         => $attempt->expires_at?->toIso8601String(),
                'seconds_remaining'  => $attempt->status === 'in_progress' ? $attempt->secondsRemaining() : 0,
                'submitted_at'       => $attempt->submitted_at?->toIso8601String(),
                'score'              => $attempt->score,
                'correct'            => $attempt->correct_count,
                'wrong'              => $attempt->wrong_count,
                'unanswered'         => $attempt->unanswered_count,
                'total_questions'    => $attempt->total_questions,
                'time_taken_seconds' => $attempt->time_taken_seconds,
                'rank'               => $attempt->rank,
                'stars_awarded'      => $attempt->stars_awarded,
            ]),
        ]);
    }

    /**
     * A student's own wallet and how they earned it.
     */
    public function myRewards(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'status'      => 'success',
            'total_stars' => $user->total_stars,
            'total_coins' => $user->total_coins,
            'history'     => RewardTransaction::where('user_id', $user->id)
                ->latest()
                ->limit(50)
                ->get(['stars', 'coins', 'reason', 'meta', 'created_at']),
        ]);
    }

    private function attemptFor(Request $request, Contest $contest): ?ContestAttempt
    {
        return ContestAttempt::where('contest_id', $contest->id)
            ->where('user_id', $request->user()->id)
            ->first();
    }

    private function stateOf(Contest $contest): string
    {
        if ($contest->status === 'finalized') {
            return 'finalized';
        }

        if (! $contest->hasStarted()) {
            return 'upcoming';
        }

        return $contest->hasEnded() ? 'ended' : 'live';
    }

    private function failed(ContestException $e): JsonResponse
    {
        return response()->json([
            'status'     => 'error',
            'error_code' => $e->errorCode,
            'message'    => $e->getMessage(),
        ], $e->statusCode);
    }
}
