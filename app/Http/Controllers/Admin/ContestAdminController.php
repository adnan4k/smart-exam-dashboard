<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contest;
use App\Models\ContestAttempt;
use App\Models\ContestQuestion;
use App\Models\ContestRewardRule;
use App\Models\Question;
use App\Models\Subject;
use App\Models\Type;
use App\Services\ContestFinalizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Contest administration. Plain controllers and Blade with Alpine for the
 * interactive parts - the paper builder and the live leaderboard - rather than
 * Livewire, so the countdown and polling stay client side.
 */
class ContestAdminController extends Controller
{
    public function index(Request $request)
    {
        $contests = Contest::with('type:id,name')
            ->withCount('attempts')
            ->when($request->input('status'), fn ($q, $s) => $q->where('status', $s))
            ->orderByDesc('starts_at')
            ->paginate(15)
            ->withQueryString();

        return view('contests.index', compact('contests'));
    }

    public function create()
    {
        return view('contests.form', [
            'contest'  => new Contest(['duration_minutes' => 40, 'join_window_minutes' => 10]),
            'types'    => Type::orderBy('name')->get(),
            'subjects' => Subject::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['created_by'] = auth()->id();

        $contest = Contest::create($data);

        return redirect()->route('contests.builder', $contest)
            ->with('success', 'Contest created. Now build the paper.');
    }

    public function edit(Contest $contest)
    {
        return view('contests.form', [
            'contest'  => $contest,
            'types'    => Type::orderBy('name')->get(),
            'subjects' => Subject::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Contest $contest)
    {
        // A contest that students have already sat must not change underneath them.
        if ($contest->attempts()->exists() && $contest->hasStarted()) {
            return back()->with('error', 'This contest has already been sat and can no longer be edited.');
        }

        $contest->update($this->validated($request));

        return redirect()->route('contests.index')->with('success', 'Contest updated.');
    }

    public function destroy(Contest $contest)
    {
        if ($contest->attempts()->exists()) {
            return back()->with('error', 'Students have entered this contest, so it cannot be deleted.');
        }

        $contest->delete();

        return redirect()->route('contests.index')->with('success', 'Contest deleted.');
    }

    /**
     * The paper builder: pick questions from the contest bank.
     */
    public function builder(Contest $contest)
    {
        return view('contests.builder', [
            'contest'  => $contest->load('type'),
            'subjects' => Subject::orderBy('name')->get(),
        ]);
    }

    /**
     * Available questions, drawn only from the contest pool - authored for
     * contests and not yet released to students.
     */
    public function pool(Request $request, Contest $contest): JsonResponse
    {
        $used = $contest->contestQuestions()->pluck('question_id');

        $questions = Question::contestPool()
            ->with('subject:id,name')
            ->whereNotIn('id', $used)
            // A question already committed to another contest is off limits.
            ->whereNotIn('id', ContestQuestion::pluck('question_id'))
            ->when($request->input('subject_id'), fn ($q, $id) => $q->where('subject_id', $id))
            ->when($request->input('difficulty'), fn ($q, $d) => $q->where('difficulty', $d))
            ->when($contest->type_id, fn ($q) => $q->where('type_id', $contest->type_id))
            ->when($request->input('search'), fn ($q, $s) => $q->where('question_text', 'like', "%{$s}%"))
            ->limit(100)
            ->get();

        return response()->json([
            'paper' => $contest->contestQuestions()->with('question.subject:id,name')->get()
                ->map(fn ($cq) => [
                    'id'          => $cq->id,
                    'question_id' => $cq->question_id,
                    'position'    => $cq->position,
                    'points'      => $cq->points,
                    'text'        => \Illuminate\Support\Str::limit(strip_tags($cq->question?->question_text ?? ''), 120),
                    'subject'     => $cq->question?->subject?->name,
                    'difficulty'  => $cq->question?->difficulty,
                ]),
            'available' => $questions->map(fn ($q) => [
                'question_id' => $q->id,
                'text'        => \Illuminate\Support\Str::limit(strip_tags($q->question_text), 120),
                'subject'     => $q->subject?->name,
                'difficulty'  => $q->difficulty,
            ]),
        ]);
    }

    public function addQuestion(Request $request, Contest $contest): JsonResponse
    {
        $request->validate(['question_id' => 'required|integer']);

        if ($contest->attempts()->exists()) {
            return response()->json(['message' => 'Students have already sat this contest.'], 409);
        }

        $position = (int) $contest->contestQuestions()->max('position') + 1;

        ContestQuestion::firstOrCreate(
            ['contest_id' => $contest->id, 'question_id' => $request->input('question_id')],
            ['position' => $position, 'points' => (int) $request->input('points', 1)]
        );

        $this->syncCount($contest);

        return response()->json(['ok' => true]);
    }

    public function removeQuestion(Request $request, Contest $contest): JsonResponse
    {
        if ($contest->attempts()->exists()) {
            return response()->json(['message' => 'Students have already sat this contest.'], 409);
        }

        $contest->contestQuestions()->where('question_id', $request->input('question_id'))->delete();
        $this->syncCount($contest);

        return response()->json(['ok' => true]);
    }

    /**
     * A draft is invisible to students; publishing it puts it on their schedule.
     */
    public function publish(Contest $contest)
    {
        if ($contest->contestQuestions()->count() === 0) {
            return back()->with('error', 'Add questions to the paper before publishing.');
        }

        $contest->update(['status' => 'scheduled']);
        $this->syncCount($contest);

        return back()->with('success', 'Contest published. Students can now see it on their schedule.');
    }

    public function unpublish(Contest $contest)
    {
        if ($contest->attempts()->exists()) {
            return back()->with('error', 'Students have already entered, so this cannot be pulled back to draft.');
        }

        $contest->update(['status' => 'draft']);

        return back()->with('success', 'Contest moved back to draft.');
    }

    /**
     * Standings. Live while the contest runs, final once it has been paid out.
     */
    public function leaderboard(Contest $contest)
    {
        return view('contests.leaderboard', [
            'contest' => $contest->load('type'),
            'rules'   => ContestRewardRule::forContest($contest),
        ]);
    }

    /**
     * Polled by the leaderboard page while a contest is running.
     */
    public function standings(Contest $contest): JsonResponse
    {
        $attempts = ContestAttempt::where('contest_id', $contest->id)
            ->with('user:id,name,institution_name')
            ->orderByDesc('score')
            ->orderBy('time_taken_seconds')
            ->orderBy('submitted_at')
            ->limit(200)
            ->get();

        $rank = 0;
        $submittedSeen = 0;

        return response()->json([
            'state'        => $contest->status,
            'is_final'     => $contest->status === 'finalized',
            'server_time'  => now()->toIso8601String(),
            'ends_at'      => $contest->ends_at->toIso8601String(),
            'entered'      => $attempts->count(),
            'in_progress'  => $attempts->where('status', 'in_progress')->count(),
            'submitted'    => $attempts->where('status', 'submitted')->count(),
            'rows'         => $attempts->map(function ($a) use (&$rank, &$submittedSeen) {
                if ($a->status === 'submitted') {
                    $submittedSeen++;
                }

                return [
                    'attempt_id'   => $a->id,
                    'rank'         => $a->rank ?? ($a->status === 'submitted' ? $submittedSeen : null),
                    'name'         => $a->user?->name,
                    'institution'  => $a->user?->institution_name,
                    'status'       => $a->status,
                    'score'        => $a->score,
                    'total'        => $a->total_questions,
                    'time_taken'   => $a->time_taken_seconds,
                    'stars'        => $a->stars_awarded,
                    'coins'        => $a->coins_awarded,
                ];
            }),
        ]);
    }

    /**
     * Rank and pay out now rather than waiting for the scheduler.
     */
    public function finalize(Contest $contest, ContestFinalizer $finalizer)
    {
        $result = $finalizer->finalize($contest);

        if (! ($result['finalized'] ?? false)) {
            return back()->with('error', 'This contest is still running, so it cannot be finalized yet.');
        }

        return back()->with('success', "Finalized: {$result['participants']} participant(s) ranked, {$result['paid']} paid.");
    }

    /**
     * Void a suspect entry. Anything already paid out is clawed back through
     * the ledger so the balances stay honest.
     */
    public function voidAttempt(Request $request, ContestAttempt $attempt)
    {
        DB::transaction(function () use ($attempt) {
            if ($attempt->stars_awarded || $attempt->coins_awarded) {
                \App\Models\RewardTransaction::create([
                    'user_id'         => $attempt->user_id,
                    'stars'           => -$attempt->stars_awarded,
                    'coins'           => -$attempt->coins_awarded,
                    'source_type'     => ContestAttempt::class,
                    'source_id'       => $attempt->id,
                    'reason'          => 'contest_attempt_voided',
                    'idempotency_key' => 'contest_attempt_void:' . $attempt->id,
                ]);

                \App\Models\User::whereKey($attempt->user_id)->update([
                    'total_stars' => DB::raw('GREATEST(0, total_stars - ' . (int) $attempt->stars_awarded . ')'),
                    'total_coins' => DB::raw('GREATEST(0, total_coins - ' . (int) $attempt->coins_awarded . ')'),
                ]);
            }

            $attempt->update(['status' => 'voided', 'stars_awarded' => 0, 'coins_awarded' => 0, 'rank' => null]);
        });

        return back()->with('success', 'Entry voided. Re-finalize the contest to re-rank the others.');
    }

    private function syncCount(Contest $contest): void
    {
        $contest->update(['question_count' => $contest->contestQuestions()->count()]);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title'               => 'required|string|max:255',
            'description'         => 'nullable|string',
            'type_id'             => 'nullable|exists:types,id',
            'subject_id'          => 'nullable|exists:subjects,id',
            'duration_minutes'    => 'required|integer|min:1|max:600',
            'join_window_minutes' => 'required|integer|min:0|max:600',
            'starts_at'           => 'required|date',
            'ends_at'             => 'required|date|after:starts_at',
        ]);
    }
}
